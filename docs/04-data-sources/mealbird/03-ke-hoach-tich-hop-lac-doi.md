# Mealbird → Lắc Đói: Phân tích toàn bộ tính năng và kế hoạch tích hợp

**Ngày:** 3/11/2026
**Phạm vi lượt này:** Giai đoạn 1 — phân tích và kế hoạch. **Chưa sửa code nào trong `shake-hungry/` trong lượt này**, theo quy tắc làm việc theo giai đoạn → phần → task của bạn: xong một task thì báo cáo và chờ duyệt rồi mới sang task tiếp theo.
**Tài liệu phân tích REA trước đó:** `mealbird-phan-tich-rea.md` (cùng thư mục này) — tài liệu này kế thừa nó, không viết lại phần bằng chứng REA.

## Nguyên tắc "clone" trong tài liệu này — đọc trước

"Clone tương tự" ở đây được hiểu là **clone về chức năng, không clone về sở hữu trí tuệ**:

- KHÔNG sao chép: tên và linh vật chim của Mealbird, văn bản marketing, ảnh chụp màn hình, công thức, mã nguồn, hay dữ liệu riêng của họ.
- CHỈ mượn: ý tưởng chức năng đã công bố công khai (lập tuần, kiểm tra từng ngày, hiển thị phép tính, danh sách chợ theo gian hàng) và triển khai lại bằng code mới, giao diện theo hệ thiết kế v2 của Lắc Đói (`ld-*`), dữ liệu món Việt của chính Lắc Đói, ngôn ngữ tiếng Việt.
- Điều khoản Mealbird cấm sao chép/phân phối lại Service và nội dung của họ. Vì vậy mọi văn bản, tên tính năng và dữ liệu trong phần tích hợp đều phải viết lại bằng lời của Lắc Đói.
- Quy tắc độ chính xác của dự án vẫn áp dụng toàn bộ: số chưa rõ thì ghi chú, không có thì để trống, không bịa; món chưa đủ dữ kiện dinh dưỡng thì không được tính là "đạt" trong kiểm tra tuần.

---

## 1. Toàn bộ tính năng Mealbird — danh mục đầy đủ, theo luồng người dùng

Danh mục này tổng hợp từ trang chủ, điều khoản và chính sách riêng tư đã đọc trực tiếp ngày 3/11/2026. Mỗi tính năng ghi rõ nguồn là công bố của nhà phát triển, không phải kết quả dịch ngược (xem tài liệu REA trước đó về giới hạn này).

### 1.1. Onboarding và mục tiêu

1. **Bộ câu hỏi setup:** mục tiêu, cơ thể, mức độ nấu ăn thực tế, món không ăn, ngân sách đi chợ.
2. **Máy tính mục tiêu công bố từng bước:** base burn Mifflin–St Jeor → nhân hệ số vận động → trừ/phần trăm theo mục tiêu (ví dụ giảm 20%) → cân nặng tại BMI 25 → protein 1 g/lb cân nặng đó → giới hạn tối đa 35% calo. Ví dụ công bố: 1.783 → 2.139 → 1.700 kcal, 175 g → giới hạn còn 145 g protein.
3. **Tự nhập mục tiêu:** người dùng có thể bỏ qua công thức, nhập protein và calo của mình; mục tiêu luôn sửa được, ảnh hưởng tuần tiếp theo.
4. **Xem trước miễn phí:** câu hỏi, mục tiêu, và một tuần ví dụ — trước khi trả tiền. Xây tuần thật cần đăng ký đang hoạt động.
5. **Máy tính protein trên web:** cùng công thức setup, cho thử trên trang chủ (ví dụ tính cho tuổi 30, tập 3–4 buổi/tuần).

### 1.2. Xây tuần và kiểm tra

