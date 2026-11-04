# Mealbird — Phân tích bằng REA và tổng hợp từ nguồn chính thức

**Ngày phân tích:** 3/11/2026
**Đối tượng:** Mealbird: Macro Meal Planner — app lập kế hoạch bữa ăn theo protein và calo
**Người làm:** Muse, dùng REA (Reverse Engineer Anything) bản 6.8.0
**Cách đọc tài liệu này:** Đọc phần "Tầng bằng chứng" trước. Tài liệu này KHÔNG phải là mã nguồn, bản dịch ngược, hay bản sao nội dung của Mealbird. Nó là tổng hợp từ những gì REA chạy được và từ các trang công khai chính thức của nhà phát triển.

---

## Tầng bằng chứng — đọc trước khi tin bất kỳ con số nào

### Đã kiểm chứng đầy đủ (chạy thật, có đầu ra)

- REA đã cài và chạy được: `rea --version` = 6.8.0, `rea mcp doctor` = healthy, đủ 229 tool và 6 prompt, khớp digest catalog.
- REA skill đã cài: `~/.agents/skills/reverse-engineer-anything/`, phiên bản 49, tool_count 229, trạng thái aligned sau `rea setup --yes --all-detected`.
- REA browser scenario đã khởi động được Chromium 156.0.8078.4 từ Playwright, nhưng navigation tới `https://mealbird.app/` thất bại trước khi ghi nhận trạng thái ban đầu, 0 event được giữ lại. Vì vậy KHÔNG có bằng chứng web-bundle nào từ REA trong tài liệu này.
- Các trang chính thức đã đọc trực tiếp ngày 3/11/2026: mealbird.app (trang chủ), mealbird.app/terms, mealbird.app/privacy, App Store Mỹ, AppBrain (trang Google Play).

### Mới tầm soát / nguồn thứ cấp (không dùng làm bằng chứng mã nguồn)

- AppBrain là trang tổng hợp dữ liệu Google Play, không phải Google Play trực tiếp: số lượt cài, xếp hạng, số quyền (13) và số thư viện (28) lấy từ đây. Danh sách quyền và thư viện cụ thể bị khóa sau paywall, nên tài liệu này KHÔNG liệt kê chúng.
- Các con số xếp hạng và đánh giá thay đổi theo ngày và theo quốc gia. Tài liệu ghi rõ nguồn và thời điểm của từng con số, không gộp thành một điểm số duy nhất.

### Chưa kiểm chứng — cố tình để trống, không đoán

- **Không có bản dịch ngược APK.** Lý do, đã kiểm chứng: máy này không có Java; `rea doctor --provider jadx` báo thiếu JADX; REA yêu cầu JADX headless MCP JAR 0.7.1 do người dùng tự cung cấp và nêu rõ REA không tự tải nó. Ngoài ra không tìm được bản APK từ nguồn đã xác minh (không có APKMirror/APKPure cho `app.mealbird` trong kết quả tìm kiếm; không có thiết bị đã ghép nối để lấy APK từ máy của bạn).
- Vì vậy tài liệu này KHÔNG khẳng định: cấu trúc mã nguồn, tên class, API nội bộ, cơ sở dữ liệu, framework mobile (Flutter/React Native/native), hay công thức protein chính xác từng hệ số. Những gì viết về "cách app làm" chỉ là những gì nhà phát triển tự công bố.
- Website mealbird.app dùng Next.js — suy luận duy nhất về công nghệ, dựa trên các URL ảnh dạng `/_next/image` trong trang đã đọc. Điều này chỉ nói về website, không nói về app mobile.

### Lưu ý pháp lý

Điều khoản của Mealbird cấm sao chép, scrape, bán lại hoặc phân phối lại Service và nội dung của nó, trừ khi được phép. Tài liệu này chỉ trích dẫn và tóm tắt các công bố công khai để phân tích sản phẩm, không đính kèm mã, ảnh chụp màn hình hay nội dung có bản quyền của app.

---

## 1. App này là gì, trong một câu

Mealbird là app lập kế hoạch ăn cho 7 ngày, dựa trên hai con số mục tiêu do người dùng đặt hoặc app tính giúp — protein (g/ngày) và calo (kcal/ngày) — rồi kiểm tra từng ngày một bằng phép nhân từ dữ liệu USDA FoodData Central, thay vì lấy trung bình cả tuần hay để AI tự viết ra con số dinh dưỡng.

**Định danh:**

- Tên: Mealbird: Macro Meal Planner
- Nhà phát triển / chủ sở hữu: Lusson LLC, 30 N Gould St, Sheridan, WY 82801, Hoa Kỳ
- Gói Android: `app.mealbird`
- App Store Mỹ: id 6794577462
- Thể loại: Health & Fitness
- Ra mắt trên Google Play: tháng 9/2026
- Không phải thiết bị y tế, không đưa ra lời khuyên y tế. Điều khoản và App Store đều ghi: hãy hỏi bác sĩ hoặc chuyên gia dinh dưỡng trước khi thay đổi chế độ ăn đáng kể, nhất là khi mang thai, cho con bú, có bệnh lý, hoặc có tiền sử rối loạn ăn uống.

---

## 2. REA đã làm gì, và đã chặn ở đâu

Đây là phần quan trọng nhất về phương pháp, vì người đọc cần biết tài liệu này dựa vào đâu.

**Bước 1 — Xác định đúng app.** "Mealbird" dễ nhầm với "Mailbird" (app email). Đã loại Mailbird khỏi phạm vi bằng cách đối chiếu tên đầy đủ, nhà phát triển Lusson LLC và gói `app.mealbird`.

