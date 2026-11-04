# Kho giá theo tuần — Lắc Đói

> Trạng thái: kho và quy trình đã lập ngày 2026-11-04, đổi từ chu kỳ ngày sang
> chu kỳ tuần ngày 2026-11-04 theo yêu cầu của chủ dự án. Đính chính ngày
> 2026-11-04: file tuần đầu tiên 2026-W45 đã có, thu thập 2026-11-04, chỉ
> 76/489 khóa, chưa hoàn chỉnh; cùng ngày chủ dự án yêu cầu mở rộng kênh
> khảo sát vì chỉ có 2 kênh nên thiếu quá nhiều (xem mục Kênh và miền).
> Mọi file tuần trong kho này đều là tài liệu nghiên cứu, CHƯA được seed
> vào app, CHƯA hiển thị cho người dùng, cho tới khi có quyết định riêng
> về việc seed và hiển thị.

## Kho này để làm gì

Kho này lưu giá bán lẻ quan sát được theo từng tuần, làm nền cho Tuần ăn và cho
dữ liệu giá theo mùa vụ, vùng miền của dự án. Nó không thay thế giá quả theo mùa
đã có trong `data/fruits/*.json` (bảng `fruit_prices`, snapshot 2026-10-31), mà bổ
sung thêm chuỗi thời gian theo tuần để sau này tính được tổng tiền đi chợ thật,
quy đổi gói thật và chi phí ước tính của món ăn theo tháng và miền.

Tài liệu gốc về phương pháp thu thập giá vẫn là
`../PRICE-COLLECTION-PLAN.md`. Kho này là phần vận hành hàng tuần của kế hoạch
đó, không phải một kế hoạch khác.

Kho này là kho tài liệu nghiên cứu, chưa phải nguồn sự thật của app. Theo
quy tắc QT-3 của dự án, dữ liệu dùng trong app phải nằm trong `data/` dưới
dạng file có version. Vì vậy khi một snapshot tuần được duyệt để dùng trong
app, việc đưa nó vào `data/` và cập nhật `DATA-MODEL` là một quyết định và
một commit riêng, không tự động xảy ra từ task hàng tuần này.

## Phạm vi: 489 khóa sản phẩm, không phải 671 hay 1.548

Phạm vi cập nhật hàng tuần là nguyên liệu, hoa quả và rau mùa, nhưng phải khử
trùng lặp trước khi đếm:

- Nguyên liệu: 489 file trong `data/ingredients/`.
- Rau mùa: 99 file trong `data/vegetables/`, mỗi file đều có `ingredient_slug`.
- Hoa quả: 82 file trong `data/fruits/`, mỗi file đều có `ingredient_slug`.
- Toàn bộ 99 `ingredient_slug` của rau và 82 `ingredient_slug` của quả đều đã
  nằm trong 489 nguyên liệu. Vì vậy số khóa sản phẩm riêng biệt là **489**,
  không phải 99 + 82 + 489 = 671, và càng không phải cộng thêm 647 món ăn và
  230 đồ uống.

Khóa chuẩn của một dòng giá là `product_key`, chính là slug nguyên liệu
(`ingredient_slug` của rau và quả, hoặc `slug` của nguyên liệu). Tên rau và tên
quả chỉ là cách gọi khác của cùng một khóa, không được tạo dòng riêng cho rau,
cho quả và cho nguyên liệu của cùng một thực phẩm.

Món ăn và đồ uống không thuộc phạm vi cập nhật giá hàng tuần này. Giá của một
món nấu sẵn phụ thuộc quán, khẩu phần và địa điểm, không tính ngược ra được từ
giá nguyên liệu theo cách đáng tin.

## Cấu trúc kho

```text
docs/04-data-sources/prices/
  README.md            # File này: mục đích, phạm vi, cách đọc kho.
  SCHEMA.md            # Schema và toàn bộ quy tắc quy đổi đơn vị, gói.
  WEEKLY-TASK.md       # Định nghĩa task hàng tuần cho Agent hoặc Dev kế nhiệm.
  validate.py          # Script kiểm tra file tuần, chạy trước khi commit.
  weekly/
    TEMPLATE.json      # Mẫu file tuần, KHÔNG phải dữ liệu thật.
    YYYY-Www.json      # Một file cho một tuần ISO, ví dụ 2026-W45.json.
```

## Một tuần là một file

- Mỗi tuần thu thập có đúng một file `weekly/YYYY-Www.json`, ví dụ
  `2026-W45.json`. Tuần ISO tính bằng lệnh `date +%G-W%V` theo múi giờ
  Asia/Saigon, ví dụ ngày 2026-11-04 thuộc tuần 2026-W45.
- Mỗi file tuần ghi rõ `collected_date` là ngày thu thập thật trong tuần
  đó, thường là ngày chạy task đầu tuần. Mọi dòng trong file đó phải có
  cùng `collected_date`, và ngày đó phải nằm trong đúng tuần ISO ghi
  trong tên file. Một file tuần không bao giờ chứa giá của hai ngày khác
  nhau.
