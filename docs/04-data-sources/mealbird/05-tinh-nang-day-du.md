# Nghiên cứu tính năng đầy đủ của Mealbird — để đối chiếu với Lắc Đói

Ngày nghiên cứu: 2026-11-04. Nguồn đọc trực tiếp: trang chủ mealbird.app,
trang máy tính protein, Điều khoản và Chính sách riêng tư (cả hai cập nhật
2026-10-04), App Store Mỹ, AppBrain (trang Google Play). Không đăng nhập,
không vượt paywall, không dịch ngược APK, không lấy dữ liệu trong app.
Vì vậy file này mô tả **tính năng theo công bố**, không khẳng định cách
app cài đặt bên trong, và không chứa công thức món, ảnh hay dữ liệu món
của Mealbird.

## 1. Tổng quan sản phẩm

- Mealbird lập **một tuần ăn** (7 ngày) theo mục tiêu protein và calo của
  người dùng, tính mọi con số từ USDA FoodData Central, và viết danh sách
  đi chợ theo ngân sách. Đây là công cụ lập kế hoạch, **không phải công
  cụ ghi nhật ký ăn uống** — người dùng đặt mục tiêu một lần, app xây
  tuần đạt mục tiêu, không cần cân, quét hay ghi lại sau đó.
- Nền tảng: iPhone / iPad (iOS 17.0+), Android (8.0+). Ngôn ngữ trên trang
  chủ: tiếng Anh, tiếng Hà Lan, tiếng Đức, tiếng Romania; App Store ghi
  thêm tiếng Slovak và tiếng Croatia trong bản mới, và danh sách ngôn ngữ
  App Store còn dài hơn (gồm tiếng Việt trong một bản đọc trước — giữ
  nguyên mâu thuẫn này, không tự chọn).
- Tài khoản: mở app lần đầu tạo tài khoản chỉ bằng ID ngẫu nhiên, không
  bắt buộc tên hay email. Có thể thêm email + mật khẩu (mật khẩu chỉ lưu
  dạng hash) để đăng nhập thiết bị khác, hoặc đăng nhập bằng Apple
  (chỉ xin email, có thể là địa chỉ relay) / Google (chỉ dùng email, nhà
  cung cấp đăng nhập lưu thêm tên và ảnh hồ sơ Google gửi sang nhưng app
  không hiển thị hay dùng tên/ảnh đó).
- Tuổi: từ 13 tuổi trở lên. Không phải thiết bị y tế, không cho lời khuyên
  y tế; mục tiêu chỉ là ước tính chung.

## 2. Thiết lập (onboarding) — app hỏi những gì

Theo trang chủ, App Store và chính sách riêng tư, app hỏi và lưu:

- Mục tiêu (giảm mỡ / duy trì / tăng cơ) và mong muốn từ app.
- Cơ thể: giới tính, tuổi, chiều cao, cân nặng, mức vận động. **Phép tính
  mục tiêu chạy trên điện thoại; chỉ kết quả calo và protein gửi lên
  máy chủ, số đo cơ thể ở lại trên điện thoại.** Quy tắc tương tự cho
  người lớn khác ở cùng bàn.
- Chế độ ăn, dị ứng và nhu cầu ăn kiêng đã chọn, nguyên liệu yêu thích
  và nguyên liệu không bao giờ muốn, món đã tim (heart) và món đã loại,
  kiểu nấu, thiết bị bếp đang có.
- Hộ gia đình: bao nhiêu người lớn và trẻ em đang ăn cùng (trẻ chỉ theo
  nhóm tuổi), mỗi người ăn cùng khẩu phần hay có mục tiêu riêng.
- Ngân sách đi chợ tuần và tiền tệ, quốc gia, cửa hàng, ngày đi chợ,
  ngày và bữa nào cần lập kế hoạch, có ăn vặt không, rào cản thường gặp
  khi ăn uống lành mạnh, ngôn ngữ.
