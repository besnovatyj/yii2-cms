FROM node:23.8.0-alpine3.23

USER node

WORKDIR /home/node/app

# Копируем package.json и package-lock.json для кеширования слоёв
COPY --chown=node:node package*.json ./

# Устанавливаем зависимости для production
RUN npm ci --only=production

# Копируем остальные файлы
COPY --chown=node:node . .

# По умолчанию production build
ENV NODE_ENV=production

# Билдим проект
RUN npm run build

# Запускаем production сервер
CMD ["npm", "run", "start"]
