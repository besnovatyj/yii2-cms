# MySQL 8.4 LTS — единая версия для dev и prod (поддержка до 2032).
# native_password в 8.4 отключён; используется caching_sha2_password.
FROM mysql:8.4

# Конфиг применяется через !includedir /etc/mysql/conf.d/ (есть в официальном образе).
# Контекст сборки = app/docker (см. docker-compose.yml: context: app/docker).
# MySQL игнорирует world-writable конфиги, поэтому фиксируем владельца и права.
COPY ./development/mysql/conf.d/ /etc/mysql/conf.d/
RUN chown -R root:root /etc/mysql/conf.d && chmod 0644 /etc/mysql/conf.d/*.cnf

WORKDIR /app
