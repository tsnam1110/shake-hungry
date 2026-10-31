# [CALC-004] — Hệ số hấp thụ dầu theo nhóm thực phẩm cho món chiên

**Mã task:** CALC-004 · **Giai đoạn:** GĐ2 · **Loại:** Engine/Dữ liệu
**Status:** ✅ DONE · **Ngày tạo:** 2026-10-31 · **Ngày chốt:** 2026-10-31 · **Phụ thuộc:** CALC-002

---

## Bối cảnh

Cổng CALC-002 (món chiên thiếu `oil_absorption_pct` có nguồn ở bất kỳ nguyên liệu
nào ⇒ nguyên liệu đó vào `missing[]`) khiến 28 món chiên không tính được dinh dưỡng.
Đề xuất hệ số theo nhóm đã ghi trong
[DATA-COLLECTION-PLAN §6](../../04-data-sources/DATA-COLLECTION-PLAN-2026-10-31.md)
và được **chủ dự án duyệt ngày 2026-10-31** (kèm phán quyết riêng cho bim bim:
tránh tính dầu hai lần).

## Nguồn được phép dùng

Chỉ hai nguồn mà [CALORIE-ENGINE §4](../../06-nutrition/CALORIE-ENGINE.md) đã duyệt từ trước:

1. Tổng quan PMC10660127 (Mahmud et al. 2023) — các điểm đo được trích dẫn trong đó:
   - Khoai chiên (French fries): dầu hút vào **12%** (canola, nghiên cứu [54]); khoai
     chiên dạng thanh ~12% (hướng dương, [72]); dải đo giữa các loại dầu 6,9–24%.
   - Bánh vỏ bột nhân phô mai (cheese samosa): **10%** (canola, [54]); dải 10–28%.
   - Phi lê cá (whiting): chất béo 1% → **10,5%** ở thành phẩm (nghiên cứu [1,2] của tổng quan).
   - Đùi gà: chất béo 2% → **18%** ở thành phẩm; đo uptake trực tiếp 8,53% trong
     nghiên cứu oleogel (môi trường giảm hút — cận dưới); một nghiên cứu khác ghi
     nhận gà chiên gần như không tăng béo ròng vì mỡ tự thân chảy ra.
   - Snack khoai lát mỏng: chất béo thành phẩm tới **40%**.
2. IJFST 43(8):1410 — nguồn thứ hai đã duyệt trong CALORIE-ENGINE §4.

## Bảng hệ số theo nhóm (áp tại `ingredients.oil_absorption_pct`)

| Nhóm | Hệ số | Nguyên liệu | Neo nguồn |
|---|---|---|---|
| Củ/quả chiên miếng | **12** | khoai-tay, khoai-lang, khoai-mon, chuoi | Fries/strips uptake 12% [54][72]. Khoai lát mỏng (bim bim): thành phẩm 40% béo nhưng hao hụt khối lượng lớn, quy về nguyên liệu sống cũng ≈12 — cùng một hệ số phục vụ cả hai dạng |
| Phô mai trong vỏ bột | **10** | pho-mai | Cheese samosa 10% — điểm đo trực tiếp [54] |
| Bột/vỏ chiên ngập | **15** | bot-mi, bot-nep, banh-trang, bot-chien-xu | Dải samosa/bánh áo bột 10–28%, lấy trung vị thận trọng |
| Bột gạo/tinh bột áo | **12** | bot-gao, bot-nang | Ma trận tinh bột, neo theo nhóm fries 12 |
| Hải sản chiên trực tiếp | **9** | ca-thac-lac, tom-su, muc-ong, ruoi | Phi lê cá 1% → 10,5% béo thành phẩm ⇒ hút ròng ~9 |
| Thịt/đạm chiên trực tiếp | **8** | thit-ga-co-da, thit-lon-xay, thit-lon-ba-chi, thit-de, nem-chua, trung-ga, trung-cut | Uptake đùi gà 8,53% (cận dưới) và hiện tượng chảy mỡ tự thân; 8 là trung vị thận trọng cho cả nhóm đạm |
| Không phải chủ thể chiên | **0** | 48 nguyên liệu còn lại trong các món chiên (xem dưới) | Theo bản chất mô hình — không phải số đo |

