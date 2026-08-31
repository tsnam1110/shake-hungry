# [DATA-001] — Biên soạn cây `regions` Việt Nam

**Mã task:** DATA-001 · **Giai đoạn:** GĐ1 · **Loại:** Dữ liệu
**Status:** ⬜ TODO · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** SETUP-003

---

## Mục tiêu

Dựng cây địa lý để phân loại món theo vùng miền — một trong những yêu cầu gốc của dự án.

## Việc cần làm

Ba cấp: `country` → `macro_region` → `province`.

```
VN (country)
├── vn-north   (Bắc Bộ)      → Hà Nội, Nam Định, Hải Phòng…
├── vn-central (Trung Bộ)    → Huế, Đà Nẵng, Quảng Nam, Nghệ An…
└── vn-south   (Nam Bộ)      → TP.HCM, Cần Thơ, An Giang…
```

Lưu vào `data/reference/regions-vn.json`. Chỉ thêm tỉnh **khi thực sự có món đặc trưng** — không cần
nhập đủ 63 tỉnh ngay.

## Definition of Done

- [ ] Ba vùng lớn + các tỉnh có món đặc trưng rõ rệt (Huế, Nam Định, Hà Nội, TP.HCM…)
- [ ] Cấu trúc đệ quy đúng, `parent_id` trỏ hợp lệ
- [ ] `country_code = 'VN'` cho mọi bản ghi
- [ ] Seed dựng lại được từ file

## Cạm bẫy

- **Đừng dùng enum `Bắc/Trung/Nam`.** Bảng phân cấp cho phép mở rộng sang quốc gia khác mà không phải
  migrate ([ARCHITECTURE §7](../../02-architecture/ARCHITECTURE.md)).
- Ranh giới vùng miền ẩm thực **không trùng** ranh giới hành chính. Nếu phải chọn, ưu tiên cách người
  ăn hiểu, không phải cách nhà nước chia.

## Ghi chú
