# WEEK-001 — Tuần ăn, Giai đoạn 2: lõi tuần ăn

**Quyết định đã chốt (chủ dự án, sáng 2026-11-04, trong main chat):**
1. "Làm cả 2" cho quy tắc protein: app tính gợi ý theo công thức Mealbird
   (1 g/lb tại BMI 25, giới hạn 35% kcal) và cho người dùng tự nhập/ghi đè.
2. Phạm vi Giai đoạn 2 chỉ lõi tuần ăn, không ngân sách, không gói, không hộ
   gia đình.
3. Chỉ món đủ protein + kcal mới được vào tuần.
4. Bỏ paywall, Scan the Plate, chat người thật, bảng góp ý công khai, SDK
   quảng cáo và DoorDash.
5. Tách Tuần ăn thành 1 trang riêng, route đề xuất `/tuan-an`, vào từ Hồ sơ
   và Thư viện, khách xem tuần ví dụ không lưu.

"Clone" chỉ là clone chức năng, không sao chép tên, linh vật, văn bản, ảnh,
công thức hay code của Mealbird. Kế hoạch đầy đủ: tài liệu Giai đoạn 1
`~/workspace/your_files/mealbird-lacdoi-ke-hoach-tich-hop.md` §4.

**Quy tắc làm việc:** giai đoạn → phần → task, một task một lần, một commit
riêng, báo cáo sau mỗi task và chờ duyệt rồi mới sang task tiếp theo.

> ## Đính chính 2026-11-04 07:51 — thay quyết định số 2 buổi sáng
>
> Chủ dự án nhắn trong chat REA mealbird: Giai đoạn 2 **bổ sung thêm cả
> thông tin ngân sách, gói, hộ gia đình** — không còn chỉ lõi tuần ăn.
> 4 quyết định còn lại giữ nguyên. Task 1 vẫn đứng vì chưa đụng 3 mảng
> mới; Task 2 (API Hồ sơ) chờ duyệt Task 1 **và** làm rõ mức độ của 3 mảng
> mới trước khi code, vì Lắc Đói hiện chưa có giá nguyên liệu/món, chưa
> có dữ liệu gói bán, và chưa có thiết kế riêng tư cho chia sẻ hộ gia
> đình — làm tổng tiền/gói thật/chia sẻ thật ngay sẽ phải bịa dữ liệu hoặc
> bỏ qua thiết kế riêng tư, cả hai đều trái quy tắc dự án.
>
> Cùng lượt này, chủ dự án cho phép lấy dữ liệu từ trang Mealbird và yêu
> cầu so sánh Mealbird với nguồn khác. Nghiên cứu so sánh đang chạy riêng
> (ngân sách/gói/hộ gia đình, đối chiếu USDA + app khác + bối cảnh Việt
> Nam); giới hạn: chỉ trang công khai, không copy văn bản marketing,
> công thức, ảnh hay dữ liệu món của Mealbird vào Lắc Đói.

## Phần 2.1 — Mục tiêu tuần

### Task 1 — ĐÃ XONG (2026-11-04, đã duyệt 09:02)
- Migration `2026_11_04_100001_add_week_targets_to_user_profiles.php`:
  `goal` (varchar 16, null), `target_protein_g_per_day` và
  `target_kcal_per_day` (unsigned smallint, null).
- `App\Models\UserProfile`: casts integer cho hai trường mục tiêu.
- `App\Profile\WeekTargetSuggester`: hàm thuần tuý tính gợi ý protein từng
  bước (BMI 25 → lb → 1 g/lb → giới hạn 35% kcal / 4); trả null khi thiếu
  chiều cao hoặc thiếu kcal mục tiêu. Không ghi đè mục tiêu đã lưu, không
  sinh số dinh dưỡng cho món (QT-1).
- Test `tests/Unit/WeekTargetSuggesterTest.php` (3 test, không cần DB).
- Tài liệu cùng commit: `docs/03-data-model/DATA-MODEL.md` (3 dòng mới ở
  bảng `user_profiles`) + file này.
- Chưa làm trong Task 1 (cố ý): API Hồ sơ, form Hồ sơ, và tỉ lệ điều chỉnh
  kcal theo mục tiêu.

