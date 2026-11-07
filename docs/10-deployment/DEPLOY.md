# DEPLOY — Hướng dẫn triển khai Production / VPS bằng Docker

> **Tài liệu duy nhất cho môi trường triển khai thực tế (Go-live VPS / Server).**  
> Dùng chung file `docker-compose.yml` duy nhất với môi trường dev.

---

## 1. Kiến trúc Production

```
Internet (HTTPS :443) ──> Caddy / Nginx Reverse Proxy (Host)
                               │ HTTP :80 (hoặc 127.0.0.1:8080)
                               v
                     [web] Nginx Container
                               │ FastCGI :9000
                               v
                     [app] PHP 8.3 FPM Container
                               │ MySQL :3306
                               v
         MySQL 8 trên Server Host (dùng chung cho nhiều dự án & Navicat)
```

- **Server chạy nhiều dự án**: MySQL đặt trên host (cổng 3306). Navicat và các app khác truy cập trực tiếp `localhost:3306`. App kết nối host qua `host.docker.internal:3306`.
- **Server chạy độc lập**: Thêm cờ `--profile db` để Docker tự chạy một container MySQL riêng biệt.

---

## 2. Chuẩn bị trên VPS / Server

### 2.1. Cài Docker & Docker Compose
```bash
sudo apt-get update && sudo apt-get install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo usermod -aG docker "$USER"
```

### 2.2. Chuẩn bị MySQL trên Host (Server nhiều dự án)
Tạo database và cấp quyền (thay `MAT-KHAU-DB` bằng mật khẩu mạnh):
```sql
CREATE DATABASE IF NOT EXISTS shake_hungry CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
SET GLOBAL log_bin_trust_function_creators = 1;
CREATE USER IF NOT EXISTS 'lakdoi'@'%' IDENTIFIED BY 'MAT-KHAU-DB';
GRANT ALL PRIVILEGES ON shake_hungry.* TO 'lakdoi'@'%';
FLUSH PRIVILEGES;
```
*(Đảm bảo MySQL trong file cấu hình `my.cnf` bind `0.0.0.0` để container Docker có thể kết nối vào qua gateway).*

---

## 3. Các bước triển khai (Go-Live)

### Bước 1: Lấy mã nguồn lên VPS
```bash
git clone -b dev <URL_REPO> /home/deploy/shake-hungry
cd /home/deploy/shake-hungry
```

### Bước 2: Tạo cấu hình môi trường `.env.docker`
```bash
cp .env.docker.example .env.docker
chmod 600 .env.docker
```

Chỉnh sửa file `.env.docker` các mục quan trọng sau:
1. `APP_ENV=production`
2. `APP_DEBUG=false`
3. `APP_URL=https://lacdoi.your-domain.vn`
4. `APP_KEY=`: Sinh bằng lệnh `docker run --rm php:8.3-cli php -r 'echo "base64:" . base64_encode(random_bytes(32)) . PHP_EOL;'`
5. `DB_HOST=host.docker.internal` (hoặc `db` nếu chạy container riêng)
6. `DB_PORT=3306`
7. `DB_USERNAME=lakdoi` và `DB_PASSWORD=MAT-KHAU-DB`
8. `WEB_PORT=127.0.0.1:8080` (khi đứng sau Caddy / Nginx host) hoặc `80` (nếu chạy trực tiếp)
9. `ADMIN_EMAIL=your-email@example.com` và `ADMIN_PASSWORD=MAT-KHAU-ADMIN-MANH`
10. `ADMIN_QUICK_LOGIN=false` (Bắt buộc giữ `false` ở production)

### Bước 3: Khởi chạy ứng dụng
```bash
# Định nghĩa alias lệnh
DC="docker compose --env-file .env.docker"

# Khởi chạy (MySQL chạy trên host)
$DC up -d --build

# (Nếu dùng container MySQL riêng: $DC --profile db up -d --build)
```
Theo dõi log:
```bash
$DC logs -f app
```
> Khi thấy dòng: `Đã tạo tài khoản quản trị: ...` và `php-fpm 8.3 ... ready to handle connections` là khởi chạy thành công.

### Bước 4: Đổi mật khẩu Admin & Xoá mật khẩu khỏi `.env.docker`
Sau khi đăng nhập web `/admin` thành công bằng mật khẩu trên:
```bash
# Xoá mật khẩu admin khỏi file env để an toàn
sed -i "s|^ADMIN_PASSWORD=.*|ADMIN_PASSWORD=|" .env.docker
$DC up -d --force-recreate app
```

---

## 4. Thiết lập HTTPS (Khuyến nghị dùng Caddy trên Host)

Cài Caddy:
```bash
sudo apt-get install -y debian-keyring debian-archive-keyring apt-transport-https curl
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt-get update && sudo apt-get install -y caddy
```

Thêm cấu hình vào `/etc/caddy/Caddyfile`:
```caddyfile
lacdoi.your-domain.vn {
    reverse_proxy 127.0.0.1:8080
    encode gzip
}
```
Reload Caddy:
```bash
sudo systemctl reload caddy
```
Caddy sẽ tự động cấp phát và gia hạn SSL Let's Encrypt.

---

## 5. Nghiệm thu sau khi triển khai

```bash
DC="docker compose --env-file .env.docker"

# 1. Kiểm tra trạng thái container
$DC ps

# 2. Khớp số lượng món giữa file data/ và database
$DC exec app php artisan tinker --execute='echo "Dishes: ", \App\Models\Dish::count(), " | Ingredients: ", \App\Models\Ingredient::count(), PHP_EOL;'

# 3. Kiểm định tính toàn vẹn dữ liệu
$DC exec app php artisan lacdoi:validate-data

# 4. Kiểm tra an toàn bảo mật (quick login phải là false)
curl -s https://lacdoi.your-domain.vn/admin/api/login-options
# Kết quả kỳ vọng: {"quick_login":false}
```

---

## 6. Vận hành thường ngày & Sao lưu (Backup)

### Sao lưu CSDL hằng ngày (cron host)
```bash
# Chạy trực tiếp trên host:
mysqldump -ulakdoi -p'MAT-KHAU-DB' --single-transaction --routines --triggers shake_hungry | gzip > ~/backups/shake_hungry-$(date +%F).sql.gz
```

### Cập nhật mã nguồn mới
```bash
cd /home/deploy/shake-hungry
git pull origin dev
$DC up -d --build
```

### Khởi động lại hoặc xem log
```bash
$DC logs -f app          # Xem log ứng dụng
$DC logs -f web          # Xem log nginx web
$DC restart app          # Khởi động lại app
$DC down && $DC up -d    # Khởi động lại toàn bộ stack
```
> ⚠️ **Tuyệt đối KHÔNG chạy `$DC down -v`**: Lệnh này sẽ xoá toàn bộ volume dữ liệu.

