# [DATA-007] — Lệnh `lacdoi:export-data` (JSON ổn định)

**Mã task:** DATA-007
**Giai đoạn:** GĐ1
**Loại:** Backend
**Status:** ✅ DONE (2026-08-31) — tiêu chí Filament chờ GĐ2, xem Ghi chú
**Ngày tạo:** 2026-08-31
**Phụ thuộc:** SETUP-003

---

## Mục tiêu

Xuất dữ liệu từ database ngược ra file JSON trong `data/`, để hiện thực vòng đồng bộ hai chiều của
QT-3. Người biên soạn làm việc trên Filament; lệnh này đưa kết quả về dạng file có version.

---

## Bối cảnh

```
Filament → MySQL → [lacdoi:export-data] → data/**/*.json → git
                                                     ↓
                        máy khác: migrate:fresh --seed → MySQL
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

- [x] `php artisan lacdoi:export-data` sinh đúng cấu trúc file (150 nguyên liệu + `_index.json`)
- [x] **Chạy lệnh hai lần liên tiếp ⇒ hash từng file giống hệt** — kiểm 2026-08-31, có test
      Pest thường trực ở `tests/Feature/ExportStabilityTest.php`
- [ ] Sửa một món trong Filament → export → diff một file — **chưa kiểm được, Filament ở GĐ2
      (ADM-001)**; tiêu chí tương đương đã kiểm: sửa DB bằng seeder → export → diff đúng phạm vi
- [x] Vòng khép kín: `export-data` → `migrate:fresh --seed` → `export-data` ⇒ không có diff
- [x] Tiếng Việt hiển thị đúng, không escape `\uXXXX` (`JSON_UNESCAPED_UNICODE`)
- [x] Không xuất dữ liệu dẫn xuất (`dish_nutrition`, `grams_resolved`, `reference_food_id`…)

---

## Cạm bẫy đã biết

- **JSON không ổn định là lỗi âm thầm giết chết quy trình.** Nếu mỗi lần export sinh diff trên 400
  file, không ai review được nữa, và người ta sẽ bỏ luôn việc review — mất toàn bộ lợi ích của QT-3.
- Kiểu `DECIMAL` của MySQL trả về chuỗi trong PHP. Ép kiểu và định dạng nhất quán.
- Chú ý ký tự xuống dòng khi commit trên Windows — cấu hình `.gitattributes` với `*.json text eol=lf`.

---

## Ghi chú

**Hoàn thành 2026-08-31.**

- **Lần export đầu tiên chuẩn hoá lại 140/150 file nguyên liệu** (thứ tự khoá, định dạng số
  `53` → `53.0`, bổ sung khoá thiếu `is_ready_to_eat`/`secondary_sources`/`density_note`
  với giá trị mặc định, sắp `units` theo `unit_name`). Đã kiểm chứng bằng script so sánh
  ngữ nghĩa: **140/140 file tương đương 100%, không mất một giá trị nào.** Trong lúc kiểm
  phát hiện `hat-sen-kho` có unit 0,675 g bị `numeric(8,2)` làm tròn — đã nới cột `grams`
  lên `numeric(8,3)` (đính chính DATA-MODEL).
- Định dạng số canonical: float giữ `.0` (`JSON_PRESERVE_ZERO_FRACTION`); mọi giá trị
  `DECIMAL` của MySQL được cast float trước khi encode để không lúc `18`, lúc `18.00`.
- Thụt lề 2 dấu cách đạt bằng hậu xử lý regex (json_encode của PHP cố định 4).
