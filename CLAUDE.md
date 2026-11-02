# CLAUDE.md — Lắc Đói (shake-hungry)

File hướng dẫn bắt buộc cho Claude Code và mọi AI agent làm việc trên repo này.

**AI PHẢI đọc hết file này trước khi thực hiện bất kỳ yêu cầu nào.** Nếu một yêu cầu mâu thuẫn với
quy tắc ở đây, phải nói ra chỗ mâu thuẫn trước khi làm, không âm thầm làm khác.

**Ngày tạo:** 2026-08-31
**Cập nhật lần cuối:** 2026-09-01 — soát toàn dự án; sửa hai lỗi engine/schema chưa ai bắt
(khoảng tin cậy rộng bằng 0 → engine 1.1.0; `needs_review` không có cơ chế duyệt → R10), 40 test xanh
**Bổ sung 2026-10-30:** thêm **QT-8** — phân tích kỹ yêu cầu và kiểm tra điều kiện ngoại vi trước
khi thực thi (quyết định của chủ dự án; xem Đính chính lần 6 và §3)

---

## 0. TRẠNG THÁI HIỆN TẠI — ĐỌC TRƯỚC TIÊN

> ## ⚠️ Đính chính 2026-08-31 — đoạn mô tả trạng thái cũ đã SAI
>
> Bản đầu của file này ghi *"repo này CHỈ CÓ TÀI LIỆU. Chưa có code, chưa có database, chưa có dữ
> liệu."* Câu đó **đúng lúc viết, sai ngay trong cùng ngày** — sau đó 150 nguyên liệu đã được biên
> soạn thành file dữ liệu. Đây chính là loại lỗi tài liệu mà [docs/README.md](docs/README.md) §5
> cảnh báo, và nó xuất hiện ngay ở dòng quan trọng nhất của repo.

> ## ⚠️ Đính chính lần 2, 2026-08-31 — đoạn "chưa có code" bên trên cũng đã hết hạn TRONG NGÀY
>
> Ngay sau đính chính thứ nhất, toàn bộ nền code GĐ0/GĐ1 đã được dựng. Bảng dưới là trạng thái
> mới; các cảnh báo "lệnh chưa chạy được" trước đây không còn đúng.

> ## ⚠️ Đính chính lần 3, 2026-09-01 — hai chỗ tài liệu tự tin hơn thực tế
>
> Soát lại toàn bộ bằng cách **chạy lệnh chứ không đọc tài liệu**. Phần lớn số liệu khớp; hai chỗ
> sai, và cả hai đều sai theo hướng làm dự án trông tốt hơn thực tế:
>
> 1. Bảng dưới từng ghi *"105 file mới đang `needs_review` chờ duyệt"*. Thực tế là **255/255** —
>    toàn bộ kho nguyên liệu, kể cả 150 file của ING-001/ING-002 đã rà soát tay. Lý do không phải
>    ai lười: **schema không có chỗ nào ghi ai đã duyệt**, nên hạ cờ đồng nghĩa với xoá thông tin.
>    Tệ hơn, `needs_review` mặc định là **`false`** ở cả hai bảng — ngược hẳn QT-1, và lỗ hổng sẽ
>    mở đúng lúc GĐ2 cho trang quản trị ghi thẳng vào database. Đã sửa: [ING-004](docs/05-tasks/tasks/ING-004.md).
> 2. Engine 1.0.0 trả **khoảng rộng bằng 0** cho món ánh xạ `high` — tức một con số điểm đội lốt
>    khoảng, đúng thứ **QT-5 cấm**, nằm sẵn trong engine từ CALC-001. Phép đo lần 1 đã in bằng chứng
>    ngay trên bảng chỉ số (*"% tham chiếu nằm trong khoảng: **1,0%**"*) nhưng không ai đọc, vì dòng
>    đầu tiên đã xanh. Đã sửa và hiệu chuẩn: [CALC-003](docs/05-tasks/tasks/CALC-003.md).
>
> Bài học chung của cả hai: **một cơ chế an toàn không có cách kiểm chứng thì không phải cơ chế an
> toàn.** Cờ `needs_review` không ai kiểm được đã hạ đúng chưa; khoảng min–max không ai kiểm được
> có thật sự chứa giá trị đúng không.

> ## ⚠️ Đính chính lần 4, 2026-10-30 — nền tảng CSDL là MySQL 8
>
> Theo quyết định của **chủ dự án**, toàn hệ thống chạy trên **MySQL 8** và dùng
> **tài khoản database mặc định** (root, không mật khẩu — kiểu Laragon), không tạo
> user database riêng. Trigger R6/R8 chạy theo cơ chế MySQL (không có deferred
> trigger — giới hạn đã biết ghi ở migration 100008 và ARCHITECTURE §4), V7 của
> `lacdoi:validate-data` tính độ giống trigram ở tầng PHP. Kiểm chứng trên
> MySQL 8.0: seed đủ **255/61/7.793**, validate **0 lỗi 🔴**, **40/40 test đạt**.
> Chi tiết: [SETUP-004](docs/05-tasks/tasks/SETUP-004.md).

> ## ⚠️ Đính chính lần 5, 2026-10-30 — trang quản trị dùng Ant Design (antd)
>
> Theo quyết định của **chủ dự án**, trang quản trị (GĐ2) dùng **Ant Design (antd)**
> với React — giao diện tại `/admin`, Laravel làm backend (API nội bộ + xác thực
> đăng nhập). Toàn bộ tài liệu đã thay theo quyết định này trong cùng commit;
> task nền là [ADM-001](docs/05-tasks/tasks/ADM-001.md) (đã viết lại), thiết kế
> tổng thể ở [ARCHITECTURE §5.6](docs/02-architecture/ARCHITECTURE.md).
>
> **Bổ sung cùng ngày (sau nghiên cứu so sánh chuyên sâu):** chốt bắt đầu ở
> **antd 6 + React 19**. Đã đối chiếu với Ant Design Vue và loại nó vì lý do
> bảo trì: bản Vue đóng băng ở 4.2.6 từ 11/2024 (không một bản phát hành nào
> trong 2025–2026), thiếu Table virtual scrolling và ProComponents, trong khi
> antd gốc phát hành gần như hằng tuần (6.6.8 tại thời điểm chốt). "Xung đột
> Laravel" từng được nhắc tới là chuyện Ant Design Vue 1.x chỉ chạy Vue 2 thời
> scaffolding Laravel cũ — không áp dụng cho dự án này.

> ## ⚠️ Đính chính lần 6, 2026-10-30 — thêm quy tắc QT-8
>
> Theo quyết định của **chủ dự án**, hệ thống có thêm quy tắc tuyệt đối **QT-8**
> (xem §3): trước khi thực thi bất kỳ yêu cầu nào phải **phân tích kỹ yêu cầu** và
> **kiểm tra các điều kiện ngoại vi cùng mức phù hợp với dự án**; việc lớn, khó đảo
> ngược hoặc đổi hướng phải trình phân tích và chờ chủ dự án chốt trước khi làm.
> Quy tắc này hệ thống hoá quy định sẵn có ở đầu file này ("mâu thuẫn thì nói ra
> trước khi làm") và bước kiểm tra phụ thuộc ở §6 — không thay đổi QT-1…QT-7.
> Đồng bộ trong cùng commit: §3, §6 của file này, [AGENTS.md](AGENTS.md) và
> [TASK-TEMPLATE](docs/05-tasks/TASK-TEMPLATE.md).

> ## ⚠️ Đính chính lần 7, 2026-10-31 — mở lại nguồn thứ cấp ở chế độ không ưu tiên + cờ "chưa kiểm định"
>
> Theo quyết định của **chủ dự án**, các nguồn từng bị hạn chế (Bảng thành phần thực
> phẩm Việt Nam, các bảng FCT nước ngoài như MEXT/IFCT/Thai FCT/FNRI, văn liệu khoa
> học) **được dùng lại làm tầng 6 — nguồn thứ cấp, KHÔNG ưu tiên** trong thang nguồn
> của [DATA-PIPELINE §7.2](docs/04-data-sources/DATA-PIPELINE.md): chỉ tra từng nguyên
> liệu khi tầng 1–5 không có dữ liệu, mỗi số có trích dẫn bảng gốc. **Lệnh cấm nhập/
> chép hàng loạt dataset và bảng số (QT-2, LICENSE-COMPLIANCE §2) vẫn nguyên hiệu
> lực** — thay đổi này chỉ mở đường tra cứu từng mục có ghi xuất xứ. Điều kiện bắt
> buộc đi kèm: dữ liệu tầng 6 bị đánh dấu **chưa kiểm định** trong hệ thống
> (`App\Nutrition\SourceTier`, cột `dish_nutrition.unverified_ingredients`) và
> **hiển thị cảnh báo trên giao diện người dùng** ở mọi món dùng chúng. QT-1 không
> đổi: vẫn cấm số từ trí nhớ và cấm gán số của loài/trạng thái khác cho nguyên liệu
> chính mà không ghi rõ.

> ## ⚠️ Đính chính lần 8, 2026-10-31 — giao diện người dùng v2: web app theo hướng Apple HIG
>
> Chủ dự án đã duyệt bản mẫu v2 và chốt triển khai (UI-004…UI-010): trang người dùng
> chuyển từ các trang Blade rời (Tailwind cam mặc định, không layout chung) thành
> **một vỏ web app**: layout `layouts/app` với sidebar theo nhóm tính năng trên
> desktop và tab bar 5 mục trên mobile; **hệ thiết kế v2 trong `app.css`** (token
> Apple: nền `#F5F5F7`, hành động `#0071E3`, màu theo ngữ nghĩa; component tiền tố
> `ld-`). Font phía người dùng là **Inter (subset tiếng Việt) + chồng font hệ
> thống Apple**, bỏ Instrument Sans (hỗ trợ tiếng Việt kém) và bỏ bunny fonts
> khỏi `vite.config.js`. Trang hồ sơ là trung tâm quản trị thông tin của người
> dùng. Tài liệu cũ ghi frontend là "Blade + Livewire 3" (ARCHITECTURE §2) là
> **sai** — đã sửa ở UI-010: dự án dùng Blade + JavaScript thuần, chưa từng
> dùng Livewire.

> ## ⚠️ Đính chính lần 9, 2026-10-31 — tuỳ chọn lắc lưu ở hồ sơ (UI-011) + thẻ đồng nhất (UI-012)
>
> Theo yêu cầu của chủ dự án: khối Lắc đưa toàn bộ lựa chọn lên trên nút Lắc, hiển thị
> giờ hiện tại và tự gợi ý bữa theo giờ. `user_profiles` có thêm `preferred_region_id`
> và `target_kcal_per_meal` (migration `2026_10_31_100005`, DATA-MODEL đã cập nhật) —
> trang chủ điền sẵn tuỳ chọn từ hồ sơ và lưu ngược khi người dùng đổi tại khối Lắc;
> các trường này là đầu vào mềm của engine, **không** thay đổi lọc cứng dị ứng (QT-5/
> engine 3 tầng giữ nguyên). Component món ăn/nguyên liệu/hoa quả chuyển sang hệ thẻ
> đồng nhất `ld-dcard` với ô ảnh 4:3 (đang dùng ảnh mẫu SVG trong `public/images/`,
> thay 1-1 khi có ảnh thật theo quyết định Q4/Q5).

> ## ⚠️ Đính chính lần 10, 2026-10-31 — yêu thích + thống kê (UI-014) và tinh chỉnh tuỳ chọn (UI-013)
>
> Hai bảng mới `favorites` và `fruit_logs` (migration `2026_10_31_100006`, DATA-MODEL đã
> cập nhật) phục vụ tính năng yêu thích món/quả và thống kê số lượng đã ăn theo yêu cầu
> của chủ dự án; API nằm ở `EngagementController`. Khối Lắc: Ăn ngoài và Tự nấu loại trừ
> lẫn nhau; kcal mục tiêu khi chưa cấu hình trong hồ sơ thì hiển thị **gợi ý suy từ TDEE**
> (chỉ là giá trị hiển thị/đầu vào mềm — không ghi vào hồ sơ nếu người dùng không đặt).

> ## ⚠️ Đính chính lần 11, 2026-10-31 — trang chuyên dụng Khám phá/Nhật ký (UI-015) + lưu ý tên món đa locale
>
> Từ UI-015, điều hướng chính trỏ tới trang chuyên dụng `/kham-pha` và `/nhat-ky`; mục
> Khám phá ở trang chủ và Nhật ký trong hồ sơ chỉ còn vai trò **dùng nhanh**. Lưu ý dữ
> liệu quan trọng: `dish_names` có tên chính (is_primary) **theo từng locale** (vi đủ
> 500, en 453) — mọi JOIN/sắp xếp theo tên phải khoá `locale='vi'`, và API danh mục luôn
> ưu tiên tên vi làm `names[0]`).

> ## ⚠️ Đính chính lần 12, 2026-10-31 — trang chính sách là nguồn sự thật pháp lý phía người dùng (UI-016)
>
> Từ UI-016, `/ho-so` không còn ép đăng nhập: khách thấy bản giới thiệu; tính năng nào
> thay đổi cách xử lý dữ liệu cá nhân (thêm trường hồ sơ, đổi mục đích dùng dữ liệu)
> phải cập nhật đồng thời trang `/chinh-sach-bao-mat`, và mọi khẳng định dinh dưỡng mới
> trên giao diện phải khớp trang `/mien-tru-y-te` (số = khoảng ước tính; lọc dị ứng
> không phải cam kết an toàn tuyệt đối). Giấy phép công bố ở `/nguon-du-lieu` theo
> LEGAL-002 (MIT / CC BY 4.0 / ODbL 1.0 cho dòng OFF).

> ## ⚠️ Đính chính lần 13, 2026-10-31 — nguyên liệu có mặt tiền người dùng (UI-017)
>
> Từ UI-017, nguyên liệu không còn chỉ là dữ liệu nền: người dùng tra cứu trực tiếp tại
> `/nguyen-lieu` và `/nguyen-lieu/{slug}` (API `GET /api/ingredients/{slug}`). Mọi thay
> đổi về cấu trúc `nutrition_snapshot` phải kiểm tra cả hai trang này. Hai cạm bẫy kỹ
> thuật đã gặp và ghi lại trong UI-017: sắp xếp phụ trên JSON phải `reorder()` để bỏ
> thứ tự mặc định, và kiểm "có số" trên JSON phải dùng `JSON_TYPE` (null trong JSON
> không phải SQL NULL).

> ## ⚠️ Đính chính lần 14, 2026-11-01 — kênh phản hồi chính thức là ticket (UI-018)
>
> Từ UI-018, góp ý/báo lỗi/hỗ trợ của người dùng đi qua **ticket** (`feedback_tickets`,
> trạng thái khởi tạo `da-ghi-nhan` = đã ghi nhận, chờ duyệt; quản trị xử lý tại trang
> admin "Góp ý · Phản hồi"). Tính năng mới liên quan đến người dùng nên cân nhắc gắn
> nút "Gửi phản hồi" (thuộc tính `data-feedback` + ngữ cảnh sản phẩm) thay vì tạo kênh
> liên hệ riêng. Đóng góp món (contributions) và phản hồi (feedback) là hai hàng đợi
> khác nhau: đóng góp là dữ liệu đề xuất, phản hồi là ticket hỗ trợ/góp ý.

