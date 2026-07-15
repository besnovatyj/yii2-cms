#!/bin/sh
set -e

# !!!
# Нет пока никаких ошибок без этого файла, поэтому файл больше не нужен в принципе, оставляю на всякий случай, вдруг что-то поломается еще
# !!!

#===========================
# Файл нужен только при разработке, когда монтирование volumes затирает все права установленные в Dockerfile
# entrypoint.sh выполняется ПОСЛЕ монтирования volumes, поэтому chown/chmod из этого файла работают на реальных данных.
# Для production файлы копируются, а не монтируются, поэтому там этот файл не нужен, там работает Dockerfile.
#===========================

# ============ Хак для host.docker.internal больше не требуется ============
HOST_DOMAIN="host.docker.internal"

if ! ping -q -c1 $HOST_DOMAIN > /dev/null 2>&1
then
  HOST_IP=$(ip route | awk 'NR==1 {print $3}')
  # shellcheck disable=SC2039
  echo -e "$HOST_IP\t$HOST_DOMAIN" >> /etc/hosts
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

exec "$@"
#===========================

#===========================
# Установка прав на директории нужна при разработке, так как
#  - Volumes монтируются с правами хоста (твоего Windows/WSL пользователя)
#  - PHP-FPM работает от www-data
#  - Без этого будут ошибки записи в `assets/`, `static/`, `var/`
# !!!
# Нет пока никаких ошибок, поэтому файл больше не нужен в принципе, оставляю на всякий случай, вдруг что-то поломается еще
# !!!
#===========================

# В /usr/local/bin/docker-php-entrypoint
# Проверяем и создаём директорию runtime
# mkdir -p /app/common/runtime

chown -R www-data:www-data /app/static
chmod -R 775 /app/static

chown -R www-data:www-data /app/backend/pub/assets
chmod -R 775 /app/backend/pub/assets

chown -R www-data:www-data /app/frontend/pub/assets
chmod -R 775 /app/frontend/pub/assets

chown -R www-data:www-data /app/tmp
chmod -R 775 /app/tmp

chown -R www-data:www-data /app/var
chmod -R 775 /app/var

# Запускаем PHP-FPM
exec "$@"