### Chưa chốt — cần chủ dự án quyết trước khi suy kcal từ mục tiêu
Tỉ lệ điều chỉnh kcal theo `goal` (giảm bao nhiêu %, tăng bao nhiêu %)
chưa nằm trong 5 quyết định. Task 1 không tự áp -20% hay bất kỳ mức nào:
`goal` chỉ được lưu, chưa dùng để suy `target_kcal_per_day`. Cần chốt tỉ lệ
này trước Task suy kcal từ TDEE + mục tiêu (thuộc Phần 2.1, sau form Hồ sơ).

### Task 2 — ĐÃ XONG (2026-11-04, đã duyệt 2026-11-04 20:57 khi chủ dự án lệnh làm Task 3)
- `ProfileController::show` và `update` (API `/api/profile`): validate
  `goal` (giam/duy-tri/tang), `target_protein_g_per_day` (20–500),
  `target_kcal_per_day` (800–6.000); cả hai đều trả thêm
  `week_target_suggestion` từ `WeekTargetSuggester`.
- Gợi ý chỉ tính khi đã có chiều cao VÀ đã nhập kcal mục tiêu; thiếu thì
  null, không tự lấy TDEE hay cân nặng thật làm thay thế. Số người dùng
  nhập không bị gợi ý ghi đè.
- Test `tests/Feature/WeekTargetApiTest.php` (3 test, MySQL test): lưu
  mục tiêu + gợi ý 175 g cho cao 180 cm / 2.000 kcal, từ chối giá trị
  ngoài giới hạn, gợi ý null khi chưa nhập kcal. Chạy lẻ 3 passed.

### Quyết định mới 2026-11-04 09:02 (ghi nhận, chưa code)
- Phạm vi Giai đoạn 2 là lõi tuần ăn **cộng thêm** ngân sách, gói, hộ gia
  đình — không phải chỉ 3 mảng đó.
- Task 1 đã duyệt.
- Tổng tiền thật, quy đổi gói thật và chia sẻ bằng mã làm ngay sau các
  task tuần ăn, với điều kiện: tìm từ nhiều nguồn (bao gồm Mealbird),
  không được bịa; giá/gói được phép bỏ qua riêng tư theo quyết định của
  chủ dự án. Vì vậy tổng tiền/gói thật chỉ code được sau task nghiên cứu
  giá/gói có nguồn — không code tổng tiền trước khi có bảng giá/gói.

### Task 3 — ĐÃ XONG (2026-11-04, chờ duyệt)
- Chỉ sửa `resources/views/profile.blade.php` (không đổi schema, không
  đổi API Task 2, không thêm trang tuần ăn): trong form "Thể trạng và
  ăn uống" thêm mục "Mục tiêu tuần ăn" với `goal` (giam/duy-tri/tang),
  `target_protein_g_per_day` (20–500) và `target_kcal_per_day`
  (800–6.000), dùng đúng giới hạn validate của API Task 2.
- Hiển thị gợi ý protein từ `week_target_suggestion` của API: gợi ý
  null thì giữ câu hướng dẫn (nhập chiều cao + kcal mỗi ngày rồi lưu),
  không tự suy từ TDEE hay cân nặng thật. Nút "Dùng gợi ý này" chỉ
  điền vào ô protein, không tự lưu; chỉ khi người dùng bấm "Lưu hồ
  sơ" số đó mới được lưu — gợi ý không bao giờ ghi đè số đã lưu.
- Ghi rõ trong form: `goal` hiện chỉ được lưu, chưa dùng để tự tính
  kcal (tỉ lệ điều chỉnh theo mục tiêu chưa chốt), và mục tiêu chỉ là
  ước tính chung, không phải lời khuyên y tế (link `/mien-tru-y-te`).
  Form chỉ hiện cho người dùng đã đăng nhập; khách không thấy form.
- Test mới `tests/Feature/WeekTargetFormTest.php` (2 test): đã đăng
  nhập thấy 3 trường + khối gợi ý; khách không thấy 3 trường.
- Kiểm chứng chạy thật: `WeekTargetFormTest` + `WeekTargetApiTest`
  5 test pass (24 assertions); Chromium headless local với tài khoản
  tạm đăng ký qua giao diện: nhập cao 180 cm, goal giam, kcal 2.000,
  protein 150 → lưu → gợi ý 175 g kèm từng bước, bấm "Dùng gợi ý
  này" → ô protein thành 175 → lưu lại → API trả goal giam,
  protein 175, kcal 2.000, gợi ý 175. Tài khoản tạm đã xoá sau kiểm
  chứng, không còn profile mồ côi. Chưa làm trong Task 3 (cố ý):
  trang `/tuan-an`, mô hình tuần, builder/validator.

