# .......Development Stage.......
# specify the node base image with your desired version node:<version>
FROM node:24-alpine3.22 AS development

# определение переменных среды
ENV HOME=/home/node/app

# Обновление npm до последней версии глобально
RUN npm i npm@latest -g

# создание папки приложения и назначение прав пользователю node (falg `-p` = with subdirs)
#RUN mkdir -p $HOME && chown -R node:node $HOME

# установка рабочего каталога
WORKDIR $HOME

# .......Production Stage.......
FROM node:20-alpine AS production
# Define an argument for the Node environment with a default value of "production"
ARG NODE_ENV=production
# Set the environment variable for the Node environment
ENV NODE_ENV=${NODE_ENV}
# Set the working directory in the container
WORKDIR /app
# Copy package.json and package-lock.json to the working directory
COPY package.json package-lock.json ./
# Install only production dependencies
RUN npm install --only=production
# Copy the entire application code into the container
COPY . .
# Copy the build artifacts from the development stage to the production stage
COPY --from=development /app/build ./build
# Default command to run when the container starts in production mode
CMD npm run prod
