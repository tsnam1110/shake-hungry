# [CALC-001] — Engine tính dinh dưỡng (hàm thuần tuý)

**Mã task:** CALC-001
**Giai đoạn:** GĐ1
**Loại:** Backend
**Status:** ✅ DONE (2026-08-31)
**Ngày tạo:** 2026-08-31
**Phụ thuộc:** ING-001

---

## Mục tiêu

Hiện thực engine tính dinh dưỡng — **trái tim kỹ thuật của dự án**. Đây là thứ biến "danh sách nguyên
liệu" thành "calo có thể tin được", và là lý do dự án tồn tại.

---

## Bối cảnh

Đặc tả đầy đủ: [CALORIE-ENGINE](../../06-nutrition/CALORIE-ENGINE.md).

Khảo sát 2026-08-31: **không tồn tại thư viện mã nguồn mở nào làm chuỗi `text nguyên liệu → tra bảng
→ tính kcal`.** Mọi dự án đều tự ghép. Thiết kế tốt nhất tham khảo được là `FoodPropertyHelper` của
Tandoor — **đọc để học thiết kế, tự viết lại bằng PHP**, vì Tandoor là AGPL + Commons Clause, không
được chép mã ([LICENSE-COMPLIANCE §2.4](../../04-data-sources/LICENSE-COMPLIANCE.md)).

Điểm đáng học từ Tandoor: nó báo cáo **cả `total_value` lẫn `missing_value`** — tức là nói rõ phần
nào chưa tính được, thay vì im lặng trả về một con số thiếu.

Điểm **không** được bắt chước từ Mealie: Mealie lưu dinh dưỡng dưới dạng **chuỗi ký tự chép từ markup
schema.org**, không đơn vị, không tính toán, không truy nguyên. Đó chính là thứ dự án này tồn tại để
làm tốt hơn.

---

## Việc cần làm

Lớp `NutritionCalculator`, thuần tuý — không HTTP, không LLM, không phụ thuộc thời gian:

```php
public function compute(
    array $ingredients,      // [{ ingredient_id, grams, is_optional }]
    string $cookingMethod,
    string $fctSource
): NutritionResult
```

Các bước theo [CALORIE-ENGINE §2](../../06-nutrition/CALORIE-ENGINE.md):

1. Quy đổi đơn vị → gram (qua `ingredient_units`, hoặc `density_g_per_ml` nếu là thể tích).
2. Tra `reference_foods` **chỉ trong `fctSource` đã chỉ định** (QT-4).
   - Không tra được ⇒ đưa vào `missing[]`, **bỏ qua khỏi phép cộng, không ước lượng thay thế**.
3. Cộng dồn từ **khối lượng nguyên liệu sống**.
4. Tính khoảng min–max theo độ bất định tích luỹ ([§5](../../06-nutrition/CALORIE-ENGINE.md)).
5. Xác định `confidence` theo bảng [§6](../../06-nutrition/CALORIE-ENGINE.md).
6. Ghi `engine_version` vào kết quả.

Phần hệ số hao hụt và hấp thụ dầu thuộc [CALC-002](./CALC-002.md).

---

## Definition of Done

- [x] Hàm thuần tuý — `App\Nutrition\NutritionCalculator`, test không cần database;
      có test "cùng đầu vào cho cùng đầu ra"
- [x] Trả về đủ: `kcal_min`, `kcal_max`, macro, `confidence`, `missing[]`, `fct_source`, `engine_version`
- [x] `missing[]` không rỗng ⇒ `confidence` tối đa `low`
- [x] `missing[]` ≥30% khối lượng ⇒ `confidence = unknown`, mọi giá trị số null
- [x] **Không trộn `fct_source`** — test `tu choi nguyen lieu ngoai fct_source chi dinh`
- [x] 15 test Pest ở `tests/Unit/NutritionCalculatorTest.php`: món đầy đủ, thiếu nguyên liệu,
      tuỳ chọn, vớt bỏ, chiên, giàu xơ, mixed, khẩu phần không cân… Quy đổi đơn vị nằm ở
      `DishNutritionService::resolveGrams` (tầng không thuần tuý)
- [x] `DishNutritionService::computeAndStore` ghi `dish_nutrition` với `source = 'computed'`;
      `unknown` ⇒ xoá bản ghi cũ, không ghi số nào (QT-5)

---

## Cạm bẫy đã biết

- **Đừng nhân dinh dưỡng với yield factor.** Khối lượng chất dinh dưỡng **không đổi** khi mất nước —
  chỉ nồng độ trên 100 g thay đổi. Tính tổng từ khối lượng **sống**, rồi mới dùng yield factor để suy
  ra khối lượng thành phẩm. Nhân sai chiều làm mất chất dinh dưỡng một cách vô lý.
- **Đừng thay nguyên liệu thiếu bằng giá trị trung bình.** Che chỗ thiếu là vi phạm QT-1. Kết quả sẽ
  thấp hơn thực tế — và đó chính là lý do `missing[]` kéo `confidence` xuống.
- **Đừng để engine gọi LLM.** Ranh giới ở [DATA-PIPELINE §2](../../04-data-sources/DATA-PIPELINE.md).

---

## Ghi chú

**Hoàn thành 2026-08-31.** Hai quyết định hiện thực ngoài đặc tả, đã bổ sung vào
[CALORIE-ENGINE §5b](../../06-nutrition/CALORIE-ENGINE.md):

1. **Nguyên liệu tuỳ chọn** (đặc tả không định nghĩa cách tính): không tính vào cận dưới,
   tính đủ vào cận trên — khoảng min–max phản ánh "có thể không có mặt". Macro (cột đơn,
   không có khoảng) là ước lượng của công thức đầy đủ.
2. **`fct_source = 'mixed'`** (ngoại lệ DATA-PIPELINE §7.2): engine nhận nguyên liệu từ
   mọi nguồn nhưng ép `confidence` tối đa `medium`.

Engine version khởi điểm: `1.0.0` — đổi hằng số nào trong engine phải nâng version để
`lacdoi:recompute-calories` biết món nào cần tính lại.
