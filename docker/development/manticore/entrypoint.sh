#!/bin/sh
# =============================================================================
# Первичное создание учётных записей демона поиска.
#
# У Manticore есть встроенная авторизация (с версии 27.1.5), но включённая настройка `auth`
# сама по себе учёток не создаёт: первого администратора заводит отдельный режим запуска
# `searchd --auth-non-interactive`, и он обращается к УЖЕ ЗАПУЩЕННОМУ демону (без него —
# «FATAL: pid file ... does not exist, run daemon first»). Поэтому порядок такой: сначала
# штатный запуск демона, а создание учёток идёт параллельно и ждёт, пока демон ответит.
#
# Всё это выполняется один раз на том с данными и отмечается маркером: повторный запуск
# создания администратора завершается ошибкой, а не «ничего не делает».
#
# Ошибки настройки контейнер не роняют. Демон должен работать в любом случае: с ним можно
# разобраться руками (`make manticore-cli`), а падение контейнера в цикле перезапуска не
# оставляет даже такой возможности.
#
# Пароли читаются из Docker Secrets. Кавычек и обратных слэшей в них быть не должно: пароль
# подставляется в SQL-команду создания пользователя. `make secrets-init-prod` генерирует
# base64url — такие пароли безопасны.
# =============================================================================
set -eu

CONF="/etc/manticoresearch/manticore.conf.sh"
DATA_DIR="/var/lib/manticore"
MARKER="$DATA_DIR/.bescms-auth-initialized"
GOSU="$(command -v gosu || true)"

# Сколько ждём готовности демона: попыток и пауза между ними в секундах.
ATTEMPTS=60
DELAY=2

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

sql_as() {
    sql_user="$1"
    sql_password="$2"
    shift 2

    MYSQL_PWD="$sql_password" mysql -h 127.0.0.1 -P 9306 -u "$sql_user" "$@"
}

# Создать администратора, дождавшись готовности демона.
create_admin() {
    attempt=0

    while :; do
        # Уже заведён (например, маркер потерян вместе с контейнером, а том остался) —
        # заводить второй раз не нужно и нельзя.
        if sql_as "$admin_user" "$admin_password" -e 'SHOW TABLES' >/dev/null 2>&1; then
            echo "manticore-init: администратор «${admin_user}» уже существует."
            return 0
        fi

        if printf '%s\n%s\n%s\n' "$admin_user" "$admin_password" "$admin_password" \
            | as_manticore searchd --config "$CONF" --auth-non-interactive >/dev/null 2>&1
        then
            echo "manticore-init: администратор «${admin_user}» создан."
            return 0
        fi

        attempt=$((attempt + 1))
        if [ "$attempt" -ge "$ATTEMPTS" ]; then
            echo "manticore-init: демон не принял создание администратора «${admin_user}»." >&2
            return 1
        fi

        sleep "$DELAY"
    done
}

# Создать учётную запись приложения: права только на работу с индексом, без администрирования
# и репликации.
create_app_user() {
    if sql_as "$app_user" "$app_password" -e 'SHOW TABLES' >/dev/null 2>&1; then
        echo "manticore-init: учётная запись приложения «${app_user}» уже существует."
        return 0
    fi

    sql_as "$admin_user" "$admin_password" <<SQL
CREATE USER '${app_user}' IDENTIFIED BY '${app_password}';
GRANT read ON * TO '${app_user}';
GRANT write ON * TO '${app_user}';
GRANT schema ON * TO '${app_user}';
SQL

    if ! sql_as "$app_user" "$app_password" -e 'SHOW TABLES' >/dev/null 2>&1; then
        echo "manticore-init: учётная запись «${app_user}» создана, но подключиться под ней не удалось." >&2
        return 1
    fi

    echo "manticore-init: учётная запись приложения «${app_user}» создана."
}

init_auth() {
    admin_user="${MANTICORE_ROOT_USER:-admin}"
    admin_password="$(secret MANTICORE_ROOT_PASSWORD)"
    app_user="$(secret MANTICORE_USER)"
    app_password="$(secret MANTICORE_PASSWORD)"

    if [ -z "$admin_password" ]; then
        echo "manticore-init: нет секрета MANTICORE_ROOT_PASSWORD — учётные записи не созданы." >&2
        return 1
    fi

    create_admin || return 1

    if [ -z "$app_user" ] || [ -z "$app_password" ]; then
        echo "manticore-init: MANTICORE_USER или MANTICORE_PASSWORD не заданы — учётная запись приложения не создана." >&2
        return 1
    fi

    create_app_user || return 1

    as_manticore touch "$MARKER"
}

if [ "${searchd_auth:-0}" = "1" ] && [ ! -f "$MARKER" ]; then
    echo "manticore-init: первый запуск с авторизацией, учётные записи будут созданы после старта демона."
    # Фоном: демон запускается ниже и должен получить управление немедленно.
    { init_auth || true; } &
fi

exec docker-entrypoint.sh "$@"
