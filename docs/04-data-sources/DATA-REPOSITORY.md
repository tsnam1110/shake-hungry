# Quy tắc lưu trữ tài nguyên trong repo — Lắc Đói

**Cập nhật lần cuối:** 2026-08-31 — bản đầu tiên
**Hiện thực quy tắc:** QT-3 trong [CLAUDE.md](../../CLAUDE.md)

---

## 1. Quy tắc gốc

> **File trong `data/` là nguồn sự thật. Database chỉ là bản dựng lại từ file.**

Không phải ngược lại. Đây là quy tắc dễ vi phạm nhất trong thực tế, vì sửa thẳng vào Filament nhanh
hơn sửa file. Nhưng vi phạm nó là mất dữ liệu.

**Tiêu chí nghiệm thu, kiểm được bằng máy:**

```bash
git clone <repo> /tmp/thu-nghiem
cd /tmp/thu-nghiem
composer install
php artisan migrate:fresh --seed
php artisan lacdoi:validate-data
```

Kết quả phải ra **đúng y hệt** dữ liệu của máy gốc: đúng số món, đúng số nguyên liệu, đúng từng giá
trị calo, đủ mọi ảnh. Không thiếu một thứ gì.

### Vì sao quy tắc này quan trọng đến vậy

| Nếu chỉ lưu trong database | Nếu lưu trong `data/` |
|---|---|
| Máy khác clone về là DB rỗng | Clone về là có đủ |
| Không biết ai sửa gì, khi nào, vì sao | `git log` cho biết chính xác |
| Sửa nhầm 50 món không undo được | `git revert` |
| Không review được thay đổi dữ liệu | Pull request xem được diff từng dòng |
| Backup là file dump nhị phân, không đọc được | Dữ liệu ở dạng người đọc được |
| Đổi schema là phải viết migration chuyển dữ liệu | Sửa seeder, chạy lại `migrate:fresh --seed` |

Với một dự án mà **dữ liệu chính là sản phẩm**, mất dữ liệu là mất toàn bộ dự án. Code viết lại được
trong một tuần; 500 món biên soạn tay thì không.

---

## 2. Cấu trúc thư mục `data/`

```
data/
├── reference/                    Dữ liệu nhập từ nguồn ngoài — CHỈ ĐỌC
│   ├── _licenses/                Bản sao điều khoản của từng nguồn + ngày tải
│   │   ├── usda-fdc.txt
│   │   └── wikidata.txt
│   ├── usda-sr-legacy.csv        Bản đã lọc, chỉ giữ cột dùng đến
│   ├── usda-foundation.csv
│   ├── usda-fndds-srlinks.csv
│   ├── usda-cooking-yields.csv
│   ├── wikidata-vn-dishes.json
│   └── MANIFEST.json             Ghi nguồn, ngày tải, checksum của từng file
│
├── ingredients/                  ⭐ Nền móng — do dự án biên soạn
│   ├── _index.json               Danh sách slug + tên, để kiểm tra trùng nhanh
│   ├── nuoc-mam.json
│   ├── thit-ba-chi.json
│   └── ...
│
├── dishes/                       Món ăn — do dự án biên soạn
│   ├── _index.json
│   ├── vn/
│   │   ├── pho-bo.json
│   │   ├── bun-bo-hue.json
│   │   └── ...
│   └── ...                       Quốc gia khác khi mở rộng
│
└── media/                        Ảnh
    ├── dishes/
    │   ├── pho-bo-01.webp
    │   └── ...
    └── MANIFEST.json             license + ghi công cho từng ảnh
```

### Vì sao mỗi nguyên liệu / món là một file riêng

Không gộp tất cả vào một file `dishes.json` khổng lồ. Lý do:

- **Diff đọc được.** Sửa một món thì git chỉ hiện một file, không hiện 20.000 dòng.
- **Không xung đột merge.** Hai người sửa hai món khác nhau thì không đụng nhau.
- **Tìm bằng tên file.** `data/dishes/vn/pho-bo.json` — không cần mở công cụ nào.

`_index.json` là file tổng hợp **do máy sinh ra** (`php artisan lacdoi:export-data`), dùng để kiểm
tra trùng lặp nhanh. Không sửa tay file này.

---

## 3. Định dạng file

### Nguyên liệu — `data/ingredients/<slug>.json`

