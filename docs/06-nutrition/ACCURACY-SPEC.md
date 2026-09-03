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
