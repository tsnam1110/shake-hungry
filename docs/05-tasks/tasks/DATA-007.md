# [DATA-007] — Lệnh `lacdoi:export-data` (JSON ổn định)

**Mã task:** DATA-007
**Giai đoạn:** GĐ1
**Loại:** Backend
**Status:** ⬜ TODO
**Ngày tạo:** 2026-08-31
**Phụ thuộc:** SETUP-003

---

## Mục tiêu

Xuất dữ liệu từ database ngược ra file JSON trong `data/`, để hiện thực vòng đồng bộ hai chiều của
QT-3. Người biên soạn làm việc trên Filament; lệnh này đưa kết quả về dạng file có version.

---

## Bối cảnh

```
Filament → PostgreSQL → [lacdoi:export-data] → data/**/*.json → git
                                                     ↓
                        máy khác: migrate:fresh --seed → PostgreSQL
```

Chi tiết: [DATA-REPOSITORY §6](../../04-data-sources/DATA-REPOSITORY.md).

---

## Việc cần làm

Xuất `ingredients`, `dishes` (và các bảng con) ra file theo định dạng ở
[DATA-REPOSITORY §3](../../04-data-sources/DATA-REPOSITORY.md). Sinh lại `_index.json`.

**Yêu cầu cốt lõi: JSON phải ỔN ĐỊNH.**

| Yêu cầu | Vì sao |
|---|---|
| Khoá sắp xếp theo thứ tự cố định (không phụ thuộc thứ tự cột trong DB) | Nếu không, mỗi lần export sinh diff giả trên hàng trăm file |
| Số định dạng nhất quán (`18.0` không lúc thành `18`, lúc thành `18.00`) | như trên |
| Mảng sắp xếp theo khoá xác định (nguyên liệu theo slug, tên theo locale) | như trên |
| Xuống dòng **LF**, không CRLF | Đang làm trên Windows — dễ dính |
| UTF-8 không BOM, **không escape ký tự Unicode** (`JSON_UNESCAPED_UNICODE`) | Để tiếng Việt đọc được trong diff |
| Thụt lề 2 dấu cách | Diff theo dòng đọc được |

**Không xuất dữ liệu dẫn xuất:** `dish_nutrition`, `grams_resolved`, `dish_tcm_summary`, `embedding`
đều được tính lại lúc seed. Xuất chúng ra file sẽ tạo hai nguồn sự thật mâu thuẫn.

---

## Definition of Done

- [ ] `php artisan lacdoi:export-data` sinh đúng cấu trúc file
- [ ] **Chạy lệnh hai lần liên tiếp không sửa gì ⇒ `git status` sạch** — đây là tiêu chí quan trọng nhất
- [ ] Sửa một món trong Filament → export → `git diff` chỉ hiện đúng một file, đúng vài dòng
- [ ] Vòng khép kín: `export-data` → `migrate:fresh --seed` → `export-data` ⇒ không có diff
- [ ] Tiếng Việt hiển thị đúng trong file, không bị escape thành `\uXXXX`
- [ ] Không xuất dữ liệu dẫn xuất

---

## Cạm bẫy đã biết

- **JSON không ổn định là lỗi âm thầm giết chết quy trình.** Nếu mỗi lần export sinh diff trên 400
  file, không ai review được nữa, và người ta sẽ bỏ luôn việc review — mất toàn bộ lợi ích của QT-3.
- Kiểu `numeric` của PostgreSQL trả về chuỗi trong PHP. Ép kiểu và định dạng nhất quán.
- Chú ý ký tự xuống dòng khi commit trên Windows — cấu hình `.gitattributes` với `*.json text eol=lf`.

---

## Ghi chú