**Nhóm hệ số 0 (theo bản chất, ghi rõ để không hiểu nhầm là số bịa):**
- Chính chất béo chiên: `dau-an`, `mo-lon-nuoc` — dầu trong công thức đã được tính
  nguyên vẹn như một nguyên liệu; "hấp thụ dầu vào dầu" không có nghĩa.
- Gia vị, nước chấm, chất chua: muối, đường, nước mắm, nước tương, mắm tôm, tương ớt,
  dấm gạo, chanh, tiêu, bột nghệ và các rau thơm — không phải chủ thể chiên.
- Rau ăn kèm sống: xà lách, cải xanh, rau diếp cá, dưa leo, cà chua, tía tô, rau mùi, húng quế, kinh giới.
- Nhân kín không tiếp xúc dầu trực tiếp: miến dong, mộc nhĩ khô, đậu xanh hạt, cùi dừa nạo,
  củ đậu, cà rốt, giá đỗ, nấm rơm, đậu Hà Lan (trong các món đang áp).
- Chất lỏng pha bột: nước cốt dừa, sữa tươi (bản thân chúng không "hút" dầu).
- Thành phẩm đã chiên sẵn mua về: `hanh-phi`, `lap-xuong`, `dau-phu-chien` — số liệu
  dinh dưỡng của chúng **đã bao gồm dầu của lần chiên sản xuất** (dau-phu-chien là mục
  USDA "Tofu, fried", béo 20,18%); khi dùng chỉ rán nóng lại.
- Tinh bột đã nấu chín đem rán với dầu đã kê trong công thức: `com-trang`, `gao-nep`
  (xôi chiên), `bun-tuoi` (bún trong bún đậu không hề chiên).

**Quy tắc cho đậu phụ tươi (`dau-phu`) khi sau này xuất hiện trong món chiên:** dùng
hệ số nhóm xốp **15** (ma trận xốp nhiều nước, ngang nhóm bột áo) — hiện chưa có món
chiên nào trong kho dùng đậu phụ tươi nên chưa áp.

## Phán quyết bim bim

Công thức bim bim cũ kê riêng 8 g `dau-an` cho 40 g khoai tây — đó chính là dầu đã
hấp thụ của dạng snack. Giữ cả hai cách tính sẽ tính dầu hai lần. **Đã bỏ dòng
`dau-an` khỏi công thức bim bim**; dầu của món do hệ số 12 của khoai-tay đảm nhiệm
(40 g × 12% = 4,8 g dầu — khớp lượng béo thực tế của ~13 g snack khoai thành phẩm).

## Đánh đổi đã biết (ghi nhận, không giấu)

- Hệ số là của **nhóm**, không phải của từng nguyên liệu: `bot-gao` = 12 phục vụ tốt
  bánh xèo/bánh khọt (bột sống chiên giòn) nhưng hơi cao cho bột chiên (bột đã nấu
  chín, chiên áp chảo). Khoảng nới ×0,80–×1,25 của engine cho món chiên được thiết
  kế để chứa sai số này.
- `thit-lon-xay` = 8 áp cả khi là nhân nem rán (nhân kín, hút ít hơn chiên trực tiếp)
  — đơn giản hoá có chủ đích, cùng lý do trên.
- Món chiên không bao giờ đạt confidence `high` (đúng thiết kế CALORIE-ENGINE §4).

## Kết quả

