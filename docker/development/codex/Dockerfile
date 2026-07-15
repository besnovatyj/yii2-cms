# =============================================================================
# Dockerfile для OpenAI Codex CLI
# Запуск AI-ассистента в изолированном контейнере
# =============================================================================
FROM node:22-bookworm-slim

LABEL maintainer="yii2-cms project"
LABEL description="OpenAI Codex CLI в Docker контейнере"

ENV CODEX_HOME=/home/node/.codex
ENV HOME=/home/node
ENV NPM_CONFIG_PREFIX=/home/node/.npm-global
ENV PATH="/home/node/.npm-global/bin:${PATH}"

# Пакеты для работы Codex с репозиторием и диагностики. bubblewrap нужен для более надёжного Linux sandbox режима Codex.
# Основная установка ниже идёт через официальный npm-пакет @openai/codex, потому что standalone installer сейчас нестабилен для Linux musl archive.
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    bash \
    curl \
    ca-certificates \
    openssh-client \
    ripgrep \
    less \
    make \
    tini \
    bubblewrap \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /workspace

RUN mkdir -p /home/node/.codex /home/node/.npm-global /workspace && \
    chown -R node:node /home/node /workspace

USER node

RUN npm install -g @openai/codex && \
    codex --version

ENTRYPOINT ["tini", "--"]
CMD ["codex"]
