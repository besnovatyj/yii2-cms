# =============================================================================
# Dockerfile для Claude Code
# Запуск AI-ассистента в изолированном контейнере
# =============================================================================
FROM debian:bookworm-slim

LABEL maintainer="yii2-cms project"
LABEL description="Claude Code CLI в Docker контейнере"

# Раньше здесь стояло "обновление claude только через пересборку образа" —
# ENV CLAUDE_CODE_DISABLE_AUTO_UPDATE=1. Флаг снят: нативный инсталлер его игнорирует
# (в ~/.claude.json стоит autoUpdatesProtectedForNative=true), апдейт всё равно скачивался
# при каждом старте — и терялся вместе с контейнером, потому что бинарник лежит в ~/.local.
# Теперь ~/.local вынесен в именованный том (см. make/claude.mk): апдейт скачивается один раз
# и переживает сессию, а пересборка образа перестала быть способом обновления claude.
ENV USE_BUILTIN_RIPGREP=0

# Устанавливаем необходимые пакеты (включая зависимости для нативного Claude Code).
# Группы пакетов (perl уже есть в базовом образе debian-slim, поэтому не дублируем):
#   - База/сеть:        git bash curl wget ca-certificates openssh-client
#   - Поиск/анализ:     ripgrep jq file tree less procps  (jq — JSON: composer.json, docker inspect; procps — ps)
#   - Скрипты/анализ:   python3 python3-pip python3-venv  (ad-hoc скрипты; пакеты ставить в venv из-за PEP 668)
#   - Архивы:           unzip zip xz-utils                (распаковка примеров/дампов/логов .zip)
#   - XML:              libxml2-utils                     (xmllint — валидация XML/HTML)
#   - Сетевой дебаг:    iputils-ping dnsutils netcat-openbsd  (проверка доступности mysql/redis/хостов)
#   - Патчи/синхр.:     patch rsync                       (наложить diff; rsync -n --delete как структурный diff деревьев)
#   - Байты/колонки:    xxd bsdextrautils                 (xxd — ловить невидимые символы: nbsp U+00A0 и прочий юникод;
#                                                          bsdextrautils даёт hexdump и column)
#   - Shell-линт:       shellcheck                        (проверка /scripts без запуска)
# ВАЖНО: контейнер запускается под пользователем node без root, доустановить пакеты в рантайме нельзя —
#        всё нужное должно быть в образе. Дополняем список по мере появления потребностей и пересобираем.
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    bash \
    curl \
    wget \
    ca-certificates \
    openssh-client \
    ripgrep \
    jq \
    file \
    tree \
    less \
    procps \
    python3 \
    python3-pip \
    python3-venv \
    unzip \
    zip \
    xz-utils \
    libxml2-utils \
    iputils-ping \
    dnsutils \
    netcat-openbsd \
    patch \
    rsync \
    xxd \
    bsdextrautils \
    shellcheck && \
    rm -rf /var/lib/apt/lists/* && \
    groupadd -r -g 1000 node && useradd -r -u 1000 -g node node

# GitHub CLI (gh): в репозиториях Debian bookworm его нет, ставим .deb из релизов проекта.
# Нужен для работы с GitHub после переезда пакетов besnovatyj/* в отдельные репозитории
# (issues, PR, релизы, теги — без ручного curl к API). Версия берётся последняя на момент сборки.
RUN arch="$(dpkg --print-architecture)" && \
    ver="$(curl -fsSL https://api.github.com/repos/cli/cli/releases/latest | jq -r .tag_name | sed 's/^v//')" && \
    curl -fsSL -o /tmp/gh.deb "https://github.com/cli/cli/releases/download/v${ver}/gh_${ver}_linux_${arch}.deb" && \
    dpkg -i /tmp/gh.deb && \
    rm -f /tmp/gh.deb && \
    gh --version

# PHP CLI 8.4 — ТОЛЬКО для проверки синтаксиса правок (php -l), не для запуска приложения.
# Берём готовый пакет из packages.sury.org: сборки из исходников тут нет, установка занимает секунды.
# Репозиторий Debian bookworm не подходит — в нём PHP 8.2, и синтаксис 8.4 давал бы ложные ошибки.
# Расширения намеренно НЕ ставятся: приложение работает в своём php-контейнере, здесь нужен только парсер.
RUN curl -fsSL https://packages.sury.org/php/apt.gpg -o /usr/share/keyrings/sury-php.gpg && \
    echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ bookworm main" \
      > /etc/apt/sources.list.d/sury-php.list && \
    apt-get update && \
    apt-get install -y --no-install-recommends php8.4-cli && \
    rm -rf /var/lib/apt/lists/* && \
    php -v

WORKDIR /workspace

# Подготавливаем домашнюю директорию для пользователя node
RUN mkdir -p /home/node/.claude /home/node/.local/bin && \
    chown -R node:node /home/node

USER node

# Явно задаём HOME, иначе useradd -r может поставить / вместо /home/node
ENV HOME=/home/node

# Ставим Claude Code через нативный инсталлер (от имени node)
RUN curl -fsSL https://claude.ai/install.sh | bash -s -- latest && \
    ls -la /home/node/.local/bin/claude # Если файла нет, сборка упадет здесь

# Добавляем в PATH
# .local/bin НЕ монтируется при docker run — бинарник выживет
ENV PATH="/home/node/.local/bin:${PATH}"

CMD ["claude"]
