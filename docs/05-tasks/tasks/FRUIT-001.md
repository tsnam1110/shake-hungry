# FRUIT-001 — Schema + hạ tầng dữ liệu nhánh hoa quả

- **Trạng thái:** ⬜ (thiết kế đã soạn tại [FRUIT-MODULE](../../03-data-model/FRUIT-MODULE.md), chờ chủ dự án chốt theo QT-8)
- **Phụ thuộc:** —
- **Nhánh:** Hoa quả (FRUIT-MODULE)

`data/fruits/*.json` là nguồn sự thật (QT-3) theo FRUIT-MODULE.md §2; migration `fruits` + `fruit_prices`; seeder dựng lại từ file; validator: giá bắt buộc có `source_name` + `collected_at`, `pairing_cautions` bắt buộc có `level`, tháng trong 1–12, cửa sổ là tập con của tháng có hàng.
