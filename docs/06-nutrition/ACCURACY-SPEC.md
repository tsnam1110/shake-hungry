# Đặc tả độ chính xác calo — Lắc Đói

**Cập nhật lần cuối:** 2026-08-31 — chạy phép đo đầu tiên trên FNDDS (TEST-002); đính chính mô tả Nutrition5k
**Trạng thái:** Định nghĩa đã chốt. **Đã chạy lần đầu 2026-08-31** trên bộ FNDDS — xem §6.

---

## 1. Vấn đề với mục tiêu ban đầu

Mục tiêu do chủ dự án đặt ra: *"các món ăn phải có ước lượng calo đúng > 80%"*.

Câu này **chưa phải một mục tiêu đo được**, vì thiếu ba thứ: đúng so với cái gì, sai lệch bao nhiêu
thì coi là "đúng", và đo trên mẫu nào.

Tài liệu này biến nó thành thứ đo được, và ghi rõ ngưỡng nào là khả thi, ngưỡng nào không.

---

## 2. Định nghĩa đã chốt

> **Mục tiêu độ chính xác:** ≥ 80% số món trong bộ mẫu kiểm chứng có sai lệch năng lượng **≤ ±20%**
> so với **giá trị tham chiếu**, trong đó giá trị tham chiếu là giá trị tính theo hướng dẫn
> FAO/INFOODS từ một bảng thành phần thực phẩm được chỉ định, do người có chuyên môn thực hiện độc
> lập với engine.

Ba thành phần, không được bỏ cái nào:

| Thành phần | Giá trị |
|---|---|
| Ngưỡng sai lệch mỗi món | **±20%** |
| Tỷ lệ món phải đạt | **≥80%** |
| Giá trị tham chiếu | Tính thủ công theo FAO/INFOODS từ một bảng thành phần duy nhất |

### Những định nghĩa KHÔNG khả thi — đừng hứa

| Định nghĩa | Vì sao không đạt được |
|---|---|
| "±10% mỗi món so với phân tích hoá học" | Không có tài liệu nào cho thấy đạt được. Kể cả **nhãn calo bắt buộc trên menu nhà hàng Mỹ** cũng không đạt: đo thực tế cao hơn công bố **trung bình ~18%**, và **~19% số món lệch ≥100 kcal** |
| "Chính xác 80%" không kèm định nghĩa | Không kiểm chứng được, và là **tuyên bố tiếp thị không chứng minh nổi** nếu có ai hỏi |
| "Chính xác hơn MyFitnessPal" | Không đo được nếu không có cùng bộ mẫu và cùng phương pháp |

**Định nghĩa phải được in ngay trong ứng dụng**, ở màn hình giải thích cách tính calo. Không giấu
trong tài liệu kỹ thuật.

---

## 3. Bằng chứng nền — vì sao ±20% là mức hợp lý

### 3.1 Phát hiện quan trọng nhất: sai số nằm ở ánh xạ, không ở phép tính

Tổng quan hệ thống + phân tích gộp **14 nghiên cứu thẩm định / 12 ứng dụng** (2013–2019):

| Chỉ số | Kết quả |
|---|---|
| Chênh lệch năng lượng gộp so với tham chiếu | **−202 kcal/ngày** (KTC 95%: −319 đến −85), I² = 72% |
| Bột đường / béo / đạm | −18,8 g · −12,7 g · −12,2 g mỗi ngày |
| **Khi app và tham chiếu dùng CÙNG một bảng thành phần** | **−57 kcal/ngày** (KTC 95%: −116 đến +2), **độ phân tán 0%** |

Tất cả các app đều **ước lượng thiếu**.

⇒ **Phần lớn cái gọi là "app tính calo không chính xác" thực chất là lỗi ánh xạ nguyên liệu sang sai
bảng dữ liệu, không phải lỗi số học.** Đây là cơ sở trực tiếp của quy tắc QT-4 (một bảng thành phần
duy nhất cho mỗi phép tính). Nếu dự án tuân thủ QT-4, nó bắt đầu từ vị thế tốt hơn hầu hết app hiện có.

### 3.2 Sai số khi tính món ghép từ nguyên liệu