- Người dùng xem được mục tiêu trước khi trả tiền; phần hỏi, mục tiêu
  tính ra và tuần ví dụ đều miễn phí, chỉ việc xây tuần thật mới cần gói
  đăng ký đang hoạt động.

## 3. Công thức mục tiêu — đầy đủ, đã đọc trực tiếp

Xem chi tiết ở `README.md` và file 01/02. Tóm tắt để đối chiếu nhanh:

- Calo: Mifflin–St Jeor (10 × cân kg + 6,25 × cao cm − 5 × tuổi, +5 nam /
  −161 nữ) × hệ số vận động 1,2 / 1,375 / 1,55 / 1,725 / 1,9, rồi giảm
  20% / giữ nguyên / tăng 10% theo mục tiêu, làm tròn 50 kcal.
- Protein: giảm mỡ 1,0 g/lb tính từ cân thấp hơn trong cân thật và cân
  BMI 25; duy trì 0,8 g/lb cân thật; tăng cơ 0,9 g/lb cân thật; làm tròn
  5 g; giới hạn 35% kcal (làm tròn xuống 5 g) kèm sàn (giảm mỡ 0,8 g/lb
  cân BMI 25, còn lại 1,2 g/kg cân thật).
- Người dùng có thể tự nhập số của mình thay cho số app tính; số tự nhập
  vẫn sửa được và áp cho tuần tiếp theo.

**Đối chiếu Lắc Đói:** đây vẫn là quy tắc của Mealbird, chưa phải quyết
định của Benjamin. Lắc Đói hiện dùng khoảng PAL FAO/WHO/UNU 2004, và
Task 1 Tuần ăn chỉ cài một công thức (1 g/lb tại BMI 25, giới hạn 35%).
Có chuyển sang công thức theo mục tiêu của Mealbird hay không vẫn là câu
hỏi mở, cần Benjamin chốt — không tự áp vào code.

## 4. Tuần ăn và bộ kiểm chứng (validator)

- Một tuần gồm sáng / trưa / tối, có thể kèm ăn vặt tùy thiết lập; mỗi
  ngày được kiểm chứng riêng: protein là **sàn từng ngày**, calo theo
  khoảng (band), không lấy trung bình tuần để che ngày thiếu.
- Nếu mục tiêu và ngân sách không còn chỗ cho mọi ngày đều đạt, tuần vẫn
  giao kèm thông báo có bao nhiêu ngày thiếu và thiếu bao nhiêu, và app
  nói là vẫn đang tiếp tục cải thiện trong nền.
- Món ăn: công thức thật, khẩu phần thật, thời gian nấu thật; mỗi nguyên
  liệu ghi tới gram, macro từng bữa, phép tính USDA mở được tới từng
  dòng (từng nguyên liệu × số USDA của nó), kèm ghi chú khi một mẻ nấu
  dùng cho nhiều bữa.
- Lặp lại có chủ đích: tuần điển hình khoảng 11 món cho 21 bữa, khoảng
  7 giờ nấu; app nói trước món nào lặp và tiết kiệm được gì. Người dùng
  chọn được mức lặp mình thích, và chọn "nấu một lần ăn bốn lần".
- Trang chủ của tuần: chào buổi sáng, bữa tiếp theo, số bữa hôm nay,
  kcal và protein hôm nay, chuỗi ngày (streak), tiến độ kcal/protein đã
  ăn so với mục tiêu ngày, tuần từ thứ Năm tới thứ Tư (ngày bắt đầu tuần
  thay được), mỗi ngày đều có dấu kiểm đạt hay chưa.
- Ghi bữa đã ăn (log meal) ở lại trên điện thoại; máy chủ chỉ ghi nhận
  việc đã ghi và độ dài chuỗi, không ghi đã ăn món gì.

## 5. Thay đổi tuần

- Đổi món một bữa, loại một nguyên liệu khỏi tuần, đổi ngày bắt đầu tuần
  hoặc ngày trong tuần (tức thì), bắt đầu lại phần còn lại của tuần.
