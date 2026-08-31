# [DATA-002] — Nhập USDA SR Legacy + Foundation Foods

**Mã task:** DATA-002 · **Giai đoạn:** GĐ1 · **Loại:** Dữ liệu
**Status:** ⬜ TODO · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** DATA-006

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

- [ ] `reference_foods` có dữ liệu với `fct_source` = `usda_sr_legacy` và `usda_foundation`
- [ ] File đã lọc trong `data/reference/`, tổng < 20 MB
- [ ] MANIFEST đầy đủ, có checksum **file gốc**
- [ ] Nhật ký thẩm định [LICENSE-COMPLIANCE §7](../../04-data-sources/LICENSE-COMPLIANCE.md) đã cập nhật
- [ ] Thử tra một vài thực phẩm quen thuộc, đối chiếu giá trị với trang web FDC

## Cạm bẫy

- **Giữ hai bộ ở hai `fct_source` riêng, không gộp.** QT-4 cấm trộn nguồn trong một phép tính; gộp
  chúng lại là phá quy tắc ngay ở tầng dữ liệu.
- SR Legacy đã **ngừng cập nhật từ 2018** — đó là bản cuối, không phải lỗi.
- USDA phủ rất kém nguyên liệu Việt. Đây là điều đã biết trước, không phải lỗi nhập liệu.

## Ghi chú