6. **Kế hoạch 7 ngày:** sáng, trưa, tối (và món ăn nhẹ / bữa cần lên kế hoạch theo lựa chọn), món thật, khẩu phần thật, thời gian nấu thật.
7. **Kiểm tra từng ngày, không trung bình tuần:** protein là sàn mà cả 7 ngày phải vượt; mỗi ngày có số protein/kcal riêng và dải (band) cho thấy ngày đó rơi vào đâu.
8. **Validator và thông báo thiếu:** nếu mục tiêu + ngân sách không cho phép mọi ngày đều đạt, vẫn giao tuần kèm thông báo: bao nhiêu ngày thiếu, thiếu bao nhiêu; họ nói "tiếp tục làm việc ở nền".
9. **Công thức hiển thị phép tính:** chạm vào bữa sẽ thấy từng nguyên liệu theo gram, mã USDA, phép nhân ra tổng. Ví dụ: Blueberry Protein Yogurt Bowl 49 g protein / 410 kcal, bảng tính tổng 49,6 g / 409,8 kcal (chênh do làm tròn).
10. **Vai trò AI:** AI chỉ chọn nguyên liệu và lượng thật; AI không viết số dinh dưỡng. Mọi số trong kế hoạch tính từ USDA FoodData Central và kiểm tra bằng phép tính.
11. **Lặp món có chủ đích:** tuần điển hình khoảng 11 món cho 21 bữa, khoảng 7 giờ nấu; app nói trước món nào lặp và tiết kiệm gì. Người dùng chọn mức độ lặp, "nấu một lần ăn 4 lần".
12. **Quy tắc bếp:** chế độ ăn, dị ứng, nguyên liệu yêu thích / không bao giờ muốn, món đã tim / loại bỏ, phong cách nấu, thiết bị trong bếp, số người ăn, bữa nào tự nấu.

### 1.3. Thay đổi tuần trong khi dùng

13. **Đổi một bữa:** trong vài giây, xem trước thay đổi trước khi áp dụng.
14. **Đổi ngày / ngày bắt đầu:** tức thì.
15. **Bắt đầu lại phần còn lại của tuần.**
16. **Loại một thực phẩm ra khỏi tuần.**
17. **Ăn ngoài:** đánh dấu bữa đó, phần còn lại của ngày giữ nguyên.
18. **Không sửa lén:** app tuyên bố không lặng lẽ sửa kế hoạch sau lưng người dùng.
19. **Ghi nhận đã ăn và streak:** màn hình chính "Good morning. Next up: lunch", số bữa, kcal/protein đã ăn trong ngày, streak. Theo chính sách: bữa đã log ở lại trên điện thoại; server chỉ ghi đã log và độ dài streak, không ghi đã ăn gì.

### 1.4. Đi chợ, ngân sách, hộ gia đình

20. **Danh sách đi chợ theo gian hàng (aisle)**, sắp xếp theo gian hàng, không phải danh sách phẳng.
21. **Mua theo gói thật:** số lượng theo gói bán ngoài cửa hàng (ví dụ túi hành 1 kg dùng khoảng 2 tuần, đậu xanh 500 g, củ tỏi 60 g); ghi rõ phần thừa dùng được bao lâu.
22. **Hai con số tiền tách riêng:** tiền thức ăn sẽ ăn trong tuần vs tiền phải trả ở quầy (tính theo gói nguyên). Ví dụ Anh: 58,15 GBP trong ngân sách 60 GBP, nhưng ở quầy 104,24 GBP.
23. **Ngân sách tuần:** người dùng đặt số, tuần được lên trong ngân sách đó; giá là ước tính, không phải giá trực tiếp từ cửa hàng. Trang chủ nói bảng giá địa phương ở Anh, Ireland, Hà Lan, Bỉ, Romania, Mỹ; nơi khác dùng giá trung bình Mỹ quy đổi. Điều khoản viết gọn là dữ liệu Mỹ — điểm chưa khớp, đã ghi trong tài liệu REA.
24. **Chia sẻ danh sách:** gửi dạng văn bản sang ghi chú/tin nhắn/app khác; tại Mỹ, trên iPhone, nút "Get it delivered" copy danh sách và mở DoorDash (chưa có trên Android; không có gì được đặt cho tới khi checkout trong DoorDash).
25. **Hộ gia đình:** khai báo người ăn (người lớn cùng khẩu phần hoặc mục tiêu riêng; trẻ em theo nhóm tuổi + tên tùy chọn); công thức hiển thị khẩu phần từng người và cả nồi chung; danh sách mua cho tất cả.
26. **Chia sẻ hộ gia đình bằng mã:** mã dùng một lần, hết hạn 14 ngày, lưu dạng hash một chiều; người xem thấy tối đa 8 tuần, công thức theo khẩu phần của họ, danh sách và tick chung; người xem không sửa được kế hoạch/câu trả lời/đổi món; người xem không nhận chế độ ăn, dị ứng, sở thích, số đo, hay mục tiêu của người khác.

### 1.5. Chế độ ăn, dị ứng, cửa hàng

