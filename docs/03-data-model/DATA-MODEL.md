# Mô hình dữ liệu — Lắc Đói

**Cập nhật lần cuối:** 2026-09-01 — thêm dấu vết duyệt + ràng buộc R10 ([ING-004](../05-tasks/tasks/ING-004.md))
**Trạng thái:** ✅ **ĐÃ HIỆN THỰC** — 19 migration trong `database/migrations/`, 10 ràng buộc
R1–R10 có test ở `tests/Feature/IntegrityConstraintsTest.php`.

Khi hiện thực, migration phải khớp tài liệu này. Nếu thực tế buộc phải khác, **sửa tài liệu trong
cùng commit** (QT-7) và ghi lý do.

> ## ⚠️ Đính chính 2026-08-31 — 5 chỗ đặc tả lệch với dữ liệu thật, phát hiện khi seed 150 file
>
> 1. **`ingredient_units.unit_name` varchar(32) → varchar(64).** Dữ liệu thật có tên đơn vị
>    34 ký tự ("phần ăn được từ 1 lb gà nguyên con"). `dish_ingredients.unit_name` nới theo.
> 2. **`ingredient_units.grams` numeric(8,2) → numeric(8,3).** Hạt sen khô 0,675 g/hạt bị
>    numeric(8,2) làm tròn thành 0,68 — phát hiện nhờ so sánh ngữ nghĩa khi kiểm DATA-007.
> 3. **`ingredient_units.source` varchar(16) → text.** Dữ liệu thật ghi nguồn dạng
>    "FDC 174277 foodMeasures" (23 ký tự) và dài hơn.
> 4. **`ingredients.category`** thực tế có thêm `trai-cay` và `rau-gia-vi` so với danh mục trong
>    DATA-REPOSITORY §3 (bảng CHECK trong migration là danh sách đúng: `thit`, `thuy-san`,
>    `rau-cu`, `rau-gia-vi`, `gia-vi`, `tinh-bot`, `dau-mo`, `trai-cay`, `khac`).
> 5. **Bảng `dishes` thiếu cột `estimated_kcal json`** trong khi định dạng file món
>    (DATA-REPOSITORY §3) đã có — cột đã thêm, chứa `{min, max, confidence, source_note}`
>    cho món ăn ngoài Mức 1.
>
> Ngoài ra: cột `embedding` cho gợi ý món tương tự hoãn có ý thức tới GĐ4, migration
> chưa tạo — xem [REC-002](../05-tasks/tasks/REC-002.md).

> ## Bổ sung 2026-08-31 (lần 2) — DATA-004 và DATA-008
>
> **`ingredients` thêm 3 cột:**
> - `edible_portion_pct` numeric(5,2) null + `edible_portion_note` text — % phần ăn được
>   của khối lượng mua ở chợ (cả xương/vỏ). CHECK: có pct thì bắt buộc có note (nguồn
>   foodMeasures hoặc phép cân). Engine áp **trước** khi tra dinh dưỡng (CALORIE-ENGINE §3b).
> - `default_yield_factor_note` text — có `default_yield_factor` thì bắt buộc ghi suy từ
>   dòng nào của bảng USDA Cooking Yields R2 (CHECK tương tự `density_note`).
>
> **`dish_ingredients` thêm cột** `is_edible_weight` boolean default false — quy ước
> [EDITORIAL-GUIDE §11](../09-standards/EDITORIAL-GUIDE.md): mặc định `quantity` là khối
> lượng mua ở chợ; `true` = đã là phần ăn được, engine không nhân hệ số nữa.
>
> **Bảng mới `cooking_yields`** (DATA-004): hệ số hao hụt khi nấu từ *USDA Table of Cooking
> Yields for Meat and Poultry, Release 2* — khoá tra (`ndb_number` → `external_id` fdc SR
> Legacy, `preparation_method`), kèm `yield_pct`, min/max, thay đổi ẩm/béo. Nạp từ
> `data/reference/usda-cooking-yields.csv` bởi `CookingYieldSeeder`; 163 dòng.
> Bảng này là dữ liệu THAM CHIẾU chỉ đọc, cùng nhóm với `reference_foods`.

---

## 0. Quy ước

- Bảng: `snake_case`, số nhiều (`dishes`, `ingredients`).
- Cột: `snake_case`, **không dùng prefix theo tên bảng**.
  > Lý do: dự án `edu-crm-v2` dùng prefix (`brh_`, `cls_`) nhưng áp dụng không đều, dẫn tới tài liệu
  > ghi sai tên cột trong nhiều tháng. Dự án này bỏ hẳn prefix để không tái diễn.
- Khoá chính: `id` (`bigint tự tăng`).
- Thời gian: `timestamp`, luôn lưu UTC.
- Enum: dùng bảng tra cứu hoặc `varchar` + `CHECK`, **không** dùng kiểu `ENUM` (khó
  migrate).
- Mọi bảng dữ liệu biên soạn có: `source`, `confidence`, `needs_review`, `created_at`, `updated_at`.
- `ingredients` và `dishes` có thêm **dấu vết duyệt**: `reviewed_by`, `reviewed_at`, `review_note`
  (bổ sung 2026-09-01, [ING-004](../05-tasks/tasks/ING-004.md)). Mặc định của `needs_review` ở hai
  bảng này là **`true`** — chưa duyệt là trạng thái mặc định, ra khỏi nó phải khai báo tường minh.
- Bảng `fruits` cũng có bộ ba `needs_review` / `reviewed_by` / `reviewed_at` (từ migration tạo bảng
  2026-10-31), nhưng `reviewed_at` của quả là kiểu `date` (chính xác tới ngày) trong khi món/nguyên
  liệu là `timestamp` — bất đối xứng đã biết, không đổi.

### Quy ước ngữ nghĩa `needs_review` (chuẩn hoá T-08, 2026-11-02)

Rà 3 tầng file ↔ DB ↔ admin ngày 2026-11-02 (chi tiết: [T-08-T12-BACKLOG](../05-tasks/tasks/T-08-T12-BACKLOG.md)):

1. **Một ngữ nghĩa duy nhất cho cả 3 loại** (món / nguyên liệu / quả): `needs_review = true` nghĩa là
   *bản ghi chưa qua duyệt biên tập của người theo từng bản ghi*. Cờ chỉ được hạ qua thao tác duyệt
   của trang quản trị (ghi `reviewed_by` + `reviewed_at`, ràng buộc R10 ở món/nguyên liệu; quả áp
   cùng kỷ luật qua controller dù migration tạo bảng không có CHECK R10). Không hạ cờ bằng sửa file
   tay, không hạ hàng loạt bằng script.
2. **Kiểm chứng chương trình ≠ duyệt.** Các đợt census/audit (AUDIT-002, AUDIT-003, AUDIT-ALLERGEN…)
   là kiểm chứng ở cấp chương trình và được truy vết bằng file task — chúng **không** hạ cờ
   `needs_review` của từng bản ghi và không được trình bày như "đã duyệt".
