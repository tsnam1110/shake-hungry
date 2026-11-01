# QA-002 — Báo cáo đo độ chính xác năng lượng · LẦN 5 (kho cuối 654 món)

**Ngày đo:** 2026-11-01 · **Kho tại thời điểm đo:** 654 món (472 Mức 2 / 182 Mức 1), 649 món tính được
**Phía engine:** DB kiểm chứng riêng dựng từ `data/` ở HEAD của đợt đo (`migrate:fresh --seed` +
`lacdoi:recompute-calories --with-ingredients`), lấy điểm giữa khoảng công bố trong `dish_nutrition`
**Mẫu:** MỚI, phân tầng, seed **20261101** — 160 món (§2)
**Phương pháp:** GIỮ NGUYÊN lần 3/4 — tham chiếu tính tay FAO/INFOODS từ nguyên liệu thô; nhóm chiên
dùng tham chiếu độc lập suy từ cặp sống→chiên USDA SR Legacy (Variant A chính, Variant B đối chứng);
không dùng hệ số engine, không dùng 2 nguồn neo của CALC-004. Định nghĩa DEC-001 không nới.

> **Kết luận lần 5: 98,1% số món trong mẫu nằm trong ±20% (157/160) — ĐẠT VỮNG TIÊU CHÍ DEC-001
> (≥80%).** Bốn nhóm không chiên: **100%** (120/120). Nhóm chiên 92,5% (37/40) với bias +9,3% —
> phân rã cho thấy **toàn bộ phần bias là tính chất cấu trúc của khoảng công bố** (nới bất đối xứng
> ×0,80/×1,25 rồi ×0,85/×1,15 làm điểm giữa cao hơn tâm ước lượng +9,5 điểm %); sau khi trừ cấu trúc,
> phần dư của nhóm chiên là **−0,2 điểm %** — mô hình dầu và cơ số của engine khớp tham chiếu độc lập.
> **Không có cơ sở hiệu chuẩn hệ số ở lần đo này** (và theo nguyên tắc đăng ký trước, hiệu chuẩn chỉ
> mở khi có bằng chứng lệch hệ thống — lần này bằng chứng cho thấy không lệch).

---

## 1. Tóm tắt và so sánh các lần đo

| Chỉ số | Lần 1 | Lần 2 | Lần 3 | Lần 4 | **Lần 5** |
|---|---|---|---|---|---|
| Mẫu | 160 (kho 500) | cùng mẫu | cùng mẫu | cùng mẫu | **160 MỚI (kho 654), seed 20261101** |
| % trong ±20% toàn cục | 76,9% | 77,5% | 98,1% | 98,8% | **98,1% (157/160)** |
| Nhóm chiên | 7,5% | — | 92,5% | 95,0% | **92,5% (37/40)** |
| Bốn nhóm còn lại | ~100% | ~100% | 100% | 100% | **100% (120/120)** |
| MAPE toàn cục | ~6,4% (chiên 31,1%) | 5,06% | 4,6% | 3,8% | **4,37%** |
| Bias toàn cục | +10,3% | — | +3,4% | +2,6% | **+2,61%** |
| TC lọt trong khoảng công bố | — | — | — | — | **160/160 (100%)** |

Lần 4 (98,8%) đo trên kho cũ với mẫu cố định ngay sau hiệu chuẩn CALC-004. Lần 5 là **mẫu mới hoàn
toàn trên kho cuối** — 127/160 món là Mức 2, phần lớn được phân xử định lượng trong 12 đợt LV2 sau
lần đo 4, cộng A1–A4 và AUDIT-FIX (hàng trăm định lượng đã đổi, gồm PX-17 sửa nguồn sai loài).
Kết quả 98,1% trên mẫu mới xác nhận: **chương trình hoàn thiện dữ liệu không làm suy giảm độ chính
xác**; con số tương đương lần 3 và nằm trong nhiễu thống kê quanh mức lần 4 (khác biệt 1 món).

## 2. Mẫu lần 5

Hồ: món thuộc 5 tầng cách nấu trong ACCURACY-SPEC §4 VÀ engine tính được (có dòng `dish_nutrition`
khẩu phần) = **559/654 món**. Ngoài hồ: 47 món sống + 46 món trộn (ngoài 5 tầng như mọi lần đo),
1 món chưa gán cách nấu, 5 món không tính được dinh dưỡng, và các món thuộc 5 tầng nhưng không có
dòng dinh dưỡng do trần nguyên liệu null ≥30% khối lượng.

| Tầng | Cách nấu | Hồ | Cỡ mẫu | Phân bổ Mức 1 / Mức 2 trong mẫu |
|---|---|---|---|---|
| Luộc/hấp | luoc + hap | 90 | 32 | 9 / 23 |
| Xào | xao | 75 | 32 | 3 / 29 |
| Chiên | chien + chien-ngap-dau | 64 | 40 | 7 / 33 |
| Nấu/canh | nau | 226 | 32 | 8 / 24 |
| Nướng/kho | nuong + kho | 104 | 24 | 6 / 18 |
| **Tổng** | | **559** | **160** | **33 / 127** |

