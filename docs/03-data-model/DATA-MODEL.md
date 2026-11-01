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

**Cho phép nhiều dòng cho một nguyên liệu** khi các nguồn mâu thuẫn nhau — đó là chuyện bình thường
với phân loại này, và mâu thuẫn phải được hiển thị chứ không giấu đi.

Trong file dữ liệu, nhãn nằm ở khoá `tcm_traits` của file nguyên liệu (xem
[DATA-REPOSITORY §3](../04-data-sources/DATA-REPOSITORY.md)). `element` để null trong file
thì được suy từ `flavor` theo ánh xạ cổ điển khi tổng hợp/hiển thị; ghi tường minh mà lệch
ánh xạ đó thì validator V20 báo 🔴. Ngoại lệ có khai báo (TCM-001a): dòng mang
`element_basis = nature` thì `element` phải khớp ánh xạ theo **tính**; dòng
`element_basis = direct` thì `element` lấy đúng như nguồn ghi, V20 chỉ kiểm từ vựng.
Trong file, khoá `element_basis` chỉ xuất hiện khi khác mặc định `flavor`.

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
| `shake_mode` | varchar(16) null | chế độ lắc mặc định: `eat_out` (1 món) / `home` (mâm cơm) — điền sẵn khi lắc (REC-003, migration 2026_10_31_100007) |
| `tray_dish_count` | tinyint unsigned null | số món trong mâm cơm 1–4, không tính cơm; null = mặc định 3 (REC-003) |

> **Về `sex`:** công thức Mifflin–St Jeor có hệ số khác nhau theo giới tính sinh học. Khi người dùng
> chọn `unspecified`, dùng công thức Katch–McArdle nếu có % mỡ, nếu không thì lấy trung bình hai hệ
> số và **hạ `confidence` xuống `low`**, đồng thời nói rõ trên giao diện.

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