**Bước 2 — Kiểm tra REA.** REA 6.8.0 chạy tốt, MCP server healthy, skill đã cài. Đây là phần duy nhất của REA được xác minh trọn vẹn.

**Bước 3 — Thử phân tích Android, thất bại có lý do rõ ràng.**

- Lệnh cần dùng theo hướng dẫn Android của REA là `rea inspect-android-package <đường dẫn APK>`, rồi `search_android_classes`, `inspect_android_class`, `inspect_android_method`.
- Nhưng chuỗi này cần JADX headless MCP JAR 0.7.1 và Java 17 trở lên, do người gọi tự cung cấp. Máy này: không có `java`, không có JAR, `rea doctor --provider jadx` báo `missing_analysis_engine`.
- Không có file APK để đưa vào lệnh. Không có thiết bị đã ghép nối. Không tìm được mirror APK đã xác minh.
- Kết luận: không chạy được bước này, và không được bịa ra kết quả dịch ngược để lấp chỗ trống.

**Bước 4 — Thử phân tích web bằng REA, thất bại một phần.**

- Đã dò đúng định dạng scenario của `rea capture-browser-scenario` qua các thông báo lỗi của chính REA: cần `browser.mode = launch`, `browser.executable_path`, `start_url`, và ít nhất một action có `step_id`; action `goto` cần `destination` và `wait_until`.
- Với Chromium của Playwright (156.0.8078.4), trình duyệt khởi động được, nhưng navigation thất bại trước khi ghi nhận trạng thái ban đầu, 0 event, 0 network transaction được giữ. Lỗi này không được lặp lại vô hạn theo hướng dẫn phục hồi của REA.
- Vì vậy không có bằng chứng REA nào về bundle JavaScript của website trong tài liệu này. Phần website dưới đây lấy từ việc đọc trang công khai bằng trình duyệt, không phải từ REA.

**Bước 5 — Đọc nguồn chính thức.** Trang chủ, điều khoản (cập nhật 4/10/2026) và chính sách riêng tư (cập nhật 4/10/2026) đã đọc toàn bộ. Đây là nguồn chính cho mọi mô tả tính năng.

---

## 3. Luồng sản phẩm: từ câu hỏi tới một tuần đã kiểm tra

### 3.1. Onboarding — trả lời trước, thấy mục tiêu trước khi trả tiền

Người dùng trả lời một bộ câu hỏi ngắn: mục tiêu, cơ thể, mức độ nấu ăn thực tế, món không ăn, ngân sách đi chợ. App tính ra mục tiêu rồi cho xem trước khi thu phí. Những gì không mất tiền, theo điều khoản: bộ câu hỏi, mục tiêu app tính giúp, và một tuần ví dụ. Muốn xây tuần thật thì cần đăng ký đang hoạt động.

Các đầu vào app lưu lại (theo chính sách riêng tư): mục tiêu, mục tiêu calo/protein, chế độ ăn, dị ứng và nhu cầu dinh dưỡng, nguyên liệu yêu thích hoặc không bao giờ muốn, món đã tim hoặc loại bỏ, phong cách nấu, thiết bị trong bếp, số người ăn (người lớn và trẻ em theo nhóm tuổi), ngân sách chợ hàng tuần và tiền tệ, quốc gia, cửa hàng, ngày đi chợ, ngày và bữa cần lên kế hoạch, món ăn nhẹ, trở ngại thường gặp, ngôn ngữ.

Điểm riêng tư đáng chú ý: **số đo cơ thể ở lại trên điện thoại.** App hỏi giới tính, tuổi, chiều cao, cân nặng, mức vận động; phép tính diễn ra trên máy, chỉ kết quả calo/protein được gửi lên server. Tương tự với người lớn khác trong bàn ăn: chỉ mục tiêu và mục tiêu calo/protein của họ được gửi, không gửi số đo.

### 3.2. Công thức mục tiêu — nhà phát triển công bố từng bước, nhưng không công bố mọi hệ số

Trang chủ có một máy tính protein dùng đúng công thức trong phần setup, và một ảnh chụp công thức đã công bố:

- Base burn (Mifflin–St Jeor): 1.783 kcal
- Nhân hệ số vận động "chủ yếu ngồi" × 1,2: 2.139 kcal
- Trừ 20% cho mục tiêu giảm (cut): 1.700 kcal
- Cân nặng tại BMI 25 theo chiều cao của ví dụ: 174 lb
- Protein 1 g × 174 lb = 175 g
- Giới hạn tối đa 35% calo: 145 g

Mục tiêu cuối trong ví dụ đó là **145 g protein và 1.700 kcal/ngày** — tức giới hạn 35% đã cắt con số 175 g xuống 145 g. Trang chủ cũng cho một ảnh mục tiêu khác với cùng cặp 145 g / 1.700 kcal.

Cần nói rõ chỗ này: trang chủ không công bố hệ số Mifflin–St Jeor cho nam/nữ trong đoạn đã đọc, và phép tính 2.139 × 0,8 ra 1.711 chứ không phải 1.700, 35% của 1.700 kcal chia 4 kcal/g ra khoảng 149 g chứ không phải đúng 145 g. Rất có thể app làm tròn theo bậc riêng, nhưng vì nhà phát triển không ghi bước làm tròn, tài liệu này ghi đúng số họ công bố và đánh dấu phần làm tròn là chưa rõ — không tự sửa số của họ cho "đẹp".