Trong mỗi tầng, mẫu được phân bổ tỉ lệ theo mức hoàn thiện trong hồ (làm tròn phần dư lớn nhất);
thứ tự hồ sắp theo slug, dùng bộ sinh `random.Random(20261101)`.

**Quy ước dòng thiếu số (ghi rõ vì kho đã khác lần 1):** chương trình Mức 2 bổ sung nhiều dòng gia vị/
rau thơm lượng vết trỏ tới nguyên liệu có hồ sơ null (ngò gai 2 g, kinh giới 3 g, lá dứa 1 g…). Engine
đưa các dòng đó vào `missing[]` (loại khỏi calo, chỉ nới trần khoảng). Phía tham chiếu tính tay cũng
không có số cho các nguyên liệu đó trong bộ bảng của dự án nên cộng 0 — **đối xứng hai phía**, trung
điểm chỉ chịu hiệu ứng nới trần theo tỉ lệ thiếu (với dòng lượng vết, <1%). Kiểm tra độ nhạy: loại
khỏi mẫu mọi món có dòng null-kcal ≥5% khối lượng công thức → kết quả không đổi (98,1%, n=158).

## 3. Kết quả theo nhóm (Variant A)

| Nhóm | n | Trong ±20% | MAPE | Bias | Trung vị lệch | Bland–Altman (kcal) | TC trong khoảng |
|---|---|---|---|---|---|---|---|
| Luộc/hấp | 32 | **100%** | 1,85% | +0,57% | +0,91% | −0,0 [−28,4 … +28,3] | 32/32 |
| Xào | 32 | **100%** | 1,92% | +0,76% | +1,28% | +4,3 [−10,0 … +18,6] | 32/32 |
| Chiên | 40 | **92,5%** | 9,93% | +9,33% | +10,09% | +40,0 [−19,5 … +99,5] | 40/40 |
| Nấu/canh | 32 | **100%** | 3,37% | +0,09% | +1,31% | +1,9 [−34,6 … +38,3] | 32/32 |
| Nướng/kho | 24 | **100%** | 3,05% | −0,02% | +1,22% | +0,7 [−37,3 … +38,7] | 24/24 |
| **Toàn cục** | **160** | **98,1%** | **4,37%** | **+2,61%** | **+1,75%** | **+11,3 [−39,7 … +62,3]** | **160/160** |

Theo mức hoàn thiện: Mức 1 đạt 100% (33/33, MAPE 3,77%); Mức 2 đạt 97,6% (124/127, MAPE 4,52%) —
cả 3 món ngoài ngưỡng đều là Mức 2 thuộc nhóm chiên (§5). Bốn nhóm không chiên giữ đúng mặt bằng
lịch sử (MAPE 1,9–3,4%, bias ≈ 0): hàng trăm định lượng Mức 2 mới **không** tạo lệch hệ thống nào
ngoài nhóm chiên.

**Đối chứng Variant B** (vỏ = 0 trừ bột-chủ-thể, dầu quy về nguyên liệu chính theo hệ thống hải sản
9,9 / thịt 5): nhóm chiên 92,5% (37/40), MAPE 9,23%, bias +8,62%; toàn cục 98,1% — kết luận không
phụ thuộc cách quy dầu trong dải neo SR cho phép.

## 4. Phân rã nhóm chiên: bias là cấu trúc khoảng, không phải lỗi mô hình

Áp lại phép phân rã của lần 3 §4 (hoàn nguyên hệ số nới ×0,80/×1,25 và ×0,85/×1,15 khỏi hai đầu
khoảng để lấy lại tâm ước lượng của engine, xấp xỉ tỉ lệ thiếu = 0):

- Thành phần cấu trúc trung điểm: **+9,54 điểm %** (trung bình trên 40 món chiên) — khớp con số
  +9,6 điểm % của lần 3.
- Bias đo được của nhóm: **+9,33%** → phần dư sau cấu trúc: **−0,21 điểm %**.

Tức là: tâm ước lượng của engine (cơ số + dầu theo CALC-004 sau hiệu chuẩn lần 4) khớp tham chiếu
độc lập ở mức −0,2 điểm % trên toàn nhóm chiên. Ba món vượt ngưỡng ở §5 là ca cá biệt về **cách quy
dầu cho từng món** và **độ rộng khoảng ở món độ tin cậy thấp**, không phải bằng chứng lệch hệ thống.
Theo ngưỡng đăng ký trước của dự án, **không mở hiệu chuẩn CALC-004** sau lần đo này.

## 5. Ba món ngoài ±20% — phân tích nguyên nhân

### 5.1. `ca-tram-sot-chua-ngot` — +26,0% (hồ sơ mở đã biết từ lần 3)

- Lần 3: +26,2% · lần 4: +23,6% · lần 5: **+26,0%** — ổn định qua ba lần đo, định lượng đã được rà
  ở LV2/AUDIT (PX-17 sửa nguồn sai loài ở món cá rô "hàng xóm"; công thức món này giữ nguyên sau
  phân xử). **Không phải lỗi định lượng mới.**
