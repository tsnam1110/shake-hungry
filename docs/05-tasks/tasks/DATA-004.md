# [DATA-004] — Nhập bảng hệ số hao hụt khi nấu

**Mã task:** DATA-004 · **Giai đoạn:** GĐ1 · **Loại:** Dữ liệu
**Status:** ✅ DONE (2026-11-02) — DoD đạt toàn bộ từ 2026-08-31; hai phần "còn lại" đã
phân xử đóng ở mục cuối file (retention = hoãn theo thiết kế vì v1 chỉ tính macro; hút
nước tinh bột = không cần trong thực hành vì công thức dùng dạng đã nấu)
· **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** DATA-006

---

## Mục tiêu

Nạp hệ số hao hụt (yield factor) và hệ số giữ lại (retention factor) để engine tính đúng khối lượng
thành phẩm. Bỏ qua bước này thì mọi chất dinh dưỡng đều sai theo cùng một tỷ lệ.

## Nguồn (public domain, đã duyệt)

| Bảng | Nội dung |
|---|---|
| *USDA Table of Cooking Yields for Meat and Poultry, Release 2* | Hệ số hao hụt thịt, gia cầm theo cách nấu |
| *USDA Table of Nutrient Retention Factors, Release 6 (2007)* | 26 chất × ~290 thực phẩm. Có cả PDF và CSV |
| *FAO/INFOODS Guidelines for Converting Units v1.0 (2012)* | **Phương pháp** chọn hệ số — đọc và áp dụng, không chép bảng |

## Việc cần làm

1. Tải bảng USDA (CSV nếu có, nếu chỉ có PDF thì bóc bảng).
2. Nạp vào bảng tra cứu hệ số, khoá theo (loại thực phẩm, cách chế biến).
3. Điền `default_yield_factor` cho các nguyên liệu đã có ở [ING-001](./ING-001.md).
4. Lưu bản sao điều khoản vào `data/reference/_licenses/`.

## Definition of Done

- [x] Bảng hệ số nạp được — bảng `cooking_yields`, 163 dòng, tra theo (`ndb_number` →
      `external_id` fdc SR Legacy, `preparation_method`); seeder dựng lại từ file
- [x] 9 nguyên liệu thịt/gia cầm cốt lõi có `default_yield_factor` suy từ dòng cụ thể
      (NDB + phương pháp ghi trong `default_yield_factor_note`); 3 mục ghi rõ vì sao null
      (ba chỉ, vịt — bảng không có). **Còn lại:** hệ số HÚT NƯỚC của gạo/bún/phở (bảng
      này chỉ có thịt/gia cầm) — cần nguồn khác hoặc tự đo
- [x] `data/reference/usda-cooking-yields.csv` + MANIFEST (SHA-256, license CC0, tiêu chí lọc)
- [x] Ghi chú nguyên liệu Việt không có trong bảng: xem `default_yield_factor_note` từng
      file (ba chỉ, vịt) và mục Ghi chú dưới đây

## Cạm bẫy

- **Hệ số giữ lại (retention) áp cho vi chất, không áp cho năng lượng.** v1 chỉ tính macro nên phần
  lớn chưa dùng đến — nhưng đừng áp nhầm vào kcal.
- **Bảng USDA không có món Việt.** Gạo, bún, bánh phở phải suy từ nhóm tương đương hoặc tự đo — và
  phải **ghi rõ là suy ra**, không ghi như thể tra được.

## Ghi chú

**2026-08-31 — nạp xong phần thịt/gia cầm:**

- Nguồn: file CSV **chính thức** `USDA_CookingYields_MeatPoultry02.csv` từ Ag Data Commons
  (public domain) — không phải bóc PDF. NDB number nối sang `fdc_id` SR Legacy qua
  `sr_legacy_food.csv`, nên tra được theo nguyên liệu đã ánh xạ.
- **Không có nguyên liệu nào của ta trùng NDB trực tiếp** với 163 dòng (bảng đo các cắt
  bán lẻ Mỹ chuyên biệt) ⇒ `default_yield_factor` phải SUY từ dòng tương đương, mỗi giá
  trị ghi rõ NDB + phương pháp trong `default_yield_factor_note` (ràng buộc CHECK + V13c).
