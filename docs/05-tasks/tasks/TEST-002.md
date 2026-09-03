# [TEST-002] — Kiểm chứng engine calo trên FNDDS + Nutrition5k

**Mã task:** TEST-002
**Giai đoạn:** GĐ1
**Loại:** Test
**Status:** 🔵 IN PROGRESS — chạy lần 1 xong 2026-08-31, **tiêu chí chính ĐẠT (89,7%)**;
còn nhóm chiên chưa đo được và nhóm hấp cần yield factor
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

- [x] Đã chạy trên **204 món FNDDS** (≥200)
- [x] Đã chạy trên Nutrition5k — **4.766 món**, nhưng xem đính chính: đó là *sanity check vòng
      tròn*, không phải phép đo độ chính xác
- [x] Báo cáo đủ **bốn** chỉ số + chỉ số chẩn đoán thứ năm (hệ số khối lượng ngầm định):
      [2026-08-31-fndds.md](../../06-nutrition/accuracy-runs/2026-08-31-fndds.md)
- [x] Kết quả tách theo cách chế biến (6 nhóm)
- [x] **Đạt ≥80% trên FNDDS: 89,7%**
- [ ] ❌ **Nhóm chiên/rán ≥70%: CHƯA ĐO ĐƯỢC.** Engine từ chối tính cả 34 món chiên vì nguyên
      liệu thiếu `oil_absorption_pct` — đúng thiết kế CALC-002, nhưng nghĩa là nhóm sai số lớn
      nhất của món Việt vẫn chưa được kiểm chứng. **Đây là lý do task chưa DONE.**
- [x] Không hiệu chỉnh hệ số nào — nguyên nhân hai nhóm lệch đã xác định được, nên không cần
      (và theo cạm bẫy #2, không được) nới hệ số
- [x] Ghi công Nutrition5k đã thêm vào README (kèm trích dẫn CVPR 2021)

---

## Cạm bẫy đã biết

- **Một con số tổng thể che được lỗi cụ thể.** Engine đạt 85% tổng thể nhưng chỉ 55% ở món chiên là
  engine **có lỗi ở phần hấp thụ dầu**, không phải engine đạt mục tiêu. Luôn tách theo cách chế biến.
- **Đừng hiệu chỉnh hệ số cho tới khi đạt số đẹp.** Nếu phải nới hệ số bất định rất rộng mới đạt
  ngưỡng, đó là dấu hiệu engine sai chứ không phải hệ số sai. Điều tra nguyên nhân trước.
- **Đừng bỏ qua món chiên vì "khó".** Đó chính là chỗ món Việt hay rơi vào, và bỏ qua là tự lừa mình.

---

## Ghi chú

**Lần chạy 1 — 2026-08-31, engine 1.0.0.** Lệnh: `php artisan lacdoi:accuracy-run --write-report`.

**Kết quả:** 89,7% trong ±20% · MAPE 3,9% · bias −3,8% · Bland–Altman −5,2 kcal (LoA −28,4…+17,9).
Bốn nhóm đạt: `song` 98,9%, `nau` 100%, `nuong` 94,2%, `luoc` 100%.

**Ba việc đã xảy ra, đáng ghi lại:**

1. **Lần chạy đầu cho bias +400% — và engine KHÔNG sai.** Nguyên nhân: `gram_weight` của SR Links
   là gram cho **cả mẻ** (tới 3.980 g/món), không phải trên 100 g như DATA-003 mô tả; so thẳng
   tổng kcal mẻ với kcal/100 g là sai đơn vị. Đúng như cạm bẫy #2 của task này cảnh báo — điều
   tra trước, không đụng vào hệ số. Sau khi chuẩn hoá về 100 g: 34,8% → 89,7%.
   Đã đính chính DATA-003.

2. **Nhóm `hap` lệch −21% ĐỒNG LOẠT (hệ số khối lượng ngầm định 0,79).** Điều tra: các món này là
   "Fish, cod/salmon/trout, **steamed**" với nguyên liệu là cá **sống**. Cá mất ~21% nước khi hấp,
   nên 100 g cá sống ra ~79 g cá chín. Phép đo giả định bảo toàn khối lượng ⇒ mẫu số quá lớn.
   Đây là **thiếu yield factor trong phép đo**, không phải lỗi engine — và chính là hiện tượng
   [CALORIE-ENGINE §3](../../06-nutrition/CALORIE-ENGINE.md) mô tả. Chỉ số "hệ số khối lượng ngầm
   định" được thêm vào báo cáo để chẩn đoán đúng loại này. Nhóm `kho` có trung vị 1,00 (đa số
   khớp), bias đến từ vài ngoại lệ như "Stewed tripe" −57%.

