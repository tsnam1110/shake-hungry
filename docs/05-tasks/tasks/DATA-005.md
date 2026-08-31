# [DATA-005] — Nhập món Việt từ Wikidata

**Mã task:** DATA-005 · **Giai đoạn:** GĐ1 · **Loại:** Dữ liệu
**Status:** ⬜ TODO · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** SETUP-003

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

- [ ] Có ≥58 món trong `dishes` với `source = 'imported'`
- [ ] Mọi món đều `status = 'draft'` và `needs_review = true` — **chưa được hiển thị ra ngoài**
- [ ] Tên tiếng Việt và tiếng Anh vào `dish_names` đúng `locale`
- [ ] File thô lưu trong `data/reference/`, MANIFEST ghi ngày truy vấn (Wikidata thay đổi liên tục)
- [ ] Ảnh từ Wikidata **chưa tải về** — chỉ ghi URL nguồn, xử lý sau khi trả lời câu hỏi Q4

## Cạm bẫy

- **Đừng để món nhập tự động ở trạng thái `published`.** Dữ liệu Wikidata thưa và không đồng đều
  (thiếu vùng miền, thiếu bữa phù hợp). Phải qua tay người biên soạn trước.
- **Ảnh Wikidata/Commons có license riêng từng ảnh** — không phải tất cả đều CC0. Kiểm từng ảnh trước
  khi tải về, và ghi vào `data/media/MANIFEST.json` (ràng buộc R4).
- Ghi ngày truy vấn: Wikidata là dữ liệu sống, chạy lại lần sau sẽ ra kết quả khác.

## Ghi chú