27. **Chế độ:** vegetarian, vegan, pescatarian; không thịt lợn, không thịt đỏ, low-carb, không gluten, không sữa, không trứng, không hạt; trang chủ nói 12 dị ứng loại bỏ hoàn toàn nhưng đoạn đã đọc không liệt kê đủ 12 tên.
28. **Ẩn dị ứng khỏi AI:** khi gửi yêu cầu cho AI, dị ứng và chế độ ăn không được nêu tên — app loại nguyên liệu đó trước khi mô hình nhìn thấy danh sách. Tên người trong bàn không bao giờ gửi cho AI.
29. **Cửa hàng và ngày đi chợ:** bước "Where do you shop?" theo quốc gia (ví dụ Lithuania có Šilas và Express Market); chọn ngày đi chợ, quốc gia, tiền tệ, ngôn ngữ.

### 1.6. Tính năng phụ và thương mại

30. **Scan the Plate (chỉ iPhone/iPad):** chụp/mô tả bữa ăn, AI ước tính; ảnh và văn bản không được giữ; chỉ ghi đã quét và có thành công không, xóa sau 30 ngày. Đây là số ước tính, khác với số đã kiểm tra bằng USDA trong kế hoạch tuần.
31. **Chat with Lukas:** trò chuyện với người thật; AI (Anthropic) chỉ dịch tin nhắn và gợi ý trả lời, một người đọc/sửa/gửi mọi trả lời; bản dịch giữ 30 ngày.
32. **Bảng góp ý:** gợi ý (80 ký tự tiêu đề, 1.000 ký tự mô tả), vote, báo cáo, ẩn tác giả; gợi ý riêng tư tới khi Lukas duyệt, sau đó hiển thị ẩn danh; gợi ý bị 3 người báo cáo thì ẩn cho tới khi xem xét.
33. **Mã creator:** mở khóa bằng mã, giới hạn số điện thoại một mã mở được, ghi ID thiết bị và IP của lần thử để chống lạm dụng.
34. **Đăng ký qua Superwall:** tháng 9,99 USD, năm 29,99 USD, năm ưu đãi 19,99 USD trên App Store; không có gói miễn phí, không dùng thử; thanh toán qua Apple/Google, tự gia hạn, hủy trong cài đặt cửa hàng, xóa app không hủy đăng ký; gói hiển thị được cá nhân hóa theo mục tiêu/chế độ ăn/ngân sách.
35. **Thông báo:** có thể chứa tên món, ngân sách, đoạn đầu trả lời chat, trạng thái gợi ý, tuần mới của hộ gia đình — chỉ khi người dùng cho phép.
36. **Tài khoản:** ID ngẫu nhiên, không bắt buộc tên/email; tùy chọn email + mật khẩu (hash), Sign in with Apple/Google (chỉ email); số đo cơ thể ở lại trên điện thoại, chỉ mục tiêu gửi lên server.
37. **Phân tích và quảng cáo (phía server của họ):** PostHog (sự kiện sử dụng), Superwall (gói), Meta/TikTok SDK chỉ trên iOS để đo quảng cáo (không nhận ID tài khoản, email, câu trả lời, kế hoạch; Android không chứa SDK này), Resend (email), Discord (cảnh báo nội bộ), Vercel/Prisma (website), Supabase (database/đăng nhập/file), Fly.io (server xây và kiểm tra kế hoạch), Inngest (job nền).

---

## 2. Đối chiếu với Lắc Đói hiện có — cái gì đã có, cái gì thiếu, cái gì bỏ

Đối chiếu này kiểm tra trực tiếp trong `shake-hungry/` ngày 3/11/2026 (nhánh `dev`, working tree sạch). Số liệu thư viện lấy từ MEMORY: 649 món / 478 nguyên liệu sau các phần ảnh (415 nguyên liệu có kcal trong file), 99 rau, 230 nước uống.

### 2.1. Đã có sẵn — tái sử dụng, không viết lại

