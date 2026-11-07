#!/usr/bin/env bash
# ==============================================================================
# Lắc Đói — Script Go-Live Tự Động (Production VPS)
# Tự động hóa 100%: Kiểm tra hệ thống -> Thiết lập .env.docker -> Build Docker
#                   -> Tạo Admin an toàn -> Hiển thị cấu hình Caddy cổng 1113
# ==============================================================================
set -e

# Màu sắc hiển thị
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
BOLD='\033[1m'
NC='\033[0m'

echo -e "${BOLD}${BLUE}================================================================${NC}"
echo -e "${BOLD}${GREEN}        🚀 KHỞI ĐỘNG GO-LIVE DỰ ÁN LẮC ĐÓI TRÊN VPS            ${NC}"
echo -e "${BOLD}${BLUE}================================================================${NC}"
echo ""

# 1. Kiểm tra Docker & Docker Compose
if ! command -v docker &> /dev/null; then
    echo -e "${RED}[LỖI] Máy chủ chưa cài Docker! Vui lòng cài Docker trước (xem DEPLOY.md §3.2).${NC}"
    exit 1
fi

if ! docker compose version &> /dev/null; then
    echo -e "${RED}[LỖI] Docker Compose plugin chưa được cài đặt!${NC}"
    exit 1
fi
echo -e "${GREEN}✔ Docker & Docker Compose đã sẵn sàng.${NC}"

# 2. Xử lý file cấu hình .env.docker
if [ ! -f .env.docker ]; then
    echo -e "${YELLOW}[!] Chưa tìm thấy .env.docker. Bắt đầu thiết lập cấu hình tự động...${NC}"
    cp .env.docker.example .env.docker

    # Tự động sinh APP_KEY (ưu tiên dùng lệnh của Laravel nếu host có PHP, fallback sang openssl)
    if command -v php &> /dev/null && [ -f artisan ]; then
        APP_KEY=$(php artisan key:generate --show 2>/dev/null || true)
    fi
    if [ -z "$APP_KEY" ]; then
        if command -v openssl &> /dev/null; then
            APP_KEY="base64:$(openssl rand -base64 32)"
        else
            APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
        fi
    fi

    echo ""
    echo -e "${BOLD}Nhập thông tin môi trường Production:${NC}"

    # Domain
    read -p "1. Tên miền web (ví dụ: https://lacdoi.your-domain.vn): " DOMAIN_INPUT
    DOMAIN_INPUT=${DOMAIN_INPUT:-"https://lacdoi.local"}

    # DB Password
    read -sp "2. Mật khẩu MySQL của user 'lakdoi' trên VPS: " DB_PASS_INPUT
    echo ""
    while [ -z "$DB_PASS_INPUT" ]; do
        read -sp "   [Bắt buộc] Vui lòng nhập mật khẩu DB: " DB_PASS_INPUT
        echo ""
    done

    # Admin Email
    read -p "3. Email đăng nhập Admin (mặc định: admin@lacdoi.vn): " ADMIN_EMAIL_INPUT
    ADMIN_EMAIL_INPUT=${ADMIN_EMAIL_INPUT:-"admin@lacdoi.vn"}

    # Admin Password
    read -sp "4. Mật khẩu Admin (để trống sẽ tự sinh mật khẩu mạnh): " ADMIN_PASS_INPUT
    echo ""
    if [ -z "$ADMIN_PASS_INPUT" ]; then
        if command -v openssl &> /dev/null; then
            ADMIN_PASS_INPUT="LacDoi@$(openssl rand -hex 6)"
        else
            ADMIN_PASS_INPUT="LacDoi@2026AutoPass"
        fi
    fi

    # Ghi cấu hình vào .env.docker
    sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env.docker
    sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env.docker
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env.docker
    sed -i "s|^APP_URL=.*|APP_URL=${DOMAIN_INPUT}|" .env.docker
    sed -i "s|^DB_HOST=.*|DB_HOST=host.docker.internal|" .env.docker
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS_INPUT}|" .env.docker
    sed -i "s|^DB_COLLATION=.*|DB_COLLATION=utf8mb4_vi_0900_ai_ci|" .env.docker
    sed -i "s|^LOG_CHANNEL=.*|LOG_CHANNEL=daily|" .env.docker
    sed -i "s|^LOG_LEVEL=.*|LOG_LEVEL=warning|" .env.docker
    sed -i "s|^# SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|" .env.docker
    sed -i "s|^WEB_PORT=.*|WEB_PORT=127.0.0.1:1113|" .env.docker
    sed -i "s|^ADMIN_PASSWORD=.*|ADMIN_PASSWORD=|" .env.docker
    sed -i "s|^ADMIN_QUICK_LOGIN=.*|ADMIN_QUICK_LOGIN=false|" .env.docker

    chmod 600 .env.docker
    echo -e "${GREEN}✔ Đã thiết lập .env.docker bảo mật (quyền 600, cố định cổng 1113).${NC}"
