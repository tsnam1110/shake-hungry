# CLAUDE.md — Lắc Đói (shake-hungry)

File hướng dẫn bắt buộc cho Claude Code và mọi AI agent làm việc trên repo này.

**AI PHẢI đọc hết file này trước khi thực hiện bất kỳ yêu cầu nào.** Nếu một yêu cầu mâu thuẫn với
quy tắc ở đây, phải nói ra chỗ mâu thuẫn trước khi làm, không âm thầm làm khác.

**Ngày tạo:** 2026-08-31
**Cập nhật lần cuối:** 2026-09-01 — soát toàn dự án; sửa hai lỗi engine/schema chưa ai bắt
(khoảng tin cậy rộng bằng 0 → engine 1.1.0; `needs_review` không có cơ chế duyệt → R10), 40 test xanh

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
>    mở đúng lúc GĐ2 cho Filament ghi thẳng vào database. Đã sửa: [ING-004](docs/05-tasks/tasks/ING-004.md).
> 2. Engine 1.0.0 trả **khoảng rộng bằng 0** cho món ánh xạ `high` — tức một con số điểm đội lốt
>    khoảng, đúng thứ **QT-5 cấm**, nằm sẵn trong engine từ CALC-001. Phép đo lần 1 đã in bằng chứng
>    ngay trên bảng chỉ số (*"% tham chiếu nằm trong khoảng: **1,0%**"*) nhưng không ai đọc, vì dòng
>    đầu tiên đã xanh. Đã sửa và hiệu chuẩn: [CALC-003](docs/05-tasks/tasks/CALC-003.md).
>
> Bài học chung của cả hai: **một cơ chế an toàn không có cách kiểm chứng thì không phải cơ chế an
> toàn.** Cờ `needs_review` không ai kiểm được đã hạ đúng chưa; khoảng min–max không ai kiểm được
> có thật sự chứa giá trị đúng không.

**Trạng thái thực tế, kiểm ngày 2026-09-01 (đã chạy lại toàn bộ lệnh kiểm chứng):**

| Hạng mục | Thực tế | Kiểm bằng |
|---|---|---|
| Tài liệu | ✅ 19 tài liệu trong `docs/` + 47 task file | `find docs -name "*.md" -not -path "*/tasks/*" \| wc -l` |
| **Dữ liệu nguyên liệu** | ✅ **255 file** (mốc 250 của GĐ1 đã đạt): 125 high / 64 medium / 28 low / **38 "không tra được"** có tài liệu. ⚠️ **255/255 đang `needs_review`** — nay đã có chỗ ghi kết quả duyệt (R10), còn chờ **người** | `php artisan lacdoi:validate-data` |
| Dữ liệu món ăn | 🔵 **61 file nháp** gieo mầm từ Wikidata (`status=draft`, vỏ rỗng: chưa vùng miền/bữa/nguyên liệu) — GĐ3 mới biên soạn thật | `ls data/dishes/vn` |
| Bảng tham chiếu | ✅ SR Legacy **7.793** + Cooking Yields R2 **163** + FNDDS **333 Food Code / 1.810 SR Links** + `regions-vn.json` + `wikidata-vn-dishes.json`; **197/255 nguyên liệu ánh xạ, 0 lệch** | `data/reference/MANIFEST.json` |
| Hệ số DATA-004/008 | ✅ khung xong: `edible_portion_pct` (3 có nguồn, 16 null chờ cân), `default_yield_factor` (9 thịt suy có nguồn) | `edible_portion_note` / `default_yield_factor_note` từng file |
| **Kiểm chứng engine** | 🔵 lần 2 (2026-09-01, engine **1.1.0**): **89,7% trong ±20%** (không đổi), MAPE 3,9%, và **% tham chiếu nằm trong khoảng 1,0% → 80,4%**. Vẫn chặn: **nhóm chiên chưa đo được** (0/255 có hệ số dầu) và **nhóm hấp lệch −21%** (Cooking Yields R2 **0 dòng thuỷ sản**) | [nhật ký đo](docs/06-nutrition/ACCURACY-SPEC.md) |
| **Code** | ✅ **Laravel 13.29 + PostgreSQL 17.11** — 15 migration (đủ R1–**R10**), 19 model, 7 seeder, engine calo **1.1.0**, 6 lệnh `lacdoi:*`, **40 test Pest xanh** | `php artisan migrate:fresh --seed; vendor\bin\pest --no-coverage` |
| **CI** | 🔵 `.github/workflows/tests.yml` viết xong, YAML hợp lệ, PSR-4 `--strict-psr` sạch, không có tên file đụng hoa/thường — nhưng **chưa chạy lần nào vì repo chưa có remote** (Q8) | `git remote -v` (trống) |
| Chưa có | pgvector (GĐ4), Filament (GĐ2), Foundation Foods, UI-001, rà soát độc lập ING-003 | [ROADMAP](docs/05-tasks/ROADMAP.md) |
| ⚠️ **Không có bản sao ngoài máy** | `git remote -v` trống. 255 nguyên liệu biên soạn tay + 20 tài liệu chỉ tồn tại trên một ổ đĩa | Q8 ở [ROADMAP](docs/05-tasks/ROADMAP.md) |