- Ăn ngoài: đánh dấu bữa ăn ngoài, phần còn lại của ngày giữ nguyên kế
  hoạch; khi đổi món hay ăn ngoài, app cho thấy thay đổi trước khi áp.
- Đổi món và lý do đổi được lưu trên máy chủ; app không tự sửa kế hoạch
  sau lưng người dùng.

## 6. Chế độ ăn, dị ứng, sở thích

- Chế độ: chay (vegetarian), thuần chay (vegan), pescatarian; không thịt
  lợn, không thịt đỏ, low-carb; không gluten, không sữa, không trứng,
  không hạt (nut-free). Trang chủ ghi 12 chất gây dị ứng có thể loại hoàn
  toàn.
- Sở thích: nguyên liệu yêu thích, nguyên liệu không bao giờ muốn, món
  đã tim, món đã loại, kiểu nấu, thiết bị bếp, bữa nào tự nấu.
- Lưu ý: chính sách riêng tư nói một số câu trả lời có thể tiết lộ thông
  tin sức khỏe hoặc tín ngưỡng (ví dụ "diabetic-friendly", "no pork") —
  đây là lý do dữ liệu này được đối xử nhạy cảm trong chia sẻ hộ gia đình
  (xem §8).

## 7. Danh sách đi chợ, ngân sách, gói

- Danh sách sắp xếp theo gian hàng (aisle), đúng gói bán thật ngoài cửa
  hàng: ghi cần mua bao nhiêu gói, cỡ gói nào, và phần thừa dùng được
  bao lâu (ví dụ túi hành 1 kg dùng khoảng 2 tuần).
- Hai con số tách riêng, luôn hiển thị cùng nhau: (a) tiền thức ăn sẽ ăn
  trong tuần — con số đối chiếu ngân sách; (b) tiền phải trả ở quầy —
  tính theo gói nguyên, phần thừa tính cho tuần sau.
- Ngân sách là ràng buộc mềm (xem §4): tuần vẫn giao kèm cảnh báo khi
  không vừa ngân sách.
- Giá luôn gắn nhãn ước tính, không phải giá trực tiếp từ cửa hàng; theo
  trang chủ từ bảng địa phương ở Anh, Ireland, Hà Lan, Bỉ, Romania và Mỹ,
  ngoài đó dùng giá trung bình Mỹ quy đổi; theo Điều khoản thì giá dựa
  trên dữ liệu Mỹ và làm mới định kỳ — giữ nguyên mâu thuẫn phạm vi này.
- Tích danh sách (tick) và mục tự thêm vào danh sách ở lại trên điện
  thoại, trừ khi chia sẻ hộ gia đình.
- Giao hàng: chỉ ở Mỹ, chỉ iPhone, qua DoorDash — nút "Get it delivered"
  chỉ copy danh sách (gồm cỡ gói và giá ước tính) rồi mở DoorDash tại
  cửa hàng người dùng chọn, dán vào ô tìm kiếm; không đặt hàng gì cho
  tới khi người dùng tự thanh toán trong DoorDash, giá kệ và phí của
  DoorDash là của DoorDash. Android chưa có nút giao hàng. Ngoài Mỹ,
  nút chia sẻ gửi danh sách dạng văn bản sang ghi chú / tin nhắn / app
  khác, cả iPhone và Android.
- **Đối chiếu Lắc Đói:** bảng giá và bảng gói đầy đủ của Mealbird không
  được công bố, nên không thể "lấy" hai bảng đó. Tổng tiền thật và quy
  đổi gói thật cho Lắc Đói chỉ code được sau task thu thập giá/gói có
  nguồn ở Việt Nam (đã thu 238 mục trong ngày 2026-11-04, hai kênh Bách
  Hóa Xanh và WinMart — xem task Tuần ăn, không thuộc file này).

## 8. Hộ gia đình — chia sẻ bằng mã, chỉ đọc

- Thêm người ở "Who's eating?": mỗi người có tên (tùy chọn, tối đa 24
  ký tự); người lớn chọn ăn cùng khẩu phần hay có mục tiêu calo/protein
  và mục tiêu riêng; trẻ em chỉ lưu nhóm tuổi và tên tùy chọn. Công thức
  hiển thị khẩu phần của từng người và cả nồi; danh sách đi chợ mua cho
  cả bàn và ghi tên mọi người.
