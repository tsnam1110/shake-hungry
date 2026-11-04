# Hệ thiết kế Lắc Đói (v2)

**Trạng thái:** chuẩn trình bày chính thức của dự án, chốt cùng bản mẫu v2 ngày 2026-10-31 (ticket UI-004, hướng Apple HIG).
**Nguồn sự thật:** tài liệu này. Hiện thân bằng code là `resources/css/app.css` (ghi chú đầu file: "Hệ thiết kế v2") và các trang chuẩn dưới đây.
**Quy tắc của thư mục docs:** code phải khớp tài liệu; khi lệch nhau thì một trong hai là sai và phải sửa ngay, không để tồn tại song song. Danh sách lệch chuẩn còn tồn tại xem §10 — chúng là việc cần sửa, không phải ngoại lệ được phép.

## 1. Trang chuẩn

Ba trang chi tiết sau là chuẩn mực để đối chiếu mọi trang khác. Trang nào không thuộc ba trang này (nước uống, rau theo mùa, hồ sơ, nhật ký, trang lắc) phải khớp với chuẩn này, không tự đặt chuẩn riêng:

| Trang chuẩn | File | Vai trò trong hệ thiết kế |
|---|---|---|
| Món ăn | `resources/views/dish.blade.php` | Chuẩn chính cho trang chi tiết: thứ tự khối, thẻ dinh dưỡng, hàng nguyên liệu, cách làm |
| Nguyên liệu | `resources/views/ingredient.blade.php` | Chuẩn cho hồ sơ thành phần: ảnh đầu trang, phân loại, thành phần cấu thành, món liên quan |
| Hoa quả | `resources/views/fruit.blade.php` | Chuẩn cho dữ liệu theo mùa: thanh mùa vụ 12 tháng, bảng giá theo miền, cách chọn và bảo quản |

Danh sách tương ứng (`explore.blade.php` cho món, `ingredients.blade.php`, `fruits.blade.php`) là chuẩn cho thẻ danh sách `ld-dcard` (§7).

## 2. Nguyên tắc thiết kế