Nghiên cứu Đan Mạch so sánh đồ ăn nhanh **phân tích hoá học** với **tính từ công thức**:

| Cơ sở dữ liệu nguyên liệu | Sai số chất béo bão hoà |
|---|---|
| FOODCOMP (cũ) | 28% |
| FRIDA (mới) | **11%**, và <15% cho mọi chất |

⇒ **Chất lượng bảng thành phần quyết định, không phải thuật toán.** Đạm/béo/bột đường ước lượng tốt
nhất; chất xơ, natri, vitamin C/D, thiamin, riboflavin ước lượng kém nhất.

Đây là lý do v1 **chỉ cam kết về năng lượng và ba chất sinh năng lượng**, không cam kết về vi chất.

### 3.3 Các nguồn sai số, xếp theo độ lớn

1. **Hệ số hao hụt khi nấu** — áp trước mọi phép tính, sai là sai toàn bộ.
2. **Dầu hấp thụ khi chiên** — lớn nhất với món Việt. Xem [CALORIE-ENGINE §4](CALORIE-ENGINE.md).
3. **Hệ số giữ lại chất dinh dưỡng** — nhỏ với năng lượng, lớn với vi chất.
4. **Ánh xạ nguyên liệu sai dòng** (nhầm phần thịt, độ mỡ, thương hiệu) — được ghi nhận là nguồn sai
   số lớn nhất trong tính toán công thức.
5. **Người dùng ước lượng sai khẩu phần** — thường là sai số lớn nhất trên thực tế, **nhưng nằm
   ngoài phạm vi engine**. Dự án không chịu trách nhiệm phần này, nhưng phải nói rõ với người dùng.

---

## 4. Quy trình đo — chạy ở cuối GĐ3 và sau mỗi lần đổi engine

### Bước 1 — Chọn bộ mẫu

- **100–200 món**, phân tầng theo **cách chế biến**.
- **Bắt buộc có nhóm chiên/rán** — đây là chỗ sai số lớn nhất, bỏ qua là tự lừa mình.
- Phân bổ đề nghị: luộc/hấp 20% · xào 20% · **chiên/rán 25%** · nấu/canh 20% · nướng/kho 15%.

### Bước 2 — Dựng giá trị tham chiếu

- Người thực hiện **không dùng engine**, tính thủ công theo FAO/INFOODS.
- Dùng **một** bảng thành phần duy nhất: USDA FDC, hoặc Bảng Thành phần Thực phẩm Việt Nam nếu đã
  xin được phép ([LEGAL-001](../05-tasks/tasks/LEGAL-001.md)).
- Áp hệ số hao hụt và hấp thụ dầu một cách tường minh, ghi lại từng bước.

### Bước 3 — Kiểm chứng phụ trợ bằng dữ liệu mở

Hai bộ dữ liệu trong danh sách trắng cho phép kiểm engine **trước khi** có người tính tay:

| Bộ dữ liệu | Cách dùng | License |
|---|---|---|
| **USDA FNDDS + SR Links** | Với mỗi Food Code, chạy engine trên danh sách nguyên liệu của nó, so với giá trị dinh dưỡng USDA công bố cho chính món đó. Lệch nhiều ⇒ **engine sai**, không phải dữ liệu sai | CC0 |
| **Nutrition5k** (Google Research) | ~5.000 món thật, nguyên liệu + khối lượng đo bằng thiết bị. ⚠️ **Calo KHÔNG đo độc lập** — xem đính chính bên dưới | CC BY 4.0 (ghi công) |

