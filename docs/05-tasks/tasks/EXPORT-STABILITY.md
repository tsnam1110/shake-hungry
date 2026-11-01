# [EXPORT-STABILITY] — Chẩn đoán + sửa vòng seed→export không phải điểm cố định phía món

**Mã task:** EXPORT-STABILITY · **Giai đoạn:** hạ tầng dữ liệu (QT-3) · **Loại:** Backend / QA
**Status:** ✅ DONE · **Ngày:** 2026-11-01 · **Phát hiện bởi:** TCM-001a (commit `a4ef55c`)

**Phát hiện:** sau TCM-001a, chạy `lacdoi:export-data` trên DB kiểm chứng (vừa
`migrate:fresh --seed` từ `data/`) viết lại **~648 file món** — dữ liệu xuất ngược không
trùng từng byte với file gốc. Phía nguyên liệu đã là điểm cố định (kể cả 25 file có nhãn
TCM); phía món thì chưa. Từ lúc phát hiện tới khi đợt này đóng: **không ai chạy export
toàn cục**.

---

## 1. Phương pháp chẩn đoán (census, không lấy mẫu)

Chụp snapshot toàn bộ 1.176 file `*.json` dưới `data/` → seed DB kiểm chứng riêng
`shake_hungry_verify` → export → so từng file, phân loại tự động theo lớp sai lệch
(so sánh cấu trúc JSON có phân biệt int/float, thứ tự khoá, thứ tự dòng theo định danh).
Mọi con số dưới đây là đếm trên **toàn bộ** diff, không suy từ mẫu.

Trạng thái ban đầu: **521/1.176 file trùng byte** (toàn bộ nguyên liệu 435 + quả 82 +
reference — phía nguyên liệu/quả vốn đã là điểm cố định), **655 file phía món lệch**
(654 món + `dishes/_index.json`).

## 2. Bảng phân loại sai lệch (trước sửa)

| Lớp | Nội dung | Phạm vi |
|---|---|---|
| A1 — thứ tự dòng mảng | Exporter tự sắp lại: `names` theo (locale, is_primary, tên) → tên **en lên trước vi**; `ingredients` theo slug; `meal_slots` theo tên slot (`toi` trước `trua`); `recipe_sources` theo URL | names **654/654** file, ingredients **630**, meal_slots **517**, recipe_sources **430** |
| A2 — định dạng số | `480` trong file thành `480.0` khi export (và `120` → `120.0` ở quantity) | serving_grams **653** file; quantity ~5.289 dòng nguyên trị |
| B1 — trường bị rớt | `is_optional: false` tường minh trong file bị exporter bỏ (chỉ ghi khi `true`) | **5.306 dòng / 646 file** (chuẩn file: ghi tường minh ở 5.583/5.583 dòng) |
| B1b — vị trí khoá | `note` của dòng nguyên liệu: file đặt **cuối dòng** (sau `confidence`), exporter đặt trước `source` | 4 dòng |
| B2 — file viết tay lệch khuôn | `dau-phu-kho-gung.json`: thừa khoá `is_variant: false` (không có cột schema nào — xem §3), thiếu `estimated_kcal`/`external_ref`/`status`, thứ tự khoá cấp cao nhất lộn xộn | 1 file |
| C — index lỗi thời | `data/dishes/_index.json` bản commit dừng ở thời kho **61 món** (count=61, còn chứa 4 món nước mắm đã xoá, 2 tên cũ `com`/`don`), trong khi exporter sinh đúng 654 | 1 file |

Sót phát hiện ở vòng sửa 1 (chỉ lộ khi 631 file đã khớp byte):