Áp dụng skill frontend-design, bản lưu trong repo tại `docs/09-standards/frontend-design/SKILL.md` (bản "Single Accent Edition", nền tảng là skill gốc của Anthropic tại https://github.com/anthropics/skills/blob/main/skills/frontend-design/SKILL.md, xem ghi chú thích ứng trong `docs/09-standards/frontend-design/README.md`), cho đúng chất liệu của dự án này, không áp dụng máy móc. Lắc Đói là thư viện thực phẩm Việt Nam để tra cứu trước khi ăn và lắc món — người dùng cần đọc nhanh, tin được con số, và tìm lại được thông tin. Mọi quyết định dưới đây đều phục vụ ba việc đó.

1. **Gắn với chất liệu, không theo mẫu chung.** Không dùng các dấu hiệu mẫu chung mà skill cảnh báo: nền kem + serif + terracotta, nền gần đen + accent chói, bố cục báo chí hairline không bo góc, bộ thẻ SaaS giống nhau với bóng mềm và gradient trang trí, nhãn ALL-CAPS có tracking, nhãn kiểu `TỪ — đoạn giải thích`. Dấu `·` chỉ dùng làm dấu phân tách trong siêu dữ liệu có cấu trúc (phụ đề "vùng · cách làm · khẩu phần", danh sách tên gọi khác), không dùng làm trang trí văn bản. Lắc Đói dùng nền xám nhạt phẳng, chữ sans, một màu hành động — đó là lựa chọn có chủ đích, không phải mặc định bỏ trống.
2. **Dồn điểm nhấn vào một chỗ.** Mỗi trang chi tiết chỉ có một điểm đáng nhớ: số kcal hero màu đỏ năng lượng (§6) đặt cạnh ảnh hero. Mọi thứ còn lại giữ yên, phẳng, xám trắng. Trước khi thêm một điểm nhấn mới, phải bỏ một điểm nhấn cũ (quy tắc "soi gương, tháo một món đồ" của skill).
3. **Cấu trúc mang thông tin, không trang trí.** Màu huy hiệu (§5) mã hoá ngữ nghĩa: xanh lá là tích cực, cam là giá trị, xanh dương là thông tin, vàng là chưa kiểm định, đỏ là nguy hiểm. Đổi màu một huy hiệu là đổi ý nghĩa của nó, không phải đổi phong cách.
4. **Đánh số chỉ khi nội dung thật sự là trình tự.** Chỉ các bước làm / bước pha mới được đánh số tròn. Không đánh số trang trí `01 / 02 / 03` cho các khối nội dung.
5. **Chuyển động chỉ trả lời thao tác.** Được phép: hiệu ứng lắc (quay số, nảy chốt, tia lửa), mở sheet phản hồi / chi tiết tính vị, toast xác nhận, chuyển trạng thái nút và công tắc. Không được: hiệu ứng xuất hiện fade-and-slide cho từng khối, hiệu ứng hover cho mọi thẻ. Mọi chuyển động trang trí đều tắt khi `prefers-reduced-motion: reduce`.
6. **Chữ viết là một phần của thiết kế.** Tiếng Việt, câu thường (không viết hoa toàn bộ nhãn), động từ chủ động, mỗi đoạn chữ làm đúng một việc: nút nói rõ hành động sẽ xảy ra ("Đã ăn quả này" → sau khi bấm thành "Đã ghi vào thống kê"), trạng thái trống hoặc thiếu dữ liệu nói rõ hướng xử lý thay vì xin lỗi chung chung. Tuyệt đối không bịa số và không bao giờ kết luận an toàn tuyệt đối (xem §8).

## 3. Màu sắc — token

Toàn bộ màu nằm trong `@theme` của `resources/css/app.css`, dùng tiền tố `--color-*`. Tailwind sinh utility từ cùng token này (`bg-paper`, `text-ink`, …), nhưng component của dự án ưu tiên class `ld-*` (§6), utility Tailwind chỉ dùng cho bố cục (lưới, flex, khoảng cách).

### 3.1. Bảng màu cốt lõi (4–6 màu theo yêu cầu của skill, mở rộng có kiểm soát)

| Token | Giá trị | Vai trò |
|---|---|---|
| `--color-paper` | `#f5f5f7` | Nền trang |
| `--color-surface` | `#ffffff` | Mặt thẻ, mặt nhóm |
| `--color-surface2` | `#fbfbfd` | Mặt trong ô chỉ số (`ld-macro`) |
| `--color-ink` | `#1d1d1f` | Chữ chính |
| `--color-accent` | `#0071e3` | Màu hành động duy nhất (nút chính, vòng số bước làm, trạng thái active, focus) |
| `--color-mint` | `#34c759` | Trạng thái bật (công tắc), vụ chính trên thanh mùa vụ |

### 3.2. Màu chữ phụ và đường kẻ

| Token | Giá trị | Vai trò |
|---|---|---|
| `--color-sub` | `#6e6e73` | Chữ phụ (phụ đề, nhãn khối dinh dưỡng) |
| `--color-faint` | `#86868b` | Chữ mờ (ghi chú `ld-note`, chú thích ảnh, số liệu phụ) |
| `--color-line` | `#e3e3e6` | Đường kẻ chia hàng, viền khối |
| `--color-fill` | `#e8e8ed` | Nền huy hiệu trung tính, nền nút xám, nền ảnh chờ |
| `--color-link` | `#0066cc` | Màu link trong nội dung (khác màu accent của nút) |
| `--color-accent-hover` | `#0077ed` | Nút chính khi hover |

### 3.3. Màu ngữ nghĩa (chỉ dùng đúng ý nghĩa đã ghi, xem §5)

| Token | Giá trị | Ý nghĩa |
|---|---|---|
| `--color-mint-dark` | `#248a3d` | Tích cực (vụ chính, chay, độ tin cậy cao) |
| `--color-mint-tint` | `#e4f8ea` | Nền của màu tích cực |
| `--color-energy` | `#c93400` | Số kcal hero, chân thẻ kcal, giá trị "hời" |
| `--color-sun` | `#ff9500` | Giá hời nhất trên hàng mùa vụ |
| `--color-sun-tint` | `#fff1de` | Nền của màu "hời" và độ tin cậy thấp |
| `--color-danger` | `#d70015` | Nguy hiểm: dị ứng, 18+, thiếu số |
| `--color-danger-bright` | `#ff3b30` | Chip dị ứng đang chọn |
| `--color-danger-tint` | `#ffe9e7` | Nền của màu nguy hiểm |
| `--color-warn-bg` | `#fff8e5` | Nền cảnh báo "chưa kiểm định" |
| `--color-warn-ink` | `#7a5200` | Chữ cảnh báo "chưa kiểm định" |
| `--color-blue-tint` | `#e8f1fd` | Nền thông tin (mức kết hợp, bổ nhất) |
| `--color-blue-dark` | `#0058b3` | Chữ thông tin |
| `--color-purple-tint` / `--color-purple-dark` | `#f0eafb` / `#5e3ca8` | Ô tile tím (`ld-tile.purple`), chỉ dùng trong các ô biểu tượng nhỏ |

**Cấm:** gradient dưới mọi hình thức (trang trí, nền nút, nền thẻ). Bóng chỉ dùng đúng hai mức đã có trong class `ld-card` / `ld-group` / `ld-dcard` (`0 1px 2px rgba(0,0,0,.03), 0 10px 30px rgba(0,0,0,.045)`), không tự đặt bóng mới.

## 4. Chữ

- **Họ chữ:** Inter 400–800 qua Google Fonts, dự phòng `-apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Segoe UI', Roboto, 'Helvetica Neue', Arial, ui-sans-serif, system-ui, sans-serif. Một họ chữ duy nhất cho cả tiêu đề và nội dung (theo skill: một hoặc hai họ, và nếu hai thì phải khác biệt rõ ràng — dự án chọn một).
- **Thang chữ** (class `ld-*` trong `app.css`):

| Thành phần | Cỡ / đậm / giãn chữ |
|---|---|
| Tiêu đề trang `ld-title` | 34px (≥1024px: 38px), 800, -0.022em, cao dòng 1.15 |
| Tiêu đề khối `ld-section-title` | 21px, 700, -0.015em, cách trên 30px, cách dưới 12px |
| Phụ đề `ld-subtitle` | 16.5px, màu `sub` |
| Chữ trong thẻ / hàng `ld-row` | 15.5px; chữ phụ `.sub` và ghi chú `ld-note`: 13.5px, màu `faint` |
| Nhãn khối `ld-group-label` | 13.5px, 600, màu `sub` |
| Huy hiệu `ld-badge` | 12px, 700 |
| Capsule độ tin cậy `ld-capsule` | 12.5px, 600 |
| Số kcal hero `ld-kcal-so` | 33px, 800, -0.02em, màu `energy` |
| Số trong ô chỉ số `ld-macro .gt` | 17.5px, 700; nhãn `.nhan`: 13px, màu `faint` |
| Tiêu đề thẻ danh sách `ld-dcard-title` | 15.5px, 700, cao 20px, cắt một dòng; dòng meta: 13px, màu `sub`, cao 18px |
| Điều hướng sidebar `ld-nav-item` | 15px, 500; thanh tab mobile: 10.5px, 600 |
| Chữ ngày `ld-date` | 14.5px, 600, màu `faint` |

- **Số liệu:** luôn kèm class `ld-tnum` (tabular-nums) để các cột số thẳng hàng.
- **Độ dài dòng:** nội dung giới hạn trong `ld-page` tối đa 820px (`ld-page-wide` 980px cho trang cần rộng), tương đương giới hạn dưới 80 ký tự mỗi dòng mà skill khuyến nghị.
- **Viết hoa:** không viết hoa toàn bộ nhãn. Ngoại lệ duy nhất đã tồn tại là thẻ "Món của ngày" trên trang lắc (`ld-dotd-tag`, uppercase, giãn 0.06em, màu `mint-dark`) — không nhân rộng mẫu này sang trang khác.

## 5. Huy hiệu — ngữ nghĩa màu (bắt buộc)

Huy hiệu là nơi lệch màu dễ thấy nhất giữa các trang, vì vậy bảng này là luật, không phải gợi ý. Cùng một ý nghĩa ở mọi trang phải cùng một màu.

| Huy hiệu | Class / màu | Dùng khi |
|---|---|---|
| Trung tính | `ld-badge` mặc định (`fill` / `sub`) | Phân loại không mang đánh giá: tên thẻ, nhóm, loại, vùng, "suy diễn" |
| Tích cực | `ld-badge vu` hoặc `ld-badge chay` (`mint-tint` / `mint-dark`) | Ô ăn (Sáng/Trưa/Chiều/Tối/Ăn vặt), "Đang vụ chính", "Chay", thẻ phân loại nguyên liệu |
| Giá trị | `ld-badge hoi` (`sun-tint` / `energy`) | "Hời nhất tháng này" |
| Thông tin tốt nhất | `ld-badge bo` (`blue-tint` / `blue-dark`) | "Bổ nhất lúc này" |
| Chưa kiểm định | `ld-badge unv` (`warn-bg` / `warn-ink`) | "Số chưa kiểm định", "Chưa kiểm định" — mọi nơi có dữ liệu chưa qua kiểm định |
| Dị ứng chắc chắn | Nền `danger-tint`, chữ `danger`, tiền tố "Dị ứng: " | Nhãn dị ứng suy từ thành phần chắc chắn |
| Dị ứng có thể có | Nền trong suốt, viền 1px `danger`, chữ `danger`, tiền tố "Có thể có: " | Dị ứng chỉ nằm ở nguyên liệu tuỳ chọn (chỉ trang món hiện có trường hợp này) |
| Mức kết hợp | Cặp xanh dương `blue-tint` / `blue-dark` | Nhãn mức của cặp kết hợp cần lưu ý (trang quả và nguyên liệu đang dùng đúng cặp này) |
| Nguy hiểm khác | Nền `danger-tint`, chữ `danger` | "18+", "Chưa có số — chưa tính" (nguyên liệu thiếu số trong món) |

Độ tin cậy của con số dùng `ld-capsule`, không dùng `ld-badge`: cao = nền `mint-tint` / chữ `mint-dark` (class mặc định, nhãn "Tin cậy cao"); trung bình = nền `#fff4d6` / chữ `warn-ink` (class `medium`, nhãn "Tin cậy trung bình"); thấp = nền `sun-tint` / chữ `energy` (class `low`, nhãn "Tin cậy thấp · Số tham khảo").

## 6. Component

Tất cả component dùng tiền tố `ld-` để không đụng utility Tailwind. Danh sách dưới đây ghi đúng giá trị đang có trong `app.css`; thêm component mới phải thêm vào đây trước khi viết code.

- **Vỏ ứng dụng** (`layouts/app.blade.php`): `ld-shell` — 1 cột trên mobile, 268px sidebar + 1 cột nội dung từ 1024px trở lên. Sidebar chỉ hiện từ 1024px: logo 38px nền `mint-dark`, tên 19px/700, nhóm điều hướng "Gợi ý" / "Của bạn", mục đang chọn nền `fill` + icon màu `accent`. Mobile dùng `ld-topbar` (dính trên, nền `paper` mờ blur 20px) và `ld-tabbar` cố định dưới (5 mục, mục chọn màu `accent`). Nội dung trong `ld-main` (padding mobile 8px 18px 110px, desktop 26px 34px 60px). Chân trang `ld-footer` gồm link điều khoản / bảo mật / miễn trừ / nguồn dữ liệu và đoạn ghi chú: số calo là khoảng ước tính kèm độ tin cậy, món chưa đủ dữ liệu sẽ không hiện số.
- **Thẻ `ld-card`**: nền `surface`, bo 20px, padding 20px, bóng chuẩn §3.3. **Nhóm `ld-group`**: nền `surface`, bo 18px, tràn ẩn, bóng chuẩn; hàng `ld-row` bên trong padding 12.5px 16px, cỡ 15.5px, đường chia màu `line` chừa trái 16px (hàng có icon thì chừa 60px, class `sep-icon`), hover nền `#fafafc`. Hàng gọn `ld-row-compact`: padding 9px 12px, cỡ 14.5px. Mũi tên phải dùng `ld-chev` (màu `#c7c7cc`).
- **Ảnh nhỏ `ld-thumb`**: 48×48px, bo 10px, `object-fit: cover`, nền `fill` khi chờ ảnh. Dùng trong hàng nguyên liệu của trang món và mọi danh sách nhỏ tương tự.
- **Ô chỉ số `ld-macro`**: nền `surface2`, viền 1px `rgba(0,0,0,.035)`, bo 14px, padding 11px 13px; số 17.5px/700, nhãn 13px/màu `faint`. Lưới macro trên trang chi tiết: 2 cột mobile, 4 cột từ `sm` trở lên, khoảng cách 10px. Giá trị thiếu hiện `?` (chuẩn trang món — xem lệch §10.5).
- **Cảnh báo `ld-warn`**: nền `warn-bg`, chữ `warn-ink`, bo 12px, padding 11px 13px, cỡ 14px/500. Dùng cho: chưa kiểm định, lưu ý an toàn thực phẩm, đồ uống có cồn, món đang biên soạn. Không dùng `ld-warn` cho dị ứng (dị ứng dùng huy hiệu đỏ §5).
- **Nút `ld-btn`**: bo tròn 980px, đậm 600, chuyển nền 0.15s. Nút chính `ld-btn-primary`: nền `accent`, chữ trắng, cỡ 16.5px, padding 13px 24px, hover `accent-hover`. Nút phụ `ld-btn-gray`: nền `fill`, chữ `ink`, hover `#dfdfe4`. Nút bị vô hiệu: mờ 0.55. Hàng thao tác trên trang chi tiết dùng nút xám với padding 11px 20px, cỡ 15px. Tim yêu thích: `ld-heart` (thẻ) và `ld-fav-btn` (nút) — chưa chọn màu `#aeaeb2`, đã chọn màu `#ff2d55` kèm fill.
- **Chip và đoạn `ld-chip` / `ld-seg` / `ld-switch` / `ld-search` / `ld-input`**: chip bo tròn, nền `fill`, đang chọn nền `ink` chữ trắng (chip dị ứng đang chọn nền `danger-bright`); đoạn `ld-seg` nền `#e9e9ec`, mục chọn nền trắng + bóng nhẹ; công tắc 51×31px, bật nền `mint`, núm trượt 20px; ô tìm kiếm nền `rgba(120,120,128,.12)`, bo 11px; ô nhập viền `line`, bo 12px, focus viền accent 2.5px.
- **Focus**: mọi nút, link, ô nhập khi focus bằng bàn phím đều có viền `accent` 2.5px, cách 2px (ô nhập cách 1px). Không được bỏ focus để "cho đẹp".
- **Toast `ld-toast`**: nền `ink`, chữ trắng, bo tròn, hiện 2600ms, dưới cùng trên mobile (96px) và desktop (34px).
- **Sheet `ld-sheet`**: hộp thoại dưới lên trên mobile (bo trên 22px), giữa màn hình từ 640px (bo 22px bốn góc), nền tối `rgba(0,0,0,.42)`. Dùng cho gửi phản hồi và chi tiết tính vị.
- **Tính vị `ld-tcm-lines`**: hệ con riêng, không thuộc bảng màu chung. Màu và icon của từng giá trị tính / vị / hành nằm trong một bảng duy nhất là `window.ldTcm` ở `layouts/app.blade.php` (tính theo dải lạnh → nóng, vị theo hành tương ứng, hành theo 5 màu ngũ hành). Trang chi tiết chỉ hiện giá trị chính kèm nút "Nguồn & chi tiết" mở sheet; chi tiết đầy đủ nằm trong `<template id="tcm-detail">`. Chỉ dựng khối này khi cờ `ldShowTcm` bật.
- **Thanh mùa vụ `ld-seasonbar`**: 12 ô, cao 6px, bo 3px; ô thường nền `fill`, ô có hàng màu `#bfe8cb` (xem lệch §10.4), ô vụ chính nền `mint`. Hàng mùa vụ chi tiết trên trang quả dùng cùng quy ước màu (xem §9).

## 7. Thẻ danh sách `ld-dcard` (chuẩn UI-012)

Món ăn, hoa quả, nguyên liệu dùng chung một khung thẻ. Quy tắc quan trọng nhất của khung này: **mọi dòng nội dung đều luôn được render, kể cả khi rỗng, với chiều cao cố định** — mọi thẻ cao bằng nhau, vị trí nội dung không bao giờ xê dịch vì nội dung dài hay ngắn.

- Khung: `display: flex; flex-direction: column; height: 100%`, nền `surface`, bo 20px, tràn ẩn, bóng chuẩn §3.3; hover bóng đậm hơn một mức. Lưới: `ld-grid` 2 cột, 3 cột từ 1024px; lưới nguyên liệu `ld-grid-ing` 2 / 3 / 4 cột (640px / 1024px).
- Ảnh: `ld-dcard-img`, tỉ lệ 4/3, `object-fit: cover`, nền `fill`; ảnh thật thay 1-1 cho ảnh mẫu SVG, kèm `srcset` 400w/800w cho thẻ danh sách và ảnh gốc cho trang chi tiết. Chưa có ảnh thật thì dùng placeholder của đúng loại (món / quả / nguyên liệu / nước uống), không dùng ảnh của loại khác.
- Thân thẻ: `ld-dcard-body`, padding 12px 14px 14px, flex cột, co giãn. Tiêu đề, dòng meta, hàng huy hiệu đều có chiều cao cố định và cắt một dòng bằng ellipsis (tiêu đề 20px, meta 18px, hàng huy hiệu 24px, cách trên 8px).
- Chân thẻ: `ld-dcard-foot`, `margin-top: auto`, cao tối thiểu 24px, hai đầu: số kcal màu `energy` (thiếu dữ liệu thì class `none`, màu `faint`, cỡ 14px/600, chữ "Chưa đủ dữ liệu") và độ tin cậy 12px/màu `faint`; thẻ theo mùa thay chân kcal bằng `ld-seasonbar`.
- Tim yêu thích: `ld-heart` tuyệt đối ở góc trên phải (10px), 36px tròn, nền trắng 94% + bóng, chưa chọn `#aeaeb2`, đã chọn `#ff2d55`.
- Huy hiệu trên thẻ tuân thủ §5, không tự đặt màu riêng cho thẻ.

## 8. Trạng thái trống, thiếu và lỗi (chuẩn viết)

- **Thiếu số dinh dưỡng:** không đoán số. Câu chuẩn: "Chưa đủ dữ liệu dinh dưỡng cho món này — hệ thống không hiện số thay vì đoán." (trang món / nguyên liệu) và "Chưa có số dinh dưỡng đạt chuẩn cho quả này — hệ thống không đoán số." (trang quả). Trên thẻ danh sách: "Chưa đủ dữ liệu" kèm "không hiện số".
- **Thiếu một phần:** ghi rõ phần thiếu và cách xử lý: nguyên liệu thiếu số gắn huy hiệu "Chưa có số — chưa tính" trong hàng nguyên liệu; nguyên liệu chưa kiểm định gắn huy hiệu `unv`; khoảng kcal được nới cận trên theo tỉ lệ thiếu và ghi chú rõ điều đó.
- **Không có dị ứng ghi nhận:** không bao giờ viết "an toàn". Câu chuẩn: "Dữ liệu hiện tại chưa ghi nhận chất gây dị ứng phổ biến trong món này. Điều này không phải cam kết an toàn tuyệt đối — nếu bạn bị dị ứng, hãy kiểm tra kỹ thành phần." kèm link "Đọc Miễn trừ y tế". Nếu chỉ suy được một phần dòng nguyên liệu thì viết "chưa đủ dữ kiện", không kết luận.
- **Tải lỗi:** câu ngắn, nói rõ đối tượng: "Không tải được món này." / "Không tải được hồ sơ quả." / "Không tìm thấy nguyên liệu" kèm phụ đề "Nguyên liệu này chưa có trong kho dữ liệu."
- **Minh bạch nguồn:** mọi con số đều có nguồn đi kèm ngay tại trang: khối "Nguồn gốc con số" của trang món (bảng thành phần, phiên bản engine, ngày tính), nguồn ghi chú văn hoá, nguồn và ngày thu thập của bảng giá quả. Không có con số nào đứng một mình.

## 9. Bố cục trang chi tiết chuẩn

Thứ tự khối dưới đây là chuẩn. Khối nào không có dữ liệu thì **bỏ cả khối**, không hiện khối rỗng (ngoại trừ thẻ dinh dưỡng và thẻ dị ứng, hai khối luôn hiện để nói rõ trạng thái thiếu như §8).

### 9.1. Trang món — thứ tự chuẩn

1. Link quay lại (chevron trái + chữ, màu `link`, 16.5px/500, padding trên 10px): về Thư viện.
2. Tiêu đề `ld-title`, dòng tên gọi khác (màu `faint`), phụ đề (vùng · cách làm · khẩu phần).
3. Hàng huy hiệu (khoảng cách 6px, cách trên 12px): ô ăn `.vu`, các thẻ khác trung tính.
4. Hộp `ld-warn` khi có: mức đặc biệt, lưu ý an toàn thực phẩm.
5. Ảnh hero: `figure`, cách trên 18px, ảnh 16/10, bo 18px, chú thích 12.5px/màu `faint` ("Ảnh minh họa" nếu là ảnh AI, ngược lại "Ảnh: …").
6. Nhóm biến thể / "Biến thể của" (nếu có): `ld-group` chứa các hàng link tới món gốc hoặc các biến thể.
7. Thẻ dinh dưỡng `ld-card` (cách trên 18px): nhãn "Năng lượng mỗi khẩu phần", số kcal hero + capsule độ tin cậy, lưới macro 4 ô (Đạm / Béo / Bột đường / Chất xơ), hộp `ld-warn` khi có nguyên liệu chưa kiểm định.
8. Hàng thao tác (cách trên 14px, khoảng cách 10px): nút xám "Yêu thích món này" (tim) + "Gửi phản hồi".
9. "Thông tin dị ứng": tiêu đề khối + `ld-card` (huy hiệu §5 + ghi chú + link miễn trừ).
10. "Kết hợp cần lưu ý trong món này": thẻ chứa các cặp + tuyên bố miễn trừ; kiêng kỵ dân gian (nếu có) đặt trong `<details>` riêng, cùng vị trí.
11. Thẻ tính vị (nếu có và cờ bật): `ld-tcm-lines` + nút "Nguồn & chi tiết".
12. "Ghi chú văn hoá": tiêu đề khối + `ld-card` (nội dung 15.5px + nguồn).
13. "Nguồn gốc con số": tiêu đề khối + `ld-group` (bảng thành phần · phiên bản engine · ngày tính) + ghi chú minh bạch.
14. "Nguyên liệu · một khẩu phần": tiêu đề khối + `ld-group` các hàng `ld-row-compact` có `ld-thumb`, tên đậm, dòng phụ (số lượng · đơn vị · ghi chú · tuỳ chọn / phần bỏ đi), huy hiệu thiếu số / chưa kiểm định, chevron phải.
15. "Cách làm": tiêu đề khối + `ld-group` các hàng có vòng số tròn 26px nền `accent` chữ trắng.
16. Hộp `ld-warn` cuối trang khi món chưa duyệt: "Món đang trong giai đoạn biên soạn dữ liệu — thông tin có thể được cập nhật sau khi duyệt."

### 9.2. Trang quả — điểm khác so với chuẩn món (có lý do)

Thứ tự: quay lại (về "Hoa quả theo mùa") → tiêu đề → tên gọi khác → phụ đề (nhóm · họ · nguồn gốc trồng/nhập) → ảnh hero 16/10 → hàng thao tác (thêm nút chính "Đã ăn quả này" — đây là hành động chính duy nhất của trang quả, nên dùng `ld-btn-primary`, các nút còn lại dùng xám) → "Thông tin dị ứng" → thẻ dinh dưỡng (nhãn "Dinh dưỡng mỗi 100g phần ăn được", kcal/100g, lưới macro, dòng natri + nguồn số liệu + tên khoa học, hộp `ld-warn` khi số chưa kiểm định) → thẻ tính vị → "Kết hợp cần lưu ý" → "Ghi chú văn hoá" → "Mùa vụ trong năm" (thẻ 12 tháng: Có hàng / Vụ chính / Bổ nhất / Giá hời nhất, kèm ghi chú bổ nhất và giá hời) → "Vùng trồng tiêu biểu" (`ld-group`) → "Lưu ý khi kết hợp" (`ld-group`, huy hiệu "Có cơ sở" màu nguy hiểm hoặc "Dân gian — chưa kiểm chứng" màu `unv`) → "Cách chọn quả ngon" và "Bảo quản" (đoạn văn 15.5px, không bọc thẻ) → "Giá tham khảo theo mùa vụ" (bảng trong thẻ, cuộn ngang, tối thiểu 520px, kèm nguồn và ngày thu thập).

Lưu ý: ở trang quả, thẻ dinh dưỡng đứng **sau** thẻ dị ứng, trong khi ở trang món thẻ dinh dưỡng đứng **trước** hàng thao tác và thẻ dị ứng. Đây là điểm khác có chủ đích giữa hai loại trang (quả không có "món" để tính theo khẩu phần mà tính theo 100g, và hành động "Đã ăn" cần lên cao), không phải lỗi cần đồng nhất máy móc. Chuẩn chung là: ảnh hero → hành động → dị ứng → dinh dưỡng → tính vị → kết hợp → văn hoá → dữ liệu riêng của loại.

### 9.3. Trang nguyên liệu — điểm khác so với chuẩn món (có lý do)

Thứ tự: quay lại (về "Nguyên liệu") → ảnh đầu trang (xem §10.6 về tỉ lệ) → tiêu đề → phụ đề (nhóm · tên tiếng Anh · tên khoa học in nghiêng) → tên gọi khác → nút "Gửi phản hồi về nguyên liệu này" → link "Hồ sơ hoa quả đầy đủ" (nếu nguyên liệu là quả) → hộp `ld-warn` khi số chưa kiểm định → thẻ dinh dưỡng (nhãn "Dinh dưỡng trong 100g phần ăn được", kcal hero, lưới macro chỉ hiện các chỉ số có số, hàng nguồn con số / độ tin cậy ánh xạ / tra cứu nguồn) → "Thông tin dị ứng" → thẻ tính vị → "Kết hợp cần lưu ý" → "Thành phần cấu thành" (`ld-group` link tới từng nguyên liệu con) → "Phân loại" (nhóm nhãn, huy hiệu `.vu`) → "Món ăn có …" (lưới `ld-grid` thẻ `ld-dcard`, chân thẻ chỉ có kcal hoặc "Chưa đủ dữ liệu").

## 10. Danh sách lệch chuẩn còn tồn tại (việc cần sửa, không phải ngoại lệ)

Các mục dưới đây đã được kiểm chứng trong code ngày 2026-11-03. Sửa chúng theo từng task riêng, commit riêng; không sửa gộp trong một commit tài liệu này.

1. **Trang nước uống, huy hiệu mức kết hợp màu đỏ** (`drink.blade.php`, thẻ kết hợp dùng nền `danger-tint` / chữ `danger`): chuẩn §5 quy định mức kết hợp dùng cặp xanh dương. Sửa thành cặp `blue-tint` / `blue-dark` như trang quả và nguyên liệu.
2. **Biến màu không tồn tại**: `drink.blade.php` dùng `var(--color-border)` cho đường kẻ giữa các cặp kết hợp; `app.css` (`.ld-footer`) dùng `var(--color-sep)` cho đường kẻ chân trang. Cả hai biến đều không có trong `@theme`, nên đường kẻ thực tế không hiện. Sửa thành `var(--color-line)`.
3. **Mã hex cứng thay token, huy hiệu mức kết hợp** (`fruit.blade.php`, `ingredient.blade.php`, `vegetable.blade.php` dùng `background:#eef4fb;color:#0058b3`): token tương ứng là `--color-blue-tint: #e8f1fd` và `--color-blue-dark: #0058b3` — giá trị nền lệch nhau (`#eef4fb` so với `#e8f1fd`). Sửa thành token, không giữ hex.
4. **Mã hex cứng, thanh mùa vụ**: `fruit.blade.php` dùng `#bfe8cb` (ô có hàng) và `#4a90d9` (ô bổ nhất); `app.css` (`.ld-seasonbar i.on`) cũng dùng `#bfe8cb`. Hai giá trị này chưa có token. Việc sửa cần thêm token (ví dụ `--color-season-light` và `--color-season-best`) rồi thay toàn bộ chỗ dùng, không đổi giá trị màu trong lúc thay.
5. **Giá trị thiếu trong lưới macro không thống nhất**: trang món hiện `?`, trang quả hiện `—` (qua hàm `fmt`). Chuẩn là `?` theo trang món. Sửa trang quả về `?`.
6. **Link quay lại của trang nguyên liệu khác chuẩn**: 15px/600, chevron 17×17, cách trên 14px — trong khi trang món và trang quả dùng 16.5px/500, chevron 11×18, padding trên 10px. Thống nhất về mẫu trang món / trang quả.
7. **Ảnh đầu trang nguyên liệu tỉ lệ 16/8 trong thẻ bọc** (`ingredient.blade.php`), khác ảnh hero 16/10 dạng `figure` của trang món và trang quả. Giữ lại như một biến thể đã ghi trong §9.3 cho tới khi có quyết định riêng — không tự ý đổi trong lúc sửa các mục khác.
8. **Trang danh sách nước uống, huy hiệu "Chưa chắc chắn" màu xám** (`drinks.blade.php` dùng `ld-badge` mặc định): chuẩn §5 quy định dữ liệu chưa chắc chắn dùng `ld-badge unv`. Sửa thành `unv`.
9. **Giá trị chưa token hoá (giữ nguyên màu, chỉ chuyển sang token khi sửa)**: nền capsule trung bình `#fff4d6` (khác `warn-bg` `#fff8e5`); tim đã chọn `#ff2d55`; avatar `#7d7aff`; chevron `#c7c7cc`; nền đoạn `#e9e9ec`; nút xám khi hover `#dfdfe4`; hover hàng `#fafafc`. Ghi lại để lần token hoá sau không đổi màu ngoài ý muốn.
10. **Chưa có component dùng chung**: 8 file tự vẽ `ld-dcard` và các trang chi tiết tự lặp lại đoạn JS dùng chung (`esc`, `heartSvg`, chevron, bảng nhãn độ tin cậy). Tài liệu này định nghĩa mẫu, nhưng việc tách thành component Blade dùng chung là một chương trình riêng, không thuộc phạm vi tài liệu này.
11. **Trang lắc (trang chủ) nằm ngoài chuẩn này**: trang lắc dùng thêm màu riêng (tím `#bf5af2`, cam `#ff9f0a`, vàng `#ffd60a` và các màu khay) phục vụ trò chơi lắc và đồng hồ. Không áp các màu đó sang trang tra cứu, và không dùng chuẩn tra cứu để "sửa" trang lắc khi chưa có quyết định riêng.

## 11. Quy trình áp dụng

1. Làm trang hoặc component mới: đọc tài liệu này trước, dùng lại class `ld-*` đã có. Cần mẫu mới thì thêm mẫu vào tài liệu này trước, rồi mới viết code — tài liệu là nguồn sự thật, không phải ghi chú sau.
2. Không viết mã hex trực tiếp trong file view mới. Không dùng biến màu chưa có trong `@theme`. Cần màu mới thì thêm token vào `@theme` kèm ý nghĩa, rồi mới dùng.
3. Không tự đặt màu cho huy hiệu: tra §5. Không có ý nghĩa nào trong §5 thì đề xuất thêm ý nghĩa vào §5 trước khi dùng màu.
4. Kiểm tra trước khi commit (checklist): token đúng chưa; huy hiệu đúng ngữ nghĩa chưa; thứ tự khối đúng §9 chưa; khối rỗng đã bỏ chưa (trừ dinh dưỡng và dị ứng); trạng thái thiếu viết theo §8 chưa; focus bàn phím còn không; `prefers-reduced-motion` có tắt chuyển động trang trí không; trang đã kiểm tra ở cả mobile (tabbar) và desktop (sidebar) chưa.
5. Mọi thay đổi hệ thiết kế đều cập nhật tài liệu này và một dòng ROADMAP trong cùng commit (quy tắc QT-7 của dự án).
