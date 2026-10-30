# [SETUP-004] — Chuyển nền tảng CSDL từ PostgreSQL sang MySQL 8

**Mã task:** SETUP-004
**Giai đoạn:** GĐ1 (thay đổi nền tảng hạ tầng)
**Loại:** Backend
**Status:** ✅ DONE (2026-10-30)
**Ngày tạo:** 2026-10-30
**Phụ thuộc:** SETUP-001, SETUP-003

---

## Lý do

**Quyết định của chủ dự án, ngày 2026-10-30.** Quyết định gốc "PostgreSQL, không MySQL"
(ARCHITECTURE §4 bản đầu, SETUP-001) bị thay thế. Tài liệu liên quan đã cập nhật trong
cùng commit theo QT-7: ARCHITECTURE §4 (mục "Thay đổi 2026-10-30"), DEPLOYMENT,
README, CLAUDE.md (Đính chính lần 4).

Kết quả rà soát trước khi chuyển: phần phụ thuộc PostgreSQL trong code thực tế rất hẹp —
1 migration bật extension, 3 chỉ mục GIN trigram, 1 partial unique index, cột `vector`,
nhóm trigger DEFERRABLE của R6/R8, và 2 điểm trong `lacdoi:validate-data` (V6 dùng cast
`::jsonb`, V7 dùng `pg_trgm similarity + unaccent`). Toàn bộ phần còn lại viết bằng
Schema Builder / Eloquent chuẩn nên chuyển nguyên vẹn.

---

## Ánh xạ thay thế theo từng điểm

| PostgreSQL | MySQL 8 |
|---|---|
| Extension `pg_trgm`, `unaccent` | Không cần extension. Tìm không dấu + sắp xếp tiếng Việt: collation `utf8mb4_vi_0900_ai_ci` ở tầng database |
| Chỉ mục GIN trigram (`ingredients.name_vi`, `dishes.slug`, `dish_names.name`) | FULLTEXT index với ngram parser |
| `similarity()` của pg_trgm (V7 — cảnh báo trùng tên món) | Thuật toán trigram tương đương pg_trgm chạy ở tầng PHP trong `LacdoiValidateData` — một thuật toán cho mọi nền tảng |
| Partial unique index `dish_names_one_primary ... WHERE is_primary` | Cột generated `primary_slot` (VIRTUAL) + unique index — NULL không bị ràng buộc duy nhất, hiệu lực tương đương |
| Cột `embedding vector(768)` (pgvector) | Bỏ qua có ý thức như trước: MySQL 8 chưa có kiểu VECTOR. GĐ4/REC-002 tính lại (MySQL 9 đã có VECTOR) |
| CONSTRAINT TRIGGER DEFERRABLE (R6/R8, kiểm lúc COMMIT) | MySQL không có deferred trigger — bố trí lại: AFTER UPDATE trên `dishes` + AFTER DELETE/UPDATE trên hai bảng con. **Giới hạn đã biết:** thời điểm INSERT món chưa kiểm được (nguyên liệu luôn chèn sau), do V4 của `lacdoi:validate-data` chốt chặn. Chi tiết trong docblock của migration 100008 |
| Default literal `'[]'` cho cột jsonb | Biểu thức `(JSON_ARRAY())` — MySQL không nhận literal làm default cho cột JSON |
| `missing_ingredients = '[]'::jsonb` trong CHECK R3 và V6 | `CAST('[]' AS JSON)` / `JSON_LENGTH(...) > 0` |

Phát hiện phụ trong lúc chuyển: cột `user_profiles.sex` khai `varchar(8)` nhưng giá trị
mặc định `'unspecified'` dài 11 ký tự — PostgreSQL chấp nhận (ép kiểu có cắt bớt),
MySQL strict mode từ chối. Đã nới cột lên `varchar(16)`, đúng với miền giá trị của
CHECK đi kèm (`male` / `female` / `unspecified`).

## Điều kiện vận hành mới

- Database phải tạo với `CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci`
  (xem DEPLOYMENT §2).
- Server MySQL phải bật `log_bin_trust_function_creators` nếu có binary logging,
  nếu không user thường không tạo được trigger (lỗi 1419). Đã ghi ở DEPLOYMENT §2.

---

## Kiểm chứng (chạy 2026-10-30, trên MySQL 8.0)

- `php artisan migrate:fresh --seed` — chạy sạch từ đầu, đủ 15 migration.
- Số lượng khớp tuyệt đối với bản PostgreSQL trước khi chuyển:
  **255 nguyên liệu / 61 món / 7.793 reference_foods**; 5 trigger R6/R8 hiện diện.
- `php artisan lacdoi:validate-data` — **0 lỗi 🔴**, 2 cảnh báo 🟡 (V10 mỡ lợn nước,
  V8 nguyên liệu chưa dùng) — y hệt kết quả trên PostgreSQL.
- `php artisan test` — **40/40 test Pest đạt** (93 assertions), gồm R6/R8/R10 trong
  `IntegrityConstraintsTest` (đã đổi cơ chế ép kiểm từ `SET CONSTRAINTS ALL IMMEDIATE`
  sang `forceCompletenessCheck()` nhận biết nền tảng trong `tests/Pest.php`).
