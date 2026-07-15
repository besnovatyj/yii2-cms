# План: Multi-stage сборка php-fpm (Alpine) с изоляцией кэша по расширениям

## Контекст

`app/docker/development/php-fpm/Dockerfile-Alpine-PRECOMPILED` собирает все PHP-расширения
из исходников (pecl/`docker-php-ext-install`). Файл нравится прозрачностью: видна каждая
библиотека (например, GD с webp/jpeg/avif), всё под контролем. **Проблема:** это один
линейный stage — правка любого раннего слоя (даже текста в `RUN echo "..."`) каскадно
инвалидирует Docker-кэш всех нижележащих слоёв, и компиляция идёт заново целиком.

Цель: сохранить полную прозрачность и ручную компиляцию, но сделать так, чтобы изменение
версии/опций одного расширения пересобирало **только его**, а не всё подряд. Решение —
многоэтапная сборка (multi-stage): каждое значимое расширение собирается в собственном
builder-stage из того же базового образа, а финальный stage копирует готовые `.so` и ставит
только runtime-библиотеки. BuildKit кэширует stage'ы независимо и собирает их параллельно.

### Разбор тупиковых попыток (чтобы закрыть вопрос и не возвращаться)

- **`claude/Dockerfile-Alpine-OPTIMIZED` (apk `php84-pecl-*`)** — собирается за ~3.7 с, но
  падает на `ApcCache requires PHP apcu extension to be loaded`. Причина фундаментальная:
  официальный образ `php:8.4-fpm-alpine` собирает PHP из исходников в `/usr/local`, а пакеты
  `php84-pecl-*` ставят `.so` для альпайновского PHP в `/usr/bin`. `.so` кладётся не туда, где
  его ищет ваш PHP. Это и есть «проблемы при смешении репозиториев». **Путь отбрасываем.**
- **`Dockerfile-Alpine-Gemini` (`mlocati/install-php-extensions`)** — рабочий, но непрозрачный
  (что внутри скрипта — надо изучать отдельно). Отклонено по вашему требованию понимать каждую
  строку. **Отбрасываем.**
- **Зачем `community`-репозиторий в PRECOMPILED** (вы забыли): из `community` берутся
  `docker-cli`, `imagemagick`/`imagemagick-libs`/`imagemagick-jpeg`, `libavif`(`-dev`),
  `libheif`(`-dev`), `libmemcached`(`-dev`). Без него GD соберётся, а **imagick и docker-cli — нет**.
  В новом файле это будет подписано построчно.

## Что делаем

Создаём **новый** файл `app/docker/development/php-fpm/Dockerfile-Alpine-MULTISTAGE`
(PRECOMPILED не трогаем — это рабочий фоллбэк, и в нём ваши комментарии). Переключаем активную
строку в `docker-compose.yml` на новый файл, старую оставляем закомментированной.

### Архитектура stage'ов

```
FROM php:8.4-fpm-alpine3.23 AS base          # общий базовый образ (для builder'ов и final)

FROM base AS builder-base                    # компиляторный toolchain ОДИН раз, общий слой
RUN apk add --no-cache $PHPIZE_DEPS linux-headers
#   $PHPIZE_DEPS = autoconf file g++ gcc libc-dev make pkgconf re2c
#   linux-headers нужен apcu и memcached

# --- по одному builder-stage на расширение, каждый FROM builder-base ---
FROM builder-base AS ext-gd        # + freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev libavif-dev zlib-dev
FROM builder-base AS ext-intl      # + icu-dev
FROM builder-base AS ext-zip       # + libzip-dev
FROM builder-base AS ext-imagick   # + imagemagick-dev libpng-dev libjpeg-turbo-dev libwebp-dev libavif-dev libheif-dev
FROM builder-base AS ext-xdebug    # (только toolchain)
FROM builder-base AS ext-apcu      # (только toolchain)
FROM builder-base AS ext-redis     # (только toolchain)
FROM builder-base AS ext-memcached # + libmemcached-dev zlib-dev
FROM builder-base AS ext-core      # + libxml2-dev — bcmath fileinfo pdo dom pcntl opcache pdo_mysql calendar exif

FROM base AS final                 # сборка готового образа из артефактов
```

**Почему именно так:** каждое расширение, у которого есть *версия* (PECL) или *внешние
библиотеки, которые вы можете захотеть подкрутить* (gd, intl, zip, imagick), — отдельный stage.
Чисто бандловые расширения без версий и без внешних либ (`bcmath`, `fileinfo`, `pdo`, `dom`,
`pcntl`, `opcache`, `pdo_mysql`, `calendar`, `exif`) объединены в один `ext-core`: дробить их
по отдельности смысла нет (нет версий → нечему меняться → кэш ничего не выиграет), а PHPIZE_DEPS
у них общий через `builder-base`. *Если захотите — любой из них тривиально вынести в свой stage.*

### Паттерн builder-stage (на примере gd)

```dockerfile
FROM builder-base AS ext-gd
RUN apk add --no-cache freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev libavif-dev zlib-dev \
 && docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp --with-avif \
 && docker-php-ext-install -j"$(nproc)" gd \
 && mkdir -p /export \
 && cp "$(php-config --extension-dir)/gd.so" /export/
```

- Артефакт каждого stage — только `.so` в стабильной папке `/export/` (не хардкодим путь
  `.../no-debug-non-zts-20240924/`, берём через `php-config --extension-dir`).
- PECL-stage аналогично: `pecl install imagick-3.8.1 && cp "$(php-config --extension-dir)/imagick.so" /export/`.
- **Версии PECL остаются явными** (`imagick-3.8.1`, `xdebug-3.5.0`, `apcu-5.1.28`, `redis-6.3.0`,
  `memcached-3.4.0`) — как сейчас. Бамп версии правит ровно одну строку в одном stage → пересоберётся
  только этот stage.
