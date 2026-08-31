# Mô hình dữ liệu — Lắc Đói

**Cập nhật lần cuối:** 2026-08-31 — hiện thực xong migration ([SETUP-003](../05-tasks/tasks/SETUP-003.md)), đính chính 5 chỗ lệch với thực tế
**Trạng thái:** ✅ **ĐÃ HIỆN THỰC** — 12 migration trong `database/migrations/`, 9 ràng buộc
R1–R9 có test ở `tests/Feature/IntegrityConstraintsTest.php`.

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
> 5. **Bảng `dishes` thiếu cột `estimated_kcal jsonb`** trong khi định dạng file món
>    (DATA-REPOSITORY §3) đã có — cột đã thêm, chứa `{min, max, confidence, source_note}`
>    cho món ăn ngoài Mức 1.
>
> Ngoài ra: cột `embedding vector(768)` chỉ được migration tạo khi extension `vector` đã cài
> (pgvector hoãn có ý thức tới GĐ4 — [SETUP-001](../05-tasks/tasks/SETUP-001.md)).

---

## 0. Quy ước

- Bảng: `snake_case`, số nhiều (`dishes`, `ingredients`).
- Cột: `snake_case`, **không dùng prefix theo tên bảng**.
  > Lý do: dự án `edu-crm-v2` dùng prefix (`brh_`, `cls_`) nhưng áp dụng không đều, dẫn tới tài liệu
  > ghi sai tên cột trong nhiều tháng. Dự án này bỏ hẳn prefix để không tái diễn.
- Khoá chính: `id` (`bigserial`).
- Thời gian: `timestamptz`, luôn lưu UTC.
- Enum: dùng bảng tra cứu hoặc `varchar` + `CHECK`, **không** dùng kiểu `ENUM` của Postgres (khó
  migrate).
- Mọi bảng dữ liệu biên soạn có: `source`, `confidence`, `needs_review`, `created_at`, `updated_at`.

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
| `id` | bigserial | |
| `fct_source` | varchar(32) | `usda_foundation`, `usda_sr_legacy`, `usda_fndds`, `vn_nin`, `off` |
| `external_id` | varchar(64) | Mã gốc ở nguồn (vd `fdc_id`) |
| `name_en` | text | |
| `name_vi` | text | nullable — dịch ở tầng biên soạn |
| `kcal_per_100g` | numeric(8,2) | |
| `protein_g` / `fat_g` / `carb_g` / `fiber_g` | numeric(8,2) | trên 100 g |
| `micronutrients` | jsonb | các vi chất khác, giữ nguyên tên nguồn |
| `raw_payload` | jsonb | bản gốc, để truy nguyên |
| `imported_at` | timestamptz | |

**Ràng buộc:** `UNIQUE (fct_source, external_id)`.

> **Cảnh báo QT-4:** không được join dữ liệu từ hai `fct_source` khác nhau trong cùng một phép tính
> của một món. Món ghi rõ mình dùng nguồn nào ở `dish_nutrition.fct_source`.

### `regions`

Phân cấp địa lý, đệ quy.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `id` | bigserial | |
| `parent_id` | bigint null | tự tham chiếu |
| `country_code` | char(2) | ISO 3166-1 alpha-2 |
| `code` | varchar(32) | `vn-north`, `vn-central`, `vn-south`, `vn-hue`… |
| `name` | text | |
| `level` | varchar(16) | `country` / `macro_region` / `province` |

---

## 3. Nhóm nguyên liệu — nền móng hệ thống

### `ingredients`

Khoảng 250 bản ghi phủ gần hết món Việt thường ngày. **Đây là tầng tốn công nhất và cũng có đòn bẩy
lớn nhất** — làm xong thì món tự tính được calo.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `id` | bigserial | |
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
| `nutrition_snapshot` | jsonb null | Bản chụp dinh dưỡng lúc tra cứu — xem §3.1 |
| `alternatives` | jsonb | Các dòng ứng viên đã bị loại, kèm lý do |
| `secondary_sources` | jsonb | Nguồn bổ sung (nhãn sản phẩm VN) |
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

**Ràng buộc:** `UNIQUE (slug)`. Index `gin (name_vi gin_trgm_ops)` để cảnh báo trùng.

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

### `ingredient_tcm_traits` (GĐ6, tuỳ chọn)

Tính vị theo y học cổ truyền. **Gán ở tầng nguyên liệu, không phải tầng món** (QT-6).

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `ingredient_id` | bigint | |
| `nature` | varchar(8) | `han`, `luong`, `binh`, `on`, `nhiet` |
| `flavor` | varchar(8) | `toan`, `kho`, `cam`, `tan`, `ham`, `dam` |
| `element` | varchar(8) | `moc`, `hoa`, `tho`, `kim`, `thuy` |
| `source_ref` | text | **bắt buộc** — tên tài liệu/URL. Không nguồn thì không được nhập |
| `confidence` | varchar(8) | |

**Cho phép nhiều dòng cho một nguyên liệu** khi các nguồn mâu thuẫn nhau — đó là chuyện bình thường
với phân loại này, và mâu thuẫn phải được hiển thị chứ không giấu đi.