Máy tính trên trang chủ ghi chú: tính cho tuổi 30 và tập 3–4 buổi/tuần; app hỏi cả hai. Người dùng cũng có thể tự nhập mục tiêu của mình thay vì dùng công thức; mục tiêu luôn sửa được, và đổi mục tiêu ảnh hưởng tới tuần tiếp theo, không sửa ngược tuần đang có.

### 3.3. Kiểm tra từng ngày, không lấy trung bình tuần

Đây là điểm bán hàng trung tâm, và cũng là điểm khác biệt lớn nhất so với app lập kế hoạch bằng AI:

- Protein được coi là **sàn (floor)** mà cả 7 ngày đều phải vượt, không phải tổng cộng dồn tới Chủ nhật.
- Mỗi ngày có số protein và calo riêng, kèm một dải (band) cho thấy ngày đó rơi vào đâu trong khoảng mục tiêu.
- Nếu mục tiêu và ngân sách không cho phép mọi ngày đều đạt, app vẫn giao tuần đó, kèm thông báo nói rõ có bao nhiêu ngày thiếu và thiếu bao nhiêu, và "tiếp tục làm việc ở nền" (theo cách họ viết).
- Người dùng không phải ghi nhật ký, cân, quét sau khi ăn. Đây là app lập kế hoạch, không phải app theo dõi ăn uống — nhưng app vẫn có nút "log a meal" (ghi nhận đã ăn) và streak; theo chính sách riêng tư, bữa đã log ở lại trên điện thoại, server chỉ ghi nhận việc đã log và độ dài streak, không ghi đã ăn gì.

**Ví dụ một tuần thật (tháng 7/2026, theo trang chủ):** mục tiêu sàn protein 160 g, 1.800 kcal, ngân sách 75 USD.

- Chủ nhật 166,3 g / 1.733 kcal
- Thứ hai 166,4 g / 1.781 kcal
- Thứ ba 164,6 g / 1.723 kcal
- Thứ tư 165,5 g / 1.744 kcal
- Thứ năm 165,2 g / 1.755 kcal
- Thứ sáu 163,7 g / 1.796 kcal
- Thứ bảy 164,7 g / 1.754 kcal

Ghi chú của họ: ước tính chợ 74,80 USD từ giá trung bình tại Mỹ. Các ngày đều nằm trên sàn 160 g và quanh 1.800 kcal, không ngày nào bị "trung bình tuần" che đi.

### 3.4. Công thức hiển thị phép tính, tới từng gram và mã USDA

Mỗi bữa có công thức thật, khẩu phần thật, thời gian nấu thật. Chạm vào một bữa sẽ thấy từng nguyên liệu tính theo gram, mã USDA của nó, và phép nhân tạo ra tổng số.

**Ví dụ đã công bố — Blueberry Protein Yogurt Bowl:**

- 5 phút, xuất hiện 4 lần trong tuần (nấu một mẻ dùng cho 4 bữa)
- Hiển thị: 49 g protein, 410 kcal, 44 g carb, 4 g chất béo
- Bảng tính chi tiết: sữa chua Hy Lạp 200 g × 10,3 g protein/100 g = 20,6 g; whey, yến mạch, việt quất tính tương tự, mỗi dòng kèm mã USDA
- Tổng trong bảng tính: 49,6 g protein và 409,8 kcal — chênh với số hiển thị 49 g / 410 kcal chỉ do làm tròn, và đây là kiểu chênh lệch làm tròn có thể chấp nhận được, khác với ví dụ công thức mục tiêu ở trên (nơi bước làm tròn không được ghi).

**Vai trò của AI, theo công bố của họ — và đây là ranh giới họ tự vẽ:**

- AI chỉ chọn nguyên liệu thật và lượng thật.
- AI không bao giờ viết ra con số dinh dưỡng trong kế hoạch.
- Mọi gram protein và calo đều tính từ USDA FoodData Central và kiểm tra bằng phép tính.
- Họ tự kiểm lại các app AI khác và nêu hai ví dụ: một bữa tối AI ghi 840 kcal nhưng cộng lại là 990 kcal; một khẩu phần AI ghi 135 g protein nhưng cộng lại chỉ khoảng 55 g. Đây là tuyên bố của Mealbird về đối thủ, chưa được kiểm chứng độc lập trong tài liệu này.

**Lượng nấu trong một tuần điển hình:** khoảng 11 món cho 21 bữa, khoảng 7 giờ nấu. App tái sử dụng món có chủ đích và nói trước món nào lặp lại và tiết kiệm được gì. Ví dụ màn hình chính: "Good morning. Next up: lunch." — 3 bữa, 1.733 kcal, 166 g protein trong ngày; streak 3 ngày; đã ăn 410/1.800 kcal và 49/160 g protein tới thời điểm đó.

---

## 4. Danh sách đi chợ, ngân sách và hộ gia đình

### 4.1. Danh sách đi chợ — sắp theo gian hàng, tính theo gói thật

- Danh sách được nhóm theo gian hàng (aisle), không phải danh sách phẳng.
- Số lượng mua tính theo **gói bán thật ngoài cửa hàng**: ví dụ một túi hành 1 kg dùng được khoảng 2 tuần, đậu xanh 500 g, một củ tỏi 60 g.
- Danh sách ghi rõ phần còn thừa dùng được bao lâu, để người dùng không mua thừa tuần sau.

### 4.2. Hai con số tiền khác nhau — và app tách chúng ra

Đây là chi tiết thiết kế đáng học nhất của Mealbird:

- **Tiền thức ăn trong tuần:** chỉ tính phần sẽ ăn hết trong tuần đó.
- **Tiền phải trả ở quầy:** tính theo gói nguyên, vì dầu ăn mua cả chai thì phần còn lại tính cho tuần sau.

