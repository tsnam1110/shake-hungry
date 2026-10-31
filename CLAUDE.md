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

**Trạng thái thực tế, kiểm ngày 2026-10-31 (đã chạy lại toàn bộ lệnh kiểm chứng):**

| Hạng mục | Thực tế | Kiểm bằng |
|---|---|---|
| Tài liệu | ✅ 23 tài liệu trong `docs/` + 52 task file | `find docs -name "*.md" -not -path "*/tasks/*" \| wc -l` |
| **Dữ liệu nguyên liệu** | ✅ **333 file**: **306 có số dinh dưỡng có nguồn**, 27 để trống có hồ sơ lý do (13 nguyên liệu gốc tra đủ 3 vòng không có bảng đạt chuẩn + các mục phát sinh sau, mới nhất: mắc khén, hạt dổi, rau nhút). Nguyên liệu gốc: **242/255 (94,9%) có số**. ⚠️ File nguyên liệu vẫn giữ `needs_review` — duyệt nguyên liệu là trục riêng, chưa duyệt (khác với món, xem dòng dưới) | `php artisan lacdoi:validate-data` |
| Dữ liệu món ăn | ✅ **500 file đầy đủ công thức**, kèm hệ nhãn phân loại (TAXONOMY.md); **495/500 tính được dinh dưỡng** (5 món còn lại vướng thiếu dữ liệu thật: gỏi ngó sen, canh rau nhút, mắm nêm ×2, mắm ruột). **300 công thức đầu đã được chủ dự án duyệt ngày 2026-10-31**; 200 công thức của DISH-004 đang chờ duyệt (`needs_review=true`) | `ls data/dishes/vn` |
| Bảng tham chiếu | ✅ SR Legacy **7.793** + Cooking Yields R2 **163** + FNDDS **333 Food Code / 1.810 SR Links** + `regions-vn.json` + `wikidata-vn-dishes.json`; **nguyên liệu gốc có số 242/255 (94,9%)** | `data/reference/MANIFEST.json` |
| Hệ số DATA-004/008 | ✅ khung xong: `edible_portion_pct` (3 có nguồn, 16 null chờ cân), `default_yield_factor` (9 thịt suy có nguồn) | `edible_portion_note` / `default_yield_factor_note` từng file |
| **Kiểm chứng engine** | 🔵 lần 2 (2026-09-01, engine **1.1.0**): **89,7% trong ±20%** (không đổi), MAPE 3,9%, và **% tham chiếu nằm trong khoảng 1,0% → 80,4%**. Hệ số hấp thụ dầu đã có theo nhóm (CALC-004, áp 2026-10-31) nên **nhóm chiên đã tính được**; vẫn chặn: **QA-002 chưa chạy** (chưa đo trên món Việt) và **nhóm hấp lệch −21%** (Cooking Yields R2 **0 dòng thuỷ sản**) | [nhật ký đo](docs/06-nutrition/ACCURACY-SPEC.md) |
| **Code** | ✅ **Laravel 13.29 + MySQL 8.0** — 19 migration (đủ R1–**R10**), engine calo **1.1.0**, **trang quản trị antd 6 + React 19 tại `/admin`**, **trang người dùng hoàn chỉnh** (Lắc + lọc theo nhãn + tài khoản + TDEE + nhật ký + đóng góp), **85 test Pest xanh** | `php artisan migrate:fresh --seed; vendor\bin\pest --no-coverage` |
| **CI** | 🔵 `.github/workflows/tests.yml` (GitHub Actions) — repo nay ở **GitHub** nên đã đúng nền; **chưa chạy lần nào vì code chưa push** (đang chờ chủ dự án cấp credential push) | `git remote -v` |
| Chưa có | cột embedding (REC-002), Foundation Foods, rà soát độc lập ING-003, tích hợp LLM trong trang quản trị (ADM-006 — chờ chủ dự án chốt nhà cung cấp/API key) | [ROADMAP](docs/05-tasks/ROADMAP.md) |
| Sao lưu | 🔵 `origin` = `https://github.com/tsnam1110/shake-hungry.git`, branch `dev`; nhánh local đang ahead origin nhiều commit (toàn bộ đợt 2026-10-30/31) — **push chờ chủ dự án cấp credential** (token hoặc khoá SSH) | `git status -sb` |

> Việc tiếp theo theo thứ tự ưu tiên: **(1) đẩy code lên GitHub** (đã duyệt, chờ
> credential); **(2) chốt phương án go-live** (VPS + subdomain / Laravel Cloud / PC +
> Tailscale Funnel — chờ chủ dự án chọn); **(3) QA-002** — đo độ chính xác trên món Việt
> khi đã đủ mẫu; **(4) hệ số hao hụt cho thuỷ sản từ nguồn độc lập với USDA** (Q7 — nhóm
> hấp lệch −21%); **(5) cân 16 hệ số phần ăn được** (ưu tiên sườn lợn); **(6) DISH-004**
> mở rộng lên 500 món theo nhu cầu. Việc chờ chủ dự án: **Q1**, **go-live**, **ADM-006**
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
| GĐ0 | Thủ tục pháp lý + chốt định nghĩa + khởi tạo repo | 🔵 IN PROGRESS — kỹ thuật xong (SETUP-001/002 ✅), LEGAL-002 ✅ (2026-10-31); còn Q1 chờ chủ dự án | — |
| GĐ1 | Xương sống dữ liệu: schema + bảng nguyên liệu + engine calo | ✅ DONE — schema/engine/SR Legacy/validate/export xong; bảng nguyên liệu vượt mốc (333 file) | 2026-10-31 |
| GĐ2 | Trình nhập liệu nội bộ (mục tiêu: 5 phút/món) | 🔵 IN PROGRESS — lõi xong (ADM-001…005 ✅, admin antd chạy thật); còn ADM-006 (LLM, chờ API key) + ADM-007 (đo giờ nhập) | — |
| GĐ3 | Sản xuất dữ liệu: 150 → 500 món | 🔵 IN PROGRESS — **đã đạt 500 món (DISH-004)**; còn QA-002 đo độ chính xác + duyệt 200 công thức mới | — |
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
