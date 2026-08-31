# [DISH-003] — Lên 300 món

**Mã task:** DISH-003 · **Giai đoạn:** GĐ3 · **Loại:** Dữ liệu
**Status:** ⬜ TODO · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** DISH-002

---

## Mục tiêu

Mở rộng chiều sâu vùng miền và món ít phổ biến hơn.

## Việc cần làm

- Món đặc trưng theo tỉnh (Huế, Nam Định, Hội An, miền Tây…)
- Món chay
- Món ăn vặt
- Món theo mùa

Đến mốc này, chạy [QA-002](./QA-002.md) — đo độ chính xác calo lần đầu, khi đã đủ mẫu để có ý nghĩa.

## Definition of Done

- [ ] 300 món published
- [ ] Có ≥20 món chay
- [ ] Có ≥10 tỉnh/thành có món đặc trưng riêng
- [ ] QA-002 đã chạy, kết quả ghi vào [ACCURACY-SPEC §6](../../06-nutrition/ACCURACY-SPEC.md)
- [ ] QA-001 chạy đều mỗi 50 món

## Cạm bẫy

- Đây là quãng dài và nhàm nhất của dự án. Chia nhỏ mục tiêu theo tuần và tự theo dõi tiến độ.
- Nếu QA-002 không đạt ngưỡng, **dừng nhập món và sửa engine trước** — nhập thêm 200 món với engine
  sai chỉ tạo thêm 200 con số sai.

## Ghi chú
