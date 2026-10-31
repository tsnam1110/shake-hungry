# Chuẩn biên soạn dữ liệu — Lắc Đói

**Cập nhật lần cuối:** 2026-08-31 — bản đầu tiên

Dự án này là dự án biên soạn dữ liệu, nên chuẩn biên soạn quan trọng ngang chuẩn code. Tài liệu này
trả lời những câu hỏi mà người biên soạn gặp hằng ngày và nếu mỗi lần trả lời một kiểu thì dữ liệu sẽ
mâu thuẫn nội bộ.

---

## 1. Đặt tên món

| Quy tắc | Đúng | Sai |
|---|---|---|
| Viết thường, trừ danh từ riêng | `Bún bò Huế`, `Phở bò` | `Bún Bò Huế`, `BÚN BÒ HUẾ` |
| Không kèm tính từ quảng cáo | `Bún bò Huế` | `Bún bò Huế đặc biệt thơm ngon` |
| Không kèm tên quán | `Cơm tấm sườn` | `Cơm tấm Ba Ghiền` |
| Dùng tên phổ thông nhất | `Bánh mì thịt` | `Bánh mì kẹp thịt kiểu Sài Gòn` |
| Tên gọi khác đưa vào `is_alias` | `Phở bò` + alias `Phở thịt bò` | tạo hai món |

`slug` sinh từ tên chính: bỏ dấu, viết thường, nối bằng gạch ngang. `Bún bò Huế` → `bun-bo-hue`.

---

## 2. Đặt tên nguyên liệu

- **Cụ thể tới mức ảnh hưởng dinh dưỡng, không cụ thể hơn.** `thịt lợn ba chỉ` chứ không phải
  `thịt lợn` (chênh calo rất lớn), nhưng cũng không cần `thịt lợn ba chỉ nuôi hữu cơ`.
- Ghi rõ **trạng thái** khi nó đổi dinh dưỡng: `gạo tẻ (sống)`, `bún tươi`, `nấm hương khô`.
- Dùng từ phổ thông toàn quốc; biến thể vùng miền đưa vào alias (`lạc` / `đậu phộng`).

---

## 3. Món chuẩn hay biến thể — quy tắc phân biệt

Đây là quyết định gây tranh cãi nhiều nhất khi nhập liệu. Quy tắc:

### Là **biến thể** (`parent_dish_id` trỏ về món chuẩn) khi:

- Chỉ khác **một** nguyên liệu chính trong cùng cấu trúc món: `Phở bò tái` / `Phở bò chín`
- Khác cách phục vụ nhưng cùng công thức: `Bún chả` / `Bún chả que tre`
- Là bản vùng miền của cùng một món: `Bánh xèo miền Trung` / `Bánh xèo miền Nam`

### Là **món độc lập** khi:

- Khác nguyên liệu nền: `Phở bò` vs `Phở gà` — đây là hai món độc lập, vì nước dùng khác hoàn toàn
- Khác cách chế biến: `Cá kho tộ` vs `Cá chiên`
- Người ăn coi là hai món khác nhau khi gọi món

### Phép thử nhanh

> **Nếu nút Lắc gợi ý cả hai trong cùng một tuần, người dùng có thấy trùng lặp không?**
>
> Có → biến thể. Không → món độc lập.

Ví dụ: gợi ý `Phở bò tái` hôm nay và `Phở bò chín` ngày mai → thấy trùng → **biến thể**.
Gợi ý `Phở bò` và `Phở gà` → không thấy trùng → **hai món độc lập**.

---

## 4. Phân vùng miền

- Món phổ biến toàn quốc (`Cơm rang`, `Trứng chiên`) → để `region_id = null`, không ép gán.
- Món có gốc rõ ràng → gán vùng gốc, kể cả khi giờ đã phổ biến khắp nơi (`Bún bò Huế` → Huế).
- **Ranh giới ẩm thực không trùng ranh giới hành chính.** Khi phải chọn, ưu tiên cách người ăn hiểu.

---

## 5. Bữa phù hợp và trọng số

Trọng số 1–10, không phải có/không:

| Món | Sáng | Trưa | Chiều | Tối |
|---|---|---|---|---|
| Phở bò | 10 | 6 | 3 | 5 |
| Cơm tấm sườn | 7 | 10 | 4 | 6 |
| Lẩu | 1 | 3 | 5 | 10 |

Nguyên tắc: nghĩ "nếu app gợi ý món này vào bữa đó, mình thấy hợp lý tới mức nào?"

---

## 6. Định lượng nguyên liệu