## Phần 2.2 — Mô hình tuần

### Task 1 — ĐÃ XONG (2026-11-04, theo lệnh duyệt toàn bộ lộ trình, không hỏi duyệt giữa chừng)
- Migration `2026_11_04_210000_create_week_plans_tables.php`: `week_plans`
  (user, start/end 7 ngày, snapshot goal/protein/kcal, status active/archived,
  unique user+start), `week_plan_days` (ngày, protein, khoảng kcal, trạng thái
  dat/thieu/chua-du-du-lieu, unique plan+ngày), `week_plan_meals` (ngày, món,
  bữa, khẩu phần mặc định 1, ăn ngoài, món gốc khi đổi bữa, unique ngày+bữa).
- Model `WeekPlan` / `WeekPlanDay` / `WeekPlanMeal` + quan hệ.
- Tài liệu cùng commit: DATA-MODEL §5 (3 bảng mới) + file này.
- Test `tests/Feature/WeekPlanModelTest.php`: snapshot không bị đổi hồ sơ
  sau đó sửa ngược, quan hệ ngày/bữa đúng.
- Chưa làm trong Phần 2.2 (cố ý): builder, validator, API, trang tuần ăn.

## Phần 2.3 — Builder + validator + API tuần ăn

### Task 1 — ĐÃ XONG (2026-11-04, theo lệnh duyệt toàn bộ lộ trình)
- `App\Week\WeekValidator`: tính lại từ món, trạng thái dat/thieu/
  chua-du-du-lieu; dat khi protein >= sàn và kcal mục tiêu nằm trong
  khoảng [kcal_min, kcal_max] của ngày (không tự đặt dải ±%); ăn ngoài,
  món thiếu/missing/unverified/unknown, thiếu bữa, hoặc chưa có mục tiêu
  đều thành chưa đủ dữ kiện, không thành đạt hay thiếu giả.
- `App\Week\WeekBuilder`: chỉ món sạch (protein + khoảng kcal, không
  missing/unverified, confidence khác unknown); xếp ứng viên theo protein
  giảm dần rồi kcal_min tăng dần rồi id (tất định); mỗi món tối đa 2 lần/
  tuần, hết mới lặp thêm; lọc cứng excluded_ingredients và chay/chay-truong
  (nhãn diet:man + nhóm thịt/thủy sản, như Recommender); low-carb không
  lọc cứng vì chưa có ngưỡng chốt; vùng yêu thích chỉ ưu tiên khi >= 5
  món/bữa; mục tiêu lấy từ hồ sơ đã nhập, không suy từ goal; tuần mới
  archive các tuần active cũ.
- API `WeekPlanController` (auth): GET /api/week-plans, POST /api/week-plans
  (start_date, tạo 7 ngày × 4 bữa sáng/trưa/tối/ăn vặt), GET
  /api/week-plans/{id} (chỉ của chính người dùng, validator tính live).
- Test `tests/Feature/WeekBuilderTest.php` 5 test pass: builder 7×4,
  snapshot, đạt; thiếu kèm số thiếu 20 g; missing thành chưa đủ dữ kiện;
  loại trừ nguyên liệu; API chỉ trả tuần của chính mình.
- Chưa làm trong Phần 2.3 (cố ý): trang /tuan-an, thao tác tuần, đi chợ.

## Phần 2.4 — Trang `/tuan-an`

### Task 1 — ĐÃ XONG (2026-11-04, theo lệnh duyệt toàn bộ lộ trình)
- Route `/tuan-an` + blade `tuan-an.blade.php` theo ld-*: khách thấy
  ngày ví dụ, người dùng thấy form tạo tuần (ngày bắt đầu), danh sách
  tuần với số ngày đạt/thiếu/chưa đủ dữ kiện, chi tiết từng ngày với
  trạng thái, protein, khoảng kcal, số thiếu/vượt, và từng bữa link tới
  trang món.
- Vào từ sidebar (mục Của bạn) và từ Hồ sơ ("Tuần ăn của bạn →").
- API tuần ví dụ công khai GET /api/week-plans/example: 1 ngày mẫu từ
  ứng viên thật, không lưu, không cá nhân hoá, không kết luận đạt/thiếu
  (đặt trước route /week-plans/{id} để không bị nuốt).