3. **Số đếm hàng đợi phải báo theo từng loại, không gộp.** Tại 2026-11-02: món **358/654** (hàng đợi
   sống thật — 296 món đã duyệt qua admin), nguyên liệu **434/434** và quả **82/82** (hai hàng đợi
   *chưa bắt đầu* duyệt theo bản ghi; mọi nguyên liệu/quả đều đã qua kiểm chứng chương trình nhưng
   chưa bản ghi nào được người duyệt riêng). Dashboard admin vốn đã trả số theo từng loại
   (`pending_review` riêng cho ingredients/dishes/fruits) — giữ nguyên cách báo đó, cấm cộng gộp
   thành một con số "chờ duyệt" duy nhất trong báo cáo.
4. **Duyệt món theo gói rủi ro:** hàng đợi món được chia gói ~25 món theo tầng R1 (Mức 1 + Mức 2
   (có note) nâng bù) → R2 (Mức 3) → R3 (Mức 2 thường), lưu tại thư mục bàn giao của chủ dự án
   (`goi-duyet/`). Việc duyệt cuối từng món vẫn là của người duyệt trong admin.

### Bảng `source` — dùng chung toàn hệ thống

| Giá trị | Nghĩa | Được phép chứa số dinh dưỡng? |
|---|---|---|
| `imported` | Nhập tự động từ nguồn ngoài (USDA, Wikidata) | ✅ |
| `curated` | Người biên soạn nhập/duyệt thủ công | ✅ |
| `computed` | Máy tính ra từ dữ liệu khác | ✅ |
| `llm_suggested` | LLM đề xuất, **chưa duyệt** | ❌ **Cấm** (QT-1) |
| `community` | Người dùng đóng góp, chưa duyệt | ❌ chưa duyệt thì không hiển thị |

### Bảng `confidence`

| Giá trị | Nghĩa | Cách hiển thị |
|---|---|---|
| `high` | Tính từ nguyên liệu đã ánh xạ đầy đủ, cùng một bảng thành phần | Số kèm khoảng hẹp |
| `medium` | Tính được nhưng có nguyên liệu ước lượng hoặc chế biến không rõ | Khoảng rộng |
| `low` | Ước lượng từ thông tin công bố hoặc món tương tự (điển hình: món ăn ngoài) | Khoảng rộng + nhãn "ước lượng" |
| `unknown` | Không đủ dữ liệu | **Không hiển thị số nào** |

---

## 1. Sơ đồ quan hệ

```
regions ──┐
          │
countries─┴──< dishes >──┬──< dish_names
                         ├──< dish_meal_slots
                         ├──< dish_ingredients >──── ingredients ──┬── reference_foods
                         ├──< dish_nutrition                       ├──< ingredient_units
                         ├──< dish_media                           └──< ingredient_tcm_traits
                         ├──< dish_tags >──── tags
                         └──< dish_tcm_summary

users ──┬──< user_profiles
        ├──< meal_logs >──── dishes
        └──< user_preferences

contributions (hàng đợi duyệt, GĐ6)
```

---

## 2. Nhóm bảng tham chiếu (chỉ đọc)

### `reference_foods`

Thành phần thực phẩm nhập từ nguồn ngoài. **Không sửa tay.** Nạp lại được bằng artisan command.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `id` | bigint tự tăng | |
| `fct_source` | varchar(32) | `usda_foundation`, `usda_sr_legacy`, `usda_fndds`, `vn_nin`, `off` |
| `external_id` | varchar(64) | Mã gốc ở nguồn (vd `fdc_id`) |
| `name_en` | text | |
| `name_vi` | text | nullable — dịch ở tầng biên soạn |
| `kcal_per_100g` | numeric(8,2) | |
| `protein_g` / `fat_g` / `carb_g` / `fiber_g` | numeric(8,2) | trên 100 g |
| `micronutrients` | json | các vi chất khác, giữ nguyên tên nguồn |
| `raw_payload` | json | bản gốc, để truy nguyên |
| `imported_at` | timestamp | |

**Ràng buộc:** `UNIQUE (fct_source, external_id)`.

> **Cảnh báo QT-4:** không được join dữ liệu từ hai `fct_source` khác nhau trong cùng một phép tính
> của một món. Món ghi rõ mình dùng nguồn nào ở `dish_nutrition.fct_source`.

### `regions`

Phân cấp địa lý, đệ quy.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `id` | bigint tự tăng | |
| `parent_id` | bigint null | tự tham chiếu |
| `country_code` | char(2) | ISO 3166-1 alpha-2 |
| `code` | varchar(32) | `vn-north`, `vn-central`, `vn-south`, `vn-hue`… |
| `name` | text | |
| `level` | varchar(16) | `country` / `macro_region` / `province` |

> **DATA-010 (2026-10-31):** `dishes.region_id` chỉ còn trỏ tới mức `country`
> hoặc `macro_region` (3 miền chính Bắc/Trung/Nam) — theo quyết định của chủ
> dự án. Món gắn với một tỉnh/thành cụ thể giữ thông tin đó bằng nhãn phân
> loại `dac-san:<tỉnh>` (facet biên tập trong TAXONOMY.md), không qua
> `region_id`. Các dòng `province` vẫn ở trong bảng làm danh mục tham chiếu
> cho facet đặc sản; API vùng miền công khai và form nhập món chỉ trả về
> `country` + `macro_region`, và API quản trị chặn gán region cấp tỉnh cho món.

---

## 3. Nhóm nguyên liệu — nền móng hệ thống

### `ingredients`

Khoảng 250 bản ghi phủ gần hết món Việt thường ngày. **Đây là tầng tốn công nhất và cũng có đòn bẩy
lớn nhất** — làm xong thì món tự tính được calo.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `id` | bigint tự tăng | |
| `slug` | varchar(64) | `nuoc-mam`, `thit-ba-chi` — dùng làm tên file trong `data/ingredients/` |
| `name_vi` | text | |
| `name_en` | text null | |
| `reference_food_id` | bigint null | trỏ `reference_foods` — **nguồn dinh dưỡng duy nhất** |
| `mapping_confidence` | varchar(8) | độ tin cậy của ánh xạ, không phải của số liệu |
| `mapping_note` | text null | vì sao chọn dòng tham chiếu này (bắt buộc khi `mapping_confidence` ≠ `high`) |
| `category` | varchar(32) | `thit`, `ca`, `rau`, `gia-vi`, `tinh-bot`… |
| `density_g_per_ml` | numeric(6,3) null | để quy đổi thể tích → khối lượng |
| `default_yield_factor` | numeric(4,3) | hệ số hao hụt mặc định khi nấu (xem CALORIE-ENGINE §3) |
| `oil_absorption_pct` | numeric(5,2) null | % dầu hấp thụ khi chiên (xem CALORIE-ENGINE §4) |
| `is_ready_to_eat` | boolean | `true` = đã chín/ăn liền (giò lụa, lạp xưởng, cơm) → engine **không** áp hệ số hao hụt |
| `density_note` | text null | **Bắt buộc khi có `density_g_per_ml`** — ghi rõ đo được hay suy ra, suy từ đâu |
| `source_url` | text null | URL đã truy cập — bắt buộc khi có số liệu |
| `scientific_name` | text null | Tên khoa học, để không nhầm loài |
| `nutrition_snapshot` | json null | Bản chụp dinh dưỡng lúc tra cứu — xem §3.1 |
| `alternatives` | json | Các dòng ứng viên đã bị loại, kèm lý do |
| `secondary_sources` | json | Nguồn bổ sung (nhãn sản phẩm VN) |
| `source` / `confidence` / `needs_review` | | |

