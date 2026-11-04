# Schema file giá tuần và quy tắc quy đổi đơn vị, gói

File này là luật bắt buộc cho mọi lần thu thập giá vào kho
`docs/04-data-sources/prices/`. Agent hoặc Dev kế nhiệm phải đọc hết file này
trước lần thu thập đầu tiên và đọc lại phần quy đổi mỗi khi gặp một dạng gói
mới. Khi luật trong file này và thói quen làm nhanh mâu thuẫn nhau, luật trong
file này thắng.

## 1. Một file tuần có hình dạng gì

Tên file: `weekly/YYYY-Www.json`, ví dụ `weekly/2026-W45.json`. Tuần trong
tên file là tuần ISO của ngày thu thập, theo múi giờ Asia/Saigon, không phải
tuần viết file, không phải tuần commit, không phải tuần đăng bài nguồn.

Một file tuần gồm đúng bốn phần: `meta`, `entries`, `missing` và `notes`.
`meta` mô tả lần thu thập. `entries` là các dòng giá đã thu được. `missing`
là các khóa sản phẩm trong phạm vi 489 khóa mà lần này không thu được, kèm lý
do. `notes` là ghi chú về kênh, về khuyến mãi chung hoặc về sự cố trong lần
thu thập. File tuần không có phần nào khác.

Các trường trong `meta`:

- `collected_date`: ngày thu thập thật trong tuần đó, dạng `YYYY-MM-DD`,
  phải nằm trong đúng tuần ISO ghi trong tên file.
- `week_iso`: tuần ISO của ngày thu thập, ví dụ `2026-W45`, lấy bằng lệnh
  `date +%G-W%V` theo Asia/Saigon, và phải trùng tuần trong tên file.
- `scope_total`: tổng số khóa sản phẩm trong phạm vi, hiện là 489. Con số này
  chỉ thay đổi khi kho nguyên liệu thay đổi và phải ghi rõ lý do thay đổi
  trong `notes` của file tuần đầu tiên dùng số mới.
- `channels_attempted`: danh sách kênh đã thử đọc trong lần này.
- `collector`: `agent` khi do task hàng tuần chạy, `human` khi do người thu
  trực tiếp, kèm tên hoặc mã phiên làm việc nếu có.
- `source_rule`: luôn ghi câu này nguyên văn: giá chỉ ghi khi đọc trực tiếp
  từ trang bán hoặc khảo sát trực tiếp tại điểm bán trong đúng ngày thu thập.

Các trường trong mỗi dòng của `entries`, theo thứ tự ghi:

- `product_key`: slug nguyên liệu, ví dụ `gao-te`. Đây là khóa duy nhất để
  nối giá với nguyên liệu, rau và quả. Không dùng tên hiển thị làm khóa.
- `product_name_as_listed`: tên sản phẩm đúng như trang bán ghi, kể cả thương
  hiệu và biến thể, ví dụ `Gạo thơm đặc sản Neptune ST25 túi 5kg`.
- `channel`: kênh đọc giá, ví dụ `bhx-online`, `winmart-online`,
  `coop-online`, `aeon-eshop-online`, `lotte-online`, `go-bigc-online`,
  `meatdeli-online`, `cho-binh-dien`. Không gộp kênh. Mỗi kênh chỉ được
  ghi khi giá đọc trực tiếp từ trang bán hoặc khảo sát trực tiếp của
  kênh đó trong đúng ngày thu thập; giá từ bài báo trích lại không được
  ghi dưới tên kênh đó.
- `region`: `bac`, `trung`, `nam` chỉ khi vị trí giao hàng hoặc địa điểm chợ
  đã được chọn và ghi rõ. Khi đọc online mà chưa chọn vị trí giao, ghi
  `online-chua-chon-vi-tri`. Không suy đoán miền từ tên miền của website.
- `collected_date`: phải bằng `meta.collected_date`, không có ngoại lệ.
  Một file tuần chỉ có một ngày thu thập duy nhất, không trộn giá của hai
  ngày khác nhau trong cùng một tuần.
- `source_name`: tên trang bán hoặc tên chợ, ví dụ `Bách Hóa Xanh online`.
- `source_url`: đường dẫn tới trang danh mục hoặc trang sản phẩm đã đọc, khi
  có.
