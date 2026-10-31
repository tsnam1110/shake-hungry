# FRUIT-001 — Schema + hạ tầng dữ liệu nhánh hoa quả

- **Trạng thái:** 🔶 (2026-10-31 — hạ tầng xong: migration `fruits`/`fruit_prices`, model, FruitSeeder,
  validator V17; hồ sơ mẫu xoài chạy end-to-end. Đóng task khi FRUIT-003/004 lấp đủ dữ liệu thật)
- **Phụ thuộc:** —
- **Nhánh:** Hoa quả (FRUIT-MODULE)

`data/fruits/*.json` là nguồn sự thật (QT-3) theo FRUIT-MODULE.md §2; migration `fruits` + `fruit_prices`; seeder dựng lại từ file; validator: giá bắt buộc có `source_name` + `collected_at`, `pairing_cautions` bắt buộc có `level`, tháng trong 1–12, cửa sổ là tập con của tháng có hàng.
