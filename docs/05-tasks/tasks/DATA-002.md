# [DATA-002] — Nhập USDA SR Legacy + Foundation Foods

**Mã task:** DATA-002 · **Giai đoạn:** GĐ1 · **Loại:** Dữ liệu
**Status:** ✅ DONE (2026-11-02) — SR Legacy xong 2026-08-31; Foundation Foods nhập
2026-11-02 (bản phát hành CSV 2025-04-24: 353 dòng có năng lượng, MANIFEST có SHA-256
file gốc; seeder dựng lại đủ 353 dòng sau migrate:fresh --seed) · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** DATA-006

---

## Mục tiêu

Nạp bảng thành phần thực phẩm nền cho engine tính calo. Đây là nguồn dinh dưỡng chính cho tới khi (và
nếu) xin được dữ liệu Viện Dinh dưỡng ([LEGAL-001](./LEGAL-001.md)).

## Bối cảnh

| Bộ | Nội dung | Kích thước | License |
|---|---|---|---|
| **SR Legacy** | Bảng chuẩn, bản cuối cùng (4/2018) | 54 MB | Public domain / CC0 |
| **Foundation Foods** | Dữ liệu phân tích mới, chi tiết hơn nhưng ít mục hơn | 29 MB | Public domain / CC0 |

Cả hai đều CC0 — không ràng buộc gì, dùng thương mại và phân phối lại thoải mái.

## Việc cần làm

1. Tải bộ dữ liệu về `storage/imports/` từ `fdc.nal.usda.gov/download-datasets/`.
2. Chạy `php artisan lacdoi:import-usda --dataset=sr-legacy` và `--dataset=foundation`.
3. Kiểm tra `data/reference/MANIFEST.json` có đủ: URL, tên bản phát hành, ngày tải, SHA-256 file gốc,
   số dòng sau lọc, license, tiêu chí lọc.
4. Lưu bản sao điều khoản vào `data/reference/_licenses/usda-fdc.txt`.

## Definition of Done

- [x] `reference_foods` có `usda_sr_legacy` (7.793 dòng) và `usda_foundation` (353 dòng,
      nhập 2026-11-02 từ bản CSV Foundation Foods 2025-04-24; giữ hai bộ ở hai
      `fct_source` riêng theo QT-4)
- [x] File đã lọc trong `data/reference/`, tổng < 20 MB (usda-sr_legacy.csv: 0,64 MB)
- [x] MANIFEST đầy đủ, có checksum SHA-256 **file zip gốc**
- [x] Nhật ký thẩm định [LICENSE-COMPLIANCE §7](../../04-data-sources/LICENSE-COMPLIANCE.md) đã cập nhật
- [x] Đối chiếu giá trị: **116/116 nguyên liệu có snapshot `usda_sr_legacy` khớp tuyệt đối
      (chênh ≤ 0,5 kcal) với bảng vừa nhập, 0 lệch** — mạnh hơn "tra một vài thực phẩm",
      vì snapshot vốn được chép tay từ trang FDC lúc tra cứu

## Cạm bẫy

- **Giữ hai bộ ở hai `fct_source` riêng, không gộp.** QT-4 cấm trộn nguồn trong một phép tính; gộp
  chúng lại là phá quy tắc ngay ở tầng dữ liệu.
- SR Legacy đã **ngừng cập nhật từ 2018** — đó là bản cuối, không phải lỗi.
- USDA phủ rất kém nguyên liệu Việt. Đây là điều đã biết trước, không phải lỗi nhập liệu.

## Ghi chú

**2026-08-31 — SR Legacy hoàn tất, còn Foundation:**

- Đã tải `FoodData_Central_sr_legacy_food_csv_2018-04.zip` (5,8 MB — nhỏ hơn nhiều con số
  54 MB ước tính trong Bối cảnh), chạy `lacdoi:import-usda --source=sr_legacy`, nạp 7.793 dòng.
- **Đối chiếu snapshot theo DATA-MODEL §3.1 đã chạy trong `IngredientSeeder`:** khớp
  (chênh kcal ≤ 0,5) ⇒ gán `reference_food_id`, engine chuyển sang dùng `reference_foods`;
  lệch ⇒ giữ null + cảnh báo, không âm thầm ghi đè. Kết quả: **116 khớp / 0 lệch.**
- `ReferenceFoodSeeder` nạp lại bảng tham chiếu từ CSV đã lọc (đã commit) — máy trắng
  không cần tải lại file gốc (QT-3).
- **Còn lại:** Foundation Foods (chỉ ảnh hưởng 3 nguyên liệu đang dùng `usda_foundation`).
  URL tải thay đổi theo bản phát hành — lấy link mới nhất tại
  fdc.nal.usda.gov/download-datasets/ rồi chạy `lacdoi:import-usda --source=foundation`.
  8 nguyên liệu `usda_branded` và 1 `usda_survey` tra lẻ theo mã — KHÔNG nhập nguyên bộ
  Branded (2,9 GB) chỉ vì 8 mã; giữ snapshot làm nguồn cho tới khi có cách nhập chọn lọc.
