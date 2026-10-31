# QA-002 lần 3 — Tham chiếu chiên ĐỘC LẬP, và lần 4 — hiệu chuẩn hệ số CALC-004

**Ngày:** 2026-10-31 · **Engine:** 1.1.0 (không đổi mã engine ở cả hai lần)
**Bộ mẫu:** đúng 160 món ở Phụ lục [báo cáo lần 2](2026-10-31-vn-dishes-lan-2.md)
(luộc/hấp 32 · xào 28 · chiên 40 · nấu/canh 32 · nướng/kho 28)
**Ngưỡng (DEC-001, không đổi):** ≥80% số món trong ±20% so với tham chiếu.
**Chủ dự án đã duyệt cả hai việc** (sau báo cáo lần 2 §7): (1) dựng tham chiếu chiên độc lập
rồi đo lại; (2) rà soát hệ số CALC-004 phía engine, chỉ sửa nhóm nào có bằng chứng lệch
hệ thống rõ rệt từ chính tham chiếu độc lập đó.

> **Kết quả chính — lần 3 (kiểm chứng độc lập): 98,1% trong ±20% — ĐẠT mục tiêu ≥80%.**
> Nhóm chiên: 92,5% (37/40), bias +12,2%. **Lần 4 (sau hiệu chuẩn 2 nhóm hệ số): 98,8%**,
> nhóm chiên 95,0% (38/40), bias +8,8%. Lần 4 là *kiểm tra hiệu chuẩn*, không phải kiểm
> chứng độc lập mới — xem §6 để đọc cho đúng.

---

## 1. Vì sao phải có lần đo này

Lần 2 kết luận: cặp (quy ước dữ liệu Phương án A + tham chiếu "công thức nguyên văn") **không
tương thích về định nghĩa** — phía tham chiếu nguyên văn không bao giờ chứa dầu hấp thụ, còn
engine cộng mô hình dầu CALC-004 (~+100 kcal/món chiên). So hai phía đó với nhau chỉ đo được
*độ lớn của mô hình dầu*, không đo được *độ đúng* của nó.

Lần 3 sửa đúng chỗ đó: phía tham chiếu của nhóm chiên được cộng dầu hấp thụ **tường minh**,
bằng hệ số suy từ nguồn **độc lập với hệ số engine**. Bốn nhóm không chiên giữ nguyên phương
pháp lần 2 để đối chứng.

### Cách tránh đo vòng tròn (điểm quan trọng nhất của báo cáo này)

- Hệ số tham chiếu **không** đọc từ `oil_absorption_pct` của engine, **không** suy ngược từ
  kết quả engine, và **không** dùng hai nguồn mà CALC-004 đã neo vào (tổng quan
  PMC10660127 — Mahmud et al. 2023; IJFST 43(8):1410 — Ziaiifar et al. 2008). Dùng lại hai
  nguồn đó làm "tham chiếu" sẽ chỉ xác nhận engine khớp với chính nguồn của nó.
- Nguồn neo chính: **dữ liệu đo thành phẩm thật trong USDA SR Legacy** (CC0 — đã có trong
  repo tại `data/reference/usda-sr_legacy.csv`, license đã whitelist theo
  LICENSE-COMPLIANCE): các cặp *nguyên liệu sống → thành phẩm chiên* và *hệ thống áo bột*.
  Hệ số hấp thụ được **suy ra bằng cân bằng khối lượng** (bảo toàn protein để suy hao hụt
  nước; cân bằng protein + carb để tách bột áo khỏi thịt cá), không phải số engine.
- Văn liệu ngoài hai nguồn cấm chỉ dùng cho **chiều quan hệ**, không lấy số tuyệt đối:
  Shih & Daigle (1999, *J Agric Food Chem* 47) — bột gạo hút dầu ít hơn bột mì rõ rệt;
  Mellema (2003, *Trends Food Sci Technol* 14(9):364–373) — cơ chế hút dầu ở lớp vỏ.
