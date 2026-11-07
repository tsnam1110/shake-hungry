# SETUP — Hướng dẫn cài đặt và chạy Lắc Đói (Local & Dev)

> **Tài liệu duy nhất cho môi trường phát triển / máy mới.**  
> Dự án dùng **MySQL 8** (cổng mặc định `3306`). Toàn bộ dữ liệu nằm trong `data/` (nguồn sự thật). Database là bản dựng lại bằng seeder.

---

> ⚠️ **Đính chính 2026-11-07:** phần Docker dưới đây chỉ dành local/dev. Cổng theo file mẫu là 1113; cổng 8000 chỉ dành native `artisan serve`. Production xem [DEPLOY.md](DEPLOY.md).

## 1. Yêu cầu hệ thống

- **PHP**: ≥ 8.3 (cần extensions: `pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `bcmath`, `exif`). `pcntl` dùng trong Docker/Linux, không bắt buộc trên PHP Windows.
- **MySQL**: 8.0+ chạy cổng `3306` (Laragon, Docker hoặc cài trực tiếp).
- **Composer**: 2.x
- **Node.js**: ≥ 20.19 hoặc ≥ 22.12 (để build frontend Vite 8).

---

## 2. Chuẩn bị MySQL trên máy (Dùng chung cổng 3306)

Nếu máy đã có MySQL (ví dụ qua Laragon hoặc MySQL Server cục bộ):
1. Mở Navicat / công cụ quản lý MySQL kết nối cổng `3306`.
2. Tạo database đúng charset tiếng Việt và cấp quyền trigger:
   ```sql
   CREATE DATABASE IF NOT EXISTS shake_hungry CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
   CREATE DATABASE IF NOT EXISTS shake_hungry_test CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
   SET GLOBAL log_bin_trust_function_creators = 1;
   ```
   *(Ghi `log_bin_trust_function_creators = 1` vào file cấu hình `my.ini`/`my.cnf` để không bị mất khi reboot).*

---

## 3. Cách 1: Chạy bằng Docker (tuỳ chọn local/dev)

Toàn bộ ứng dụng (PHP-FPM + Nginx) được đóng gói trong file `docker-compose.yml`.

### Bước 1: Tạo cấu hình môi trường
```bash
cp .env.docker.example .env.docker
```
- Nếu dùng MySQL trên host (Laragon/cục bộ): Giữ nguyên `DB_HOST=host.docker.internal`, `DB_PORT=3306`, user `root`. Navicat kết nối bình thường qua cổng 3306.
- Nếu muốn Docker tự tạo container MySQL riêng: Đổi `DB_HOST=db`, `DB_USERNAME=lakdoi`, `DB_PASSWORD=lacdoi-secret`.

### Bước 2: Khởi chạy
```bash
# Trường hợp 1: Dùng MySQL trên host (mặc định)
docker compose --env-file .env.docker up -d --build

# Trường hợp 2: Docker chạy cả MySQL container riêng
docker compose --env-file .env.docker --profile db up -d --build
```

Container tự động chạy migration, seed toàn bộ dữ liệu từ `data/` nếu DB mới, và tính sẵn dinh dưỡng.
- Mở web: `http://127.0.0.1:1113`
- Quản trị: `http://127.0.0.1:1113/admin` (tài khoản mặc định: `admin@system.com` / `LacDoi@2026`)

---

## 4. Cách 2: Chạy trực tiếp không dùng Docker (Native / Laragon)

```bash
# 1. Cài đặt package
composer install
npm install

# 2. Cấu hình .env
cp .env.example .env
php artisan key:generate

# 3. Khởi tạo dữ liệu
# Chỉ DB local mới có thể xoá: lệnh dưới xoá toàn bộ dữ liệu hiện có.
php artisan migrate:fresh --seed
php artisan lacdoi:validate-data

# 4. Tạo tài khoản admin đầu tiên
php artisan tinker --execute='\App\Models\User::create(["name"=>"Admin","email"=>"admin@system.com","password"=>bcrypt("LacDoi@2026"),"role"=>"admin"]);'

# 5. Build asset & chạy server
npm run build
php artisan serve --port=8000
```

---

## 5. Lệnh thường dùng & Kiểm chứng

- **Kiểm định dữ liệu**: `php artisan lacdoi:validate-data` (kỳ vọng: 0 lỗi 🔴).
- **Chạy kiểm thử (test)**: `php artisan test` (chạy trên database `shake_hungry_test`).
- **Sửa CSS/JS**: Bắt buộc chạy `npm run build` sau khi chỉnh sửa giao diện.

