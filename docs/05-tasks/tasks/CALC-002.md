# [CALC-002] — Hệ số hao hụt khi nấu và hấp thụ dầu khi chiên

**Mã task:** CALC-002 · **Giai đoạn:** GĐ1 · **Loại:** Backend
**Status:** ✅ DONE (2026-11-02) — code xong 2026-08-31; phía dữ liệu đã đối chiếu xong:
DATA-004 nhập bảng CY R2 (163 dòng) + 9 yield có nguồn, DATA-008 DONE, phần yield thuỷ sản
phân xử ở Q7/TEST-002 (không đặt cho nguyên liệu cá Việt — lý do ở TEST-002 mục đóng)
· **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** CALC-001, DATA-004

---

## Mục tiêu

Bổ sung vào engine hai hiệu chỉnh quyết định độ chính xác thực tế: hao hụt khi nấu, và **dầu hấp thụ
khi chiên — nguồn sai số lớn nhất với món Việt**.

## Bối cảnh

Số liệu đo được về dầu hấp thụ:

| Thực phẩm | Chất béo trước | Sau khi chiên |
|---|---|---|
| Khoai tây → khoai chiên | 0,2 % | ~14 % |
| Khoai tây → snack khoai | 0,2 % | tới 40 % |
| Phi lê cá tuyết | 1 % | 10,5 % |
| Đùi gà | 2 % | 18 % |

Dầu chỉ thấm ~1 mm lớp vỏ ngoài; lượng hấp thụ phụ thuộc diện tích bề mặt, bột áo, độ ẩm, nhiệt độ
dầu, thời gian — **không thứ nào được ghi trong công thức nấu ăn**.

⇒ Không tính chính xác được. Chỉ tính được **khoảng**. Đó là lý do món chiên luôn có khoảng rộng và
không bao giờ đạt `confidence = high`.

Đặc tả: [CALORIE-ENGINE §3–§4](../../06-nutrition/CALORIE-ENGINE.md).

## Việc cần làm

1. Áp yield factor: `gram_sau_nấu = gram_sống × yield_factor`, dùng **để suy khối lượng thành phẩm**,
   không nhân vào chất dinh dưỡng.
2. Với `cooking_method ∈ {chien, ran, chien-ngap-dau}`:
   ```
   dầu_hấp_thụ_g = Σ(gram_nguyên_liệu_được_chiên × oil_absorption_pct / 100)
   kcal += dầu_hấp_thụ_g × 9
   kcal_min = kcal × 0,80 ; kcal_max = kcal × 1,25
   confidence ≤ medium
   ```
3. Nguyên liệu thiếu `oil_absorption_pct` trong món chiên ⇒ đưa vào `missing[]`.

## Definition of Done

- [x] Yield factor áp đúng chiều — chỉ dùng suy `cookedGramsEstimate`, không nhân vào
      chất dinh dưỡng
- [x] Món chiên nới khoảng ×0,80–×1,25 và `confidence` tối đa `medium`
- [x] Test `cung nguyen lieu, luoc vs chien cho ket qua khac nhau ro ret`
- [x] Test `mon chien co nguyen lieu thieu oil_absorption_pct thi vao missing`
- [x] Hệ số trong code trích nguồn ở đầu class `NutritionCalculator` + CALORIE-ENGINE §9
- [x] Dữ liệu hệ số — ĐÃ ĐỐI CHIẾU (2026-11-02; dòng chữ cũ "null toàn bộ 150 nguyên liệu,
      bảng chưa nhập" đã lỗi thời): bảng hao hụt USDA CY R2 ĐÃ nhập (163 dòng,
      [DATA-004](./DATA-004.md) DONE); 9 nguyên liệu thịt/gia cầm có `default_yield_factor`
      suy từ dòng cụ thể kèm ghi chú NDB, các nguyên liệu còn lại null CÓ LÝ DO ghi trong
      `default_yield_factor_note`/hồ sơ (bảng nguồn không có mục tương ứng — không chế số);
      hệ số phần ăn được: [DATA-008](./DATA-008.md) DONE; yield thuỷ sản: phân xử Q7 —
      không đặt cho nguyên liệu cá Việt (xem TEST-002 mục đóng 2026-11-02).

## Cạm bẫy

> **Đây là lỗi âm thầm nguy hiểm nhất của engine.** Bỏ qua dầu hấp thụ thì calo món chiên **thấp hơn
> thực tế 30–50%** mà không có gì báo sai — mọi con số chỉ đơn giản là thấp. Món Việt có rất nhiều
> món chiên rán, nên sai số này lan rộng.

- **Đừng dùng một hệ số hấp thụ dầu chung cho mọi nguyên liệu.** Khoai tây và cá chênh nhau vài lần.
  Hệ số nằm ở từng nguyên liệu (`ingredients.oil_absorption_pct`).
- **Đừng bịa hệ số cho nguyên liệu chưa có số liệu.** Để null, món vào `missing[]`. (QT-1)

## Ghi chú