- Chủ bàn tạo một mã hộ gia đình cho từng người, tự gửi mã cho họ. Người
  nhập mã ở màn hình chào ("Have a household code?") trên điện thoại của
  mình sẽ xem được: các tuần gần đây và sắp tới (tối đa 8 tuần), mọi công
  thức theo khẩu phần của mình, danh sách đi chợ, và tích chung danh
  sách với chủ bàn. Người xem **không sửa được** kế hoạch, câu trả lời
  hay đổi món của chủ bàn.
- Mã chỉ lưu dạng hash một chiều có khóa (máy chủ không đọc ngược được;
  điện thoại chủ bàn giữ mã đọc được để hiển thị), kèm người nhận, hạn
  14 ngày, đã dùng hay chưa. Mỗi lần thử dùng mã đều ghi lại (ID tài
  khoản, ID thiết bị, IP, 2 ký tự đầu của mã, kết quả) để chống đoán mã.
- Người xem chỉ nhận đúng những gì màn hình của họ hiển thị: bữa và công
  thức, khẩu phần của mình và của từng người khác ở bàn, danh sách và
  tích của nó, tên người ở bàn (và nhóm tuổi của trẻ), ngân sách tuần và
  chi phí từng tuần, tổng calo/protein của kế hoạch. Họ **không nhận**:
  chế độ ăn, dị ứng, sở thích, nguyên liệu loại trừ, số đo cơ thể, hay
  mục tiêu calo/protein của người khác ở bàn. Họ cũng không thấy email
  của chủ bàn; nếu gói của chủ bàn hết hạn, họ chỉ thấy kế hoạch tạm dừng.
- Chủ bàn xem được trên thẻ Household: điện thoại nào đã tham gia (mẫu
  máy, lần mở app gần nhất) và gỡ bất kỳ điện thoại nào; người xem rời
  đi bất cứ lúc nào.
- Ngoài ra có trang mời qua trình duyệt `mealbird.app/join`: trang này
  chỉ đọc loại trình duyệt để chuyển tới đúng cửa hàng app, link không
  mang theo mã.
- **Đối chiếu Lắc Đói:** Benjamin đã chỉ thị làm chia sẻ hộ gia đình
  bằng mã ngay, và cho phép bỏ qua riêng tư với giá/gói. Mô hình cụ thể
  (xem gì, hạn mã, chỉ đọc hay không) vẫn cần chốt riêng — mô hình mã
  chỉ-đọc của Mealbird và mô hình tài khoản dùng chung của Plan to Eat
  là hai hướng không tương thích (xem file 02).

## 9. Scan the Plate, Chat với Lukas, bảng góp ý, mã creator

- **Scan the Plate — chỉ iPhone và iPad:** chụp hoặc mô tả một bữa, ảnh
  và chữ gửi lên máy chủ rồi chuyển cho nhà cung cấp AI để ước tính.
  Mealbird không giữ ảnh hay chữ; chỉ ghi lại việc quét có thành công
  không, và xóa bản ghi đó sau 30 ngày. Điện thoại giữ lại ước tính và
  một ảnh thu nhỏ. Đây là số ước tính, không phải số đã kiểm chứng như
  số trong tuần ăn.
- **Chat với Lukas:** do người trả lời (Lukas và quản trị viên được ủy
  quyền đọc và trả lời trong hộp thư riêng), không phải chatbot tự động.
  AI (Anthropic) chỉ dịch tin nhắn sang tiếng Anh và gợi ý câu trả lời,
  kèm tóm tắt tài khoản (gói đăng ký, phiên bản app, mẫu máy, quốc gia,
  ngôn ngữ, ngân sách và chi phí tuần hiện tại); người đọc, sửa và gửi
  mọi câu trả lời. Bản dịch tiếng Anh của mỗi tin nhắn giữ 30 ngày.
