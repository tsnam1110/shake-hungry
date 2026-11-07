#!/usr/bin/env bash
# Production: MySQL rieng, khong publish 3306; cau hinh rieng .env.production.
set -Eeuo pipefail
cd -- "$(dirname -- "${BASH_SOURCE[0]}")"
umask 077
die() { printf 'LOI: %s\n' "$*" >&2; exit 1; }
for command_name in docker openssl curl flock; do
    command -v "$command_name" >/dev/null || die "Thieu $command_name."
done
docker compose version >/dev/null
docker info >/dev/null 2>&1 || die 'Docker chua chay hoac tai khoan chua co quyen.'
[[ $# -eq 0 ]] || die 'Cach chay: bash deploy.sh'
docker compose up --help | grep -q -- '--wait-timeout' \
    || die 'Can cap nhat Docker Compose: thieu ho tro --wait-timeout.'
# Tranh hai lan deploy sinh env hoac thay container cung luc.
exec 9>.deploy.lock
flock -n 9 || die 'Mot tien trinh deploy khac dang chay trong thu muc nay.'
read_env() {
    sed -n "s/^$1=//p" "$2" | head -n 1 | tr -d '\r' \
        | sed 's/[[:space:]]*#.*$//;s/[[:space:]]*$//;s/^"//;s/"$//'
}

# Khong source file env: gia tri cau hinh khong duoc thuc thi nhu shell code.
if [[ ! -f .env.production ]]; then
    read -r -p 'URL HTTPS (vi du https://lacdoi.tsnam.name.vn): ' deploy_url
    [[ "$deploy_url" =~ ^https://[a-zA-Z0-9.-]+(:[0-9]+)?/?$ ]] || die 'URL HTTPS khong hop le.'
    deploy_key=''
    if [[ -f .env.docker ]]; then
        deploy_key=$(read_env APP_KEY .env.docker)
        [[ "$deploy_key" =~ ^base64:[A-Za-z0-9+/]{43}=$ ]] || die 'Khong doc duoc APP_KEY cu; can giu lai key cu trong .env.production truoc khi deploy.'
    fi
    [[ -n "$deploy_key" ]] || deploy_key="base64:$(openssl rand -base64 32)"
    read -r -p 'Email quan tri: ' deploy_email
    [[ "$deploy_email" =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]] || die 'Email khong hop le.'
    # Hex tranh loi noi suy $, #, dau nhay khi Compose doc mat khau.
    deploy_password=$(openssl rand -hex 24)
    deploy_root_password=$(openssl rand -hex 32)
    deploy_admin_password=$(openssl rand -hex 16)
    deploy_tmp=$(mktemp .env.production.XXXXXX)
    trap 'rm -f -- "${deploy_tmp:-}"' EXIT
    cat > "$deploy_tmp" <<EOF
APP_NAME="Lắc Đói"
APP_ENV=production
APP_DEBUG=false
APP_KEY=$deploy_key
APP_URL=${deploy_url%/}
LOG_CHANNEL=stderr
LOG_LEVEL=warning
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=shake_hungry
DB_USERNAME=lakdoi
DB_PASSWORD=$deploy_password
DB_ROOT_PASSWORD=$deploy_root_password
DB_COLLATION=utf8mb4_vi_0900_ai_ci
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
CACHE_STORE=database
ADMIN_NAME=Admin
ADMIN_EMAIL=$deploy_email
ADMIN_PASSWORD=$deploy_admin_password
ADMIN_QUICK_LOGIN=false
EOF
    mv -- "$deploy_tmp" .env.production
    trap - EXIT
    printf 'Da tao .env.production (quyen 600). Thong tin admin luu trong file nay.\n'
fi
chmod 600 .env.production
for deploy_setting in 'DB_HOST=db' 'DB_USERNAME=lakdoi' 'DB_DATABASE=shake_hungry'; do
    grep -qxF "$deploy_setting" .env.production \
        || die ".env.production can co $deploy_setting cho MySQL rieng; xem docs/10-deployment/DEPLOY.md."
done
grep -Eq '^APP_KEY=base64:[A-Za-z0-9+/]{43}=$' .env.production \
    || die 'APP_KEY trong .env.production thieu hoac khong hop le; khong tu doi key cu.'
deploy_url=$(read_env APP_URL .env.production)
[[ "$deploy_url" =~ ^https://[a-zA-Z0-9.-]+(:[0-9]+)?/?$ ]] \
    || die 'APP_URL trong .env.production can la URL HTTPS hop le.'
# Bien export cua shell uu tien hon --env-file o buoc noi suy Compose.
# Loai bo chung de DB va PHP luon doc cung mat khau trong .env.production.
unset DB_PASSWORD DB_ROOT_PASSWORD COMPOSE_PROFILES COMPOSE_FILE COMPOSE_PROJECT_NAME
dc=(docker compose --project-name shake-hungry --env-file .env.production -f docker-compose.deploy.yml)
"${dc[@]}" config --quiet
printf 'Build image truoc khi thay container dang chay...\n'
"${dc[@]}" build app web
printf 'Khoi dong MySQL rieng va cho ung dung (toi da 15 phut)...\n'
# Giu DB neu khong doi cau hinh; tao lai web cung app de khong giu IP FPM cu.
if ! "${dc[@]}" up -d --wait --wait-timeout 240 db \
    || ! "${dc[@]}" up -d --no-deps --force-recreate --wait --wait-timeout 900 app \
    || ! "${dc[@]}" up -d --no-deps --force-recreate --wait --wait-timeout 90 web; then
    "${dc[@]}" ps -a
    printf 'Deploy chua thanh cong. Xem log:\n'
    printf 'docker compose --env-file .env.production -f docker-compose.deploy.yml logs --tail=100 db app web\n'
    exit 1
fi
for deploy_path in /up / /admin; do
    deploy_http=$(curl --silent --show-error --output /dev/null --write-out '%{http_code}' --max-time 15 "http://127.0.0.1:1113$deploy_path") \
        || die "Khong ket noi duoc HTTP $deploy_path tren cong 1113."
    [[ "$deploy_http" == 200 ]] || die "HTTP $deploy_path that bai (ma $deploy_http)."
done
printf '\nUng dung da san sang tai 127.0.0.1:1113. Proxy HTTPS cua host can tro ve dia chi nay.\n'
printf 'Cap nhat lan sau: bash deploy.sh\n'