- Không bao giờ mang giá của tuần trước sang file của tuần sau. Tuần nào
  không thu được mặt hàng nào thì mặt hàng đó vào danh sách `missing`,
  kèm lý do, không được lấp bằng giá cũ, giá của kênh khác hay giá của
  mặt hàng tương tự.
- File tuần KHÔNG tính trung bình giá của nhiều ngày thành một giá tuần,
  vì trong một file tuần chỉ có một ngày thu thập duy nhất. Muốn biết giá
  thay đổi ra sao thì so sánh các file tuần liên tiếp, không gộp chúng.

## Kênh và miền: ghi đúng cái đã thấy, không suy đoán

- Kênh là nơi giá được đọc trực tiếp. Từ ngày 2026-11-04, theo yêu cầu của
  chủ dự án vì tuần W45 chỉ có 2 kênh nên thiếu quá nhiều, các kênh mặc
  định là: `bhx-online`, `winmart-online`, `coop-online`,
  `aeon-eshop-online`, `lotte-online`, `go-bigc-online`, cộng kênh chuyên
  biệt `meatdeli-online` và `cho-<tên chợ>` khi khảo sát trực tiếp tại
  chợ. Bài báo trích lại giá, trang tổng hợp và gian hàng bên thứ ba
  không phải là kênh giá. Mỗi kênh là một dòng riêng, không bao giờ gộp
  giá chợ và giá siêu thị thành một dòng, không bao giờ tính trung bình
  hai kênh.
- Miền chỉ ghi `bac`, `trung` hoặc `nam` khi vị trí giao hàng hoặc địa điểm
  chợ đã được chọn và ghi rõ. Khi đọc online mà chưa chọn vị trí giao, miền
  phải ghi `online-chua-chon-vi-tri`, không được gán miền theo suy đoán.
- Giá khuyến mãi, giá gốc, mua kèm và điều kiện khuyến mãi phải ghi riêng,
  không được tính giá hiệu dụng rồi ghi đè lên giá niêm yết.

## Quy tắc vàng về đơn vị và gói

Toàn bộ quy tắc nằm trong `SCHEMA.md` và bắt buộc phải đọc trước mỗi lần thu
thập. Tóm tắt ba điểm dễ sai nhất:

1. Giá niêm yết luôn đi kèm đúng cỡ gói như trang bán ghi, ví dụ 10.000 đ cho
   gói 100 g khác hoàn toàn 10.000 đ cho gói 1.000 g: gói đầu quy ra
   100.000 đ/kg, gói sau quy ra 10.000 đ/kg. So sánh giá niêm yết của hai gói
   khác cỡ mà không quy đổi là sai.
2. Trong hồ sơ nguyên liệu KHÔNG có cỡ gói bán lẻ. Cỡ gói chỉ lấy từ trang bán
   tại thời điểm thu thập, ghi vào dòng giá, không được suy ra từ trường
   `units` của nguyên liệu (đơn vị cup, muỗng chỉ dùng cho nấu ăn, không phải
   cỡ gói bán).
3. Giá/kg in sẵn trên trang bán chỉ là thông tin tham khảo. Giá/kg dùng trong
   kho luôn phải tự tính lại từ giá/gói chia cho cỡ gói. Nếu hai số lệch nhau
   thì giữ số tự tính, ghi số in sẵn vào trường tham khảo kèm cờ lệch, không
   bao giờ ghi đè số tự tính bằng số in sẵn.

## Trạng thái của kho này so với app

| Phần | Trạng thái |
| ---- | ---------- |
| Kho, schema, task tuần, script kiểm tra | Đã lập, xem commit lập kho. |
| File giá tuần đầu tiên | Đã có 2026-W45, thu thập 2026-11-04, 76/489 khóa, chưa hoàn chỉnh (commit `624b950`). |
| Seed giá tuần vào database hoặc `data/` | Chưa làm, cần quyết định riêng. |
| Hiển thị giá tuần trên trang web | Chưa làm, cần quyết định riêng. |
| Tổng tiền tuần ăn và quy đổi gói từ kho này | Chưa code, chỉ code sau khi kho có đủ tuần và đã được duyệt. |

## Cách đọc kho này trong 5 phút

1. Đọc file này để biết phạm vi và cấu trúc.
2. Đọc `SCHEMA.md` để biết từng trường và từng công thức quy đổi.
3. Đọc `WEEKLY-TASK.md` để biết một lần chạy hàng tuần phải làm những bước nào,
   dừng ở đâu và báo cáo ra sao.
4. Mở `weekly/TEMPLATE.json` để thấy hình dạng của một file tuần trống.
5. Chạy `python3 validate.py weekly/YYYY-Www.json` trước khi commit bất kỳ
   file tuần nào.
