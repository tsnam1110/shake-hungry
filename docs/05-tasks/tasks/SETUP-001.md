# [SETUP-001] — Khởi tạo Laravel + PostgreSQL + extension

**Mã task:** SETUP-001
**Giai đoạn:** GĐ0
**Loại:** Backend
**Status:** ✅ DONE (2026-08-31) — riêng `pgvector` hoãn có ý thức, xem Ghi chú
**Ngày tạo:** 2026-08-31
**Phụ thuộc:** —

---

## Mục tiêu

Dựng nền chạy được: Laravel kết nối PostgreSQL có đủ ba extension mà kiến trúc phụ thuộc vào.

---

## Bối cảnh

Laragon mặc định dùng MySQL. Dự án này **bắt buộc PostgreSQL** vì cần `pg_trgm` (chống trùng tên món
tiếng Việt lúc nhập liệu) và `pgvector` (gợi ý món tương tự ở GĐ4). Lý do đầy đủ:
[ARCHITECTURE §4](../../02-architecture/ARCHITECTURE.md).

---

## Việc cần làm

1. Bật PostgreSQL 16+ trong Laragon (Menu → PostgreSQL). Nếu Laragon bản đang dùng không kèm sẵn,
   cài riêng và trỏ cổng.
2. Cài `pgvector` — không có sẵn trong bản PostgreSQL tiêu chuẩn trên Windows, phải tải bản build cho
   đúng phiên bản PostgreSQL đang dùng.
3. Tạo database `shake_hungry`, bật extension:
   ```sql
   CREATE EXTENSION IF NOT EXISTS pg_trgm;
   CREATE EXTENSION IF NOT EXISTS unaccent;
   CREATE EXTENSION IF NOT EXISTS vector;
   ```
4. `composer create-project laravel/laravel .` (hoặc khởi tạo vào thư mục hiện tại).
5. Cấu hình `.env`: `DB_CONNECTION=pgsql`, cổng, tên DB. Tạo `.env.example` tương ứng (không có mật khẩu).
6. Viết một migration thử để xác nhận kết nối.
7. Ghi lại quy trình cài đặt vào [DEPLOYMENT §2](../../10-deployment/DEPLOYMENT.md) — **kể cả những
   chỗ vướng**, vì người kế nhiệm sẽ vướng đúng chỗ đó.

---

## Definition of Done

- [x] `php artisan --version` chạy được — Laravel 13.29.0 trên PHP 8.3.30
- [x] `php artisan migrate` chạy sạch trên PostgreSQL — 12 migration, kiểm 2026-08-31
- [x] Truy vấn kiểm chứng **hai** extension bắt buộc trả kết quả (`pg_trgm`, `unaccent`);
      `pgvector` **hoãn có ý thức** — xem Ghi chú
- [x] `.env.example` đã commit, `.env` nằm trong `.gitignore`
- [x] Quy trình cài đặt (kể cả chỗ vướng) đã ghi vào DEPLOYMENT §2

---

## Cạm bẫy đã biết

- **`pgvector` trên Windows là chỗ vướng nhiều nhất.** Bản build phải khớp đúng phiên bản
  PostgreSQL. Nếu quá vất vả, có thể tạm hoãn `pgvector` — GĐ1 chưa cần tới nó, chỉ GĐ4 mới cần.
  Nhưng **phải hoãn có ý thức và ghi vào task này**, không phải quên.
- **Đừng vì `pgvector` khó cài mà quay về MySQL.** `pg_trgm` mới là thứ GĐ2 cần, và nó có sẵn trong
  PostgreSQL tiêu chuẩn.
- Kiểm tra sắp xếp (collation) tiếng Việt khi tạo database — ảnh hưởng tới sắp xếp tên món.

---

## Ghi chú

**Hoàn thành 2026-08-31.** Những gì thực tế xảy ra:

1. **Laragon bản đang dùng không kèm PostgreSQL** (chỉ có MySQL). Giải pháp: dùng bản
   **binaries portable 17.11-1** của EnterpriseDB, giải nén vào
   `D:\laragon\bin\postgresql\postgresql-17.11-1`, data dir tại `D:\laragon\data\postgresql-17`.
   Không cài Windows service — khởi động bằng `pg_ctl` (lệnh trong DEPLOYMENT §2).
2. **Cluster khởi tạo với ICU locale tiếng Việt**: `initdb -E UTF8 --locale-provider=icu
   --icu-locale=vi-VN --locale=C -A trust`. Sắp xếp tên món tiếng Việt do ICU đảm nhiệm.
3. **Xác thực `trust` cho dev local** (chỉ lắng nghe localhost). Production phải đổi —
   ghi ở DEPLOYMENT.
4. **PHP của Laragon chưa bật sẵn `pdo_pgsql`/`pgsql`** — DLL có sẵn trong `ext/`, chỉ cần
   thêm 2 dòng `extension=` vào `php.ini`. Đây là chỗ vướng ai dựng lại cũng sẽ gặp.
5. **`pgvector`: HOÃN CÓ Ý THỨC.** Không có bản build Windows chính thức cho bản binaries
   portable; GĐ1–GĐ3 không cần nó (chỉ REC-002 ở GĐ4 cần). Migration đã viết kiểu dò:
   `enable_pg_extensions` tự bật `vector` nếu có, `create_dishes_tables` chỉ thêm cột
   `embedding vector(768)` khi extension tồn tại. Khi tới GĐ4: build pgvector bằng
   `nmake` theo hướng dẫn chính thức, hoặc chuyển sang bản PostgreSQL cài đặt EDB đầy đủ,
   rồi chạy `migrate:fresh --seed` (an toàn vì database chỉ là bản dựng từ `data/` — QT-3).
6. Laravel tạo bằng `composer create-project laravel/laravel` ra **Laravel 13.29** —
   tài liệu ghi "Laravel 12+", vẫn thoả. Test framework chuyển sang **Pest 4.7**
   theo CODING-STANDARDS §6.
