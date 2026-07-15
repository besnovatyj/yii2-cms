# =====================================================================================================================
# php-tools (dev only): phpcs, phpcbf, phpDocumentor, php-cs-fixer, phpstan + composer
# ---------------------------------------------------------------------------------------------------------------------
# Заменяет ранее использовавшийся каталог /app/bin с phar-файлами, скачиваемыми через скрипты /init/install-*.sh.
# Все phar-файлы вшиваются в образ на этапе сборки с проверкой SHA256 — сборка детерминированная и воспроизводимая.
#
# Обновление версии любого инструмента:
#   1. Поднимите соответствующий ARG *_VERSION ниже.
#   2. Выполните `make tools-checksums` — скрипт скачает новые phar и выведет их sha256.
#   3. Подставьте новые значения в ARG *_SHA256.
#   4. Соберите образ: `make tools-build`.
#
# Источники:
#   - https://hub.docker.com/_/php
#   - https://github.com/PHPCSStandards/PHP_CodeSniffer/releases
#   - https://github.com/phpDocumentor/phpDocumentor/releases
#   - https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/releases
#   - https://github.com/phpstan/phpstan/releases
# =====================================================================================================================

FROM php:8.4-cli-alpine3.23

# =====================================================================================================================
# Версии инструментов и их SHA256 (обязательны, без них сборка падает)
# =====================================================================================================================

# PHP_CodeSniffer (https://github.com/PHPCSStandards/PHP_CodeSniffer)
ARG PHPCS_VERSION=3.10.3
ARG PHPCS_SHA256=763c61a526ba2e903878cb5060e7aa49ceba56fa652bd21bef0f48bd5e06a4b8
ARG PHPCBF_SHA256=e446161933b3710a91bcbb9ca6c3e2115b90abb6a34ce4faab7b50e4c931f059

# phpDocumentor (https://github.com/phpDocumentor/phpDocumentor)
ARG PHPDOC_VERSION=3.5.3
ARG PHPDOC_SHA256=9760ac280a10041928a8743354f68692c22f14cd5d05135dfc15e11d3b3c25ea

# PHP-CS-Fixer (https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)
ARG PHP_CS_FIXER_VERSION=3.64.0
ARG PHP_CS_FIXER_SHA256=7d67bbf0635df7239e0a09aef9999f6f91bbc7ef55541ee06aaed727453bfa5e

# PHPStan (https://github.com/phpstan/phpstan)
ARG PHPSTAN_VERSION=2.0.0
ARG PHPSTAN_SHA256=0c0d795a85bb111a60c47ff9667550a52b922e726fc1d0aab1a04a2ce8f43b10

# =====================================================================================================================
# Репозитории Alpine
# =====================================================================================================================

RUN echo "Установка репозиториев ..." \
    && echo http://dl-cdn.alpinelinux.org/alpine/v3.23/main >> /etc/apk/repositories \
    && echo http://dl-cdn.alpinelinux.org/alpine/v3.23/community >> /etc/apk/repositories \
    && apk update

# =====================================================================================================================
# Расширения PHP, нужные phar-инструментам
# ---------------------------------------------------------------------------------------------------------------------
# - intl   — нужен phpDocumentor (форматирование, локали)
# - xsl    — нужен phpDocumentor (трансформации шаблонов)
# - zip    — нужен composer и phpcs/phpDocumentor (работа с архивами)
# Базовые расширения из php:cli-alpine (dom, mbstring, tokenizer, xml*, phar, json) уже включены.
# =====================================================================================================================

RUN echo "Установка intl ..." \
    && apk add --no-cache --virtual .build-deps icu-dev \
    && apk add --no-cache icu-libs icu-data-full \
    && docker-php-ext-install -j"$(nproc)" intl \
    && apk del .build-deps

RUN echo "Установка xsl ..." \
    && apk add --no-cache --virtual .build-deps libxslt-dev \
    && apk add --no-cache libxslt \
    && docker-php-ext-install -j"$(nproc)" xsl \
    && apk del .build-deps

RUN echo "Установка zip ..." \
    && apk add --no-cache --virtual .build-deps libzip-dev \
    && apk add --no-cache libzip \
    && docker-php-ext-install -j"$(nproc)" zip \
    && apk del .build-deps

# =====================================================================================================================
# Утилиты, нужные для самих инструментов
# ---------------------------------------------------------------------------------------------------------------------
# - git    — phpstan/phpcs/php-cs-fixer и composer часто читают git-метаданные проекта
# - bash   — некоторые тулы зовут /bin/bash в подкомандах
# =====================================================================================================================

