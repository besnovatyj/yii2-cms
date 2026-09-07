#!/bin/sh
# =============================================================================
# Первичная настройка авторизации демона поиска.
#
# У Manticore есть встроенная авторизация (с версии 27.1.5), но включённая настройка `auth`
# сама по себе учётных записей не создаёт: первого администратора заводит отдельный режим
# запуска `searchd --auth-non-interactive`, и делается это ОДИН раз, до старта демона.
# Пока учёток нет, подключиться нельзя вообще — поэтому создание выполняется здесь, при первом
# запуске контейнера, как это делает официальный образ MySQL.
#
# Порядок:
#   1. создаётся администратор — им пользуются `make manticore-*` и сопровождение;
#   2. демон поднимается на время, чтобы создать учётную запись приложения с правами только на
#      работу с индексом (без прав администрирования и репликации), и останавливается;
#   3. ставится отметка, чтобы при следующих запусках ничего этого не повторялось: повторный
#      запуск создания администратора завершается ошибкой, а не «ничего не делает».
#
# Пароли читаются из Docker Secrets. Кавычек и обратных слэшей в них быть не должно: пароль
# подставляется в SQL-команду создания пользователя. `make secrets-init-prod` генерирует
# base64 — такие пароли безопасны.
# =============================================================================
set -eu

CONF="/etc/manticoresearch/manticore.conf.sh"
DATA_DIR="/var/lib/manticore"
MARKER="$DATA_DIR/.bescms-auth-initialized"
GOSU="$(command -v gosu || true)"

# Штатный запуск демона в образе выполняется от пользователя manticore; всё, что создаёт файлы
# в каталоге данных, должно работать от него же, иначе демон потом не сможет их перезаписать.
as_manticore() {
    if [ -n "$GOSU" ]; then
        "$GOSU" manticore "$@"
    else
        "$@"
    fi
}

# Секрет: файл Docker Secrets, иначе одноимённая переменная окружения.
secret() {
    if [ -r "/run/secrets/$1" ]; then
        tr -d '\r\n' < "/run/secrets/$1"
    else
        printenv "$1" || true
    fi
}

admin_sql() {
    sql_password="$1"
    sql_user="$2"
    shift 2

    MYSQL_PWD="$sql_password" mysql -h 127.0.0.1 -P 9306 -u "$sql_user" "$@"
}

if [ "${searchd_auth:-0}" = "1" ] && [ ! -f "$MARKER" ]; then
    admin_user="${MANTICORE_ROOT_USER:-admin}"
    admin_password="$(secret MANTICORE_ROOT_PASSWORD)"
    app_user="${MANTICORE_USER:-}"
    app_password="$(secret MANTICORE_PASSWORD)"

    if [ -z "$admin_password" ]; then
        echo "manticore-init: нет секрета MANTICORE_ROOT_PASSWORD — включать авторизацию нечем." >&2
        exit 1
    fi

    echo "manticore-init: первый запуск, создаётся администратор «${admin_user}»."
    printf '%s\n%s\n%s\n' "$admin_user" "$admin_password" "$admin_password" \
        | as_manticore searchd --config "$CONF" --auth-non-interactive

    if [ -n "$app_user" ] && [ -n "$app_password" ]; then
        echo "manticore-init: создаётся учётная запись приложения «${app_user}»."
        as_manticore searchd --config "$CONF"

        attempt=0
        until admin_sql "$admin_password" "$admin_user" -e 'SHOW TABLES' >/dev/null 2>&1; do
            attempt=$((attempt + 1))
            if [ "$attempt" -ge 30 ]; then
                echo "manticore-init: демон не ответил за 30 секунд, настройка прервана." >&2
                exit 1
            fi
            sleep 1
        done

        # Права: читать, писать и управлять таблицами индекса. Администрирование и репликация
        # приложению не нужны — их у этой учётной записи нет.
        admin_sql "$admin_password" "$admin_user" <<SQL
CREATE USER '${app_user}' IDENTIFIED BY '${app_password}';
GRANT read ON * TO '${app_user}';
GRANT write ON * TO '${app_user}';
GRANT schema ON * TO '${app_user}';
SQL

        as_manticore searchd --config "$CONF" --stopwait
    else
        echo "manticore-init: MANTICORE_USER или секрет MANTICORE_PASSWORD не заданы — учётная запись приложения не создана." >&2
    fi

    as_manticore touch "$MARKER"
fi

exec docker-entrypoint.sh "$@"
