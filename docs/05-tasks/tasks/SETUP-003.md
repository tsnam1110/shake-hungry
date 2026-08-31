# [SETUP-003] — Migration toàn bộ schema

**Mã task:** SETUP-003
**Giai đoạn:** GĐ1
**Loại:** Backend
**Status:** ⬜ TODO
**Ngày tạo:** 2026-08-31
**Phụ thuộc:** SETUP-001

---

## Mục tiêu

Hiện thực toàn bộ schema theo [DATA-MODEL](../../03-data-model/DATA-MODEL.md), **bao gồm cả 9 ràng
buộc toàn vẹn R1–R9** — đó là phần bảo vệ các quy tắc tuyệt đối ở tầng database.

---

## Việc cần làm

Migration theo thứ tự phụ thuộc:

1. `regions`, `reference_foods`
2. `ingredients`, `ingredient_units`
3. `dishes`, `dish_names`, `dish_meal_slots`, `dish_ingredients`, `dish_nutrition`, `dish_media`,
   `dish_recipe_sources`, `dish_tags`
4. `users`, `user_profiles`, `meal_logs`
5. `contributions`
6. `ingredient_tcm_traits`, `dish_tcm_summary` (tạo sẵn bảng, dùng ở GĐ6)

Index bắt buộc:
- `gin (name_vi gin_trgm_ops)` trên `ingredients`
- `gin (name gin_trgm_ops)` trên `dish_names`
- `ivfflat (embedding)` trên `dishes` — bỏ qua nếu `pgvector` chưa cài, ghi lại vào Ghi chú

Ràng buộc R1–R9 viết bằng `CHECK` hoặc trigger. **Không** chỉ kiểm ở tầng ứng dụng — mục đích của
chúng là chặn cả khi code có lỗi.

---

## Definition of Done

- [ ] `php artisan migrate:fresh` chạy sạch trên database trắng
- [ ] Mọi bảng, cột, kiểu dữ liệu khớp [DATA-MODEL](../../03-data-model/DATA-MODEL.md)
- [ ] 9 ràng buộc R1–R9 đã tạo và **thử nghiệm chặn được** (xem [TEST-001](./TEST-001.md))
- [ ] Index trigram đã tạo và `EXPLAIN` cho thấy có dùng
- [ ] Nếu schema thực tế khác tài liệu: **đã sửa DATA-MODEL trong cùng commit** (QT-7)

---

## Cạm bẫy đã biết

- **R1 (`dish_nutrition.source <> 'llm_suggested'`) là chốt chặn kỹ thuật cho QT-1.** Đừng bỏ qua vì
  "đằng nào code cũng không ghi vào đó" — chính là để phòng khi code sai.
- Cột `embedding vector(768)` sẽ lỗi nếu chưa cài `pgvector`. Có thể tạm bỏ cột này và thêm ở GĐ4,
  nhưng phải ghi vào Ghi chú, không được quên.
- **Không dùng kiểu `ENUM` của PostgreSQL** — thêm giá trị mới cần migration phiền phức. Dùng
  `varchar` + `CHECK`.

---

## Ghi chú
