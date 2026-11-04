# Mealbird — thư mục dữ liệu tham khảo

Thư mục này lưu **dữ liệu tham khảo về Mealbird** để dùng lâu dài cho dự án
Lắc Đói, ngay cả khi công cụ REA không còn trên máy. Đây là tài liệu tham
khảo, không phải dữ liệu món ăn của Lắc Đói: không đưa gì trong này vào
`data/` (kho món ăn / nguyên liệu / rau / quả / nước uống) nếu chưa qua quy
trình kiểm chứng riêng của dự án (QT-1, QT-5, DATA-PIPELINE).

## Phạm vi và ranh giới — đọc trước khi dùng

- "Clone" ở đây chỉ là **clone chức năng**, không sao chép tài sản trí tuệ:
  không tên, linh vật, văn bản marketing, ảnh, công thức món, code hay dữ
  liệu món độc quyền của Mealbird. Các file trong này tóm tắt sự thật chức
  năng bằng lời của mình, từ trang công khai.
- Mealbird là app của **Lusson LLC**, tên đầy đủ *Mealbird: Macro Meal
  Planner*, gói Android `app.mealbird`, App Store Mỹ id 6794577462. Đừng
  nhầm với Mailbird (app email).
- Mọi con số về Mealbird trong này đều có **ngày và nguồn** trong chính file
  đó. Con số xếp hạng, giá gói, phiên bản thay đổi theo ngày và theo quốc
  gia — không dùng một con số cũ như con số hiện tại mà không kiểm lại.
- Quy tắc của Mealbird **không phải quyết định của Benjamin cho Lắc Đói**,
  trừ khi Benjamin đã chốt riêng. Ví dụ: công thức protein/kcal theo mục
  tiêu của Mealbird, hệ số vận động đơn của họ, ngân sách mềm — tất cả chỉ
  là tham khảo. Lắc Đói dùng khoảng PAL FAO/WHO/UNU 2004, không copy nguyên
  hệ số đơn của họ, và `goal` trong Tuần ăn chỉ được dùng để suy kcal khi
  Benjamin đã chốt tỉ lệ (xem `docs/05-tasks/tasks/WEEK-001.md`).

## Các file trong thư mục

| File | Nội dung | Ngày / nguồn chính |
|---|---|---|
| `01-phan-tich-rea.md` | Phân tích Mealbird bằng REA + tổng hợp từ nguồn chính thức | 2026-11-03, trang chủ / điều khoản / riêng tư (cập nhật 2026-10-04), App Store Mỹ, AppBrain |
| `02-so-sanh-ngan-sach-goi-ho-gia-dinh.md` | So sánh Mealbird với nguồn khác, chỉ 3 mảng ngân sách / gói / hộ gia đình | 2026-11-04, trang công khai, không vượt paywall, không dịch ngược APK |
| `03-ke-hoach-tich-hop-lac-doi.md` | Kế hoạch tích hợp 37 tính năng vào Lắc Đói, chia đã-có / cần-làm / chưa-làm-được-trung-thực | 2026-11-03, đối chiếu code Lắc Đói lúc đó |
| `04-rea-status.md` | Trạng thái REA, cách cài bền, các lần cào thất bại, và thứ cần có để dịch ngược APK thật | Cập nhật tới 2026-11-04 19:55 |
| `05-tinh-nang-day-du.md` | Nghiên cứu tính năng đầy đủ của Mealbird từ nguồn công khai, đối chiếu với Lắc Đói | 2026-11-04, trang chủ / máy tính protein / Điều khoản / Riêng tư / App Store / AppBrain |

Bản gốc của 3 file 01–03 vẫn nằm ở thư mục goal Tuần ăn
(`workspace/goals/mealbird-weekly-plan-integration-for-l-c-i/files/`).
Khi cập nhật nội dung Mealbird trong tương lai, cập nhật **cả hai nơi**
hoặc ghi rõ nơi nào là bản chính, tránh để hai bản lệch nhau mà không ai
biết. Bản trong thư mục này là bản đưa vào repo để dùng lâu dài.

## Tóm tắt nhanh — chỉ dùng làm chỉ mục, chi tiết xem file gốc

- **Tuần ăn:** 7 ngày, protein là sàn từng ngày (không lấy trung bình
  tuần), calo theo khoảng (band); ngày không đạt vẫn giao tuần kèm thông
  báo thiếu bao nhiêu ngày, thiếu bao nhiêu.
- **Công thức mục tiêu của Mealbird** (trang máy tính protein, đọc trực
  tiếp 2026-11-04, chưa phải quyết định của Lắc Đói): calo từ
  Mifflin–St Jeor × hệ số vận động 1,2–1,9, rồi giảm 20% / giữ nguyên /
  tăng 10%, làm tròn 50 kcal. Protein: giảm mỡ 1,0 g/lb tính từ cân thấp
  hơn trong cân thật và cân BMI 25; duy trì 0,8 g/lb cân thật; tăng cơ
  0,9 g/lb cân thật; làm tròn 5 g; giới hạn 35% kcal (làm tròn xuống 5 g)
  kèm sàn (giảm mỡ 0,8 g/lb cân BMI 25, còn lại 1,2 g/kg cân thật).
  1 g/lb = 2,2 g/kg, trên trần ISSN 2,0 và DGA 1,2–1,6 — không trình bày
  như chuẩn y tế phổ quát.
- **Ngân sách / gói:** ngân sách là ràng buộc mềm; hai con số tách riêng —
  tiền thức ăn sẽ ăn trong tuần (đối chiếu ngân sách) và tiền phải trả ở
  quầy (tính theo gói nguyên, phần thừa tính cho tuần sau). Giá luôn gắn
  nhãn ước tính, từ bảng địa phương ở Anh, Ireland, Hà Lan, Bỉ, Romania
  và Mỹ; ngoài đó dùng giá trung bình Mỹ quy đổi. **Bảng giá và bảng gói
  đầy đủ của Mealbird không được công bố** — không thể "lấy" hai bảng đó
  từ trang họ.
- **Hộ gia đình:** hai mô hình không tương thích đã đối chiếu trong file
  02 — mã chỉ-đọc kiểu Mealbird (mã 1 lần, 14 ngày, tối đa 8 tuần, ẩn dị
  ứng / mục tiêu của người khác) và tài khoản dùng chung kiểu Plan to Eat
  (không phân quyền). Mô hình cụ thể cho Lắc Đói vẫn cần Benjamin chốt.
- **Gói đăng ký (App Store Mỹ, 2026-10-01 theo trang chủ):** $9,99/tháng,
  $29,99/năm, không dùng thử, không tầng miễn phí. Tính năng đã quyết bỏ
  ở Lắc Đói: paywall, Scan the Plate, chat người thật, bảng góp ý công
  khai, SDK quảng cáo, DoorDash.
- **Cái chưa có trong thư mục này, cố tình để trống:** bản dịch ngược APK,
  danh sách quyền / thư viện cụ thể của app (AppBrain chỉ cho số lượng:
  13 quyền, 28 thư viện, chi tiết bị paywall), bảng giá / bảng gói đầy đủ
  của Mealbird, dữ liệu món của Mealbird.

## Nếu REA mất — làm lại thế nào

Xem `04-rea-status.md`. Tóm tắt: cài bền vào home, không cài global
(vì cài global đã mất 2 lần sau thay VM); cào web bằng REA hiện thất bại
với mealbird.app trên mọi hướng đã thử; muốn dịch ngược APK thật thì cần
file APK từ chính điện thoại của Benjamin, Java 17+, và file
jadx-headless-mcp 0.7.1 do người dùng tự cung cấp.