- `source_note`: ghi chú nguồn khi không có URL, ví dụ mô tả chợ, sạp, ngày
  và giờ khảo sát tại chợ. Không được để cả `source_url` và `source_note`
  đều trống trong cùng một dòng.
- `listed_price_vnd`: giá bán đang hiển thị tại thời điểm đọc, số nguyên
  đồng Việt Nam, không có số thập phân, không quy đổi sang ngoại tệ. Đây là
  sự thật ghi nhận được, là đầu vào duy nhất cho mọi phép tính sau đó.
- `original_price_vnd`: giá gốc khi trang ghi giảm giá, để trống khi trang
  không ghi giá gốc. Không tự suy giá gốc từ phần trăm giảm.
- `promo_note`: ghi chú khuyến mãi đúng như trang ghi, ví dụ `giảm 13%`,
  `mua 2 tặng 1`, `mua từ 2 túi 16.000 đ/túi`. Không tính giá hiệu dụng từ
  khuyến mãi rồi ghi đè lên `listed_price_vnd`.
- `pack_quantity`: số lượng trong một đơn vị bán, đúng như trang ghi.
- `pack_unit`: đơn vị của `pack_quantity`, đúng như trang ghi. Các giá trị
  được phép là `g`, `kg`, `ml`, `l`, `qua`, `cai`, `con`, `hop`, `goi`, `tui`,
  `thung`, `loc`, `vi`, `mo`, `bo`, `chai`, `lon`, `bich`, `khay`.
- `pack_weight_g`: chỉ ghi khi trang bán ghi rõ tổng khối lượng của đơn vị
  bán, ví dụ gà nguyên con 1,2 kg thì ghi 1200. Khi trang chỉ ghi số quả,
  số hộp, số con mà không ghi khối lượng thì để trống, không được tự điền
  khối lượng ước tính.
- `sub_unit_quantity` và `sub_unit_size`: chỉ dùng cho thùng và lốc. Ví dụ
  thùng 48 hộp 180 ml thì `pack_quantity` là 48, `pack_unit` là `thung`,
  `sub_unit_quantity` là 1, `sub_unit_size` là `180ml`. Khi không biết cỡ
  của từng đơn vị con thì để trống cả hai trường, không được suy đoán.
- `pack_size_note`: ghi chú về cỡ gói khi trang ghi không rõ, ví dụ `quy cách
  không hiển thị`, `giao ngẫu nhiên thương hiệu`. Trường này để giải thích,
  không để lấp số liệu thiếu.
- `price_per_kg_vnd`: số suy ra, xem mục 2. Để trống trong mọi trường hợp
  không tính được theo đúng công thức.
- `price_per_100g_vnd`: số suy ra cho hàng bán theo g, bằng giá niêm yết
  chia cho số g rồi nhân với 100, làm tròn tới đồng gần nhất. Các dạng gói
  khác không bắt buộc trường này.
- `price_per_litre_vnd`: số suy ra, xem mục 2. Để trống trong mọi trường
  hợp không tính được theo đúng công thức.
- `price_per_unit_vnd`: số suy ra cho hàng bán theo cái, quả, con, hộp, mớ,
  xem mục 2.
- `printed_price_per_kg_vnd`: giá/kg mà trang bán tự in sẵn, chỉ ghi để đối
  chiếu, không bao giờ dùng làm `price_per_kg_vnd`.
- `price_discrepancy`: `true` khi `printed_price_per_kg_vnd` có và khác số
  tự tính, kèm ghi chú trong `pack_size_note` về mức lệch. Khi không có giá
  in sẵn thì để trống.
- `variant_key`: mã biến thể để phân biệt các dòng cùng `product_key`,
  cùng kênh, cùng ngày nhưng khác thương hiệu hoặc khác cỡ gói. Cách tạo
  là nối thương hiệu và cỡ gói đúng như trang ghi, ví dụ
  `neptune-st25-tui-5kg`. Không dùng `variant_key` để gộp hai biến thể khác
  nhau thành một dòng.

Các trường trong mỗi dòng của `missing`:

- `product_key`: khóa sản phẩm không thu được.
- `channel`: kênh không thu được, chỉ ghi khi lý do thiếu chỉ đúng với một
  kênh, ví dụ tìm được ở Bách Hóa Xanh nhưng không tìm được ở WinMart.
  Khi để trống kênh thì dòng này có nghĩa là không thu được ở bất kỳ kênh
  nào đã thử, và khóa đó không được xuất hiện trong `entries`.
