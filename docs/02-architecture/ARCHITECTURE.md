# Kiến trúc hệ thống — Lắc Đói

**Cập nhật lần cuối:** 2026-08-31 — bản đầu tiên
**Trạng thái:** ⚠️ Toàn bộ tài liệu này là **thiết kế dự kiến**. Chưa có dòng code nào được viết.

---

## 1. Nguyên tắc kiến trúc

1. **Dữ liệu là sản phẩm, code là công cụ.** Khi phải chọn giữa "code đẹp" và "nhập dữ liệu nhanh
   hơn", chọn cái thứ hai.
2. **File là nguồn sự thật, database là bản dựng lại.** Xem [DATA-REPOSITORY](../04-data-sources/DATA-REPOSITORY.md).
3. **Tính toán tách khỏi phỏng đoán.** Mọi con số dinh dưỡng đi qua một hàm thuần tuý, có thể test,
   có thể chạy lại. Không có số nào được "nhập tay cho nhanh" mà không ghi nguồn.
4. **Mỗi giá trị mang theo xuất xứ.** `source`, `confidence`, `computed_at` là cột hạng nhất, không
   phải metadata bổ sung.
5. **Ít công nghệ nhất có thể.** Mỗi ngôn ngữ/runtime thêm vào là một môi trường phải dựng lại trên
   máy mới và một chỗ hỏng thêm.

---

## 2. Stack

| Lớp | Công nghệ | Ghi chú |
|---|---|---|
| Runtime | PHP 8.3+ trên Laragon (Windows) | Không dùng Docker cho dev hằng ngày |
| Framework | Laravel 12+ | |
| Database | **MySQL 8.0+** | Xem §4 |
| Tìm kiếm tên | FULLTEXT ngram + độ giống trigram ở tầng PHP | |
| Admin / nhập liệu | Filament 3+ | Công cụ sản xuất dữ liệu chính |
| Giao diện người dùng | Blade + Livewire 3 | |
| Trợ lý nhập liệu | Claude API | Vai trò giới hạn theo QT-1 |
| Test | Pest | |

---

## 3. Quyết định: KHÔNG dùng Python

**Bối cảnh.** Hệ sinh thái xử lý dữ liệu thực phẩm gần như toàn bộ nằm ở Python. Phương án ban đầu là
tách một pipeline Python riêng cho việc nhập và ánh xạ dữ liệu.

**Quyết định (2026-08-31): không dùng Python. Làm tất cả trong Laravel.**

**Lý do.** Khảo sát cho thấy các thư viện Python được kỳ vọng đều **không dùng được cho tiếng Việt**:

| Thư viện | Lý do loại |
|---|---|
| `ingredient-parser-nlp` (MIT, chính xác 95,62% mức câu, có trường `foundation_foods` làm khoá join) | **Chỉ tiếng Anh.** Dùng cho tiếng Việt phải fine-tune lại mô hình CRF trên tập câu tiếng Việt tự gán nhãn — một dự án con riêng, tốn hơn là dùng LLM |
| `recipe-scrapers` (MIT, ~600 site) | **Không có site Việt Nam nào.** Và dự án không import từ web — dữ liệu do người biên soạn nhập |
| `extruct` | Chỉ hữu ích khi crawl web, không thuộc quy trình v1 |

Việc còn lại — nhập USDA FoodData Central — chỉ là **đọc file CSV**, PHP làm thừa sức.

**Đánh đổi đã chấp nhận:** nếu sau này cần fine-tune mô hình NLP tiếng Việt hoặc chạy phân tích dữ
liệu quy mô lớn, sẽ phải thêm Python. Khi đó đặt ở thư mục `tools/` riêng, **không** ghép vào luồng
chạy của ứng dụng, và phải cập nhật lại mục này.

---

## 4. Quyết định: MySQL 8

Nền tảng CSDL chính thức: **MySQL 8.0+** — cũng là CSDL mặc định của Laragon, môi
trường phát triển của dự án, nên không tốn thêm công cài đặt riêng.

Các nhu cầu đặc thù của dự án và cách MySQL đáp ứng:

1. **Khớp mờ tên món tiếng Việt** — cần cảnh báo trùng ngay lúc biên soạn viên đang gõ
   ("Bún bò Huế" ≈ "bún bò huế đặc biệt"). Độ giống trigram tính ở tầng PHP (dùng trong
   V7 của `lacdoi:validate-data`, và ADM-005 ở GĐ2), kèm chỉ mục FULLTEXT ngram cho
   tìm kiếm theo tên.