| Tính năng Mealbird | Lắc Đói đã có | Ghi chú |
|---|---|---|
| Máy tính mục tiêu (Mifflin–St Jeor) | `App\Profile\TdeeCalculator` — BMR Mifflin–St Jeor (mặc định), Schofield khi thiếu chiều cao; TDEE = BMR × khoảng PAL FAO/WHO/UNU 2004, luôn là khoảng, không số lẻ | Đây là nền tốt hơn ví dụ của Mealbird ở chỗ Lắc Đói đã giữ dạng khoảng. Đừng ép thành một số lẻ khi tích hợp. |
| Hồ sơ thể trạng | `UserProfile`: height_cm, weight_kg, birth_year, sex, activity_level (`nhe`/`vua`/`nang`), diet_type (`chay`/`chay-truong`/`low-carb`), excluded_ingredients, preferred_region_id, target_kcal_per_meal | Thiếu: mục tiêu (giảm/duy trì/tăng), mục tiêu protein/ngày, mục tiêu kcal/ngày, ngân sách chợ, ngày đi chợ, số người ăn. Đó là các trường mới cần thêm ở Giai đoạn 2, mỗi trường một migration riêng. |
| Món theo bữa | `DishMealSlot`: `sang`, `trua`, `chieu`, `toi`, `an-vat` | Khớp với sáng/trưa/tối + ăn nhẹ của Mealbird, chỉ khác tên. Trang Lắc đã dùng tỉ lệ kcal theo bữa (sáng 0,25 / trưa 0,35 / chiều 0,15 / tối 0,25 / ăn vặt 0,10) để gợi ý mục tiêu theo bữa. |
| Dinh dưỡng món, có protein | `DishNutrition`: kcal_min/kcal_max, protein_g, fat_g, carb_g, fiber_g, missing_ingredients, unverified_ingredients | Đủ để làm kiểm tra tuần theo protein + kcal, với điều kiện xử lý đúng cờ thiếu/chưa kiểm chứng (xem §3). |
| Lắc món + đổi món | API `/api/shake`, `/api/shake/tray-swap`, `/api/shake/tray-candidates`; trang `shake.blade.php` | Mẫu cho tính năng "đổi một bữa" trong tuần: chọn ứng viên cùng bữa, tôn trọng loại trừ và mục tiêu. |
| Nhật ký ăn, ghi nhận đã ăn | `/nhat-ky`, API `/api/meal-logs` (tạo/sửa/xóa) | Tương đương log meal + streak ở mức tối thiểu; streak chưa có thì thêm sau, không gộp vào Giai đoạn 2. |
| Yêu thích, phản hồi | `/api/favorites`, `/api/feedback`, trang Hồ sơ | Tương đương tim món và feedback ticket; không cần bảng góp ý công khai kiểu Mealbird ở giai đoạn đầu. |
| Dị ứng (mới chỉ cho nước uống) | `AllergenDisplay::forDrink` — gắn đủ thì hiện nhãn, gắn một phần thì "chưa đủ dữ kiện", không kết luận an toàn | Cần mở rộng sang món ăn trước khi dùng dị ứng để lọc tuần, nếu không tuần sẽ lọc sai. Đây là một task riêng, không gộp vào builder tuần. |
| Trang món, nguyên liệu, rau, quả, nước uống; hệ thiết kế v2 `ld-*` | Toàn bộ thư viện + `docs/09-standards/DESIGN-SYSTEM.md` | Giao diện tuần ăn phải theo mẫu trang món, không copy giao diện Mealbird. |
| Hồ sơ không cần đăng nhập để đổi lựa chọn lắc | Theo MEMORY: tính năng lắc ở trang Hồ sơ, không cần đăng nhập để thay đổi lựa chọn, mặc định luôn bật | Lưu ý khi thiết kế tuần ăn: tuần ăn gắn với tài khoản thì cần đăng nhập; tuần ăn thử (không lưu) thì có thể cho khách xem như Mealbird cho xem tuần ví dụ. Phân biệt hai chế độ này ngay từ đầu. |

### 2.2. Thiếu — phải xây mới, theo thứ tự