> ## ⚠️ Đính chính 2026-08-31 — mô tả cũ ở mục này SAI VÀ THIẾU
>
> Bản đầu của tài liệu này đưa một ví dụ **tự bịa để minh hoạ**, và nó sai ở ba chỗ:
> 1. Ghi `external_id: "174608"` cho nước mắm. Mã USDA thật là **174531** — 174608 là số bịa.
> 2. Ghi `default_yield_factor: 1.0`, trong khi **toàn bộ 55 file thật đều là `null`** (không tra
>    được hệ số hao hụt có nguồn cho nhóm này).
> 3. Nêu "dùng tạm cho tới khi có dữ liệu Viện Dinh dưỡng" — hướng đó đã bị huỷ
>    ([LEGAL-001](../05-tasks/tasks/LEGAL-001.md)).
>
> Ngoài ra schema cũ **thiếu 10 trường** mà file thật đang dùng. Đây là vi phạm QT-7: dữ liệu đã
> chạy trước tài liệu. Mục dưới đây được viết lại **từ file thật**, không phải từ trí nhớ.
>
> **Bài học ghi lại:** ví dụ trong tài liệu phải lấy từ file có thật, hoặc phải dùng giá trị rõ ràng
> là giả (`"external_id": "<fdc_id>"`). Một ví dụ bịa trông giống thật sẽ bị chép vào code.

**Ví dụ thật** — trích `data/ingredients/nuoc-tuong.json` (rút gọn phần ghi chú dài):

```json
{
  "slug": "nuoc-tuong",
  "name_vi": "Nước tương (xì dầu)",
  "name_en": "Soy sauce (shoyu)",
  "category": "gia-vi",
  "reference_food": {
    "fct_source": "usda_sr_legacy",
    "external_id": "174277",
    "source_url": "https://fdc.nal.usda.gov/food-details/174277/nutrients",
    "scientific_name": null,
    "kcal_per_100g": 53,
    "kcal_min": null,
    "kcal_max": null,
    "protein_g": 8.14,
    "fat_g": 0.57,
    "carb_g": 4.93,
    "fiber_g": 0.8,
    "water_g": null,
    "sodium_mg": 5490,
    "mapping_confidence": "medium",
    "mapping_note": "Shoyu (đậu nành + lúa mì) là loại gần nhất với xì dầu phổ thông bán tại VN...",
    "alternatives": [
      {
        "external_id": "174278",
        "name": "Soy sauce, tamari (thuần đậu nành, không lúa mì)",
        "kcal_per_100g": 60,
        "why_not": "Dùng khi công thức ghi rõ tamari (protein 10,5 / Na 5590 mg)"
      }
    ]
  },
  "secondary_sources": [],
  "density_g_per_ml": 1.08,
  "density_note": "SUY RA từ USDA foodMeasures 174277, kiểm chứng chéo 3 điểm nhất quán...",
  "default_yield_factor": null,
  "oil_absorption_pct": null,
  "is_ready_to_eat": false,
  "units": [
    { "unit_name": "muỗng canh", "grams": 16.0, "note": null, "source": "FDC 174277 foodMeasures" }
  ],
  "source": "curated",
  "confidence": "medium",
  "needs_review": true
}
```

#### Các trường — định nghĩa đầy đủ

| Trường | Bắt buộc | Ý nghĩa |
|---|---|---|
| `slug` | ✅ | Tên file, không dấu, gạch ngang |
| `name_vi` / `name_en` | `name_vi` ✅ | Tên nguyên liệu |
| `category` | ✅ | `thit`, `thuy-san`, `rau-cu`, `gia-vi`, `tinh-bot`, `dau-mo`, `khac` |
| `reference_food` | ✅ | Khối nguồn dinh dưỡng — xem dưới |
| `secondary_sources[]` | | Nguồn **bổ sung** (nhãn sản phẩm VN). Khác `alternatives` là các dòng đã bị **loại** |
| `density_g_per_ml` | | Quy đổi thể tích → khối lượng |
| `density_note` | ✅ khi có `density_g_per_ml` | **Bắt buộc ghi là đo hay suy ra**, và suy từ đâu |
| `default_yield_factor` | | Hệ số hao hụt khi nấu. `null` là hợp lệ khi không tra được |
| `default_yield_factor_note` | ✅ khi có `default_yield_factor` | Suy từ dòng nào của bảng USDA Cooking Yields R2 (NDB + phương pháp) — bổ sung 2026-08-31, DATA-004 |
| `oil_absorption_pct` | | % dầu hấp thụ khi chiên. `null` khi không có số đo |
| `edible_portion_pct` | | % phần ăn được của khối lượng mua ở chợ (cả xương/vỏ) — bổ sung 2026-08-31, DATA-008. `null` + `edible_portion_note` giải thích là hợp lệ |
| `edible_portion_note` | ✅ khi có `edible_portion_pct` | Nguồn: foodMeasures USDA hoặc phép cân (ghi ngày + phương pháp) |
| `is_ready_to_eat` | ✅ | `true` = đã chín/ăn liền (giò lụa, lạp xưởng, cơm) → engine **không** áp hệ số hao hụt |
| `units[]` | | Quy đổi đơn vị đời thường; mỗi dòng **bắt buộc có `source`** |
| `source` / `confidence` / `needs_review` | ✅ | Ba cột chuẩn của mọi bảng biên soạn |