- `reason`: một trong các lý do sau, không viết lý do tự do ngoài danh sách
  này trừ khi ghi thêm chi tiết sau dấu hai chấm: `khong-tim-thay-trang`,
  `trang-khong-tai-duoc`, `khong-co-co-goi`, `chi-co-o-kenh-khac`,
  `can-khao-sat-truc-tiep`, `bi-chan-truy-cap`, `het-hang`.
- `detail`: chi tiết ngắn về lý do, ví dụ trang nào, lỗi gì, cần khảo sát ở
  đâu. Không ghi giá ước tính vào `detail`.

## 2. Quy tắc quy đổi: chỉ tính từ giá niêm yết và cỡ gói đã ghi

Nguyên tắc chung: mọi số suy ra đều phải tính lại từ `listed_price_vnd` và
cỡ gói đã ghi trong cùng một dòng, bằng đúng một công thức trong mục này.
Không copy số suy ra từ trang bán, không mượn cỡ gói của ngày khác, không
mượn cỡ gói của sản phẩm tương tự.

### 2.1. Hàng bán theo khối lượng

Khi `pack_unit` là `g`:

```text
price_per_kg_vnd  = listed_price_vnd / pack_quantity * 1000
price_per_100g_vnd = listed_price_vnd / pack_quantity * 100
```

Khi `pack_unit` là `kg`:

```text
price_per_kg_vnd = listed_price_vnd / pack_quantity
```

Ví dụ bắt buộc phải nhớ, vì đây là lỗi dễ mắc nhất: 10.000 đ cho gói
100 g quy ra 100.000 đ/kg, còn 10.000 đ cho gói 1.000 g quy ra 10.000 đ/kg.
Hai gói cùng giá niêm yết nhưng khác cỡ gói thì giá/kg khác nhau gấp mười
lần. So sánh hai giá niêm yết mà không quy đổi theo cỡ gói là sai, và ghi
giá/kg của gói này sang gói kia là sai.

Làm tròn: `price_per_kg_vnd` và `price_per_100g_vnd` làm tròn tới đồng gần
nhất, tức số nguyên. Không làm tròn `listed_price_vnd` và không làm tròn
`pack_quantity` trước khi tính.

### 2.2. Hàng bán theo thể tích

Khi `pack_unit` là `ml`:

```text
price_per_litre_vnd = listed_price_vnd / pack_quantity * 1000
```

Khi `pack_unit` là `l`:

```text
price_per_litre_vnd = listed_price_vnd / pack_quantity
```

Không tự đổi ml sang g hoặc sang kg. Chỉ khi hồ sơ nguyên liệu của đúng
`product_key` đó có trường `density_g_per_ml` kèm nguồn, mới được tính thêm
`price_per_kg_vnd` bằng công thức sau, và phải ghi nguồn mật độ vào
`pack_size_note`:

```text
kg_tren_mot_lit = density_g_per_ml
price_per_kg_vnd = price_per_litre_vnd / kg_tren_mot_lit
```

Ví dụ: dầu ăn có mật độ 0,92 g/ml thì một lít nặng 0,92 kg, nên giá
59.000 đ/lít quy ra khoảng 64.130 đ/kg. Khi không có mật độ kèm nguồn thì
`price_per_kg_vnd` để trống, giữ nguyên giá theo lít. Không dùng mật độ của
chất lỏng khác, không dùng mật độ bằng 1 cho mọi chất lỏng.

### 2.3. Hàng bán theo cái, quả, con, hộp, vỉ, mớ

Khi `pack_unit` là `qua`, `cai`, `con`, `hop`, `vi`, `mo` hoặc `bo`, và
`pack_weight_g` để trống:

```text
price_per_unit_vnd = listed_price_vnd / pack_quantity
price_per_kg_vnd   = (để trống)
price_per_litre_vnd = (để trống)
```

Không được tự điền khối lượng một quả, một con gà, một hộp trứng hay một mớ
rau để quy ra giá/kg. Trứng bán theo hộp 10 quả, bắp bán theo trái, gà bán
theo con, rau thơm bán theo mớ đều thuộc trường hợp này. Chỉ khi trang bán
ghi rõ tổng khối lượng thì mới ghi `pack_weight_g` và tính giá/kg từ khối
lượng đó theo mục 2.1.