#### 3.1 Vì sao có `nutrition_snapshot` — quyết định 2026-08-31

> **Đính chính:** bản đầu của tài liệu này khẳng định `reference_food_id` là **nguồn dinh dưỡng duy
> nhất** của nguyên liệu. Thực tế đã đi khác: 55 file nguyên liệu đầu tiên **lưu thẳng giá trị dinh
> dưỡng vào file**, vì `data/reference/` còn trống và USDA chưa được nhập hàng loạt.

Đây là mâu thuẫn thật giữa thiết kế và thực tế, giải quyết như sau:

| | Vai trò |
|---|---|
| `reference_foods` | **Nguồn sự thật lâu dài.** Khi DATA-002/003 xong, đây là nơi engine tra |
| `ingredients.nutrition_snapshot` | **Bản chụp lúc tra cứu**, kèm `external_id` + `source_url`. Cho phép biên soạn dữ liệu **trước khi** có bảng tham chiếu |

**Quy tắc bắt buộc khi DATA-002 hoàn tất:** chạy đối chiếu snapshot với `reference_foods`.
- Khớp ⇒ engine dùng `reference_foods`, snapshot giữ lại làm dấu vết.
- **Lệch ⇒ dừng và điều tra**, không âm thầm ghi đè. Lệch nghĩa là một trong hai sai, và biết cái nào
  sai quan trọng hơn là có một con số đẹp.

Snapshot **không được** dùng làm nguồn tính calo khi `reference_foods` đã có dữ liệu tương ứng —
nếu không sẽ có hai nguồn sự thật, đúng thứ QT-4 cấm.

**Ràng buộc:** `UNIQUE (slug)`. Index FULLTEXT ngram trên `name_vi` để tìm kiếm và cảnh báo trùng.

> **Vì sao `reference_food_id` là nullable:** nhiều nguyên liệu Việt (nước mắm, bánh phở, giò lụa,
> rau muống) **không có trong USDA**. Chúng sẽ null cho tới khi có dữ liệu Viện Dinh dưỡng
> ([LEGAL-001](../05-tasks/tasks/LEGAL-001.md)) hoặc được nhập thủ công có nguồn. Nguyên liệu null
> làm món chứa nó **không tính được calo** — đó là hành vi đúng theo QT-1, không phải lỗi.

### `ingredient_units`

Quy đổi đơn vị đời thường sang gram. Không có bảng này thì công thức "2 muỗng canh nước mắm" vô nghĩa.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `ingredient_id` | bigint | |
| `unit_name` | varchar(32) | `muỗng canh`, `muỗng cà phê`, `chén`, `quả`, `lát`, `bó` |
| `grams` | numeric(8,2) | |
| `note` | text null | vd "quả cỡ vừa ~150 g" |
| `source` | varchar(16) | phải ghi rõ đo được hay tra được ở đâu |

### `ingredient_aliases` — tên gọi khác của nguyên liệu (ALIAS-001, 2026-10-31)

Nhiều nguyên liệu/quả có tên gọi khác theo vùng miền (dứa thơm = thơm = khóm; roi = mận ở miền Nam). Tên chính giữ nguyên ở `ingredients.name_vi`; tên gọi khác nằm ở bảng riêng mô phỏng `dish_names`, để tìm kiếm tra được theo mọi tên:

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `ingredient_id` | bigint | |
| `name` | varchar(255) | tên gọi khác, vd `Khóm` |
| `note` | varchar(255) null | nhãn vùng miền, vd `miền Nam`, `miền Trung, miền Nam`, `tên cũ` |

Quy ước chung cho cả món và nguyên liệu (validator **V18**): tên gọi khác không trùng tên chính, không trùng nhau trong cùng một món/nguyên liệu; chỉ ghi tên gọi khác **có thật** — cùng một đối tượng, khác tên theo vùng miền/thời kỳ, không gộp món/nguyên liệu chỉ tương tự. Trong file dữ liệu: nguyên liệu dùng khoá `aliases: [{name, note?}]`; món dùng dòng `names[]` với `is_alias: true` và `note` tuỳ chọn. Mọi bề mặt tìm kiếm (danh mục món, API nguyên liệu, quản trị, trang hoa quả) đều tra cả tên gọi khác.

### `ingredient_tcm_traits` (GĐ6, tuỳ chọn)

Tính vị theo y học cổ truyền. **Gán ở tầng nguyên liệu, không phải tầng món** (QT-6).

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `ingredient_id` | bigint | |
| `nature` | varchar(8) | `han`, `luong`, `binh`, `on`, `nhiet` |
| `flavor` | varchar(8) | `toan`, `kho`, `cam`, `tan`, `ham`, `dam` |
| `element` | varchar(8) | `moc`, `hoa`, `tho`, `kim`, `thuy` |
| `element_basis` | varchar(8) | TCM-001a: căn cứ của `element` — `flavor` (mặc định, suy từ vị) \| `nature` (suy từ tính) \| `direct` (nguồn ghi trực tiếp). Chỉ dòng `flavor` bỏ phiếu `dominant_element` của món |
| `source_ref` | text | **bắt buộc** — tên tài liệu/URL. Không nguồn thì không được nhập |
| `confidence` | varchar(8) | |
| `usage_restricted` | boolean | TCM-000: nguồn uy tín nhưng điều khoản hạn chế sử dụng/tái phân phối — dữ liệu vẫn lưu theo chính sách lưu trữ nội bộ, kèm ghi chú |
| `usage_note` | text null | **Bắt buộc khi `usage_restricted = true`** (CHECK `tcm_usage_note_check`) — phải chứa ghi chú "không được dùng" và lý do |
| `derived_from` | varchar(128) null | TCM-001c (chính sách **P-DERIVE**): slug của nguyên liệu gốc khi dòng này là nhãn **suy diễn** cho dạng chế biến cơ học đơn thành phần; null = nhãn nguồn trực tiếp. CHECK `tcm_derived_confidence_check`: có `derived_from` thì `confidence` phải là `low` |