> Việc tiếp theo theo thứ tự ưu tiên: **(0) đẩy repo lên remote** — rẻ nhất, gỡ rủi ro mất trắng
> lớn nhất (Q8); **(1) hệ số hấp thụ dầu có nguồn** (chặn TEST-002, làm mọi món chiên VN hiển thị
> "chưa đủ dữ liệu"); **(2) hệ số hao hụt cho thuỷ sản từ nguồn ĐỘC LẬP với USDA** (Q7 — nhóm hấp
> lệch −21%); **(3) duyệt 255 nguyên liệu** (người — nay đã có chỗ ghi kết quả); **(4) cân 16 hệ số
> phần ăn được** (ưu tiên sườn lợn); rồi mở GĐ2 (ADM-001 Filament).
> Việc chờ chủ dự án: **LEGAL-002**, **Q1**, **Q6**, **Q7**, **Q8**.
>
> ⚠️ **Chưa được tuyên bố "độ chính xác >80%" ra ngoài.** 89,7% là trên món Mỹ của FNDDS với ánh
> xạ do USDA cung cấp sẵn, không có nhóm chiên — không phải trên kho món Việt. Soát bộ mẫu ngày
> 2026-09-01 còn cho thấy: **bỏ qua 39% Food Code**, và **45% bộ mẫu là món sống** (`song`) tức
> không có chuyển hoá do nấu. Nói cho đúng: 89,7% là bằng chứng engine **cộng đúng**, chưa phải
> bằng chứng engine **tính đúng món nấu**. Xem ACCURACY-SPEC §6.

Bảng trạng thái tổng quan — **bắt buộc cập nhật mỗi khi đóng một giai đoạn**:

| Giai đoạn | Nội dung | Trạng thái | Ngày đóng |
|---|---|---|---|
| GĐ0 | Thủ tục pháp lý + chốt định nghĩa + khởi tạo repo | 🔵 IN PROGRESS — kỹ thuật xong (SETUP-001/002 ✅); còn LEGAL-002 + Q1 chờ chủ dự án | — |
| GĐ1 | Xương sống dữ liệu: schema + bảng nguyên liệu + engine calo | 🔵 IN PROGRESS — schema ✅, engine ✅, SR Legacy ✅, validate/export ✅; còn 150/250 nguyên liệu, DATA-003/004/005/008, TEST-002 | — |
| GĐ2 | Trình nhập liệu nội bộ (mục tiêu: 5 phút/món) | ⬜ TODO | — |
| GĐ3 | Sản xuất dữ liệu: 150 → 500 món | ⬜ TODO | — |
| GĐ4 | Engine gợi ý + nút Lắc bản chính thức | ⬜ TODO | — |
| GĐ5 | Hồ sơ người dùng + TDEE + lịch sử bữa ăn | ⬜ TODO | — |
| GĐ6 | Đóng góp cộng đồng + lớp ngũ hành (tuỳ chọn) | ⬜ TODO | — |

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
php artisan lacdoi:validate-data        # kiểm tra toàn vẹn dữ liệu V1-V15, chạy trước mỗi commit dữ liệu
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

## 3. BẢY QUY TẮC TUYỆT ĐỐI

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

---

## 4. KIẾN TRÚC TỔNG QUAN

### Stack đã chốt

| Lớp | Công nghệ | Lý do |
|---|---|---|
| Backend | **Laravel 12+ (PHP 8.3+)** | Hợp môi trường Laragon sẵn có |
| Database | **PostgreSQL 16+** (KHÔNG dùng MySQL) | Cần `pg_trgm` (khớp mờ tên món tiếng Việt) và `pgvector` (gợi ý món tương tự) |
| Admin / nhập liệu | **Filament 3+** | Đây là công cụ sản xuất dữ liệu chính, không phải phần phụ |
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

1. Đọc [ROADMAP](docs/05-tasks/ROADMAP.md), tìm mã task.
2. Mở file task tại `docs/05-tasks/tasks/<MÃ>.md`. Nếu chưa có, copy
   [TASK-TEMPLATE](docs/05-tasks/TASK-TEMPLATE.md).
3. Kiểm tra cột **Phụ thuộc** — task phụ thuộc chưa DONE thì không được bắt đầu.
4. Làm. Đối chiếu [DEFINITION-OF-DONE](docs/05-tasks/DEFINITION-OF-DONE.md).
5. Cập nhật status trong **cả** file task **và** bảng ở ROADMAP. Ghi ngày hoàn thành.
6. Nếu phát sinh quyết định thiết kế → ghi vào mục "Ghi chú" của task **và** vào tài liệu tương ứng.

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
| Tải PDF bảng thành phần thực phẩm VN về nhập luôn | Sách có bản quyền của Viện Dinh dưỡng; tải được ≠ được phép dùng | Xin phép chính thức (task [LEGAL-001](docs/05-tasks/tasks/LEGAL-001.md)) |
| Để LLM điền calo cho nhanh | Phá thẳng mục tiêu chính của dự án, và không phát hiện được về sau | QT-1 |
| Lưu dữ liệu chỉ trong database | Máy khác clone về là mất sạch | QT-3 |