Riêng mớ và bó rau thơm: giá chỉ ghi dạng đồng trên một mớ, không quy ra
đồng trên kg cho tới khi có khối lượng mớ cân thực tế tại điểm bán trong
đúng ngày thu thập. Cách cân được chấp nhận là cân 3 mớ, lấy trung bình,
ghi khối lượng trung bình, địa điểm cân, ngày cân vào `pack_size_note` và
chỉ dùng cho lần thu thập đó. Không dùng khối lượng mớ của ngày khác, của
chợ khác hay của gói siêu thị đã cân sẵn để thay cho mớ chợ.

### 2.4. Hàng bán theo thùng, lốc, gói nhiều đơn vị con

Khi `pack_unit` là `thung` hoặc `loc`, tổng dung tích hoặc tổng khối lượng
chỉ tính được khi biết cỡ của từng đơn vị con:

```text
tong_ml = pack_quantity * sub_unit_ml (khi sub_unit_size ghi theo ml)
tong_g  = pack_quantity * sub_unit_g  (khi sub_unit_size ghi theo g)
```

Sau đó tính giá theo lít hoặc theo kg từ tổng đó theo mục 2.1 và 2.2.
Khi không biết cỡ đơn vị con thì giữ nguyên `listed_price_vnd` theo thùng
hoặc lốc, để trống `price_per_kg_vnd` và `price_per_litre_vnd`, ghi rõ
`chua-biet-co-don-vi-con` vào `pack_size_note`. Không chia giá thùng cho
số hộp rồi gọi đó là giá một hộp khi chưa biết hộp đó cỡ bao nhiêu, và
không chia giá thùng cho một cỡ hộp suy đoán.

### 2.5. Khuyến mãi, mua kèm, combo

Giá niêm yết là giá của một đơn vị bán tại thời điểm đọc, không phải giá
hiệu dụng sau khuyến mãi. Các trường hợp sau đều giữ nguyên
`listed_price_vnd` và ghi khuyến mãi vào `promo_note`:

- Giảm phần trăm hoặc giảm giá gốc: ghi `original_price_vnd` và
  `promo_note`, tính giá/kg từ `listed_price_vnd`, không tính từ giá gốc.
- Mua kèm giảm giá cho sản phẩm thứ hai: giá của sản phẩm đầu tiên vẫn là
  `listed_price_vnd`, điều kiện mua kèm ghi vào `promo_note`.
- Mua nhiều tặng thêm: không chia giá cho số lượng tặng thêm để ra giá hiệu
  dụng. Giá hiệu dụng có thể ghi thêm vào `pack_size_note` dưới dạng ghi
  chú, nhưng không được dùng thay cho `listed_price_vnd`.
- Combo hai sản phẩm khác nhau: ghi thành một dòng riêng với
  `product_key` là sản phẩm chính và `promo_note` mô tả combo, hoặc ghi
  vào `notes` của file tuần và không đưa vào `entries` như một mặt hàng
  đơn. Không bao giờ lấy giá combo làm giá của một mặt hàng đơn trong
  combo đó.

### 2.6. Giá in sẵn trên trang bán

Khi trang bán in sẵn giá/kg hoặc giá/lít, ghi số đó vào
`printed_price_per_kg_vnd` hoặc vào `pack_size_note` để đối chiếu. Sau đó
vẫn tính lại `price_per_kg_vnd` từ giá/gói và cỡ gói theo mục 2.1. Nếu hai
số khác nhau thì giữ số tự tính, đặt `price_discrepancy` là `true` và ghi
mức lệch vào `pack_size_note`. Không bao giờ ghi đè số tự tính bằng số in
sẵn, kể cả khi số in sẵn trông tròn trịa hơn.

Ví dụ đã kiểm chứng trong snapshot 2026-11-04: ức gà phi lê có da ghi
24.900 đ cho gói 300 g, tự tính ra 83.000 đ/kg, trong khi trang in
93.000 đ/kg. Số đúng trong kho là 83.000 đ/kg, số 93.000 đ/kg chỉ giữ lại
làm tham khảo kèm cờ lệch.