- Tiếp tục 2026-11-04: tuần ví dụ cho khách nâng lên đủ 7 ngày
  (`WeekBuilder::exampleWeek`, lặp món tối đa 2 lần/tuần như builder
  chính, kho ví dụ nhỏ thì cho phép lặp thêm còn hơn bỏ trống bữa),
  trang khách render 7 ngày thay vì 1 ngày. Vẫn không lưu, không cá
  nhân hoá, không kết luận đạt/thiếu.
- Test `tests/Feature/WeekPageTest.php` 3 test pass (khách/auth/API ví dụ).
- Chưa làm trong Phần 2.4 (cố ý): thao tác tuần, đi chợ — trang hiện
  ghi rõ thao tác sẽ có ở phần tiếp theo, không giả vờ có nút.

## Phần 2.5 — Thao tác tuần (mỗi thao tác một commit)

### Task 1 — Đổi một bữa — ĐÃ XONG (2026-11-04)
- API: GET /api/week-plans/{plan}/meals/{meal}/candidates (ứng viên kèm
  dinh dưỡng, xem trước khi áp) và POST swap (dish_id, chỉ món đủ điều
  kiện cho bữa đó mới được áp; món thiếu/missing/unverified hoặc vi phạm
  loại trừ/chay bị 422; ghi replaced_from_dish_id, xoá cờ ăn ngoài, tính
  lại ngày).
- Trang /tuan-an: nút Đổi ở mỗi bữa, hiện danh sách ứng viên kèm kcal và
  protein, bấm chọn mới áp dụng.
- Test `tests/Feature/WeekOperationsTest.php` (2 test đầu): đổi bữa ghi
  món gốc + tính lại protein, đổi sang món thiếu bị 422.

### Task 2 — Đổi ngày bắt đầu — ĐÃ XONG (2026-11-04)
- API POST /api/week-plans/{plan}/change-start: dịch toàn bộ ngày theo
  cùng độ lệch, giữ nguyên món, cập nhật end_date; trùng start_date với
  tuần khác của cùng người dùng thì 422. Cập nhật ngày theo thứ tự tránh
  va chạm unique (plan, date) — dùng query trực tiếp vì quan hệ days()
  đã orderBy sẵn.
- Trang /tuan-an: form đổi ngày bắt đầu trong chi tiết tuần.
- Test thêm: dịch ngày + giữ món + trùng ngày bị 422.

### Task 3 — Bắt đầu lại phần còn lại — ĐÃ XONG (2026-11-04)
- `WeekBuilder::rebuildFrom`: ngày trước mốc giữ nguyên (kể cả món đổi
  tay), ngày từ mốc trở đi xoá bữa cũ và xây lại bằng logic ứng viên,
  số lần dùng tính từ ngày giữ lại + ngày vừa xây; API POST
  /api/week-plans/{plan}/restart-remaining, mốc ngoài tuần thì 422.
- Trang /tuan-an: chọn ngày + nút bắt đầu lại, có xác nhận trước khi áp.
- Test thêm: ngày trước giữ nguyên món, 5 ngày từ mốc xây lại đủ 4 bữa,
  mốc ngoài tuần bị 422.

### Task 4 — Đánh dấu ăn ngoài — ĐÃ XONG (2026-11-04)
- API POST /api/week-plans/{plan}/meals/{meal}/eat-out (is_eat_out):
  bữa ăn ngoài không tính vào tổng ngày, validator chuyển ngày thành
  chưa đủ dữ kiện (không biết đã ăn gì ngoài); bỏ dấu thì tính lại và
  có thể trở lại đạt.
- Trang /tuan-an: nút Ăn ngoài / Bỏ ăn ngoài ở mỗi bữa.
- Test thêm: đánh dấu → chưa đủ dữ kiện với 1 bữa chưa biết, bỏ dấu → đạt.

### Task 5 — Loại thực phẩm khỏi tuần — ĐÃ XONG (2026-11-04)
- API POST /api/week-plans/{plan}/exclude-ingredient: thêm slug vào
  excluded_ingredients của Hồ sơ, thay các bữa trong tuần chứa nó bằng
  ứng viên khác, bữa không còn ứng viên thì xoá (ngày thành chưa đủ dữ
  kiện); bữa đang ăn ngoài giữ nguyên vì không ăn món đó.
- Trang /tuan-an: tìm nguyên liệu theo tên, bấm loại sau khi xác nhận.
- Test thêm: tuần có bữa chứa lạc → loại lạc → không còn bữa chứa lạc,
  hồ sơ có lạc trong danh sách loại trừ.

