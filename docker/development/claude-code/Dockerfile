# =============================================================================
# Dockerfile для Claude Code
# Запуск AI-ассистента в изолированном контейнере
# =============================================================================
FROM debian:bookworm-slim

LABEL maintainer="yii2-cms project"
LABEL description="Claude Code CLI в Docker контейнере"

# Обновление claude только через пересборку образа
ENV CLAUDE_CODE_DISABLE_AUTO_UPDATE=1
ENV USE_BUILTIN_RIPGREP=0

# Устанавливаем необходимые пакеты (включая зависимости для нативного Claude Code).
# Группы пакетов (perl уже есть в базовом образе debian-slim, поэтому не дублируем):
#   - База/сеть:        git bash curl wget ca-certificates openssh-client
#   - Поиск/анализ:     ripgrep jq file tree less procps  (jq — JSON: composer.json, docker inspect; procps — ps)
#   - Скрипты/анализ:   python3 python3-pip python3-venv  (ad-hoc скрипты; пакеты ставить в venv из-за PEP 668)
#   - Архивы:           unzip zip xz-utils                (распаковка примеров/дампов/логов .zip)
#   - XML:              libxml2-utils                     (xmllint — валидация XML/HTML)
#   - Сетевой дебаг:    iputils-ping dnsutils netcat-openbsd  (проверка доступности mysql/redis/хостов)
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
    netcat-openbsd && \
    rm -rf /var/lib/apt/lists/* && \
    groupadd -r -g 1000 node && useradd -r -u 1000 -g node node

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
