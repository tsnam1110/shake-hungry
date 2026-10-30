# [SETUP-004] — Thiết lập nền tảng CSDL MySQL 8 + tài khoản mặc định

**Mã task:** SETUP-004
**Giai đoạn:** GĐ1 (nền tảng hạ tầng)
**Loại:** Backend
**Status:** ✅ DONE (2026-10-30)
**Ngày tạo:** 2026-10-30
**Phụ thuộc:** SETUP-001, SETUP-003

---

## Nội dung

Chốt lại toàn bộ lớp CSDL của dự án trên **MySQL 8** theo quyết định của chủ dự án
ngày 2026-10-30, và đồng bộ tài liệu toàn hệ thống trong cùng commit (QT-7):
ARCHITECTURE §4, DEPLOYMENT, DATA-MODEL, README, CLAUDE.md (Đính chính lần 4),
ROADMAP, SETUP-001, SETUP-003, `.env.example`, CI (`.github/workflows/tests.yml`).

## Quy ước nền tảng

| Hạng mục | Quy ước |
|---|---|
| CSDL | MySQL 8.0+, database `shake_hungry` / `shake_hungry_test` |
| Charset / collation | `utf8mb4` / `utf8mb4_vi_0900_ai_ci` — tìm kiếm không dấu + sắp xếp đúng tiếng Việt ở tầng database |
| Tài khoản | **Mặc định `root`, không mật khẩu** (kiểu Laragon) — không tạo user database riêng cho dự án |
| Điều kiện server | Bật `log_bin_trust_function_creators` (khi có binary logging) để tạo được trigger — lỗi 1419 nếu thiếu |
| Tìm kiếm theo tên | Chỉ mục FULLTEXT với ngram parser (`ingredients.name_vi`, `dishes.slug`, `dish_names.name`) |
| Cảnh báo trùng tên (V7) | Độ giống trigram tính ở tầng PHP trong `LacdoiValidateData` sau khi chuẩn hoá bỏ dấu — không phụ thuộc tính năng riêng của CSDL |
| "Đúng một tên chính cho mỗi (món, locale)" | Cột generated `primary_slot` (VIRTUAL) + unique index — MySQL không có partial unique index, NULL không bị ràng buộc duy nhất nên hiệu lực tương đương |
| Default cho cột JSON | Biểu thức `(JSON_ARRAY())` — MySQL không nhận literal làm default cho cột JSON |
| Cột `embedding` | Chưa tạo — hoãn tới GĐ4 (REC-002), khi đó chốt kiểu theo phiên bản MySQL đang dùng (MySQL 9 có `VECTOR`) |
| Ràng buộc R6/R8 | Trigger MySQL: AFTER UPDATE trên `dishes`, AFTER DELETE/UPDATE trên `dish_ingredients` và `dish_recipe_sources`. **Giới hạn đã biết:** MySQL không có deferred trigger nên thời điểm INSERT món chưa kiểm được (nguyên liệu luôn chèn sau) — V4 của `lacdoi:validate-data` chốt chặn. Chi tiết trong docblock migration 100008 |

Phát hiện phụ: cột `user_profiles.sex` từng khai `varchar(8)` nhưng giá trị mặc
định `unspecified` dài 11 ký tự — đã nới lên `varchar(16)`, đúng miền giá trị của
CHECK đi kèm (`male` / `female` / `unspecified`). Ghi ở DATA-MODEL.

---

## Kiểm chứng (chạy 2026-10-30, trên MySQL 8.0)

- `php artisan migrate:fresh --seed` — chạy sạch từ đầu, đủ 14 migration.
- Số lượng: **255 nguyên liệu / 61 món / 7.793 reference_foods**; 5 trigger R6/R8
  hiện diện trong `information_schema.TRIGGERS`.
- `php artisan lacdoi:validate-data` — **0 lỗi 🔴**, 2 cảnh báo 🟡 (V10 mỡ lợn nước,
  V8 nguyên liệu chưa được món nào dùng).
- `php artisan test` — **40/40 test Pest đạt** (93 assertions), gồm R6/R8/R10 trong
  `IntegrityConstraintsTest` (ép kiểm bằng `forceCompletenessCheck()` ở
  `tests/Pest.php`) và CI đã chuyển sang service MySQL 8.