> ## ⚠️ Đính chính 2026-08-31 — mô tả Nutrition5k ở trên SAI
>
> Bản đầu ghi Nutrition5k có *"calo **đo bằng thiết bị**, không phải ước lượng"* và gọi nó là
> *"gần với sự thật nhất"*. **Kiểm tra thực tế trên toàn bộ 4.766 món cho thấy điều đó không
> đúng:** `total_calories` bằng đúng tổng (gram × cal/g) tra từ chính `ingredients_metadata.csv`
> của bộ — **100% số món khớp dưới 0,5 kcal, sai lệch trung vị 0,00%**. Thứ được đo bằng thiết
> bị là **khối lượng**; calo là **tính ra** từ một bảng thành phần.
>
> **Hệ quả:** chạy engine trên chính các nguyên liệu đó rồi so với `total_calories` là một phép
> thử **vòng tròn**. Nó chỉ chứng minh engine cộng đúng về số học — **không nói gì** về chất
> lượng dinh dưỡng. Kết quả của nó phải được gọi là *sanity check*, và **tuyệt đối không được
> trích như "độ chính xác của ứng dụng"** (§5).
>
> Muốn Nutrition5k có giá trị kiểm chứng thật, phải **ánh xạ 555 nguyên liệu của nó sang bảng
> thành phần của dự án** rồi mới so — khi đó hai vế mới độc lập. Đó là việc chưa làm.
>
> **Bài học:** kiểm tra giả định về bộ dữ liệu **bằng chính dữ liệu** trước khi thiết kế phép đo
> quanh nó. Câu "đo bằng thiết bị" đọc rất thuyết phục và đã suýt tạo ra một con số vô nghĩa.

⇒ Bộ kiểm chứng thật sự hiện có **chỉ là FNDDS**. Chạy nó **trước** khi tốn công người tính tay.
Nếu engine đã sai trên FNDDS thì không cần đo tiếp — sửa engine đã.

### Bước 4 — Báo cáo

Không báo cáo một con số "độ chính xác". Báo cáo **bốn** chỉ số:

| Chỉ số | Ý nghĩa |
|---|---|
| **% món trong ±20%** | Chỉ số chính, đối chiếu mục tiêu ≥80% |
| **MAPE** (sai số phần trăm tuyệt đối trung bình) | Độ lớn sai số điển hình |
| **Sai lệch trung bình (bias)** | Engine ước lượng thừa hay thiếu một cách hệ thống? |
| **Bland–Altman với giới hạn đồng thuận 95%** | Sai số có tăng theo món nhiều calo không? |

Tách kết quả theo cách chế biến. Một engine đạt 85% tổng thể nhưng chỉ 55% ở món chiên là một engine
có lỗi cụ thể cần sửa, không phải một engine đạt mục tiêu.

### Bước 5 — Ghi kết quả

Vào `docs/06-nutrition/accuracy-runs/YYYY-MM-DD.md`, kèm: bộ mẫu, `engine_version`, bảng thành phần
đã dùng, đủ bốn chỉ số, và phân tách theo cách chế biến. Cập nhật bảng §6.

---

## 5. Điều KHÔNG được tuyên bố

| Cấm nói | Vì |
|---|---|
| "Calo chính xác 100%" | Không thể, kể cả phân tích hoá học cũng có sai số |
| "Chính xác >80%" mà không kèm định nghĩa ±20% và bộ mẫu | Tuyên bố không chứng minh được |
| "Chính xác hơn app X" | Chưa đo cùng phương pháp |
| Bất kỳ tuyên bố nào về **giảm cân, chữa bệnh, cải thiện sức khoẻ** | Vượt khỏi phạm vi và chạm vào quy định quảng cáo — xem [NGU-HANH-AM-DUONG §5](../08-cultural/NGU-HANH-AM-DUONG.md) |

Và ngược lại, **phải nói rõ** với người dùng: con số này là ước lượng từ công thức tiêu chuẩn; khẩu
phần thực tế và cách nấu của từng quán/từng nhà làm thay đổi kết quả.

---

## 6. Nhật ký đo

