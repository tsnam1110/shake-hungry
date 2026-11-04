# Kho giá theo ngày và theo tuần — Lắc Đói

> Trạng thái: kho và quy trình đã lập ngày 2026-11-04. Chưa có dữ liệu giá ngày nào
> được thu thập trong kho này. Mọi file ngày trong kho này đều là tài liệu nghiên
> cứu, CHƯA được seed vào app, CHƯA hiển thị cho người dùng, cho tới khi có quyết
> định riêng về việc seed và hiển thị.

## Kho này để làm gì

Kho này lưu giá bán lẻ quan sát được theo từng ngày, làm nền cho Tuần ăn và cho
dữ liệu giá theo mùa vụ, vùng miền của dự án. Nó không thay thế giá quả theo mùa
đã có trong `data/fruits/*.json` (bảng `fruit_prices`, snapshot 2026-10-31), mà bổ
sung thêm chuỗi thời gian theo ngày để sau này tính được tổng tiền đi chợ thật,
quy đổi gói thật và chi phí ước tính của món ăn theo tháng và miền.

Tài liệu gốc về phương pháp thu thập giá vẫn là
`../PRICE-COLLECTION-PLAN.md`. Kho này là phần vận hành hàng ngày của kế hoạch
đó, không phải một kế hoạch khác.

Kho này là kho tài liệu nghiên cứu, chưa phải nguồn sự thật của app. Theo
quy tắc QT-3 của dự án, dữ liệu dùng trong app phải nằm trong `data/` dưới
dạng file có version. Vì vậy khi một snapshot ngày được duyệt để dùng trong
app, việc đưa nó vào `data/` và cập nhật `DATA-MODEL` là một quyết định và
một commit riêng, không tự động xảy ra từ task hàng ngày này.

## Phạm vi: 489 khóa sản phẩm, không phải 671 hay 1.548

Phạm vi cập nhật hàng ngày là nguyên liệu, hoa quả và rau mùa, nhưng phải khử
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

Món ăn và đồ uống không thuộc phạm vi cập nhật giá hàng ngày này. Giá của một
món nấu sẵn phụ thuộc quán, khẩu phần và địa điểm, không tính ngược ra được từ
giá nguyên liệu theo cách đáng tin.

## Cấu trúc kho

```text
docs/04-data-sources/prices/
  README.md            # File này: mục đích, phạm vi, cách đọc kho.
  SCHEMA.md            # Schema và toàn bộ quy tắc quy đổi đơn vị, gói.
  DAILY-TASK.md        # Định nghĩa task hàng ngày cho Agent hoặc Dev kế nhiệm.
  validate.py          # Script kiểm tra file ngày, chạy trước khi commit.
  daily/
    TEMPLATE.json      # Mẫu file ngày, KHÔNG phải dữ liệu thật.
    YYYY-MM-DD.json    # Một file cho một ngày thu thập, xem chi tiết ở dưới.
  weekly/
    TEMPLATE.md        # Mẫu tổng kết tuần, chỉ lập chỉ mục, không gộp giá.
    YYYY-Www.md        # Một file cho một tuần ISO, ví dụ 2026-W45.md.
```

## Một ngày là một file, một tuần là một chỉ mục

- Mỗi ngày thu thập có đúng một file `daily/YYYY-MM-DD.json`. Mọi dòng trong
  file đó phải có cùng `collected_date` bằng đúng ngày trong tên file.
- Không bao giờ mang giá của ngày trước sang file của ngày sau. Ngày nào không
  thu được mặt hàng nào thì mặt hàng đó vào danh sách `missing`, kèm lý do,
  không được lấp bằng giá cũ, giá của kênh khác hay giá của mặt hàng tương tự.
- File tuần `weekly/YYYY-Www.md` chỉ lập chỉ mục: tuần đó có những file ngày
  nào, mỗi ngày thu được bao nhiêu trên 489 khóa, còn thiếu những gì, có ghi
  chú gì. File tuần KHÔNG tính trung bình giá của các ngày trong tuần thành
  một giá tuần, KHÔNG chọn một ngày đại diện cho cả tuần.
- Tuần ISO tính bằng lệnh `date +%G-W%V` theo múi giờ Asia/Saigon, ví dụ
  ngày 2026-11-04 thuộc tuần 2026-W45.

## Kênh và miền: ghi đúng cái đã thấy, không suy đoán

- Kênh là nơi giá được đọc trực tiếp, ví dụ `bhx-online`, `winmart-online`,
  hoặc `cho-<tên chợ>` khi khảo sát trực tiếp tại chợ. Mỗi kênh là một dòng
  riêng, không bao giờ gộp giá chợ và giá siêu thị thành một dòng, không bao
  giờ tính trung bình hai kênh.
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
| Kho, schema, task ngày, script kiểm tra | Đã lập, xem commit lập kho. |
| File giá ngày đầu tiên | Chưa có. Task hàng ngày sẽ tạo file đầu tiên vào lần chạy tới. |
| Seed giá ngày vào database hoặc `data/` | Chưa làm, cần quyết định riêng. |
| Hiển thị giá ngày trên trang web | Chưa làm, cần quyết định riêng. |
| Tổng tiền tuần ăn và quy đổi gói từ kho này | Chưa code, chỉ code sau khi kho có đủ ngày và đã được duyệt. |

## Cách đọc kho này trong 5 phút

1. Đọc file này để biết phạm vi và cấu trúc.
2. Đọc `SCHEMA.md` để biết từng trường và từng công thức quy đổi.
3. Đọc `DAILY-TASK.md` để biết một lần chạy hàng ngày phải làm những bước nào,
   dừng ở đâu và báo cáo ra sao.
4. Mở `daily/TEMPLATE.json` để thấy hình dạng của một file ngày trống.
5. Chạy `python3 validate.py daily/YYYY-MM-DD.json` trước khi commit bất kỳ
   file ngày nào.