- **Ưu tiên đơn vị đời thường** người nấu thực sự dùng: muỗng canh, chén, quả, bó. Hệ thống tự quy
  đổi sang gram qua `ingredient_units`.
- Ghi cho **một khẩu phần**, không phải cho cả nồi. Nếu công thức gốc là cho 4 người, chia ra.
- **Gia vị nêm nếm ít calo** (muối, tiêu, mì chính) vẫn nên nhập để công thức đầy đủ, đánh
  `is_optional = true` nếu không ảnh hưởng calo đáng kể.
- Nguyên liệu ăn kèm tuỳ chọn (rau sống, chanh, ớt) → `is_optional = true`.

---

## 7. Viết `mapping_note`

Bắt buộc khi `mapping_confidence` khác `high`. Viết cho **người đọc trong tương lai**, không phải cho
mình hôm nay.

✅ Tốt:
> "USDA chỉ có 'Sauce, fish, ready-to-serve' chung, không phân biệt độ đạm. Nước mắm Việt 40 độ đạm
> có thể cao hơn. Dùng tạm cho tới khi có dữ liệu Viện Dinh dưỡng."

🚫 Tệ:
> "gần đúng" · "tạm dùng" · "không có cái nào chuẩn"

---

## 8. Viết `source_note` cho calo ước lượng (món ăn ngoài)

Bắt buộc. Phải nói rõ **ước lượng dựa trên cái gì**.

✅ Tốt:
> "Ước lượng từ khẩu phần điển hình (1 tô 500 g) và món tương tự trong FNDDS (Food Code 27450010).
> Không phải giá trị đo."

🚫 Tệ:
> "khoảng 450 kcal" · "theo kinh nghiệm"

Ước lượng không có căn cứ nào thì **không nhập** — để trống, món ở `confidence = unknown` (QT-1).

---

## 9. Bẫy tra cứu đã gặp thật — đọc trước khi tra nguyên liệu mới

Ghi lại từ đợt tra cứu 55 nguyên liệu đầu tiên (2026-08-31, [ING-001](../05-tasks/tasks/ING-001.md)).
Đây là những chỗ đã suýt nhập sai, không phải giả định.

### 9.1 Tên tiếng Anh trùng không có nghĩa là cùng loài

| Nguyên liệu | Bẫy |
|---|---|
| **Rau muống** | USDA **không** có mục nào tên `water spinach`. Tra từ đó ra *Spinacia oleracea* (rau chân vịt), *Basella alba* (mồng tơi). Bản ghi đúng tên là **"Water convolvulus, raw"** (fdc 169301), `scientificName = Ipomoea aquatica`. Ai lấy kết quả đầu của `water spinach` sẽ nhập nhầm rau chân vịt. |
| **Cá basa** | Đừng thay bằng `Fish, catfish, channel, farmed` (fdc 175165) — đó là *Ictalurus punctatus*, **khác họ**, lipid 5,94 g so với 1,6 g, **sai gần 4 lần**. |
| **Cá thu** | Cá thu VN là *Scomberomorus commerson*. Dòng `Fish, mackerel, Atlantic` (fdc 175119) là *Scomber scombrus* — **khác chi**, cá vùng lạnh, béo gấp đôi (205 so với 139 kcal). Phải chọn dòng cùng chi *Scomberomorus*. |
| **Giá đỗ** | `Mung beans, mature seeds, sprouted` (30 kcal) so với `Mung beans, mature seeds, raw` (347 kcal) — **chênh hơn 11 lần**. Một cái là giá, một cái là hạt đậu khô. |
| **Cải xanh** | `Mustard greens` (*Brassica juncea*) đúng; `Mustard spinach / tendergreen` (*Brassica rapa*) là loài khác. |

> **Quy tắc rút ra: luôn kiểm trường `scientificName` của bản ghi USDA**, đừng tin tên thường gọi.

### 9.2 Phần thịt và độ mỡ — nguồn sai số lớn nhất

Đây được ghi nhận trong tài liệu khoa học là **nguồn sai số lớn nhất** khi tính dinh dưỡng công thức.

- **Thịt lợn "thăn"** trong tiếng Việt gồm cả thăn ngoại (166 kcal) và thăn nội/phi lê (119 kcal) —
  chênh 47 kcal/100g. Dự án **đã tách thành hai nguyên liệu**; đừng gộp lại.
- **Ba chỉ**: hai dòng USDA lệch **138 kcal/100g** (380 so với 518). Ba chỉ kiểu Mỹ là để làm bacon,
  nhiều mỡ hơn ba chỉ VN.