| Ngày | engine_version | Bộ mẫu | % trong ±20% | MAPE | Bias | Đạt mục tiêu? | Báo cáo |
|---|---|---|---|---|---|---|---|
| 2026-08-31 | 1.0.0 | 204 Food Code FNDDS (`usda_sr_legacy`) | **89,7%** | 3,9% | −3,8% | ✅ tiêu chí chính đạt; **nhóm chiên chưa đo được** | [2026-08-31-fndds.md](accuracy-runs/2026-08-31-fndds.md) |
| 2026-08-31 | 1.0.0 | 4.766 món Nutrition5k | 100% | 0,04% | +0,01% | ⚠️ **sanity check vòng tròn** — không phải độ chính xác (xem §4) | — |
| 2026-09-01 | **1.1.0** | 204 Food Code FNDDS (`usda_sr_legacy`) | **89,7%** | 3,9% | −3,8% | ✅ tiêu chí chính giữ nguyên; **% tham chiếu trong khoảng 1,0% → 80,4%** | [2026-09-01-fndds.md](accuracy-runs/2026-09-01-fndds.md) |
| 2026-10-31 | **1.1.0** | **160 món Việt của dự án, phân tầng theo cách chế biến (QA-002)** | **76,9%** | 11,5% | **+10,3%** | ❌ **CHƯA ĐẠT** — 4/5 nhóm đạt 100%; nhóm chiên/rán chỉ 7,5% (bias +39,8%): dầu bị tính hai lần (dòng `dau-an` trong công thức + mô hình hấp thụ CALC-004) | [2026-10-31-vn-dishes.md](accuracy-runs/2026-10-31-vn-dishes.md) |
| 2026-10-31 | **1.1.0** (không đổi) | **160 món Việt, cùng thiết kế mẫu (QA-002 lần 2 — sau Phương án A: bỏ/giảm dòng dầu chiên trong công thức)** | **77,5%** | 13,8% | **+12,6%** | ❌ **CHƯA ĐẠT** — 4/5 nhóm vẫn 100%; nhóm chiên/rán 10,0% (bias +49,0%): chênh lệch tuyệt đối nhóm chiên gần như không đổi (+143,7 → +137,0 kcal) vì phía tham chiếu nguyên văn không chứa dầu hấp thụ mà engine mô hình hoá — cặp (quy ước A + tham chiếu nguyên văn) không tương thích về định nghĩa; chờ chủ dự án chốt bước tiếp (báo cáo §7) | [2026-10-31-vn-dishes-lan-2.md](accuracy-runs/2026-10-31-vn-dishes-lan-2.md) |
| 2026-10-31 | **1.1.0** (không đổi) | **160 món Việt, cùng mẫu (QA-002 lần 3 — tham chiếu chiên ĐỘC LẬP: cộng dầu hấp thụ bằng hệ số suy từ cặp sống→chiên USDA SR Legacy, không dùng hệ số engine, không dùng 2 nguồn neo của CALC-004)** | **98,1%** | 4,6% | **+3,4%** | ✅ **ĐẠT** — 4/5 nhóm vẫn 100%; nhóm chiên 92,5% (bias +12,2%, trong đó +9,6 điểm % là cấu trúc điểm giữa khoảng; tham chiếu nằm trong khoảng engine 40/40 món chiên). Biến thể đối chứng (quy dầu về hệ thống): 93,8% tổng, chiên 75,0% — vẫn đạt | [2026-10-31-vn-dishes-lan-3.md](accuracy-runs/2026-10-31-vn-dishes-lan-3.md) |
| 2026-10-31 | **1.1.0** (không đổi; chỉ sửa hệ số dữ liệu CALC-004) | **160 món Việt, cùng mẫu + cùng tham chiếu độc lập (QA-002 lần 4 — sau hiệu chuẩn: thịt/đạm 8→4, củ/quả 12→9)** | **98,8%** | 3,8% | **+2,6%** | ✅ **ĐẠT** (kiểm tra hiệu chuẩn — không phải kiểm chứng độc lập mới) — nhóm chiên 95,0% (38/40), bias +8,8% ≈ sàn cấu trúc điểm giữa; 2 món còn lệch đều là cá chiên trần sốt, đã ghi hồ sơ mở (hải sản chiên trần) | [2026-10-31-vn-dishes-lan-3.md](accuracy-runs/2026-10-31-vn-dishes-lan-3.md) |
| 2026-11-01 | **1.1.0** (không đổi) | **160 món Việt MỚI, seed 20261101 (QA-002 lần 5 — kho cuối 654 món sau LV2 + A1–A4 + AUDIT-FIX; tham chiếu độc lập giữ nguyên phương pháp lần 3/4)** | **98,1%** | 4,37% | **+2,61%** | ✅ **ĐẠT trên kho cuối** — 4 nhóm không chiên 100% (120/120); nhóm chiên 92,5% (37/40), bias +9,33% mà cấu trúc điểm giữa chiếm +9,54 điểm % (dư −0,21 điểm %: không lệch hệ thống, **không hiệu chuẩn**); tham chiếu lọt khoảng công bố 160/160; 3 món lệch đều có nguyên nhân cấu trúc (§6 lần đo 7) | [2026-11-01-vn-dishes-lan-5.md](accuracy-runs/2026-11-01-vn-dishes-lan-5.md) |
| 2026-11-02 | **1.1.0** (không đổi) | 204 Food Code FNDDS — **tái lập baseline** trước vòng thử Q7 (giả định bảo toàn khối lượng như lần đo 1) | **89,7%** | 3,9% | −3,8% | ✅ tái lập khớp lần đo 1 (nhóm `hap` vẫn 6,7% trong ±20%, bias −20,6%, hệ số KL ngầm 0,79) — bộ máy đo ổn định | [2026-11-02-fndds.md](accuracy-runs/2026-11-02-fndds.md) |
| 2026-11-02 | **1.1.0** (không đổi; thêm tuỳ chọn đo `--seafood-yield`) | 204 Food Code FNDDS — **vòng thử Q7/T-09**: hệ số hao hụt cá vây hấp/luộc **0,84 từ FSANZ** (độc lập với USDA, đăng ký trước theo tiền lệ CALC-004; tôm/sò cố ý KHÔNG áp) | **95,6%** | 3,0% | −2,8% | ✅ **CHẨN ĐOÁN Q7 ĐƯỢC XÁC NHẬN** — nhóm `hap`: 6,7% → **86,7%** trong ±20% (13/13 món cá vây đạt), bias −20,6% → −7,5%, hệ số KL ngầm 0,79 → 0,94; tôm + sò giữ nguyên −21% (chưa có hệ số riêng đạt chuẩn — hồ sơ mở); các nhóm khác không đổi | [2026-11-02-q7-fndds.md](accuracy-runs/2026-11-02-q7-fndds.md) |