**Cho phép nhiều dòng cho một nguyên liệu** khi các nguồn mâu thuẫn nhau — đó là chuyện bình thường
với phân loại này, và mâu thuẫn phải được hiển thị chứ không giấu đi.

Trong file dữ liệu, nhãn nằm ở khoá `tcm_traits` của file nguyên liệu (xem
[DATA-REPOSITORY §3](../04-data-sources/DATA-REPOSITORY.md)). `element` để null trong file
thì được suy từ `flavor` theo ánh xạ cổ điển khi tổng hợp/hiển thị; ghi tường minh mà lệch
ánh xạ đó thì validator V20 báo 🔴. Ngoại lệ có khai báo (TCM-001a): dòng mang
`element_basis = nature` thì `element` phải khớp ánh xạ theo **tính**; dòng
`element_basis = direct` thì `element` lấy đúng như nguồn ghi, V20 chỉ kiểm từ vựng.
Trong file, khoá `element_basis` chỉ xuất hiện khi khác mặc định `flavor`.

**Nhãn suy diễn P-DERIVE (TCM-001c):** dòng có `derived_from` gương đúng một dòng
nguồn thật của nguyên liệu gốc (nature/flavor/element/`element_basis` khớp nguyên
vẹn), `confidence` khoá ở `low`, `source_ref` bắt buộc chứa cụm `P-DERIVE` kèm
nguồn của nhãn gốc. V20 cấm: suy diễn chuỗi (gốc chỉ có nhãn suy diễn), suy diễn
cho nguyên liệu có `components` (đa thành phần/hỗn hợp), và lệch gương. Chính sách
đầy đủ: [NGU-HANH-AM-DUONG §9](../08-cultural/NGU-HANH-AM-DUONG.md). Trong file,
khoá `derived_from` chỉ xuất hiện ở dòng suy diễn.

### `ingredient_components` (TCM-000)

Liên kết thành phần của **nguyên liệu hỗn hợp** (bột rang muối, nước mắm pha…): người dùng
từ trang nguyên liệu bấm sang được từng thành phần để xem chi tiết. Nguồn sự thật là khoá
`components: [slug…]` trong file nguyên liệu; bảng này là bản dựng để truy vấn.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `ingredient_id` | bigint | nguyên liệu hỗn hợp |
| `component_ingredient_id` | bigint | nguyên liệu thành phần — FK restrict khi xoá, unique theo cặp, CHECK chống tự trỏ |

---

## 4. Nhóm món ăn

### `dishes`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `id` | bigint tự tăng | |
| `slug` | varchar(96) | tên file trong `data/dishes/` |
| `parent_dish_id` | bigint null | **null = món chuẩn; có giá trị = biến thể** |
| `country_code` | char(2) | |
| `region_id` | bigint null | |
| `is_eat_out` | boolean | có thể ăn ngoài |
| `is_home_cook` | boolean | có thể tự nấu — một món có thể cả hai |
| `cooking_method` | varchar(24) null | `luoc`, `hap`, `xao`, `chien`, `nuong`, `kho`, `nau`, `song` — **quyết định hệ số calo** |
| `completeness_level` | smallint | 1 / 2 / 3 — xem [PRD §7](../01-prd/PRD.md) |
| `level_basis` | varchar(24) null | TCM-000: căn cứ của mức khi không theo cổng thường — hiện chỉ có `tcm_compensation` (đường nâng bù: Mức 1 không đạt cổng Mức 2 nhưng đủ thông tin Mức 3 ⇒ "Mức 2 (có note)"). CHECK: chỉ đi với `completeness_level = 2` |
| `level_note` | text null | **Bắt buộc khi `level_basis` khác null** (CHECK) — ghi rõ cổng chưa đạt và căn cứ được nâng; hiển thị công khai cạnh nhãn mức |
| `cultural_note` | json null | TCM-000: ghi chú văn hoá của món — `{text, sources: [{ref, url}]}`; bắt buộc (kèm coverage ≥60%) cho cổng Mức 3 và đường nâng bù, do V20 kiểm |
| `recipe_steps` | json null | mảng bước nấu; null nếu chỉ Mức 1 |
| `import_note` | text null | ghi chú biên tập nội bộ (không hiển thị công khai) |
| `safety_note` | text null | cảnh báo an toàn thực phẩm ở tầng món — lớp THÔNG TIN, hiển thị trên `/mon/{slug}` (hiện chỉ `tiet-canh`: huyết tươi chưa nấu chín). Không phải cấm, không chặn/lọc. Thêm 2026-11-03 khi phát hiện ghi chú cũ nói "cảnh báo hiện có" nhưng thực tế chưa hiển thị gì |
| `serving_desc` | text null | "1 tô cỡ vừa" |
| `serving_grams` | numeric(8,2) null | khối lượng khẩu phần |
| `status` | varchar(16) | `draft`, `published`, `archived` |
| `source` / `confidence` / `needs_review` | | |
| `embedding` | vector(768) null | cho gợi ý món tương tự (GĐ4) |

**Index:** FULLTEXT ngram trên `slug`; chỉ mục cho `embedding` bổ sung ở GĐ4 cùng cột.

> **Vì sao tách `parent_dish_id` ngay từ đầu:** "Phở bò", "Phở bò tái", "phở bò nam định" là cùng một
> món chuẩn với ba biến thể. Nếu để phẳng, dữ liệu sẽ phình và nút Lắc sẽ gợi ý ba lần cùng một thứ.
> Quy tắc phân biệt: [EDITORIAL-GUIDE §3](../09-standards/EDITORIAL-GUIDE.md).

### `dish_names`

Đa ngôn ngữ + tên gọi khác. Không dùng cột `name_vi`/`name_en` cạnh nhau (xem ARCHITECTURE §7).

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `dish_id` | bigint | |
| `locale` | varchar(8) | `vi`, `en` |
| `name` | text | |
| `is_primary` | boolean | mỗi (dish, locale) đúng một dòng primary |
| `is_alias` | boolean | tên gọi dân gian khác — dùng cho tìm kiếm và chống trùng |
| `note` | varchar(255) null | nhãn vùng miền của tên gọi khác, vd `miền Nam`, `tên cũ` (bổ sung 2026-10-31, ALIAS-001) |

### `dish_meal_slots`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `dish_id` | bigint | |
| `slot` | varchar(8) | `sang`, `trua`, `chieu`, `toi`, `an-vat` |
| `weight` | smallint | 1–10, mức phù hợp với bữa đó |

Bảng nhiều-nhiều có trọng số, không phải boolean — vì "phở" hợp bữa sáng hơn bữa tối, chứ không phải
chỉ hợp/không hợp.

