# data/ — Nguồn sự thật của dữ liệu

> **File trong thư mục này là nguồn sự thật. Database chỉ là bản dựng lại từ file.**
> Đặc tả đầy đủ: [docs/04-data-sources/DATA-REPOSITORY.md](../docs/04-data-sources/DATA-REPOSITORY.md) · Quy tắc QT-3 trong [CLAUDE.md](../CLAUDE.md).

## Cấu trúc

| Thư mục | Nội dung | Ai sửa |
|---|---|---|
| `reference/` | Dữ liệu nhập từ nguồn ngoài (USDA, Wikidata…) + `regions-vn.json` | Máy (`lacdoi:import-usda`), trừ regions biên soạn tay |
| `reference/_licenses/` | Bản sao điều khoản từng nguồn + ngày tải | Người, khi thêm nguồn mới |
| `ingredients/` | Nguyên liệu do dự án biên soạn — mỗi nguyên liệu một file | Người biên soạn (hoặc `lacdoi:export-data`) |
| `dishes/vn/` | Món ăn do dự án biên soạn — mỗi món một file | Người biên soạn (hoặc `lacdoi:export-data`) |
| `media/dishes/` | Ảnh món (WebP ≤150 KB, cạnh dài ≤1280 px) | Người, kèm mục trong `media/MANIFEST.json` |

## Giấy phép dữ liệu

Chốt tại [LEGAL-002](../docs/05-tasks/tasks/LEGAL-002.md) ngày 2026-10-31. Thư mục này là một
**cơ sở dữ liệu tập hợp** (collective database) — giấy phép áp theo từng thành phần:

- **Phần tự biên soạn** (toàn bộ file món và phần lớn file nguyên liệu): **CC BY 4.0**,
  ghi công "Lắc Đói".
- **Dòng có nguồn Open Food Facts** (nhận diện qua `license_note` trong file nguyên liệu):
  **ODbL 1.0**, ghi công Open Food Facts.
- **`reference/` nhập từ USDA:** public domain (CC0). **Wikidata:** CC0.
- **Số tra cứu từng mục từ nguồn thứ cấp** (Bảng TPTP Việt Nam, FNRI, MEXT, IFCT, văn liệu):
  ghi trích dẫn trong `mapping_note`/`source_url` của từng file; dự án chỉ dùng dữ kiện
  dinh dưỡng tra cứu riêng lẻ, không sao chép bảng/dataset của các nguồn này.

## Quy tắc bắt buộc

1. **Sửa dữ liệu = sửa file ở đây**, không sửa thẳng database. Database dựng lại bằng
   `php artisan migrate:fresh --seed`.
2. Trước mỗi commit dữ liệu: chạy `php artisan lacdoi:validate-data` — không được có lỗi 🔴.
3. `_index.json` do máy sinh (`lacdoi:export-data`) — **không sửa tay**.
4. Ảnh không có license trong `media/MANIFEST.json` thì **không được commit** (ràng buộc R4).
5. Không commit file nguồn gốc dung lượng lớn (USDA ~1,6 GB) — chúng nằm ở `storage/imports/`
   (đã gitignore); chỉ commit bản đã lọc kèm checksum trong `reference/MANIFEST.json`.
