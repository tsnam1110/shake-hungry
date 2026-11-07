# UI-SHAKE-UX-001 — Tối ưu form lắc trang chủ

**Status:** ✅ DONE
**Ngày tạo / bắt đầu / hoàn thành:** 2026-11-07
**Phụ thuộc:** UI-011, REC-003/004, SHAKE-DEVICE-001 (chức năng đang có).
**Phạm vi:** giao diện form lắc và vị trí điều khiển cảm biến; không đổi API,
database, thuật toán gợi ý, số dinh dưỡng hoặc bộ lọc dị ứng.

## Phân tích và thiết kế

Áp dụng [frontend-design-accent](../../09-standards/frontend-design/SKILL.md)
theo yêu cầu dùng skill Design; kế hoạch trước triển khai được ghi ở
[DESIGN-SYSTEM §12](../../09-standards/DESIGN-SYSTEM.md#12-form-lắc-trang-chủ-ui-shake-ux-001-2026-11-07).
Form cũ có đồng hồ quá lớn, nhiều mô tả lặp lại, lựa chọn chưa phân cấp,
kcal hiển thị thường trực và công tắc cảm biến lặp với Hồ sơ.

Giữ Inter/hệ token v2, nền trắng + chữ tối; một accent xanh cho CTA, lựa chọn
và focus. Thứ tự: bữa → chế độ → vùng/ăn chay → dị ứng → kcal tuỳ chọn → Lắc.
Mục dị ứng luôn thấy; kcal nằm trong details nhưng có tóm tắt giá trị đang dùng,
kể cả khi điền từ hồ sơ/TDEE. Không thay đổi ý nghĩa input/payload.

## Thay đổi

- `shake.blade.php`: fieldset/legend, mode hai lựa chọn có mô tả, đồng hồ phụ,
  lưu hồ sơ ngắn gọn, disclaimer dị ứng không hứa an toàn tuyệt đối; giữ ID
  điều khiển để bảo toàn luồng lưu hồ sơ và lắc món/mâm.
- `app.css`: lớp ld-shake-* chỉ tác động form; mobile 360px, touch target 44px,
  focus rõ; màu dùng token, hover CTA dùng link tối hơn.
- Bỏ switch và nút cấp quyền cảm biến khỏi home. Switch chỉ còn ở Hồ sơ.
  Home đọc cùng localStorage, cập nhật qua storage/pageshow; iOS xin quyền
  tối đa một lần trên mỗi lượt mở trang từ click trực tiếp nút Lắc.
  Từ chối/lỗi quyền không ngăn lắc bằng nút. Giữ motion detector/cooldown.
- `profile.blade.php`: cập nhật hướng dẫn vị trí xin quyền, giữ công tắc.

## Kiểm chứng

- `npm ci` và `npm run build`: đạt. Cảnh báo bundle admin lớn vẫn có sẵn;
  không đổi dependency/package-lock, không làm lại admin ngoài phạm vi.
- Render Blade thật của home trước/sau và profile bằng Laravel; dữ liệu slug/
  category nguyên liệu đọc từ data/ vào SQLite memory chỉ phục vụ render preset.
  Không chạy migration/seed trên DB thật; không dùng SQLite kiểm engine.
- Edge headless qua Playwright, 1440px và 360px, API fixtures: không lỗi JS,
  không tràn ngang; mode home hiện đúng số món; payload giữ mode home, 4 món,
  bình dân, ăn chay, region_id, target_kcal và slug dị ứng trứng.
- Hồ sơ vẫn có switch, home không còn switch/nút permission; tắt ở Hồ sơ
  được đọc ở Home. Mô phỏng iOS permission rejection: tắt không xin quyền,
  bật chỉ xin một lần từ trusted click; devicemotion vẫn kích hoạt Lắc.
- Kiểm mục tiêu 650 kcal từ hồ sơ vẫn hiện ở summary khi details đóng;
  thay vùng vẫn lưu ngược hồ sơ; nút Lắc trở lại trạng thái dùng được sau
  phản hồi không có món phù hợp.
- Chụp trước/sau desktop, mobile, home mode và base-only; đã mở ảnh kiểm trực quan.
  Accent làm rõ hành động/lựa chọn, không thay cấu trúc nền.
- Contrast đo: white/accent **4.70:1**, white/link hover **5.57:1**,
  sub/white **5.07:1**, ink/white **16.83:1**. Không thêm màu accent thứ hai.
- `git diff --check`: đạt.

Giới hạn: MySQL local không hoạt động nên chưa chạy API/engine end-to-end hoặc
Pest MySQL; backend không thay đổi. Cấp quyền iPhone kiểm qua API mô phỏng,
chưa xác nhận trên iPhone vật lý. Không push/deploy production trong task này.