**Đọc bảng này cho đúng.** Lần đo đầu tiên nói được ba điều, không hơn:

1. **Engine không có lỗi số học.** MAPE 0,04% trên 4.766 món ở phép thử vòng tròn, và 89,7% số món
   FNDDS trong ±20% — trong đó bốn nhóm `song`/`nau`/`nuong`/`luoc` đạt 94–100%.
2. **Hai nhóm lệch có nguyên nhân đã xác định, không phải lỗi thuật toán.** Nhóm `hap` lệch
   **−21% đồng loạt** (hệ số khối lượng ngầm định 0,79) vì phép đo giả định khối lượng thành phẩm
   bằng khối lượng nguyên liệu — cá hấp mất ~21% nước. Đây là **thiếu yield factor**, đúng thứ
   [CALORIE-ENGINE §3](CALORIE-ENGINE.md) mô tả.
3. **Nhóm chiên/rán CHƯA được kiểm chứng lần nào.** Engine từ chối tính mọi món chiên vì nguyên
   liệu thiếu `oil_absorption_pct` (đúng thiết kế CALC-002 — không bịa hệ số). Vì đây là nhóm sai
   số lớn nhất với món Việt, **TEST-002 chưa được coi là hoàn tất** dù tiêu chí chính đã đạt.

### Lần đo 2 (2026-09-01, engine 1.1.0) — sửa khoảng, KHÔNG sửa điểm

Lần này chỉ đổi một thứ: thêm bất định nền ±4% vào engine ([CALORIE-ENGINE §5](CALORIE-ENGINE.md)),
vì bản 1.0.0 trả khoảng rộng bằng 0 cho món ánh xạ `high` — vi phạm QT-5.

| Chỉ số | 1.0.0 | 1.1.0 | Vì sao |
|---|---|---|---|
| % trong ±20% | 89,7% | **89,7%** | điểm giữa khoảng không đổi ⇒ tiêu chí chính không bị động tới |
| MAPE / Bias | 3,9% / −3,8% | **3,9% / −3,8%** | như trên |
| % tham chiếu nằm trong khoảng | 1,0% | **80,4%** | khoảng từ rộng-0 thành ±4% |
| `hap` / `kho` | 6,7% / 76,9% | **6,7% / 76,9%** | **cố ý không đổi** — xem dưới |