3. **Nutrition5k không phải thứ tài liệu mô tả.** Kiểm tra 4.766 món: `total_calories` = tổng
   (gram × cal/g) từ chính bảng nguyên liệu của bộ, 100% khớp dưới 0,5 kcal. Calo **không đo độc
   lập**; thứ đo bằng thiết bị là khối lượng. Dùng nó kiểm chứng engine là **vòng tròn** — kết quả
   MAPE 0,04% chỉ chứng minh engine cộng đúng. Đã đính chính ACCURACY-SPEC §4 và ghi nhãn rõ trong
   lệnh để không ai trích nhầm thành "độ chính xác".

**Việc còn lại để đóng task:**

- Bổ sung `oil_absorption_pct` có nguồn (ít nhất cho nhóm nguyên liệu chiên phổ biến) rồi chạy lại
  — chừng nào nhóm chiên còn rỗng thì chưa đánh giá được tiêu chí ≥70%.
- Bổ sung `default_yield_factor` cho cá/thuỷ sản (bảng USDA CY R2 chỉ có thịt và gia cầm), chạy
  lại và **so hai lần đo** để xác nhận nhóm `hap` lên đúng như dự đoán.
- Ba test Pest ở `tests/Feature/AccuracyRunTest.php` chốt lại hành vi đã kiểm chứng (chuẩn hoá
  100 g, yield factor cho khối lượng thành phẩm) để sửa engine mà hỏng thì đỏ ngay.


---

## Bổ sung 2026-09-01 — lần đo 2 (engine 1.1.0) và hai điểm chặn còn lại

Lần đo 2 **không phải** một vòng kiểm chứng mới. Nó chạy lại đúng bộ mẫu cũ sau khi
[CALC-003](CALC-003.md) sửa lỗi khoảng rộng bằng 0. Kết quả: `% tham chiếu nằm trong khoảng`
**1,0% → 80,4%**, còn `% trong ±20%` / MAPE / bias **không đổi** (89,7% / 3,9% / −3,8%) vì điểm
giữa khoảng không đổi. Báo cáo: [2026-09-01-fndds.md](../../06-nutrition/accuracy-runs/2026-09-01-fndds.md).

**Chỉ số "% tham chiếu nằm trong khoảng" là chỉ số có ích nhất mà lần 1 đã bỏ qua.** Nó bằng 1,0%
và không ai dừng lại hỏi vì sao, vì tiêu chí chính đã đạt. Bài học cho các lần đo sau: **đọc hết
bảng chỉ số, kể cả khi dòng đầu tiên đã xanh.**

### Hai điểm chặn để đóng task — cả hai đều là thiếu DỮ LIỆU, không phải thiếu code

| # | Điểm chặn | Vì sao chưa gỡ được |
|---|---|---|
| 1 | **Nhóm chiên chưa đo được** | 0/255 nguyên liệu có `oil_absorption_pct` có nguồn ⇒ engine từ chối tính (đúng CALC-002/QT-1) |
| 2 | **Nhóm `hap` lệch −21% đều đặn** | Cần hệ số hao hụt cho **thuỷ sản**. Bảng USDA Cooking Yields R2 đã nhập có **0 dòng thuỷ sản** — xem Q7 ở [ROADMAP](../ROADMAP.md) |

> ⚠️ **Không được suy hệ số thuỷ sản từ SR Legacy (kcal sống ÷ kcal chín).** Về mặt số học thì suy
> được và ra đúng ~0,79. Nhưng chính USDA là **vế tham chiếu** của phép đo này, nên lấy hệ số từ đó
> rồi đem so lại với nó là **phép thử vòng tròn** — nó sẽ khớp 100% và không chứng minh được gì.
> Đúng loại lỗi đã mắc một lần với Nutrition5k (xem §4). Hệ số phải đến từ **nguồn độc lập**.

### Giới hạn bộ mẫu — ghi lại để lần sau không trích sai

Soát thành phần bộ mẫu ngày 2026-09-01, chi tiết ở [ACCURACY-SPEC §6](../../06-nutrition/ACCURACY-SPEC.md):
bỏ qua 129/333 Food Code (39%); 45% bộ mẫu là món `song` (không có chuyển hoá do nấu); và nhóm
`hap` — nhóm phơi bày rõ nhất giả định khối lượng — có 80% số món chỉ gồm một nguyên liệu chiếm
>90% khối lượng (điển hình: 100 g cá sống + 0,2 g muối).