#### Khối `reference_food`

| Trường | Ghi chú |
|---|---|
| `fct_source` | `usda_sr_legacy`, `usda_foundation`, `usda_branded`, `usda_survey`, `ciqual`, `cnf`, `uk_cofid`, `off`, `nhan_san_pham`, `tu_do`, hoặc `null` |
| `external_id` | `fdc_id`, mã Ciqual, mã vạch OFF… |
| `source_url` | **Bắt buộc khi có số liệu.** URL cụ thể đã truy cập |
| `scientific_name` | Tên khoa học — **rất quan trọng để không nhầm loài**, xem [EDITORIAL-GUIDE §9.1](../09-standards/EDITORIAL-GUIDE.md) |
| `kcal_per_100g` | Giá trị trung tâm. `null` = không tra được, **là kết quả hợp lệ** |
| `kcal_min` / `kcal_max` | Khoảng bất định khi các nguồn/giống mâu thuẫn (QT-5). **`null` khi chưa tra được biến thiên** — tuyệt đối không đặt `min = max = giá trị điểm`, vì như thế máy đọc ra là "đo chính xác, biên độ 0", ngược hẳn ý nghĩa. Xem [RESEARCH-PROTOCOL §4.11](../09-standards/RESEARCH-PROTOCOL.md) |
| `protein_g`, `fat_g`, `carb_g`, `fiber_g`, `water_g`, `sodium_mg` | Trên 100 g. **`null` ≠ `0`** — `null` là "không đo", `0` là "biết chắc bằng 0" |
| `mapping_confidence` | `high` / `medium` / `low` |
| `mapping_note` | **Bắt buộc khi `mapping_confidence` ≠ `high`** (ràng buộc R7) |
| `alternatives[]` | Các dòng ứng viên **đã bị loại**, kèm `why_not`. Giữ lại để người sau không tra lại từ đầu và không chọn nhầm dòng đã bị loại có lý do |

> **Vì sao lưu cả giá trị dinh dưỡng vào file nguyên liệu, trong khi `reference_foods` mới là bảng
> tham chiếu?** Vì `data/reference/` hiện **còn trống** — chưa nhập USDA hàng loạt. Các giá trị này
> là **bản chụp tại thời điểm tra cứu**, có `external_id` và `source_url` để đối chiếu lại. Khi
> [DATA-002](../05-tasks/tasks/DATA-002.md) hoàn tất, phải chạy đối chiếu: bản chụp lệch bảng tham
> chiếu ⇒ điều tra, không âm thầm ghi đè. Xem [DATA-MODEL §3](../03-data-model/DATA-MODEL.md).

### Món ăn — `data/dishes/vn/<slug>.json`

```json
{
  "slug": "pho-bo",
  "parent_dish_slug": null,
  "country_code": "VN",
  "region_code": "vn-north",
  "names": [
    { "locale": "vi", "name": "Phở bò", "is_primary": true },
    { "locale": "vi", "name": "Phở thịt bò", "is_primary": false, "is_alias": true },
    { "locale": "en", "name": "Beef pho", "is_primary": true }
  ],
  "is_eat_out": true,
  "is_home_cook": true,
  "cooking_method": "nau",
  "completeness_level": 2,
  "meal_slots": [
    { "slot": "sang", "weight": 10 },
    { "slot": "toi", "weight": 5 }
  ],
  "serving_desc": "1 tô cỡ vừa",
  "serving_grams": 500,
  "ingredients": [
    { "ingredient_slug": "banh-pho-tuoi", "quantity": 180, "unit_name": "g", "source": "curated" },
    { "ingredient_slug": "thit-bo-nam", "quantity": 70, "unit_name": "g", "source": "curated" },
    { "ingredient_slug": "nuoc-mam-truyen-thong", "quantity": 1, "unit_name": "muỗng canh", "source": "curated" }
  ],
  "recipe_steps": ["...", "..."],
  "recipe_sources": [
    { "url": "https://...", "accessed": "2026-09-05", "note": "định lượng cho 4 người, đã chia 4" },
    { "url": "https://...", "accessed": "2026-09-05", "note": "nguồn thứ 2, thịt nhiều hơn khoảng 20%" },
    { "url": "https://...", "accessed": "2026-09-05", "note": "nguồn thứ 3, dùng để chốt khoảng dao động" }
  ],
  "media": [
    { "path": "dishes/pho-bo-01.webp", "license": "own", "is_primary": true }
  ],
  "estimated_kcal": null,
  "source": "curated",
  "confidence": "medium",
  "needs_review": false
}
```