- **Kiểm chứng đường ống trước khi tin số:** (a) bản sao thuật toán engine (replica) đối
  chiếu với `dish_nutrition` trong DB khớp **496/496 món trong ±0,5%**; (b) chạy lại đúng
  phương pháp lần 2 trên cùng snapshot tái lập **chính xác mọi con số đã công bố của lần 2**
  (tổng 77,5%; bốn nhóm 100%; chiên 10,0%, bias +49,0%, Bland–Altman +137,0 [43,7…230,2]).
  Chỉ khi (a) và (b) đều khớp mới tính tham chiếu mới.

**Giới hạn còn nguyên (như lần 1–2):** hai phía vẫn dùng chung một bảng thành phần của dự án
cho phần *không phải dầu* — phép đo kiểm chứng đường biến đổi nguyên liệu → món và mô hình
dầu, không kiểm chứng sai số của bảng thành phần so với phân tích hoá học. Phần dầu — đối
tượng của lần đo này — là phần duy nhất được neo ra ngoài dự án, qua số đo thành phẩm USDA.

## 2. Neo độc lập từ USDA SR Legacy

Công thức suy hệ số (g dầu hấp thụ / 100 g nguyên liệu sống ăn được):

- **Cặp trơn (không áo bột):** hệ số hao hụt Y = protein_sống / protein_chiên (protein được
  bảo toàn, nước bay hơi làm protein đậm đặc lại); dầu = béo_chiên × Y − béo_sống.
- **Hệ thống áo bột:** giải cân bằng protein + carb với bột mì chuẩn (FDC 168936: đạm 10,33,
  béo 0,98, carb 76,31) để tách khối lượng cá/thịt, khối lượng bột áo khô; dầu = béo thành
  phẩm − béo tự thân của hai thành phần.