- **Gà**: cùng con gà mà ức có da 172, đùi có da 221, lưng có da 319, **riêng da gà 440 kcal**.
  Chọn nhầm "có da / không da" lệch 60–100 kcal/100g.
- Luôn **loại các dòng `with added solution`** — thịt bơm dung dịch muối phosphate, phổ biến ở siêu
  thị Mỹ, không có ở chợ VN.

### 9.3 Quy đổi đơn vị của USDA theo cỡ phương Tây

| Đơn vị | Bẫy |
|---|---|
| **Quả ớt** | USDA `1 pepper = 45 g` (ớt sừng/jalapeño). Ớt hiểm VN chỉ **1–3 g/quả** — lệch **15–45 lần** |
| **Tép tỏi** | USDA `1 clove = 3,0 g`. Tỏi Lý Sơn tép nhỏ hơn nhiều |
| **Bìa đậu phụ** | USDA `1/4 block = 81 g` (bìa Mỹ ~324 g), khác bìa đậu phụ VN |
| **Trứng** | Trứng gà ta VN gần cỡ `small` (38 g) hơn là `large` (50 g) của Mỹ |
| **Ổ bánh mì** | USDA có `slice = 139 g` nhưng đó là lát baguette Mỹ. **Đã cố ý không nhập** để tránh con số chính xác giả |

> **Quy tắc: định lượng món Việt bằng GRAM, không đếm quả/tép/bìa.** Đơn vị đời thường Việt Nam (bó
> rau, mớ cải, con cá, ổ bánh mì, khoanh giò) **không có nguồn nào tra được** — phải tự cân, và ghi
> rõ trong trường `source` là đo chứ không phải tra.

### 9.4 Món đã nấu: sai số do nước, không phải do phép đo

Cơm trắng, bún tươi, bánh phở tươi — calo/100g phụ thuộc gần như hoàn toàn vào tỉ lệ nước khi nấu.
Cơm trắng: USDA 130 kcal (nước 68,4%) so với Ciqual 155 kcal (nước 61,4%) — **chênh 19% thuần tuý do
độ ngậm nước**.

> **Cách đúng: lưu dạng KHÔ/SỐNG rồi nhân hệ số hút nước do chính dự án đo.** Đừng lưu dạng đã nấu
> như một nguyên liệu độc lập — engine calo sẽ không truy nguyên được.

### 9.5 Sản phẩm Việt Nam khác sản phẩm cùng tên ở nước ngoài

- **Sữa đặc**: dòng USDA là sữa đặc **sữa thật** (protein 7,91 g). Phần lớn sữa đặc bán tại VN là
  **creamer pha dầu thực vật** — Ông Thọ 4,8 g, Ngôi Sao Phương Nam 2,7 g protein, mà kcal lại
  **cao hơn**. Ảnh hưởng trực tiếp cà phê sữa đá, bánh flan.
- **Nước mắm**: độ đạm quyết định tất cả. Protein trải từ 5,06 (dòng USDA chung) đến 27,0 g/100ml
  (cốt cá cơm 40 độ đạm) — **hơn 5 lần**.
- **Tương ớt**: tương ớt Việt là hệ đường + tinh bột biến tính; hai dòng USDA đều không thay được
  (một cái nền cà chua kiểu Mỹ, một cái thuần ớt xay).

### 9.6 Cảnh giác với số liệu trôi nổi trên web tiếng Việt

Nhiều trang web Việt đăng số dinh dưỡng cho món/nguyên liệu Việt mà **không ghi nguồn**, hoặc dẫn lại
"Viện Dinh dưỡng" / "Viện Y tế". Những con số đó gần như chắc chắn **chép từ Bảng Thành phần Thực
phẩm Việt Nam có bản quyền** — dùng là vi phạm QT-2, dù nguồn trung gian trông vô hại.

Dấu hiệu nhận biết: nhiều trang cho **cùng một con số lẻ giống hệt nhau**, không trang nào dẫn được
về phép đo gốc. Khi gặp, **loại bỏ** và ghi lại đã loại vì lý do gì.

---

## 10. Khi phân vân

Ghi câu hỏi vào mục "Câu hỏi mở" ở [ROADMAP](../05-tasks/ROADMAP.md) và **chọn phương án bảo thủ hơn**:

| Phân vân | Chọn |
|---|---|
| Biến thể hay món độc lập? | Biến thể (gộp lại dễ tách hơn là tách rồi gộp) |
| Ánh xạ nguyên liệu này `medium` hay `low`? | `low` |
| Có nên đoán con số này không? | Không |
| Có nên nhập món khi thiếu nhiều thông tin? | Nhập ở Mức 1, đừng cố lên Mức 2 |