- Cơ chế: cá nguyên con chiên trần — engine dùng hệ số nhóm hải sản 9% trên 117 g phần ăn được
  (= 94,8 kcal dầu) cộng vỏ bột năng 12%; tham chiếu A dùng neo phần thịt 7% (= 73,7 kcal) — chênh
  dầu ≈ +19 kcal (+6 điểm %). Phần còn lại là cấu trúc khoảng: món độ tin cậy thấp, khoảng công bố
  196–592 (tỉ lệ 3,0×) nên điểm giữa bị kéo cao theo đúng tính chất đã biết. Engine còn *loại* hai
  dòng ớt chuông/hành tây (thiếu `oil_absorption_pct`, 17,8 kcal cơ số) — kéo lệch xuống ~5,7 điểm %;
  nếu không có hiệu ứng ngược này, lệch danh nghĩa còn cao hơn.
- Variant B: +17,2% (trong ngưỡng) — ca này nhạy với cách quy dầu như đã ghi ở lần 3. Giữ nguyên
  trạng thái hồ sơ mở "hải sản chiên trần" (cùng họ với ca dưới); tham chiếu nằm trong khoảng công
  bố (196–592).

### 5.2. `cua-rang-muoi` — +24,5% (ca MỚI, cùng họ cấu trúc)

- Món được hoàn thiện ở LV2-012 theo quy ước A4.5 (chiên rồi xóc bột rang muối 8 g). Engine cộng dầu
  theo hệ số cua biển 9% × 250 g phần ăn được = **202,5 kcal**; tham chiếu A dùng neo phần thịt 7%
  = **157,5 kcal** — chênh +45 kcal = +11,3 điểm %, cộng cấu trúc khoảng (độ tin cậy thấp, khoảng
  272–720) tạo thành +24,5%.
- Variant B (hệ thống nguyên con 9,9%): **+7,0%** — trong ngưỡng rõ rệt. Cua chiên nguyên con (cả
  mai, gạch) không tương đương cặp philê cá sống→chiên của neo 7%: sự thật vật lý nằm giữa hai
  variant và gần B hơn. Đây là **giới hạn của neo tham chiếu cho hải sản nguyên con**, không phải
  bằng chứng hệ số engine sai — cùng họ hồ sơ với cá chiên trần ở 5.1, nay thêm cua nguyên con.
- Tham chiếu nằm trong khoảng công bố. Khuyến nghị: gộp vào cùng hồ sơ mở; chỉ xem xét hệ số riêng
  cho "hải sản nguyên con chiên" nếu có cặp SR/phân tích trực tiếp — không suy diễn từ một ca.

### 5.3. `com-chien-ca-man` — +20,1% (sát ngưỡng; lệch do độ rộng khoảng)

- Phân rã đầy đủ: tâm ước lượng engine *trước nới khoảng* ≈ 496 kcal so với tham chiếu 493,5 kcal
  — **chênh +0,6%**. Dầu: engine 58,5 kcal (cá thu khô 9% + trứng 4%) so với tham chiếu A 50,4 kcal
  (7% + 4,2%) — chênh +1,6 điểm %. Engine loại dòng cải ngọt (thiếu hệ số dầu, −5,2 kcal).
- Toàn bộ phần lệch còn lại đến từ khoảng công bố 294–892 (tỉ lệ 3,03×): món độ tin cậy thấp, tỉ lệ
  thiếu 10,6% khối lượng (cải ngọt) nới trần thêm ×1,106, cộng dải ánh xạ rộng ở hai đầu — điểm giữa
  của một khoảng nới bất đối xứng rộng 3 lần tất yếu cao hơn tâm ~+19%. Tham chiếu (493,5) nằm giữa
  khoảng. **Không có lỗi định lượng, không có lỗi mô hình** — đây là ca minh hoạ thuần tuý nhất cho
  tính chất "điểm giữa khoảng ≠ tâm ước lượng" đã ghi nhận từ lần 3.

## 6. Các ca đáng chú ý trong ngưỡng

- **`ca-chim-sot-ca-chua` +18,5%** (lần 3: +21,9% — ngoài ngưỡng; lần này vào trong): định lượng sau
  các đợt LV2/AUDIT đưa ca cá chiên trần thứ hai về trong ngưỡng ở Variant A; Variant B +6,4%.
- **`banh-mi-trung-op-la` −9,5%**: dòng `xot-mayonnaise` (21,8% cơ số) thiếu `oil_absorption_pct` nên
  bị engine loại khỏi calo; hiệu ứng nới trần theo tỉ lệ thiếu bù phần lớn, lệch ròng vẫn trong
  ngưỡng. Cùng tồn dư: `pate-gan` ở `banh-mi-chao` (ngoài mẫu lần này). Đây là tồn dư của họ PX-31
  (thành phần không chiên trong món phương thức chiên chưa có hệ số 0 tường minh) — vấn đề *đầy đủ
  dữ liệu*, không phải độ chính xác của phần đã tính; đề xuất xử lý ở nhiệm vụ dữ liệu riêng (gán 0
  cho thành phần không qua chiên hoặc hệ số phù hợp theo vai trò), không thuộc phạm vi đo này.
