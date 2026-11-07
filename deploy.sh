#!/usr/bin/env bash
# Production: MySQL riêng, không publish 3306; cấu hình riêng .env.production.
set -Eeuo pipefail
cd -- "$(dirname -- "${BASH_SOURCE[0]}")"
umask 077
die() { printf 'LỖI: %s\n' "$*" >&2; exit 1; }
for command_name in docker openssl curl flock; do
    command -v "$command_name" >/dev/null || die "Thiếu $command_name."
done
docker compose version >/dev/null
docker info >/dev/null 2>&1 || die 'Docker chưa chạy hoặc tài khoản chưa có quyền.'
[[ $# -eq 0 ]] || die 'Cách chạy: bash deploy.sh'
docker compose up --help | grep -q -- '--wait-timeout' \
    || die 'Cần cập nhật Docker Compose: thiếu hỗ trợ --wait-timeout.'
# Tránh hai lần deploy sinh env hoặc thay container cùng lúc.
exec 9>.deploy.lock
flock -n 9 || die 'Một tiến trình deploy khác đang chạy trong thư mục này.'
read_env() {
    sed -n "s/^$1=//p" "$2" | head -n 1 | tr -d '\r' \
        | sed 's/[[:space:]]*#.*$//;s/[[:space:]]*$//;s/^"//;s/"$//'
}

# Không source file env: giá trị cấu hình không được thực thi như shell code.
if [[ ! -f .env.production ]]; then
    read -r -p 'URL HTTPS (ví dụ https://lacdoi.tsnam.name.vn): ' deploy_url
    [[ "$deploy_url" =~ ^https://[a-zA-Z0-9.-]+(:[0-9]+)?/?$ ]] || die 'URL HTTPS không hợp lệ.'
    deploy_key=''
    if [[ -f .env.docker ]]; then
        deploy_key=$(read_env APP_KEY .env.docker)
        [[ "$deploy_key" =~ ^base64:[A-Za-z0-9+/]{43}=$ ]] || die 'Không đọc được APP_KEY cũ; cần giữ lại key cũ trong .env.production trước khi deploy.'
    fi
    [[ -n "$deploy_key" ]] || deploy_key="base64:$(openssl rand -base64 32)"
    read -r -p 'Email quản trị: ' deploy_email
    [[ "$deploy_email" =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]] || die 'Email không hợp lệ.'
    # Hex tránh lỗi nội suy $, #, dấu nháy khi Compose đọc mật khẩu.
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
    printf 'Đã tạo .env.production (quyền 600). Thông tin admin lưu trong file này.\n'
fi
chmod 600 .env.production
for deploy_setting in 'DB_HOST=db' 'DB_USERNAME=lakdoi' 'DB_DATABASE=shake_hungry'; do
    grep -qxF "$deploy_setting" .env.production \
        || die ".env.production cần có $deploy_setting cho MySQL riêng; xem docs/10-deployment/DEPLOY.md."
done
grep -Eq '^APP_KEY=base64:[A-Za-z0-9+/]{43}=$' .env.production \
    || die 'APP_KEY trong .env.production thiếu hoặc không hợp lệ; không tự đổi key cũ.'
deploy_url=$(read_env APP_URL .env.production)
[[ "$deploy_url" =~ ^https://[a-zA-Z0-9.-]+(:[0-9]+)?/?$ ]] \
    || die 'APP_URL trong .env.production cần là URL HTTPS hợp lệ.'
# Biến export của shell ưu tiên hơn --env-file ở bước nội suy Compose.
# Loại bỏ chúng để DB và PHP luôn đọc cùng mật khẩu trong .env.production.
unset DB_PASSWORD DB_ROOT_PASSWORD COMPOSE_PROFILES COMPOSE_FILE COMPOSE_PROJECT_NAME
dc=(docker compose --project-name shake-hungry --env-file .env.production -f docker-compose.deploy.yml)
"${dc[@]}" config --quiet
printf 'Build image trước khi thay container đang chạy…\n'
"${dc[@]}" build app web
printf 'Khởi động MySQL riêng và chờ ứng dụng (tối đa 15 phút)…\n'
# Giữ DB nếu không đổi cấu hình; tạo lại web cùng app để không giữ IP FPM cũ.
if ! "${dc[@]}" up -d --wait --wait-timeout 240 db \
    || ! "${dc[@]}" up -d --no-deps --force-recreate --wait --wait-timeout 900 app \
    || ! "${dc[@]}" up -d --no-deps --force-recreate --wait --wait-timeout 90 web; then
    "${dc[@]}" ps -a
    printf 'Deploy chưa thành công. Xem log:\n'
    printf 'docker compose --env-file .env.production -f docker-compose.deploy.yml logs --tail=100 db app web\n'
    exit 1
fi
for deploy_path in /up / /admin; do
    deploy_http=$(curl --silent --show-error --output /dev/null --write-out '%{http_code}' --max-time 15 "http://127.0.0.1:1113$deploy_path") \
        || die "Không kết nối được HTTP $deploy_path trên cổng 1113."
    [[ "$deploy_http" == 200 ]] || die "HTTP $deploy_path thất bại (mã $deploy_http)."
done
printf '\nỨng dụng đã sẵn sàng tại 127.0.0.1:1113. Proxy HTTPS của host cần trỏ về địa chỉ này.\n'
printf 'Cập nhật lần sau: bash deploy.sh\n'