### `dish_ingredients`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `dish_id` | bigint | |
| `ingredient_id` | bigint | |
| `quantity` | numeric(8,2) | |
| `unit_name` | varchar(32) | phải khớp `ingredient_units.unit_name` hoặc là `g`/`ml` |
| `grams_resolved` | numeric(8,2) null | kết quả quy đổi — do máy tính, không nhập tay |
| `is_optional` | boolean | nguyên liệu tuỳ chọn — có thể có hoặc không, khi có thì **được ăn** |
| `is_discarded` | boolean | nguyên liệu **luôn có mặt nhưng bị vớt bỏ, không ăn** — engine tính khối lượng nhưng **không tính calo** |

> **Vì sao `is_discarded` khác `is_optional`** (phát hiện 2026-08-31 khi tra cứu sả): sả, lá chanh,
> hoa hồi, quế, thảo quả, gừng đập dập trong nồi phở — luôn có trong công thức, nhưng **bị vớt ra
> trước khi ăn**. Nếu đánh `is_optional` thì sai nghĩa (chúng không tuỳ chọn); nếu để bình thường thì
> engine cộng calo của thứ không ai ăn. Riêng sả là 99 kcal/100g — đủ để làm sai một tô phở.
| `note` | text null | |
| `source` / `confidence` | | `llm_suggested` cho tới khi người biên soạn duyệt |

### `dish_nutrition`

Kết quả tính. **Chỉ được ghi bởi engine calo, không bao giờ nhập tay.**

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `dish_id` | bigint | |
| `basis` | varchar(16) | `per_serving` hoặc `per_100g` |
| `kcal_min` / `kcal_max` | numeric(8,2) | **luôn là khoảng** (QT-5) |
| `protein_g` / `fat_g` / `carb_g` / `fiber_g` | numeric(8,2) null | |
| `confidence` | varchar(8) | |
| `fct_source` | varchar(32) | bảng thành phần đã dùng — QT-4 |
| `missing_ingredients` | json | danh sách nguyên liệu chưa ánh xạ; **không rỗng ⇒ `confidence` ≤ `medium`** |
| `unverified_ingredients` | json null | slug các nguyên liệu dùng **nguồn thứ cấp** (tầng 6) thực sự tham gia phép tính — nền của cờ "chưa kiểm định" hiển thị cho người dùng (migration `2026_10_31_100001`, `App\Nutrition\SourceTier`) |
| `computed_at` | timestamp | |
| `engine_version` | varchar(16) | để biết cần tính lại khi engine đổi |

**Ràng buộc CHECK bắt buộc:**
- `source <> 'llm_suggested'` — chốt chặn kỹ thuật cho QT-1.
- `kcal_min <= kcal_max`.

### `dish_recipe_sources`

Nguồn đã đọc để tổng hợp công thức. **Bắt buộc với món `completeness_level >= 2`.**

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `dish_id` | bigint | |
| `url` | text | Nguồn đã đọc |
| `accessed_at` | date | Ngày truy cập — trang web thay đổi |
| `note` | text | Nguồn này cho định lượng bao nhiêu người, khác nguồn khác ở chỗ nào |

> **Vì sao là bảng riêng chứ không phải `json`:** đây là danh sách có cấu trúc lặp, cần truy vấn
> được ("món nào chỉ có 1 nguồn?", "nguồn nào bị dùng nhiều nhất?"). `alternatives` của nguyên liệu
> để `json` vì nó là ghi chú tra cứu, không cần truy vấn.

**Ràng buộc R8:** `dishes.completeness_level >= 2` ⇒ tồn tại **≥3** dòng `dish_recipe_sources`.
Lý do 3 nguồn: [RESEARCH-PROTOCOL §11.2](../09-standards/RESEARCH-PROTOCOL.md).

### `dish_media`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `dish_id` | bigint | |
| `path` | text | đường dẫn tương đối trong `data/media/` — **không phải URL ngoài** (QT-3) |
| `license` | varchar(32) | `own`, `cc-by`, `cc0`… — bắt buộc |
| `attribution` | text null | bắt buộc khi license yêu cầu ghi công |
| `is_primary` | boolean | |

### `tags`, `dish_tags`, `ingredient_tags` — phân loại & nhãn (TAXONOMY.md)

Hệ nhãn theo **facet** (khía cạnh): slug dạng `facet:value`, từ vựng có kiểm soát
trong `config/taxonomy.php` — không nhãn tự do. Thiết kế đầy đủ, định nghĩa từng
nhãn và API lọc: xem `docs/03-data-model/TAXONOMY.md`.

| Bảng | Cột | Ghi chú |
|---|---|---|
| `tags` | `id`, `slug` (unique), `name` | 1 hàng/nhãn trong đăng ký |
| `dish_tags` | `dish_id`, `tag_id` | nhãn món: `loai:` ghi trong file món; `diet:`/`protein:`/`allergen:` do seeder SUY từ thành phần (`App\Taxonomy\DishTagDeriver`) |
| `ingredient_tags` | `ingredient_id`, `tag_id` | nhãn nguyên liệu ghi trong file, sinh bằng `App\Taxonomy\TaggingRules` |

Toàn vẹn nhãn được kiểm bởi validate-data **V16**: nhãn file lệch quy tắc sinh,
hoặc nhãn suy trong DB lệch phép suy lại từ thành phần, đều là lỗi 🔴.

### `dish_tcm_summary` (GĐ6)

Kết quả tổng hợp tính vị từ nguyên liệu lên món. Do máy tính, không nhập tay.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `dish_id` | bigint | |
| `nature_score` | numeric(4,2) | thang −2 (rất hàn) → +2 (rất nhiệt) |
| `dominant_element` | varchar(8) null | |
| `coverage_pct` | numeric(5,2) | % khối lượng nguyên liệu có nhãn tính vị |
| `confidence` | varchar(8) | `coverage_pct` < 60 ⇒ tối đa `low` |
| `has_conflicts` | boolean | TCM-000: có nguyên liệu trong món mang ≥2 nhãn `nature` mâu thuẫn — phải hiển thị, và `confidence` bị hạ xuống `low` |
| `computed_at` | timestamp | |

Do `App\Cultural\TcmAggregator` tính (lệnh `lacdoi:recompute-tcm`); quy ước tổng hợp đầy
đủ tại [NGU-HANH-AM-DUONG §8](../08-cultural/NGU-HANH-AM-DUONG.md). Món không có nguyên
liệu có nhãn nào thì **không có dòng** ở bảng này.

---

## 5. Nhóm người dùng