- **`lau-ngan` −18,2%** (nấu/canh, Mức 1): lẩu — tham chiếu cộng toàn bộ nguyên liệu nồi trong khi
  khẩu phần thực ăn một phần; đặc tính đã biết của nhóm lẩu, vẫn trong ngưỡng.
- Nhóm chiên chỉ có 2 món lệch âm (`banh-mi-trung-op-la` −9,5%; `banh-tieu` −2,5%) — phân phối lệch
  của nhóm chiên dồn về phía dương đúng như cấu trúc khoảng dự báo.

## 7. Kết luận và đề xuất

1. **QA-002 ĐẠT ở kho cuối** với định nghĩa DEC-001 giữ nguyên: 98,1% ≥ 80%; khoảng công bố chứa
   tham chiếu ở 160/160 món. Độ chính xác sau chương trình hoàn thiện (LV2 + A1–A4 + AUDIT-FIX)
   được xác nhận trên mẫu mới độc lập.
2. **Không hiệu chuẩn CALC-004.** Bằng chứng lần 5 cho thấy sau khi trừ cấu trúc khoảng, nhóm chiên
   không lệch hệ thống (−0,2 điểm %); hiệu chuẩn theo 3 ca cá biệt sẽ là quá khớp với nhiễu quy dầu
   của tham chiếu.
3. Hồ sơ mở duy nhất của QA-002 vẫn là **cách quy dầu cho hải sản nguyên con chiên trần** (cá nguyên
   con — `ca-tram-sot-chua-ngot`; nay thêm cua nguyên con — `cua-rang-muoi`): hai variant neo SR
   chênh nhau ~10–17 điểm % ở các ca này. Chỉ nên xử lý khi có bằng chứng trực tiếp (cặp SR nguyên
   con hoặc phân tích), theo đúng nguyên tắc các lần trước.
4. Tồn dư đầy đủ dữ liệu họ PX-31 (`xot-mayonnaise`, `pate-gan` thiếu `oil_absorption_pct` trong món
   phương thức chiên) nên xử lý ở một nhiệm vụ dữ liệu riêng có duyệt — tác động đo được ở lần này
   nằm trong ngưỡng nhưng khoảng công bố của các món đó đang bù trừ thay vì tính đúng thành phần.
5. Lần đo tiếp theo: khi kho thay đổi lớn tiếp (đợt dữ liệu mới hoặc đổi engine), hoặc khi có bằng
   chứng trực tiếp cho hồ sơ hải sản nguyên con. Mẫu lần 5 (seed 20261101) lưu kèm báo cáo này để
   tái lập.

## Phụ lục — Mẫu lần 5 và kết quả từng món (Variant A)