**Ví dụ Anh (bảng Anh):** thức ăn trong tuần 58,15 GBP, nằm trong ngân sách 60 GBP; tiền ở quầy 104,24 GBP.

**Ví dụ hộ gia đình (2 người lớn + 1 trẻ, chỉ ăn nhẹ cho một người):** thức ăn trong tuần 180,20 GBP, trong ngân sách 250 GBP; tiền ở quầy 199,93 GBP.

**Giá là ước tính, không phải giá trực tiếp từ cửa hàng.** Trang chủ nói giá từ bảng giá địa phương ở Anh, Ireland, Hà Lan, Bỉ, Romania và Mỹ; nơi khác thì dùng giá trung bình Mỹ quy đổi sang tiền tệ địa phương. Điều khoản (cập nhật 4/10/2026) lại viết gọn là giá chợ và gian hàng dựa trên dữ liệu Mỹ. Hai văn bản này chưa khớp nhau hoàn toàn — tài liệu ghi cả hai và đánh dấu là điểm cần đối chiếu, không tự chọn một bản làm đúng.

### 4.3. Nấu cho nhiều người

- Khai báo ai ngồi cùng bàn: người lớn có thể ăn cùng khẩu phần hoặc có mục tiêu calo/protein và mục tiêu riêng; trẻ em chỉ lưu nhóm tuổi và tên (tùy chọn, tối đa 24 ký tự).
- Công thức hiển thị khẩu phần của bạn và cả nồi chung.
- Danh sách đi chợ mua cho tất cả mọi người.

**Chia sẻ hộ gia đình (household sharing):**

- Tạo mã hộ gia đình cho từng người, gửi mã cho họ tự nhập ở màn hình chào mừng ("Have a household code?").
- Người xem thấy tối đa 8 tuần gần nhất và sắp tới, mọi công thức theo khẩu phần của họ, và danh sách đi chợ; họ tick chung được với bạn.
- Người xem **không** thay đổi được kế hoạch, câu trả lời hay lượt đổi món của bạn.
- Mã dùng một lần, hết hạn sau 14 ngày; mã chỉ lưu dưới dạng hash một chiều có khóa, điện thoại của bạn giữ mã đọc được để hiển thị lại.
- Người xem không nhận được: chế độ ăn, dị ứng, sở thích, nguyên liệu loại bỏ, số đo cơ thể, hay mục tiêu của người khác trong bàn.
- Bạn xem được trên thẻ Household những điện thoại nào đã tham gia (mẫu máy, lần mở app gần nhất) và gỡ bất kỳ điện thoại nào; người xem rời đi bất cứ lúc nào.
- Nếu gói của bạn hết hạn, người xem thấy kế hoạch ở trạng thái tạm dừng.

### 4.4. Thay đổi tuần, ăn ngoài, đổi món

- Đổi ngày hoặc ngày bắt đầu: tức thì.
- Đổi một bữa: trong vài giây.
- Bắt đầu lại phần còn lại của tuần.
- Loại một thực phẩm ra khỏi tuần.
- Ăn ngoài: đánh dấu bữa đó, phần còn lại của ngày giữ nguyên kế hoạch.
- Đổi món và ăn ngoài đều cho xem trước thay đổi trước khi áp dụng — app tuyên bố "sẽ không lặng lẽ sửa kế hoạch sau lưng bạn".

### 4.5. Chế độ ăn và dị ứng

- Chế độ: chay (vegetarian), thuần chay (vegan), pescatarian.
- Loại bỏ: không thịt lợn, không thịt đỏ, low-carb, không gluten, không sữa, không trứng, không hạt (nut-free).
- Trang chủ nói có 12 dị ứng có thể loại bỏ hoàn toàn, nhưng đoạn đã đọc không liệt kê đủ 12 tên — tài liệu không tự điền thêm danh sách.
- Một đánh giá trên App Store Hà Lan (dịch bởi chính Mealbird) phàn nàn còn thiếu lựa chọn halal, và người đó có dị ứng thịt lợn nên cần lựa chọn này. Đây là phản hồi người dùng, không phải tính năng đã xác nhận.
- Theo chính sách riêng tư, khi gửi yêu cầu cho AI, dị ứng và chế độ ăn **không được nêu tên**: app loại bỏ các nguyên liệu đó trước khi mô hình nhìn thấy danh sách. Tên người trong bàn cũng không bao giờ gửi cho AI.

---

## 5. Các tính năng phụ: quét đĩa, trò chuyện, bảng góp ý

### 5.1. Scan the Plate (chỉ iPhone và iPad)

- Chụp ảnh hoặc mô tả một bữa ăn, app ước tính nó.
- Ảnh và văn bản được gửi lên server rồi chuyển cho nhà cung cấp AI (Anthropic). **Ảnh và văn bản không được giữ lại.** Chỉ ghi nhận đã quét và quét có thành công không, và bản ghi đó xóa sau 30 ngày. Điện thoại giữ ước tính và một thumbnail nhỏ.
- Đây là ngoại lệ duy nhất cho tuyên bố "AI không viết số dinh dưỡng": số từ Scan the Plate là ước tính, không phải số đã kiểm tra bằng USDA như trong kế hoạch tuần.

### 5.2. Chat with Lukas (trò chuyện với người thật, có AI hỗ trợ dịch và gợi ý)