### `user_profiles`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `user_id` | bigint | |
| `height_cm` / `weight_kg` | numeric(5,1) | |
| `birth_year` | smallint | lưu năm sinh, không lưu tuổi (tuổi thay đổi) |
| `sex` | varchar(16) | `male`, `female`, `unspecified` — dùng cho công thức BMR. (`unspecified` dài 11 ký tự nên không dùng varchar(8) — xem SETUP-004) |
| `activity_level` | varchar(16) | ánh xạ sang PAL, xem CALORIE-ENGINE §5 |
| `diet_type` | varchar(24) null | `chay`, `chay-truong`, `low-carb`… |
| `excluded_ingredients` | json | dị ứng / không ăn được — **lọc cứng** ở engine gợi ý |
| `preferred_region_id` | bigint null | vùng miền yêu thích — điền sẵn bộ lọc vùng khi lắc (UI-011, migration 2026_10_31_100005) |
| `target_kcal_per_meal` | smallint unsigned null | kcal mục tiêu mỗi bữa — điền sẵn khi lắc, là đầu vào mềm của engine (UI-011) |
| `goal` | varchar(16) null | mục tiêu tuần ăn: `giam` / `duy-tri` / `tang` — chỉ lưu ở Task 1, chưa dùng để suy kcal vì tỉ lệ điều chỉnh kcal theo mục tiêu chưa được chốt (Giai đoạn 2, Phần 2.1, migration 2026_11_04_100001) |
| `target_protein_g_per_day` | smallint unsigned null | protein mục tiêu mỗi ngày do người dùng nhập/ghi đè; gợi ý tính bằng `App\Profile\WeekTargetSuggester` (1 g/lb tại BMI 25, giới hạn 35% kcal), không ghi đè tự động |
| `target_kcal_per_day` | smallint unsigned null | kcal mục tiêu mỗi ngày do người dùng nhập/ghi đè; null = chưa đặt |
| `shake_mode` | varchar(16) null | chế độ lắc mặc định: `eat_out` (1 món) / `home` (mâm cơm) — điền sẵn khi lắc (REC-003, migration 2026_10_31_100007) |
| `tray_dish_count` | tinyint unsigned null | số món trong mâm cơm 1–4, không tính cơm; null = mặc định 3 (REC-003) |
| `tray_binh_dan` | boolean | mâm cơm chỉ gồm món bình dân dễ nấu/nguyên liệu phổ biến (REC-004, migration 2026_10_31_100008) |
| `show_tcm` | boolean, mặc định true | hiển thị lớp tính vị ở trang món/nguyên liệu/quả; mặc định bật theo quyết định chủ dự án 2026-11-01, người dùng tự tắt trong hồ sơ (TCM-003, migration 2026_11_02_100001) — thuần hiển thị, không vào engine gợi ý |

> **Về `sex`:** công thức Mifflin–St Jeor có hệ số khác nhau theo giới tính sinh học. Khi người dùng
> chọn `unspecified`, dùng công thức Katch–McArdle nếu có % mỡ, nếu không thì lấy trung bình hai hệ
> số và **hạ `confidence` xuống `low`**, đồng thời nói rõ trên giao diện.

### `week_plans` / `week_plan_days` / `week_plan_meals` (Tuần ăn, Giai đoạn 2, Phần 2.2, migration 2026_11_04_210000)

| Bảng | Cột chính | Ghi chú |
|---|---|---|
| `week_plans` | `user_id`, `start_date`, `end_date` (= start + 6 ngày), `goal` / `target_protein_g_per_day` / `target_kcal_per_day` (snapshot từ hồ sơ lúc tạo tuần, null = chưa đặt), `status` (`active` / `archived`) | unique (`user_id`, `start_date`); đổi hồ sơ sau đó không sửa ngược tuần cũ |
| `week_plan_days` | `week_plan_id`, `date`, `protein_g`, `kcal_min`, `kcal_max`, `status` (`dat` / `thieu` / `chua-du-du-lieu`) | unique (`week_plan_id`, `date`); tổng là ảnh chụp, validator Phần 2.3 tính lại từ món để kiểm chứng |
| `week_plan_meals` | `week_plan_day_id`, `dish_id`, `slot` (`sang` / `trua` / `chieu` / `toi` / `an-vat`), `servings` (mặc định 1), `is_eat_out`, `replaced_from_dish_id` (null nếu do builder chọn) | unique (`week_plan_day_id`, `slot`) — đúng một món mỗi bữa; `dish_id` RESTRICT để không xoá món đang nằm trong tuần |

### `combinations` (FOOD-COMBINE-001)

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `slug` | varchar(96) unique | định danh cặp, khớp tên file `data/combinations/<slug>.json` |
| `side_a_*` / `side_b_*` | type varchar(16), ref varchar(96), label varchar(160) null, members json null | mỗi phía: `ingredient` / `fruit` (ref = slug kho) · `food_group` (label + members = các slug thành viên để gắn trang) · `drug_group` (chỉ label, không có trang riêng) |
| `category` | varchar(16) | `food_food` / `drug_food` |
| `evidence_class` | varchar(2) | `E1` / `E2` / `E3` — **tầng hiển thị suy ra từ đây bằng hàm thuần trên model, không lưu tay** |
| `science_status` | varchar(16) | `supported` (E1) / `conditional` (E2) / `unproven` / `refuted` (E3) — validator V21 ép khớp với class |
| `condition_vi` / `mechanism_vi` | text null | E2 bắt buộc có `condition_vi` (V21) |
| `traditional_source` | json null | E3 bắt buộc có (V21): `{text, quote}` — nguồn truyền thống có tên cụ thể |
| `sources` | json | nguồn đã mở kiểm chứng `{url, note, accessed}` — bắt buộc không rỗng |
| `needs_review` | boolean | duyệt nội dung của chủ dự án như mọi dữ kiện biên soạn (QT-1) |

> Bảng này là lớp THÔNG TIN thuần ("Kết hợp cần lưu ý") — không tham gia gợi ý, lọc
> hay chấm điểm ở bất kỳ đâu; tuyến thuốc × thực phẩm không gắn vào món/Lắc.
> Nguồn sự thật là file trong `data/combinations/` (QT-3), dựng lại qua `CombinationSeeder`.

### `vegetables` (RAU-MUA, 2026-11-03 — theo mẫu `fruits`)

Nguồn sự thật là file `data/vegetables/<slug>.json` (QT-3), dựng lại qua `VegetableSeeder`. Tháng là tháng có bán trên thị trường; vùng null = chưa có lịch chợ riêng; dạng chế biến luôn bán quanh năm nên luôn khớp lọc tháng.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `slug` | varchar unique | khớp tên file |
| `name_vi` | varchar | |
| `ingredient_id` | bigint null, FK nullOnDelete | null với rau nhập khẩu chưa có hồ sơ nguyên liệu |
| `group` | varchar | `rau-la` / `rau-cu` / `rau-qua` / `dau-do` / `rau-thom-an-kem` / `dang-che-bien` |
| `supply` | varchar | `trong-nuoc` / `da-lat` / `nhap-khau` / `ca-hai` (giống nhập trồng tại Đà Lạt + nhập thuần túy) |
| `state` | varchar | `tuoi` / `muoi` / `kho` / `chua` |
| `months_bac` / `months_trung` / `months_nam` | json | mảng tháng 1–12 hoặc null |
| `region_notes` | json | ghi chú vùng, gồm "hàng Đà Lạt" ở miền Nam |
| `main_season_months` / `best_months` | json | mảng tháng hoặc null — mục UNCERTAIN để trống, không bịa |
| `kcal_per_100g` / `kcal_confidence` | decimal null / varchar null | null thì UI hiện "Chưa đủ dữ liệu" |
| `aliases` | json | tên gọi khác, tìm kiếm cả alias |
| `selection_note` / `storage_note` / `cooking_note` / `safety_note` | text null | `safety_note` chỉ là ghi chú sơ chế/nấu chín, KHÔNG phải cặp cấm |
| `fresh_counterpart_slug` | varchar null | chỉ dạng chế biến (đối tác tươi) |
| `uncertain` / `uncertain_note` | boolean / text null | cờ "tháng chưa chắc chắn" |
| `source` / `source_note` | varchar / text null | catalogue rau theo mùa v2 (2026-11-03), phần nguồn |
| `needs_review` / `reviewed_by` / `reviewed_at` / `review_note` | boolean / varchar null / date null / text null | bộ ba duyệt như `fruits` |