> **Bổ sung 2026-08-31 (DATA-008):** dòng nguyên liệu trong file món nhận thêm khoá tuỳ chọn
> `is_edible_weight: true` khi `quantity` ĐÃ là phần ăn được (nguồn công thức ghi thịt đã
> lọc xương/bóc vỏ). Mặc định vắng khoá = khối lượng mua ở chợ, engine tự nhân
> `edible_portion_pct` — quy ước [EDITORIAL-GUIDE §11](../09-standards/EDITORIAL-GUIDE.md).

> ⚠️ **`ingredient_slug` phải khớp tên file có thật trong `data/ingredients/`.** Ví dụ trên dùng
> `banh-pho-tuoi` và `nuoc-mam-truyen-thong` — không phải `banh-pho` / `nuoc-mam`, vì hai nguyên
> liệu đó **đã bị tách đôi** khi tra cứu (bánh phở tươi/khô, nước mắm truyền thống/công nghiệp). Xem
> [ING-001](../05-tasks/tasks/ING-001.md). Kiểm tra V-slug trong `lacdoi:validate-data` phải bắt
> được slug không tồn tại.

#### `recipe_sources` — bắt buộc với món Mức 2

| Trường | Bắt buộc | Ý nghĩa |
|---|---|---|
| `url` | ✅ | Nguồn đã đọc để tổng hợp định lượng |
| `accessed` | ✅ | Ngày truy cập — trang web thay đổi |
| `note` | ✅ | Nguồn này cho định lượng bao nhiêu người, khác các nguồn kia ở chỗ nào |

**Quy tắc: món Mức 2 phải có ít nhất 3 nguồn.** Lý do và quy trình tổng hợp:
[RESEARCH-PROTOCOL §11](../09-standards/RESEARCH-PROTOCOL.md).

Ghi nguồn **không phải để xin phép** — danh sách nguyên liệu và định lượng là dữ kiện, không có bản
quyền. Ghi để (a) người sau kiểm chứng được, (b) thấy định lượng được tổng hợp từ đâu chứ không phải
bịa ra, và (c) khi ba nguồn mâu thuẫn thì chính mâu thuẫn đó là thông tin về khoảng dao động thật.

> 🚫 **Không chép văn bản công thức.** `recipe_steps` phải **viết bằng lời của mình**. Danh sách
> nguyên liệu là dữ kiện; cách diễn đạt có bản quyền. Chi tiết:
> [RESEARCH-PROTOCOL §11.1](../09-standards/RESEARCH-PROTOCOL.md).

**Lưu ý quan trọng:** file món **không chứa giá trị dinh dưỡng đã tính**. Calo là **kết quả dẫn
xuất**, do engine tính lúc seed. Lưu nó vào file sẽ tạo ra hai nguồn sự thật mâu thuẫn nhau khi bảng
nguyên liệu thay đổi.

Ngoại lệ duy nhất: `estimated_kcal` cho **món ăn ngoài Mức 1** — đây là ước lượng có nguồn, không
tính được từ nguyên liệu:

```json
"estimated_kcal": {
  "min": 400,
  "max": 500,
  "confidence": "low",
  "source_note": "Ước lượng từ khẩu phần điển hình và món tương tự trong FNDDS (Food Code 27450010). Không phải giá trị đo."
}
```

`source_note` là **bắt buộc**. Ước lượng không nguồn thì không được nhập (QT-1).

### Ảnh — `data/media/MANIFEST.json`

```json
{
  "dishes/pho-bo-01.webp": {
    "license": "own",
    "author": "Nguyễn Văn A",
    "captured_at": "2026-09-10",
    "attribution": null
  },
  "dishes/bun-cha-01.webp": {
    "license": "cc-by-4.0",
    "author": "Tên tác giả",
    "source_url": "https://...",
    "attribution": "Ảnh: Tên tác giả (CC BY 4.0)"
  }
}
```