---

## 11. Quy ước khối lượng nguyên liệu có xương/vỏ — bổ sung 2026-08-31 (DATA-008)

**Mặc định, `quantity` trong công thức món là KHỐI LƯỢNG MUA Ở CHỢ** — cả xương, cả vỏ,
cả đầu tôm. Engine tự nhân `edible_portion_pct` của nguyên liệu để ra phần ăn được
TRƯỚC khi tra dinh dưỡng (CALORIE-ENGINE §3b).

- Công thức ghi "300 g sườn" ⇒ nhập 300 g, KHÔNG tự trừ xương.
- Nếu nguồn công thức ghi rõ khối lượng ĐÃ lọc xương/bóc vỏ ⇒ đánh dấu
  `is_edible_weight: true` trên dòng nguyên liệu đó, engine sẽ không nhân hệ số nữa.
- Nguyên liệu bán cả xương/vỏ mà `edible_portion_pct` còn null (xem
  `edible_portion_note` của từng file): calo món sẽ bị VỐNG LÊN — ưu tiên bổ sung hệ số
  (tự cân theo phương pháp ghi trong note) trước khi nhập nhiều món dùng nguyên liệu đó.
---

## 12. Quy ước dầu ăn trong món chiên/rán — Phương án A, chốt 2026-10-31 (QA-002)

**Bối cảnh.** QA-002 lần 1 (2026-10-31) đo thấy nhóm chiên lệch **+39,8%** vì dầu bị tính hai
lần: công thức kê sẵn dòng `dau-an` 8–20 g, engine lại cộng thêm mô hình hấp thụ dầu
CALC-004. Chủ dự án đã chốt **Phương án A — đổi quy ước dữ liệu, không đổi engine** và toàn
bộ 46 món chiên trong kho đã rà lại theo quy ước này.

**Quy ước (bắt buộc khi biên soạn món `chien` / `chien-ngap-dau`):**

1. **Dầu dùng cho bước chiên chính KHÔNG ghi vào công thức.** Lượng dầu thực phẩm hấp thụ
   khi chiên do engine mô hình hoá theo `oil_absorption_pct` của từng nguyên liệu
   (CALC-004) — kê vào công thức là tính hai lần.
2. **Dòng `dau-an` chỉ ghi dầu dùng cho chế biến KHÁC ngoài bước chiên chính**, khi bước đó
   có thật trong `recipe_steps`: phi hành/tỏi thơm, xào nhân trước khi gói, xào sốt rưới
   lên món, phi nguyên liệu tạo màu/mùi. Mức tham chiếu cho một bước phi/xào nhanh ở một
   khẩu phần là **3 g** — định lượng chế biến (không phải số dinh dưỡng), và phần dầu này
   được ăn vào cùng nhân/sốt nên kê nguyên. Ghi rõ lý do vào `import_note` của món.
3. **Ngoại lệ — chủ thể chiên có hệ số hấp thụ 0 theo thiết kế CALC-004:** tinh bột đã nấu
   chín đem rán (xôi chiên, bánh chưng chiên — `gao-nep`; xem danh mục hệ số 0 trong
   CALC-004) thì dòng dầu đã kê *là* cơ chế tính dầu duy nhất của món, không tính trùng —
   **giữ dòng `dau-an`** và ghi chú ngoại lệ vào `import_note`. Khi gặp món mới thuộc diện
   này (mọi nguyên liệu chính đều hệ số 0 và dầu chiên chưa được mô hình hoá ở đâu khác),
   áp cùng xử lý và ghi chú; không tự ý bỏ dòng dầu khiến món chiên tính ra 0 g dầu.
4. **Phân biệt dầu/mỡ là NGUYÊN LIỆU với dầu là MÔI TRƯỜNG chiên:** mỡ lợn trộn trong
   chạo/nhân, dầu phi đã thành một phần của món… là nguyên liệu ăn vào — giữ nguyên như
   mọi nguyên liệu khác. Chỉ phần dầu làm môi trường cho bước chiên chính mới thuộc mục 1.

**Hệ quả đo lường đã biết (QA-002 lần 2, 2026-10-31):** sau khi áp quy ước này, phía tham
chiếu "công thức nguyên văn" của QA-002 không còn chứa đại diện nào cho dầu hấp thụ, nên
nhóm chiên vẫn lệch +49,0% so với engine dù quy ước dữ liệu đã nhất quán — cặp (quy ước A +
tham chiếu nguyên văn) không tương thích về định nghĩa. Phân tích đầy đủ và các lựa chọn
tiếp theo: [accuracy-runs/2026-10-31-vn-dishes-lan-2.md](../06-nutrition/accuracy-runs/2026-10-31-vn-dishes-lan-2.md) §5, §7.
Không vì kết quả đo này mà quay lại kê dầu chiên vào công thức: quy ước trên là quyết định
đã chốt; phần cần quyết tiếp là *phương pháp đo* và/hoặc *hệ số engine*, thuộc chủ dự án.