else
    echo -e "${GREEN}✔ Đã có file .env.docker sẵn có. Sử dụng cấu hình hiện tại.${NC}"
fi

# 3. Build & Khởi động Docker containers
echo ""
echo -e "${BOLD}Đang build và khởi chạy container (Cổng 1113)...${NC}"
DOCKER_BUILDKIT=1 docker compose --env-file .env.docker up -d --build

echo -e "${YELLOW}Đang chờ container sẵn sàng...${NC}"
sleep 5

# 4. Tạo tài khoản Admin nếu vừa nhập thông tin
if [ -n "$ADMIN_EMAIL_INPUT" ] && [ -n "$ADMIN_PASS_INPUT" ]; then
    echo -e "${BOLD}Đang kiểm tra và khởi tạo tài khoản Admin...${NC}"
    docker compose --env-file .env.docker exec -T app php artisan tinker --execute="
    if (!\App\Models\User::where('email', '${ADMIN_EMAIL_INPUT}')->exists()) {
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => '${ADMIN_EMAIL_INPUT}',
            'password' => \Illuminate\Support\Facades\Hash::make('${ADMIN_PASS_INPUT}'),
            'role' => 'admin'
        ]);
        echo 'ADMIN_CREATED';
    } else {
        echo 'ADMIN_EXISTS';
    }" > /tmp/admin_status.txt 2>/dev/null || true

    if grep -q "ADMIN_CREATED" /tmp/admin_status.txt 2>/dev/null; then
        echo -e "${GREEN}✔ Đã tạo tài khoản quản trị thành công!${NC}"
    else
        echo -e "${BLUE}ℹ Tài khoản quản trị đã tồn tại trong DB, giữ nguyên mật khẩu cũ.${NC}"
    fi
    rm -f /tmp/admin_status.txt
fi

chmod +x update.sh 2>/dev/null || true

# 5. Hoàn tất & In hướng dẫn Caddy
DOMAIN_CLEAN=$(grep "^APP_URL=" .env.docker | cut -d '=' -f2 | sed -e 's|^https://||' -e 's|^http://||' -e 's|/.*$||')

echo ""
echo -e "${BOLD}${GREEN}================================================================${NC}"
echo -e "${BOLD}${GREEN}         🎉 TRIỂN KHAI HOÀN TẤT THÀNH CÔNG!                     ${NC}"
echo -e "${BOLD}${GREEN}================================================================${NC}"
echo ""
echo -e "🔹 ${BOLD}Cổng nội bộ container:${NC} 127.0.0.1:1113 (Cố định, không xung đột)"
if [ -n "$ADMIN_EMAIL_INPUT" ]; then
    echo -e "🔹 ${BOLD}Trang quản trị:${NC}       ${DOMAIN_INPUT:-https://$DOMAIN_CLEAN}/admin"
    echo -e "🔹 ${BOLD}Email Admin:${NC}          ${ADMIN_EMAIL_INPUT}"
    echo -e "🔹 ${BOLD}Mật khẩu Admin:${NC}       ${ADMIN_PASS_INPUT}"
fi
echo ""
echo -e "${BOLD}Cấu hình Caddy Reverse Proxy trên Host (/etc/caddy/Caddyfile):${NC}"
echo -e "${BLUE}------------------------------------------------------------${NC}"
echo -e "${DOMAIN_CLEAN:-lacdoi.your-domain.vn} {"
echo -e "    reverse_proxy 127.0.0.1:1113"
echo -e "    encode gzip"
echo -e "}"
echo -e "${BLUE}------------------------------------------------------------${NC}"
echo -e "Sau khi dán vào Caddyfile, reload bằng lệnh: ${YELLOW}sudo systemctl reload caddy${NC}"
echo ""
echo -e "👉 Khi cập nhật sau này, chỉ cần chạy đúng 1 lệnh: ${BOLD}${GREEN}./update.sh${NC}"
echo ""