| Lớp | Nội dung | Phạm vi |
|---|---|---|
| B3 — thứ tự khoá dòng nguồn | 67 dòng `recipe_sources` ghi theo thứ tự `(accessed, note, url)` thay vì khuôn `(url, accessed, note)` — dấu vết các đợt sửa nguồn bằng script (AUDIT-FIX/LINK-ROT-FIX ghi theo thứ tự cột DB) | 67 dòng / 20 file (liệt kê §4) |
| A2b — số nguyên viết dạng float | `quantity: 1.0` thay vì `1` | đúng **1 dòng** (`suon-non-kho-tieu`) |

## 3. Nguyên nhân gốc (bằng chứng code)

- **A1:** `LacdoiExportData::dishPayload()` `sortBy` theo nội dung cho cả 5 quan hệ phía
  món. Chuẩn file đã chốt là **thứ tự biên tập trong file** (vi trước en; nguyên liệu theo
  trình tự công thức; bữa theo trình tự ngày; nguồn theo thứ tự biên soạn).
  `DishSeeder` chèn các dòng con đúng thứ tự mảng trong file → khoá `id` tăng dần **mang
  sẵn thứ tự đó**; exporter bỏ qua và sắp lại.
- **A2:** cột `DECIMAL(8,2)` (`2026_08_31_100005_create_dishes_tables.php` dòng 30
  `serving_grams`, dòng 90 `quantity`) + cast `'float'` (`app/Models/Dish.php:31`,
  `app/Models/DishIngredient.php:19`) + `JSON_PRESERVE_ZERO_FRACTION` trong `writeJson()`
  → số nguyên trị luôn in kèm `.0`. Chuẩn file: nguyên trị ghi `480`, chỉ số thật sự lẻ
  mới có thập phân (294 dòng quantity lẻ trong kho).
- **B1/B1b:** `dishPayload()` chỉ ghi `is_optional` khi `true` và đặt `note` trước
  `source` — lệch khuôn dòng của toàn kho.
- **B2:** `is_variant` không tồn tại trong schema: tính biến thể được biểu diễn bằng
  `parent_dish_slug` (file này `parent_dish_slug: null` và `is_variant: false` → khoá
  **không mang thông tin nào**), importer không đọc nên export rớt. Ba khoá còn lại là
  khoá chuẩn mà file viết tay đợt DISH-005 bỏ sót; seeder tự điền mặc định
  (`status: draft`, hai khoá còn lại `null` — khớp mặc định của chính seeder).
- **C:** `_index.json` là file máy sinh ("không sửa tay") nhưng bản commit bị bỏ quên từ
  thời kho 61 món — các phiên export sau đó không commit lại file này. Exporter sinh
  đúng; lỗi nằm ở kỷ luật commit, không nằm ở code.
- **B3/A2b:** DB không lưu thứ tự khoá JSON cũng không phân biệt `1` với `1.0` trong cột
  DECIMAL → **điểm cố định từng byte chỉ tồn tại ở khuôn exporter**; mọi ghi file lệch
  khuôn sớm muộn cũng bị vòng export chuẩn hoá lại thành diff bất ngờ.

## 4. Cách sửa đã áp

**Sửa exporter** (`app/Console/Commands/LacdoiExportData.php`, chỉ phía món — phía
nguyên liệu/quả không đụng vì đã là điểm cố định):

1. `names`, `mealSlots`, `dishIngredients`, `recipeSources`, `media`: `sortBy('id')`
   thay cho sắp xếp theo nội dung — xuất đúng thứ tự biên tập đã seed từ file.
2. Dòng nguyên liệu: luôn ghi `is_optional`; `note` chuyển xuống cuối dòng sau
   `confidence` (đúng khuôn 5.583 dòng hiện hữu).
3. Thêm `numValue()`: float nguyên trị → int, áp cho `quantity` và `serving_grams`.

**Chuẩn hoá file qua chính vòng seed→export (23 file, chỉ cơ học — nội dung ngữ nghĩa
không đổi, đã đối chiếu diff từng file):**

