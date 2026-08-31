# [SETUP-002] — Dựng cấu trúc `data/` + `.gitignore` + MANIFEST

**Mã task:** SETUP-002
**Giai đoạn:** GĐ0
**Loại:** Backend
**Status:** ⬜ TODO
**Ngày tạo:** 2026-08-31
**Phụ thuộc:** SETUP-001

---

## Mục tiêu

Hiện thực quy tắc **QT-3: mọi tài nguyên phải nằm trong repo**. Dựng cấu trúc thư mục `data/` và các
quy ước file để dữ liệu không bao giờ chỉ tồn tại trong database.

---

## Bối cảnh

Yêu cầu gốc từ chủ dự án: *"Mọi tài nguyên về món ăn, nguyên liệu... đều phải được lưu trữ trong dự
án để khi triển khai trên thiết bị khác sẽ không bị thiếu hay sai sót."*

Đặc tả đầy đủ: [DATA-REPOSITORY](../../04-data-sources/DATA-REPOSITORY.md).

---

## Việc cần làm

1. Tạo cấu trúc:
   ```
   data/
   ├── README.md                 Giải thích quy tắc, trỏ về DATA-REPOSITORY
   ├── reference/
   │   ├── _licenses/            Bản sao điều khoản từng nguồn + ngày tải
   │   └── MANIFEST.json         Nguồn, ngày tải, phiên bản, checksum
   ├── ingredients/
   ├── dishes/vn/
   └── media/
       ├── dishes/
       └── MANIFEST.json         License + ghi công từng ảnh
   ```
2. Thêm `.gitkeep` vào thư mục rỗng để git giữ chúng.
3. Cập nhật `.gitignore`:
   ```
   /storage/imports/     # file USDA gốc 1,6 GB — tải lại được
   /.env
   ```
4. Tạo `data/reference/MANIFEST.json` và `data/media/MANIFEST.json` rỗng, đúng cấu trúc.
5. Viết `data/README.md` nêu rõ: file là nguồn sự thật, database là bản dựng lại, không sửa
   `_index.json` bằng tay.

---

## Definition of Done

- [ ] Cấu trúc thư mục đúng như [DATA-REPOSITORY §2](../../04-data-sources/DATA-REPOSITORY.md)
- [ ] Hai file MANIFEST tồn tại và đúng schema
- [ ] `.gitignore` loại trừ `storage/imports/` và `.env`
- [ ] `data/README.md` đã viết
- [ ] `git status` sạch sau khi commit (không có file rác)

---

## Cạm bẫy đã biết

- **Đừng commit file USDA gốc.** FNDDS đầy đủ ~1,6 GB. Chỉ commit **bản đã lọc** (vài MB) do
  [DATA-006](./DATA-006.md) sinh ra, kèm checksum của file gốc trong MANIFEST để người sau tải đúng
  bản đó.
- **Đừng tạo một file `dishes.json` khổng lồ.** Mỗi món một file — để diff đọc được và không xung đột
  merge.

---

## Ghi chú