Ảnh **không có license trong MANIFEST thì không được commit**. Đây là ràng buộc R4, kiểm bởi
`lacdoi:validate-data`.

---

## 4. Ảnh và kích thước repo

Ảnh là thứ làm phình repo nhanh nhất. Quy tắc:

| Quy tắc | Giá trị |
|---|---|
| Định dạng | **WebP** (hoặc AVIF), không dùng PNG/JPEG gốc |
| Cạnh dài tối đa | 1280 px |
| Dung lượng mỗi ảnh | ≤ 150 KB |
| Số ảnh mỗi món | 1 ảnh chính, tối đa 3 |
| Ngân sách tổng | ~500 món × 150 KB ≈ **75 MB** — chấp nhận được với git thường |

Nếu vượt ngân sách, **chuyển sang Git LFS trước khi vượt**, không phải sau. Chuyển ngược lại rất
phiền. Quyết định này gắn với câu hỏi Q4 ở [PRD §9](../01-prd/PRD.md).

> ⚠️ **Không lưu ảnh bằng URL ngoài.** Link chết là mất ảnh, và vi phạm QT-3. Ảnh phải nằm trong repo.

---

## 5. Dữ liệu tham chiếu lớn (USDA)

USDA FNDDS đầy đủ khoảng 1,6 GB — **không commit nguyên bản vào repo**.

Quy trình đúng:

1. Tải bản gốc về `storage/imports/` (đã có trong `.gitignore`).
2. Chạy `php artisan lacdoi:import-usda` để **lọc** — chỉ giữ các cột và các dòng thực sự dùng đến.
3. Bản đã lọc (dự kiến vài MB) được ghi vào `data/reference/*.csv` và **có commit**.
4. `data/reference/MANIFEST.json` ghi: URL nguồn, ngày tải, phiên bản, checksum SHA-256 của **file
   gốc** — để người sau tải đúng bản đó.

```json
{
  "usda-sr-legacy.csv": {
    "source_url": "https://fdc.nal.usda.gov/download-datasets/",
    "source_release": "SR Legacy (April 2018, final)",
    "downloaded_at": "2026-09-01",
    "original_sha256": "…",
    "filtered_rows": 7793,
    "license": "Public domain / CC0 1.0",
    "filter_note": "Chỉ giữ nhóm thực phẩm liên quan món Việt; bỏ nhóm thức ăn nhanh Mỹ."
  }
}
```

Như vậy repo vẫn tự đủ (clone về là chạy được), mà không phình vì mấy GB dữ liệu không dùng đến.

---

## 6. Luồng đồng bộ hai chiều

Người biên soạn làm việc trên Filament, không phải trên file JSON. Nên cần đường quay về:

```
      sửa trong Filament (nhanh, tiện)
                  │
                  ▼
            PostgreSQL
                  │  php artisan lacdoi:export-data
                  ▼
          data/**/*.json           ← commit vào git
                  │  php artisan migrate:fresh --seed
                  ▼
            PostgreSQL             ← trên máy khác, dựng lại y hệt
```

**Quy tắc bắt buộc:** kết thúc mỗi phiên biên soạn phải chạy `lacdoi:export-data` rồi commit. Dữ liệu
chỉ nằm trong database mà chưa export **coi như chưa tồn tại** — vì máy khác không có nó.

`lacdoi:export-data` phải sinh ra JSON **ổn định**: khoá sắp xếp theo thứ tự cố định, số định dạng
nhất quán, xuống dòng LF. Nếu không, mỗi lần export sẽ tạo ra diff giả trên hàng trăm file và không
ai review được nữa. Đây là yêu cầu bắt buộc của task
[DATA-007](../05-tasks/tasks/DATA-007.md), không phải chi tiết làm đẹp.

---

## 7. Những gì KHÔNG commit

| Không commit | Vì sao | Nằm ở đâu |
|---|---|---|
| File USDA gốc (1,6 GB) | Quá lớn, tải lại được | `storage/imports/` (gitignore) |
| Database dump | Không phải nguồn sự thật (§1) | — |
| `.env` | Chứa khoá API | `.env.example` có commit |
| Ảnh gốc chưa nén | Phình repo | Nén rồi mới commit |
| Kết quả tính calo | Là dữ liệu dẫn xuất, sinh lại được | Tính lúc seed |
| Bất kỳ dữ liệu nào trong danh sách đen | QT-2 | Không tồn tại trong dự án |