- **Bảng góp ý công khai ("Help improve Mealbird"):** gợi ý gồm tiêu đề
  tối đa 80 ký tự và mô tả tùy chọn tối đa 1.000 ký tự; người khác bình
  chọn lên/xuống, báo cáo hoặc ẩn. Gợi ý chỉ riêng tư cho tới khi Lukas
  duyệt, sau đó hiển thị cho mọi người dùng bảng (kèm tổng bình chọn,
  trạng thái, câu trả lời của Lukas, đã dịch sang các ngôn ngữ của app)
  mà không kèm tên, ID tài khoản hay chi tiết người viết; không ai thấy
  ai đã bình chọn. Gợi ý bị 3 người khác nhau báo cáo sẽ bị ẩn với mọi
  người cho tới khi Lukas xem; người xem xét mọi báo cáo.
- **Mã creator:** người dùng nhập mã creator để mở khóa; app giới hạn số
  điện thoại một mã mở được, ghi ID thiết bị và IP mỗi lần thử để chống
  lạm dụng. Có cả form đăng ký làm creator (tên, email, WhatsApp, địa
  chỉ bưu điện, quốc gia, tài khoản mạng xã hội, thời gian bắt đầu, ghi
  chú).
- **Đối chiếu Lắc Đói:** theo quyết định đã chốt, Lắc Đói bỏ paywall
  (theo nghĩa không thu phí), Scan the Plate, chat người thật, bảng góp
  ý công khai, SDK quảng cáo và DoorDash. Các mục này ghi ở đây để hiểu
  đầy đủ Mealbird, không phải để làm theo.

## 10. Gói đăng ký, thông báo, quyền, nhà cung cấp

- Gói: $29,99/năm, $9,99/tháng (App Store Mỹ, theo trang chủ ngày
  2026-10-01), không dùng thử, không tầng miễn phí; thanh toán và gia
  hạn tự động qua Apple hoặc Google, hủy trong cài đặt của cửa hàng;
  xóa app không hủy gói; hoàn tiền (nếu có) do Apple/Google xử lý.
  Trước khi trả tiền, người dùng vẫn xem được câu hỏi, mục tiêu tính ra
  và tuần ví dụ.
- Thông báo: chỉ khi người dùng cho phép; thông báo có thể chứa chi tiết
  kế hoạch (tên món, ngân sách) và phần đầu câu trả lời chat; với người
  dùng bảng góp ý hoặc hộ gia đình, thông báo còn báo gợi ý đã chuyển
  trạng thái hoặc tuần mới của hộ đã sẵn.
- Quyền trên điện thoại: iPhone/iPad chỉ xin camera cho Scan the Plate
  (hoặc chọn ảnh qua trình chọn hệ thống, app chỉ nhận đúng ảnh đã
  chọn); cả hai nền tảng chỉ xin thông báo khi người dùng muốn. App
  không dùng vị trí chính xác, danh bạ, micro, Apple Health hay Health
  Connect; không xin theo dõi (tracking) nên không nhận ID quảng cáo.
- Phân tích (analytics): ghi một danh sách cố định sự kiện — tới từng
  bước onboarding, tuần đã sẵn, màn hình chào gói, đăng ký, ghi bữa,
  quét đĩa, yêu cầu đổi món — kèm ID tài khoản, mẫu máy, phiên bản hệ
  điều hành, ngôn ngữ, múi giờ (Android thêm nhà mạng); nhà cung cấp
  phân tích nhận IP và suy ra vị trí gần đúng (thành phố, quốc gia).
  App không ghi màn hình hay thao tác chạm.
- Lỗi / crash: gửi màn hình lỗi, phiên bản app và hệ điều hành, mẫu máy,
  thời gian, kèm ID tài khoản nhưng không kèm câu trả lời hay kế hoạch.
