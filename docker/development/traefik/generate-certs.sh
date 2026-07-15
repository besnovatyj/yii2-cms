#!/bin/bash
# =============================================================================
# Универсальный скрипт для генерации wildcard SSL сертификата
# для локальной разработки с Traefik
# =============================================================================
set -euo pipefail

# -------------------------------- Defaults -----------------------------------
DEFAULT_DOMAIN="docker.localhost"
DEFAULT_OUTPUT_DIR="."
DEFAULT_CERT_NAME="local"
DEFAULT_DAYS=365

# -------------------------------- Usage --------------------------------------
usage() {
    cat <<EOF
Использование: $(basename "$0") [OPTIONS]

Генерация self-signed wildcard SSL сертификата для локальной разработки.

Опции:
  -d, --domain DOMAIN       Базовый домен (по умолчанию: $DEFAULT_DOMAIN)
  -p, --project PROJECT     Имя проекта для субдомена (опционально)
                            Если указано, добавляются: *.PROJECT.DOMAIN, PROJECT.DOMAIN
  -o, --output DIR          Директория для сертификатов (по умолчанию: $DEFAULT_OUTPUT_DIR)
  -n, --name NAME           Имя файлов сертификата без расширения (по умолчанию: $DEFAULT_CERT_NAME)
  -e, --days DAYS           Срок действия в днях (по умолчанию: $DEFAULT_DAYS)
  -h, --help                Показать эту справку

Примеры:
  $(basename "$0")
      → Создаст local.crt и local.key для *.docker.localhost

  $(basename "$0") -d docker.localhost -p yii2-cms -o ./certs
      → Создаст ./certs/local.crt для *.docker.localhost и *.yii2-cms.docker.localhost

  $(basename "$0") -d loc -p myproject -o /path/to/traefik/certs -n myproject
      → Создаст /path/to/traefik/certs/myproject.crt для *.loc и *.myproject.loc

EOF
    exit 0
}

# -------------------------------- Parse args ---------------------------------
DOMAIN="$DEFAULT_DOMAIN"
PROJECT=""
OUTPUT_DIR="$DEFAULT_OUTPUT_DIR"
CERT_NAME="$DEFAULT_CERT_NAME"
DAYS="$DEFAULT_DAYS"

while [[ $# -gt 0 ]]; do
    case $1 in
        -d|--domain)
            DOMAIN="$2"
            shift 2
            ;;
        -p|--project)
            PROJECT="$2"
            shift 2
            ;;
        -o|--output)
            OUTPUT_DIR="$2"
            shift 2
            ;;
        -n|--name)
            CERT_NAME="$2"
            shift 2
            ;;
        -e|--days)
            DAYS="$2"
            shift 2
            ;;
        -h|--help)
            usage
            ;;
        *)
            echo "❌ Неизвестный аргумент: $1" >&2
            echo "Используй -h для справки" >&2
            exit 1
            ;;
    esac
done

# -------------------------------- Validation ---------------------------------
if ! command -v openssl &> /dev/null; then
    echo "❌ OpenSSL не найден. Установи его и повтори." >&2
    exit 1
fi

# -------------------------------- Prepare ------------------------------------
# Создаём директорию если не существует
mkdir -p "$OUTPUT_DIR"

KEY_FILE="${OUTPUT_DIR}/${CERT_NAME}.key"
CRT_FILE="${OUTPUT_DIR}/${CERT_NAME}.crt"

# Формируем список SAN (Subject Alternative Names)
SAN_LIST="DNS:*.${DOMAIN},DNS:${DOMAIN}"

if [[ -n "$PROJECT" ]]; then
    SAN_LIST="${SAN_LIST},DNS:*.${PROJECT}.${DOMAIN},DNS:${PROJECT}.${DOMAIN}"
fi

# -------------------------------- Generate -----------------------------------
echo "🔐 Генерация SSL сертификата..."
echo ""
echo "   Домен:      *.$DOMAIN, $DOMAIN"
[[ -n "$PROJECT" ]] && echo "   Проект:     *.$PROJECT.$DOMAIN, $PROJECT.$DOMAIN"
echo "   Выходной путь: $OUTPUT_DIR/"
echo "   Файлы:      ${CERT_NAME}.key, ${CERT_NAME}.crt"
echo "   Срок:       $DAYS дней"
echo ""

openssl req -x509 -newkey rsa:2048 -nodes \
    -keyout "$KEY_FILE" \
    -out "$CRT_FILE" \
    -days "$DAYS" \
    -subj "/CN=*.${DOMAIN}" \
    -addext "subjectAltName=${SAN_LIST}" \
    2>/dev/null

# -------------------------------- Result -------------------------------------
echo "✅ Сертификаты созданы:"
echo "   - $CRT_FILE"
echo "   - $KEY_FILE"
echo ""
echo "📋 SAN (Subject Alternative Names):"
for san in ${SAN_LIST//,/ }; do
    echo "   - ${san#DNS:}"
done
echo ""
echo "📝 Для WSL2/Windows (чтобы браузер доверял сертификату):"
echo ""
echo "   Вариант 1 — Вручную:"
echo "   1. Открой $CRT_FILE в проводнике Windows"
echo "   2. Дважды кликни → 'Установить сертификат'"
echo "   3. Выбери 'Локальный компьютер' → 'Далее'"
echo "   4. 'Поместить в хранилище' → 'Обзор' → 'Доверенные корневые центры сертификации'"
echo "   5. Перезапусти браузер"
echo ""
echo "   Вариант 2 — PowerShell (от Администратора):"
echo "   Import-Certificate -FilePath '\\\\wsl.localhost\\Ubuntu${CRT_FILE}' -CertStoreLocation Cert:\\LocalMachine\\Root"
echo ""
echo "   Вариант 3 — mkcert (рекомендуется, если ещё не установлен):"
echo "   choco install mkcert && mkcert -install"
echo ""