- 71 nguyên liệu được gán hệ số (23 theo nhóm đo + 48 hệ số 0 theo bản chất).
- Sau khi chạy lại engine: **297/300 món tính được dinh dưỡng** (trước đợt: 269) —
  toàn bộ 28 món chiên bị cổng dầu chặn đều mở. Ba món còn lại chưa tính được đều
  vướng **thiếu dữ liệu dinh dưỡng**, không còn vướng cổng dầu: gỏi ngó sen (ngó sen
  không có bảng đạt chuẩn), mắm nêm (không nguồn nào có số), mắm ruột An Giang
  (vỏ rỗng chưa có công thức). Kiểm chứng: validate-data 0 🔴, Pest 77/77;
  bim bim cho béo 4,8 g/khẩu phần — đúng tay tính 40 g × 12%.

## Bổ sung 2026-10-31 — Hiệu chuẩn sau QA-002 lần 3 (tham chiếu độc lập)

Bảng hệ số ở trên được rà lại bằng tham chiếu **độc lập với hai nguồn neo gốc**: hệ số hấp
thụ suy từ cặp sống → chiên và hệ thống áo bột trong **USDA SR Legacy** (cân bằng
protein/khối lượng; FDC ids và số suy ra đầy đủ ở Phụ lục/Bảng §2 của
[báo cáo QA-002 lần 3](../../06-nutrition/accuracy-runs/2026-10-31-vn-dishes-lan-3.md)).
Ngưỡng sửa đăng ký trước: tỉ lệ dầu engine/tham chiếu ngoài [0,80; 1,25] **và** nhóm đủ
vật chất trong mẫu. Kết quả:

| Nhóm | Cũ | Mới | Lý do (neo độc lập) |
|---|---|---|---|
| Thịt/đạm | 8 | **4** | Tỉ lệ dầu engine/tham chiếu 1,97. Gà không da chiên 3,30; gà ức+cả da áo bột 2,30; áo bột mì mỏng **−3,41** (mất béo ròng); trứng có cặp trực tiếp 4,19 ⇒ cả nhóm gồm trứng về 4. Hệ số 8 cũ neo vào điểm uptake 8,53% trong môi trường oleogel — không điển hình |
| Củ/quả chiên miếng | 12 | **9** | Tỉ lệ 1,31. Fries 8,07/8,40 (hai chuỗi), plantain 6,53–9,77. Dạng lát mỏng (chips 10,81) dùng chung slug khoai-tay — 9 là giá trị dung hoà; bim bim sau sửa lệch −3,3% so với tham chiếu dạng chips |
| Hải sản | 9 | giữ 9 | Theo dòng thì cao (1,29) nhưng theo hệ thống áo bột (9,84–11,72) thì khớp — bằng chứng chưa kết luận. Điểm yếu thật: cá chiên trần (xem hồ sơ mở ở báo cáo lần 3 §8) |
| Phô mai | 10 | giữ 10 | Tỉ lệ 0,79 nhưng n = 1 nguyên liệu/1 món — chưa đủ bằng chứng |
| Bột/vỏ chiên ngập | 15 | giữ 15 | Tỉ lệ 0,89 — trong dải |
| Bột gạo/tinh bột áo | 12 | giữ 12 | Tỉ lệ 0,83 — trong dải |

**Phán quyết bim bim được giữ nguyên cơ chế** (bỏ dòng `dau-an`, hệ số đảm nhiệm) nhưng số
học đổi theo hệ số mới: 40 g khoai-tay × 9% = **3,6 g dầu** (trước: 4,8 g ở hệ số 12).

**Hai món ngoại lệ gạo nếp giữ nguyên** (hệ số 0 + dầu kê trong công thức): lệch so với
tham chiếu độc lập lần 3 là +6,2% (`xoi-chien`) và +8,4% (`banh-chung-chien`) — trong
ngưỡng; gán hệ số cho `gao-nep`/`com-trang` có rủi ro tính hai lần ở hàng loạt món khác.

Kiểm chứng sau hiệu chuẩn: validate-data 0 🔴, recompute 491/496 món, Pest 106/106;
QA-002 lần 4 (kiểm tra hiệu chuẩn, cùng tham chiếu độc lập): **98,8% trong ±20%**, nhóm
chiên 95,0% — hai món còn lệch đều là cá chiên trần sốt (điểm mở của nhóm hải sản ở trên).
