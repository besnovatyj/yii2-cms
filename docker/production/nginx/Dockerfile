FROM nginx:1.29.0-alpine3.22-slim

# https://github.com/docker-library/docs/tree/master/nginx#using-environment-variables-in-nginx-configuration-new-in-119
COPY ./production/nginx/templates /etc/nginx/templates

WORKDIR /app