- 20 file chuẩn hoá thứ tự khoá dòng nguồn về `(url, accessed, note)`:
  `ba-chi-rang-tom-kho`, `banh-canh-cua`, `bap-cai-xao-ca-chua`, `bi-xanh-xao-tom-kho`,
  `bong-bi-xao-toi`, `bong-cai-xanh-xao-toi`, `bun-ca`, `bun-ga`, `bun-mam`,
  `bun-rieu-tom`, `canh-ca-nuc-la-giang`, `canh-chua-ca-nuc`, `canh-kho-qua-ca-loc`,
  `com-tam-long-xuyen`, `ga-rim-nuoc-dua`, `long-lon-xao-dua-chua`, `mi-quang-ga`,
  `mien-mang-ga`, `mien-xao-cua`, `vit-xao-mang`.
- `suon-non-kho-tieu`: `quantity 1.0 → 1` (1 dòng).
- `dau-phu-kho-gung`: về khuôn chuẩn — bỏ `is_variant: false`, bổ sung
  `estimated_kcal: null`, `external_ref: null`, `status: "draft"` (đúng giá trị seeder
  vốn đã dùng khi seed file này), thứ tự khoá cấp cao nhất theo khuôn.
- `data/dishes/_index.json`: sinh lại đủ 654 món với tên chính hiện hành.

**Khoá bằng test:** `ExportStabilityTest` thêm test toàn cục *"vòng khép kín toàn cục"*:
seed Region + Ingredient + Dish + Fruit từ `data/` trên DB test → export → khẳng định
**hash manifest của toàn bộ cây `data/**/*.json` không đổi một byte** (gồm cả hai file
`_index.json`). Trước đợt này test chỉ phủ phía nguyên liệu — đó là lý do lỗi phía món
sống sót qua nhiều tháng export.

## 5. Kiểm chứng

- Vòng 1 sau sửa exporter: còn lệch **23 file** — đúng danh sách chuẩn hoá dự kiến ở §4,
  631/654 file món khớp byte ngay từ dữ liệu gốc (kèm 7 file đang dở của phiên song song:
  khối `media` của chúng đi qua vòng seed→export nguyên vẹn).
- Vòng 2 (seed lại từ cây đã chuẩn hoá → export): **0/1.176 file lệch** — điểm cố định
  toàn cục đạt.
- `lacdoi:validate-data` trên DB kiểm chứng riêng: **0 🔴**, 11 🟡 (nhóm cảnh báo đã biết).
- Pest: **154/154** (gồm test toàn cục mới; test này tự nó cũng chạy một vòng export
  thật vào `data/` và cây không đổi byte nào).

## 6. Quy tắc vận hành rút ra

1. **Không chạy `lacdoi:export-data` toàn cục khi chưa qua cổng điểm cố định** — từ nay
   cổng đó là test toàn cục trong `ExportStabilityTest`: sửa exporter/seeder/định dạng
   file mà test này đỏ thì không commit.
2. Mọi script sửa dữ liệu hàng loạt phải ghi file **theo đúng khuôn exporter** (thứ tự
   khoá dòng nguồn `url, accessed, note`; `is_optional` ghi tường minh; số nguyên trị
   không kèm `.0`; thứ tự dòng = thứ tự biên tập) — lệch khuôn sẽ thành diff bất ngờ ở
   lần export kế tiếp.
3. File máy sinh (`_index.json`) phải được commit **cùng** lần export sinh ra nó.

**Sự cố môi trường trong đợt:** VM bị thay mới hai lần (mất `/tmp` cùng bản chụp chẩn
đoán đầu; mất PHP/MySQL lần hai). Đã khôi phục theo quy trình quen thuộc (apt chỉ qua
azure mirror — apt replay của VM treo ở mirror cogentco nên phải ngắt; MySQL root native
không mật khẩu + `log_bin_trust_function_creators=1`; tạo lại các DB). Toàn bộ kiểm
chứng chạy trên DB kiểm chứng riêng `shake_hungry_verify`; **không seed DB dev**.