- Nhà cung cấp theo chính sách riêng tư: Supabase (cơ sở dữ liệu, đăng
  nhập), Fly.io, Inngest, Anthropic (dịch/gợi ý chat, và ảnh món qua
  OpenAI/Google theo tài liệu phân tích trước), PostHog (phân tích),
  Superwall (gói đăng ký), Meta/TikTok SDK (chỉ iOS, đo quảng cáo),
  Resend (email), Discord, Vercel/Prisma (website). App không hiển thị
  quảng cáo và không bán dữ liệu cá nhân.
- Liên hệ: info@mealbird.app, Lusson LLC, 30 N Gould St, Sheridan, WY
  82801, Mỹ; luật áp dụng: Wyoming, Mỹ.

## 11. Những gì vẫn chưa biết — không tự điền

- Cách app cài đặt bên trong: framework mobile, API, cơ sở dữ liệu, cách
  AI chọn món, cách khớp USDA, cách xây bảng giá địa phương.
- Danh sách quyền (13) và thư viện (28) cụ thể của app — AppBrain chỉ
  cho số lượng, chi tiết bị paywall.
- Bảng giá và bảng gói đầy đủ của Mealbird; dữ liệu món, công thức và
  ảnh trong app; số liệu kinh doanh (doanh thu, tỉ lệ trả phí).
- Mâu thuẫn snapshot cần giữ nguyên: phiên bản iOS 1.1.17/79,9 MB (đọc
  trực tiếp trước đây) so với 1.1.14/69,2 MB (bản crawl App Store);
  phạm vi bảng giá địa phương (trang chủ liệt kê 6 nước, Điều khoản nói
  chung là dữ liệu Mỹ); danh sách ngôn ngữ (trang chủ 4 ngôn ngữ, App
  Store dài hơn); waitlist (Điều khoản còn mô tả waitlist, chính sách
  riêng tư nói waitlist cũ đã đóng).

## 12. Đối chiếu nhanh với Lắc Đói — dùng để quyết, không phải để code ngay

| Tính năng Mealbird | Lắc Đói hiện có | Hướng làm (cần Benjamin duyệt từng task) |
|---|---|---|
| Mục tiêu protein/kcal, tự nhập hoặc app tính | Hồ sơ có TDEE theo khoảng PAL, Task 1–2 Tuần ăn đã lưu `goal`, protein, kcal và gợi ý | Chốt tỉ lệ kcal theo mục tiêu và công thức protein theo mục tiêu trước khi code tiếp |
| Tuần 7 ngày, kiểm chứng từng ngày | Chưa có mô hình tuần | Task 2.2 trở đi, chỉ món đủ protein + kcal mới vào tuần (quyết định đã chốt) |
| Công thức tới gram, phép tính USDA từng dòng | Món có kcal min/max, protein, nguyên liệu theo gram (QT-1, QT-5) | Giữ nguyên tắc tính từ bảng tham chiếu, không để AI viết số dinh dưỡng |
| Danh sách đi chợ theo gian hàng, theo gói | Chưa có; mới thu giá/gói 238 mục ngày 2026-11-04 | Code quy đổi gói + tổng tiền sau khi có bảng giá/gói có nguồn, kèm nhãn snapshot |
| Ngân sách mềm, tách tiền ăn / tiền quầy | Chưa có | Làm theo nguyên tắc tách hai con số, không hứa ngân sách cứng |
| Hộ gia đình bằng mã, chỉ đọc | Chưa có | Làm theo chỉ thị mã ngay, chốt mô hình xem gì / hạn mã / gỡ thiết bị |
| Đổi món, ăn ngoài, đổi ngày bắt đầu | Trang Lắc có đổi món trong khay (tray-swap) | Mở rộng theo cùng nguyên tắc: cho thấy thay đổi trước khi áp |
| Chế độ ăn, dị ứng, sở thích | Hồ sơ có `diet_type`, `excluded_ingredients`; dị ứng món chưa có (chỉ có dị ứng nước uống) | Cần task dị ứng món riêng trước khi dùng làm bộ lọc cứng |
| Scan the Plate, chat người thật, bảng góp ý, DoorDash, paywall | Không có, và đã quyết bỏ | Không làm theo |