> ## ⚠️ Đính chính lần 15, 2026-11-01 — lọc dưỡng chất và vị trí nhãn dị ứng (UI-019)
>
> (1) `dish_nutrition` **không có cột natri** — engine không tính natri theo món;
> natri chỉ tồn tại trong `nutrition_snapshot` của nguyên liệu. Bộ lọc dưỡng chất
> phía món chỉ được dùng các cột có thật (protein/fat/carb/fiber/kcal); muốn lọc
> natri theo món phải mở rộng engine trước, không được bịa cột. (2) Nhãn dị ứng ở
> trang chi tiết hiển thị trong **mục "Thông tin dị ứng" riêng** (không trộn vào
> hàng nhãn đầu trang hay nhóm Phân loại) và luôn kèm lưu ý không-phải-cam-kết-an-toàn.

> ## ⚠️ Đính chính lần 14, 2026-10-31 — rà soát toàn bộ (census) sau chương trình Mức 2 (AUDIT-002)
>
> Đợt rà soát toàn bộ đã xếp hàng từ khi chương trình Mức 2 còn chạy nay đã làm xong:
> census 100% (654 món, 434 nguyên liệu, 82 hồ sơ quả, 1.087 ảnh, 2.185 URL nguồn —
> 407 URL không-200 được mở lại bằng trình duyệt ở lượt 2 độc lập). Kết quả nền:
> cấu trúc toàn kho sạch, media khớp 3 chiều tuyệt đối, hồ sơ giữ Mức 1 của 179 món
> còn đúng, validate 0 🔴, recompute 475/475, Pest 127/127. Ba nhóm vấn đề thật đã
> gom thành **danh sách phân xử PX-01…PX-35 chờ chủ dự án quyết** (không tự sửa):
> (A) link rot làm 16 món Mức 2 rớt cổng nguồn — nặng nhất `bau-xao-tep` còn 0/3
> nguồn mở lại được; (B) nguồn sai loài/họ kỹ thuật — trừ `ca-ro-dong-chien-xu`
> (PX-17, không công khai + định lượng đi theo nguồn sai loài), các ca còn lại đều
> đã công khai trong ghi chú và chờ **một quyết định quy ước chung** về việc nguồn
> họ có tính vào cổng 3 nguồn hay không; (C) cặp `canh-hen`/`canh-hen-nau-chua`
> trùng tên chính + link rot FDC (260 URL — số đã kiểm chứng độc lập trên bản trích
> xuất SR Legacy). Ngoại lệ sửa cơ học duy nhất của đợt: 6 file nguyên liệu sửa
> `oil_absorption_pct` theo nhóm CALC-004 đã hiệu chuẩn (truy vết đầy đủ trong báo
> cáo). Chi tiết: [AUDIT-002](docs/05-tasks/tasks/AUDIT-002.md) và báo cáo rà soát
> toàn bộ 2026-10-31 trong thư mục tài liệu bàn giao của chủ dự án.

> ## ⚠️ Đính chính lần 15, 2026-11-01 — pha khắc phục sau AUDIT-002 (AUDIT-FIX)
>
> Bốn nhóm khắc phục đã làm xong (chi tiết: [AUDIT-FIX](docs/05-tasks/tasks/AUDIT-FIX.md)):
> nhóm A — 13/16 món khôi phục cổng 3 nguồn bằng nguồn thay thế đã mở kiểm chứng,
> **3 món hạ Mức 2 → 1 có hồ sơ** (`canh-khoai-so`, `dau-phu-kho-chao`,
> `canh-rau-lang-tom`) → **tổng Mức 2 còn 472/654**; PX-17 — đã loại nguồn sai
> loài cá rô phi khỏi `ca-ro-dong-chien-xu` (công khai trong hồ sơ) và sửa tỉ lệ
> bột theo nguồn đúng loài; PX-34 — mở lại toàn bộ URL chưa kết luận của 23 món:
> 9 kết luận sống, 0 chết, không món nào rớt cổng; PX-28 — 286 URL FoodData
> Central (260 ID) đã chuyển từ khuôn `food-details/<id>/nutrients` (404 sau khi
> FDC đổi site) sang **endpoint API chính thức `api.nal.usda.gov/fdc/v1/food/<id>`**
> (kiểm chứng 11/11 ID trước khi sửa hàng loạt, mở lại 260/260 sau sửa; số dinh
> dưỡng không đổi). Kiểm chứng: validate 0 🔴, recompute 472/472 trên DB kiểm
> chứng riêng, đối chiếu calo cách ly chỉ 1 món đổi (PX-17), Pest 127/127, rà
> độc lập mở lại 100% nguồn thay thế mới (19/22 đạt, 3 ghi truy vết). Các PX còn
> lại không đổi: **PX-25 (quy ước họ nguồn) và PX-26 (cặp canh hến) vẫn chờ chủ
> dự án chốt.**

> ## ⚠️ Đính chính lần 16, 2026-11-01 — chương trình Mức 3 mở, nền kỹ thuật đã dựng (TCM-000)
>
> Chủ dự án đã **duyệt kế hoạch Mức 3** (tính vị + ghi chú văn hoá cho toàn bộ món,
> nguyên liệu, quả) và **uỷ quyền tự chủ triển khai đến khi hoàn thiện** — không cần
> dừng hỏi ở từng đề xuất trong phạm vi kế hoạch. Các chốt: (1) nguồn tính vị chính =
> **Dược điển Trung Quốc + CMRO Hồng Kông**, bổ sung nguồn các nước khác; (2) cổng
> Mức 3 = **coverage ≥60% + ghi chú văn hoá có nguồn**; (3) hiển thị lớp tính vị
> **MẶC ĐỊNH BẬT** với nhãn khối đúng chữ **"Tính vị:"** (đảo ngược thiết kế opt-in
> của tài liệu gốc — ngưỡng trung thực theo coverage giữ nguyên); (4) **đường nâng
> bù**: Mức 1 không đạt cổng Mức 2 nhưng đủ thông tin Mức 3 ⇒ **"Mức 2 (có note)"**;
> (5) nguyên liệu hỗn hợp khai báo `components` + hiển thị liên kết thành phần;
> (6) nguồn tính vị uy tín dù điều khoản hạn chế vẫn **lưu vào thư viện** kèm cờ
> "không được dùng" (lưu trữ/lưu hành nội bộ; chính sách hiển thị công khai chốt
> trước go-live).
>
> **TCM-000 đã dựng xong nền** (chi tiết: [TCM-000](docs/05-tasks/tasks/TCM-000.md)):
> định dạng `tcm_traits`/`components` trong file nguyên liệu + `cultural_note` (món,
> quả) + `level_basis`/`level_note` ở file món, qua seed/export hai chiều (file hiện
> tại không đổi một byte); migration 100010 (cột mới + bảng `ingredient_components`
> + R8 miễn trừ đúng nhóm nâng bù); engine `TcmAggregator` + lệnh
> `lacdoi:recompute-tcm`; cổng **V20** trong validator (file + DB); khối "Tính vị:"
> ở trang món/nguyên liệu/quả qua `TcmDisplay`; admin gắn thẻ "Mức 2 (có note)".
> **Chưa gieo nhãn dữ liệu thật nào** — TCM-001a bắt đầu gieo từ thang nguồn đã chốt.
> Kiểm chứng: validate 0 🔴 trên DB kiểm chứng riêng, Pest 149/149 (15 test mới).

> ## ⚠️ Đính chính lần 17, 2026-11-01 — TCM-001a: đã gieo nhãn tầng 1–2 + hai điều chỉnh nền
>
> **Hai điều chỉnh nền** (xử lý hai điểm mở của TCM-000 theo uỷ quyền): (1) dòng nhãn
> có thêm **`element_basis`** (`flavor` mặc định | `nature` | `direct`, migration
> 100011) — `dominant_element` của món chỉ bỏ phiếu từ dòng basis=flavor; (2) **trần
> cứng `confidence` tổng hợp món = `medium`** trong `TcmAggregator` (hệ số chế biến
> là quy ước dự án không nguồn). **Đã gieo 25 nguyên liệu / 35 dòng nhãn**: 31 dòng
> tầng 1 (Dược điển TQ + danh mục 药食同源 106 chất) và 4 dòng tầng 2 CMRO 《中藥趣談》
> (đều mang cờ `usage_restricted` theo chính sách §5b của LICENSE-COMPLIANCE, vì ấn
> phẩm ghi © CMRO cấm sao chép khi chưa có văn bản đồng ý). Kỷ luật đợt: đối chiếu
> định danh từng mục (loài/bộ phận/dạng tươi–khô), KHÔNG gieo khi lệch loài hoặc lệch
> bộ phận — danh sách chờ tầng 3+ (đậu xanh, riềng, khoai mỡ, các mục chỉ có chuyên
> khảo cho hạt/vỏ…) nằm ở [TCM-001](docs/05-tasks/tasks/TCM-001.md). Kiểm chứng:
> **rà census mở lại 100% source_ref** (17 URL; 1 lệch dịch máy + 1 biến thể thảo
> dược đã ghi minh bạch trong source_ref, 1 URL chết đã thay), validate **0 🔴**,
> seed + recompute trên DB kiểm chứng riêng: **7/654 món đạt coverage ≥30%, 4 món
> ≥60%** (nhóm chè hạt sen/long nhãn), Pest **153/153**.

> ## ⚠️ Đính chính lần 18, 2026-11-01 — vòng seed→export phía món KHÔNG phải điểm cố định (đã sửa, EXPORT-STABILITY)
>
> TCM-001a phát hiện: export trên DB vừa seed từ `data/` viết lại ~648 file món — phía
> nguyên liệu/quả là điểm cố định từng byte, phía món thì chưa từng ai kiểm (test ổn định
> cũ chỉ phủ nguyên liệu). Chẩn đoán census toàn bộ 1.176 file JSON:
> [EXPORT-STABILITY](docs/05-tasks/tasks/EXPORT-STABILITY.md). Ba nguyên nhân ở exporter
> (`LacdoiExportData::dishPayload`): (1) tự sắp lại thứ tự dòng theo nội dung (tên en lên
> trước vi, nguyên liệu theo slug, bữa theo tên slot, nguồn theo URL) trong khi chuẩn file
> là **thứ tự biên tập** mà seeder đã giữ trong khoá `id`; (2) cột DECIMAL + cast float in
> số nguyên thành `480.0` trong khi file ghi `480`; (3) rớt `is_optional: false` tường minh
> (5.306 dòng) + đặt `note` sai vị trí. Cộng hai lỗi phía file: `dau-phu-kho-gung` viết tay
> lệch khuôn (thừa `is_variant` không có schema, thiếu 3 khoá chuẩn) và `dishes/_index.json`
> bản commit **lỗi thời từ thời kho 61 món**. Đã sửa exporter (sortBy `id`, `is_optional`
> luôn ghi, `numValue()` chuẩn hoá số) + chuẩn hoá cơ học 23 file qua chính vòng export
> (nội dung ngữ nghĩa không đổi) → **seed từ `data/` → export = 0/1.176 file lệch**;
> `ExportStabilityTest` có thêm test điểm cố định **toàn cục** làm cổng bắt buộc.
> **Quy tắc vận hành mới: không chạy export toàn cục khi chưa qua cổng điểm cố định này;
> script sửa dữ liệu hàng loạt phải ghi file đúng khuôn exporter** (thứ tự khoá dòng nguồn
> `url, accessed, note`, `is_optional` tường minh, số nguyên không `.0`) — lệch khuôn sẽ
> thành diff bất ngờ ở lần export sau. Kiểm chứng: validate 0 🔴 trên DB kiểm chứng riêng,
> Pest 154/154.