Việc `hap` và `kho` **không nhúc nhích** là điều quan trọng nhất của lần đo này, không phải con số
80,4%. Nếu nới khoảng tới ±20% thì hai nhóm đó cũng "đạt" ngay, báo cáo đẹp lên mà engine không tốt
hơn một chút nào. Khoảng tin cậy dùng để **nói ra** mình không chắc, không phải để **giấu** chỗ mình sai.

### Lần đo 3 + 4 (2026-10-31, QA-002 trên món Việt — trước và sau Phương án A)

Lần đo 3 là lần đầu đo trên kho món Việt của dự án: **76,9%**, và toàn bộ phần chưa đạt nằm ở
nhóm chiên (7,5%, bias +39,8%) — nguyên nhân: công thức kê sẵn dòng `dau-an`, engine cộng thêm
mô hình hấp thụ CALC-004. Chủ dự án chốt **Phương án A** (đổi quy ước dữ liệu: bỏ/giảm dòng
dầu chiên trong công thức, engine giữ nguyên) và đo lại ở lần 4: **77,5% — vẫn chưa đạt**,
nhóm chiên 10,0% (bias +49,0%).

Bài học của lần 4 quan trọng hơn con số: chênh lệch tuyệt đối engine ↔ tham chiếu ở nhóm chiên
gần như không đổi (+143,7 → +137,0 kcal/món), vì chênh lệch đó *chính là* dầu hấp thụ do engine
mô hình hoá, còn phía tham chiếu "công thức nguyên văn" không chứa nó ở cả hai lần. Lần 3, dòng
`dau-an` trong công thức vô tình làm đại diện cho dầu hấp thụ ở phía tham chiếu. **Định nghĩa
mục tiêu không đổi**; việc cần quyết tiếp là phương pháp dựng phía tham chiếu cho món chiên
(phương án C ở phía tham chiếu) và/hoặc độ lớn hệ số CALC-004 — đều chờ chủ dự án. Chi tiết:
[báo cáo lần 1](accuracy-runs/2026-10-31-vn-dishes.md) ·
[báo cáo lần 2](accuracy-runs/2026-10-31-vn-dishes-lan-2.md).

### Lần đo 5 + 6 (2026-10-31, QA-002 lần 3 + 4 — tham chiếu chiên độc lập, hiệu chuẩn CALC-004)

Chủ dự án duyệt cả hai bước tiếp theo của báo cáo lần 2 §7. **Lần 5 (QA-002 lần 3)** đổi đúng
một thứ ở phía đo: tham chiếu nhóm chiên cộng dầu hấp thụ tường minh bằng hệ số **suy từ dữ
liệu thành phẩm USDA SR Legacy** (cặp sống → chiên, cân bằng protein/khối lượng) — độc lập
với hệ số engine ở cả ba nghĩa: không đọc từ `oil_absorption_pct`, không suy ngược từ kết
quả engine, không dùng hai nguồn neo của CALC-004. Đường ống được kiểm chứng trước bằng
tái lập chính xác toàn bộ số lần 2. Kết quả: **98,1% — ĐẠT**, nhóm chiên 92,5%.

Ba điều lần đo này nói được mà các lần trước không nói được:

1. **Mô hình dầu của engine về tổng thể là đúng cỡ.** Khi tham chiếu cũng chứa dầu (từ
   nguồn ngoài dự án), nhóm chiên đi từ 10,0% lên 92,5% mà engine không đổi một dòng code.
   Kết luận lần 2 — "lệch là do không tương thích định nghĩa" — được xác nhận bằng số.
2. **Phần bias còn lại của nhóm chiên (+12,2%) phân rã được:** +9,6 điểm % là *cấu trúc
   điểm giữa khoảng* (khoảng món chiên nới không đối xứng ×0,80/×1,25 rồi ×0,85/×1,15 nên
   điểm giữa nằm trên ước lượng trung tâm của chính engine) — tính chất của cách công bố
   số theo QT-5, không phải sai số mô hình; chỉ +3,9 điểm % là chênh dầu ròng, và nó tập
   trung ở hai nhóm hệ số (thịt/đạm gấp đôi tham chiếu; củ/quả cao ~1,3 lần).