1. **Mục tiêu tuần:** trường mục tiêu protein/ngày + kcal/ngày (hoặc suy ra từ TDEE + mục tiêu + quy tắc protein, xem quyết định Q1), mục tiêu giảm/duy trì/tăng, ngân sách (tùy chọn, xem §2.3).
2. **Mô hình tuần ăn:** `week_plans` (người dùng, ngày bắt đầu, mục tiêu snapshot, trạng thái), `week_plan_days` (ngày, tổng protein/kcal, trạng thái đạt/thiếu/chưa đủ dữ kiện), `week_plan_meals` (ngày, bữa, món, khẩu phần, lặp lại, ăn ngoài, đổi từ món nào). Snapshot mục tiêu vào tuần là bắt buộc: đổi mục tiêu sau đó không được sửa ngược tuần cũ, giống Mealbird.
3. **Week builder (service, không phải AI viết số):** chọn món từ thư viện Lắc Đói theo: bữa, chế độ ăn, nguyên liệu loại trừ, vùng (nếu có), mức lặp cho phép; cộng dồn protein/kcal theo `DishNutrition`; dừng khi mỗi ngày đạt sàn protein và nằm trong dải kcal, hoặc ghi rõ ngày nào thiếu và thiếu bao nhiêu.
4. **Week validator:** chạy lại phép cộng từ dữ liệu món (không tin số đã lưu), trả về từng ngày: đạt / thiếu / chưa đủ dữ kiện. Ngày có món thiếu protein/kcal hoặc có nguyên liệu unverified ảnh hưởng tới tổng thì không được ghi "đạt".
5. **Hiển thị phép tính:** trang chi tiết bữa trong tuần cho xem từng nguyên liệu theo gram (từ `DishIngredient`), số dinh dưỡng của nguyên liệu, phép nhân ra tổng món — tương đương "How we compute this" của Mealbird, nhưng dùng dữ liệu và engine của Lắc Đói.
6. **Thao tác tuần:** đổi một bữa (tái dùng logic ứng viên của lắc), đổi ngày bắt đầu, bắt đầu lại phần còn lại, đánh dấu ăn ngoài, loại thực phẩm khỏi tuần.
7. **Danh sách đi chợ:** cộng gộp `DishIngredient` theo nguyên liệu, nhóm theo nhóm nguyên liệu hiện có (chưa có khái niệm "gian hàng" — cần mapping nhóm → gian hàng, một task dữ liệu riêng, không đoán). Giai đoạn đầu chỉ ghi gram và tên nguyên liệu, **không ghi gói và không ghi giá** (xem §2.3).
8. **Trang tuần ăn:** `/tuan-an` (tên route đề xuất, chưa chốt) theo mẫu trang món: tiêu đề, dải mục tiêu từng ngày, danh sách bữa, nút đổi bữa, nút đi chợ, nhãn "chưa đủ dữ kiện" khi cần.

### 2.3. Không có dữ liệu thật — không được bịa, và hệ quả

- **Giá chợ:** Lắc Đói chỉ có giá quả (snapshot 31/10, 4 quả chưa có giá) và rau chưa có giá nào (theo MEMORY). Không có giá nguyên liệu/món. Vì vậy **tính năng ngân sách và "tiền ở quầy" của Mealbird không thể tích hợp thật trong Giai đoạn 2**. Lựa chọn: (a) bỏ ngân sách khỏi Giai đoạn 2, chỉ làm tuần + đi chợ theo gram; (b) làm ngân sách ở giai đoạn dữ liệu riêng sau khi có bảng giá. Đề xuất: (a).
- **Gói bán thật:** không có dữ liệu gói (bao nhiêu gram một túi, một chai). Không được tự gán gói cho nguyên liệu. Danh sách đi chợ Giai đoạn 2 chỉ ghi tổng gram, kèm ghi chú "chưa quy đổi ra gói".
- **Thời gian nấu:** cần kiểm tra từng món có trường thời gian nấu hay không trước khi hiển thị "tổng giờ nấu tuần" kiểu Mealbird. Nếu không có đủ, bỏ chỉ số này khỏi Giai đoạn 2 thay vì ước tính.
- **Dị ứng món ăn:** chưa có suy luận dị ứng cho món như đã có cho nước uống. Tuần ăn Giai đoạn 2 chỉ lọc theo `excluded_ingredients` và `diet_type` đã có; nhãn dị ứng chỉ hiện khi dữ liệu đủ, theo đúng quy tắc "chưa đủ dữ kiện".

### 2.4. Bỏ — không clone, có lý do

| Tính năng Mealbird | Quyết định | Lý do |
|---|---|---|
| Đăng ký trả phí qua Superwall, paywall, gói tháng/năm, không dùng thử | **Bỏ** | Lắc Đói là thư viện/hồ sơ, không phải sản phẩm đăng ký. Thêm paywall sẽ đổi mô hình sản phẩm và cần quyết định kinh doanh riêng, không phải quyết định kỹ thuật trong lượt này. |
| Scan the Plate (AI ước tính từ ảnh) | **Bỏ** | Trái với quy tắc độ chính xác của dự án: số ước tính từ ảnh không kiểm chứng được bằng engine, và Mealbird cũng tự phân biệt nó với số đã kiểm tra. |
| Chat with Lukas (người thật + AI dịch/gợi ý) | **Bỏ** | Cần con người trực và nhà cung cấp AI; Lắc Đói đã có feedback ticket cho phản hồi. |
| Bảng góp ý công khai, vote, ẩn tác giả | **Bỏ ở Giai đoạn 2** | Feedback ticket hiện có đã đủ cho phản hồi; bảng công khai là tính năng cộng đồng riêng. |
| Mã creator, đo quảng cáo Meta/TikTok, PostHog, Discord cảnh báo | **Bỏ** | Không liên quan tới trải nghiệm người dùng Lắc Đói; không thêm SDK theo dõi. |
| DoorDash (giao hàng Mỹ, chỉ iPhone) | **Bỏ** | Không áp dụng cho thị trường Việt Nam và dữ liệu Lắc Đói; chia sẻ danh sách dạng văn bản có thể làm sau nếu cần, không phải Giai đoạn 2. |
| Hộ gia đình chia sẻ bằng mã | **Hoãn sang Giai đoạn 4** | Cần thiết kế riêng tư (hash mã, hết hạn, quyền chỉ xem, dữ liệu nào người xem được/không được) và kiểm tra kỹ. Không gộp vào tuần ăn lõi. |