### `drinks` (DRINK-MODULE, 2026-11-03 — mở rộng từ schema món, KHÔNG dùng `meal_slots`/`cooking_method` nguyên dạng)

Nguồn sự thật là file `data/drinks/<slug>.json` (QT-3), dựng lại qua `DrinkSeeder`. Hai kind hiển thị khác nhau, cấm lẫn: `pha-che` có công thức; `dong-goi` CHỈ khối "Thông tin nhãn" (seeder fail nếu có `recipe_steps`). Giao diện chi tiết `drink.blade.php` đồng nhất theo mẫu trang món từ 2026-11-03 (chỉ thị mới): link quay lại kiểu chevron, tiêu đề/phụ đề/hàng badge `ld-badge`, hộp cảnh báo `ld-warn` (18+, "Chưa chắc chắn"), khối dinh dưỡng "Năng lượng mỗi khẩu phần" đặt ngay sau đầu trang, tiêu đề mục `ld-section-title`, bước pha đánh số tròn như "Cách làm", kết hợp chỉ hiện khi có cặp; giữ nguyên đặc thù: badge 18+, khối "Thông tin nhãn" cho đóng gói, không hiện `recipe_steps` cho đóng gói. Trang nước uống không có ảnh (file drinks không có `media`) nên không có khối ảnh như trang món — đó là thiếu dữ liệu thật, không lấp bằng ảnh giả (QT-9).

Từ 2026-11-03 (hoàn thiện lõi nước uống): mỗi dòng nguyên liệu trong `ingredients` của file pha chế có thêm trường tùy chọn `ingredient_slug` trỏ tới hồ sơ trong `data/ingredients/` (cột `ingredients` đã là JSON nên không cần migration). `DrinkNutritionService` tính kcal pha chế bằng lại `NutritionCalculator` (fct `mixed`, phương pháp `song`, khẩu phần có cân): `g` trực tiếp, `ml` × `density_g_per_ml`, `quả`/`thìa cà phê`/`viên` qua bảng `ingredient_units` (alias `thìa cà phê`→`muỗng cà phê`, `viên`→`viên đường`); dòng garnish (lá/nhánh/dash/giọt, muối/tiêu viền), dòng nước/đá (= 0 kcal) và đầu vào pha (bột cà phê phin, túi trà lọc) bỏ qua có ghi chú; dòng ml/g không gắn slug hoặc có slug mà kcal null được đưa vào engine dạng input null-kcal để ngưỡng missing ≥30% có tác dụng (tránh số thiếu đội lốt số đủ, ví dụ Old Fashioned). **Không có logic ethanol riêng**: kcal tham chiếu USDA của rượu đã bao gồm năng lượng ethanol (6,93 kcal/g), nên rượu mạnh gắn slug tính đúng; rượu mùi/siro chỉ có số nhãn /100ml mà chưa đo density thì kcal để null và đồ uống giữ "chưa tính được" với lý do cụ thể. Đồ đóng gói không qua service này.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `slug` | varchar unique | khớp tên file |
| `name_vi` | varchar | |
| `drink_group` | varchar | pha chế: `tra` / `ca-phe` / `sinh-to` / `nuoc-ep` / `giai-khat` / `cocktail-co-con` / `mocktail`; đóng gói: `nuoc-ngot-co-ga` / `tra-dong-chai` / `ca-phe-dong-goi` / `nuoc-ep-dong-hop` / `sua-dong-hop` / `tang-luc-dien-giai` / `nuoc-dong-goi-khac` / `bia` / `vang` / `ruou` / `nuoc-giai-khat-dong-goi` |
| `kind` | varchar | `pha-che` / `dong-goi` |
| `temperature` | varchar null | `nong` / `lanh` / `ca-hai` |
| `glass` / `technique` / `ice` | varchar null | chỉ pha chế (technique: pha-phin / ham-tra / xay / ep / lac-shake / khuay-stir / pha-truc-tiep-build / nau-sam…) |
| `garnish` / `ingredients` / `recipe_steps` | json null | ingredients: `[{name, quantity, unit}]` (ml/g) — chỉ pha chế; đóng gói luôn rỗng |
| `serving_ml` | integer null | dung tích 1 ly (pha chế) hoặc dung tích tham chiếu (đóng gói) |
| `sugar_added_g` / `sugar_note` | decimal null / text null | đường thêm — chỉ pha chế; khoảng thì ghi số thấp nhất + ghi rõ khoảng vào note |
| `caffeine_level` | varchar null | `khong` / `thap` / `trung-binh` / `cao` — định tính; mg chỉ khi có `caffeine_mg_*` |
| `caffeine_mg_min` / `caffeine_mg_max` / `caffeine_mg_note` | decimal null / decimal null / text null | mg/ly, khoảng tầm soát vẫn ghi rõ tầng trong note, không ghi điểm đo giả; ví dụ bạc xỉu 25–70 mg/ly cho 25 ml phin (2026-11-03) |
| `liquid_ml` | integer null | tổng ml chất lỏng thật, không tính đá, không tính dung tích ly; mẫu số cho kcal/100 ml |
| `key_indicators` | json null | `[{label, value, value_min, value_max, unit, per_serving, basis, confidence, source_ref}]` — nguồn sự thật là file JSON, seeder chỉ sao chép; ô thứ tư lưới macro lấy từ đây khi không có cồn, không lấp bằng "Không cồn" |
| `alcohol` / `alcohol_pct_estimate` / `alcohol_note` | boolean / decimal null / text null | ước tính TRƯỚC pha loãng; mọi entry có cồn >0,5% gắn cờ 18+ |
| `safety_flags` | json null | `18+`, `khong-lai-xe`, `caffeine-cao`, `duong-cao`, `trung-song`, `di-ung`… |
| `difficulty` / `prep_minutes` / `best_occasion` / `food_pairing` | varchar null / integer null / varchar null / json null | `best_occasion` thay `meal_slots` cho đồ uống |
| `packaging` / `sugar_range_g` / `alcohol_pct_range` / `label_note` / `brand_examples` | varchar null / json null / json null / text null / json null | chỉ đóng gói; khoảng là khoảng theo loại, dao động theo thương hiệu; `label_note` bắt buộc "đọc nhãn sản phẩm cụ thể" |
| `mocktail_counterpart_slug` | varchar null | bản không cồn tương đương của cocktail |
| `uncertain` / `uncertain_note` | boolean / text null | cờ chưa chắc chắn (cà phê muối Huế, trà sữa nướng, rượu thủ công…) |
| `source_note` | text null | catalogue nước uống Việt Nam v2 (2026-11-03) |
| `needs_review` / `reviewed_by` / `reviewed_at` / `review_note` | boolean / varchar null / date null / text null | bộ ba duyệt như `fruits` |