Đơn vị kcal/khẩu phần. "TC dầu A" = dầu cộng thêm phía tham chiếu cho nhóm chiên (0 với nhóm khác).
| Món | Tầng | Mức | Engine (khoảng) | Giữa | TC cơ số | TC dầu A | TC A | Lệch A |
|---|---|---|---|---|---|---|---|---|
| banh-bao-nhan-thit | luoc-hap | 2 | 232–351 | 291.3 | 291.9 | 0.0 | 291.9 | -0.2% |
| banh-bao-viet-nam | luoc-hap | 1 | 327–492 | 409.6 | 412.0 | 0.0 | 412.0 | -0.6% |
| banh-beo | luoc-hap | 2 | 313–474 | 393.4 | 390.1 | 0.0 | 390.1 | +0.9% |
| banh-beo-bi | luoc-hap | 1 | 382–589 | 485.6 | 481.0 | 0.0 | 481.0 | +1.0% |
| banh-bot-loc | luoc-hap | 2 | 394–657 | 525.3 | 517.2 | 0.0 | 517.2 | +1.6% |
| banh-cay | luoc-hap | 2 | 479–712 | 595.5 | 592.2 | 0.0 | 592.2 | +0.6% |
| banh-chuoi | luoc-hap | 2 | 265–412 | 338.6 | 334.4 | 0.0 | 334.4 | +1.3% |
| banh-cuon | luoc-hap | 2 | 308–547 | 427.6 | 446.0 | 0.0 | 446.0 | -4.1% |
| banh-da-lon | luoc-hap | 2 | 272–431 | 351.5 | 342.5 | 0.0 | 342.5 | +2.6% |
| banh-gai | luoc-hap | 1 | 289–443 | 366.1 | 365.2 | 0.0 | 365.2 | +0.2% |
| banh-hoi | luoc-hap | 1 | 422–909 | 665.7 | 720.7 | 0.0 | 720.7 | -7.6% |
| banh-it | luoc-hap | 1 | 398–615 | 506.6 | 504.3 | 0.0 | 504.3 | +0.4% |
| banh-ong-la-dua | luoc-hap | 1 | 221–355 | 288.2 | 283.2 | 0.0 | 283.2 | +1.8% |
| banh-to | luoc-hap | 2 | 270–396 | 333.1 | 331.7 | 0.0 | 331.7 | +0.4% |
| banh-troi | luoc-hap | 2 | 211–347 | 278.9 | 275.5 | 0.0 | 275.5 | +1.3% |
| banh-xu-xe | luoc-hap | 2 | 191–294 | 242.2 | 239.7 | 0.0 | 239.7 | +1.1% |
| ca-tram-hap-xi-dau | luoc-hap | 2 | 106–190 | 147.9 | 144.9 | 0.0 | 144.9 | +2.1% |
| cha-com | luoc-hap | 2 | 296–504 | 399.6 | 392.8 | 0.0 | 392.8 | +1.7% |
| ghe-hap | luoc-hap | 2 | 191–382 | 286.3 | 281.2 | 0.0 | 281.2 | +1.8% |
| long-lon-luoc | luoc-hap | 2 | 286–463 | 374.6 | 369.6 | 0.0 | 369.6 | +1.3% |
| ngao-hap | luoc-hap | 2 | 59–103 | 80.8 | 74.4 | 0.0 | 74.4 | +8.6% |
| thit-ngam-mam | luoc-hap | 2 | 486–925 | 705.5 | 689.2 | 0.0 | 689.2 | +2.4% |
| tom-the-hap-sa | luoc-hap | 2 | 120–193 | 156.7 | 153.8 | 0.0 | 153.8 | +1.9% |
| xoi-dau-den | luoc-hap | 1 | 764–1217 | 990.5 | 1012.3 | 0.0 | 1012.3 | -2.1% |
| xoi-dau-do | luoc-hap | 2 | 680–1022 | 850.9 | 852.9 | 0.0 | 852.9 | -0.2% |
| xoi-dau-xanh-mo-hanh | luoc-hap | 1 | 611–895 | 753.0 | 748.6 | 0.0 | 748.6 | +0.6% |
| xoi-ga | luoc-hap | 2 | 596–905 | 750.2 | 741.9 | 0.0 | 741.9 | +1.1% |
| xoi-khuc | luoc-hap | 2 | 728–1178 | 952.6 | 921.1 | 0.0 | 921.1 | +3.4% |
| xoi-la-cam | luoc-hap | 1 | 563–840 | 701.8 | 696.3 | 0.0 | 696.3 | +0.8% |
| xoi-lac | luoc-hap | 2 | 659–989 | 824.2 | 833.2 | 0.0 | 833.2 | -1.1% |
| xoi-mit | luoc-hap | 2 | 575–908 | 741.5 | 752.4 | 0.0 | 752.4 | -1.4% |
| xoi-vo | luoc-hap | 2 | 592–958 | 774.9 | 799.9 | 0.0 | 799.9 | -3.1% |
| bi-xanh-xao-tom-kho | xao | 2 | 83–127 | 105.2 | 111.7 | 0.0 | 111.7 | -5.8% |
| bo-xao-la-lot | xao | 2 | 274–516 | 394.7 | 385.2 | 0.0 | 385.2 | +2.5% |
| bong-cai-xao-nam-chay | xao | 2 | 87–132 | 109.3 | 110.2 | 0.0 | 110.2 | -0.7% |
| bun-nghe | xao | 2 | 301–536 | 418.8 | 402.8 | 0.0 | 402.8 | +4.0% |
| bun-thit-xao | xao | 2 | 429–735 | 582.1 | 572.1 | 0.0 | 572.1 | +1.8% |
| cai-chua-xao-long | xao | 2 | 192–315 | 253.7 | 250.2 | 0.0 | 250.2 | +1.4% |
| com-ga-xao-sa | xao | 2 | 393–690 | 541.3 | 531.0 | 0.0 | 531.0 | +1.9% |
| com-hen-xao | xao | 1 | 236–497 | 366.7 | 364.8 | 0.0 | 364.8 | +0.5% |
| cua-rang-me | xao | 2 | 275–496 | 385.8 | 375.3 | 0.0 | 375.3 | +2.8% |
| dau-dua-xao-thit-bam | xao | 2 | 171–262 | 216.7 | 214.6 | 0.0 | 214.6 | +1.0% |
| dau-que-xao-bo | xao | 2 | 195–332 | 263.7 | 259.2 | 0.0 | 259.2 | +1.7% |
| doc-mung-xao-tep | xao | 2 | 81–134 | 107.3 | 110.2 | 0.0 | 110.2 | -2.7% |
| ga-xao-sa-ot | xao | 2 | 224–330 | 277.1 | 275.4 | 0.0 | 275.4 | +0.6% |
| ghe-rang-me | xao | 2 | 312–558 | 434.8 | 425.9 | 0.0 | 425.9 | +2.1% |
| kho-qua-xao-trung | xao | 2 | 127–186 | 156.5 | 159.8 | 0.0 | 159.8 | -2.0% |
| long-lon-xao-dua-chua | xao | 2 | 242–384 | 313.0 | 312.2 | 0.0 | 312.2 | +0.3% |
| mien-luon | xao | 2 | 321–653 | 487.1 | 503.0 | 0.0 | 503.0 | -3.2% |
| mien-xao-chay | xao | 2 | 251–394 | 322.4 | 325.8 | 0.0 | 325.8 | -1.0% |
| mong-toi-xao-toi | xao | 2 | 85–128 | 106.7 | 105.8 | 0.0 | 105.8 | +0.9% |
| muop-xao-tep | xao | 1 | 91–148 | 119.2 | 117.0 | 0.0 | 117.0 | +1.9% |
| ngan-xao-sa-ot | xao | 2 | 290–458 | 374.0 | 371.3 | 0.0 | 371.3 | +0.7% |
| nui-xao-trung | xao | 1 | 294–432 | 363.0 | 360.8 | 0.0 | 360.8 | +0.6% |
| oc-len-xao-dua | xao | 2 | 452–797 | 624.7 | 608.6 | 0.0 | 608.6 | +2.6% |
| pho-xao | xao | 2 | 320–597 | 458.6 | 448.0 | 0.0 | 448.0 | +2.4% |
| rau-den-xao-toi | xao | 2 | 90–134 | 111.9 | 110.4 | 0.0 | 110.4 | +1.4% |
| su-hao-xao-thit | xao | 2 | 147–228 | 187.7 | 193.7 | 0.0 | 193.7 | -3.1% |
| suon-xao-chua-ngot | xao | 2 | 551–871 | 710.9 | 702.6 | 0.0 | 702.6 | +1.2% |
| thit-bam-xao-sa | xao | 2 | 335–527 | 431.1 | 426.2 | 0.0 | 426.2 | +1.1% |
| thit-bo-xao-hanh-tay | xao | 2 | 290–535 | 412.3 | 402.9 | 0.0 | 402.9 | +2.3% |
| thit-rang-chay-canh | xao | 2 | 421–823 | 621.9 | 605.3 | 0.0 | 605.3 | +2.7% |
| thit-xao-mam-ruoc | xao | 2 | 407–766 | 586.1 | 572.1 | 0.0 | 572.1 | +2.5% |
| tom-cang-rang-me | xao | 2 | 230–407 | 318.4 | 312.1 | 0.0 | 312.1 | +2.0% |
| banh-bao-chien | chien | 2 | 163–391 | 276.9 | 214.8 | 44.1 | 258.9 | +6.9% |
| banh-cam | chien | 2 | 270–635 | 452.3 | 342.8 | 94.0 | 436.8 | +3.6% |
| banh-chung-chien | chien | 2 | 415–1001 | 708.4 | 642.9 | 0.0 | 642.9 | +10.2% |
| banh-cong | chien | 1 | 320–736 | 528.5 | 390.6 | 124.2 | 514.8 | +2.7% |
| banh-goi | chien | 2 | 351–845 | 598.1 | 454.6 | 140.7 | 595.2 | +0.5% |
| banh-khot | chien | 2 | 302–762 | 532.2 | 385.9 | 119.2 | 505.2 | +5.4% |
| banh-mi-trung-op-la | chien | 2 | 256–660 | 457.8 | 468.2 | 37.8 | 506.0 | -9.5% |
| banh-ran | chien | 2 | 283–663 | 473.0 | 362.7 | 94.0 | 456.7 | +3.6% |
| banh-ran-duong | chien | 2 | 291–681 | 486.1 | 370.5 | 97.2 | 467.7 | +3.9% |
| banh-re | chien | 1 | 184–453 | 318.6 | 225.5 | 72.9 | 298.4 | +6.8% |
| banh-tieu | chien | 2 | 157–354 | 255.3 | 190.7 | 71.3 | 262.0 | -2.5% |
| banh-tom-ho-tay | chien | 2 | 429–1020 | 724.8 | 457.6 | 235.3 | 692.8 | +4.6% |
| banh-xeo-chay | chien | 1 | 274–641 | 457.4 | 366.2 | 79.2 | 445.4 | +2.7% |
| banh-xeo-mien-tay | chien | 2 | 288–741 | 514.5 | 382.6 | 89.5 | 472.2 | +9.0% |
| ca-chim-sot-ca-chua | chien | 2 | 300–783 | 541.4 | 330.8 | 126.0 | 456.8 | +18.5% |
| ca-linh-chien-gion | chien | 2 | 203–514 | 358.6 | 190.4 | 116.1 | 306.5 | +17.0% |
| ca-me-chien | chien | 2 | 167–442 | 304.5 | 186.8 | 75.8 | 262.7 | +15.9% |
| ca-tram-sot-chua-ngot | chien | 2 | 196–592 | 394.2 | 232.1 | 80.9 | 313.0 | +26.0% |
| cha-ca-thac-lac | chien | 2 | 189–594 | 391.3 | 263.9 | 84.6 | 348.5 | +12.3% |
| cha-ruoi | chien | 2 | 214–527 | 370.5 | 244.0 | 83.7 | 327.7 | +13.1% |
| chao-tom | chien | 2 | 289–691 | 490.0 | 331.9 | 102.0 | 433.9 | +12.9% |
| com-chay-ninh-binh | chien | 2 | 202–565 | 383.5 | 315.5 | 25.2 | 340.7 | +12.6% |
| com-chien-ca-man | chien | 2 | 294–892 | 592.8 | 443.1 | 50.4 | 493.5 | +20.1% |
| com-chien-dua-trung | chien | 1 | 228–637 | 432.4 | 373.5 | 18.9 | 392.4 | +10.2% |
| com-chien-trung-muoi | chien | 2 | 351–983 | 666.7 | 556.4 | 44.6 | 601.0 | +10.9% |
| com-chien-trung-toi | chien | 2 | 265–725 | 494.8 | 428.8 | 18.9 | 447.7 | +10.5% |
| com-dua-bo | chien | 2 | 327–920 | 623.5 | 526.1 | 32.9 | 559.0 | +11.5% |
| com-ga-xoi-mo | chien | 2 | 332–916 | 624.1 | 529.9 | 32.9 | 562.8 | +10.9% |
| com-rang | chien | 2 | 330–953 | 641.2 | 555.7 | 18.9 | 574.6 | +11.6% |
| cua-rang-muoi | chien | 2 | 272–720 | 496.4 | 241.3 | 157.5 | 398.8 | +24.5% |
| khoai-lang-ken | chien | 1 | 234–582 | 408.1 | 275.4 | 117.0 | 392.4 | +4.0% |
| muc-nhoi-thit | chien | 2 | 350–829 | 589.8 | 405.3 | 130.9 | 536.1 | +10.0% |
| nem-chua-ran | chien | 1 | 162–489 | 325.2 | 244.0 | 50.4 | 294.4 | +10.5% |
| nem-ran | chien | 2 | 183–518 | 350.2 | 274.9 | 51.7 | 326.6 | +7.3% |
| pho-mai-que | chien | 1 | 384–939 | 661.4 | 479.2 | 85.1 | 564.3 | +17.2% |
| thit-vien-sot-ca | chien | 2 | 305–777 | 541.1 | 430.2 | 59.7 | 489.9 | +10.5% |
| trung-chien-hanh | chien | 2 | 96–216 | 155.8 | 118.4 | 28.3 | 146.8 | +6.2% |
| trung-chien-thit-bam | chien | 2 | 174–410 | 292.0 | 225.4 | 46.4 | 271.8 | +7.4% |
| trung-cuon-com | chien | 2 | 190–484 | 337.4 | 272.4 | 37.8 | 310.2 | +8.8% |
| xoi-chien | chien | 2 | 670–1659 | 1164.7 | 1107.0 | 0.0 | 1107.0 | +5.2% |
| banh-dau-xanh | nau-canh | 2 | 266–390 | 327.7 | 325.8 | 0.0 | 325.8 | +0.6% |
| banh-duc-nong | nau-canh | 1 | 298–449 | 373.4 | 374.6 | 0.0 | 374.6 | -0.3% |
| bun-bung | nau-canh | 2 | 308–608 | 458.0 | 421.8 | 0.0 | 421.8 | +8.6% |
| bun-do | nau-canh | 2 | 368–660 | 513.8 | 502.6 | 0.0 | 502.6 | +2.2% |
| bun-mam-chay | nau-canh | 1 | 285–541 | 412.8 | 403.7 | 0.0 | 403.7 | +2.3% |
| bun-rieu-chay | nau-canh | 2 | 446–781 | 613.8 | 604.5 | 0.0 | 604.5 | +1.5% |
| bun-rieu-tom | nau-canh | 2 | 330–576 | 452.8 | 443.1 | 0.0 | 443.1 | +2.2% |
| bun-thang | nau-canh | 2 | 269–498 | 383.3 | 364.3 | 0.0 | 364.3 | +5.2% |
| canh-bi-do-tom | nau-canh | 2 | 59–103 | 80.8 | 79.3 | 0.0 | 79.3 | +2.0% |
| canh-bong | nau-canh | 2 | 376–599 | 487.5 | 484.7 | 0.0 | 484.7 | +0.6% |
| canh-ca-ro-rau-cai | nau-canh | 2 | 110–195 | 152.6 | 149.5 | 0.0 | 149.5 | +2.0% |
| canh-hen | nau-canh | 2 | 105–177 | 141.2 | 137.4 | 0.0 | 137.4 | +2.8% |
| canh-kho-qua-nhoi-thit | nau-canh | 2 | 167–267 | 216.8 | 229.5 | 0.0 | 229.5 | -5.5% |
| canh-rau-ma-thit-bo | nau-canh | 2 | 109–173 | 140.9 | 146.5 | 0.0 | 146.5 | -3.8% |
| canh-suon-nau-dau | nau-canh | 2 | 580–911 | 745.5 | 737.3 | 0.0 | 737.3 | +1.1% |
| chao-ca-tram | nau-canh | 1 | 223–353 | 288.2 | 285.1 | 0.0 | 285.1 | +1.1% |
| chao-dau-xanh | nau-canh | 1 | 400–608 | 504.1 | 499.7 | 0.0 | 499.7 | +0.9% |
| chao-ngan | nau-canh | 1 | 317–484 | 400.6 | 397.1 | 0.0 | 397.1 | +0.9% |
| che-bap | nau-canh | 2 | 265–412 | 338.5 | 334.2 | 0.0 | 334.2 | +1.3% |
| che-chuoi | nau-canh | 2 | 298–499 | 398.5 | 404.6 | 0.0 | 404.6 | -1.5% |
| che-dau-ngu | nau-canh | 1 | 180–354 | 266.8 | 289.3 | 0.0 | 289.3 | -7.8% |
| che-khuc-bach | nau-canh | 2 | 322–516 | 419.0 | 426.4 | 0.0 | 426.4 | -1.7% |
| com-ga-hoi-an | nau-canh | 2 | 422–789 | 605.5 | 597.4 | 0.0 | 597.4 | +1.3% |
| com-ga-quang-nam | nau-canh | 2 | 411–767 | 589.0 | 581.2 | 0.0 | 581.2 | +1.3% |
| com-ga-tam-ky | nau-canh | 2 | 392–698 | 544.8 | 530.3 | 0.0 | 530.3 | +2.7% |
| com-nieu | nau-canh | 2 | 224–448 | 336.1 | 325.0 | 0.0 | 325.0 | +3.4% |
| gia-cay | nau-canh | 2 | 464–742 | 602.8 | 571.8 | 0.0 | 571.8 | +5.4% |
| hu-tieu-ca | nau-canh | 1 | 268–486 | 377.0 | 368.9 | 0.0 | 368.9 | +2.2% |
| lau-ca-chep | nau-canh | 2 | 216–722 | 469.0 | 542.4 | 0.0 | 542.4 | -13.5% |
| lau-ngan | nau-canh | 1 | 59–211 | 135.1 | 165.1 | 0.0 | 165.1 | -18.2% |
| sot-me | nau-canh | 2 | 86–129 | 107.7 | 106.8 | 0.0 | 106.8 | +0.8% |
| tao-pho | nau-canh | 2 | 167–255 | 211.0 | 205.1 | 0.0 | 205.1 | +2.9% |
| banh-can | nuong-kho | 2 | 278–444 | 361.0 | 364.3 | 0.0 | 364.3 | -0.9% |
| banh-mi-que | nuong-kho | 1 | 185–336 | 260.6 | 268.0 | 0.0 | 268.0 | -2.8% |
| banh-mi-thanh-long | nuong-kho | 2 | 244–368 | 306.2 | 304.4 | 0.0 | 304.4 | +0.6% |
| banh-pia | nuong-kho | 2 | 338–535 | 436.7 | 443.2 | 0.0 | 443.2 | -1.5% |
| banh-trang-nuong | nuong-kho | 2 | 139–382 | 260.9 | 306.2 | 0.0 | 306.2 | -14.8% |
| bo-sot-vang | nuong-kho | 2 | 483–1125 | 803.8 | 866.6 | 0.0 | 866.6 | -7.2% |
| ca-keo-rim-tieu | nuong-kho | 2 | 154–263 | 208.2 | 204.7 | 0.0 | 204.7 | +1.7% |
| ca-linh-kho-tieu | nuong-kho | 2 | 151–259 | 205.0 | 196.9 | 0.0 | 196.9 | +4.1% |
| ca-trich-kho | nuong-kho | 2 | 262–417 | 339.6 | 335.6 | 0.0 | 335.6 | +1.2% |
| com-tam-chay | nuong-kho | 2 | 448–876 | 662.3 | 666.5 | 0.0 | 666.5 | -0.6% |
| com-tam-long-xuyen | nuong-kho | 2 | 435–760 | 597.5 | 586.2 | 0.0 | 586.2 | +1.9% |
| ga-rim-nuoc-dua | nuong-kho | 2 | 377–601 | 489.0 | 483.1 | 0.0 | 483.1 | +1.2% |
| heo-quay | nuong-kho | 2 | 488–964 | 725.9 | 706.1 | 0.0 | 706.1 | +2.8% |
| mam-kho | nuong-kho | 2 | 250–454 | 352.1 | 349.9 | 0.0 | 349.9 | +0.6% |
| mam-ruoi | nuong-kho | 1 | 242–472 | 356.6 | 342.4 | 0.0 | 342.4 | +4.1% |
| mam-tep-chung-thit | nuong-kho | 2 | 325–636 | 480.8 | 460.3 | 0.0 | 460.3 | +4.5% |
| muc-kho-nuong | nuong-kho | 1 | 105–210 | 157.4 | 153.6 | 0.0 | 153.6 | +2.5% |
| nam-kho-tieu | nuong-kho | 2 | 93–152 | 122.1 | 127.9 | 0.0 | 127.9 | -4.6% |
| so-diep-nuong | nuong-kho | 1 | 291–448 | 369.2 | 365.6 | 0.0 | 365.6 | +1.0% |
| suon-chay-rim | nuong-kho | 1 | 137–236 | 186.3 | 195.3 | 0.0 | 195.3 | -4.6% |
| suon-nuong | nuong-kho | 2 | 316–511 | 413.6 | 408.2 | 0.0 | 408.2 | +1.3% |
| thit-kho-tau | nuong-kho | 2 | 551–1026 | 788.7 | 770.4 | 0.0 | 770.4 | +2.4% |
| thit-kho-tieu | nuong-kho | 2 | 464–899 | 681.9 | 664.2 | 0.0 | 664.2 | +2.7% |
| thit-lon-gac-bep | nuong-kho | 1 | 403–805 | 604.0 | 582.9 | 0.0 | 582.9 | +3.6% |