---

## 3. Thiết kế tuần ăn cho Lắc Đói — những điểm phải chốt trước khi code

### 3.1. Kiểm tra tuần theo đúng quy tắc độ chính xác

- Tổng ngày = tổng từ `DishNutrition` của từng món trong ngày, theo khẩu phần đã chọn. Không cộng số đã lưu sẵn mà không kiểm tra lại khi hiển thị validator.
- Món có `kcal_min`/`kcal_max` là khoảng thì ngày cũng hiển thị khoảng, không ép thành một số.
- Món thiếu protein hoặc kcal, hoặc có nguyên liệu trong `missing_ingredients`/`unverified_ingredients` ảnh hưởng tới tổng, thì ngày đó ghi **"chưa đủ dữ kiện"**, không ghi "đạt" và không ghi "thiếu" — vì thiếu và chưa biết là hai trạng thái khác nhau.
- Ngày "thiếu" chỉ dùng khi dữ liệu đủ và tổng thật sự dưới sàn protein hoặc ngoài dải kcal, kèm số thiếu cụ thể (ví dụ thiếu 12 g protein, vượt 80 kcal).
- Cảnh báo kết hợp (`/ket-hop-can-luu-y`) vẫn là lớp thông tin hiển thị, không chặn, không lọc, không chấm điểm tuần — theo ranh giới đã ghi trong MEMORY. Tuần ăn không được tự loại món chỉ vì cặp kết hợp, trừ khi bạn quyết khác bằng văn bản.

### 3.2. Món nào được vào tuần

- Chỉ xét món có `meal_slots` chứa bữa cần lên kế hoạch.
- Tôn trọng `diet_type` và `excluded_ingredients` trong hồ sơ; tôn trọng `preferred_region_id` nếu người dùng đã chọn (không ép vùng khi họ không chọn).
- Mức lặp: người dùng chọn (ví dụ: cho phép lặp tối đa N lần/tuần, hoặc nấu một lần ăn nhiều bữa). Builder phải ghi rõ món nào lặp và lặp bao nhiêu lần, giống Mealbird nói trước về lặp.
- Không dùng AI để viết số dinh dưỡng, không dùng AI để chọn món rồi giấu logic. Builder là thuật toán chọn lọc có thể giải thích được: tiêu chí chọn, thứ tự ưu tiên, và lý do ngày thiếu (nếu có) đều hiển thị được.

### 3.3. Vị trí trong sản phẩm

Đề xuất (chưa chốt, cần bạn duyệt): mục **"Tuần ăn"** mới, vào từ Hồ sơ (`/ho-so`) và từ Thư viện, dùng chung khung `ld-*` với trang món. Khách chưa đăng nhập xem được **tuần ví dụ** (không lưu, không cá nhân hóa sâu) như Mealbird cho xem trước khi trả tiền; người dùng đã đăng nhập mới lưu tuần, đổi bữa và ghi nhật ký từ tuần. Lắc món hiện tại giữ nguyên, không thay thế bằng tuần ăn — tuần ăn là lớp kế hoạch trên thư viện, lắc là lớp gợi ý nhanh.

---

## 4. Kế hoạch theo giai đoạn → phần → task (không gộp)

Mỗi giai đoạn xong thì báo cáo và chờ duyệt rồi mới sang giai đoạn tiếp theo. Mỗi task xong thì commit riêng. Không xuất bundle trừ khi bạn yêu cầu, và bundle luôn tính từ commit cuối trên git.

### Giai đoạn 1 — Phân tích và kế hoạch (lượt này, đã xong khi bạn đọc tài liệu này)

- Phần 1.1: Danh mục tính năng Mealbird (§1) — xong.
- Phần 1.2: Đối chiếu với Lắc Đói (§2) — xong.
- Phần 1.3: Thiết kế và quyết định cần duyệt (§3, §5) — đang chờ bạn.