## Phần 2.5 đã xong cả 5 thao tác.

## Phần 2.6 — Danh sách đi chợ theo gram

### Task 1 — ĐÃ XONG (2026-11-04, theo lệnh duyệt toàn bộ lộ trình)
- `App\Week\ShoppingListBuilder`: cộng gộp DishIngredient theo nguyên
  liệu, gram × khẩu phần; dòng không có grams_resolved giữ nguyên
  quantity + unit_name, không tự đổi đơn vị; bữa ăn ngoài không tính;
  dòng optional gắn cờ, dòng is_discarded vẫn mua; nhóm theo category
  sẵn có của nguyên liệu, CHƯA phải gian hàng.
- API GET /api/week-plans/{plan}/shopping-list, trang /tuan-an có nút
  Danh sách đi chợ, ghi rõ chưa quy đổi gói và chưa có giá.
- Test `tests/Feature/WeekShoppingListTest.php`: gộp 300 g gà từ 2 món,
  trứng giữ 2 quả, bữa ăn ngoài không tính, ghi chú chưa quy đổi gói.

## Lõi Tuần ăn (Phần 2.1–2.6) đã xong. Các phần sau lõi xem tiếp dưới.

## Giai đoạn 3 — Dị ứng món + gian hàng (theo lệnh duyệt toàn bộ lộ trình)

### Task 1 — Dị ứng món trong tuần — ĐÃ XONG (2026-11-04)
- Nhãn dị ứng món vốn đã có ở trang món (AllergenDisplay, QD-ALLERGEN)
  nay hiện luôn trong tuần: API chi tiết tuần và ứng viên đổi bữa trả
  `allergen_display` (definite / possible "Có thể có"), trang /tuan-an
  hiện nhãn dưới tên món. Chỉ hiển thị — lọc cứng tuần vẫn qua
  excluded_ingredients (preset nhóm dị ứng ở Hồ sơ) ở builder và swap,
  không tự thêm lớp lọc mới.
- Test `tests/Feature/WeekAllergenTest.php`: món chứa lạc → chi tiết tuần
  trả nhóm dau-phong, nhãn Đậu phộng, definite.

### Task 2 — Mapping gian hàng cho đi chợ — ĐÃ XONG (2026-11-04)
- `App\Week\AisleMapper`: category → gian hàng chung (rau · trái cây,
  thịt · thủy sản, gạo · mì · bột, gia vị · dầu mỡ, khác), thứ tự gợi
  ý đi chợ. Ghi rõ trong code, API và giao diện: KHÔNG phải sơ đồ của
  một cửa hàng cụ thể, vì chưa có nguồn sơ đồ theo cửa hàng; khi có dữ
  liệu thật theo cửa hàng thì thay mapping này, không sửa logic cộng gộp.
- API đi chợ thêm `aisle_groups` + `aisle_note`, trang /tuan-an render
  theo gian hàng thay vì theo category phẳng.
- Test thêm: thịt gà vào "Quầy thịt · thủy sản".

## Giai đoạn 4 — Ngân sách mềm + hộ gia đình (theo lệnh duyệt toàn bộ lộ trình)

### Task 1 — Ngân sách mềm — ĐÃ XONG (2026-11-04)
- Migration thêm `budget_vnd` vào `week_plans`; API POST
  /api/week-plans/{plan}/budget; trang /tuan-an có ô lưu ngân sách.
- Ngân sách CHỈ là mục tiêu mềm do người dùng đặt, không phải tổng
  tiền thật, và hệ thống không kết luận dưới/vượt — vì chưa có bảng
  giá/gói đủ nguồn. Tổng tiền thật và quy đổi gói cho từng mặt hàng
  CHƯA làm được một cách trung thực, xem phần "Còn chặn bởi dữ liệu"
  ở cuối file: chỉ có 1 kênh BHX đạt chuẩn trực tiếp, các snapshot
  khác ngày không được cộng thành tổng tuần, chưa có kênh chợ, chưa
  cân mớ rau thơm, chưa có kênh siêu thị thứ hai cùng ngày.
- Test `tests/Feature/WeekBudgetTest.php`: lưu ngân sách 1.500.000 đ,
  ghi rõ chưa có tổng tiền thật, số âm bị 422.

### Task 2 — Chia sẻ tuần bằng mã chỉ-đọc — ĐÃ XONG (2026-11-04)
- Bảng `week_plan_shares`: chỉ lưu hash SHA-256 của mã (mã hiện một lần
  lúc tạo, mất thì tạo mã mới), hết hạn sau 14 ngày, thu hồi được.
