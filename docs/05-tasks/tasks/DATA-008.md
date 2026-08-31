# [DATA-008] — Bảng hệ số phần ăn được (nguyên liệu có xương/vỏ)

**Mã task:** DATA-008 · **Giai đoạn:** GĐ1 · **Loại:** Dữ liệu
**Status:** ⬜ TODO · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** —

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

- [ ] Mọi nguyên liệu bán cả xương/vỏ đều có `edible_portion_pct` hoặc `null` kèm giải thích
- [ ] Mỗi hệ số có nguồn (USDA `foodMeasures`, hoặc phép cân của dự án kèm ngày và phương pháp)
- [ ] Trường đã thêm vào DATA-MODEL và DATA-REPOSITORY **trong cùng commit** (QT-7)
- [ ] Engine calo áp hệ số đúng thứ tự: khối lượng mua → phần ăn được → tra dinh dưỡng
- [ ] Có test: cùng "300 g sườn", kết quả sau khi áp hệ số phải thấp hơn rõ rệt

---

## Cạm bẫy đã biết

- **Đừng nhầm `edible_portion_pct` với `yield_factor`.** Phần ăn được = bỏ xương/vỏ **trước khi nấu**.
  Yield factor = thay đổi khối lượng **do nấu** (bay hơi, chảy mỡ). Hai thứ khác nhau, áp nối tiếp.
- **Công thức phải ghi rõ khối lượng là loại nào.** "300 g sườn" mơ hồ. Cần quy ước trong
  [EDITORIAL-GUIDE](../../09-standards/EDITORIAL-GUIDE.md): mặc định là **khối lượng mua ở chợ**
  (cả xương), và ghi chú khi khác.

---

## Ghi chú