### Giai đoạn 2 — Lõi tuần ăn (đề xuất làm tiếp theo, sau khi bạn duyệt)

- Phần 2.1 — Mục tiêu tuần: migration thêm trường mục tiêu (protein/ngày, kcal/ngày, mục tiêu giảm/duy trì/tăng — theo Q1), form ở Hồ sơ, hiển thị công thức TDEE + quy tắc protein từng bước như Mealbird công bố, nhưng dùng TDEE khoảng của Lắc Đói. Commit riêng.
- Phần 2.2 — Mô hình tuần: migration `week_plans` / `week_plan_days` / `week_plan_meals`, snapshot mục tiêu. Commit riêng.
- Phần 2.3 — Week builder + validator: service chọn món và kiểm tra từng ngày, xử lý khoảng kcal và cờ thiếu/chưa kiểm chứng. Commit riêng, kèm test cho các trường hợp: ngày đạt, ngày thiếu, ngày chưa đủ dữ kiện.
- Phần 2.4 — Trang tuần ăn: `/tuan-an` theo mẫu trang món, hiển thị dải từng ngày, bữa, phép tính nguyên liệu. Commit riêng.
- Phần 2.5 — Thao tác tuần: đổi một bữa, đổi ngày bắt đầu, bắt đầu lại, ăn ngoài, loại thực phẩm. Mỗi thao tác một task, một commit, kiểm tra sau mỗi task.
- Phần 2.6 — Danh sách đi chợ theo gram, nhóm theo nhóm nguyên liệu hiện có, không gói, không giá, kèm ghi chú rõ ràng. Commit riêng.
- Nghiệm thu Giai đoạn 2: chạy validate dữ liệu, kiểm tra bằng trình duyệt local (không qua tunnel Pinggy, theo bài học đã ghi), đếm tuần/món/ngày mẫu và báo cáo theo tầng bằng chứng.

### Giai đoạn 3 — Dị ứng món ăn và gian hàng đi chợ (sau Giai đoạn 2)

- Mở rộng suy luận dị ứng từ nước uống sang món ăn, theo quy tắc độ phủ (đủ thì nhãn, thiếu thì "chưa đủ dữ kiện").
- Mapping nhóm nguyên liệu → gian hàng đi chợ, có kiểm tra từng nhóm.
- Chỉ sau đó mới cho phép lọc tuần theo dị ứng một cách đầy đủ.

### Giai đoạn 4 — Hộ gia đình và các tính năng mở rộng (chỉ khi bạn duyệt riêng)

- Chia sẻ hộ gia đình bằng mã (hash một chiều, hết hạn 14 ngày, người xem chỉ đọc, dữ liệu nào được/không được chia sẻ — thiết kế riêng tư phải duyệt trước khi code).
- Ngân sách và giá: chỉ làm sau khi có bảng giá nguyên liệu/món đã kiểm chứng; không dùng giá ước tính không nguồn.
- Chia sẻ danh sách dạng văn bản; streak; tuần ví dụ cho khách — mỗi tính năng một task riêng.

### Không nằm trong kế hoạch này

Paywall/đăng ký, Scan the Plate, Chat với người thật, bảng góp ý công khai, mã creator, SDK quảng cáo/phân tích, DoorDash — xem §2.4. Muốn thêm bất kỳ mục nào trong số này thì cần một quyết định riêng, không tự thêm vào trong lúc làm Giai đoạn 2.

---

## 5. Quyết định cần bạn chốt trước Giai đoạn 2 (gom thành một nhóm, không hỏi lẻ)

**Q1 — Quy tắc protein.** Chọn một trong hai, hoặc đưa quy tắc khác của bạn:
- (a) Theo Mealbird: protein = 1 g × cân nặng (lb) tại BMI 25 theo chiều cao, giới hạn tối đa 35% tổng kcal. Ưu: giống đối chiếu, dễ giải thích. Nhược: cần chiều cao; người không có chiều cao thì phải dùng cân nặng thật hoặc để trống.
- (b) Đơn giản hơn: người dùng tự nhập protein/ngày, app chỉ gợi ý từ TDEE và cân nặng, không ép công thức. Ưu: không áp đặt một công thức chưa kiểm chứng y tế cho mọi người. Nhược: kém "tự động" hơn Mealbird.
- Kèm theo: mục tiêu gồm những lựa chọn nào? Đề xuất: giảm / duy trì / tăng, với mức điều chỉnh kcal ghi rõ (ví dụ giảm 20% như ví dụ của Mealbird — cần bạn chốt mức, không tự lấy).