3. **Hiệu chuẩn có kỷ luật (lần 6 / QA-002 lần 4):** chỉ hai nhóm vượt ngưỡng đăng ký trước
   mới bị sửa (thịt/đạm 8→4, củ/quả 12→9); hải sản giữ nguyên vì bằng chứng hai cách quy
   dầu mâu thuẫn nhau. Sau sửa: **98,8%**, nhóm chiên 95,0% — và báo cáo ghi rõ lần này là
   *kiểm tra hiệu chuẩn*, giá trị kiểm chứng độc lập nằm ở lần 5. Hai món còn lệch
   (`ca-tram-sot-chua-ngot`, `ca-chim-sot-ca-chua`) đều là cá chiên trần nguyên con — hồ sơ
   mở "hải sản chiên trần", chờ thêm neo độc lập, không bịa hệ số.

> Tuyên bố ra ngoài được cập nhật theo lần đo này: trên kho món Việt của dự án, engine đạt
> **98,1% trong ±20%** (mẫu 160 món phân tầng) so với tham chiếu tính từ bảng thành phần của
> dự án cộng mô hình dầu neo USDA — **vẫn không** được rút gọn thành "chính xác 98%" và
> vẫn chưa phải đối chiếu phân tích hoá học (xem các giới hạn ở báo cáo lần 3 §1).

### Lần đo 7 (2026-11-01, QA-002 lần 5 — kiểm chứng kho cuối trên mẫu mới)

Sau khi chương trình hoàn thiện dữ liệu đóng lại (12 đợt LV2 nâng Mức 2, gói A1–A4, AUDIT-002/FIX —
hàng trăm định lượng đổi so với lần đo 4), chủ dự án yêu cầu đo lại để con số độ chính xác phản ánh
**kho hiện tại** chứ không phải kho cũ. Lần này đổi đúng một thứ: **mẫu** — 160 món mới rút phân
tầng từ hồ 559 món tính được của kho 654 (seed 20261101, phân bổ theo nhóm cách nấu và tỉ lệ Mức 1/
Mức 2 trong hồ; 127 món Mức 2). Phương pháp tham chiếu, định nghĩa DEC-001 và Variant A/B giữ nguyên
từng chữ so với lần 3/4; phía engine lấy từ DB kiểm chứng riêng dựng mới từ `data/`.

Kết quả **98,1% (157/160)** — tương đương lần 3 trên mẫu độc lập mới và xác nhận chương trình hoàn
thiện không làm suy giảm độ chính xác. Ba điểm đáng ghi:

1. **Bốn nhóm không chiên vẫn 100%** (MAPE 1,9–3,4%, bias ≈ 0): hàng trăm định lượng Mức 2 mới
   không tạo lệch hệ thống nào — đây là kiểm chứng mạnh nhất cho chất lượng phân xử LV2.
2. **Nhóm chiên 92,5% nhưng bias phân rã sạch:** cấu trúc điểm giữa khoảng chiếm +9,54 điểm % trong
   bias +9,33% → phần dư **−0,21 điểm %**. Theo ngưỡng đăng ký trước, không có bằng chứng lệch hệ
   thống nên **không mở hiệu chuẩn CALC-004** — khác lần 4, nơi chênh dầu ròng +3,9 điểm % tập trung
   theo nhóm hệ số là bằng chứng đủ mạnh.
3. **Ba món lệch đều là ca cấu trúc đã hiểu rõ:** `ca-tram-sot-chua-ngot` +26,0% (hồ sơ hải sản
   chiên trần, ổn định qua 3 lần đo); `cua-rang-muoi` +24,5% (ca mới cùng họ — cua nguyên con:
   Variant B +7,0%, giới hạn của neo philê 7% chứ không phải lỗi hệ số); `com-chien-ca-man` +20,1%
   (tâm engine trước nới khoảng lệch tham chiếu chỉ +0,6% — toàn bộ phần vượt ngưỡng là khoảng công
   bố rộng 3,03× ở món độ tin cậy thấp). Tham chiếu nằm trong khoảng công bố ở cả 160 món.