2. **Tìm kiếm không dấu + sắp xếp tiếng Việt** — collation `utf8mb4_vi_0900_ai_ci`
   ở tầng database lo cả hai, không cần extension.
3. **Gợi ý món tương tự bằng embedding (GĐ4)** — cột `embedding` hoãn tới GĐ4
   ([REC-002](../05-tasks/tasks/REC-002.md)); khi đó chốt kiểu dữ liệu theo phiên bản
   MySQL đang dùng (MySQL 9 đã có kiểu `VECTOR`).

**Điều kiện vận hành:** database phải tạo với collation ở trên; server bật
`log_bin_trust_function_creators` (khi có binary logging) để tạo được trigger.
Quy trình: [DEPLOYMENT §2](../10-deployment/DEPLOYMENT.md). Tài khoản database dùng
mặc định `root` (không mật khẩu, kiểu Laragon), không tạo user riêng.

**Giới hạn đã biết:** MySQL không có deferred constraint trigger (kiểm lúc COMMIT)
nên R6/R8 kiểm tại thời điểm UPDATE trên `dishes` và DELETE/UPDATE trên hai bảng
con; thời điểm INSERT món do V4 của `lacdoi:validate-data` chốt chặn. Chi tiết:
migration `100008` và [SETUP-004](../05-tasks/tasks/SETUP-004.md).

---

## 5. Các khối hệ thống

```
┌──────────────────────────────────────────────────────────────┐
│  data/  (file JSON/CSV có version — NGUỒN SỰ THẬT)           │
│  reference/ · ingredients/ · dishes/ · media/                │
└────────────────────────┬─────────────────────────────────────┘
                         │ seeder (một chiều, dựng lại được)
                         ▼
┌──────────────────────────────────────────────────────────────┐
│  MySQL 8 (từ 2026-10-30)                                      │
│  ┌────────────┐  ┌────────────┐  ┌─────────────────────────┐ │
│  │ Bảng tham  │  │ Nguyên     │  │ Món ăn                  │ │
│  │ chiếu      │◄─┤ liệu       │◄─┤ + dish_ingredients      │ │
│  │ (USDA/VN)  │  │ (~250)     │  │ + nutrition (đã tính)   │ │
│  └────────────┘  └────────────┘  └─────────────────────────┘ │
└────────┬──────────────────┬──────────────────┬───────────────┘
         │                  │                  │
         ▼                  ▼                  ▼
┌─────────────────┐ ┌───────────────┐ ┌──────────────────────┐
│ Engine tính calo│ │ Engine gợi ý  │ │ Filament — nhập liệu │
│ (hàm thuần tuý) │ │ (luật+vector) │ │ + trợ lý LLM         │
└─────────────────┘ └───────┬───────┘ └──────────────────────┘
                            ▼
                    ┌───────────────┐
                    │ Web — nút Lắc │
                    └───────────────┘
```

### 5.1 Bảng tham chiếu (`reference_foods`)

Dữ liệu nhập từ nguồn ngoài, **chỉ đọc, không sửa tay**. Mỗi dòng là một thực phẩm với thành phần
trên 100g, kèm `fct_source` (`usda_foundation`, `usda_sr_legacy`, `vn_nin`…) và mã gốc.

Nạp lại bằng `php artisan lacdoi:import-usda`. Nếu cần sửa một giá trị, **không sửa bảng này** — tạo
bản ghi đè ở tầng nguyên liệu và ghi lý do.

### 5.2 Nguyên liệu (`ingredients`)

Tầng do dự án biên soạn. Đây là **nền móng của toàn hệ thống**: khoảng 250 nguyên liệu phủ gần hết
món Việt thường ngày. Mỗi nguyên liệu:

- Trỏ tới một dòng trong `reference_foods` (`reference_food_id`) — đây là nguồn dinh dưỡng.
- Có bảng quy đổi đơn vị riêng (`ingredient_units`): "1 muỗng canh nước mắm = 18 g".
- Có hệ số hao hụt khi nấu và hệ số hấp thụ dầu khi chiên.

Làm xong tầng này thì mọi món sau đó **tự tính được calo**. Đây là lý do GĐ1 ưu tiên nguyên liệu chứ
không phải món.

### 5.3 Món ăn (`dishes`)

