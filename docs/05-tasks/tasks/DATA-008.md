# [DATA-008] — Bảng hệ số phần ăn được (nguyên liệu có xương/vỏ)

**Mã task:** DATA-008 · **Giai đoạn:** GĐ1 · **Loại:** Dữ liệu
**Status:** ✅ DONE (2026-08-31) — khung + 3 hệ số có nguồn; các hệ số null chờ phép cân, xem Ghi chú · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** —

---

## Mục tiêu

Biên soạn bảng hệ số **phần ăn được** (edible portion) cho các nguyên liệu bán theo khối lượng **cả
xương / cả vỏ**, để engine calo không vống calo lên.

---

## Vì sao cần — phát hiện 2026-08-31 khi tra cứu sườn lợn

Bảng USDA ghi giá trị trên **phần ăn được đã loại xương** (`separable lean and fat`). Nhưng ở chợ Việt
Nam, sườn, gà, cá, ghẹ, nghêu đều **bán theo cân cả xương/cả vỏ**.

Nếu công thức ghi *"300 g sườn"* — đó là khối lượng **có xương**. Áp thẳng 277 kcal/100 g lên 300 g sẽ
cho 831 kcal, trong khi phần ăn được thực tế chỉ khoảng một nửa.

> **Đây là lỗi âm thầm và lan rộng:** không có gì báo sai, chỉ là mọi món có xương đều bị vống calo.
> Nó ảnh hưởng tới chính những món trung tâm của kho dữ liệu — sườn kho, gà luộc, cá kho, canh xương.

---

## Việc cần làm

1. Lập danh sách nguyên liệu bán cả xương/vỏ trong `data/ingredients/`: sườn lợn, thịt gà có da, đùi
   gà, cá (cả con), ghẹ, nghêu, tôm cả vỏ, ốc…
2. Tra hệ số phần ăn được. **Nguồn ưu tiên:**
   - Trường `foodMeasures` của USDA có nhãn `"fruit without refuse"` / `"yield from 1 lb ready-to-cook"`
     — đã dùng được cho vài mục (gà: 276 g phần ăn được từ 1 lb nguyên con; nghêu: 68 g thịt từ 1 lb
     cả vỏ, tỷ lệ **15,0%**).
   - ⚠️ **KHÔNG tin trường `totalRefuse` của endpoint portal** — nó **luôn trả 0**, kể cả với chuối
     (thực tế bỏ vỏ khoảng 36%). Xem [RESEARCH-PROTOCOL §4](../../09-standards/RESEARCH-PROTOCOL.md).
   - Không tra được thì **tự cân**: mua, cân cả xương, lọc, cân lại, ghi rõ phương pháp và ngày.
3. Thêm trường `edible_portion_pct` vào schema nguyên liệu và [DATA-MODEL](../../03-data-model/DATA-MODEL.md).
4. Engine calo áp hệ số **trước** khi tra dinh dưỡng — xem [CALORIE-ENGINE](../../06-nutrition/CALORIE-ENGINE.md).

---

## Definition of Done

- [x] 19 nguyên liệu bán cả xương/vỏ đã xử lý: 3 có `edible_portion_pct` nguồn foodMeasures
      (gà nguyên con 60,9%; nghêu 15,0%; chim cút 89,3%), 16 để `null` kèm
      `edible_portion_note` giải thích thiếu gì và cân thế nào
- [x] Ràng buộc CHECK + kiểm tra V13b: có pct thì bắt buộc có note nguồn
- [x] Trường đã thêm vào DATA-MODEL và DATA-REPOSITORY trong cùng commit (QT-7); quy ước
      khối lượng chợ ghi vào [EDITORIAL-GUIDE §11](../../09-standards/EDITORIAL-GUIDE.md)
- [x] Engine áp đúng thứ tự trong `DishNutritionService`: khối lượng mua → ×pct → tra
      dinh dưỡng; dòng công thức `is_edible_weight = true` thì không áp
- [x] Test `tests/Feature/EdiblePortionTest.php`: 300 g sườn × 50% ⇒ 300 kcal so với 600

---

## Cạm bẫy đã biết

- **Đừng nhầm `edible_portion_pct` với `yield_factor`.** Phần ăn được = bỏ xương/vỏ **trước khi nấu**.
  Yield factor = thay đổi khối lượng **do nấu** (bay hơi, chảy mỡ). Hai thứ khác nhau, áp nối tiếp.
- **Công thức phải ghi rõ khối lượng là loại nào.** "300 g sườn" mơ hồ. Cần quy ước trong
  [EDITORIAL-GUIDE](../../09-standards/EDITORIAL-GUIDE.md): mặc định là **khối lượng mua ở chợ**
  (cả xương), và ghi chú khi khác.

---

## Ghi chú

**Hoàn thành 2026-08-31** (khung + dữ liệu tra được). Phần chờ:

- 16 hệ số `null` (sườn, móng giò, đùi/cánh/chân gà, cá nguyên con, tôm, ốc, cua…) cần
  **phép cân của dự án** — foodMeasures của các dòng tương ứng không có mục yield.
  Phương pháp ghi sẵn trong `edible_portion_note` từng file: mua → cân cả xương/vỏ →
  lọc → cân lại → ghi ngày + cách làm. Đây là việc tay chân của chủ dự án, mỗi lần đi
  chợ cân được 2–3 mục.
- Sườn lợn là mục ĐAU NHẤT còn thiếu (ví dụ gốc của chính task này) — ưu tiên cân trước
  khi nhập các món sườn ở GĐ3.
- `totalRefuse` của portal FDC đã được xác nhận là vô dụng (luôn 0) — đúng cảnh báo
  RESEARCH-PROTOCOL §4; toàn bộ hệ số lấy từ modifier dạng 'yield from 1 lb…' trong
  `food_portion.csv` của bộ CSV.
