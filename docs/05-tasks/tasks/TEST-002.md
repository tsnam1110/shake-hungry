# [TEST-002] — Kiểm chứng engine calo trên FNDDS + Nutrition5k

**Mã task:** TEST-002
**Giai đoạn:** GĐ1
**Loại:** Test
**Status:** ⬜ TODO
**Ngày tạo:** 2026-08-31
**Phụ thuộc:** CALC-002, DATA-003

---

## Mục tiêu

Chứng minh engine tính calo **đúng về mặt thuật toán**, trước khi tốn công người tính tay và trước
khi sản xuất hàng loạt dữ liệu món.

**Đây là cổng chặn của GĐ1.** Engine chưa qua kiểm chứng thì không được mở GĐ2.

---

## Bối cảnh

Hai bộ dữ liệu trong danh sách trắng cho phép kiểm engine mà không cần chuyên gia:

| Bộ | Cách dùng | License |
|---|---|---|
| **USDA FNDDS + SR Links** | Mỗi Food Code có sẵn danh sách nguyên liệu kèm gram/100 g món, **và** giá trị dinh dưỡng USDA công bố cho chính món đó. Chạy engine trên danh sách nguyên liệu rồi so với giá trị công bố | CC0 |
| **Nutrition5k** (Google Research) | ~5.000 món thật, nguyên liệu + khối lượng + calo **đo bằng thiết bị scanning**, không phải ước lượng. Gần "sự thật" nhất mà dự án tiếp cận được miễn phí | CC BY 4.0 — **bắt buộc ghi công** |

**Logic quan trọng:** với FNDDS, cả nguyên liệu lẫn kết quả đều đến từ cùng một nguồn USDA. Nếu engine
lệch nhiều ở đây thì **engine sai**, không phải dữ liệu sai. Đây là phép thử sạch nhất có thể có.

---

## Việc cần làm

1. Chọn 200–300 Food Code từ FNDDS, ưu tiên món có cách chế biến tương tự món Việt (nấu, xào, chiên,
   luộc). Bỏ món đóng gói công nghiệp.
2. Chạy engine trên danh sách nguyên liệu của từng món, so với giá trị năng lượng USDA công bố.
3. Tính bốn chỉ số theo [ACCURACY-SPEC §4](../../06-nutrition/ACCURACY-SPEC.md): **% trong ±20%**,
   MAPE, bias, Bland–Altman.
4. **Tách kết quả theo cách chế biến.** Nhóm chiên/rán phải báo cáo riêng.
5. Lặp lại với Nutrition5k.
6. Nếu chưa đạt: sửa engine hoặc hiệu chỉnh hệ số ở
   [CALORIE-ENGINE §5](../../06-nutrition/CALORIE-ENGINE.md), rồi chạy lại.
7. Ghi báo cáo vào `docs/06-nutrition/accuracy-runs/YYYY-MM-DD-fndds.md` và cập nhật bảng nhật ký đo
   ở [ACCURACY-SPEC §6](../../06-nutrition/ACCURACY-SPEC.md).

---

## Definition of Done

- [ ] Đã chạy trên ≥200 món FNDDS
- [ ] Đã chạy trên mẫu Nutrition5k
- [ ] Báo cáo đủ **bốn** chỉ số, không phải một con số "độ chính xác"
- [ ] Kết quả **tách theo cách chế biến**, có nhóm chiên/rán riêng
- [ ] Đạt ≥80% số món trong ±20% trên FNDDS
- [ ] Nhóm chiên/rán đạt ≥70% trong ±20% (ngưỡng thấp hơn vì sai số bản chất lớn hơn)
- [ ] Hệ số đã hiệu chỉnh (nếu có) được cập nhật vào CALORIE-ENGINE §5 kèm lý do
- [ ] Ghi công Nutrition5k đã thêm vào README

---

## Cạm bẫy đã biết

- **Một con số tổng thể che được lỗi cụ thể.** Engine đạt 85% tổng thể nhưng chỉ 55% ở món chiên là
  engine **có lỗi ở phần hấp thụ dầu**, không phải engine đạt mục tiêu. Luôn tách theo cách chế biến.
- **Đừng hiệu chỉnh hệ số cho tới khi đạt số đẹp.** Nếu phải nới hệ số bất định rất rộng mới đạt
  ngưỡng, đó là dấu hiệu engine sai chứ không phải hệ số sai. Điều tra nguyên nhân trước.
- **Đừng bỏ qua món chiên vì "khó".** Đó chính là chỗ món Việt hay rơi vào, và bỏ qua là tự lừa mình.

---

## Ghi chú
