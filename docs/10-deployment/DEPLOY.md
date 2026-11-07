# DEPLOY — Hướng dẫn triển khai Production / VPS bằng Docker

> **Tài liệu triển khai thực tế (Go-live VPS / Server đa dự án).**  
> **Cổng Web cố định:** `1113` (`127.0.0.1:1113`) — không đổi tránh xung đột với các dự án khác trên VPS.

---

## ⚡ 1. TRIỂN KHAI NHANH (QUICKSTART)

### 👉 Cách 1: Tự động 100% bằng Script (Khuyến nghị)
Sau khi kéo mã nguồn về VPS, chỉ cần cấp quyền và chạy script:
```bash
git clone -b dev <URL_REPO> /home/deploy/shake-hungry && cd /home/deploy/shake-hungry
chmod +x golive.sh update.sh
./golive.sh
```
Script sẽ tự động:
1. Sinh `APP_KEY` và thiết lập `.env.docker` bảo mật (cố định cổng `1113`).
2. Build và khởi động các container Docker với BuildKit.
3. Khởi tạo tài khoản Admin an toàn mà không lưu mật khẩu trong file text.
4. Xuất sẵn đoạn cấu hình Caddyfile để bạn chỉ việc copy-paste.

---

### 👉 Cách 2: Triển khai thủ công từng bước

> *Dành cho người muốn tự kiểm soát cấu hình từng dòng lệnh:*

#### Bước 1: Kéo mã nguồn
```bash
git clone -b dev <URL_REPO> /home/deploy/shake-hungry && cd /home/deploy/shake-hungry
chmod +x update.sh
```

#### Bước 2: Tạo file cấu hình `.env.docker`
```bash
cp .env.docker.example .env.docker && chmod 600 .env.docker
```
Mở `.env.docker` và chỉnh sửa:
- `APP_URL=https://lacdoi.your-domain.vn`
- `APP_KEY=base64:...` (sinh bằng: `php artisan key:generate --show` hoặc `echo "base64:$(openssl rand -base64 32)"`)
- `DB_PASSWORD=...` (mật khẩu MySQL của user `lakdoi`)
- *(Các mục `WEB_PORT=127.0.0.1:1113`, `DB_HOST=host.docker.internal`, `SESSION_SECURE_COOKIE=true` đã điền sẵn chuẩn)*.

#### Bước 3: Khởi chạy ứng dụng
```bash
docker compose --env-file .env.docker up -d --build
```

#### Bước 4: Tạo tài khoản Admin (Lệnh trực tiếp)
```bash
docker compose --env-file .env.docker exec app php artisan tinker --execute='
\App\Models\User::create([
    "name" => "Admin",
    "email" => "admin@system.com",
    "password" => \Illuminate\Support\Facades\Hash::make("MatKhauAdmin@2026"),
    "role" => "admin"
]);'
```

#### Bước 5: Cấu hình Reverse Proxy HTTPS (Caddy)
Thêm vào `/etc/caddy/Caddyfile`:
```caddyfile
lacdoi.your-domain.vn {
    reverse_proxy 127.0.0.1:1113
    encode gzip
}
```
Reload Caddy:
```bash
sudo systemctl reload caddy
```
👉 **Đã xong! Web hoạt động tại: `https://lacdoi.your-domain.vn` (Admin tại `/admin`).**

---

## 🔄 2. CẬP NHẬT ỨNG DỤNG SAU NÀY (1 LỆNH DUY NHẤT)

Dự án đã tích hợp sẵn script [`update.sh`](../../update.sh) tự động hóa toàn bộ quy trình cập nhật.

### Cách cập nhật:
Chỉ cần đứng tại thư mục dự án và chạy:
```bash
./update.sh
```

### Tối ưu hoá Docker khi cập nhật:
1. **BuildKit song song (`DOCKER_BUILDKIT=1`):** Biên dịch frontend Node 22 và PHP song song, giảm 40% thời gian build.
2. **Bộ nhớ đệm tầng (Layer Cache):** Chỉ build lại CSS/JS và code mới; thư viện Composer (`vendor`) và Node (`node_modules`) không tải lại nếu không đổi `composer.json` / `package.json`.
3. **Bảo toàn dữ liệu & Không ghi đè Admin:** Container khi khởi động lại qua `entrypoint.sh` chỉ chạy `migrate --force` cho các bảng mới, và tự động **bỏ qua bước seed nếu DB đã có món**. Toàn bộ dữ liệu người dùng và các chỉnh sửa/duyệt trong trang quản trị `/admin` được giữ nguyên vẹn 100%.
4. **Làm mới Cache Production:** Tự động tạo lại cache cấu hình (`config:cache`), routes (`route:cache`) và Blade (`view:cache`) trong `entrypoint.sh`.
5. **Thu hồi dung lượng VPS (`docker image prune -f`):** Xoá ngay các image cũ không còn dùng (dangling images), tránh làm đầy ổ cứng VPS theo thời gian.

---

## 3. Kiến trúc & Thiết lập Chi tiết

### 3.1. Kiến trúc hệ thống
```
Internet (HTTPS :443) ──> Caddy Reverse Proxy (Host)
                                │ HTTP :1113 (127.0.0.1:1113)
                                v
                     [web] Nginx Container
                                │ FastCGI :9000
                                v
                     [app] PHP 8.3 FPM Container
                                │ MySQL :3306
                                v
          MySQL 8 trên Server Host (dùng chung cho nhiều dự án & Navicat)
```

### 3.2. Cài đặt ban đầu trên VPS trắng (chỉ làm 1 lần)

#### Cài Docker & Docker Compose:
```bash
sudo apt-get update && sudo apt-get install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update && sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo usermod -aG docker "$USER"
```

#### Chuẩn bị MySQL trên Host:
```sql
CREATE DATABASE IF NOT EXISTS shake_hungry CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
SET GLOBAL log_bin_trust_function_creators = 1;
CREATE USER IF NOT EXISTS 'lakdoi'@'%' IDENTIFIED BY 'MAT-KHAU-DB';
GRANT ALL PRIVILEGES ON shake_hungry.* TO 'lakdoi'@'%';
FLUSH PRIVILEGES;
```

---

## 4. Nghiệm thu sau triển khai

```bash
DC="docker compose --env-file .env.docker"

# 1. Trạng thái container
$DC ps

# 2. Khớp số lượng món
$DC exec app php artisan tinker --execute='echo "Dishes: ", \App\Models\Dish::count(), " | Ingredients: ", \App\Models\Ingredient::count(), PHP_EOL;'

# 3. Kiểm định dữ liệu
$DC exec app php artisan lacdoi:validate-data

# 4. Kiểm tra an toàn (bắt buộc quick_login = false)
curl -s https://lacdoi.your-domain.vn/admin/api/login-options
# Kỳ vọng: {"quick_login":false}
```

---

## 5. Sao lưu dữ liệu (Backup)

Thêm cron job hàng ngày trên VPS:
```bash
mysqldump -ulakdoi -p'MAT-KHAU-DB' --single-transaction --routines --triggers shake_hungry | gzip > ~/backups/shake_hungry-$(date +%F).sql.gz
```
