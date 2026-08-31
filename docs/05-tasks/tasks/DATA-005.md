# [DATA-005] — Nhập món Việt từ Wikidata

**Mã task:** DATA-005 · **Giai đoạn:** GĐ1 · **Loại:** Dữ liệu
**Status:** ✅ DONE (2026-08-31) · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** SETUP-003

---

## Mục tiêu

Gieo mầm danh mục món Việt để app có thứ hiển thị ngay từ đầu, thay vì database rỗng.

## Bối cảnh — kỳ vọng thực tế

Wikidata là **CC0**, sạch license tuyệt đối. Nhưng số lượng rất khiêm tốn:

| Truy vấn | Số lượng |
|---|---|
| Món có `cuisine = ẩm thực Việt Nam` | **58** |
| Tính cả các ẩm thực vùng của Việt Nam | **79** |
| Tổng số món trên Wikidata (mọi quốc gia) | 9.365 |

⇒ Đây là **mồi**, không phải nguồn dữ liệu chính. 58 món là điểm khởi đầu để nút Lắc không rỗng, còn
lại phải tự biên soạn ở GĐ3.

## Việc cần làm

1. Viết truy vấn SPARQL lấy món Việt kèm: nhãn `vi` và `en`, ảnh, nguyên liệu (P527/P186), quốc gia.
2. Lưu kết quả thô vào `data/reference/wikidata-vn-dishes.json` kèm ngày truy vấn.
3. Nạp vào `dishes` với `source = 'imported'`, `status = 'draft'`, `completeness_level = 1`,
   `needs_review = true`.
4. Nạp tên vào `dish_names`.

## Definition of Done

- [x] **61 món** trong `dishes` với `source = 'imported'` (mốc ≥58 đạt)
- [x] Mọi món `status = 'draft'`, `needs_review = true`, `confidence = 'unknown'`
- [x] Tên vi/en vào `dish_names` đúng `locale`
- [x] `data/reference/wikidata-vn-dishes.json` (kết quả thô + **câu SPARQL nguyên văn** + ngày
      truy vấn trong `_meta`), MANIFEST có SHA-256 và ghi rõ Wikidata là dữ liệu sống
- [x] Ảnh **chưa tải về** — URL nguồn ghi trong `import_note` kèm cảnh báo phải kiểm license
      từng ảnh (R4) và chờ Q4

## Cạm bẫy

- **Đừng để món nhập tự động ở trạng thái `published`.** Dữ liệu Wikidata thưa và không đồng đều
  (thiếu vùng miền, thiếu bữa phù hợp). Phải qua tay người biên soạn trước.
- **Ảnh Wikidata/Commons có license riêng từng ảnh** — không phải tất cả đều CC0. Kiểm từng ảnh trước
  khi tải về, và ghi vào `data/media/MANIFEST.json` (ràng buộc R4).
- Ghi ngày truy vấn: Wikidata là dữ liệu sống, chạy lại lần sau sẽ ra kết quả khác.

## Ghi chú

**Hoàn thành 2026-08-31.** Lệnh: `php artisan lacdoi:import-wikidata --fetch`.

**Hai lỗi đã mắc và sửa khi viết truy vấn — ghi lại để không lặp:**

1. **Sai Q-ID của "ẩm thực Việt Nam".** Đoán Q1968435 → truy vấn trả **0 dòng** mà không có lỗi
   gì. ID đúng là **Q826059**, xác minh bằng `wbsearchentities` của Wikidata API. Bài học: xác
   minh Q-ID bằng API trước, đừng viết từ trí nhớ (đúng tinh thần RESEARCH-PROTOCOL).
2. **P2012 (`cuisine`) cũng gắn cho NHÀ HÀNG.** Truy vấn không lọc lớp trả về 82 kết quả lẫn
   Alkemē, Bambu, Banana Tree, "Duyen Vietnamese Homecooking"… Phải thêm ràng buộc
   `?dish wdt:P31/wdt:P279* ?class` với `?class ∈ {Q746549 dish, Q2095 food, Q19861951 type of
   food or dish}`. Sau khi lọc còn 33 món sạch.

**Mở rộng có chủ đích:** thêm nhánh `UNION { ?dish wdt:P495 wd:Q881 }` (country of origin = Việt
Nam) vì nhiều món Việt trên Wikidata không có P2012 — tổng lên **94 kết quả**, sinh ra 61 file
món (33 bị bỏ: thiếu nhãn người đọc được, hoặc trùng slug sau khi chuẩn hoá).

**Kỳ vọng trong "Bối cảnh" bên trên vẫn đúng về bản chất:** đây là **mồi**, không phải nguồn
chính. 61 món đều là vỏ rỗng — không vùng miền, không bữa phù hợp, không cách chế biến, không
định lượng. Người biên soạn phải bổ sung trước khi chuyển `published`. Gợi ý nguyên liệu từ
Wikidata (tiếng Anh) được ghi vào `import_note` để tham khảo, **chưa ánh xạ** sang
`data/ingredients/`.
