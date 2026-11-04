# Trạng thái REA với Mealbird — cập nhật tới 2026-11-04 19:33

## REA còn hoạt động không?

Có. Bản cài bền trong home vẫn còn nguyên sau các lần thay VM:

- Lệnh: `~/rea/node_modules/.bin/rea`
- Cài bằng: `npm install --prefix ~/rea rea-agents@6.8.0`
- Phiên bản kiểm lúc 2026-11-04 19:33: 6.8.0, MCP khỏe 229/229 công cụ, 6 prompt.
- Lệnh `rea` gõ trực tiếp ngoài PATH thì không thấy — phải dùng đường
  dẫn đầy đủ trong home. Bản cài global (`npm install --global`) đã mất
  2 lần sau thay VM (03/11 và 04/11), nên không dùng lại cách đó.

`rea setup --yes --all-detected` vẫn cho kết quả cũ: Muse không nằm trong
26 client REA hỗ trợ, chỉ cài skill
(`~/.agents/skills/reverse-engineer-anything/`, phiên bản 49), không thêm
công cụ REA vào chat Muse này. Trong chat này REA dùng được qua CLI.

## Các lần cào web bằng REA — đều thất bại, cùng một chỗ

| Lần | Trang | Kết quả |
|---|---|---|
| 2026-11-03 | `mealbird.app/` | Chromium 156 của REA khởi động được, điều hướng thất bại trước khi ghi trạng thái ban đầu, 0 event |
| 2026-11-04 | `mealbird.app/` | Như trên |
| 2026-11-04 | `mealbird.app/protein-calculator` | Như trên |
| 2026-11-04 | CDP: mở sẵn `mealbird.app/` bằng Chromium thường, rồi bảo REA phân tích | Trình duyệt thường tải được trang, REA liệt kê được đúng target, nhưng `analyze-web-bundle`, `capture-web-screenshot`, `discover-webmcp-tools` đều trả `target_not_allowed`, kể cả khi dùng đúng địa chỉ WebSocket cấp trình duyệt |

Kết luận: trang Mealbird không sập, trình duyệt thường vào được, lỗi nằm
ở phía REA với trang này. Cào web bằng REA hiện không phải là đường lấy
dữ liệu Mealbird.

## Dịch ngược APK — cần gì, và đang thiếu gì

REA phân tích APK Android cần đồng thời:

1. File APK của đúng app (`app.mealbird`), từ nguồn đã xác minh.
2. Java 17+ trên máy — hiện máy không có `java`.
3. File jadx-headless-mcp 0.7.1 do người dùng tự cung cấp, đặt biến môi
   trường `REA_JADX_MCP_JAR` trỏ đường dẫn tuyệt đối. REA nêu rõ không
   tự tải file này.

Trạng thái tới 2026-11-04 19:55, sau khi Benjamin chỉ thị "tự cài APK":

- Tìm kiếm lại (APKMirror, APKPure, Google Play, AppBrain) vẫn **không
  thấy mirror APK đã xác minh nào cho `app.mealbird`**. AppBrain ghi
  "APK size 21,74 MB, latest APK version 1.1.17" nhưng AppBrain không
  host file APK — mọi link cài đặt của họ đều dẫn về cửa hàng chính thức.
- Google Play trên web không cho tải file APK trực tiếp; nó chỉ cài vào
  thiết bị đã liên kết với tài khoản.
- Máy này không có điện thoại Android đã ghép nối, không có tài khoản
  Google Play nào được cung cấp, nên không có cách tự cài APK hợp lệ
  trong ngày mà không dùng mirror chưa xác minh (nguy cơ file bị sửa,
  sai phiên bản, hoặc chứa mã độc — không dùng).
- Vì vậy dịch ngược APK **chưa làm được**, không phải vì không thử.
  Muốn làm được, cần một trong hai: (a) Benjamin gửi file APK lấy từ
  chính điện thoại của mình, hoặc (b) cung cấp tài khoản Google Play để
  cài vào máy Android / giả lập, qua kênh an toàn.

## Ranh giới khi dùng dữ liệu Mealbird

Điều khoản của Mealbird (cập nhật 2026-10-04, §7–§8) cấm copy, scrape,
bán lại hoặc phân phối lại Dịch vụ và nội dung của họ, trừ khi điều khoản
cho phép; tên, linh vật, phần mềm, thiết kế và văn bản thuộc Lusson LLC,
chỉ cấp phép sử dụng cá nhân, phi thương mại. Dữ liệu USDA thì thuộc
phạm vi công cộng (public domain) và không bị hạn chế đó.

Vì vậy thư mục này chỉ tóm tắt **sự thật chức năng bằng lời của mình**,
không sao chép văn bản marketing, công thức món, ảnh, code hay dữ liệu
món của Mealbird, và không đưa dữ liệu Mealbird vào `data/` của Lắc Đói.