- Tin nhắn trong app được lưu cùng trả lời, dấu thời gian và trạng thái đã đọc. Người trả lời là Lukas và quản trị viên được ủy quyền, đọc và trả lời trong hộp thư riêng.
- Để trả lời nhanh và đúng ngôn ngữ, các tin nhắn gần đây được gửi cho Anthropic để dịch sang tiếng Anh và gợi ý trả lời, kèm tóm tắt tài khoản (đã đăng ký hay chưa, phiên bản app, mẫu máy, quốc gia, ngôn ngữ, ngân sách tuần hiện tại, tiền thức ăn và tiền ở quầy). **Một người đọc, sửa và gửi mọi trả lời; không có gì tự động gửi cho bạn.**
- Bản dịch tiếng Anh của mỗi tin nhắn được giữ 30 ngày.

### 5.3. Bảng góp ý (feedback board)

- Gợi ý tối đa 80 ký tự tiêu đề, 1.000 ký tự mô tả; người khác vote lên/xuống, báo cáo hoặc ẩn tác giả.
- Gợi ý riêng tư cho tới khi Lukas duyệt; sau đó hiển thị cho mọi người dùng bảng, không kèm tên, ID hay chi tiết nào về bạn.
- Trong trang quản trị riêng, Lukas thấy được ID tài khoản, ngôn ngữ app, quốc gia, trạng thái đăng ký và số gợi ý của tác giả để kiểm duyệt và xử lý lạm dụng. Gợi ý bị 3 người khác nhau báo cáo sẽ bị ẩn với mọi người cho tới khi anh ta xem xét.
- Gợi ý đã công bố vẫn ở lại bảng sau khi bạn xóa tài khoản, nhưng không còn liên kết với bạn.

### 5.4. Ảnh món ăn

- Ảnh món do AI tạo (OpenAI; có thể dùng Google thay thế). Yêu cầu gửi đi chỉ có tên món và danh sách nguyên liệu. Ảnh được chia sẻ cho mọi người nhận cùng món đó.

---

## 6. Giá, thanh toán và mô hình kinh doanh

- **Không có gói miễn phí, không có dùng thử miễn phí.** Đây là tuyên bố rõ ràng của họ: "No free trial and no free tier. Building a checked week costs us real money, so we charge for it rather than pretend it's free."
- Trước khi trả tiền, người dùng được: trả lời câu hỏi, xem mục tiêu, mở một tuần ví dụ.
- Gói tháng: **9,99 USD/tháng**. Gói năm: **29,99 USD/năm** (họ tự tính ~2,50 USD/tháng, rẻ hơn 75% so với trả tháng).
- Trên App Store Mỹ còn có gói năm ưu đãi đặc biệt **19,99 USD**.
- Giá tại châu Âu khác: một bình luận trên Google Play ghi 12 EUR/tháng và phàn nàn đắt hơn Netflix và YouTube. Giá hiển thị trước khi trả, theo tiền tệ địa phương của cửa hàng.
- Thanh toán qua Apple hoặc Google; Mealbird không bao giờ thấy thông tin thẻ. Đăng ký tự động gia hạn cho tới khi hủy; hủy trong Cài đặt Apple Account hoặc Payments & subscriptions trên Google Play. **Xóa app không hủy đăng ký.** Trừ khi luật hoặc cửa hàng yêu cầu khác, thanh toán không hoàn lại; hoàn tiền do Apple hoặc Google xử lý.
- Một bình luận 2 sao (10/10/2026) phàn nàn: muốn thấy giá trước, để khỏi mất công setup rồi mới thấy giá. Đây là điểm ma sát trong luồng paywall mà tài liệu ghi lại nguyên văn ý, không tô hồng.

---

## 7. Nền tảng, phiên bản, ngôn ngữ, đánh giá

### 7.1. Phiên bản và yêu cầu hệ thống

| Nền tảng | Phiên bản mới nhất đã đọc | Ngày / ghi chú | Kích thước | Yêu cầu |
|---|---|---|---|---|
| Android (Google Play qua AppBrain) | 1.1.17 | Cập nhật 28/10/2026 | APK 21,74 MB | Android 8.0 trở lên, Everyone |
| iOS (App Store Mỹ, đọc trực tiếp) | 1.1.17 | "5d ago" tại thời điểm đọc 3/11/2026 | 79,9 MB | iOS 17.0 trở lên, iPadOS 17.0 trở lên, 9+ |

Lưu ý mâu thuẫn nhỏ giữa các snapshot: kết quả tìm kiếm cũ ghi iOS 1.1.14 và 69,2 MB; trang App Store đọc trực tiếp ghi 1.1.17 và 79,9 MB. Tài liệu dùng bản đọc trực tiếp mới nhất. Ghi chú phát hành mới nhất: "Mealbird now speaks Lithuanian and Japanese. In Lithuania, the 'Where do you shop?' step now also lists Šilas and Express Market. Fixes a bug that could hide your current week after you changed your plan."

### 7.2. Ngôn ngữ

- Trang chủ ghi: "Now in English, Nederlands, Deutsch and Română."
- App Store Mỹ (đọc trực tiếp) ghi đầy đủ hơn nhiều: **tiếng Anh và 18 ngôn ngữ khác**, gồm Bulgaria, Croatia, Hà Lan, Estonia, Pháp, Đức, Hungary, Ý, Nhật, Lithuania, Nepal, Ba Lan, Bồ Đào Nha, Romania, Slovakia, Tây Ban Nha, Thụy Điển, **Việt Nam**.
- Hai nguồn này không khớp nhau — có thể trang chủ chưa cập nhật, hoặc đang nhấn mạnh 4 ngôn ngữ chính. Tài liệu ghi cả hai, và lưu ý: **Mealbird đã có tiếng Việt trong danh sách App Store**, điều này đáng chú ý nếu bạn đang xây sản phẩm cho người Việt.

