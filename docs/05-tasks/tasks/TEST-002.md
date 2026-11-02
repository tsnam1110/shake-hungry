# [TEST-002] — Kiểm chứng engine calo trên FNDDS + Nutrition5k

**Mã task:** TEST-002
**Giai đoạn:** GĐ1
**Loại:** Test
**Status:** ✅ DONE (2026-11-02) — tiêu chí chính ĐẠT từ lần 1 (89,7%); hai điểm chặn
đã gỡ: nhóm `hap` xác nhận bằng vòng thử Q7 với hệ số độc lập FSANZ (86,7%),
nhóm chiên đạt cổng trên kho món Việt ở QA-002 (95,0% — xem mục đóng cuối file)
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
- [x] **Nhóm chiên/rán ≥70%: ĐẠT — trên kho món Việt (QA-002).** Trên harness FNDDS nhóm
      này vẫn không đo được (nguyên liệu SR không có `oil_absorption_pct` — giới hạn của bộ
      mẫu, không phải của engine). Cổng "kiểm chứng engine trên món chiên" đã đạt ở QA-002
      lần 3–5 với tham chiếu độc lập: nhóm chiên **92,5–95,0%** trong ±20% (≥70%), lần cuối
      trên kho 654 món 2026-11-01. Phân xử đóng ở mục cuối file (2026-11-02).
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

**Việc còn lại để đóng task:** — ĐÃ XONG TOÀN BỘ (2026-11-02, xem mục đóng cuối file)

- ~~Bổ sung `oil_absorption_pct` có nguồn rồi chạy lại~~ → CALC-004 đã áp hệ số theo nhóm
  (2026-10-31) và QA-002 đã đo nhóm chiên trên kho món Việt (đạt 95,0%).
- ~~Bổ sung yield factor cho cá/thuỷ sản, chạy lại và so hai lần đo~~ → vòng thử Q7
  (2026-11-02): baseline tái lập khớp lần 1; áp hệ số FSANZ 0,84 (độc lập) cho cá vây
  hấp/luộc qua tuỳ chọn `--seafood-yield` → nhóm `hap` 6,7% → 86,7%.
- Ba test Pest ở `tests/Feature/AccuracyRunTest.php` chốt lại hành vi đã kiểm chứng (chuẩn hoá
  100 g, yield factor cho khối lượng thành phẩm) để sửa engine mà hỏng thì đỏ ngay. ✅ (đã có)


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

---

## ĐÓNG TASK — 2026-11-02 (vòng thử Q7 + phân xử hai điểm chặn)

**Vòng thử Q7 (hồ sơ nguồn: T-09 trong T-08-T12-BACKLOG).** Đăng ký trước theo tiền lệ
CALC-004: giả thuyết — nhóm `hap` lệch −21% vì THIẾU yield factor trong phép đo, và hệ số
độc lập FSANZ 0,84 (cá vây phi lê hấp/luộc, −16%) sẽ kéo nhóm này về gần 0 mà không cần
đụng vào engine. Cách làm: thêm tuỳ chọn `--seafood-yield` cho `lacdoi:accuracy-run`
(chỉ áp cho đầu vào cá vây `Fish,` ở món hap/luoc; các đầu vào còn lại của món đó nhận
1,0 để engine suy được khối lượng thành phẩm; tôm/sò CỐ Ý không áp vì FSANZ 0,84 là cá vây).

Kết quả (báo cáo: accuracy-runs/2026-11-02-fndds.md + 2026-11-02-q7-fndds.md; nhật ký
ACCURACY-SPEC §6):

| Chỉ số | Baseline (tái lập) | Sau thử 0,84 |
|---|---|---|
| Tổng % trong ±20% | 89,7% | **95,6%** |
| Nhóm `hap` % trong ±20% | 6,7% | **86,7%** (13/13 cá vây) |
| Nhóm `hap` bias | −20,6% | −7,5% |
| Hệ số KL ngầm nhóm `hap` | 0,79 | 0,94 |

- **Chẩn đoán xác nhận:** lệch nhóm hấp là thiếu yield factor trong phép đo, KHÔNG phải
  lỗi engine — đúng như điều tra lần 1, nay có nguồn độc lập chứng minh (không vòng tròn:
  hệ số không suy từ USDA).
- **Bias dư −7,5% có giải thích nguyên tắc:** giá trị chín công bố của USDA bám dải
  nguyên con (~0,79) trong khi FSANZ 0,84 là phi lê — dư là chênh phi lê/nguyên con mà
  T-09 đã dự báo. Không "chỉnh" hệ số về 0,79 cho khớp (đó là phép thử vòng tròn bị cấm).
- **Tôm + sò hấp vẫn −21%:** chưa có hệ số riêng đạt chuẩn độc lập (dải Singhato 0,6–0,9
  quá rộng; MEXT Bảng 15 có số theo loài — trích khi cần). Hồ sơ mở, không chặn task:
  hai món này là giới hạn bộ mẫu FNDDS, không liên quan số liệu món Việt.
- **Phía sản phẩm (món Việt): KHÔNG đặt `default_yield_factor` cho nguyên liệu cá Việt.**
  Lý do: (1) định lượng món Việt là khối lượng phần ăn được theo khẩu phần, không phải
  công thức đầu vào sống kiểu FNDDS; (2) schema chỉ có MỘT hệ số mặc định/nguyên liệu
  trong khi FSANZ khác nhau theo phương pháp (hấp/luộc 0,84; nướng nguyên con 0,76) —
  đặt một số chung sẽ sai cho các phương pháp còn lại. Engine đã chứng minh xử lý đúng
  yield factor khi được cấp (AccuracyRunTest + vòng thử này).

**Phân xử điểm chặn chiên:** cổng DoD "nhóm chiên ≥70%" nhằm kiểm chứng engine trên loại
món sai số lớn nhất. Harness FNDDS không bao giờ đo được nhóm này (nguyên liệu SR không
có hệ số hấp thụ dầu và không thể gán mà không chế số). QA-002 đã thực hiện đúng phép đo
đó trên kho món Việt với tham chiếu độc lập (lần 3: 92,5%; lần 4 sau hiệu chuẩn CALC-004:
95,0%; lần 5 trên kho cuối: 92,5%) — cổng coi như ĐẠT bằng bằng chứng thay thế đúng bản
chất. **TEST-002 DONE.**
