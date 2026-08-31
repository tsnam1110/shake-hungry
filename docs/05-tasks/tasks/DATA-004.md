# [DATA-004] — Nhập bảng hệ số hao hụt khi nấu

**Mã task:** DATA-004 · **Giai đoạn:** GĐ1 · **Loại:** Dữ liệu
**Status:** ⬜ TODO · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** DATA-006

---

## Mục tiêu

Nạp hệ số hao hụt (yield factor) và hệ số giữ lại (retention factor) để engine tính đúng khối lượng
thành phẩm. Bỏ qua bước này thì mọi chất dinh dưỡng đều sai theo cùng một tỷ lệ.

## Nguồn (public domain, đã duyệt)

| Bảng | Nội dung |
|---|---|
| *USDA Table of Cooking Yields for Meat and Poultry, Release 2* | Hệ số hao hụt thịt, gia cầm theo cách nấu |
| *USDA Table of Nutrient Retention Factors, Release 6 (2007)* | 26 chất × ~290 thực phẩm. Có cả PDF và CSV |
| *FAO/INFOODS Guidelines for Converting Units v1.0 (2012)* | **Phương pháp** chọn hệ số — đọc và áp dụng, không chép bảng |

## Việc cần làm

1. Tải bảng USDA (CSV nếu có, nếu chỉ có PDF thì bóc bảng).
2. Nạp vào bảng tra cứu hệ số, khoá theo (loại thực phẩm, cách chế biến).
3. Điền `default_yield_factor` cho các nguyên liệu đã có ở [ING-001](./ING-001.md).
4. Lưu bản sao điều khoản vào `data/reference/_licenses/`.

## Definition of Done

- [ ] Bảng hệ số nạp được, tra được theo (thực phẩm, cách chế biến)
- [ ] Nguyên liệu ở ING-001 có `default_yield_factor` hợp lý
- [ ] File trong `data/reference/`, MANIFEST đầy đủ
- [ ] Có ghi chú cho các nguyên liệu Việt không có trong bảng USDA và cách xử lý

## Cạm bẫy

- **Hệ số giữ lại (retention) áp cho vi chất, không áp cho năng lượng.** v1 chỉ tính macro nên phần
  lớn chưa dùng đến — nhưng đừng áp nhầm vào kcal.
- **Bảng USDA không có món Việt.** Gạo, bún, bánh phở phải suy từ nhóm tương đương hoặc tự đo — và
  phải **ghi rõ là suy ra**, không ghi như thể tra được.

## Ghi chú