### 7.3. Đánh giá và xếp hạng — mỗi con số kèm nguồn và thời điểm

**App Store (theo trang chủ, tính tới 1/10/2026, tính riêng từng quốc gia vì Apple xếp hạng theo quốc gia):**

- Romania: 4,9 — 460 đánh giá
- Hà Lan: 4,6 — 234 đánh giá
- Anh: 4,9 — 56 đánh giá
- Bỉ: 4,7 — 40 đánh giá
- Đức: 4,9 — 24 đánh giá
- Mỹ: 5,0 — 14 đánh giá

**App Store Mỹ (đọc trực tiếp 3/11/2026):** 4,9 sao, 166 đánh giá. Con số này khác với 5,0/14 của snapshot 1/10 — do thời điểm và mẫu đánh giá khác nhau, không phải lỗi số liệu.

**Google Play (qua AppBrain, đọc 3/11/2026):** 4,83 sao, 2.020 đánh giá, 18.000 lượt cài, trung bình 590 lượt cài/ngày trong 30 ngày qua, 10.000+ lượt tải theo bậc hiển thị của Play. Xếp hạng Top Free Health & Fitness: Thụy Điển #4, Slovakia #6, Séc #89, Ba Lan #120, Mexico #129, Bồ Đào Nha #152, Hà Lan #159; Top Grossing: Iran #172. Một kết quả tìm kiếm cũ hơn ghi #5 Health & Fitness — xếp hạng thay đổi theo ngày, không lấy một snapshot làm hạng cố định.

**Phản hồi người dùng tiêu biểu (ghi nguyên ý, không chọn lọc chỉ khen):**

- Khen: lập kế hoạch tuần nhanh cho người muốn tăng cơ, nói rõ mua gì, nấu gì, nấu như thế nào, meal prep ra sao (Romania, 6/8/2026); tốt cho người làm việc giờ giấc không đều, nhưng còn thiếu lựa chọn dị ứng/chế độ như halal (Hà Lan, 28/9/2026); giúp gia đình vào nề nếp và đúng ngân sách (Mỹ, 18/9/2026).
- Chê: muốn thấy giá trước khi setup (2 sao, 10/10/2026); 12 EUR/tháng đắt hơn Netflix và YouTube, đề nghị hạ giá để nhiều người dùng hơn (2 sao, 18/10/2026).

---

## 8. Dữ liệu, quyền riêng tư và nhà cung cấp — phần này đọc từ chính sách riêng tư, không phải từ mã nguồn

### 8.1. Tài khoản và dữ liệu thu thập

- Mở app lần đầu tạo tài khoản chỉ bằng **ID ngẫu nhiên**. Không bắt buộc tên hay email. Muốn đăng nhập trên thiết bị khác thì thêm email + mật khẩu (mật khẩu chỉ lưu dạng hash bởi nhà cung cấp đăng nhập); hoặc Sign in with Apple (chỉ xin email, có thể là địa chỉ relay riêng tư) / Google (chỉ dùng email; nhà cung cấp đăng nhập lưu cả tên và ảnh đại diện từ Google gửi sang, nhưng app không hiển thị hay dùng tên/ảnh đó).
- Thiết bị gửi: ID thiết bị ngẫu nhiên do app tự tạo (lưu trong iOS Keychain hoặc Google Block Store trên Android), mẫu máy, phiên bản app, ngôn ngữ, quốc gia/vùng (trên Android đọc từ SIM hoặc mạng di động nếu có). ID này dùng để đếm lượt cài, nhận lại cùng điện thoại sau khi cài lại, và giới hạn số điện thoại một mã creator có thể mở khóa.
- Thông báo: chỉ khi bạn cho phép; push token được lưu, và thông báo có thể chứa chi tiết kế hoạch (tên món, ngân sách) hoặc đoạn đầu trả lời chat.
- **Không dùng:** vị trí chính xác, danh bạ, micro, Apple Health, Health Connect. Không xin quyền theo dõi, nên không bao giờ nhận advertising ID.
- **Quyền trên iPhone/iPad:** camera chỉ cho Scan the Plate; cũng có thể chọn ảnh qua system picker (app chỉ nhận đúng ảnh bạn chọn). Thông báo chỉ khi bạn muốn.
- **Dữ liệu App Store khai báo:** liên kết với bạn — Health & Fitness, Purchases, Contact Info, User Content, Identifiers, Usage Data, Diagnostics; không liên kết với bạn — Purchases, User Content, Other Data.

### 8.2. Nhà cung cấp và dữ liệu họ nhận (bảng từ chính sách riêng tư, mục 7)

