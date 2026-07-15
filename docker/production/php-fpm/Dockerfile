FROM php:8.4-fpm-alpine3.23

# Основной репозиторий Alpine
RUN echo "Установка репозиториев ..." \
    && echo http://dl-cdn.alpinelinux.org/alpine/v3.23/main >> /etc/apk/repositories \
    && apk update

# Устанавливаем supervisor
RUN echo "Установка supervisor ..." \
    && apk add --no-cache supervisor \
    && mkdir -p /etc/supervisor.d/ /var/log/supervisor /run/supervisor \
    && chown -R www-data:www-data /var/log/supervisor /run/supervisor \
    && chmod -R 775 /var/log/supervisor /run/supervisor

# Копируем конфигурацию
COPY docker/common/supervisor/supervisord.ini /etc/supervisord.conf

# Запускаем Supervisor
CMD ["supervisord", "-c", "/etc/supervisord.conf"]

# ======================================================================================================================
# Прочее
# ======================================================================================================================

RUN apk add unzip

WORKDIR /app

# TODO - Нужен ли composer на production? Сервер то слабенький.
# Можно запускать composer через временный контейнер при деплое:
# `docker run --rm -v $(pwd):/app composer install`

# Для кеширования слоёв. Чтобы при изменении исходного кода, vendor был закеширован.
COPY ./composer.json ./composer.lock ./

# Устанавливаем composer зависимости
RUN composer install --no-dev --prefer-dist --optimize-autoloader \
    && rm -rf /root/.composer/cache

COPY ./ ./

# Что делает эта команда:
# Устанавливает владельца root для всего содержимого /app
# Устанавливает права 555 (чтение+запуск) для всех директорий
# Устанавливает права 444 (только чтение) для всех файлов
# Для каждого из указанных каталогов (которые должны быть доступны для записи):
# Создает директорию (если ее нет)
# Устанавливает владельца www-data
# Устанавливает права 755 (чтение+запись+запуск для владельца, чтение+запуск для остальных)
RUN chown -R root:root /app \
    && find /app -type d -exec chmod 555 {} \; \
    && find /app -type f -exec chmod 444 {} \; \
    && for dir in /app/backend/pub/assets /app/frontend/pub/assets /app/common/runtime /app/static; do \
         mkdir -p "$dir" && chown -R www-data:www-data "$dir" && chmod 755 "$dir"; \
       done
