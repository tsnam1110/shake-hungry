# Đặc tả độ chính xác calo — Lắc Đói

**Cập nhật lần cuối:** 2026-08-31 — bản đầu tiên
**Trạng thái:** Định nghĩa đã chốt. Quy trình đo **chưa chạy lần nào**.

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
| **Nutrition5k** (Google Research) | ~5.000 món thật, nguyên liệu + khối lượng + calo **đo bằng thiết bị**, không phải ước lượng. Đây là gần với "sự thật" nhất mà dự án tiếp cận được miễn phí | CC BY 4.0 (ghi công) |

> Chạy hai bộ này **trước** khi tốn công người tính tay. Nếu engine đã sai trên FNDDS thì không cần
> đo tiếp — sửa engine đã.

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
| — | — | — | — | — | — | — | *chưa chạy lần nào* |

> Bảng này trống là đúng với trạng thái hiện tại (chưa có engine). **Không được tuyên bố bất kỳ con
> số độ chính xác nào ra bên ngoài khi bảng này còn trống.**