> ## ⚠️ Đính chính lần 19, 2026-11-01 — TCM-001b: thí điểm nhãn tầng 3–4 cho 55 nguyên liệu khối lượng lớn nhất
>
> Danh sách đích tự tính bằng `resolveGrams` (55 NL chưa có nhãn ≈ 65,0% tổng khối lượng
> toàn kho; công bố trong [TCM-001](docs/05-tasks/tasks/TCM-001.md) trước khi gieo).
> **Đã gieo 40/55 NL — 56 dòng mới (tổng kho 65 NL / 91 dòng); 15 NL giữ chờ có lý do**
> (nhóm bột/sợi đã chế biến không suy nhãn từ hạt gốc; dầu ăn/nước mắm không định danh
> hoặc không có mục cổ điển; lệch loài hanh-tim/ca-troi; không mục đạt chuẩn muc-ong/
> ca-nuc/bap-chuoi/mang-tuoi/dau-phu-chien). Nguồn: sổ tay người chăm sóc bệnh nhân
> ung thư Hồng Kông (TS. Lâm Đạo Nghi — 32 dòng/27 NL, nguồn tầng 3 tốt nhất đợt này),
> A+醫學百科 biên theo 《中藥大辭典》/《全國中草藥彙編》 (14 dòng/8 NL — **bắt buộc đọc
> toàn trang: 4/8 mục có hai giá trị lệch một bậc trong cùng trang**, gieo cả hai),
> 《本草綱目》 toàn văn (trung-ga), y văn Việt Nam có tên sách (Đỗ Tất Lợi; Đỗ Huy Bích
> và cộng sự — 9 dòng/6 NL, confidence low–medium). **Liu et al. 2012 không dùng được:
> bảng 284 thực phẩm nằm sau paywall Wiley.** Ca kinh điển đậu xanh: HK ghi 寒 (han),
> Đỗ Tất Lợi ghi mát (luong) — **giữ cả hai dòng, hiển thị mâu thuẫn, không nắn**.
> Kiểm chứng: census mở lại 100% source_ref mới (17 URL, 56/56 khớp nguyên văn — dòng
> luong của nam-rom bổ sung ngay trong census), validate **0 🔴**, ExportStabilityTest
> đạt, Pest **154/154**. Coverage sau gieo (DB kiểm chứng riêng): **227/630 món ≥60%**
> (trước: 4), 443 món ≥30%, 130 món có cờ mâu thuẫn; **57/182 món Mức 1 đạt coverage
> ≥60%** — đủ tiêu chí độ phủ của cổng Mức 3 cho đường nâng bù "Mức 2 (có note)"
> (**chỉ thống kê đợt này, chưa nâng thật**). Nhận xét nhịp TCM-001c: xem TCM-001 §nhận
> xét thí điểm (nhóm bột/sợi cần quyết định chính sách riêng).
>
> ## ⚠️ Đính chính lần 20, 2026-11-01 — TCM-001c đợt 1: chính sách P-DERIVE + gieo nhóm rau/củ/quả/nấm còn lại
>
> **Phần A — P-DERIVE ĐÃ HIỆN THỰC** (quyết định chính sách của TCM-001b §nhận xét, nay có
> cổng kỹ thuật): dòng nhãn suy diễn mang `derived_from` (migration 100012) — chỉ cho dạng
> chế biến CƠ HỌC ĐƠN THÀNH PHẦN của nguyên liệu gốc đã có nhãn thật; dòng suy diễn gương
> đúng một dòng thật của gốc, confidence trần **low** (CHECK ở DB + V20), cấm chuỗi suy
> diễn, cấm cho nguyên liệu có components, kế thừa `usage_restricted`; trang nguyên liệu
> hiển thị dòng phụ "Nhãn suy diễn từ …". Đã áp **12 dòng / 10 nguyên liệu** (nhóm bột/sợi
> từ gạo + `mang-kho`). `bot-nang` vẫn chờ: gốc `khoai-mi` chưa có nhãn thật (mục 木薯 của
> A+ chỉ cho lá). Chi tiết + danh sách phân xử không-suy-diễn: [TCM-001](docs/05-tasks/tasks/TCM-001.md).
> **Phần B — gieo 74 nguyên liệu / 164 dòng nguồn thật** (sổ tay HK + A+ đọc toàn trang +
> 1 mục thuvienso): tổng kho **149 nguyên liệu / 267 dòng** (255 thật + 12 suy diễn). Ca
> kiểm đậu nành: `dau-nanh-hat` = **bình ở cả hai nguồn** — khớp kỳ vọng cổ điển, ghi
> nguyên văn nguồn. Mâu thuẫn trong cùng trang (`du-du-chin` 4 giá trị) giữ đủ, không ép
> chọn. ~79 mục giữ CHỜ có hồ sơ lý do (lệch loài/bộ phận/dạng, không nguồn đạt chuẩn).
> ⚠️ Phát hiện ngoài phạm vi: file `nam-dui-ga` trùng tên khoa học *Pleurotus ostreatus*
> với `nam-bao-ngu` — chờ rà ánh xạ. Kiểm chứng: census **214/214 trích khớp nguyên văn**,
> validate **0 🔴**, Pest **157/157**, ExportStabilityTest đạt. Coverage (DB kiểm chứng):
> **401 món ≥60%** (trước: 227, +174); món Mức 1 đạt tiêu chí phủ cho đường nâng bù:
> **106/182** (trước: 57) — chỉ thống kê, đợt nâng bù riêng làm sau.
>
> ## ⚠️ Đính chính lần 21, 2026-11-01 — TCM-001c đợt 2: gieo nhóm đạm & thuỷ sản + components + kết luận nam-dui-ga
>
> **Phần A — gieo +43 nguyên liệu / +75 dòng** (sổ tay HK 23 NL/33 dòng; A+ đọc toàn trang
> 18 NL/38 dòng; P-DERIVE 2 NL/4 dòng) → tổng kho **192 nguyên liệu / 342 dòng** (326 thật +
> 16 suy diễn). P-DERIVE mới: `sua-dau-nanh` ← `dau-nanh-hat` (NHẬN — nước là môi trường
> chế biến, không chất đông tụ) và `muc-kho` ← `muc-ong`; LOẠI suy diễn cho
> `dau-phu-chien`/`bong-bi` (chiên ngập dầu = thêm chất thứ hai). **Components thay nhãn
> cho 14 nguyên liệu chế biến** (25 liên kết; quy ước: chỉ thành phần định danh/chính).
> Kỷ luật census đợt này bác/sửa 8+ đề xuất của agent nghiên cứu con khi mở lại trang —
> nặng nhất: `thit-de` ← 山羊肉 bị bác vì trang ghi loài gốc *Naemorhedus goral* ≠ dê nhà
> (thay bằng mục 羊肉 chung của sổ tay HK); trang 404/lỗi mạng → chuyển chờ, không gieo.
> **Phần B — kết luận `nam-dui-ga`:** định danh thật là ***Pleurotus eryngii*** (king
> oyster); trường tên khoa học ghi *P. ostreatus* là loài của bản ghi mượn SR 168580
> (proxy cùng chi). Đã sửa metadata + đính chính trong `mapping_note`; **con số không
> đổi** (vẫn proxy theo G6 — thay ánh xạ khi có FCT đúng loài ở tầng cao hơn). Nhãn của
> `nam-bao-ngu` đúng loài của nó, giữ nguyên. Kiểm chứng: validate **0 🔴**, Pest
> **157/157** (gồm 1 sửa tiền đề test TCM-000: host phải là nguyên liệu chưa có nhãn —
> assertion không đổi), ExportStabilityTest đạt. Coverage (DB kiểm chứng): **470 món
> ≥60%** (trước: 401, +69); món Mức 1 đạt tiêu chí phủ: **118/182** (trước: 106) — chỉ
> thống kê, đợt nâng bù riêng làm sau. Chi tiết: [TCM-001](docs/05-tasks/tasks/TCM-001.md).
>
> ## ⚠️ Đính chính lần 22, 2026-11-01 — bổ sung TCM-001c đợt 2 (báo cáo agent con đến muộn, đã tự kiểm chứng lại)
>
> Báo cáo của agent nghiên cứu con (nhóm thịt/nội tạng/trứng) đến sau commit đợt 2, kèm
> URL nguyên văn của 3 trang mà phiên gieo đoán tựa không trúng nên đã ghi chờ. Tự mở lại
> cả ba: báo cáo chính xác — **bổ sung `bi-lon` 2 dòng** (A+ 豬膚： lương/cam + dòng lệch
> hàn), **`trung-vit` 2 dòng** (A+ 鴨卵： lương/cam hai từ điển đồng thuận + dòng lệch
> hàm), **`thit-de` +2 dòng** (A+ 羊肉 chung — khác trang 山羊肉 đã bác). Tổng sau bổ
> sung: **194 nguyên liệu / 348 dòng** (332 thật + 16 suy diễn); `trung-vit-muoi` và
> `bong-bi` giữ nguyên phân xử chờ/loại-suy-diễn (lý do tại TCM-001 §Bổ sung đợt 2).
> Kiểm chứng lại: validate **0 🔴**, Pest **157/157**. Coverage (DB kiểm chứng):
> **473 món ≥60%**; món Mức 1 đạt tiêu chí phủ: **120/182**.
>
> ## ⚠️ Đính chính lần 23, 2026-11-01 — bổ sung đợt 2 lần 2: gieo `tep` từ Tuệ Tĩnh + đối chiếu hai báo cáo con còn lại
>
> Agent con nhóm giáp xác/thân mềm tìm được nguồn cho `tep` mà cả đợt không ai có:
> Tuệ Tĩnh toàn tập, mục "Hà ngư" tự định nghĩa "Mễ là tép" — phiên gieo tự mở bản số
> hoá, kiểm chứng nguyên văn («Tôm tép đều có vị ngọt, tính ấm hơi độc») → **gieo `tep`
> (on/cam, medium, ghi rõ phạm vi tôm+tép chung + ghi chú hơi độc)**. Các dòng VN cho
> `cua-dong` vẫn chờ vì không mở kiểm chứng first-hand được bài trích (lỗi fetch, không
> phải bằng chứng nguồn sai). Đề xuất ngoài thang (Baidu cho `ngheu`/`mi-can`) bị loại;
> bốn mục agent con "không tìm thấy" nhưng phiên gieo đã tự mở ở census (`ca-doi`,
> `hau`, `bach-tuoc`, `muc-ong`) giữ nguyên. Agent con cũng kiểm chứng độc lập: nhãn
> `nam-bao-ngu` đúng, định danh `nam-dui-ga` = *P. eryngii* đúng. **Tổng cuối đợt 2:
> 195 nguyên liệu / 349 dòng** (333 thật + 16 suy diễn); validate **0 🔴**, Pest
> **157/157**; coverage (DB kiểm chứng): **477 món ≥60%**, món Mức 1 đạt tiêu chí phủ:
> **122/182**. Chi tiết: [TCM-001](docs/05-tasks/tasks/TCM-001.md) §Bổ sung đợt 2 lần 2.
>
> ## ⚠️ Đính chính lần 24, 2026-11-01 — TCM-001c đợt 3 (đợt vét cuối): ĐÓNG CHƯƠNG TRÌNH GIEO NHÃN TCM-001
>
> Đợt vét cuối đi qua **toàn bộ** nguyên liệu chưa nhãn, gieo phần còn gieo được theo
> đúng kỷ luật census (phiên gieo tự mở kiểm chứng 100% source_ref mới; đề xuất của agent
> con không tự kiểm chứng được thì loại/xếp chờ — đợt này loại hẳn trích dẫn không có
> thật trên trang cho `dau-me`/`bot-mi`, và claim tính ôn cho `muoi`). Kết quả:
> **+24 nguyên liệu nhãn thật / +52 dòng** (thảo mộc: `la-he`, `rau-mui`, `hung-que`,
> `la-e`, `hung-lui`, `sa`, `la-lot`, `rieng`, `ot-tuoi`, `rau-can-ta`, `rau-den`,
> `khoai-mo`, `rau-lang`, `rau-ram`; chế biến: `muoi`, `duong-phen`, `dam-gao`,
> `giam-bong`, `ruou-trang`, `tra-xanh`, `mo-lon-nuoc`, `rau-cau-kho`, `rong-bien`,
> `dua-le`), **thử lại `cua-dong` THÀNH CÔNG** (+2 dòng Tuệ Tĩnh hàn/hàm + Lãn Ông
> hàn/cam — hai kênh đều mở được lần này, ghi chú "lỗi fetch" của đợt 2 hết hiệu lực),
> **+4 P-DERIVE** (`nuoc-mia`, `cui-dua-nao`, `ot-bot`, `bot-ngo`), **+13 nguyên liệu
> components / +14 liên kết** (gồm `trung-vit-muoi` [trung-vit] — chỉ components, nhãn
> vẫn giữ phân xử chờ của đợt 2). `khoai-mi` (củ) vẫn chưa có nguồn → cụm bột sắn
> (`bot-nang`, `tran-chau`, `bot-khoai`, `bot-bang`) không mở khoá. **Tổng cuối: 223/434
> nguyên liệu có nhãn — 405 dòng (385 thật + 20 suy diễn); components 27 NL / 39 liên
> kết; 184 nguyên liệu chưa nhãn đều có bản đồ lý do từng mục tại TCM-001 §Đợt 3: (i) 58
> không tồn tại phân loại truyền thống · (ii) 87 hàng đợi nguồn · (iii) 10 lệch định danh
> chưa phân xử · (iv) 29 trần chính sách đã phân xử.** Rà nhất quán toàn lớp: 0 vấn đề.
> Kiểm chứng: validate **0 🔴 / 11 🟡** (lớp đã biết), Pest **157/157**,
> ExportStabilityTest đạt; coverage (DB kiểm chứng riêng): **498 món ≥60%** · 99 món
> 30–<60% · 51 món <30% (648 món có tổng hợp); món Mức 1 đạt tiêu chí phủ **133/182**
> (chỉ thống kê — đường nâng bù là vòng riêng). **TCM-001 ĐÓNG.** Chi tiết:
> [TCM-001](docs/05-tasks/tasks/TCM-001.md) §TCM-001c đợt 3.
>
> ## ⚠️ Đính chính lần 25, 2026-11-01 — TCM-001c đợt 3 BỔ SUNG (sau báo cáo nghiên cứu phụ)
>
> Ba báo cáo nghiên cứu phụ về sau thời điểm chốt đợt 3; phiên gieo tự mở lại kiểm chứng
> nguyên văn từng phát hiện mới (tiền lệ bổ sung đợt 2) và gieo thêm **+4 nguyên liệu /
> +8 dòng thật**: `cha-la` (ôn/cam — A+ mục 無漏子）, `dau-trang` (bình/cam + bình/đạm —
> A+ trích 《中藥大辭典》 mục 白飯豆， giải quyết khỏi nhóm lệch định danh), `so-diep`
> (4 dòng **low** — giá trị của DẠNG KHÔ 乾貝： bình theo thân bài/HK 瑤柱 + ôn theo
> infobox, áp sang dạng tươi), `ca-phe-den` (bình/kho **low** — infobox-only ở A+ mục
> 咖啡）. Sửa `source_ref` của `duong-phen` (ghi nhận câu lẻ 性溫 bị bác) và
> `rau-cau-kho` (làm rõ phạm vi định danh). Hai khẳng định âm tính của nhóm nghiên cứu
> (A+ không có mục muối / không có trang 豬脂膏） bị bác bởi kiểm chứng trực tiếp — giữ
> nguyên gieo đợt 3 chính. Bản đồ chờ cập nhật: (i) 57 · (ii) 84 · (iii) 10 (ra
> `dau-trang`, vào `mam-xoi` — trang 覆盆子 tự trộn *R. idaeus*/*R. chingii*) · (iv) 29 →
> **180 mục**; đính chính cách đếm: 207 NL không có dòng nhãn = 180 ở bản đồ + 27 xử lý
> bằng components. **Tổng cuối THAY số của lần 24: 227/434 nguyên liệu có nhãn — 413
> dòng (393 thật + 20 suy diễn); coverage 500 món ≥60% · 98 món 30–<60% · 50 món <30%;
> món Mức 1 đạt tiêu chí phủ 135/182.** Toàn bộ cổng đã chạy lại sau bổ sung: validate
> **0 🔴 / 11 🟡**, Pest **157/157**, ExportStabilityTest đạt, rà nhất quán 0 vấn đề.
> Chi tiết: [TCM-001](docs/05-tasks/tasks/TCM-001.md) §Đợt 3 — Bổ sung.

> ## ⚠️ Đính chính lần 26, 2026-11-01 — VN-001 đợt 1: ghi chú văn hoá cho nhóm ứng viên nâng bù
>
> **VN-001 đợt 1 ĐÃ XONG:** viết `cultural_note` cho **87/135** món Mức 1 đạt tiêu chí
> phủ tính vị ≥60% (nhóm ứng viên đường nâng bù); **48 món chờ có lý do** (31 không có
> nguồn biên tập đạt chuẩn viết riêng · 12 lệch định danh/chờ quyết định danh · 5 chất
> liệu chủ yếu theo khung sức khoẻ bị cấm). Census mở lại 100% URL nguồn: 7 URL
> chết/thay nội dung → 6 món viết lại từ nguồn thay thế đã kiểm chứng. Rà soát diễn
> đạt theo ranh giới `/mien-tru-y-te`: 0 câu thuộc khung y tế bị cấm. **Đợt này
> KHÔNG đổi `completeness_level`** — 87 món đủ 2 tiêu chí nâng bù chỉ là thống kê;
> nâng bù là đợt riêng. Quy ước rút ra: nguồn phải mở lại được lúc chốt đợt; ghi chú
> bám định danh trong file; ghi chú cấp nguyên liệu phải minh bạch cấp nguyên liệu.
> Kiểm chứng: validate **0 🔴**, Pest **157/157** (gồm ExportStabilityTest). Chi
> tiết: [VN-001](docs/05-tasks/tasks/VN-001.md).

> ## ⚠️ Đính chính lần 27, 2026-11-01 — TCM-PROMOTE-001: đã áp đường nâng bù "Mức 2 (có note)" cho 87 món
>
> **87 món** đủ cả 2 tiêu chí nâng bù của VN-001 đợt 1 (Mức 1 + phủ tính vị ≥60% +
> có `cultural_note`) đã được nâng lên **"Mức 2 (có note)"**:
> `completeness_level` 1 → 2 kèm `level_basis='tcm_compensation'` và `level_note`
> riêng cho từng món — soạn từ hồ sơ giữ Mức 1 trong `import_note` của chính món
> (các mục LV2/AUDIT-FIX), nêu đúng cổng chưa đạt: phân bố lý do — 23 thiếu
> nguồn/không đồng thuận/không quy đổi được · 21 biến thể/chờ quyết định danh ·
> 20 trần nguyên liệu định danh null · 14 bị chặn 429 chưa kết luận · 8 không có
> công thức đúng định danh/chưa biên soạn · 1 nguồn đúng món chết link
> (`dau-phu-kho-chao`, AUDIT-FIX PX-14). Danh sách đối chiếu khớp tuyệt đối 3
> chiều (file `data/` · VN-001 135−48 · DB kiểm chứng riêng sau seed +
> recompute-tcm) — không món nào ngoài nhóm 87 đổi mức. **Toàn kho sau nâng:
> 472 Mức 2 thường + 87 Mức 2 (có note) + 95 Mức 1 = 654.** Kiểm chứng: validate
> **0 🔴** (cổng V20 đạt cả 87; 22 🟡 đều là lớp đã biết, không 🟡 mới), Pest
> **157/157** gồm ExportStabilityTest (vòng seed→export vẫn điểm cố định), API
> trả `level_label` "Mức 2 (có note)" đúng nhóm, admin gắn thẻ vàng theo
> `level_basis`. Chi tiết + bảng 87 món kèm note đầy đủ:
> [TCM-PROMOTE-001](docs/05-tasks/tasks/TCM-PROMOTE-001.md).

> ## ⚠️ Đính chính lần 28, 2026-11-01 — VN-001 đợt 3: ĐÓNG TUYẾN GHI CHÚ VĂN HOÁ
>
> Đợt cuối của tuyến ghi chú: **51/95 món Mức 1** còn lại (24 món cứu từ
> nhóm chờ đợt 1 — gồm cả 4 món nhóm C; chờ còn 44: A 23 · B 19 · C 2) và
> **75/82 hồ sơ quả** (chờ 7: A 3 · B 2 · C 1 · B/C 1) đã có `cultural_note`
> đạt chuẩn; 7 file Mức 2 bị phiên song song giữ (`banh-canh-ca-loc`,
> `bun-thang`, `canh-bap-cai-cuon-thit`, `com-bo-kho`, `com-ga-tam-ky`,
> `dau-bap-xao-toi`, `rau-muong-luoc`) vẫn chỉ diff `media` → bỏ qua lần 2.
> Census mở lại 100% URL nguồn mới (128 URL): **0 chết/0 thay nội dung**;
> khắc phục 18 file bằng cắt câu ngoài nguồn; rà diễn đạt **0 vi phạm**.
> **Toàn kho: 388/654 món + 75/82 quả có `cultural_note`.** Thống kê đủ
> cổng Mức 3 (chưa gán): 187 Mức 2 thường · 274 tính cả nâng bù · 301 phủ
> ≥60% + có ghi chú ở mọi mức. Validate **0 🔴**; ExportStabilityTest đạt;
> Pest 160/162 (2 đỏ ở `SwapTrayTest` — tính năng của phiên song song,
> không liên quan dữ liệu). Hàng đợi định danh thêm 9 mục (nổi bật:
> `mam-ruoi` cần chủ dự án quyết đổi tên hay viết lại công thức). Chi tiết:
> [VN-001](docs/05-tasks/tasks/VN-001.md) mục đợt 3 + tổng kết tuyến.

> ## ⚠️ Đính chính lần 28, 2026-11-01 — TCM-003: khối "Tính vị:" đổi sang bản gọn (giá trị chính + popup)
>
> Theo chỉ đạo trực tiếp của chủ dự án sau khi xem bản thật (khối cũ đổ toàn bộ
> kết luận + độ phủ + nguồn dài + ghi chú ra trang, gây loạn thông tin và vỡ bố
> cục di động): trang món/nguyên liệu/quả nay chỉ hiện **một dòng giá trị chính
> + nút "Nguồn & chi tiết"**; toàn bộ thông tin thêm (độ phủ, độ tin cậy, mọi
> dòng nhãn và nguồn, mâu thuẫn, giải thích phương pháp) nằm trong **popup**
> dùng chung ở layout (cùng pattern hộp thoại UI-018; đóng bằng X / bấm ra
> ngoài / Esc). Nhãn chính của nguyên liệu do `TcmDisplay` chọn (field `primary`
> cộng thêm vào API): confidence cao nhất → nhãn thật trước suy diễn → thứ tự
> file; nhãn chính là suy diễn thì có chip "suy diễn". Ngưỡng trung thực không
> đổi: món phủ <30% vẫn không hiện khối; không đổi dữ liệu. Chi tiết:
> [TCM-003](docs/05-tasks/tasks/TCM-003.md) mục "Cập nhật 2026-11-01 (chiều)".

> ## ⚠️ Đính chính lần 29, 2026-11-01 — VN-001 đợt 2: ghi chú văn hoá cho nhóm 472 món Mức 2 thường
>
> **Đã viết 250/465 món** trong phạm vi viết thực tế của đợt (472 món đích;
> 7 file đang dở của phiên song song bị bỏ qua và ghi rõ trong VN-001:
> `banh-canh-ca-loc`, `bun-thang`, `canh-bap-cai-cuon-thit`, `com-bo-kho`,
> `com-ga-tam-ky`, `dau-bap-xao-toi`, `rau-muong-luoc`), **215 món chờ có lý
> do** (A 201 không có nguồn biên tập đạt chuẩn viết riêng cho món · B 9 lệch
> định danh/chờ quyết định danh · C 5 chất liệu chủ yếu theo khung sức khoẻ bị
> cấm). Toàn kho sau đợt: **337/654 món có `cultural_note`** (87 đợt 1 + 250
> đợt 2). Census cổng đợt mở lại **100% URL nguồn** của ghi chú mới: **0 URL
> chết, 0 vi phạm khung y tế bị cấm trong text ghi chú**; khắc phục sau census:
> 29 slug cắt/sửa chi tiết không có trong nguồn, 23 slug thay nguồn biên tập +
> viết lại toàn bộ (nguồn cũ là trang công thức/SEO/thương mại/blog), 4 slug gỡ
> ghi chú chuyển về chờ (`ca-bong-kho-tieu-quang` → B; `canh-ca-nuc-la-giang`,
> `thit-xao-mam-ruoc`, `com-rang-dua` → A). Phát hiện định danh mới chỉ ghi vào
> hàng đợi rà soát trong VN-001, không tự sửa dữ liệu. **Đợt này KHÔNG đổi
> mức** — thống kê đủ cổng Mức 3 chỉ để thống kê: **187/472 món Mức 2 thường**
> đủ cả phủ tính vị ≥60% + `cultural_note`; toàn kho **274 món** đủ cổng
> (187 + 87 Mức 2 (có note)). Kiểm chứng: seed DB kiểm chứng riêng, validate
> **0 🔴** (3.344 bản ghi), Pest **157/157** gồm ExportStabilityTest. Chi
> tiết: [VN-001](docs/05-tasks/tasks/VN-001.md).
>
> ## ⚠️ Đính chính lần 30, 2026-11-01 — TCM-003: giá trị chính tính vị tách 3 dòng có màu + icon động
>
> Theo chỉ đạo bổ sung của chủ dự án (~15:29, thay phần giá trị chính 1 dòng
> của lần 28): phần chính của khối tính vị nay tách thành các dòng riêng có
> tiêu đề — **"Tính:"**, **"Vị:"**, **"Ngũ hành:"** — mỗi giá trị có **màu
> riêng + icon SVG động** (bảng tra duy nhất `window.ldTcm` trong layout;
> CSS `.ld-tcm-*` trong `resources/css/app.css`, đã `npm run build` và kiểm
> chứng bundle mới). Màu tính theo dải lạnh→nóng (Hàn xanh đậm #0058B3,
> Lương xanh lơ #3F8FD2, Bình sage #5F7A61, Ôn hổ phách #C77400, Nhiệt đỏ
> #D70015); vị theo hành tương ứng (Toan Mộc xanh lá, Khô Hoả đỏ, Cam Thổ
> vàng nâu #7A5200, Tân Kim xám #636368, Hàm Thuỷ xanh đậm, Đạm Thổ nhạt);
> hành đúng 5 màu ngũ hành (Kim bạc #6E6E73 + viền chống chìm, Thuỷ xanh
> dương đậm). Icon hành **bắt buộc động** (lá đung đưa, lửa nhấp nháy, núi
> nhấp nhô, tia sáng loé, sóng gợn — chỉ transform/opacity, tắt khi
> `prefers-reduced-motion`). **Trang món chỉ 2 dòng Tính + Ngũ hành** (món
> không có vị — không bịa); nguyên liệu/quả đủ 3 dòng từ nhãn chính; popup
> giữ nguyên cấu trúc lần 28, các dòng nhãn trong popup dùng cùng ngôn ngữ
> màu/icon (segments). Quy tắc trung thực và dữ liệu không đổi. Kiểm chứng
> render DOM thật 7 ca đều đạt; Pest 156/157 (đỏ duy nhất ExportStabilityTest
> do file dữ liệu đang dở của phiên VN-001 đợt 3, không liên quan đợt này).
> Chi tiết: [TCM-003](docs/05-tasks/tasks/TCM-003.md).

> ## ⚠️ Đính chính lần 31, 2026-11-01 — TCM-PROMOTE-002: gán Mức 3 lần đầu + nâng bù đợt 2 — bức tranh mức toàn kho đổi
>
> **187 món Mức 2 thường đủ cổng Mức 3 (phủ tính vị ≥60% + ghi chú văn hoá)
> đã gán `completeness_level=3`** — đối chiếu thống kê VN-001 khớp tuyệt
> đối; món "Mức 2 (có note)" không gán Mức 3 (Mức 3 đầy đủ đòi nền Mức 2
> thường). Grep toàn bộ nơi dùng mức: **0 dòng code phải sửa**
> (`Dish::levelLabel()` sinh nhãn động → "Mức 3"; bộ lọc mức ở admin đã có
> sẵn tuỳ chọn Mức 3; validator V20 đã kiểm cổng Mức 3 từ TCM-000) — đã
> kiểm hiển thị thật qua API/trang món trên DB kiểm chứng. **Nâng bù đợt
> 2: 27 món Mức 1 mới đủ 2 tiêu chí sau VN-001 đợt 3** lên "Mức 2 (có
> note)" với `level_note` riêng từng món soạn từ hồ sơ giữ Mức 1.
> **Bức tranh mức toàn kho hiện tại: 187 Mức 3 + 285 Mức 2 thường + 114
> Mức 2 (có note) + 68 Mức 1 = 654 món** (khớp 3 cách file · DB ·
> recompute). Kiểm chứng: validate 0 🔴 (24 🟡 — +2 V9 mới ở hai món rau
> luộc vừa nâng bù do seed nay tính kcal/khẩu phần, cùng lớp đã biết),
> Pest 162/162 (cả SwapTrayTest của phiên song song đã xanh),
> ExportStabilityTest đạt. Chi tiết:
> [TCM-PROMOTE-002](docs/05-tasks/tasks/TCM-PROMOTE-002.md).

> ## ⚠️ Đính chính lần 32, 2026-11-01 — AUDIT-ALLERGEN: nhãn dị ứng sạch, nhưng LỌC CỨNG rò rỉ ở tầng preset giao diện
>
> Census dị ứng toàn kho (654 món / 434 nguyên liệu / 82 quả / 11 nhóm, 2 phương pháp độc lập khớp
> tuyệt đối, validate 0 🔴): **tầng nhãn không có lỗi cơ học nào** — file khớp `TaggingRules` 100%,
> nhãn món khớp `DishTagDeriver` 100%. Cờ cá mà chủ dự án thắc mắc đã phân rã trọn: 388 món có cờ =
> 109 món có cá thật + **274 món chỉ vì nước mắm/mắm cá (đúng chính sách đã ghi ở TAXONOMY §4 — chờ
> chủ dự án quyết ALG-Q1)** + 5 món vì giò/chả quy ước + 0 món không giải thích được. **Lỗi thật nằm
> ở tầng lọc:** `Recommender` lọc theo slug, còn danh sách slug người dùng chọn được là **preset viết
> tay đóng băng từ REC-001** ở `shake.blade.php`/`profile.blade.php` — **thiếu hẳn chip Cá (388 món),
> Động vật có vỏ (56), Mè (53)** và thiếu slug hàng loạt (đo `pool()` thật: chọn "Đậu nành" vẫn lọt 36
> món mang cờ, "Tôm" lọt 22 — gồm cả món tôm càng; dùng toàn bộ slug có nhãn thì rò 0). Gắn thiếu duy
> nhất ở tầng dữ liệu: `cha-bong` (hồ sơ ghi có nước mắm nhưng bảng luật chưa gắn cờ cá; hiện 0 món
> dùng). Chưa sửa gì trong đợt rà — đề xuất FIX-A1…A4 + quyết định ALG-Q1…Q4 tại
> [AUDIT-ALLERGEN](docs/05-tasks/tasks/AUDIT-ALLERGEN.md).

> ## ⚠️ Đính chính lần 33, 2026-11-01 — AUDIT-004: giá trị tính/vị/ngũ hành xác minh độc lập, 0 dòng sai
>
> Kiểm tra độc lập bằng **tái suy dẫn mù** (9 agent chỉ nhận định danh 227 nguyên liệu, cấm đọc dữ
> liệu/TCM-001; lô thịt tra 2 lượt bằng 2 họ kênh) + lượt 2 mở đúng `source_ref` cho 185 dòng chưa
> khớp cùng nguồn (85 URL) + script tính lại không dùng code engine trên DB riêng
> `shake_hungry_audit4`: **sai giá trị 0/413 dòng** — xác minh 413/413 (208 khớp mù cùng nguồn ·
> 183 lượt 2 · 20 P-DERIVE qua gốc đã xác minh · 2 giá trị đúng nhưng trích dẫn khuyết điểm).
> 13 dòng bị cờ "mâu thuẫn" ở lượt đối chiếu đầu đều đúng theo cổ thư chúng trích — trang nguồn
> chép nhiều truyền thống lệch nhau, không phải lỗi sao chép. Tổng hợp độc lập **0/648 món lệch**
> (phủ 500/98/50 khớp công bố), element 413/413, hiển thị API **0 lỗi/881 endpoint**. 🟠 Duy nhất:
> `dau-do-hat` dẫn trang tcmfans làm "bản tái bản kiểm chứng" của Dược điển nhưng trang đó ghi
> biến thể ngược (vi lương) — giá trị bình vẫn đúng theo Dược điển (FIX-TCM4-1). Còn: đề xuất đợt
> gieo bổ sung TCM-001d ~14 truyền thống có thật chưa gieo (FIX-TCM4-2), quy ước bản EN dịch máy
> của zhongyifangji không dùng làm căn cứ tính vị (FIX-TCM4-3), 1 ca link 404 chuyển AUDIT-003
> (FIX-TCM4-4). Chưa sửa dữ liệu/code trong đợt. Chi tiết:
> [AUDIT-004](docs/05-tasks/tasks/AUDIT-004.md).

> ## ⚠️ Đính chính lần 34, 2026-11-01 — ALLERGEN-FIX: lọc cứng hết rò — preset dị ứng nay SINH TỪ TaggingRules
>
> Khắc phục các mục đã kết luận của AUDIT-ALLERGEN (ALG-Q1…Q4 giữ nguyên chờ chủ dự án): **xoá preset
> slug viết tay đóng băng từ REC-001** ở `shake.blade.php`/`profile.blade.php`, thay bằng
> `App\Taxonomy\AllergenPreset` sinh danh sách loại trừ từ `TaggingRules` trên toàn bộ catalog —
> cả hai trang đủ **11 chip dị ứng** (trước thiếu Cá/Động vật có vỏ/Mè) và đủ mọi slug có nhãn
> (`nuoc-tuong`, `tom-cang-xanh`, `ghe`...). **Đo lại bằng `Recommender::pool()` trên toàn kho:
> rò 0 cho cả 11 nhóm** (trước: đậu nành 36, tôm 22, lúa mì 11, mắm tôm 9, trứng 7, sữa 5, cua 3),
> loại oan 0; `AllergenExclusionTest` (5 test) khoá tính chất preset ≡ tập slug có nhãn + hành vi
> pool để không tái phát. `cha-bong` đã gắn cờ `ca` ở bảng luật + file (0 món dùng → 0 món bị ảnh
> hưởng; nguyên liệu có cờ cá 38 → 39). Luật tiền tố `ca-` trong `TaggingRules` **không đổi** nhưng
> nay có test liệt kê toàn catalog phủ ca biên (cá thật đều có cờ; `ca-chua`/`ca-rot`/`ca-tim`/
> `ca-na`/`ca-phe-den` không bao giờ thành cá; thuỷ sản không-`ca-` chỉ có cờ qua bảng tường minh:
> `luon`, `cha-ca-vien`). TAXONOMY §4 sửa câu chữ: mắm nêm lên men từ **cá cơm** → thuộc nhóm cá,
> không thuộc nhóm `mam-tom` (dữ liệu vốn đã đúng, chỉ câu chữ sai). Kiểm chứng: validate 0 🔴 trên
> DB kiểm chứng seed mới, ExportStabilityTest đạt, **Pest 167/167**. Chi tiết:
> [AUDIT-ALLERGEN](docs/05-tasks/tasks/AUDIT-ALLERGEN.md) §6.

> ## ⚠️ Đính chính lần 35, 2026-11-01 — FIX-TCM4: sửa trích dẫn dau-do-hat + gieo bổ sung TCM-001d (+23 dòng)
>
> Khắc phục đầu ra của AUDIT-004. **FIX-TCM4-1:** `dau-do-hat` giữ nguyên giá trị bình/cam/toan
> theo Dược điển; liên kết "bản tái bản kiểm chứng" tcmfans (tự mang biến thể "Slightly cool")
> đã thay bằng bản tái bản **A+醫學百科 赤小豆** mở kiểm chứng tận nơi khớp Dược điển
> 「性平，味甘、酸」, ref ghi rõ biến thể 微寒 thuộc 《食性本草》. **FIX-TCM4-2 = TCM-001d:**
> gieo bổ sung **+23 dòng cho 13 nguyên liệu** từ danh sách ứng viên AUDIT-004 §7 (bí đao,
> cần tây, đậu bắp, ếch, nghêu, thịt vịt ×2, tim lợn, lòng lợn, cà tím, dưa leo, củ sen,
> quả sấu) — mọi nguồn **mở lại kiểm chứng first-hand tại lúc gieo**, gieo song song không
> ghi đè; tổng nhãn **436 dòng / 227 NL**. Quy tắc phân xử mới ghi tại TCM-001d: cổ thư ghi
> kép hai tính trong một câu → gieo cả hai vế; câu dẫn chỉ có tính không có vị → không lập
> dòng. **`rau-mui` chờ có hồ sơ:** vế lương （孟詵「平、微寒」) không kèm vị — gieo sẽ phải
> trộn nguồn hoặc mở tiền lệ dòng `flavor = null` chưa có thiết kế. **FIX-TCM4-3:** quy ước
> bản EN của zhongyifangji/tcmfans chỉ làm biển chỉ đường đã ghi tại
> [NGU-HANH-AM-DUONG](docs/08-cultural/NGU-HANH-AM-DUONG.md) §10. Ảnh hưởng tổng hợp (đo
> diff trên DB kiểm chứng riêng `shake_hungry_tcm4`): **độ phủ không đổi** (500/98/50,
> 0 món qua ngưỡng khoá <30%); `has_conflicts` 220 → **259** (+39, đúng thiết kế);
> 24 món confidence medium → low; 11 món đổi nhãn tính hiển thị; 2 món đổi hành chủ đạo.
> Kiểm chứng: validate **0 🔴**, Pest **167/167**, ExportStabilityTest đạt. Chi tiết:
> [TCM-001](docs/05-tasks/tasks/TCM-001.md) mục TCM-001d, [AUDIT-004](docs/05-tasks/tasks/AUDIT-004.md) §5.

> ## ⚠️ Đính chính lần 36, 2026-11-01 — AUDIT-003: census toàn lớp Mức 3 — lớp đạt chuẩn cao, khuyết điểm chỉ ở chuỗi trích dẫn
>
> Rà soát census 100% hai lượt toàn lớp Mức 3 (baseline 413 dòng nhãn; corpus đã lên 436
> sau FIX-TCM4 giữa đợt — phán quyết gắn với baseline, 23 dòng TCM-001d chưa qua census
> nguồn của đợt này). Kết quả: **tổng hợp độc lập 0/648 lệch**; **cổng mức sạch hai chiều**
> (187/187 Mức 3 đạt cổng, 114/114 nâng bù không trôi so với lúc nâng, 0 ca sót đủ điều
> kiện); **ghi chú 463/463: 0 vi phạm khung y tế bị cấm**, sự kiện chính khớp ở mọi URL
> mở được (359/465 URL kiểm chứng nội dung — 0 chết, 0 sai bài; 106 URL chưa kết luận vì
> dịch vụ mở trang chặn 429, không phải lỗi dữ liệu); **components 39/39** tồn tại + hợp
> lý; **hiển thị đúng chuẩn TCM-003, 55/55 ca DOM**; validate **0 🔴**, Pest 162/162 tại
> thời điểm chạy (gồm ExportStabilityTest đạt). **Giá trị tính vị: 0 dòng sai** — hội tụ
> với AUDIT-004. 🔴 duy nhất nằm ở **chuỗi URL trích dẫn** (giá trị gieo đều đúng):
> `ca-chim` URL sai loài （鯇 cá trắm cỏ thay 鯧 cá chim); `ca-doi` ×2 URL trượt ký tự
> （鱸魚 thay 鯔魚) + bản phồn thể đã rot — chính là ca **FIX-TCM4-4** AUDIT-004 chuyển
> sang, đích thay thế là bản giản thể còn nguyên văn; lớp **URL dính dấu chấm câu cuối**
> 3 token (`dau-do-hat` ×2 — kể cả URL A+ 赤小豆 mới của FIX-TCM4-1 — và `rau-diep-ca`
> id/394): token trích ra được trả 404, đích không-dấu-chấm tồn tại đúng nội dung. 🟡:
> 8 dòng trích tên mục không kèm URL (nội dung kiểm chứng 8/8 tại trang mục A+); một số
> chi tiết phụ trong ghi chú chưa có căn cứ ở nguồn đã mở (AX3-08); nguồn của chính ghi
> chú mam-ca-ro ghi mắm làm từ **cá kình con** — corroboration cho mục định danh đang chờ
> quyết. Khắc phục gom tại danh sách AUDIT-003-FIX (báo cáo §9), chưa thực thi. Chi tiết:
> [AUDIT-003](docs/05-tasks/tasks/AUDIT-003.md) + báo cáo rà soát lớp Mức 3 2026-11-01.

> ## ⚠️ Đính chính lần 37, 2026-11-01 — AUDIT-003-FIX: khắc phục toàn bộ danh sách AUDIT-003
>
> Toàn bộ khắc phục chỉ trong `source_ref`/diễn đạt ghi chú — **0 thay đổi giá trị
> tính/vị/hành, định lượng, mức**. 🔴: `ca-chim` sửa ký tự URL sang 鯧魚 (mở lại:
> 「味甘；性平」khớp); `ca-doi` ×2 thay URL sang bản giản thể A+ 鱼 (cùng mục, còn
> sống, *Mugil cephalus* 平/甘咸 khớp); tách dấu chấm cuối ở 3 token và quét lại
> toàn corpus theo token URL — **0 còn sót**. 🟡: bổ sung URL đã mở kiểm chứng cho
> 8/8 dòng không-URL (AX3-05); `hat-tieu-den` mở lại được và **khớp** nhiệt/tân
> (AX3-06, không sửa). **Census bổ sung 106 URL ghi chú (AX3-07): 99 sống-khớp ·
> 5 lệch-phụ · 1 chết · 1 chưa kết luận → 464/465 URL ghi chú có phán quyết nội
> dung (99,8%)**; cả 5 ca lệch-phụ đều khép ở tầng hợp tập nguồn khi mở kiểm chứng
> nguồn còn lại của chính ghi chú (en.wiki Bò kho, wiki Festival Dừa, Dân Việt
> "Xôi Sài Gòn", wiki Nộm, Thương hiệu & Công luận cho mam-tom-chua); URL chết duy
> nhất là wiki **Bún kèn** (curl HTTP 404, phân xử dứt điểm mâu thuẫn hai kênh ở
> AUDIT-003) — đã gỡ khỏi ghi chú, ghi chú đứng trên Dân Việt. AX3-08: gọn diễn
> đạt 11 ghi chú (bỏ chi tiết không nguồn nào đỡ), bổ sung nguồn VietnamNet cho
> `mien-luon`, 7 ca kiểm lại có căn cứ nên giữ nguyên; 2 ca bị gắn cờ hoá ra
> **không tồn tại trong ghi chú đã lưu** (`mam-ca-loc`, `bun-mam-chay` — chi tiết
> chao chỉ ở `import_note` nội bộ). AX3-09: ghi chú chè Thái là cấp món, khớp bài
> "chè tứ xứ" (sữa tươi, sầu riêng, Nguyễn Tri Phương); chi tiết cấp quán Ý
> Phương không đưa vào. **Quét bù TCM-001d: 23/23 dòng khớp nguyên văn trên 8/8
> URL**; quét cổng + tổng hợp lại trên corpus 436 dòng: bức tranh mức
> **187 Mức 3 · 285 Mức 2 · 114 Mức 2 (có note) · 68 Mức 1 không đổi**, phủ
> 500/98/50, 114/114 nâng bù ≥60%, 0 sót thăng hạng. Tồn cuối duy nhất: PDF
> huetc của `bun-hen` (lỗi vận chuyển ở 3 kênh, chưa từng có HTTP status — không
> kết luận chết; ghi chú còn nguồn Cục Du lịch đã kết luận). Kiểm chứng: validate
> **0 🔴**, Pest **167/167** (ExportStabilityTest đạt). Chi tiết:
> [AUDIT-003](docs/05-tasks/tasks/AUDIT-003.md) §5–§6.

> ## 📘 Bổ sung lần 38, 2026-11-02 — tài liệu setup dev trên máy khác (DEV-SETUP-DOC)
>
> Theo chỉ đạo của **chủ dự án** (chuẩn bị tự go-live): đã lập
> [SETUP-DEV.md](docs/10-deployment/SETUP-DEV.md) — hướng dẫn cài môi trường dev từ số 0
> trên máy khác (Windows/macOS/Linux), đối chiếu file thật tại baseline `a310c22`
> (`composer.json`, `package.json`, `.env.example`, `phpunit.xml`, seeders,
> `config/admin.php`). **Đính chính phạm vi của DEPLOYMENT.md:** §1–§5 của
> [DEPLOYMENT.md](docs/10-deployment/DEPLOYMENT.md) (2026-08-31) nay được SETUP-DEV
> thay thế ở phần cài dev — bản cũ thiếu extension PHP, cơ chế tạo tài khoản admin
> (seeder không tạo user; tạo bằng tinker, `role='admin'`), cảnh báo
> `ADMIN_QUICK_LOGIN`, quy tắc bắt buộc `npm run build` sau khi sửa `resources/css|js`
> (bài học UI-020) và kỷ luật chạy Pest trên DB test riêng; DEPLOYMENT.md giữ §5
> (checklist bàn giao) + §6 (production). Docker tại thời điểm này **chưa có trong
> repo** — gói Docker go-live là task riêng đang bổ sung. Chi tiết:
> [DEV-SETUP-DOC](docs/05-tasks/tasks/DEV-SETUP-DOC.md).

**Trạng thái thực tế, kiểm ngày 2026-10-31 (đã chạy lại toàn bộ lệnh kiểm chứng):**

| Hạng mục | Thực tế | Kiểm bằng |
|---|---|---|
| Tài liệu | ✅ 23 tài liệu trong `docs/` + 52 task file | `find docs -name "*.md" -not -path "*/tasks/*" \| wc -l` |
| **Dữ liệu nguyên liệu** | ✅ **406 file**: **365 có số dinh dưỡng có nguồn**, 41 để trống có hồ sơ lý do (13 nguyên liệu gốc tra đủ 3 vòng không có bảng đạt chuẩn + các mục phát sinh sau, mới nhất: chả lụa chay của DISH-005 đợt 3). Nguyên liệu gốc: **242/255 (94,9%) có số**. (Số đếm trực tiếp từ thư mục `data/ingredients` 2026-10-31 sau DISH-005 đợt 3; các đợt trước: 386 theo tầm soát, 397 sau đợt 1, 403 sau đợt 2.) ⚠️ File nguyên liệu vẫn giữ `needs_review` — duyệt nguyên liệu là trục riêng, chưa duyệt (khác với món, xem dòng dưới) | `php artisan lacdoi:validate-data` |
| Dữ liệu món ăn | ✅ **654 file đầy đủ công thức** (628 món chuẩn + 25 biến thể + 1 món hoãn dau-phu-chien-sa-ot — đã gộp tầm soát 2026-10-31: xoá 4 món nước mắm địa danh trùng tuyệt đối, tên địa danh thành alias của `nuoc-mam`; liên kết biến thể `nom-mit-non` → `goi-mit-non` và `xoi-ngo` → `xoi-bap`; xem [DATA-AUDIT-FIXES](docs/04-data-sources/DATA-AUDIT-FIXES-2026-10-31.md); DISH-005 đợt 1 +60 món canh/rau, canh cả 3 miền đều ≥20; đợt 2 +65 món: mặn nhanh ≤30′ 40 + lõi miền Trung 25; đợt 3 +35 món: món chính chay 20 — **kiểm chứng DB 20/20 suy đúng `diet:chay`** — + bữa sáng tự nấu 15; DISH-005 đã đóng đủ quota; hiệu chuẩn đậu phụ CALC-004 → biên soạn thêm món hoãn `dau-phu-chien-sa-ot` → 657; **LV2-001 đợt 1: 47/50 món nền tảng đạt Mức 2 thật** — ≥3 nguồn công thức đã mở kiểm chứng/món, 3 món giữ Mức 1 có lý do ghi trong hồ sơ; **Phần A đã sửa bất thường LV2-001 (9 món) và LV2-002 đợt 2 nâng thêm 50/50 món — tổng 97 món Mức 2; Phần A phân xử bất thường LV2-002 (26 món sửa dòng/phân loại) và LV2-003 đợt 3 nâng thêm 37/50 món — tổng 134 món Mức 2, 13 món giữ Mức 1 có lý do ghi trong [LV2-003](docs/05-tasks/tasks/LV2-003.md); LV2-004: Phần A phân xử toàn bộ bất thường LV2-003 (21 món sửa dòng) + 5 nguyên liệu mới đều null + hồ sơ, Phần B đợt 4 nâng thêm 50/50 món — tổng 184 món Mức 2; **LV2-005 đợt 5 (đợt chốt): Phần A phân xử bất thường LV2-004 (vùng ca-tram-hap-xi-dau, 5 ca định danh) + 32 món — tổng 216 món Mức 2, CHƯƠNG TRÌNH MỨC 2 ĐỢT 1 HOÀN TẤT vượt mốc 200; quyết định còn chờ gom tại [LV2-005](docs/05-tasks/tasks/LV2-005.md) §4**; **LV2-006 — đợt đầu CHƯƠNG TRÌNH MỞ RỘNG nâng toàn bộ món lên Mức 2 (chỉ đạo chủ dự án 2026-10-31): Phần A phân xử bất thường LV2-005 (com-hen nâng Mức 2, vit-xao-mang đổi method sang nau, bun-mam hồ sơ chờ số mắm cá) + Phần B 39/50 món — tổng 256 món Mức 2, còn 401 món Mức 1 cho các đợt tiếp theo**; **LV2-007: Phần A phân xử toàn bộ bất thường LV2-006 (7 ca) + hàng đợi nguyên liệu (rau đắng, trân châu CÓ SỐ; mắm cá sặc/linh, heo quay giữ null có hồ sơ; muối tôm, củ nén slug mới) — bun-mam/lau-mam vẫn chưa bổ sung dòng mắm cá vì mắm còn null; Phần B nâng 45/50 món — TỔNG 301 món Mức 2, còn 356 món Mức 1; biên soạn thêm 6 nguyên liệu (bot-khoai, gelatin, kem-whipping, men-no, bot-no có số USDA; cha-bong null) — kho 420 nguyên liệu; quyết định mới chờ chủ dự án tại [LV2-007](docs/05-tasks/tasks/LV2-007.md) §3**; **LV2-008: Phần A phân xử bất thường LV2-007 (cha-bong VẪN null sau vòng tra nhãn — xoi-man giữ Mức 1; hung-lui slug mới CÓ SỐ USDA SR 173475 + bổ sung dòng cho goi-xoai; khép 2 bất thường chờ phân xử sau) + Phần B nâng 45/60 món xét — TỔNG 346 món Mức 2, còn 311 món Mức 1; 15 món giữ có hồ sơ (5 do 429, bun-nuoc-leo trần mắm cá null, ca-bong-om-chuoi chờ xác nhận có thật, canh-rau-den-tep chờ quyết tép khô/tươi) — kho 421 nguyên liệu; bất thường mới chờ chủ dự án tại [LV2-008](docs/05-tasks/tasks/LV2-008.md) §3**; **LV2-009 Phần A: phân xử §3 của LV2-008 — biên soạn 4 slug thiếu đều null + hồ sơ (`cha-com`, `bong-sung`, `ngo-om`, `ngai-bun` — danh tính ngải bún chốt là *Boesenbergia rotunda*, bộ số "krachai" trên web là số của gừng sao chép), bổ sung dòng cho `bun-dau-mam-tom`/`canh-ca-linh-bong-dien-dien`, thay dòng sai loài `ngo-gai` → `ngai-bun` ở `bun-nuoc-leo` — kho 425 nguyên liệu; **LV2-009 Phần B đợt 9: 27/55 món xét đạt Mức 2 + bổ sung 4/5 món bánh — TỔNG 377/657 món Mức 2, còn 280 món Mức 1; 29 món giữ có hồ sơ theo nhóm lý do, bất thường mới + ca chờ quyết gom tại [LV2-009](docs/05-tasks/tasks/LV2-009.md) §B.3 và §B.5**; **LV2-010 Phần A: phân xử bất thường LV2-009 (áp vùng/nhãn banh-goi + gạo nếp che-dau-trang; giữ có lý do các mục còn lại) + biên soạn 8 slug hàng đợi (5 có số USDA, 3 null + hồ sơ) — kho 433 nguyên liệu, bổ sung dòng lau-bo/che-com/che-hat-sen-long-nhan; **LV2-010 Phần B đợt 10: 23/50 món xét đạt Mức 2 — TỔNG 400/657 món Mức 2, còn 257 Mức 1; 27 món giữ có hồ sơ (11 biến thể, 9 còn 429, 6 thiếu nguồn, 1 trần nguyên liệu); bất thường mới gom tại LV2-010 §B.3; kiểm chứng: validate 0 🔴 cả DB, Pest 127/127, đối chiếu calo cách ly bằng worktree sạch****LV2-010 Phần C — gói quyết định A1–A4 của chủ dự án: áp bộ sửa canh-ba-roi-nau-dau → Mức 2; XOÁ 3 món không xác nhận được tồn tại (canh-nghe-ca-ro, ca-bong-om-chuoi, canh-ca-bong) — kho còn 654 món; A3 chốt định danh cấp món (banh-bo men nở khô → Mức 2; bau-xao-tep/canh-rau-den-tep → tép khô; dau-phu-kho-nam → nấm rơm); A4 áp quy ước cấp kho (rau xào bỏ nêm mắm 12 món + 2 ngoại lệ; cá kho bỏ thốt nốt 11 món; quy ước lượng sợi tại EDITORIAL-GUIDE §14; mong-gio 150 g) + nhóm rang muối chiên + xóc bột rang muối (slug mới bot-rang-muoi null) — 3 món nâng Mức 2; TỔNG 406/654 món Mức 2, còn 248 Mức 1; 434 nguyên liệu; kiểm chứng: validate 0 🔴, recompute 406/406 tính đủ, Pest 127/127, đối chiếu calo cách ly 0 món ngoài phạm vi đổi >1%**; **LV2-011: Phần A rà bất thường LV2-010 §B.3 — không đổi dữ liệu (11 ca chờ chủ dự án, 2 ca chuyển Phần B; dau-bap-xao-toi hoãn A4.1 vì file còn dở của phiên song song); Phần B đợt 11: 15/50 món xét đạt Mức 2 thật — TỔNG 421/654 món Mức 2, còn 233 Mức 1 (35 món giữ có hồ sơ: 22 thiếu nguồn quy đổi được, 11 biến thể, 2 trần nguyên liệu định danh null; không món nào gặp 429 đợt này); kiểm chứng: validate 0 🔴 (file 3.344 bản ghi + DB kiểm chứng riêng), recompute 421/421 tính đủ, đối chiếu calo cách ly 0 món cũ đổi khoảng + đúng 15 dòng mới, Pest 127/127, rà độc lập 9/10 nguồn mẫu khớp trực tiếp; bất thường mới: lau-ngan nghi sáng tạo biên tập (đề xuất danh sách xác nhận tồn tại), cặp goi-oc/nom-chuoi-xanh chờ quyết hoa chuối vs quả chuối xanh — chi tiết [LV2-011](docs/05-tasks/tasks/LV2-011.md)**; **LV2-012 — ĐỢT VÉT CUỐI: bảng kê toàn bộ 233 món Mức 1 (173 chưa từng xét + 60 đã giữ có hồ sơ), xét toàn bộ nhóm chưa từng xét + thử lại 12 món + vòng 2 nhịp chậm — 54 món nâng Mức 2, TỔNG 475/654 món Mức 2 (72,6%), CHƯƠNG TRÌNH MỞ RỘNG MỨC 2 TOÀN KHO ĐÓNG; 179 món còn Mức 1 đều có hồ sơ lý do theo nhóm; danh sách chờ quyết bản cuối 42 mục + hàng đợi nguyên liệu null tại [LV2-012](docs/05-tasks/tasks/LV2-012.md); kiểm chứng: validate 0 🔴, recompute DB kiểm chứng 475/475 tính đủ, đối chiếu calo cách ly đúng 54 dòng mới/0 dòng cũ đổi, Pest 127/127, rà độc lập 10/10 khớp**; **AUDIT-FIX 2026-11-01 (pha khắc phục sau AUDIT-002): nhóm A thay nguồn cho 16 món rớt cổng — 13 món khôi phục, 3 món hạ Mức 1 có hồ sơ (`canh-khoai-so`, `dau-phu-kho-chao`, `canh-rau-lang-tom`) → TỔNG 472/654 món Mức 2, còn 182 Mức 1; PX-17 loại nguồn sai loài + sửa tỉ lệ bột `ca-ro-dong-chien-xu`; PX-34 mở lại toàn bộ URL chưa kết luận (9 sống, 0 chết); PX-28 sửa 286 URL FDC sang mẫu API USDA NAL (260/260 mở lại đạt); **LINK-ROT-FIX 2026-11-01: thay nguồn cho 4 món rớt cổng vì rot MỚI phát hiện ở baseline LINK-ROT (`banh-uot-long-ga`, `canh-bi-ro-suon`, `canh-rau-ngot-thit-bam`, `mien-mang-ga` — cụm thegioiamthuc.com chết cả cụm) — cả 4 món khôi phục đủ 3 nguồn xác nhận sống, đối chiếu lại định lượng đều trong ngưỡng nên 0 món đổi số, TỔNG MỨC 2 GIỮ NGUYÊN 472/654** — chi tiết [AUDIT-FIX](docs/05-tasks/tasks/AUDIT-FIX.md) §8**), kèm hệ nhãn phân loại (TAXONOMY.md); **649/654 tính được dinh dưỡng** (5 món còn lại vướng thiếu dữ liệu thật: gỏi ngó sen, canh rau nhút, mắm nêm ×2, mắm ruột). **300 công thức đầu đã được chủ dự án duyệt ngày 2026-10-31** (4 món nước mắm trong số đó đã gộp vào món chuẩn); 361 công thức (200 của DISH-004 + 160 của DISH-005 cả 3 đợt + `dau-phu-chien-sa-ot`) đang chờ duyệt (`needs_review=true`) | `ls data/dishes/vn` |
| Bảng tham chiếu | ✅ SR Legacy **7.793** + Cooking Yields R2 **163** + FNDDS **333 Food Code / 1.810 SR Links** + `regions-vn.json` + `wikidata-vn-dishes.json`; **nguyên liệu gốc có số 242/255 (94,9%)** | `data/reference/MANIFEST.json` |
| Hệ số DATA-004/008 | ✅ khung xong: `edible_portion_pct` (3 có nguồn, 16 null chờ cân), `default_yield_factor` (9 thịt suy có nguồn) | `edible_portion_note` / `default_yield_factor_note` từng file |
| **Kiểm chứng engine** | 🔵 lần 2 (2026-09-01, engine **1.1.0**): **89,7% trong ±20%** (không đổi), MAPE 3,9%, và **% tham chiếu nằm trong khoảng 1,0% → 80,4%**. Hệ số hấp thụ dầu đã có theo nhóm (CALC-004, áp 2026-10-31) nên **nhóm chiên đã tính được**; **lần 3 (2026-10-31, QA-002 trên 160 món Việt): 76,9% trong ±20% — CHƯA ĐẠT 80%** (4 nhóm không chiên 100%; nhóm chiên 7,5%, bias +39,8% do dầu tính hai lần — xem ACCURACY-SPEC §6). **Lần 4 (2026-10-31, QA-002 lần 2 sau Phương án A — chủ dự án chốt đổi quy ước dữ liệu: bỏ/giảm dòng dầu chiên ở 40/46 món chiên, engine không đổi): 77,5% — VẪN CHƯA ĐẠT** (nhóm chiên 10,0%, bias +49,0%; chênh lệch tuyệt đối nhóm chiên gần như không đổi +143,7 → +137,0 kcal vì tham chiếu nguyên văn không chứa dầu hấp thụ engine mô hình hoá — chờ chủ dự án chốt bước tiếp theo báo cáo lần 2 §7); **Lần 5 (2026-10-31, QA-002 lần 3 — chủ dự án duyệt dựng tham chiếu chiên ĐỘC LẬP: hệ số dầu suy từ cặp sống→chiên USDA SR Legacy, không dùng hệ số engine hay 2 nguồn neo CALC-004): 98,1% trong ±20% — ĐẠT mục tiêu ≥80%** (nhóm chiên 92,5%, bias +12,2% trong đó +9,6 điểm % là cấu trúc điểm giữa khoảng). Rà soát CALC-004 theo ngưỡng đăng ký trước: **thịt/đạm 8→4, củ/quả 12→9** (giữ hải sản 9, phô mai 10, bột/vỏ 15, bột gạo 12; giữ 2 ngoại lệ gạo nếp); **lần 6 (hiệu chuẩn sau sửa): 98,8%**, nhóm chiên 95,0% (38/40 — 2 món còn lệch là cá chiên trần sốt, hồ sơ mở "hải sản chiên trần"); validate 0 🔴, Pest 106/106. **Lần 7 (2026-11-01, QA-002 lần 5 — mẫu MỚI seed 20261101 trên kho cuối 654 món sau LV2 + A1–A4 + AUDIT-FIX): 98,1% (157/160) — TÁI XÁC NHẬN ĐẠT** (4 nhóm không chiên 100%; chiên 92,5%, bias +9,33% mà cấu trúc điểm giữa chiếm +9,54 điểm % → dư −0,21 điểm %, **không hiệu chuẩn**; tham chiếu lọt khoảng 160/160; hồ sơ hải sản nguyên con chiên trần thêm `cua-rang-muoi`). Vẫn còn **nhóm hấp lệch −21%** ở bộ FNDDS (Cooking Yields R2 **0 dòng thuỷ sản**) | [nhật ký đo](docs/06-nutrition/ACCURACY-SPEC.md) |
| **Code** | ✅ **Laravel 13.29 + MySQL 8.0** — 19 migration (đủ R1–**R10**), engine calo **1.1.0**, **trang quản trị antd 6 + React 19 tại `/admin`**, **trang người dùng hoàn chỉnh** (Lắc + lọc theo nhãn + tài khoản + TDEE + nhật ký + đóng góp), **115 test Pest xanh** | `php artisan migrate:fresh --seed; vendor\bin\pest --no-coverage` |
| **CI** | 🔵 `.github/workflows/tests.yml` (GitHub Actions) — repo nay ở **GitHub** nên đã đúng nền; **chưa chạy lần nào vì code chưa push** (đang chờ chủ dự án cấp credential push) | `git remote -v` |
| Chưa có | cột embedding (REC-002), Foundation Foods, rà soát độc lập ING-003, tích hợp LLM trong trang quản trị (ADM-006 — chờ chủ dự án chốt nhà cung cấp/API key) | [ROADMAP](docs/05-tasks/ROADMAP.md) |
| Sao lưu | 🔵 `origin` = `https://github.com/tsnam1110/shake-hungry.git`, branch `dev`; nhánh local đang ahead origin nhiều commit (toàn bộ đợt 2026-10-30/31) — **push chờ chủ dự án cấp credential** (token hoặc khoá SSH) | `git status -sb` |

> Việc tiếp theo theo thứ tự ưu tiên: **(1) đẩy code lên GitHub** (đã duyệt, chờ
> credential); **(2) chốt phương án go-live** (VPS + subdomain / Laravel Cloud / PC +
> Tailscale Funnel — chủ dự án quyết định 2026-10-31 **chưa go-live**, hoàn thiện sản phẩm
> trước); **(3) nhóm chiên sau QA-002 — ĐÃ XONG 2026-10-31**: chủ dự án duyệt làm cả hai bước
> tiếp (tham chiếu chiên độc lập + rà soát CALC-004): lần 3 đạt **98,1%** (ĐẠT mục tiêu
> ≥80%), hiệu chuẩn thịt/đạm 8→4 và củ/quả 12→9, lần 4 đạt **98,8%** (nhóm chiên 95,0%);
> phần mở còn lại chỉ là hồ sơ "hải sản chiên trần" chờ thêm neo độc lập — không chặn
> việc khác;
> **(4) hệ số hao hụt cho thuỷ sản từ nguồn độc lập với USDA** (Q7 — nhóm
> hấp lệch −21%); **(5) cân 16 hệ số phần ăn được** (ưu tiên sườn lợn); **(6) DISH-004**
> mở rộng lên 500 món theo nhu cầu (đã đạt mốc 500; sau gộp tầm soát còn 496). Việc chờ
> chủ dự án: **go-live**, **ADM-006**
> (chọn nhà cung cấp/API key cho LLM trong admin), **tắt nút đăng nhập nhanh admin**
> (đang bật trên môi trường dev kèm cảnh báo bảo mật).
>
> ✅ **[LEGAL-002](docs/05-tasks/tasks/LEGAL-002.md) đã chốt 2026-10-31:** mã nguồn theo
> **MIT** (file `LICENSE` ở gốc repo); dữ liệu dạng **collective database** — phần tự biên
> soạn **CC BY 4.0**, các dòng có nguồn Open Food Facts giữ **ODbL 1.0** + ghi công.
>
> ⚠️ **Chưa được tuyên bố "độ chính xác >80%" ra ngoài.** 89,7% là trên món Mỹ của FNDDS với ánh
> xạ do USDA cung cấp sẵn, không có nhóm chiên — không phải trên kho món Việt. Soát bộ mẫu ngày
> 2026-09-01 còn cho thấy: **bỏ qua 39% Food Code**, và **45% bộ mẫu là món sống** (`song`) tức
> không có chuyển hoá do nấu. Nói cho đúng: 89,7% là bằng chứng engine **cộng đúng**, chưa phải
> bằng chứng engine **tính đúng món nấu**. Xem ACCURACY-SPEC §6.

Bảng trạng thái tổng quan — **bắt buộc cập nhật mỗi khi đóng một giai đoạn**:

| Giai đoạn | Nội dung | Trạng thái | Ngày đóng |
|---|---|---|---|
| GĐ0 | Thủ tục pháp lý + chốt định nghĩa + khởi tạo repo | 🔵 IN PROGRESS — kỹ thuật xong (SETUP-001/002 ✅), LEGAL-002 ✅ (2026-10-31), Q1 ✅ chốt "Lắc Đói" (2026-10-31) | — |
| GĐ1 | Xương sống dữ liệu: schema + bảng nguyên liệu + engine calo | ✅ DONE — schema/engine/SR Legacy/validate/export xong; bảng nguyên liệu vượt mốc (386 file) | 2026-10-31 |
| GĐ2 | Trình nhập liệu nội bộ (mục tiêu: 5 phút/món) | 🔵 IN PROGRESS — lõi xong (ADM-001…005 ✅, admin antd chạy thật); còn ADM-006 (LLM, chờ API key) + ADM-007 (đo giờ nhập) | — |
| GĐ3 | Sản xuất dữ liệu: 150 → 500 món | 🔵 IN PROGRESS — **đã đạt mốc 500 (DISH-004), sau gộp tầm soát còn 496 món; DISH-005 đợt 1 (canh + rau) → 556 món, đợt 2 (mặn nhanh + lõi Trung) → 621 món, đợt 3 (chay + sáng) → 656 món — DISH-005 đóng đủ quota; +1 món hoãn sau hiệu chuẩn đậu phụ CALC-004 → **657 món**; **LV2-001 đợt 1: 47/50 món nền tảng lên Mức 2 thật; LV2-002 đợt 2: +50/50 — tổng 97 món Mức 2; LV2-003 đợt 3: +37/50 (13 giữ Mức 1 có lý do) — tổng 134 món Mức 2; LV2-004 đợt 4: +50/50 — tổng 184 món Mức 2; LV2-005 đợt 5 (đợt chốt): Phần A phân xử bất thường LV2-004 + 32 món — **tổng 216 món Mức 2; CHƯƠNG TRÌNH MỨC 2 ĐỢT 1 HOÀN TẤT, vượt mốc 200** (chi tiết + danh sách quyết định còn chờ: docs/05-tasks/tasks/LV2-005.md §4)**; **LV2-006 đợt 6 — đợt đầu chương trình mở rộng toàn kho: Phần A phân xử bất thường LV2-005 + 39/50 món — tổng 256 món Mức 2 (11 giữ Mức 1 có hồ sơ)**; **LV2-007 đợt 7: Phần A phân xử bất thường LV2-006 + hàng đợi nguyên liệu (rau đắng/trân châu có số; mắm cá/heo quay giữ null; bun-mam/lau-mam vẫn chờ) + 45/50 món — tổng 301 món Mức 2 (5 giữ Mức 1 có hồ sơ), kho 420 nguyên liệu; quyết định mới chờ tại LV2-007 §3**; **LV2-008 đợt 8: Phần A phân xử bất thường LV2-007 (cha-bong vẫn null sau tra nhãn — xoi-man giữ Mức 1; hung-lui có số USDA + bổ sung goi-xoai; khép 2 ca chờ) + 45/60 món xét — tổng 346 món Mức 2 (15 giữ Mức 1 có hồ sơ: 5×429, bun-nuoc-leo trần mắm cá null, ca-bong-om-chuoi chờ xác nhận có thật), kho 421 nguyên liệu; bất thường mới chờ tại LV2-008 §3**; **LV2-009 Phần A: phân xử §3 LV2-008 (4 slug thiếu biên soạn đều null có hồ sơ + bổ sung/thay dòng ở 3 món) — kho 425 nguyên liệu; LV2-009 Phần B đợt 9: 27/55 món xét đạt + bổ sung 4/5 — tổng 377/657 Mức 2, còn 280 Mức 1**; **LV2-010: Phần A phân xử bất thường LV2-009 + 8 slug hàng đợi — kho 433 nguyên liệu; Phần B đợt 10: 23/50 món đạt — TỔNG 400/657 Mức 2, còn 257 Mức 1**; **LV2-010 Phần C (gói quyết định A1–A4): xoá 3 món không xác nhận được tồn tại → kho 654 món; +6 món nâng Mức 2 (canh-ba-roi-nau-dau, banh-bo, bau-xao-tep, 3 món rang muối) — TỔNG 406/654 Mức 2, còn 248 Mức 1; 434 nguyên liệu (thêm bot-rang-muoi null); quy ước cấp kho đã áp (rau xào/cá kho/lượng sợi/mong-gio)**; **LV2-011: Phần A rà §B.3 của LV2-010 — không đổi dữ liệu (chờ quyết/chuyển Phần B); Phần B đợt 11: 15/50 món đạt — TỔNG 421/654 Mức 2, còn 233 Mức 1**; **LV2-012 đợt vét cuối: xét toàn bộ 233 món Mức 1 còn lại — 54 món đạt — TỔNG 475/654 Mức 2; CHƯƠNG TRÌNH MỞ RỘNG MỨC 2 ĐÓNG, 179 món giữ Mức 1 có hồ sơ (chi tiết LV2-012.md)**; QA-002 đã đo lại 2026-10-31 (lần 3: **98,1% — ĐẠT**, lần 4 sau hiệu chuẩn CALC-004: **98,8%**) + duyệt 361 công thức mới | — |
| GĐ4 | Engine gợi ý + nút Lắc bản chính thức | ✅ DONE — REC-001 + UI-002/003 chạy thật ở trang chủ | 2026-10-31 |
| GĐ5 | Hồ sơ người dùng + TDEE + lịch sử bữa ăn | ✅ DONE — USR-001…004 chạy thật | 2026-10-31 |
| Nhánh hoa quả | Hồ sơ quả: mùa vụ + giá theo miền/mùa + vùng trồng + kết hợp thực phẩm; admin riêng `/admin/hoa-qua`; trang người dùng `/hoa-qua` | ✅ XONG (2026-10-31) — FRUIT-001…006: 82 hồ sơ, 1.387 dòng giá có nguồn, admin + trang người dùng + khối trang chủ | — |
| GĐ6 | Đóng góp cộng đồng + lớp ngũ hành (tuỳ chọn) | 🔵 IN PROGRESS — đóng góp cộng đồng ✅ (CTR-001); lớp ngũ hành ⬜ (TCM-001…003, tuỳ chọn) | — |

---

## 1. COMMANDS — LỆNH THƯỜNG DÙNG

> Môi trường dev là **Laragon trên Windows (PHP/Node native trên host), KHÔNG dùng Docker.**
> Chạy `php`, `composer`, `npm` trực tiếp trên host qua PowerShell.
> Shell mặc định của Claude Code trong repo này là PowerShell — `&&` không dùng được, dùng `;` hoặc
> `if ($?) { }`.

```bash
php artisan migrate                     # chạy migration
php artisan migrate:fresh --seed        # reset DB + nạp lại toàn bộ dữ liệu từ data/ (XOÁ DATA)
php artisan db:seed --class=IngredientSeeder
php artisan lacdoi:import-usda          # nạp USDA FoodData Central vào bảng tham chiếu
php artisan lacdoi:recompute-calories   # tính lại calo toàn bộ món sau khi sửa nguyên liệu
php artisan lacdoi:export-data          # xuất DB ngược ra data/*.json (xem mục 4)
php artisan lacdoi:validate-data        # kiểm tra toàn vẹn dữ liệu V1-V16, chạy trước mỗi commit dữ liệu
php artisan lacdoi:accuracy-run --dataset=fndds   # đo lại độ chính xác engine (TEST-002)
vendor\bin\pest --no-coverage           # chạy test
```

> ✅ **Cập nhật 2026-08-31:** cả 6 lệnh `lacdoi:*` đã tồn tại và chạy được — `import-usda`
> (`--source=sr_legacy|foundation|cooking_yields|fndds`), `import-wikidata`, `validate-data`,
> `export-data`, `recompute-calories`, `accuracy-run` (`--dataset=fndds|nutrition5k`).
> `import-usda` cần file gốc trong `storage/imports/` — xem [DEPLOYMENT §4](docs/10-deployment/DEPLOYMENT.md).
> Sau mỗi phiên sửa dữ liệu trong DB: `lacdoi:export-data` rồi commit (QT-3).

---

## 2. DỰ ÁN NÀY LÀ GÌ

**Lắc Đói** là ứng dụng gợi ý món ăn. Người dùng bấm một nút, hệ thống gợi ý món nên ăn, có tính đến:
quốc gia/vùng miền, ăn ngoài hay tự nấu, bữa trong ngày, món đã ăn bữa trước, và hồ sơ thể trạng
của người dùng.

**Nhưng về bản chất kỹ thuật, đây KHÔNG phải một dự án web app. Đây là một dự án biên soạn cơ sở dữ
liệu, có kèm một web app ở đầu ra.**

Hiểu sai điều này là cách chắc chắn nhất để dự án chết. Cụ thể:

- Nút Lắc và engine gợi ý là **khoảng 60–200 dòng code**. Nó không phải phần khó, và không phải lợi
  thế cạnh tranh. Khảo sát mã nguồn mở (2026-08-31) cho thấy toàn bộ danh mục "meal recommender" là
  đồ án đã chết; bản triển khai nghiêm túc duy nhất (trong Mealie) chỉ là vài chục dòng luật + query
  random có seed.
- Thứ duy nhất tạo ra giá trị là **dữ liệu món ăn và nguyên liệu do chính dự án biên soạn**, cùng
  **engine tính calo** dựa trên dữ liệu đó.
- Vì vậy thứ tự ưu tiên là: **nguyên liệu → engine calo → công cụ nhập liệu → dữ liệu món → nút Lắc.**
  Không được đảo thứ tự này để "làm cái vui trước".

Chi tiết: [PRD](docs/01-prd/PRD.md).

---

## 3. TÁM QUY TẮC TUYỆT ĐỐI

Vi phạm bất kỳ quy tắc nào dưới đây là lỗi nghiêm trọng, không phải "khác biệt về phong cách".

### QT-1 — Không bao giờ để LLM sinh ra con số dinh dưỡng

LLM **được phép** làm đúng ba việc: (a) đề xuất ánh xạ nguyên liệu sang mã trong bảng tham chiếu,
(b) đề xuất định lượng gram cho nguyên liệu trong công thức, (c) dịch và chuẩn hoá tên món.

LLM **bị cấm** sinh ra giá trị kcal, protein, lipid, glucid, hay bất kỳ con số dinh dưỡng nào. Những
con số đó **chỉ được tính bằng hàm thuần tuý** từ bảng thành phần thực phẩm đã nhập. Nếu một nguyên
liệu chưa ánh xạ được, món đó **không có calo** và hiển thị "chưa đủ dữ liệu" — tuyệt đối không lấp
chỗ trống bằng phỏng đoán.

Mọi bản ghi do LLM đề xuất phải mang `source = 'llm_suggested'` và `needs_review = true`, và phải
được người duyệt trước khi hiển thị ra ngoài. Chi tiết: [DATA-PIPELINE](docs/04-data-sources/DATA-PIPELINE.md).

### QT-2 — Không đưa dữ liệu có license bẩn vào dự án

Trước khi nhập bất kỳ nguồn dữ liệu mới nào, **bắt buộc** đối chiếu
[LICENSE-COMPLIANCE](docs/04-data-sources/LICENSE-COMPLIANCE.md).

Danh sách cấm tuyệt đối (đã xác minh 2026-08-31): **RecipeNLG, Recipe1M/Recipe1M+, mọi bản dump
Food.com và Epicurious trên Kaggle, Web Data Commons schema.org Recipe, các bảng FAO/INFOODS (NC),
30VNFoods.** Không nhập, kể cả "chỉ để tham khảo nội bộ", vì một khi đã nằm trong repo thì đã là phân
phối lại.

Không fork **Tandoor** (AGPL + Commons Clause — không phải mã nguồn mở), **Mealie / KitchenOwl /
Nextcloud Cookbook** (AGPL-3.0, copyleft mạng), **RecipeSage** (phi thương mại + CLA).

### QT-3 — Mọi tài nguyên phải nằm trong repo

Dữ liệu món ăn, nguyên liệu, bảng tham chiếu, ảnh — **tất cả phải được version trong thư mục `data/`
dưới dạng file**. Database chỉ là bản dựng lại từ file, **không phải nguồn sự thật**.

Tiêu chí nghiệm thu: clone repo trên một máy trắng, chạy `php artisan migrate:fresh --seed`, phải ra
đúng y hệt dữ liệu của máy gốc, không thiếu một món, một ảnh, một nguyên liệu nào. Chi tiết và lý do:
[DATA-REPOSITORY](docs/04-data-sources/DATA-REPOSITORY.md).

### QT-4 — Một bảng thành phần thực phẩm duy nhất cho mỗi phép tính

Không trộn USDA với bảng Việt Nam với Open Food Facts trong cùng một món. Phân tích tổng hợp 14
nghiên cứu cho thấy sai số của app dinh dưỡng sụt từ **−202 kcal/ngày xuống −57 kcal/ngày và độ phân
tán về 0** khi dùng cùng một bảng tham chiếu. Phần lớn sai số nằm ở **ánh xạ sai bảng**, không phải ở
phép tính. Mỗi món ghi rõ `fct_source` mình dùng. Chi tiết: [CALORIE-ENGINE](docs/06-nutrition/CALORIE-ENGINE.md).

### QT-5 — Không hiển thị con số chính xác giả

Calo món ăn ngoài hiển thị dạng **khoảng** kèm độ tin cậy ("≈ 400–500 kcal · tin cậy: thấp"), không
bao giờ hiển thị "437 kcal". Nhu cầu năng lượng của người dùng cũng hiển thị dạng khoảng — công thức
Mifflin–St Jeor chỉ đúng trong ±10% ở 82% người không béo phì và ~70% người béo phì.

Mọi giá trị dinh dưỡng bắt buộc đi kèm ba trường: `value_min`, `value_max`, `confidence`.

### QT-6 — Ngũ hành / âm dương là lớp phụ, gán ở tầng nguyên liệu

Không gán nhãn tính vị trực tiếp vào món. Gán ở **nguyên liệu**, rồi tổng hợp lên món theo trọng số
khối lượng cộng hệ số chế biến. Mỗi nhãn bắt buộc có `source` và `confidence` vì phân loại này
**không được chuẩn hoá** cho thực phẩm thông thường.

Diễn đạt ở khung **văn hoá ẩm thực**, không phải khung **tư vấn sức khoẻ**. Cấm mọi câu dạng "thể
trạng bạn mất cân bằng, nên tránh món này". Chi tiết và lý do pháp lý:
[NGU-HANH-AM-DUONG](docs/08-cultural/NGU-HANH-AM-DUONG.md).

### QT-7 — Cập nhật tài liệu trong cùng commit với code

Đổi schema mà không sửa [DATA-MODEL](docs/03-data-model/DATA-MODEL.md) là lỗi. Đóng task mà không
cập nhật [ROADMAP](docs/05-tasks/ROADMAP.md) là lỗi. Phát hiện tài liệu cũ ghi sai thì **không xoá
lặng lẽ** — chèn khối `> ⚠️ **Đính chính YYYY-MM-DD**` giải thích chỗ sai và vì sao, theo đúng cách
`edu-crm-v2` đang làm. Người kế nhiệm cần biết dự án đã từng hiểu sai chỗ nào.

### QT-8 — Phân tích kỹ yêu cầu và kiểm tra điều kiện ngoại vi trước khi thực thi

Không bắt tay vào làm ngay khi nhận yêu cầu. Trước khi thực thi, **bắt buộc** làm đủ bốn việc:

1. **Phân tích yêu cầu:** mục tiêu thật là gì, phạm vi tới đâu, tiêu chí nghiệm thu là gì, chỗ nào
   còn chưa rõ. Chỗ chưa rõ mà ảnh hưởng kết quả thì phải làm rõ hoặc nêu giả định rõ ràng —
   không tự suy diễn rồi làm theo suy diễn đó.
2. **Kiểm tra mức phù hợp với dự án:** đối chiếu yêu cầu với QT-1…QT-7, kiến trúc đã chốt,
   [DATA-MODEL](docs/03-data-model/DATA-MODEL.md), quy định license và [ROADMAP](docs/05-tasks/ROADMAP.md)
   (giai đoạn hiện tại, task phụ thuộc). Yêu cầu mâu thuẫn với bất kỳ điểm nào thì **nói rõ chỗ
   mâu thuẫn trước khi làm**, theo quy định ở đầu file này — không âm thầm làm khác.
3. **Kiểm tra điều kiện ngoại vi:** dữ liệu đầu vào đã có chưa, môi trường / database / công cụ đã
   sẵn sàng chưa, việc này tác động gì lên dữ liệu, tài liệu và test hiện có, có đảo ngược được
   không, có rủi ro bảo mật hay pháp lý nào không.
4. **Trình phân tích trước khi làm khi việc lớn:** nếu yêu cầu thuộc diện khó đảo ngược, đổi hướng /
   đổi kiến trúc / đổi công nghệ, hoặc có nhiều phương án khả thi, phải trình bày phân tích —
   các phương án, đánh đổi, khuyến nghị — và **chờ chủ dự án chốt** rồi mới triển khai.
   Không tự ý chọn thay chủ dự án.

Mức độ phân tích **tỉ lệ với rủi ro**: việc nhỏ, hiển nhiên và dễ đảo ngược thì kiểm tra nhanh
bốn điểm trên là đủ; không dùng QT-8 làm lý do trì hoãn việc hiển nhiên. Chỉ bắt đầu thực thi
khi các điều kiện đã rõ, hoặc phần chưa rõ / rủi ro đã được chủ dự án chấp thuận.

---

## 4. KIẾN TRÚC TỔNG QUAN

### Stack đã chốt

| Lớp | Công nghệ | Lý do |
|---|---|---|
| Backend | **Laravel 12+ (PHP 8.3+)** | Hợp môi trường Laragon sẵn có |
| Database | **MySQL 8.0+** | Khớp mờ tên: trigram ở tầng PHP + FULLTEXT ngram; tìm không dấu/sắp xếp tiếng Việt: collation `utf8mb4_vi_0900_ai_ci`; vector: hoãn tới GĐ4, tính lại khi đó |
| Admin / nhập liệu | **Ant Design (antd) 6 + React 19** | Đây là công cụ sản xuất dữ liệu chính, không phải phần phụ. Quyết định của chủ dự án 2026-10-30 — xem ADM-001 và Đính chính lần 5 |
| Frontend người dùng | Blade + Livewire (v1) | Giữ đơn giản; tách SPA chỉ khi thực sự cần |
| Trợ lý nhập liệu | Claude API | Chỉ đóng vai trò ở QT-1 |

> **Không cần Python.** Đã cân nhắc và loại bỏ ở 2026-08-31: `ingredient-parser-nlp` và
> `recipe-scrapers` đều **chỉ hỗ trợ tiếng Anh** và không có site Việt Nam, nên lợi thế hệ sinh thái
> Python biến mất với phạm vi v1 (món Việt, tự biên soạn). Nhập USDA chỉ là đọc CSV. Lý do đầy đủ:
> [ARCHITECTURE §3](docs/02-architecture/ARCHITECTURE.md).

### Cấu trúc thư mục

```
shake-hungry/
├── CLAUDE.md              File này — quy tắc bắt buộc
├── AGENTS.md              Trỏ về CLAUDE.md (cho agent không đọc CLAUDE.md)
├── README.md              Giới thiệu dự án
├── app/                   Laravel application code
├── database/
│   ├── migrations/
│   └── seeders/           Seeder ĐỌC TỪ data/, không hardcode dữ liệu
├── data/                  ⭐ NGUỒN SỰ THẬT CỦA DỮ LIỆU (xem QT-3)
│   ├── reference/         Bảng tham chiếu nhập từ nguồn ngoài (USDA, Wikidata...)
│   ├── ingredients/       Nguyên liệu do dự án biên soạn
│   ├── dishes/            Món ăn do dự án biên soạn
│   └── media/             Ảnh món ăn
└── docs/                  Tài liệu — nguồn sự thật về thiết kế
```

---

## 5. BẢN ĐỒ TÀI LIỆU

Đọc theo thứ tự này khi tiếp nhận dự án:

| # | Tài liệu | Trả lời câu hỏi |
|---|---|---|
| 1 | [docs/01-prd/PRD.md](docs/01-prd/PRD.md) | Dự án làm gì, cho ai, phạm vi tới đâu |
| 2 | [docs/02-architecture/ARCHITECTURE.md](docs/02-architecture/ARCHITECTURE.md) | Hệ thống ghép từ những khối nào, vì sao chọn vậy |
| 3 | [docs/03-data-model/DATA-MODEL.md](docs/03-data-model/DATA-MODEL.md) | Bảng nào, cột nào, quan hệ ra sao |
| 4 | [docs/04-data-sources/DATA-SOURCES.md](docs/04-data-sources/DATA-SOURCES.md) | Dữ liệu lấy từ đâu |
| 5 | [docs/04-data-sources/LICENSE-COMPLIANCE.md](docs/04-data-sources/LICENSE-COMPLIANCE.md) | Được dùng nguồn nào, cấm nguồn nào |
| 6 | [docs/04-data-sources/DATA-REPOSITORY.md](docs/04-data-sources/DATA-REPOSITORY.md) | Dữ liệu lưu ở đâu trong repo, định dạng gì |
| 7 | [docs/04-data-sources/DATA-PIPELINE.md](docs/04-data-sources/DATA-PIPELINE.md) | Quy trình nhập một món từ đầu đến cuối |
| 8 | [docs/06-nutrition/CALORIE-ENGINE.md](docs/06-nutrition/CALORIE-ENGINE.md) | Calo được tính bằng công thức nào |
| 9 | [docs/06-nutrition/ACCURACY-SPEC.md](docs/06-nutrition/ACCURACY-SPEC.md) | "Chính xác >80%" nghĩa là gì, đo thế nào |
| 10 | [docs/07-recommendation/RECOMMENDATION-ENGINE.md](docs/07-recommendation/RECOMMENDATION-ENGINE.md) | Nút Lắc chọn món theo logic nào |
| 11 | [docs/08-cultural/NGU-HANH-AM-DUONG.md](docs/08-cultural/NGU-HANH-AM-DUONG.md) | Lớp âm dương/ngũ hành thiết kế ra sao |
| 12 | [docs/05-tasks/ROADMAP.md](docs/05-tasks/ROADMAP.md) | Đã làm gì, đang làm gì, làm gì tiếp |
| 13 | [docs/05-tasks/DEFINITION-OF-DONE.md](docs/05-tasks/DEFINITION-OF-DONE.md) | Thế nào là xong |
| 14 | [docs/09-standards/RESEARCH-PROTOCOL.md](docs/09-standards/RESEARCH-PROTOCOL.md) | **Tra cứu dữ liệu ở đâu, thế nào** — chính sách nền, thứ tự nguồn, endpoint, bẫy kỹ thuật |
| 15 | [docs/09-standards/EDITORIAL-GUIDE.md](docs/09-standards/EDITORIAL-GUIDE.md) | Chuẩn biên soạn dữ liệu: đặt tên món, phân biệt biến thể, định lượng |
| 16 | [docs/09-standards/CODING-STANDARDS.md](docs/09-standards/CODING-STANDARDS.md) | Quy ước đặt tên, cấu trúc code |
| 17 | [docs/10-deployment/DEPLOYMENT.md](docs/10-deployment/DEPLOYMENT.md) | Dựng lại dự án trên máy mới |

---

## 6. QUY TRÌNH LÀM VIỆC

### Khi nhận một task

1. **Phân tích trước theo QT-8:** làm rõ mục tiêu / phạm vi / tiêu chí nghiệm thu; đối chiếu
   QT-1…QT-8, kiến trúc và dữ liệu hiện có; kiểm tra điều kiện ngoại vi (phụ thuộc, môi trường,
   dữ liệu đầu vào, rủi ro). Có mâu thuẫn, hoặc task thuộc diện lớn / đổi hướng → trình phân
   tích và chờ chủ dự án chốt trước khi bắt đầu.
2. Đọc [ROADMAP](docs/05-tasks/ROADMAP.md), tìm mã task.
3. Mở file task tại `docs/05-tasks/tasks/<MÃ>.md`. Nếu chưa có, copy
   [TASK-TEMPLATE](docs/05-tasks/TASK-TEMPLATE.md).
4. Kiểm tra cột **Phụ thuộc** — task phụ thuộc chưa DONE thì không được bắt đầu.
5. Làm. Đối chiếu [DEFINITION-OF-DONE](docs/05-tasks/DEFINITION-OF-DONE.md).
6. Cập nhật status trong **cả** file task **và** bảng ở ROADMAP. Ghi ngày hoàn thành.
7. Nếu phát sinh quyết định thiết kế → ghi vào mục "Ghi chú" của task **và** vào tài liệu tương ứng.

### Khi thêm dữ liệu

1. Sửa file trong `data/`, **không sửa thẳng vào database**.
2. Chạy `php artisan lacdoi:validate-data`.
3. Chạy `php artisan migrate:fresh --seed` để xác nhận dựng lại được từ file.
4. Commit file `data/` cùng commit.

### Khi bí / thiếu thông tin

Không đoán. Ghi câu hỏi vào mục "Ghi chú" của task và vào [ROADMAP](docs/05-tasks/ROADMAP.md) mục
"Câu hỏi mở", rồi làm phần không phụ thuộc câu trả lời đó trước.

---

## 7. NHỮNG SAI LẦM ĐÃ ĐƯỢC CẢNH BÁO TRƯỚC

Ghi lại để người kế nhiệm không lặp lại. Đây là kết luận từ khảo sát ngày 2026-08-31, không phải suy đoán.

| Sai lầm | Vì sao chết | Làm đúng |
|---|---|---|
| Làm nút Lắc trước, dữ liệu sau | DB rỗng → app vô dụng → không ai dùng → mất động lực → dự án chết | Nguyên liệu → engine calo → công cụ nhập → dữ liệu → nút Lắc |
| Nhập dữ liệu theo **món** | Tra dinh dưỡng lại từ đầu 400 lần, số liệu mâu thuẫn nhau | Biên soạn ~250 **nguyên liệu** trước; món sau đó tự tính được |
| Bỏ qua công cụ nhập liệu, "nhập tay cho nhanh" | 15 phút/món × 500 món = 125 giờ → bỏ cuộc ở món thứ 80 | Đầu tư GĐ2 tới khi đạt **≤5 phút/món** rồi mới sản xuất hàng loạt |
| Fork Tandoor/Mealie cho nhanh | Tandoor có Commons Clause (không phải OSS); Mealie AGPL kéo theo cả app | Đọc thiết kế, tự viết lại. Chỉ dùng thư viện MIT/Apache/CC0 |
| Dùng RecipeNLG vì có 2,2 triệu công thức | Điều khoản phi thương mại, **ràng buộc cả công ty của người dùng** | Xem [LICENSE-COMPLIANCE](docs/04-data-sources/LICENSE-COMPLIANCE.md) |
| Tải PDF bảng thành phần thực phẩm VN về nhập luôn | Sách có bản quyền của Viện Dinh dưỡng; tải được ≠ được phép dùng | Tra cứu **từng nguyên liệu** khi nguồn chính thống không có (tầng 6, DATA-PIPELINE §7.2) kèm trích dẫn + cờ chưa kiểm định; LEGAL-001 (xin phép) đã huỷ |
| Để LLM điền calo cho nhanh | Phá thẳng mục tiêu chính của dự án, và không phát hiện được về sau | QT-1 |
| Lưu dữ liệu chỉ trong database | Máy khác clone về là mất sạch | QT-3 |