- `apk del .build-deps` в builder-stage больше **не нужен** (stage целиком отбрасывается, в финал
  попадают только `.so`). Это убирает «грязный» dance `--virtual .build-deps … && apk del`.

### Финальный stage (final)

```dockerfile
FROM base AS final
ARG TIME_ZONE=UTC

# Один прозрачный список RUNTIME-библиотек (без -dev). Подписать, что чьё:
RUN apk add --no-cache \
      libzip \                                  # zip
      icu-libs icu-data-full \                  # intl
      freetype libjpeg-turbo libpng libwebp libavif \   # gd
      libxml2 \                                 # dom
      imagemagick imagemagick-libs libheif \    # imagick (community)
      libmemcached zlib \                       # memcached (community)
      docker-cli \                              # метрики yii2-cms-info (community)
      supervisor
# community-репозиторий нужен для imagemagick*, libheif, libavif, libmemcached, docker-cli

# Готовые .so из builder-stage'ей
COPY --from=ext-gd        /export/ /tmp/exts/
COPY --from=ext-intl      /export/ /tmp/exts/
COPY --from=ext-zip       /export/ /tmp/exts/
COPY --from=ext-imagick   /export/ /tmp/exts/
COPY --from=ext-xdebug    /export/ /tmp/exts/
COPY --from=ext-apcu      /export/ /tmp/exts/
COPY --from=ext-redis     /export/ /tmp/exts/
COPY --from=ext-memcached /export/ /tmp/exts/
COPY --from=ext-core      /export/ /tmp/exts/

RUN cp /tmp/exts/*.so "$(php-config --extension-dir)/" \
 && docker-php-ext-enable gd intl zip imagick xdebug apcu redis memcached \
      bcmath fileinfo pdo dom pcntl opcache pdo_mysql calendar exif \
 && rm -rf /tmp/exts
#   docker-php-ext-enable сам генерирует conf.d/docker-php-ext-*.ini и сам ставит
#   zend_extension= для opcache/xdebug — порядок загрузки корректный.
```

Остальное в `final` — без изменений, переносится 1:1 из PRECOMPILED (строки 107–165):
supervisor (mkdir + chown + `COPY supervisord.ini` + `CMD`), `COPY --from=composer/composer`,
симлинк `php.ini-development`, `COPY ./common/php/conf.d` и `./development/php/conf.d`,
`RUN echo "date.timezone=..."`, `WORKDIR /app`. Все ваши комментарии сохраняются.

### Опциональные ускорители (можно включить, помечу как «по желанию»)

- `# syntax=docker/dockerfile:1` первой строкой — включает современные возможности BuildKit
  (нужно для cache-mount'ов).
- ccache при компиляции: `RUN --mount=type=cache,target=/root/.ccache CC="ccache gcc" CXX="ccache g++" docker-php-ext-install …`
  — ускоряет повторную компиляцию того же stage при бампе версии.
- apk cache-mount в builder-base вместо `--no-cache` — ускоряет «холодные» пересборки.

Эти три не обязательны для главной цели (изоляции кэша); предлагаю добавить, но согласовать.

## Критические файлы

- **Новый:** `app/docker/development/php-fpm/Dockerfile-Alpine-MULTISTAGE`
- **Правка 1 строки:** `docker-compose.yml:268` — переключить `dockerfile:` на новый файл,
  строку 268 оставить закомментированной как фоллбэк (рядом со строкой 269).
- **Не трогаем:** `Dockerfile-Alpine-PRECOMPILED` (фоллбэк + ваши комментарии),
  `claude/*`, `Dockerfile-Alpine-Gemini`, `Dockerfile-Alpine-COMPILED`.
- Контекст сборки — `app/docker` (как в compose), поэтому относительные `COPY ./common/...`
  и `./development/...` остаются валидными.

## Ожидаемый эффект

- Правка версии/опций одного расширения → пересобирается только его stage (секунды–минуты),
  остальные берутся из кэша.
- «Холодная» сборка: stage'ы идут параллельно (BuildKit), wall-clock стремится к самому
  долгому stage (imagick ~120 с) + final, вместо суммы ~675 с — при наличии нескольких ядер.
- Финальный образ меньше: build-deps (gcc/g++/make/-dev) физически не попадают в `final`.
- Прозрачность сохранена: ручная компиляция, явные версии, явные `--with-*` флаги GD,
  один читаемый список runtime-либ с подписями.

## Проверка (end-to-end)

1. Холодная сборка: `docker compose build php-fpm` — засечь время.
2. Бамп версии для проверки изоляции: поменять, например, `redis-6.3.0` → `redis-6.4.0` в
   stage `ext-redis`, снова `docker compose build php-fpm` — убедиться, что пересобрался
   **только** `ext-redis`, остальные `CACHED`.
3. Запуск и проверка расширений:
   - `docker compose up -d php-fpm`
   - `docker compose exec php-fpm php -m` — все расширения присутствуют.
   - `docker compose exec php-fpm php -r 'var_dump(gd_info());'` — `WebP Support` и
     `AVIF Support` = `true` (подтверждает, что ручные `--with-webp/--with-avif` сработали).
   - `docker compose exec php-fpm php -r 'var_dump(extension_loaded("apcu"));'` → `true`
     (закрывает баг `ApcCache requires PHP apcu extension`, который ловился на OPTIMIZED).
   - `docker compose exec php-fpm php --ri imagick | head` — imagick работает.
4. Поднять приложение и убедиться, что страница админки/кэш ApcCache работают без ошибок.