**Q2 — Phạm vi Giai đoạn 2.** Bạn duyệt đúng phạm vi §Giai đoạn 2 (lõi tuần ăn, không ngân sách, không gói, không hộ gia đình) hay muốn cắt/bớt thêm? Ví dụ: bỏ danh sách đi chợ khỏi Giai đoạn 2, hoặc chỉ làm tuần ăn mà chưa làm thao tác đổi bữa.

**Q3 — Món chưa đủ dữ kiện có được vào tuần không?**
- (a) Chỉ món có đủ protein + kcal mới được vào tuần; món thiếu thì loại khỏi ứng viên và ghi rõ lý do. Ưu: tuần nào cũng kiểm tra được trọn vẹn.
- (b) Món thiếu vẫn vào được, nhưng ngày chứa nó luôn ghi "chưa đủ dữ kiện", không ghi "đạt". Ưu: thư viện dùng được rộng hơn; nhược: nhiều tuần sẽ ở trạng thái chưa đủ dữ kiện cho tới khi dữ liệu món hoàn thiện.
- Đề xuất của tôi: (a) cho Giai đoạn 2, vì nó giữ được tuyên bố "mọi ngày đều đã kiểm tra" — nếu bạn chọn (b) thì tuyên bố đó phải đổi.

**Q4 — Xác nhận các mục bỏ ở §2.4.** Đặc biệt: không paywall, không Scan the Plate, không chat người thật. Nếu bạn muốn giữ lại mục nào, nói rõ mục đó để tách thành quyết định riêng.

**Q5 — Vị trí và tên gọi.** Đồng ý với mục "Tuần ăn" vào từ Hồ sơ và Thư viện, route đề xuất `/tuan-an`, khách xem tuần ví dụ không lưu? Hay bạn muốn tên/ vị trí khác?

---

## Rủi ro và giới hạn đã thấy trước

1. **Dữ liệu món chưa đủ để mọi tuần đều "đạt".** Nhiều món có khoảng kcal hoặc nguyên liệu chưa kiểm chứng; nếu chọn Q3(a) thì số món đủ điều kiện vào tuần có thể ít hơn bạn kỳ vọng. Cần đếm thật số món đủ điều kiện ở đầu Giai đoạn 2 và báo cáo trước khi xây builder, không hứa trước.
2. **Công thức protein không phải lời khuyên y tế.** Dù chọn Q1(a) hay (b), trang mục tiêu phải giữ miễn trừ y tế như Lắc Đói đã có (`/mien-tru-y-te`) và ghi rõ mục tiêu là ước tính chung, không phải chỉ định cá nhân.
3. **Tuần ăn không thay thế Lắc.** Lắc là gợi ý nhanh theo bữa; tuần ăn là kế hoạch 7 ngày. Hai tính năng phải sống chung, không được làm hỏng luồng lắc hiện tại khi thêm tuần ăn.
4. **Working tree và các chat khác.** Nhánh `dev` hiện sạch tại thời điểm đối chiếu, nhưng có các file chưa commit thuộc về chat giao diện khác trong các lượt trước (đã ghi trong MEMORY). Giai đoạn 2 không được commit hay hoàn tác các file đó; chỉ commit file của tuần ăn.
5. **Host và tunnel.** Kiểm chứng Giai đoạn 2 bằng trình duyệt local (Chromium headless), không dùng link Pinggy làm bằng chứng chính, theo bài học đã ghi.

---

## Nguồn

- Tài liệu phân tích REA trước đó: `mealbird-phan-tich-rea.md`
- Trang chủ Mealbird: https://mealbird.app/
- Điều khoản: https://mealbird.app/terms — Chính sách riêng tư: https://mealbird.app/privacy
- App Store Mỹ: https://apps.apple.com/us/app/mealbird-macro-meal-planner/id6794577462
- AppBrain (Google Play): https://www.appbrain.com/app/mealbird-macro-meal-planner/app.mealbird
- Code Lắc Đói đã đối chiếu: `shake-hungry/app/Profile/TdeeCalculator.php`, `shake-hungry/app/Models/UserProfile.php`, `shake-hungry/app/Models/DishNutrition.php`, `shake-hungry/app/Models/DishMealSlot.php`, `shake-hungry/app/Http/Controllers/PublicWeb/ProfileController.php`, `shake-hungry/routes/web.php`

*Hết tài liệu Giai đoạn 1.*