Thực thể người dùng nhìn thấy. Phân biệt rõ **món chuẩn** và **biến thể** ngay từ đầu (`parent_dish_id`)
— sửa sau khi đã có vài nghìn bản ghi là bất khả thi. Quy tắc phân biệt:
[EDITORIAL-GUIDE §3](../09-standards/EDITORIAL-GUIDE.md).

### 5.4 Engine tính calo

Hàm thuần tuý, không phụ thuộc HTTP, không gọi LLM, test được bằng unit test:

```
tính_dinh_dưỡng(danh_sách_nguyên_liệu_có_gram, phương_pháp_chế_biến) → {kcal_min, kcal_max, macro, confidence, missing[]}
```

Trả về `missing[]` liệt kê nguyên liệu chưa ánh xạ được. **Có `missing[]` thì món không được coi là
đã tính calo.** Chi tiết: [CALORIE-ENGINE](../06-nutrition/CALORIE-ENGINE.md).

### 5.5 Engine gợi ý

Ba tầng, chạy theo thứ tự: **lọc cứng** (bộ lọc người dùng, dị ứng, chế độ ăn) → **loại trừ** (N món
vừa ăn) → **chấm điểm** (phù hợp bữa, phù hợp TDEE còn lại, đa dạng so với gần đây) → chọn ngẫu nhiên
có trọng số trong nhóm điểm cao. Chi tiết:
[RECOMMENDATION-ENGINE](../07-recommendation/RECOMMENDATION-ENGINE.md).

### 5.6 Công cụ nhập liệu (Filament)

**Đây là sản phẩm được ưu tiên cao nhất ở GĐ2.** Tiêu chí nghiệm thu duy nhất: nhập trọn một món ở
Mức 2 trong **≤5 phút**. Thiết kế: [DATA-PIPELINE §4](../04-data-sources/DATA-PIPELINE.md).

---

## 6. Vai trò của LLM trong hệ thống

LLM **nằm ngoài đường đi của mọi con số**. Sơ đồ luồng:

```
Người biên soạn gõ tên món
        │
        ▼
   [LLM] đề xuất danh sách nguyên liệu + gram ước lượng
        │  → mọi đề xuất mang source='llm_suggested', needs_review=true
        ▼
Người biên soạn duyệt / sửa từng dòng  ◄── bắt buộc, không bỏ qua được
        │
        ▼
   [Hàm thuần tuý] tra reference_foods → tính kcal/macro
        │  → source='computed'
        ▼
   Lưu vào dish_nutrition
```

LLM **không bao giờ** xuất hiện ở bước cuối. Nếu một ngày phát hiện có con số dinh dưỡng nào mang
`source='llm_suggested'` trong bảng `dish_nutrition`, đó là **lỗi nghiêm trọng**, phải dừng và truy
nguyên. Lý do đầy đủ: [DATA-PIPELINE §2](../04-data-sources/DATA-PIPELINE.md).

---

## 7. Thiết kế cho đa quốc gia ngay từ đầu

Dữ liệu v1 chỉ có món Việt, nhưng **schema phải đa quốc gia ngay** để không phải migrate đau đớn về sau:

- `dishes.country_code` (ISO 3166-1 alpha-2), không phải cột boolean `is_vietnamese`.
- `dishes.region_id` trỏ tới bảng `regions` phân cấp (quốc gia → vùng → tỉnh), không phải enum
  `Bắc/Trung/Nam`.
- Tên món đa ngôn ngữ trong bảng `dish_names` (`dish_id`, `locale`, `name`, `is_primary`), không phải
  cột `name_en` cạnh `name_vi`.
- `reference_foods.fct_source` cho phép nhiều bảng thành phần cùng tồn tại (nhưng QT-4 cấm trộn
  chúng trong một phép tính).

---

## 8. Những gì cố tình KHÔNG làm ở v1

| Không làm | Lý do |
|---|---|
| Tách API + SPA | Thêm một tầng phải bảo trì; v1 không có client thứ hai |
| Redis / hàng đợi | Chưa có việc gì đủ nặng để chạy nền, trừ nhập USDA (chạy bằng artisan command) |
| Elasticsearch | FULLTEXT ngram + trigram ở tầng PHP đủ cho quy mô vài nghìn món |
| Đa người dùng biên soạn với phân quyền chi tiết | v1 chỉ có 1–2 người biên soạn. Filament role cơ bản là đủ |
| Microservice | Không có lý do gì |