> Tuyên bố ra ngoài giữ nguyên như sau lần đo 5 (98,1% trong ±20%, mẫu phân tầng, so với tham chiếu
> tính từ bảng thành phần của dự án cộng mô hình dầu neo USDA SR Legacy) — lần đo 7 xác nhận con số
> đó **áp dụng cho kho cuối 654 món**, không phải chỉ kho 500 món thời điểm lần đo 5.

### Ba giới hạn của bộ mẫu — phải đọc trước khi trích con số 89,7%

Bổ sung 2026-09-01 sau khi soát lại thành phần bộ mẫu:

1. **Bỏ qua 129/333 Food Code (39%).** Bộ nhập đã lọc sẵn "≥80% nguyên liệu ánh xạ được", rồi phép
   đo lại loại tiếp món không tính được. Nghĩa là 204 món còn lại là **phần dễ của phần dễ** — món
   khó ánh xạ, đúng loại hay gặp ở ẩm thực Việt, không có mặt trong mẫu số.
2. **45% bộ mẫu (`song`, n = 91) không có chuyển hoá do nấu.** Nhóm này đo được năng lực **cộng dồn
   nguyên liệu** của engine (và đo tốt: các món này trung bình ~6 nguyên liệu, chỉ 1,1% có một
   nguyên liệu chiếm >90% khối lượng), nhưng **không nói gì** về hao hụt nước, hấp thụ dầu, hay hệ
   số phần ăn được.
3. **Nhóm phơi bày rõ nhất giả định khối lượng lại là nhóm ít nguyên liệu nhất.** `hap` có 80% số
   món chỉ gồm một nguyên liệu chiếm >90% khối lượng (điển hình: 100 g cá sống + 0,2 g muối),
   `luoc` là 100%. Ở đó engine không còn gì để "cộng đúng" — nó chỉ còn đúng hoặc sai ở đúng một
   phép: khối lượng thành phẩm. Và nó sai −21%, đều đặn.

> Nói gọn: **89,7% chưa phải bằng chứng engine tính đúng món nấu.** Nó là bằng chứng engine cộng
> đúng, và chưa mâu thuẫn với việc tính đúng món nấu ở những nhóm đã đo được.

> **Vẫn chưa được tuyên bố "độ chính xác >80%" ra bên ngoài.** Con số 89,7% là trên **món Mỹ của
> FNDDS**, với nguyên liệu do chính USDA ánh xạ sẵn — không phải trên kho món Việt do dự án biên
> soạn, và không có nhóm chiên. Đo trên kho món thật là việc của [QA-002](../05-tasks/tasks/QA-002.md) ở GĐ3.

### Lần đo 3 (2026-10-31, QA-002 — lần đầu đo trên món Việt): 76,9%, chưa đạt

Mẫu 160 món phân tầng (luộc/hấp 32 · xào 28 · chiên/rán 40 · nấu/canh 32 · nướng/kho 28), tham
chiếu tính tay độc lập theo FAO/INFOODS từ chính bộ số nguyên liệu của dự án (xem giới hạn "chung
một bảng thành phần" trong báo cáo). Kết quả tách nhóm cho thấy bức tranh rõ hơn mọi lần đo trước:

- **Bốn nhóm không chiên đạt 100% trong ±20%** (MAPE 1,6–2,7%, bias −0,2…+1,2%) — đường biến đổi
  nguyên liệu → món của engine khớp phép tính tay.
- **Nhóm chiên/rán chỉ đạt 7,5%**, bias **+39,8%** một chiều: công thức đã kê sẵn dòng `dau-an`
  8–20 g, engine lại cộng thêm dầu hấp thụ theo mô hình CALC-004 (trung vị +100,5 kcal/món) —
  **dầu bị tính hai lần**. Đây là mâu thuẫn quy ước giữa dữ liệu và engine, không phải lỗi số học.
- Tổng thể 76,9% < 80% ⇒ **mục tiêu chưa đạt, không nới định nghĩa**. Sửa xong nhóm chiên (chờ
  chủ dự án chốt phương án — báo cáo QA-002 §5) phải đo lại trên cùng bộ mẫu mới được kết luận.

> Sau lần đo này, câu cấm tuyên bố ở trên còn nguyên hiệu lực và có thêm lý do: con số trên chính
> kho món Việt hiện là **76,9%**.