---

## 4. Nhóm món ăn

### `dishes`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `id` | bigserial | |
| `slug` | varchar(96) | tên file trong `data/dishes/` |
| `parent_dish_id` | bigint null | **null = món chuẩn; có giá trị = biến thể** |
| `country_code` | char(2) | |
| `region_id` | bigint null | |
| `is_eat_out` | boolean | có thể ăn ngoài |
| `is_home_cook` | boolean | có thể tự nấu — một món có thể cả hai |
| `cooking_method` | varchar(24) null | `luoc`, `hap`, `xao`, `chien`, `nuong`, `kho`, `nau`, `song` — **quyết định hệ số calo** |
| `completeness_level` | smallint | 1 / 2 / 3 — xem [PRD §7](../01-prd/PRD.md) |
| `recipe_steps` | jsonb null | mảng bước nấu; null nếu chỉ Mức 1 |
| `serving_desc` | text null | "1 tô cỡ vừa" |
| `serving_grams` | numeric(8,2) null | khối lượng khẩu phần |
| `status` | varchar(16) | `draft`, `published`, `archived` |
| `source` / `confidence` / `needs_review` | | |
| `embedding` | vector(768) null | cho gợi ý món tương tự (GĐ4) |

**Index:** `gin (slug gin_trgm_ops)`, `ivfflat (embedding)`.

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
| `missing_ingredients` | jsonb | danh sách nguyên liệu chưa ánh xạ; **không rỗng ⇒ `confidence` ≤ `medium`** |
| `computed_at` | timestamptz | |
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

> **Vì sao là bảng riêng chứ không phải `jsonb`:** đây là danh sách có cấu trúc lặp, cần truy vấn
> được ("món nào chỉ có 1 nguồn?", "nguồn nào bị dùng nhiều nhất?"). `alternatives` của nguyên liệu
> để `jsonb` vì nó là ghi chú tra cứu, không cần truy vấn.

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

### `dish_tcm_summary` (GĐ6)

Kết quả tổng hợp tính vị từ nguyên liệu lên món. Do máy tính, không nhập tay.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `dish_id` | bigint | |
| `nature_score` | numeric(4,2) | thang −2 (rất hàn) → +2 (rất nhiệt) |
| `dominant_element` | varchar(8) null | |
| `coverage_pct` | numeric(5,2) | % khối lượng nguyên liệu có nhãn tính vị |
| `confidence` | varchar(8) | `coverage_pct` < 60 ⇒ tối đa `low` |

---

## 5. Nhóm người dùng

### `user_profiles`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `user_id` | bigint | |
| `height_cm` / `weight_kg` | numeric(5,1) | |
| `birth_year` | smallint | lưu năm sinh, không lưu tuổi (tuổi thay đổi) |
| `sex` | varchar(8) | `male`, `female`, `unspecified` — dùng cho công thức BMR |
| `activity_level` | varchar(16) | ánh xạ sang PAL, xem CALORIE-ENGINE §5 |
| `diet_type` | varchar(24) null | `chay`, `chay-truong`, `low-carb`… |
| `excluded_ingredients` | jsonb | dị ứng / không ăn được — **lọc cứng** ở engine gợi ý |

> **Về `sex`:** công thức Mifflin–St Jeor có hệ số khác nhau theo giới tính sinh học. Khi người dùng
> chọn `unspecified`, dùng công thức Katch–McArdle nếu có % mỡ, nếu không thì lấy trung bình hai hệ
> số và **hạ `confidence` xuống `low`**, đồng thời nói rõ trên giao diện.

### `meal_logs`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `user_id` | bigint | |
| `dish_id` | bigint | |
| `slot` | varchar(8) | |
| `eaten_at` | timestamptz | |
| `source` | varchar(16) | `shake_accepted`, `manual` |

Đây là đầu vào cho hai chức năng: loại trừ món vừa ăn, và gợi ý dựa trên bữa trước.

### `contributions` (GĐ6)

Hàng đợi đóng góp từ người dùng. Ở v1 đây là **kênh phụ**, giữ đơn giản.

| Cột | Kiểu | Ghi chú |
|---|---|---|
| `id` | bigserial | |
| `type` | varchar(24) | `new_dish`, `fix_dish`, `new_place` |
| `payload` | jsonb | nội dung đề xuất |
| `status` | varchar(16) | `pending`, `approved`, `rejected` |
| `reviewed_by` / `reviewed_at` / `reject_reason` | | |

Dữ liệu ở bảng này **không bao giờ hiển thị ra ngoài khi `status <> 'approved'`**, và khi được duyệt
thì được ghi sang `dishes`/`ingredients` với `source='curated'`, không phải `community`.

---

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
| R8 | `dishes.completeness_level >= 2` ⇒ có ≥3 dòng `dish_recipe_sources` | truy nguyên công thức |
| R9 | `dish_ingredients.ingredient_id` phải trỏ tới nguyên liệu có thật | chống slug lỗi thời |

Test kiểm chứng các ràng buộc này: task [TEST-001](../05-tasks/tasks/TEST-001.md).