| Nhà cung cấp | Việc làm | Dữ liệu nhận |
|---|---|---|
| Supabase | Database, đăng nhập, lưu file sau app | Mọi thứ app lưu trên server |
| Fly.io | Chạy server xây và kiểm tra kế hoạch | Yêu cầu kế hoạch của bạn |
| Inngest | Lên lịch job nền | ID bản ghi, và ID tài khoản khi có mua mới |
| Anthropic | AI cho kế hoạch, đổi món, Scan the Plate, trả lời chat, dịch bảng góp ý | Nội dung mục 5 của chính sách, không kèm định danh |
| OpenAI, Google | Ảnh món | Chỉ tên món và nguyên liệu |
| PostHog | Phân tích sản phẩm | Sự kiện sử dụng, ID tài khoản, chi tiết thiết bị, IP |
| Superwall | Hiển thị gói đăng ký, báo trạng thái đăng ký | ID tài khoản, ID thiết bị riêng cho app, màn hình gói đã xem và thao tác, mua hàng, chi tiết thiết bị, một số con số trên màn hình gói |
| Apple, Google | Cửa hàng, thanh toán, đăng nhập, push, nhắc đánh giá, giữ ID thiết bị (Android) | Mua hàng, đăng nhập, push token, nội dung thông báo |
| Meta, TikTok | Đo lường quảng cáo (chỉ iPhone/iPad) | Lượt cài, mở app, bắt đầu đăng ký kèm giá và tiền tệ, mẫu máy, phiên bản OS, ngôn ngữ, IP, ID thiết bị riêng cho app |
| Resend | Gửi email (reset mật khẩu, cảnh báo cho team) | Địa chỉ email và nội dung thư |
| Discord | Cảnh báo nội bộ cho team về lỗi, lượt cài mới, mua mới | ID tài khoản rút gọn, quốc gia, mẫu máy, phiên bản app, chi tiết lỗi, chiến dịch Apple Search Ads, ngôn ngữ gợi ý mới, lý do báo cáo (không bao giờ là nội dung gợi ý) |
| Vercel, Prisma | Hosting, analytics và database cho website | Lượt xem trang, đăng ký creator |

Lưu ý: **app Android không chứa SDK Meta/TikTok.** Trên iOS, Meta/TikTok không nhận ID tài khoản, email, câu trả lời hay kế hoạch; kết quả quảng cáo được báo cáo tổng hợp qua SKAdNetwork của Apple.

### 8.3. Thời gian lưu giữ

- Tài khoản, câu trả lời, kế hoạch, chat, push token: tới khi bạn xóa tài khoản.
- Bản ghi quét, lần thử mã creator, bản dịch tiếng Anh của chat: 30 ngày. Ảnh quét không giữ lại.
- Báo cáo lỗi/crash: 60 ngày.
- Mã hộ gia đình: dùng một lần, tối đa 14 ngày; mã xóa 30 ngày sau khi hết hạn. Bản ghi điện thoại đã tham gia xóa 90 ngày sau khi rời khỏi hoặc bị gỡ.
- Gợi ý đã công bố: ở lại bảng sau khi xóa tài khoản, không còn liên kết với bạn, cho tới khi Lukas gỡ.
- Bản ghi mua hàng: giữ theo luật thuế/kế toán, kể cả sau khi xóa tài khoản.
- Bản ghi thiết bị: giữ sau khi xóa tài khoản, không còn liên kết với tài khoản, để số lượt cài đúng và mã creator không dùng lại được trên cùng điện thoại.
- Sự kiện phân tích: xóa khi người dùng yêu cầu.
- Xóa tài khoản: trong app, Settings (tab You trên Android) → Delete account; nếu chưa đăng ký thì cũng có trong menu ở màn hình đăng ký. Xóa sẽ gỡ hồ sơ, mọi tuần đã xây, lượt đổi món, lịch sử chat, push token và đăng nhập khỏi server ngay lập tức, và dọn app trên điện thoại; đồng thời gỡ hộ gia đình bạn chia sẻ và vị trí của bạn trong hộ gia đình đã tham gia (dấu tick bạn đã tick vẫn ở lại danh sách đó). Xóa app hoặc xóa tài khoản đều không hủy đăng ký.

### 8.4. Website thu thập gì

- Vercel Web Analytics đếm lượt xem trang và referrer ở dạng tổng hợp, không dùng cookie, không lập hồ sơ theo dõi liên site. Trang reset mật khẩu không gửi analytics.
- Trang sau link mời hộ gia đình (mealbird.app/join) chỉ đọc loại trình duyệt để chuyển bạn tới đúng cửa hàng; link không mang mã.
- Form đăng ký creator lưu: tên, email, số WhatsApp, địa chỉ bưu chính, quốc gia, tài khoản mạng xã hội, thời gian có thể bắt đầu, ghi chú, cộng quốc gia gửi yêu cầu.

---

## 9. Những gì tài liệu này KHÔNG kết luận được

Ghi rõ để lần sau không ai trích dẫn sai:

1. **Không biết app mobile viết bằng gì.** Không có APK, không có mã, không có danh sách thư viện chi tiết. Con số "28 thư viện" của AppBrain chỉ là số lượng, không phải tên thư viện.
2. **Không biết danh sách 13 quyền cụ thể.** Chính sách riêng tư nói app không dùng vị trí chính xác, danh bạ, micro, Health — nhưng đó là tuyên bố về hành vi, không phải danh sách quyền trong manifest.
3. **Không biết API, endpoint, hay cấu trúc server.** Chỉ biết tên nhà cung cấp (Supabase, Fly.io, Inngest) từ chính sách, không biết họ gọi nhau như thế nào.
4. **Không biết công thức protein đầy đủ.** Chỉ biết các bước và con số ví dụ mà trang chủ công bố, và phần làm tròn chưa được ghi.
5. **Không biết USDA matching hoạt động ra sao.** Chỉ biết tuyên bố: nhân gram với bản ghi USDA, hiển thị mã USDA. Không biết họ chọn bản ghi nào khi một nguyên liệu có nhiều bản ghi, hay xử lý nguyên liệu không có trong USDA ra sao.
6. **Không biết giá chợ tính như thế nào.** Chỉ biết tuyên bố về bảng giá địa phương và giá trung bình Mỹ, và mâu thuẫn giữa trang chủ và điều khoản về "dữ liệu Mỹ".
7. **Không biết doanh thu, số người dùng trả phí, hay tỷ lệ giữ chân.** Chỉ có lượt cài và đánh giá công khai.
8. **Không xác minh được tuyên bố về đối thủ AI.** Ví dụ "840 kcal thành 990 kcal" và "135 g protein thành 55 g" là tuyên bố của Mealbird, chưa có nguồn độc lập trong tài liệu này.

