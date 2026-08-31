# [DATA-006] — Lệnh `lacdoi:import-usda` và `lacdoi:validate-data`

**Mã task:** DATA-006
**Giai đoạn:** GĐ1
**Loại:** Backend
**Status:** ✅ DONE (2026-08-31)
**Ngày tạo:** 2026-08-31
**Phụ thuộc:** SETUP-003

---

## Mục tiêu

Hai lệnh artisan là xương sống của quy trình dữ liệu:

- `lacdoi:import-usda` — nhập và **lọc** dữ liệu USDA, sinh file trong `data/reference/`
- `lacdoi:validate-data` — kiểm tra toàn vẹn dữ liệu, chạy trước mỗi commit và trong CI

---

## Việc cần làm

### `lacdoi:import-usda`

1. Đọc file gốc từ `storage/imports/` (không commit — xem
   [DATA-REPOSITORY §5](../../04-data-sources/DATA-REPOSITORY.md)).
2. **Lọc**: chỉ giữ cột dùng đến và các nhóm thực phẩm liên quan món Việt. Bỏ nhóm thức ăn nhanh Mỹ,
   sản phẩm thương hiệu không bán ở VN. Mục tiêu: từ hàng GB xuống vài MB.
3. Ghi kết quả ra `data/reference/*.csv`.
4. Cập nhật `data/reference/MANIFEST.json`: URL nguồn, tên bản phát hành, ngày tải, **SHA-256 của
   file gốc**, số dòng sau lọc, license, và ghi chú về tiêu chí lọc.
5. Nạp vào bảng `reference_foods` với `fct_source` tương ứng.

### `lacdoi:validate-data`

Hiện thực 14 kiểm tra V1–V14 ở [DATA-PIPELINE §5](../../04-data-sources/DATA-PIPELINE.md).

- Lỗi 🔴 → exit code khác 0 (để CI chặn được).
- Cảnh báo 🟡 → in ra, exit code 0.
- In tóm tắt: số bản ghi kiểm, số lỗi, số cảnh báo.

---

## Definition of Done

- [x] `php artisan lacdoi:import-usda` chạy được — đã chạy thật với SR Legacy 2026-08-31,
      sinh `data/reference/usda-sr_legacy.csv` (7.793 dòng, 0,64 MB)
- [x] MANIFEST có đủ: nguồn, ngày, checksum SHA-256 file zip gốc, license CC0, tiêu chí lọc
- [x] File sau lọc < 20 MB tổng cộng
- [x] `php artisan lacdoi:validate-data` hiện thực đủ V1–V14
- [x] Lỗi 🔴 trả exit code 1 — đã cố ý chèn bản ghi sai, lệnh bắt được đúng 4 vi phạm
      (V3, V5, V13, V14) rồi xanh lại khi gỡ
- [x] Test Pest cho V1 và V5 ở `tests/Feature/ValidateDataCommandTest.php` — mô phỏng dữ liệu
      bẩn lọt qua bằng cách tạm gỡ ràng buộc R1/R2 trong transaction test

---

## Cạm bẫy đã biết

> **Đọc [RESEARCH-PROTOCOL §3–§4](../../09-standards/RESEARCH-PROTOCOL.md) trước khi viết lệnh này.**
> Chín bẫy kỹ thuật ghi ở đó đều đã mắc thật trong lúc tra cứu thủ công, và lệnh này sẽ mắc lại nếu
> không biết trước. Ba cái nguy hiểm nhất:
>
> 1. **`nutrient id 1008 = kcal`, `1062 = kJ`.** Lọc theo tên thay vì id là sai 4,184 lần. Foundation
>    lại dùng `2047`/`2048`.
> 2. **Response bị cắt trông giống "không có dữ liệu".** Response tìm kiếm tới 1,24 MB, mất 60–150 s.
>    Timeout ngắn ⇒ JSON cụt ⇒ lỗi parse ⇒ lệnh báo "không tra được" cho một nguyên liệu vốn có dữ
>    liệu. **Phải kiểm tra parse thành công trước khi kết luận không có dữ liệu.** Đây là lỗi im lặng,
>    không có gì báo sai.
> 3. **Bản ghi Foundation có thể không có năng lượng.** Quy tắc "ưu tiên Foundation vì mới hơn" sẽ
>    tạo ra nguyên liệu không tính được calo. Kiểm tra sự tồn tại của năng lượng trước khi chọn dòng.

> **Quy tắc chung về cổng chất lượng:** thêm bất kỳ kiểm tra tự động nào cũng phải kiểm chứng rằng nó
> **đỏ được** — cố ý chèn một lỗi, xem lệnh có báo không, rồi mới tin. Một cổng chất lượng luôn xanh
> là bằng chứng giả, còn tệ hơn không có cổng nào.

- Đừng nhập nguyên bản USDA vào DB rồi lọc sau — 1,6 GB sẽ làm mọi thao tác chậm và làm phình dump.
- Checksum phải là của **file gốc chưa lọc**, để người sau tải đúng bản đó về đối chiếu.

---

## Ghi chú

**Hoàn thành 2026-08-31.**

- Lệnh nhận `--source=sr_legacy|foundation`, `--dir=` (thư mục CSV đã giải nén) và `--zip=`
  (file zip gốc, để ghi SHA-256 vào MANIFEST). Thiếu `--zip` thì cảnh báo chứ không chặn.
- Kiểm tra V-file chạy thẳng trên `data/` không cần database; kiểm tra trên dữ liệu dẫn xuất
  (V1, V5-DB, V6, V7-trigram, V8, V9-DB) tự bỏ qua **có thông báo** khi database chưa kết nối.
- V8 (nguyên liệu mồ côi) chỉ chạy khi đã có món — với 0 món, 150 cảnh báo "mồ côi" là nhiễu
  vô nghĩa, không phải tín hiệu.
- Ba bẫy trong mục "Cạm bẫy" đã né đúng: lọc theo nutrient id (1008, dự phòng 2047/2048),
  bỏ bản ghi không có năng lượng (0 bản ghi ở SR Legacy — đúng như kỳ vọng vì SR Legacy
  luôn có mục Energy), đọc CSV bằng generator nên không phình bộ nhớ.