## 13. Quy ước cân `mong-gio` — chốt 2026-10-31 (LV2-010 Phần C, quyết định A4.4)

Lượng `mong-gio` trong file món cân theo **khoanh có xương bày tô** — tức khối lượng
sống có xương như chợ bán và như các công thức nguồn ghi (thực tế đã đối chiếu ở
banh-canh-gio-heo: 3 họ cho 125–250 g có xương/khẩu phần).

Hệ quả phải ghi nhận công khai, không giấu: bộ số của ingredient `mong-gio`
(USDA SR Legacy 167859) là trên **phần ăn được đã loại xương**, nên kcal tính ra
cho dòng giò sẽ cao hơn phần thực ăn cho tới khi tra/cân được hệ số phần ăn được
(DATA-008). `mapping_note` của ingredient đã hướng dẫn tạm thời đúng quy ước này
("ghi khối lượng theo quy ước chợ và chấp nhận confidence thấp hơn"). Không tự
chế hệ số xương, không âm thầm đổi sang cân phần thịt — khi DATA-008 có hệ số,
rà lại toàn bộ món dùng `mong-gio` trong một đợt riêng.

## 14. Quy ước lượng sợi theo khẩu phần — chốt 2026-10-31 (LV2-010 Phần C, quyết định A4.3)

Một quy ước duy nhất cho nhóm món sợi, suy từ tổng hợp định lượng đã đối chiếu
qua các đợt LV2-001…LV2-010 (chỉ tính các món đã phân xử bằng ≥3 họ nguồn):

| Sợi | Dạng | Lượng/khẩu phần | Căn cứ đã đối chiếu |
|---|---|---|---|
| `bun-tuoi` | tươi | **150 g** | Điểm hội tụ của hàng chục món bún đã nâng Mức 2 (bun-bo-hue, bun-cha, bun-ca, bun-mam, bun-nem… đều 150 g sau phân xử) |
| `bun-tuoi` ăn kèm lẩu | tươi, dòng tuỳ chọn | **80 g** | Thống nhất ở toàn bộ nhóm lẩu đã rà (lau-bo, lau-thai, lau-vit… đều 80 g) |
| `bun-kho` | khô | **50 g** | Tương đương ~150 g sau ngâm/trụng (tỉ lệ nở ~3 lần) — bun-gao-xao-trung |
| `soi-banh-canh` | tươi | **130 g** | banh-canh-gio-heo phân xử ở LV2-003/004 (nguồn 125–250 g); các món bánh canh khác đã phân xử riêng (80–200 g) giữ theo nguồn của chính món |
| `hu-tieu-kho` | khô | **60 g** | hu-tieu, hu-tieu-ga đều 60 g sau phân xử (≈150 g sau ngâm) |
| `banh-pho-tuoi` | tươi | **180 g** | pho-bo, pho-ga, pho-chay, pho-xao đều 180 g sau phân xử |
| `banh-pho-kho` | khô | **60 g** | pho-kho-gia-lai — cùng mặt bằng sợi khô 60 g |
| `mi-trung-kho` | khô | **70 g** | Dải đã phân xử 60–90 g (mi-xao-gion 60, mi-van-than 80, mi-xao-bo 90); lấy 70 g làm chuẩn soạn mới, lệch ≤30% không bắt sửa |
| `mi-an-lien` | khô | **60–65 g** | Một gói nhỏ tiêu chuẩn (mi-xao-chay 60, mi-goi-xao-trung-rau 65) |

Quy tắc áp dụng: món soạn mới hoặc món Mức 1 chưa từng đối chiếu nguồn dùng
đúng bảng trên. Món đã phân xử bằng nguồn riêng (thường là món đã đạt Mức 2)
mà lệch quy ước thì **giữ theo nguồn của chính món** và ghi ngoại lệ trong
`import_note` — ví dụ bun-dau-mam-tom/bun-thang 120 g (tô nhỏ), bun-thit-nuong
200 g, hu-tieu-chay 100 g, banh-canh-tom-cang 80 g (nguồn của món bất đồng,
đã giữ ở LV2-010). Lệch quy ước không có căn cứ nguồn thì sửa về quy ước khi
rà tới món, kèm ghi phân xử.