> **Kcal đồ uống — chặn kỹ thuật đã chốt:** module này KHÔNG hiển thị kcal tính từ engine. Ethanol ~7 kcal/g không nằm trong protein/fat/carb của USDA, và phần lớn nguyên liệu bar chưa có trong kho — mọi entry đều trả `kcal_status = 'chua-tinh-duoc'` với ghi chú lý do. Không bao giờ hiện số thiếu như số đầy đủ. Cocktail không được gợi ý tự động trong Lắc món (chưa có lọc tuổi). Pháp lý quảng cáo rượu bia VN: chưa kết luận, cần kiểm tra trước khi public khối cocktail.

### `favorites` (UI-014)

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `user_id` | bigint | |
| `dish_id` / `fruit_id` | bigint null | đúng một trong hai có giá trị; unique theo (user_id, dish_id) và (user_id, fruit_id) |

### `fruit_logs` (UI-014)

Bản ghi mỗi lần người dùng ăn một quả — đối tác của `meal_logs` ở phía món ăn, dùng cho thống kê số lượng quả đã ăn: `user_id`, `fruit_id`, `eaten_at`, `source`.

### `feedback_tickets` (UI-018)

Ticket góp ý/phản hồi của người dùng đã đăng nhập: `user_id`, `type` (`gop-y` | `ho-tro` | `phat-trien` | `loi`), `title` (null), `content`, ngữ cảnh tuỳ chọn `dish_id` / `fruit_id` / `ingredient_id` + `page`, `status` (mặc định `da-ghi-nhan` — đã ghi nhận, chờ duyệt; sau đó `da-duyet` / `dang-xu-ly` / `da-xu-ly` / `da-dong` do quản trị đặt), `admin_note`.

### `meal_logs`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `user_id` | bigint | |
| `dish_id` | bigint | |
| `slot` | varchar(8) | |
| `eaten_at` | timestamp | |
| `source` | varchar(16) | `shake_accepted`, `manual` |

Đây là đầu vào cho hai chức năng: loại trừ món vừa ăn, và gợi ý dựa trên bữa trước.

### `contributions` (GĐ6)

Hàng đợi đóng góp từ người dùng. Ở v1 đây là **kênh phụ**, giữ đơn giản.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `id` | bigint tự tăng | |
| `type` | varchar(24) | `new_dish`, `fix_dish`, `new_place` |
| `payload` | json | nội dung đề xuất |
| `status` | varchar(16) | `pending`, `approved`, `rejected` |
| `reviewed_by` / `reviewed_at` / `reject_reason` | | |

Dữ liệu ở bảng này **không bao giờ hiển thị ra ngoài khi `status <> 'approved'`**, và khi được duyệt
thì được ghi sang `dishes`/`ingredients` với `source='curated'`, không phải `community`.

---

> ## ⚠️ Đính chính 2026-09-01 — `needs_review` từng mặc định SAI CHIỀU
>
> Migration đầu đặt `needs_review` mặc định **`false`** cho cả `ingredients` lẫn `dishes`. Nghĩa là
> mọi bản ghi tạo bằng đường không phải seeder — trang quản trị antd ở GĐ2, form đóng góp [CTR-001](../05-tasks/tasks/CTR-001.md),
> script nhập liệu — mặc nhiên được coi là **đã duyệt**. Đúng ngược với QT-1.
>
> Không ai phát hiện sớm vì `IngredientSeeder` truyền `?? true` tường minh, nên toàn bộ dữ liệu đi
> qua file đều đúng; lỗ hổng chỉ mở ra đúng lúc GĐ2 bắt đầu ghi thẳng vào database.
> Đã đảo mặc định thành `true` và thêm R10 trong cùng commit.

## 6. Ràng buộc toàn vẹn phải có

Những ràng buộc này là hiện thực kỹ thuật của các quy tắc tuyệt đối. Phải viết thành CHECK/trigger,
không chỉ kiểm tra ở tầng ứng dụng:

| # | Ràng buộc | Bảo vệ quy tắc |
|---|---|---|
| R1 | `dish_nutrition.source <> 'llm_suggested'` | QT-1 |
| R2 | `dish_nutrition.kcal_min <= kcal_max` | QT-5 |
| R3 | `missing_ingredients <> '[]'` ⇒ `confidence <> 'high'` | QT-5 |
| R4 | `dish_media.license` NOT NULL | QT-2 |
| R5 | `ingredient_tcm_traits.source_ref` NOT NULL | QT-6 |
| R6 | `dishes.completeness_level >= 2` ⇒ tồn tại ≥1 `dish_ingredients` | PRD §7 |
| R7 | `ingredients.mapping_confidence <> 'high'` ⇒ `mapping_note` NOT NULL | truy nguyên |
| R8 | `dishes.completeness_level >= 2` ⇒ có ≥3 dòng `dish_recipe_sources` — **ngoại lệ TCM-000:** món `level_basis = 'tcm_compensation'` được miễn (đạt Mức 2 bằng cổng thông tin Mức 3 do R11 kiểm thay); trigger đã tạo lại trong migration TCM-000 | truy nguyên công thức |
| R11 | `dishes.level_basis = 'tcm_compensation'` ⇒ `level_note` không rỗng (CHECK) **và** có `cultural_note` **và** `dish_tcm_summary.coverage_pct >= 60` (hai điều kiện sau do V20 kiểm ở tầng DB vì coverage là dữ liệu dẫn xuất) | đường nâng bù Mức 3 — chống nâng hình thức |
| R9 | `dish_ingredients.ingredient_id` phải trỏ tới nguyên liệu có thật | chống slug lỗi thời |
| R10 | `needs_review = false` ⇒ `reviewed_by` và `reviewed_at` NOT NULL (bảng `ingredients` và `dishes`) | QT-1 — duyệt phải có bằng chứng |

Test kiểm chứng các ràng buộc này: task [TEST-001](../05-tasks/tasks/TEST-001.md).
