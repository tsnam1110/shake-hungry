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