RUN apk add --no-cache git bash

# =====================================================================================================================
# Composer (берём из официального образа, без скачивания phar отдельно)
# =====================================================================================================================

COPY --from=composer/composer:latest-bin /composer /usr/local/bin/composer

# =====================================================================================================================
# Phar-инструменты: скачивание + проверка SHA256 + размещение в /usr/local/bin
# ---------------------------------------------------------------------------------------------------------------------
# Каждый phar имеет встроенный shebang `#!/usr/bin/env php`, поэтому достаточно chmod +x и вызывать по имени.
# Имена без `.phar` — единый стиль вызова: `phpcs`, `phpcbf`, `phpdoc`, `php-cs-fixer`, `phpstan`.
# =====================================================================================================================

RUN set -eux; \
    echo "Установка PHP_CodeSniffer ${PHPCS_VERSION} (phpcs, phpcbf) ..."; \
    BASE="https://github.com/PHPCSStandards/PHP_CodeSniffer/releases/download/${PHPCS_VERSION}"; \
    wget -qO /tmp/phpcs.phar  "${BASE}/phpcs.phar"; \
    wget -qO /tmp/phpcbf.phar "${BASE}/phpcbf.phar"; \
    echo "${PHPCS_SHA256}  /tmp/phpcs.phar"   | sha256sum -c -; \
    echo "${PHPCBF_SHA256} /tmp/phpcbf.phar"  | sha256sum -c -; \
    mv /tmp/phpcs.phar  /usr/local/bin/phpcs; \
    mv /tmp/phpcbf.phar /usr/local/bin/phpcbf; \
    chmod +x /usr/local/bin/phpcs /usr/local/bin/phpcbf

RUN set -eux; \
    echo "Установка phpDocumentor ${PHPDOC_VERSION} ..."; \
    wget -qO /tmp/phpdoc.phar "https://github.com/phpDocumentor/phpDocumentor/releases/download/v${PHPDOC_VERSION}/phpDocumentor.phar"; \
    echo "${PHPDOC_SHA256}  /tmp/phpdoc.phar" | sha256sum -c -; \
    mv /tmp/phpdoc.phar /usr/local/bin/phpdoc; \
    chmod +x /usr/local/bin/phpdoc

RUN set -eux; \
    echo "Установка PHP-CS-Fixer ${PHP_CS_FIXER_VERSION} ..."; \
    wget -qO /tmp/php-cs-fixer.phar "https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/releases/download/v${PHP_CS_FIXER_VERSION}/php-cs-fixer.phar"; \
    echo "${PHP_CS_FIXER_SHA256}  /tmp/php-cs-fixer.phar" | sha256sum -c -; \
    mv /tmp/php-cs-fixer.phar /usr/local/bin/php-cs-fixer; \
    chmod +x /usr/local/bin/php-cs-fixer

RUN set -eux; \
    echo "Установка PHPStan ${PHPSTAN_VERSION} ..."; \
    wget -qO /tmp/phpstan.phar "https://github.com/phpstan/phpstan/releases/download/${PHPSTAN_VERSION}/phpstan.phar"; \
    echo "${PHPSTAN_SHA256}  /tmp/phpstan.phar" | sha256sum -c -; \
    mv /tmp/phpstan.phar /usr/local/bin/phpstan; \
    chmod +x /usr/local/bin/phpstan

# =====================================================================================================================
# Конфигурация PHP CLI ($PHP_INI_DIR === /usr/local/etc/php/)
# ---------------------------------------------------------------------------------------------------------------------
# Берём php.ini-development как базу — для статанализа и линтеров нужны display_errors и подробные ошибки.
# Memory limit повышаем — phpstan и phpDocumentor любят память.
# =====================================================================================================================

RUN ln -sf "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN printf "memory_limit = -1\n" > "$PHP_INI_DIR/conf.d/zz-tools.ini"

# =====================================================================================================================
# Рабочая директория и entrypoint
# ---------------------------------------------------------------------------------------------------------------------
# WORKDIR /app — проект монтируется как /app (см. docker-compose.yml).
# ENTRYPOINT сбрасываем — стандартный docker-php-entrypoint при первом аргументе-флаге автоматически подставляет `php`,
# что ломает вызовы вида `phpcs --version` (превратит в `php --version`).
# =====================================================================================================================

WORKDIR /app

ENTRYPOINT []
CMD ["sh"]
