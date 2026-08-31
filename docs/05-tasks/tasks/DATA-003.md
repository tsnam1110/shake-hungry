# [DATA-003] — Nhập USDA FNDDS + SR Links

**Mã task:** DATA-003 · **Giai đoạn:** GĐ1 · **Loại:** Dữ liệu
**Status:** ✅ DONE (2026-08-31) · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** DATA-006

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

- [x] **333 Food Code** kèm 1.810 dòng nguyên liệu + gram (bảng `fndds_foods`, `fndds_food_inputs`)
- [x] Mỗi Food Code có năng lượng USDA công bố — điều kiện lọc bắt buộc
- [x] `data/reference/usda-fndds-foods.csv` + `usda-fndds-srlinks.csv`, MANIFEST đầy đủ
- [x] Truy vấn thử đã chạy: Food Code 13126000 "Ice cream, fried" → 3 nguyên liệu (graham
      crackers 20 g, shortening 5 g, vanilla ice cream 80 g), kcal công bố 282/100 g
- [x] Đã dùng thật ở [TEST-002](./TEST-002.md): 204/333 món chạy được qua engine

## Cạm bẫy

- **Đừng nhập nguyên 1,6 GB vào database.** Lọc trước. Xem
  [DATA-REPOSITORY §5](../../04-data-sources/DATA-REPOSITORY.md).
- **Đừng dùng dữ liệu FNDDS làm món hiển thị cho người dùng.** Đây là món Mỹ, không phù hợp ngữ cảnh
  Việt Nam, và trộn vào sẽ làm bẩn kho món.

## Ghi chú

**Hoàn thành 2026-08-31.**

> ### ⚠️ Đính chính — mô tả `gram_weight` ở mục "Bối cảnh" bên trên SAI
>
> Bản đầu ghi SR Links cho *"số gram **trên 100 g món**"*. **Không đúng:** `gram_weight` trong
> `input_food.csv` là gram cho **cả mẻ công thức** — có món tới 3.980 g (Stewed tripe). Ví dụ
> Food Code 58155510 "Soupy rice mixture": tổng 3.150,9 g nguyên liệu, trong khi kcal công bố
> là trên 100 g.
>
> Hiểu sai câu này khiến lần chạy TEST-002 đầu tiên cho **bias +400%** và tưởng engine hỏng.
> Phải chuẩn hoá về 100 g trước khi so — xem [TEST-002](./TEST-002.md) Ghi chú.

**Bẫy kỹ thuật mới, chưa có trong RESEARCH-PROTOCOL:** bộ **Survey (FNDDS) dùng `nutrient_nbr`
trong cột `nutrient_id`** — 208 = kcal, 203/204/205/291 = đạm/béo/carb/xơ — **khác** SR Legacy
dùng id chuẩn (1008/1003/1004/1005/1079), **dù `nutrient.csv` của chính bộ đó vẫn khai id chuẩn**.
Lọc theo 1008 cho ra **0 dòng mà không báo lỗi gì** — đúng loại lỗi im lặng mà DATA-006 cảnh báo.
Lệnh nay dò cả hai hệ và **fail to** nếu không đọc được năng lượng cho bất kỳ Food Code nào.

**Tiêu chí lọc** (từ 5.432 Food Code xuống 333): có kcal công bố · ≥2 nguyên liệu SR Links ·
≥80% nguyên liệu ánh xạ được sang SR Legacy · suy được cách chế biến từ mô tả (bỏ 1.560 món
không rõ cách chế biến, 1.936 món nhiều nguyên liệu không ánh xạ được). Phân bố: `song` 137,
`nau` 62, `nuong` 57, `chien` 34, `kho` 24, `hap` 15, `luoc` 4.

**Nguyên liệu không ánh xạ được** phần lớn có `sr_code` 8 chữ số — đó là Food Code lồng nhau
(món trong món), không phải NDB của nguyên liệu đơn. Bỏ chúng là đúng.