| Neo | Cặp FDC (sống → chiên) | Dầu suy ra /100 g sống |
|---|---|---|
| Khoai tây chiên (DENNY'S) | 170026 → 167672 | **8,40** |
| Khoai tây chiên (APPLEBEE'S) | 170026 → 169009 | **8,07** |
| Khoai tây par-fried extruded (tham khảo phụ) | 170026 → 169267 | 10,74 |
| Snack khoai lát mỏng (chips, plain salted) | 170026 → 169677 | **10,81** |
| Chuối plantain xanh chiên | 168215 → 168199 | **9,77** |
| Chuối plantain vàng chiên (Latino) | 169130 → 168200 | **6,53** |
| Trứng chiên | 171287 → 173423 | **4,19** |
| Đậu phụ chiên (chỉ đối chứng — engine cho 0 vì số đã gồm dầu) | 172475 → 172451 | 9,80 |
| Cá da trơn tự nhiên, áo bột (hệ thống) | 174186 → 174187 | **9,92** |
| Cá da trơn nuôi, áo bột (hệ thống) | 175165 → 174187 | 5,91 |
| Cá croaker, áo bột (hệ thống) | 174192 → 171957 | **9,84** |
| Cá mập, áo bột nhúng (hệ thống) | 173697 → 175143 | **11,72** |
| Mực, áo mỏng (hệ thống) | 174223 → 171982 | **5,43** |
| Tôm, áo bột (hệ thống, trên thịt tôm đã nấu ẩm 171971) | → 171970 | 10,66 /100 g thành phẩm |
| Gà ức+cả da, áo bột (hệ thống) | 171474 → 171475 | **2,30** |
| Gà ức+cả da, áo bột mì mỏng (hệ thống) | 171474 → 171476 | **−3,41** (mất béo ròng) |
| Gà không da chiên (áo rất mỏng) | 171052 → 171053 | **3,30** |
| Phô mai que chiên (hệ thống 2 thành phần) | mozzarella 170845 + vỏ → 169015 | **12,56** /100 g phô mai; 20,83 nếu quy hết về vỏ |

Ba quan sát rút ra từ bảng neo:

1. **Thịt chiên hút dầu ròng rất ít** (2,3–3,3), và gà còn da áo bột mì mỏng **mất béo ròng**
   (−3,41) vì mỡ tự thân chảy ra nhanh hơn dầu thấm vào — khớp hiện tượng "gần như không tăng
   béo ròng" mà chính tổng quan của CALC-004 cũng ghi nhận nhưng chưa phản ánh vào hệ số 8.
2. **Hệ thống cá áo bột hội tụ quanh 9,8–11,7** (trừ cá nuôi béo tự thân: 5,91; mực áo mỏng:
   5,43). Dầu nằm phần lớn ở **lớp vỏ** — quy về vỏ trong hệ phô mai que cho 20,83/100 g vỏ.
3. **Củ/quả chiên miếng hội tụ quanh 8,1–9,8**; dạng lát mỏng (chips) cao hơn: 10,81.

## 3. Hệ số tham chiếu dùng cho lần 3

Hai biến thể, tính độc lập nhau ở cách quy dầu, để kết luận không phụ thuộc một giả định:

- **Variant A (chính — quy theo dòng nguyên liệu):** mỗi dòng chủ thể chiên nhận hệ số của
  nhóm nó; vỏ bột mang hệ số riêng. Củ/quả **9** · hải sản (phần thịt) **7** + vỏ tính riêng ·
  thịt **4** · trứng **4,2** · phô mai **12,6** (đã gồm phần vỏ theo tỉ lệ que — dòng vỏ
  trong món `pho-mai-que` nhận 0 để khỏi tính hai lần) · vỏ bột mì **20** · vỏ bột
  gạo/tinh bột **16** (= 20 × 0,8 theo chiều Shih & Daigle) · bột là *chủ thể* chiên trực
  tiếp (bánh rán/cam/tiêu/gối/quai vạc): bột mì **22**, bột gạo/nếp **18** (neo bánh
  doughnut suy bằng cân bằng bột, dải ~20–28, lấy cận thận trọng).
- **Variant B (đối chứng — quy về hệ thống):** vỏ = 0 (trừ vai trò bột-chủ-thể giữ như A),
  toàn bộ dầu quy về nguyên liệu chính theo *hệ thống*: hải sản **9,9**, thịt **5**, còn lại
  như A.

Ghi đè theo món (có lý do, ghi tại đây): `bim-bim` — khoai-tay **10,8** (neo chips, không
dùng 9 của dạng miếng); `bot-chien` — bot-gao/bot-nang **8** (bánh bột gạo *đã nấu chín*
chiên áp chảo — **bằng chứng yếu**, không có cặp SR tương ứng, lấy thấp hơn bột sống; đây là
giả định yếu nhất của lần đo); `banh-bao-chien` — bot-mi **12** (bánh đã hấp chín đem rán
lại — bằng chứng yếu, cùng lưu ý). Hai món ngoại lệ `xoi-chien`, `banh-chung-chien` **không
cộng thêm dầu** ở phía tham chiếu: dầu của chúng đã kê tường minh trong công thức và được
cộng ở cả hai phía (xem phán quyết ngoại lệ ở §5).

Chỉ các dòng mà engine coi là chủ thể chiên (`oil_absorption_pct > 0`) mới được cộng dầu ở
phía tham chiếu — đúng cùng tập dòng, để phép so không lẫn với tranh cãi "dòng nào là chủ
thể". 27 slug chủ thể xuất hiện trong 40 món mẫu, tất cả đều có nhóm tham chiếu.

## 4. Kết quả lần 3 — engine hiện hành vs tham chiếu độc lập

| Nhóm | n | Trong ±20% | MAPE | Bias | Bland–Altman (kcal) |
|---|---|---|---|---|---|
| Luộc/hấp | 32 | **100%** | 1,6% | −0,0% | −1,1 [−26,1…23,9] |
| Xào | 28 | **100%** | 1,7% | +1,0% | +5,3 [−7,6…18,1] |
| **Chiên (Variant A)** | 40 | **92,5%** (37/40) | 12,3% | **+12,2%** | +50,2 [−8,0…108,4] |
| Nấu/canh | 32 | **100%** | 2,4% | −0,1% | +0,4 [−19,2…19,9] |
| Nướng/kho | 28 | **100%** | 2,5% | +1,2% | +4,2 [−28,3…36,6] |
| **Tổng (Variant A)** | **160** | **98,1%** (157/160) | **4,6%** | **+3,4%** | +14,1 [−40,1…68,3] |
| Tổng (Variant B) | 160 | 93,8% (150/160) | 5,1% | +3,9% | +16,0 [−46,0…78,1] |
| Chiên (Variant B) | 40 | 75,0% (30/40) | 14,3% | +14,1% | +58,0 [−10,5…126,6] |

- **Mục tiêu ≥80% ĐẠT ở lần 3** với cả hai biến thể (98,1% / 93,8%). Variant B thấp hơn A ở
  nhóm chiên (75,0% so với 92,5%) — bản thân điều này là một bằng chứng: quy dầu theo *dòng*
  (vỏ bột mang hệ số riêng) khớp thực tế món Việt (nhiều món vỏ dày) tốt hơn quy hết về
  nguyên liệu chính.
- Tham chiếu nằm trong khoảng [min, max] engine công bố: **40/40 món chiên** (lần 2 không
  tính chỉ số này cho nhóm chiên; bốn nhóm kia giữ nguyên kết quả lần 2).
- Ba món chiên còn ngoài ±20% ở Variant A: `nem-chua-ran` +28,6%, `ca-tram-sot-chua-ngot`
  +26,2%, `ca-chim-sot-ca-chua` +21,9% — đều là món mà *một nhóm hệ số duy nhất* chiếm gần
  hết dầu mô hình (nem chua; cá nguyên con chiên trần rồi sốt), xem §5–§6.

### Phân rã bias nhóm chiên (+12,2%) — phần lớn không nằm ở hệ số dầu

| Thành phần | Đóng góp |
|---|---|
| (a) **Cấu trúc điểm giữa khoảng**: engine công bố điểm giữa của khoảng đã nới không đối xứng (×0,80/×1,25 cho món chiên, ×0,85/×1,15 cho khẩu phần chưa cân) — điểm giữa nằm **trên** ước lượng trung tâm của chính engine | **+9,6 điểm %** |
| (b) Dầu mô hình engine − dầu tham chiếu độc lập (ròng sau khi các nhóm thừa/thiếu bù nhau) | +3,9 điểm % |
| (c) Phần base (hiệu chỉnh xơ, làm tròn) | −1,3 điểm % |

Tức là: ngay cả khi hệ số dầu của engine *đúng tuyệt đối*, phép đo tại điểm giữa vẫn cho
nhóm chiên lệch khoảng **+9–10%** theo thiết kế khoảng. Đây là tính chất của *cách công bố
số* (QT-5: khoảng rộng và lệch lên cho món chiên), không phải sai số của mô hình calo —
và nó cũng giải thích vì sao bias nhóm chiên không về 0 được ở lần 4 (§6). Không đề xuất
đổi hệ số khoảng trong đợt này: đó là quyết định thiết kế riêng của CALORIE-ENGINE §4–§5,
tác động mọi nhóm, nằm ngoài phạm vi QA-002.

### Phân rã dầu theo nhóm hệ số (40 món chiên, engine so với tham chiếu A)

| Nhóm CALC-004 | Hệ số engine | Khối lượng mẫu | Dầu engine | Dầu tham chiếu A | Engine/TC | Hệ số TC suy ra |
|---|---|---|---|---|---|---|
| Thịt/đạm | 8 | 1.045 g | 83,6 g (753 kcal) | 42,4 g (382 kcal) | **1,97** | ~4,1 |
| Củ/quả | 12 | 390 g | 46,8 g (421 kcal) | 35,8 g (322 kcal) | **1,31** | ~9,2 |
| Hải sản | 9 | 1.932 g | 173,9 g (1.565 kcal) | 135,2 g (1.217 kcal) | 1,29 | 7,0 (theo dòng) |
| Bột gạo/tinh bột áo | 12 | 547 g | 65,6 g (591 kcal) | 79,3 g (714 kcal) | 0,83 | ~14,5 |
| Bột/vỏ chiên ngập | 15 | 499 g | 74,9 g (674 kcal) | 84,0 g (756 kcal) | 0,89 | ~16,8 |
| Phô mai | 10 | 70 g | 7,0 g (63 kcal) | 8,8 g (79 kcal) | 0,79 | 12,6 |

## 5. Rà soát hệ số CALC-004 — phán quyết theo nhóm

**Ngưỡng đăng ký trước khi xem kết quả:** một nhóm bị coi là "lệch hệ thống rõ rệt" khi
(i) tỉ lệ dầu engine/tham chiếu nằm ngoài [0,80; 1,25] **và** (ii) nhóm đủ vật chất — là
chủ thể trong ≥3 món mẫu hoặc đóng góp ≥10% tổng dầu engine của mẫu chiên. Hệ số mới (nếu
sửa) = làm tròn từ giá trị trung tâm của neo độc lập, kèm trích dẫn.

| Nhóm | Phán quyết | Bằng chứng |
|---|---|---|
| **Thịt/đạm: 8 → 4** | ✅ **SỬA** | Tỉ lệ 1,97 — engine gấp đôi tham chiếu. Ba neo SR độc lập đều thấp: gà không da chiên 3,30; gà ức+cả da áo bột 2,30; áo bột mì mỏng **−3,41** (mất béo ròng). Trứng có cặp trực tiếp 4,19 — cùng về ~4 nên cả nhóm (gồm `trung-ga`, `trung-cut`) dùng chung 4. Giá trị 8 cũ neo vào "uptake đùi gà 8,53%" — đó là điểm đo trong môi trường oleogel giảm hút của một nghiên cứu riêng lẻ, không phải mức điển hình của thịt chiên ngập. |
| **Củ/quả: 12 → 9** | ✅ **SỬA** | Tỉ lệ 1,31. Neo: fries hai chuỗi 8,07/8,40; plantain xanh 9,77, vàng 6,53; par-fried 10,74 là cận trên. 9 là trung tâm thận trọng của dạng *miếng*; dạng lát mỏng (chips 10,81) dùng chung slug `khoai-tay` nên 9 là giá trị dung hoà — món `bim-bim` sau sửa lệch −3,3% so với tham chiếu riêng dạng chips (§6), vẫn trong ngưỡng. |
| **Hải sản: giữ 9** | ⚠️ **Chưa đủ bằng chứng sửa** | Hai cách đọc mâu thuẫn nhau: theo dòng (A) tỉ lệ 1,29 — engine cao; theo hệ thống (B) hệ số 9,9 của tham chiếu ≈ 9 của engine — khớp. Với cá áo bột (đa số món hải sản chiên), tổng engine (thịt 9 + vỏ 15) so với hệ thống độc lập 9,84–11,72 chỉ chênh +4–18% tuỳ tỉ lệ vỏ — chưa phải lệch hệ thống rõ rệt. Điểm yếu thật nằm ở **cá chiên trần nguyên con** (neo mực áo mỏng 5,43; cá không áo sẽ thấp hơn nữa): hai món cá sốt là 2/3 món còn lệch ở lần 3 và cả 2 món còn lệch ở lần 4. Ghi hồ sơ mở: nếu sau này tách hệ số "hải sản chiên trần" (~6) khỏi "áo bột" thì xử lý được đúng hai món này; chưa tách vì bằng chứng mới có 1–2 neo gián tiếp (QT-1: không bịa hệ số từ một neo). |
| **Phô mai: giữ 10** | ⚠️ Chưa đủ bằng chứng | Tỉ lệ 0,79 (engine hơi thấp), nhưng n = 1 món, 1 nguyên liệu trong toàn kho; hệ thống neo 12,56 gồm cả phần vỏ theo tỉ lệ que Mỹ, chưa chắc đúng tỉ lệ vỏ của `pho-mai-que` Việt. Món này ở lần 4 lệch +17,2% — trong ngưỡng. |
| **Bột/vỏ chiên ngập: giữ 15** | ✅ Giữ (khớp) | Tỉ lệ 0,89 — trong dải. Neo vỏ quy từ hệ phô mai que (20,83) cao hơn 15, nhưng đó là *vụn bánh khô*; batter bột mì ướt hút ít hơn — engine 15 nằm giữa hai dạng, và các món bánh bột (rán/cam/tiêu/gối) ở Variant A lệch chỉ −3,5…+4,3%. |
| **Bột gạo/tinh bột áo: giữ 12** | ✅ Giữ (khớp) | Tỉ lệ 0,83 — trong dải, engine hơi thấp. Lưu ý cũ của CALC-004 vẫn đúng chiều ngược lại cho một ca: bột chiên (đã nấu chín, áp chảo) thì 12 lại *cao* — nhưng đó là một món (`bot-chien`, lệch +17,6% lần 3, trong ngưỡng) và neo tham chiếu cho ca này là giả định yếu nhất (§3). Chưa đủ cơ sở tách hệ số riêng. |

### Hai món ngoại lệ (gạo nếp đã nấu, hệ số 0): **GIỮ ngoại lệ**

- **Bằng chứng thực nghiệm:** `xoi-chien` lệch +6,2%, `banh-chung-chien` +8,4% so với tham
  chiếu lần 3 — cả hai đều trong ±20% và tham chiếu nằm trong khoảng engine. Cơ chế hiện
  tại (dầu kê tường minh trong công thức, engine không cộng mô hình) cho số đúng.
- **Rủi ro hệ thống nếu gán hệ số:** `gao-nep`/`com-trang` xuất hiện trong rất nhiều món
  không chiên và món cơm chiên mà dầu đã là dòng riêng; gán hệ số cho chúng sẽ hoặc tính
  hai lần ở các món đó, hoặc buộc phải rà lại toàn bộ — đổi lớn, bằng chứng được thêm lại
  mỏng (không có cặp SR cho xôi/bánh chưng chiên).
- Kết luận: giữ thiết kế CALC-004 (nhóm 0 "dầu đã kê"), hai món tiếp tục là ngoại lệ có
  ghi chú trong công thức như hiện tại.

## 6. Thay đổi đã áp và lần đo 4 (kiểm tra hiệu chuẩn)

**Thay đổi dữ liệu (11 file `data/ingredients/*.json`, chỉ đổi `oil_absorption_pct`):**
nhóm thịt/đạm 8 → 4 (`thit-ga-co-da`, `thit-lon-xay`, `thit-lon-ba-chi`, `thit-de`,
`nem-chua`, `trung-ga`, `trung-cut`); nhóm củ/quả 12 → 9 (`khoai-tay`, `khoai-lang`,
`khoai-mon`, `chuoi`). Hệ quả phụ đã kiểm: phán quyết bim bim ở CALC-004 nay cho
40 g × 9% = 3,6 g dầu (thay cho 4,8 g ở hệ số 12) — món vẫn đạt −3,3% so với tham chiếu
dạng chips 10,81, nên phán quyết "bỏ dòng dầu, hệ số đảm nhiệm" vẫn đứng.
Kiểm chứng sau sửa: validate-data **964 bản ghi, 0 🔴**; `recompute-calories
--with-ingredients` **491/496 món**; Pest **106/106**.

| Nhóm | n | Lần 3 (trước sửa) | Lần 4 (sau sửa) |
|---|---|---|---|
| Chiên — trong ±20% | 40 | 92,5% | **95,0%** (38/40) |
| Chiên — MAPE / bias | 40 | 12,3% / +12,2% | **9,2% / +8,8%** |
| Chiên — Bland–Altman | 40 | +50,2 [−8,0…108,4] | **+37,3 [−17,4…92,0]** |
| **Tổng — trong ±20%** | 160 | 98,1% | **98,8%** (158/160) |
| Tổng — MAPE / bias | 160 | 4,6% / +3,4% | **3,8% / +2,6%** |

- Món cải thiện rõ nhất: `nem-chua-ran` +28,6% → **+10,5%**; `com-ga-xoi-mo` +17,1% →
  +10,9%; `khoai-lang-ken` +11,3% → +4,0%; `chuoi-chien` +9,0% → +2,0%.
- **Hai món còn ngoài ±20%** — không đổi so với lần 3 vì công thức của chúng không chứa
  dòng thịt/đạm hay củ nào: `ca-tram-sot-chua-ngot` +26,2% và `ca-chim-sot-ca-chua`
  +21,9%. Cả hai là cá nguyên con chiên trần khẩu phần lớn (200 g) rồi sốt: gồm ~+9,6 điểm
  % cấu trúc điểm giữa (§4) cộng phần hải sản chiên trần mà §5 đã kết luận *chưa đủ bằng
  chứng* để sửa ở cấp nhóm. Đây là tồn dư đã biết, có hồ sơ, không phải lỗi mới.
- Bias nhóm chiên còn +8,8% ở lần 4 — xấp xỉ đúng mức sàn cấu trúc +9,6 điểm % trừ phần
  base −1,3: tức sau hiệu chuẩn, **phần dầu của engine đã khớp tham chiếu độc lập trong
  sai số đo**, phần lệch còn lại nằm ở cách nới khoảng (ngoài phạm vi đợt này).
- Đọc lần 4 cho đúng: hệ số mới được chọn *từ* tham chiếu A, nên lần 4 chỉ chứng minh việc
  sửa có hiệu lực như tính toán và không phá các nhóm khác (bốn nhóm không chiên không đổi
  số nào). Giá trị kiểm chứng độc lập nằm ở **lần 3**. Lần kiểm chứng độc lập tiếp theo
  phải dùng bộ mẫu/neo khác (hoặc dữ liệu phân tích ngoài) mới có thêm thông tin.

## 7. Kết luận so với mục tiêu (DEC-001)

- **Mục tiêu ≥80% món trong ±20%: ĐẠT** — lần 3: **98,1%** (tham chiếu độc lập), lần 4 sau
  hiệu chuẩn: **98,8%**. Định nghĩa không thay đổi ở bất kỳ lần đo nào.
- Tuyên bố ra ngoài vẫn bị ràng buộc bởi các giới hạn đã ghi ở §1 và ACCURACY-SPEC §6:
  con số này là "engine so với tham chiếu tính từ bảng thành phần của dự án + mô hình dầu
  neo USDA", **không** phải độ chính xác so với phân tích hoá học. Khi trích dẫn phải kèm
  định nghĩa ±20% và bộ mẫu 160 món.

## 8. Việc để mở (có hồ sơ, không tự xử trong đợt này)

1. **Hải sản chiên trần** — cân nhắc tách hệ số riêng (~6) khi có thêm neo độc lập; hai món
   cá sốt là ca kiểm chứng sẵn có.
2. **Phô mai (n=1)** và **bột chiên áp chảo** — chờ thêm dữ liệu/món trước khi kết luận.
3. **Cấu trúc điểm giữa khoảng món chiên (+9,6 điểm %)** — nếu chủ dự án muốn bias điểm
   giữa về ~0, đó là thay đổi thiết kế khoảng ở CALORIE-ENGINE §4–§5, cần quyết riêng.
4. Nhóm hấp −21% ở bộ FNDDS (Q7) — ngoài phạm vi QA-002, giữ nguyên trạng thái mở.

---

## Phụ lục A — 40 món chiên, lần 3 Variant A (kcal/khẩu phần)

| Món | Engine (điểm giữa) | Tham chiếu base | + Dầu TC | Tham chiếu 3 | Lệch |
|---|---|---|---|---|---|
| nem-chua-ran | 378,5 | 244,0 | 50 | 294,4 | **+28,6%** |
| ca-tram-sot-chua-ngot | 391,1 | 228,9 | 81 | 309,8 | **+26,2%** |
| ca-chim-sot-ca-chua | 553,4 | 328,0 | 126 | 454,0 | **+21,9%** |
| pho-mai-que | 667,0 | 479,2 | 85 | 564,3 | +18,2% |
| com-chay-ninh-binh | 397,1 | 310,9 | 25 | 336,1 | +18,2% |
| bot-chien | 667,1 | 472,9 | 94 | 567,4 | +17,6% |
| muc-nhoi-thit | 604,4 | 383,4 | 131 | 514,3 | +17,5% |
| ca-keo-chien-gion | 394,5 | 196,3 | 140 | 336,7 | +17,2% |
| com-ga-xoi-mo | 659,0 | 529,9 | 33 | 562,8 | +17,1% |
| ca-bong-chien-gion | 416,9 | 216,1 | 140 | 356,5 | +17,0% |
| ca-linh-chien-gion | 360,2 | 192,4 | 116 | 308,5 | +16,8% |
| bun-dau-mam-tom | 724,6 | 601,3 | 22 | 622,9 | +16,3% |
| ca-me-chien | 304,5 | 186,8 | 76 | 262,7 | +15,9% |
| ca-ro-dong-chien-xu | 583,5 | 342,0 | 164 | 506,1 | +15,3% |
| ca-tram-chien-gion | 267,9 | 149,9 | 83 | 233,0 | +15,0% |
| com-rang | 660,2 | 555,7 | 19 | 574,6 | +14,9% |
| com-chien-ca-man | 529,6 | 443,1 | 19 | 462,0 | +14,6% |
| chao-tom | 495,7 | 331,9 | 102 | 433,9 | +14,2% |
| banh-re | 338,6 | 225,5 | 71 | 296,6 | +14,2% |
| banh-bao-chien | 447,9 | 319,9 | 75 | 394,6 | +13,5% |
| bim-bim | 78,8 | 30,8 | 39 | 69,7 | +13,1% |
| cha-ca-thac-lac | 424,6 | 285,4 | 92 | 377,2 | +12,6% |
| nem-ran | 383,5 | 290,4 | 52 | 342,0 | +12,1% |
| cha-muc | 296,4 | 177,2 | 89 | 265,8 | +11,5% |
| khoai-lang-ken | 436,7 | 275,4 | 117 | 392,4 | +11,3% |
| chuoi-chien | 402,2 | 236,6 | 132 | 368,9 | +9,0% |
| banh-xeo-mien-tay | 505,2 | 378,6 | 90 | 468,2 | +7,9% |
| banh-xeo | 703,5 | 529,2 | 123 | 652,0 | +7,9% |
| banh-tom-ho-tay | 527,5 | 330,0 | 162 | 492,1 | +7,2% |
| com-chien-trung-muoi | 616,6 | 551,9 | 26 | 577,7 | +6,7% |
| banh-gia | 400,6 | 276,7 | 100 | 376,6 | +6,4% |
| banh-cong | 537,1 | 390,6 | 115 | 505,8 | +6,2% |
| xoi-chien (ngoại lệ) | 878,3 | 827,2 | 0 | 827,2 | +6,2% |
| banh-ran-duong | 396,2 | 307,0 | 73 | 379,9 | +4,3% |
| banh-cam | 455,2 | 342,8 | 94 | 436,8 | +4,2% |
| banh-ran | 417,2 | 327,9 | 73 | 400,8 | +4,1% |
| banh-quai-vac | 534,2 | 385,1 | 129 | 513,8 | +4,0% |
| banh-tieu | 350,4 | 254,1 | 109 | 363,0 | −3,5% |
| banh-xeo-chay | 457,4 | 366,2 | 79 | 445,4 | +2,7% |
| banh-goi | 523,7 | 387,4 | 125 | 512,7 | +2,2% |

## Phụ lục B — Ghi chú tái lập

- Cả hai phía của lần 3 đo từ cùng một snapshot `data/` (nền commit `ebd92bd` + các sửa
  tầm soát đã kiểm chứng không chạm công thức của 160 món mẫu; việc gộp món ở commit
  `2dacc57` không đổi công thức món nào trong mẫu). Lần 4 đo trên `data/` sau khi sửa hệ
  số, DB dựng lại bằng `migrate:fresh --seed` + `recompute-calories --with-ingredients`.
- Phía engine lấy từ `dish_nutrition` đã lưu; replica thuật toán đối chiếu khớp 496/496
  món (trước sửa) và 496/496 (sau sửa) trong ±0,5% điểm giữa.
- Tham chiếu base (phần không phải dầu) giữ đúng phương pháp lần 2: cộng công thức nguyên
  văn từ giá trị file nguyên liệu, đã nhân hệ số phần ăn được, tính cả dòng tuỳ chọn, không
  hiệu chỉnh xơ, không mô hình dầu — tái lập đúng toàn bộ số liệu lần 2 trước khi cộng dầu.