### 2.7. Những phép tính bị cấm

- Đổi giá theo ngày: không lấy giá ngày trước làm giá ngày nay.
- Đổi giá theo kênh: không lấy giá siêu thị làm giá chợ và ngược lại.
- Đổi giá theo miền: không lấy giá miền Bắc làm giá miền Nam và ngược lại.
- Đổi giá theo biến thể: không lấy giá gói 2 kg làm giá gói 5 kg.
- Đổi giá theo mặt hàng tương tự: không lấy giá gạo ST25 làm giá gạo
  Japonica, không lấy giá thịt heo làm giá thịt gà.
- Đổi đơn vị không có căn cứ: không đổi quả sang kg, con sang kg, mớ sang
  kg, ml sang kg khi chưa có khối lượng hoặc mật độ kèm nguồn của đúng mặt
  hàng đó.
- Làm tròn trước khi tính: không làm tròn giá niêm yết, không làm tròn cỡ
  gói, chỉ làm tròn kết quả cuối cùng tới đồng gần nhất.

## 3. Ví dụ đã kiểm chứng, chỉ dùng để học công thức

Các ví dụ dưới đây lấy từ snapshot 2026-11-04 đã ghi trong kho làm việc của
dự án, chỉ dùng để minh họa công thức, không phải dữ liệu của kho giá này
và không được copy vào file tuần mới.

- Thịt đùi heo C.P: 34.500 đ cho gói 300 g. Giá/kg tự tính là
  34.500 / 300 * 1000 = 115.000 đ/kg.
- Ức gà phi lê có da: 24.900 đ cho gói 300 g. Giá/kg tự tính là
  24.900 / 300 * 1000 = 83.000 đ/kg, khác giá in sẵn 93.000 đ/kg, giữ số tự
  tính kèm cờ lệch.
- Dầu ăn Meizan: 105.000 đ cho chai 2 lít. Giá/lít tự tính là
  105.000 / 2 = 52.500 đ/lít. Không quy ra giá/kg khi chưa có mật độ kèm
  nguồn của đúng mặt hàng đó.
- Trứng gà tươi hộp 10 quả: 24.000 đ cho hộp 10 quả. Giá một quả tự tính là
  24.000 / 10 = 2.400 đ/quả. Giá/kg để trống vì trang không ghi khối lượng
  hộp trứng.
- Gạo thơm Neptune ST25 túi 5 kg: 124.000 đ cho túi 5 kg. Giá/kg tự tính là
  124.000 / 5 = 24.800 đ/kg. Không dùng số này cho túi 2 kg của cùng thương
  hiệu, mỗi cỡ túi là một dòng riêng.

## 4. Kiểm tra trước khi commit

Chạy script kiểm tra trong cùng thư mục kho:

```bash
python3 docs/04-data-sources/prices/validate.py docs/04-data-sources/prices/weekly/YYYY-Www.json
```

Script kiểm tra các lỗi sau và dừng lại ở lỗi đầu tiên nó gặp, theo thứ tự
này: tên file không khớp `week_iso`, `collected_date` không nằm trong tuần
ISO ghi trong tên file; thiếu trường bắt buộc; trùng
dòng cùng `product_key`, kênh và biến thể; `listed_price_vnd` không phải
số nguyên dương; giá/kg hoặc giá/lít suy ra không khớp công thức trong mục
2; giá/kg được ghi trong khi đơn vị bán là cái, quả, con, hộp, mớ mà không
có khối lượng; thùng hoặc lốc có giá/kg hoặc giá/lít trong khi chưa biết
cỡ đơn vị con; dòng trong `missing` lại xuất hiện trong `entries`; khóa
nguyên liệu chưa được tính vào `entries` lẫn `missing`; `product_key`
không nằm trong kho nguyên liệu; `scope_total` khác số khóa nguyên liệu
thực tế. Khi script báo lỗi, sửa sự thật ghi nhận hoặc công thức, không
bao giờ sửa giá để script chạy qua.

Script kiểm tra chỉ kiểm tra hình thức và toán học, không kiểm tra được
giá có đúng với trang bán hay không. Vì vậy script chạy qua không có nghĩa
là giá đã đúng, người thu thập vẫn phải đối chiếu lại từng dòng với trang
bán trước khi commit.
