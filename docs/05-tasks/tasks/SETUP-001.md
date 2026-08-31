# [SETUP-001] — Khởi tạo Laravel + PostgreSQL + extension

**Mã task:** SETUP-001
**Giai đoạn:** GĐ0
**Loại:** Backend
**Status:** ⬜ TODO
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

- [ ] `php artisan --version` chạy được
- [ ] `php artisan migrate` chạy sạch trên PostgreSQL
- [ ] Truy vấn kiểm chứng ba extension đều trả kết quả:
      ```sql
      SELECT 'x' % 'y';                    -- pg_trgm
      SELECT unaccent('Phở bò');            -- unaccent
      SELECT '[1,2,3]'::vector;             -- pgvector
      ```
- [ ] `.env.example` đã commit, `.env` nằm trong `.gitignore`
- [ ] Quy trình cài đặt (kể cả chỗ vướng) đã ghi vào DEPLOYMENT §2

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

*(Ghi các vấn đề gặp phải khi cài đặt ở đây.)*
