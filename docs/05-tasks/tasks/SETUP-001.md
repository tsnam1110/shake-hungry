# [SETUP-001] — Khởi tạo Laravel + MySQL

**Mã task:** SETUP-001
**Giai đoạn:** GĐ0
**Loại:** Backend
**Status:** ✅ DONE (2026-08-31) — cột `embedding` hoãn có ý thức tới GĐ4, xem Ghi chú
**Ngày tạo:** 2026-08-31
**Phụ thuộc:** —

---

## Mục tiêu

Dựng nền chạy được: Laravel kết nối MySQL 8 với đúng charset/collation tiếng Việt
mà kiến trúc phụ thuộc vào.

---

## Bối cảnh

Dự án dùng **MySQL 8** — cũng là CSDL mặc định của Laragon, môi trường dev chính.
Hai nhu cầu đặc thù được đáp ứng mà không cần extension nào thêm: tìm kiếm không dấu
và sắp xếp tiếng Việt bằng collation `utf8mb4_vi_0900_ai_ci`; cảnh báo trùng tên món
bằng độ giống trigram tính ở tầng PHP. Lý do đầy đủ:
[ARCHITECTURE §4](../../02-architecture/ARCHITECTURE.md).

---

## Việc cần làm

1. Dùng MySQL 8 có sẵn trong Laragon.
2. Tạo database với đúng charset/collation tiếng Việt:
   ```sql
   CREATE DATABASE shake_hungry CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
   CREATE DATABASE shake_hungry_test CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
   ```
   Dùng tài khoản mặc định `root` (không mật khẩu, kiểu Laragon) — không tạo user riêng.
3. Bật `log_bin_trust_function_creators` nếu server có binary logging, để tạo được
   trigger (xem DEPLOYMENT §2).
4. `composer create-project laravel/laravel .` (hoặc khởi tạo vào thư mục hiện tại).
5. Cấu hình `.env`: `DB_CONNECTION=mysql`, cổng 3306, tên DB, user `root`. Tạo
   `.env.example` tương ứng (không có mật khẩu).
6. Viết một migration thử để xác nhận kết nối.
7. Ghi lại quy trình cài đặt vào [DEPLOYMENT §2](../../10-deployment/DEPLOYMENT.md) — **kể cả những
   chỗ vướng**, vì người kế nhiệm sẽ vướng đúng chỗ đó.

---

## Definition of Done

- [x] `php artisan --version` chạy được — Laravel 13.29.0 trên PHP 8.3.30
- [x] `php artisan migrate` chạy sạch trên MySQL — kiểm 2026-08-31
- [x] Database dùng đúng collation `utf8mb4_vi_0900_ai_ci` (kiểm bằng
      `information_schema.SCHEMATA`)
- [x] `.env.example` đã commit, `.env` nằm trong `.gitignore`
- [x] Quy trình cài đặt (kể cả chỗ vướng) đã ghi vào DEPLOYMENT §2

---

## Cạm bẫy đã biết

- **Collation của database quyết định chất lượng tìm kiếm tiếng Việt.** Tạo database
  mà quên đặt `utf8mb4_vi_0900_ai_ci` thì tìm kiếm không dấu và sắp xếp tên món đều
  sai — và lỗi này không báo ở đâu cả, phải tự kiểm.
- **Cột `embedding` cho gợi ý món tương tự: HOÃN CÓ Ý THỨC tới GĐ4** (REC-002).
  GĐ1–GĐ3 không cần nó. Khi tới GĐ4 mới chốt kiểu dữ liệu theo phiên bản MySQL
  đang dùng (MySQL 9 đã có kiểu VECTOR).

---

## Ghi chú

**Hoàn thành 2026-08-31.**

1. Laravel tạo bằng `composer create-project laravel/laravel` ra **Laravel 13.29** —
   tài liệu ghi "Laravel 12+", vẫn thoả. Test framework chuyển sang **Pest 4.7**
   theo CODING-STANDARDS §6.
2. Ngày 2026-10-30 chủ dự án chốt lại toàn bộ nền tảng ở SETUP-004: MySQL 8 là CSDL
   duy nhất của dự án, tài khoản database dùng mặc định `root` — tài liệu toàn hệ
   thống đã đồng bộ theo quyết định này.