- API: POST /api/week-plans/{plan}/shares (tạo mã), POST revoke,
  GET /api/shares/{code} công khai, trang /chia-se/{code}.
- Người xem mã chỉ thấy món và đi chợ, KHÔNG thấy mục tiêu, ngân sách,
  hồ sơ hay thông tin người dùng — lọc ở controller, không chỉ ở giao
  diện. Mã hết hạn/thu hồi/sai thì 404.
- Test `tests/Feature/WeekShareTest.php` 3 test: tạo + xem lọc riêng tư,
  hết hạn/thu hồi 404, người khác không tạo được mã.

## Còn chặn bởi dữ liệu (không phải bởi phê duyệt — không tự bịa để lấp)
- Tổng tiền thật của tuần và quy đổi gói chính xác cho từng mặt hàng:
  chỉ có 1 kênh BHX đạt chuẩn đọc trực tiếp; snapshot BHX rải rác
  02/11 và 24/10 không được cộng thành tổng tuần; chưa có kênh siêu thị
  thứ hai cùng ngày đọc trực tiếp, chưa có kênh chợ trực tiếp, chưa cân
  mớ rau thơm tại điểm bán, chưa có nhóm sữa/đậu phụ/đường/muối BHX
  ngày 04/11. Vì vậy ngân sách ở Task 1 chỉ là mục tiêu mềm, và đi chợ
  vẫn ở mức gram + gian hàng chung, chưa có tổng tiền hay số gói.
- Tỉ lệ điều chỉnh kcal theo goal — ĐÃ CHỐT VÀ LÀM XONG 2026-11-04 theo
  đề xuất đã được Benjamin duyệt (main chat 21:44): giảm −20% / duy trì
  0% / tăng +10%, làm tròn 50 kcal, áp trên kcal Hồ sơ đã nhập, không
  suy từ TDEE, không ghi đè kcal Hồ sơ. `WeekTargetSuggester::adjustKcalForGoal`,
  builder snapshot kcal tuần đã điều chỉnh (ví dụ Hồ sơ 2.000 + giảm →
  tuần 1.600, Hồ sơ vẫn 2.000), ghi chú Hồ sơ và danh sách tuần đều ghi
  rõ tỉ lệ. Test `WeekKcalGoalTest` 2 test pass. Đây là quy tắc Lắc Đói
  chọn theo công thức Mealbird, không phải chuẩn y tế phổ quát.
  Công thức protein theo từng mục tiêu của Mealbird vẫn chỉ là tham
  khảo, Task 1 giữ một công thức chung đã chốt.
- Mô hình hộ gia đình đã chọn hướng mã chỉ-đọc (Task 2 trên); hướng tài
  khoản dùng chung kiểu Plan to Eat không làm song song vì hai hướng
  không tương thích.

## Tiếp tục sau Giai đoạn 4 (2026-11-04, theo lệnh "tiếp tục" của Benjamin)
- Tuần ví dụ cho khách nâng lên đủ 7 ngày (xem Phần 2.4): `WeekBuilder::exampleWeek`,
  trang khách render 7 ngày, vẫn không lưu, không cá nhân hoá.
- Chi tiết tuần hiện cặp kết hợp cần lưu ý của món, gộp theo ngày, kèm
  miễn trừ — chỉ hiển thị, không lọc, không chặn, không chấm điểm tuần
  (nguyên tắc đỏ §7.5; cặp thuốc × thực phẩm không bao giờ vào món).
  Test `tests/Feature/WeekCombinationsTest.php` 1 test pass.
- Phần 2.5 Task 2–5 và Phần 2.6 đã xong trước đó, không còn là "phần tiếp
  theo" (mục cũ đã dọn để khỏi gây hiểu lầm).
- Tỉ lệ kcal theo goal đã chốt và làm xong 2026-11-04 (xem phần "Còn
  chặn" đã cập nhật): builder snapshot kcal tuần theo goal, test
  `WeekKcalGoalTest`, ghi chú Hồ sơ cập nhật. Quy đổi gói cho mặt hàng
  đóng gói sẵn CHƯA code ở đây — đề xuất nói rõ chỉ code sau khi chốt
  snapshot 04/11, mà snapshot đang xử lý ở chat riêng, nên không làm
  trùng và không code trên snapshot chưa chốt.
