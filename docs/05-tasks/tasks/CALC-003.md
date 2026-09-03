# CALC-003 — Hiệu chuẩn khoảng min–max (QT-5), engine 1.1.0

**Mã task:** CALC-003
**Giai đoạn:** GĐ1
**Loại:** Backend
**Status:** ✅ DONE
**Ngày tạo:** 2026-09-01
**Ngày bắt đầu:** 2026-09-01
**Ngày hoàn thành:** 2026-09-01
**Phụ thuộc:** [CALC-001](CALC-001.md), [TEST-002](TEST-002.md)

---

## Mục tiêu

Engine 1.0.0 trả về **khoảng rộng bằng 0** cho món có toàn nguyên liệu ánh xạ `high`, không chiên,
khẩu phần có cân — vì `MAPPING_SPREAD['high']` bằng 0 và không có bất định nền nào khác.

`kcal_min === kcal_max` chính là **"con số chính xác giả"** mà QT-5 cấm. Lỗi này nằm trong engine
từ CALC-001, và phép đo [TEST-002](TEST-002.md) lần 1 đã in ra bằng chứng ngay trên bảng chỉ số —
*"% tham chiếu nằm trong khoảng engine trả về: **1,0%**"* — nhưng không ai đọc dòng đó, vì tiêu chí
chính (89,7%) đã đạt.

---

## Bối cảnh

- Quy tắc: **QT-5** ([CLAUDE.md §3](../../../CLAUDE.md))
- Đặc tả khoảng: [CALORIE-ENGINE §5](../../06-nutrition/CALORIE-ENGINE.md)
- Nhật ký đo: [ACCURACY-SPEC §6](../../06-nutrition/ACCURACY-SPEC.md)
- [DoD chung](../DEFINITION-OF-DONE.md)

---

## Việc cần làm

- [x] Thêm `FCT_BASELINE_SPREAD` vào `NutritionCalculator`, **luôn** áp dụng, cộng với spread ánh xạ
- [x] Hiệu chuẩn giá trị bằng dữ liệu, không đặt tay
- [x] Ghi chú `khoi-luong-thanh-pham-khong-suy-duoc` khi không suy được khối lượng món chín
- [x] `ENGINE_VERSION` 1.0.0 → **1.1.0**
- [x] Cập nhật 13 test dùng cận khoảng như thể là giá trị điểm → dùng **điểm giữa**
- [x] Chạy lại phép đo, ghi báo cáo `docs/06-nutrition/accuracy-runs/2026-09-01-fndds.md`

---

## Cách hiệu chuẩn — 0,04 đến từ đâu

Không đặt tay. Lấy **phân vị 90 của sai số tuyệt đối trên nhóm món mà giả định của engine thực sự
đúng**: nhóm bảo toàn khối lượng (`song` / `luoc` / `nau`, n = 124 của bộ FNDDS).

| Phân vị | Nhóm bảo toàn KL | Toàn bộ 204 món |
|---|---|---|
| p50 | 0,40% | 0,44% |
| p80 | 0,94% | 3,61% |
| **p90** | **3,61%** | 20,06% |
| p95 | 9,91% | 20,93% |

Làm tròn lên **±4%** ⇒ phủ 91,1% nhóm bảo toàn khối lượng, 80,9% toàn bộ.

Tái lập: `php artisan lacdoi:accuracy-run --dataset=fndds`.

---

## Definition of Done

- [x] Không đầu vào hợp lệ nào còn cho `kcal_min === kcal_max`
- [x] `% tham chiếu nằm trong khoảng`: 1,0% → **80,4%**
- [x] `% trong ±20%` **giữ nguyên 89,7%**, MAPE 3,9% và bias −3,8% **không đổi**
- [x] `hap` (6,7%) và `kho` (76,9%) **không đổi** — thiên lệch không bị khoảng nuốt mất
- [x] 40/40 test Pest xanh

---

## Cạm bẫy đã biết

- **Đừng hiệu chuẩn trên toàn bộ bộ mẫu.** p90 toàn bộ là 20,06%, vì phân bố sai số **hai đỉnh**:
  phần lớn món gần như đúng hoàn toàn, còn nhóm `hap`/`kho` lệch ~20% do thiên lệch có hệ thống.
  Hiệu chuẩn trên đó sẽ ra ±20% — nuốt trọn thiên lệch, mọi nhóm "đạt", và engine không tốt hơn
  một chút nào. **Nới khoảng không bao giờ được dùng để sửa thiên lệch**, chỉ để mô tả bất định.
- **Test bám cận khoảng sẽ vỡ hàng loạt.** 13 test cũ viết `expect($result->kcalMin)->toBe(250.0)`
  khi 250 là giá trị điểm — đúng ở 1.0.0 chỉ vì khoảng rộng 0. Đã đổi sang so **điểm giữa**, đúng
  thứ phép đo FNDDS dùng, nên lần hiệu chuẩn sau sẽ không làm vỡ chúng nữa.
- **Khoảng rộng ra thì cận dưới tụt xuống.** Test `luoc vs chien` cũ khẳng định
  `chien->kcalMin > luoc->kcalMax * 2`; sau khi nới, cận dưới của món chiên (đã bị ×0,80) tụt sát
  ngưỡng và assertion suýt sai vì lý do vô nghĩa. Đã đổi sang so điểm giữa.

---

## Ghi chú

**Việc này KHÔNG đóng [TEST-002](TEST-002.md).** Nó sửa một lỗi mà TEST-002 phát hiện ra. Hai điểm
chặn của TEST-002 vẫn nguyên: nhóm chiên chưa đo được (thiếu `oil_absorption_pct`), và nhóm `hap`
lệch −21% (thiếu hệ số hao hụt cho thuỷ sản — xem Q7 ở [ROADMAP](../ROADMAP.md)).

**Bất định nền 0,04 là giá trị tạm cho GĐ1.** Nó được đo trên món Mỹ với ánh xạ USDA cung cấp sẵn.
Khi [QA-002](QA-002.md) đo trên kho món Việt do dự án biên soạn, phải hiệu chuẩn lại — ánh xạ tự làm
gần như chắc chắn kém chắc chắn hơn ánh xạ USDA cho sẵn, nên con số thật sẽ **lớn hơn** 4%.
