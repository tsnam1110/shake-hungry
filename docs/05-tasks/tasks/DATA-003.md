# [DATA-003] — Nhập USDA FNDDS + SR Links

**Mã task:** DATA-003 · **Giai đoạn:** GĐ1 · **Loại:** Dữ liệu
**Status:** ⬜ TODO · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** DATA-006

---

## Mục tiêu

Nạp đồ thị **món → nguyên liệu → gram** của USDA. Đây không phải dữ liệu hiển thị cho người dùng —
nó là **bộ dữ liệu để kiểm chứng engine tính calo**.

## Bối cảnh — vì sao bộ này quan trọng

FNDDS có tệp `SR Links`: với mỗi **Food Code** (một món đã chế biến), nó cho danh sách **SR Code**
nguyên liệu kèm **số gram trên 100 g món**. Đây là đồ thị món→nguyên liệu→dinh dưỡng do chính phủ Mỹ
duy trì, ở public domain.

Nó **không có món Việt**, nhưng cho hai thứ:

1. **Mẫu tham chiếu về cấu trúc** — một món chế biến thường được mô tả bằng bao nhiêu nguyên liệu, tỷ
   lệ điển hình ra sao.
2. **Bộ kiểm chứng engine** — chạy engine trên nguyên liệu của một Food Code rồi so với giá trị dinh
   dưỡng USDA công bố cho chính món đó. Cả hai vế cùng một nguồn, nên lệch nhiều nghĩa là **engine
   sai**. Dùng ở [TEST-002](./TEST-002.md).

## Việc cần làm

1. Tải FNDDS 2021–2023 (~1,6 GB CSV) về `storage/imports/`.
2. `php artisan lacdoi:import-usda --dataset=fndds` — lọc mạnh, chỉ giữ Food Code có cách chế biến
   tương tự món Việt (nấu, xào, chiên, luộc). Bỏ đồ đóng gói công nghiệp.
3. Lưu vào bảng riêng phục vụ kiểm chứng, `fct_source = 'usda_fndds'`.

## Definition of Done

- [ ] Có ≥300 Food Code kèm danh sách nguyên liệu và gram
- [ ] Mỗi Food Code có giá trị năng lượng USDA công bố (để đối chiếu)
- [ ] File đã lọc trong `data/reference/`, MANIFEST đầy đủ
- [ ] Truy vấn thử: lấy một Food Code, in ra nguyên liệu + gram + kcal công bố

## Cạm bẫy

- **Đừng nhập nguyên 1,6 GB vào database.** Lọc trước. Xem
  [DATA-REPOSITORY §5](../../04-data-sources/DATA-REPOSITORY.md).
- **Đừng dùng dữ liệu FNDDS làm món hiển thị cho người dùng.** Đây là món Mỹ, không phù hợp ngữ cảnh
  Việt Nam, và trộn vào sẽ làm bẩn kho món.

## Ghi chú