- **Hoãn có ý thức:** (1) *Nutrient Retention Factors R6* — chỉ áp cho VI CHẤT, v1 tính
  macro chưa dùng; PDF đã tải về `storage/imports/retn06.pdf` chờ GĐ sau. (2) Hệ số hút
  nước gạo/bún/mì — ngoài phạm vi bảng thịt; hiện `com-trang` đã là bản chụp cơm CHÍN
  (is_ready_to_eat) nên món cơm không phụ thuộc hệ số này; cần khi công thức ghi gạo sống.
- Việc tra hệ số **theo cách nấu của từng món** (thay vì default theo nguyên liệu) là phần
  còn lại của [CALC-002](./CALC-002.md) — bảng đã sẵn trong DB để làm việc đó.


---

## Bổ sung 2026-09-01 — bảng đã nhập KHÔNG có thuỷ sản

Kiểm lại `data/reference/usda-cooking-yields.csv` (163 dòng, USDA Cooking Yields R2):

```bash
grep -icE 'fish|seafood|shrimp|salmon|cod|scallop' data/reference/usda-cooking-yields.csv
# → 0
```

Bảng R2 theo đúng tên gọi của nó — *"for **Meat and Poultry**"* — chỉ có thịt và gia cầm. Đây là
nguyên nhân trực tiếp khiến nhóm `hap` của [TEST-002](TEST-002.md) lệch **−21% đều đặn**: cá hấp
mất ~21% nước, engine không có hệ số nên giả định bảo toàn khối lượng.

**Không được suy từ SR Legacy** (kcal sống ÷ kcal chín) — chi tiết và lý do ở TEST-002, mục "Hai
điểm chặn". Cần nguồn độc lập với USDA FoodData Central.

**Ứng viên đã tra, chưa dùng được:** USDA Agriculture Handbook 102 *"Food Yields Summarized by
Different Stages of Preparation"* (1975, public domain) —
<https://www.ars.usda.gov/ARSUserFiles/80400530/pdf/ah102.pdf>. Có mục cá và thuỷ sản, nhưng bản
trên mạng là **ảnh quét không có lớp text**, phải OCR rồi đối chiếu tay từng dòng. Chưa làm.

Đã ghi thành **Q7** ở [ROADMAP](../ROADMAP.md) mục "Câu hỏi mở".

---

## ĐÓNG TASK — phân xử hai phần còn lại (2026-11-02, uỷ quyền hoàn thiện)

- **Retention Factors R6:** HOÃN THEO THIẾT KẾ, không phải tồn đọng. Retention chỉ áp cho
  VI CHẤT (chính mục Cạm bẫy của task này đã ghi); engine v1 chỉ tính macro
  (kcal/đạm/béo/bột đường/xơ) nên bảng retention không có nơi tiêu thụ — nhập vào DB bây
  giờ là dữ liệu chết không ai đọc. PDF nguồn đã lưu trữ lại tại `storage/imports/retn06.pdf`
  (bản lưu cũ mất theo lần thay VM; tải lại 2026-11-02 từ USDA ARS, 18 trang) — khi vi chất
  vào phạm vi (GĐ sau) thì bóc bảng từ chính file này. Theo đó, ô DoD cuối của CALC-002
  cũng đóng (xem CALC-002).
- **Hệ số hút nước tinh bột (gạo/bún/phở):** KHÔNG CẦN trong thực hành hiện tại — nguyên
  liệu tinh bột trong kho ở dạng ĐÃ NẤU (bản chụp cơm chín `com-trang` is_ready_to_eat;
  bún/phở tương tự), công thức món ghi theo dạng ăn được nên không phát sinh phép quy đổi
  sống → chín. Nếu sau này có công thức ghi gạo sống, nguồn ứng viên là FAO/INFOODS
  (phương pháp) + tự đo có ghi chép — mở task riêng khi đó.
- **Thuỷ sản:** câu hỏi yield thuỷ sản đã tách thành Q7 và ĐÓNG ở T-09/TEST-002
  (2026-11-02, vòng thử FSANZ 0,84 đạt).

**DATA-004 DONE.**
