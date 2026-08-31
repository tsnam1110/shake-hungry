# Definition of Done — Lắc Đói

**Cập nhật lần cuối:** 2026-08-31 — bản đầu tiên

> Một task chỉ được đánh dấu **✅ DONE** khi **tất cả** tiêu chí áp dụng được thoả mãn.
> Đánh DONE khi chưa xong là cách nhanh nhất để người kế nhiệm mất niềm tin vào toàn bộ ROADMAP.

---

## DoD chung — áp dụng cho mọi task

- [ ] Đã làm đúng phần "Việc cần làm" trong file task
- [ ] Không làm hỏng chức năng đã có (chạy lại test cũ)
- [ ] Không hardcode giá trị cấu hình (URL, khoá API, đường dẫn tuyệt đối)
- [ ] Đã cập nhật status + ngày hoàn thành ở **cả** file task **và** [ROADMAP](./ROADMAP.md)
- [ ] Quyết định phát sinh đã ghi vào mục "Ghi chú" của task
- [ ] Nếu đổi thiết kế: tài liệu tương ứng đã sửa **trong cùng commit** (QT-7)

---

## DoD cho task Backend

- [ ] `php artisan migrate` chạy sạch trên database trắng
- [ ] Không có lỗi PHP trong `storage/logs/laravel.log`
- [ ] Có ít nhất một test Pest phủ đường đi chính
- [ ] `vendor\bin\pest --no-coverage` toàn bộ pass
- [ ] Không có truy vấn N+1 ở màn hình danh sách (eager loading)

## DoD cho task Dữ liệu

- [ ] Dữ liệu nằm trong `data/` dưới dạng file, **không chỉ trong database** (QT-3)
- [ ] `php artisan lacdoi:validate-data` không có lỗi 🔴
- [ ] `php artisan migrate:fresh --seed` dựng lại **đúng y hệt** dữ liệu
- [ ] Nguồn của mọi dữ liệu nhập từ ngoài đã ghi vào `data/reference/MANIFEST.json`
- [ ] License của nguồn đã đối chiếu [LICENSE-COMPLIANCE](../04-data-sources/LICENSE-COMPLIANCE.md)
      và ghi vào nhật ký thẩm định §7
- [ ] Không có bản ghi nào còn `needs_review = true` mà đã `published`

## DoD cho task liên quan dinh dưỡng

- [ ] Mọi giá trị dinh dưỡng có `source`, `confidence`, và là **khoảng min–max** (QT-5)
- [ ] Không có bản ghi `dish_nutrition` nào mang `source = 'llm_suggested'` (QT-1)
- [ ] Chỉ dùng **một** `fct_source` cho mỗi phép tính (QT-4)
- [ ] Có `missing_ingredients` thì `confidence` không được là `high`
- [ ] Công thức mới thêm vào engine có **nguồn trích dẫn** trong
      [CALORIE-ENGINE §9](../06-nutrition/CALORIE-ENGINE.md)

## DoD cho task Frontend

- [ ] Hiển thị đúng quy tắc QT-5: không có con số chính xác giả
- [ ] Có trạng thái loading, lỗi, và rỗng
- [ ] Món `confidence = unknown` hiển thị "Chưa đủ dữ liệu", **không hiện số nào**
- [ ] Chạy được trên màn hình điện thoại (360px trở lên)
- [ ] Người dùng xem được nguồn của con số dinh dưỡng (bảng thành phần + danh sách nguyên liệu)

## DoD cho task Quyết định / Thủ tục

- [ ] Quyết định đã ghi vào "Nhật ký quyết định" ở [ROADMAP](./ROADMAP.md), **kèm lý do**
- [ ] Các phương án đã cân nhắc và bị loại cũng được ghi lại — để người sau không lật lại vô ích
- [ ] Tài liệu bị ảnh hưởng đã cập nhật
- [ ] Nếu là thủ tục gửi đi bên ngoài: đã lưu ngày gửi, người nhận, và bản sao nội dung

---

## DoD cho một Giai đoạn

Ngoài DoD của mọi task trong giai đoạn:

- [ ] **Cổng chặn** của giai đoạn (cột trong bảng tổng quan ROADMAP) đã đạt và **đo được**
- [ ] Bảng trạng thái ở [CLAUDE.md §0](../../CLAUDE.md) đã cập nhật
- [ ] `git clone` vào thư mục trắng → `composer install` → `migrate:fresh --seed` → chạy được,
      không thiếu dữ liệu (QT-3)
- [ ] Câu hỏi mở phát sinh đã ghi vào mục "Câu hỏi mở" của ROADMAP

---

## Quy tắc "chưa xong thì nói chưa xong"

Nếu một task **xong một phần**:

- **Không** đánh ✅ DONE.
- Để 🔵 IN PROGRESS, và ghi rõ trong mục "Ghi chú": phần nào xong, phần nào chưa, chưa xong vì sao.
- Nếu phần còn lại đủ lớn, **tách thành task mới** và thêm vào ROADMAP.

Nếu một task **bị chặn**:

- Đánh ⏸️ BLOCKED, ghi rõ **chặn bởi cái gì** (task nào, câu hỏi nào, chờ ai).
- Thêm vào mục "Câu hỏi mở" của ROADMAP nếu chặn bởi một quyết định chưa có.
- Chuyển sang làm phần không phụ thuộc điều đó.
