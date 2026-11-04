# Skill frontend-design trong Lắc Đói

**Nguồn và trạng thái:** bản lưu nguyên vẹn từ file `frontend-design.rar`
Benjamin gửi ngày 2026-11-04. Ba file `SKILL.md`,
`references/accent-palettes.md` và `LICENSE.txt` trong thư mục này
**không được sửa** — mọi thích ứng cho Lắc Đói chỉ ghi ở file README này,
để lần sau đối chiếu với bản gốc không bị lẫn.

**Đây không phải skill gốc Anthropic nguyên bản.** Frontmatter của
`SKILL.md` ghi tên `frontend-design-accent`, mô tả là bản
"Single Accent Edition": nền tảng là skill `frontend-design` của
Anthropic (Apache License 2.0, xem `LICENSE.txt`), cộng thêm hệ
"2 + 1" — nền 2 màu + đúng 1 màu nhấn — chắt lọc từ video của
Hoàng Anh | Website WP (@hoanganh_thrive). Ghi công đầy đủ ở cuối
`SKILL.md` (§ "Sources and credit").

## Khi nào dùng

Khi thiết kế giao diện mới, làm lại một giao diện, chọn bảng màu,
hoặc khi giao diện "đã đẹp nhưng chưa ấn tượng và cần một màu nhấn"
(cụm kích hoạt trong mô tả skill, cả tiếng Anh và tiếng Việt).
Không dùng skill này cho dữ liệu dinh dưỡng, nội dung món hay logic
gợi ý — nó chỉ là hướng dẫn trình bày.

## Thứ tự ưu tiên trong Lắc Đói — DESIGN-SYSTEM.md thắng khi xung đột

`docs/09-standards/DESIGN-SYSTEM.md` là nguồn sự thật về trình bày
của dự án. Skill này là hướng dẫn, xếp dưới nó:

- **Không thêm màu nhấn thứ hai.** Lắc Đói đã có một điểm nhấn duy
  nhất là số kcal hero màu `--color-energy` (`#c93400`, DESIGN-SYSTEM
  §6) và một màu hành động duy nhất `--color-accent` (`#0071e3`).
  Hệ "2 + 1" của skill không được hiểu là thêm một accent mới lên
  trên hai màu đó.
- **Không copy 3 palette mẫu nguyên xi.** Neon hồng `#FF2D95`,
  vàng mù tạt `#EAAA00`, tím lavender `#E6E0F0` trong
  `references/accent-palettes.md` chỉ là ví dụ cho 3 cơ chế
  (neon trên nền tối, màu bổ túc, tint ánh kim). Áp lên Lắc Đói
  mà không qua DESIGN-SYSTEM là sai hướng.
- **Màu ngữ nghĩa là hệ riêng, không phải accent.** Xanh lá = tích
  cực, cam = giá trị, xanh dương = thông tin, vàng = chưa kiểm
  định, đỏ = nguy hiểm (DESIGN-SYSTEM §5). Đổi màu một huy hiệu là
  đổi ý nghĩa của nó, không phải đổi phong cách — skill cũng ghi
  rõ màu ngữ nghĩa (đỏ lỗi, xanh thành công) tách khỏi accent.
- **Các cấm kỵ của DESIGN-SYSTEM vẫn giữ:** cấm gradient dưới mọi
  hình thức, bóng chỉ đúng 2 mức của `ld-card` / `ld-group` /
  `ld-dcard`, không tự đặt bóng mới.

## Những quy tắc của skill áp được trực tiếp

- Mọi quyết định màu viết bằng hex có tên, không chỉ gọi tên màu
  (skill ghi rõ: video gọi `#FF2D95` là "neon red" trong khi swatch
  là hồng nóng — tin hex, không tin tên gọi).
- Đo contrast WCAG cho đúng cặp màu sẽ dùng (4.5:1 cho chữ thường,
  3:1 cho chữ lớn / yếu tố phi văn bản) và ghi tỉ lệ vào kế hoạch
  thiết kế, kể cả các bẫy đã đo sẵn trong `accent-palettes.md`
  (ví dụ `#EAAA00` trên nền sáng `#E5E4E2` chỉ 1.61:1 — không dùng
  làm chữ).
- Ngân sách accent ≤ ~10% diện tích nhìn thấy của một màn hình,
  danh sách vị trí cho phép viết ra trước, không rải khắp nơi.
- Chụp so sánh base-only và base + accent, giữ ảnh base-only làm
  đối chứng: nếu bản chỉ có nền đã hỏng thì lỗi ở nền, không phải
  ở accent; nếu bản có accent không đổi cảm xúc đã đặt tên bằng
  một từ thì accent sai hoặc đặt sai chỗ.
- Một màn hình chỉ một điểm đáng nhớ; còn lại giữ yên, phẳng.
  Chuyển động chỉ trả lời thao tác, tôn trọng
  `prefers-reduced-motion`. Đánh số chỉ khi nội dung thật sự là
  trình tự. Tránh các dấu hiệu mẫu chung skill liệt kê: nền kem +
  serif + terracotta, nền gần đen + accent chói, bố cục báo chí
  hairline không bo góc, bộ thẻ SaaS giống nhau, nhãn ALL-CAPS
  có tracking, nhãn kiểu `TỪ — đoạn giải thích`.
- Chữ viết là một phần của thiết kế: tiếng Việt, câu thường,
  động từ chủ động, nút nói rõ hành động sẽ xảy ra, trạng thái
  trống/thiếu nói rõ hướng xử lý thay vì xin lỗi chung chung.

## Phạm vi của lần cập nhật này

Chỉ lưu skill + ghi chú thích ứng + trỏ link từ DESIGN-SYSTEM.md
sang bản lưu trong repo. **Không đổi code, không đổi `app.css`,
không áp accent lên trang nào.** Áp skill lên một trang cụ thể
là task riêng, cần Benjamin duyệt từng trang, theo đúng quy tắc
làm từng task một của dự án.

## Bản làm việc cho Muse

Ngoài bản trong repo này, còn một bản sao làm việc tại
`~/workspace/custom-skills/frontend-design/` để Muse tìm được qua
tìm skill trong các chat sau. Bản trong repo là nguồn sự thật;
bản sao chỉ để tra cứu nhanh, khi lệch nhau thì theo repo.