---

## 10. Bài học có thể áp dụng cho Lắc Đói (phần suy luận, tách riêng khỏi sự thật về Mealbird)

Phần này là phân tích của người viết, không phải tuyên bố của Mealbird, và chỉ có giá trị nếu bạn thấy phù hợp với dự án của mình:

1. **"Hiển thị phép tính" là tính năng tạo niềm tin, không chỉ là trang trí.** Mealbird không giấu công thức sau một con số tổng; họ cho xem từng dòng nhân và mã USDA. Với một kho dữ liệu đặt độ chính xác lên hàng đầu, việc cho xem nguồn và phép tính của từng con số có giá trị hơn việc chỉ hiển thị con số cuối.
2. **Kiểm tra từng ngày, không lấy trung bình.** Lắc Đói hiện có cảnh báo kết hợp và dữ liệu theo món; nếu sau này có tính năng lập tuần, nguyên tắc "sàn cho từng ngày, không trung bình tuần" của Mealbird là một chuẩn kiểm tra đáng mượn.
3. **Tách "tiền thức ăn trong tuần" khỏi "tiền trả ở quầy".** Đây là cách trình bày trung thực về chi phí mà người dùng Việt Nam đi chợ theo gói, theo cân cũng gặp hàng ngày.
4. **Nói rõ cái gì là ước tính.** Mealbird ghi nhãn giá chợ là ước tính ở mọi nơi xuất hiện, và ghi rõ số dinh dưỡng trong kế hoạch là đã kiểm tra bằng phép tính, còn số từ Scan the Plate chỉ là ước tính. Phân biệt hai loại số này bằng nhãn, thay vì để chung một bảng, là cách làm đúng với quy tắc "không rõ thì ghi chú, không có thì để trống" của dự án bạn.
5. **Số đo cơ thể ở lại trên máy, chỉ gửi mục tiêu.** Nếu Lắc Đói sau này tính mục tiêu cá nhân, đây là mẫu thiết kế riêng tư tối thiểu đáng tham khảo.
6. **Đừng mượn uy tín của Mealbird cho Lắc Đói.** Mealbird tự nhận dữ liệu USDA "có thể chứa lỗi và thực phẩm thật có thể khác nhau", và không bảo đảm chính xác tuyệt đối. Một tài liệu phân tích cũng nên giữ đúng mức đó.

---

## 11. Muốn có bản dịch ngược thật thì cần gì (bước tiếp theo, chưa làm)

Nếu bạn muốn lần sau phân tích tới mức mã nguồn, cần đủ ba thứ này trước khi chạy REA, theo đúng yêu cầu của chính REA:

1. **Một file APK hợp pháp của chính bạn:** lấy từ điện thoại của bạn (qua ADB, `list-adb-packages` rồi lấy base APK), hoặc từ Google Play trên tài khoản của bạn. Không dùng APK từ mirror chưa xác minh, vì không xác minh được chữ ký và có thể là bản đã sửa.
2. **Java 17 trở lên** trên máy phân tích.
3. **JADX headless MCP JAR 0.7.1** do bạn cung cấp, rồi đặt biến môi trường:
   - `REA_JADX_MCP_JAR=/đường/dẫn/tuyệt/đối/tới/jadx-headless-mcp-0.7.1.jar`
   - Kiểm tra bằng `rea doctor --provider jadx` cho tới khi báo sẵn sàng.
4. Sau đó chạy, theo thứ tự trong hướng dẫn Android của REA:
   - `rea inspect-android-package <đường dẫn APK>` — định danh gói, manifest, phạm vi bao phủ
   - `rea search-android-classes <APK> <từ khóa>` — tìm class ứng viên
   - `rea inspect-android-class` — xem thành viên của một class
   - `rea inspect-android-method` — dịch ngược một method, chọn `overload_index` từ inventory khi method mơ hồ, không đoán
   - `rea trace-android-references` — tìm tham chiếu tĩnh tới một class/method
5. Lưu toàn bộ Evidence JSON trước khi đọc bản tóm tắt, ghi lại digest của APK, phiên bản, và những phần chưa bao phủ (reflection, dynamic loading, cuộc gọi chưa phân giải vẫn là ẩn số theo chính hướng dẫn của REA).

Không cần Hopper, Ghidra hay IDA cho phân tích Android — các engine đó phục vụ phân tích native, không phải APK. Cũng không cần cài Hopper kèm setup trừ khi bạn phân tích binary native.

---

## Nguồn

- Trang chủ Mealbird: https://mealbird.app/
- Điều khoản sử dụng: https://mealbird.app/terms
- Chính sách riêng tư: https://mealbird.app/privacy
- App Store Mỹ: https://apps.apple.com/us/app/mealbird-macro-meal-planner/id6794577462
- AppBrain (Google Play): https://www.appbrain.com/app/mealbird-macro-meal-planner/app.mealbird
- REA: https://github.com/morluto/rea — bản npm `rea-agents` 6.8.0, skill `reverse-engineer-anything` phiên bản 49
- File làm việc REA trong lần phân tích này (không đính kèm, chỉ để đối chiếu): `~/workspace/mealbird-analysis/` — các scenario JSON và đầu ra capture (navigation thất bại, 0 event)

*Hết tài liệu.*
