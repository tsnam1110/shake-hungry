# Task hàng tuần: cập nhật giá cho toàn bộ sản phẩm

> Tài liệu này là định nghĩa duy nhất của task hàng tuần. Task tự động
> (cron `weekly-price-update`) và người làm thủ công đều chạy đúng các bước
> trong tài liệu này, không tự thêm bước, không tự bớt bước, không tự đổi
> phạm vi, kênh hay công thức. Khi tài liệu này và một quyết định mới của
> chủ dự án mâu thuẫn nhau, quyết định mới thắng nhưng phải sửa tài liệu
> này trong cùng lần thay đổi, không sửa miệng.

## 1. Task này là gì

Mỗi tuần một lần, vào sáng thứ Hai theo giờ Asia/Saigon, tạo một file giá
tuần mới trong kho `docs/04-data-sources/prices/weekly/`, kiểm tra bằng
script, rồi commit riêng file tuần đó. Một tuần chỉ có một file, và file
đó ghi giá đọc được trong đúng ngày chạy task, tức ngày thứ Hai đầu tuần
đó, trừ khi lần chạy phải dời sang ngày khác trong cùng tuần và ghi rõ
ngày thu thập thật vào `collected_date`. Task chỉ tạo tài liệu nghiên
cứu, không seed vào app, không hiển thị cho người dùng, không tự động
push lên git.

## 2. Phạm vi không được tự đổi

- Sản phẩm: 489 khóa nguyên liệu, trong đó rau mùa và hoa quả đã được khử
  trùng lặp qua `ingredient_slug` (xem `README.md`). Món ăn và đồ uống
  không thuộc phạm vi.
- Kênh mặc định, mở rộng ngày 2026-11-04 theo yêu cầu của chủ dự án (lần
  chạy W45 chỉ có 2 kênh nên chỉ thu được 76/489 khóa): đọc trực tiếp từ
  trang online của các siêu thị sau, theo thứ tự này, chỉ đọc, không đăng
  nhập, không mua hàng, giỏ hàng giữ bằng 0:
  1. Bách Hóa Xanh online (`bhx-online`).
  2. WinMart online (`winmart-online`).
  3. Co.op Online (`coop-online`, trang cooponline.vn) — kênh bổ sung mạnh
     nhất cho thịt, cá, rau, quả, gạo, trứng vì có danh mục giá trực tiếp.
  4. AEON ESHOP (`aeon-eshop-online`, trang aeoneshop.com).
  5. Lotte Mart online (`lotte-online`, trang lotte.vn hoặc ứng dụng
     Lotte ON).
  6. GO! / Big C online (`go-bigc-online`, chủ yếu qua ứng dụng).
  Kênh nào yêu cầu đăng nhập mới xem được giá, bắt chọn vị trí giao mà
  không cho xem giá chung, hoặc chỉ có trên ứng dụng mà trình duyệt không
  đọc được, thì dừng kênh đó, ghi vào `missing` và ghi rõ trong `notes`
  là kênh đó chưa đọc được trong tuần này, không dùng giá từ bài báo
  trích lại để thay.
- Kênh chuyên biệt, mỗi kênh là một dòng riêng, không bao giờ tính trung
  bình với giá siêu thị: MEATDeli online (`meatdeli-online`) cho thịt heo
  mát và các gian hàng chính hãng khác chỉ khi đó là trang bán chính thức
  của thương hiệu, có ghi cỡ gói. Giá thương hiệu không thay cho giá
  siêu thị và ngược lại.
- Kênh chợ: chỉ ghi khi có khảo sát trực tiếp tại chợ trong đúng ngày thu
  thập của tuần đó. Khi không đi chợ được thì ghi kênh chợ vào `missing`
  với lý do `can-khao-sat-truc-tiep`, không lấy giá siêu thị thay cho
  giá chợ. Bảng giá chợ đầu mối chỉ được dùng khi mở trực tiếp trang
  chính thức của chợ đó trong đúng ngày thu thập và trang đó ghi rõ ngày;
  bài báo, blog hay trang tổng hợp đăng lại giá chợ không phải là nguồn,
  dù số đó trông hợp lý.
- Logic dự phòng khi không có trên kênh chính, thêm ngày 2026-11-04 theo
  yêu cầu của chủ dự án: chỉ khi một khóa đã tìm thật trên tất cả kênh
  chính ở trên mà không thấy, mới được tìm kiếm trên mạng trong đúng
  ngày thu thập đó, theo đúng thứ tự sau. Tìm kiếm web không bao giờ
  thay cho việc thử kênh chính, và không mang giá tìm được tuần này
  sang tuần sau:
  1. Trang bán chính thức khác tìm qua tìm kiếm: siêu thị khác, gian
     hàng chính hãng trên sàn, trang web của thương hiệu, có ghi giá và
     cỡ gói. Đây là trang bán trực tiếp, ngày quan sát là ngày thu thập.
     Ghi `channel` là `web-search`, `source_name` là tên người bán tìm
     được, `source_url` là trang đó, `source_note` ghi rõ đã tìm kênh
     chính nào trước và tìm qua tìm kiếm web ngày nào.
  2. Bảng giá chính thức có ngày: Sở Công Thương, ban quản lý chợ, chợ
     đầu mối chính thức, có ghi rõ ngày thu thập. Ghi tương tự, `channel`
     là `web-search`.
  3. Bài báo hoặc trang tổng hợp chỉ được dùng ở bước cuối, khi bài đó
     ghi rõ ngày thu thập và trích đúng một người bán cụ thể với giá và
     cỡ gói, và không có nguồn trực tiếp nào ở bước 1 và 2. Khi đó vẫn
     ghi `channel` là `web-search`, `source_name` là tên bài báo cộng tên
     người bán được trích, và `source_note` ghi rõ đây là giá qua bài
     báo, không phải đọc trực tiếp trang bán, để báo cáo phân tầng được.
     Bài báo không ghi ngày, bài cũ khác ngày thu thập, bài chỉ ghi giá
     mà không ghi cỡ gói hoặc người bán, bài ghi giá của mặt hàng tương
     tự đều không được dùng.
  Khi cả ba bước đều không đủ điều kiện thì vẫn ghi `missing`, kèm
  `detail` ghi rõ đã thử kênh chính nào và đã tìm kiếm web ngày nào mà
  không tìm được trang bán trực tiếp. Báo cáo cuối lần chạy phải tách
  riêng số dòng kênh chính và số dòng `web-search`, để chủ dự án thấy rõ
  phần nào là giá trực tiếp, phần nào là giá qua tìm kiếm.
- `channels_attempted` trong `meta` phải ghi đúng các kênh đã thử đọc
  trong tuần đó. Kênh chưa thử thì ghi vào `notes` là chưa thử, không ghi
  vào `missing` như thể đã tìm mà không thấy. Lý do `khong-tim-thay-trang`
  chỉ dùng khi đã tìm thật trên kênh đó mà không thấy mặt hàng.
- Miền: chỉ ghi `bac`, `trung`, `nam` khi vị trí giao hàng hoặc địa điểm
  chợ đã được chọn. Khi đọc online mà chưa chọn vị trí giao thì ghi
  `online-chua-chon-vi-tri`.
- Ngày thu thập: mọi giá trong một file tuần phải là giá đọc được trong
  đúng ngày thu thập ghi trong `collected_date` của file đó, và ngày đó
  phải nằm trong đúng tuần ISO ghi trong tên file. Không mang giá tuần
  trước sang, không dùng giá của bài báo không ghi ngày, không dùng giá
  của trang đã lưu từ tuần khác, không trộn giá của hai ngày khác nhau
  vào cùng một file tuần.

## 3. Các bước trong một lần chạy

### Bước 0: đọc luật trước khi thu thập

Đọc `README.md` và `SCHEMA.md` trong cùng thư mục kho. Đọc lại mục 2 của
`SCHEMA.md` mỗi khi gặp một dạng gói mới: gói theo g, theo kg, theo ml,
theo lít, theo cái, quả, con, hộp, mớ, thùng, lốc. Không bắt đầu thu thập
khi chưa đọc xong hai file đó trong lần chạy này.

### Bước 1: lập danh sách phạm vi của tuần đó

Chạy lệnh sau từ gốc repo để lấy danh sách khóa nguyên liệu hiện có, rồi
đối chiếu với `scope_total` trong file tuần:

```bash
python3 -c "import json, glob; print(len([f for f in glob.glob('data/ingredients/*.json') if not f.endswith('_index.json')]))"
```

Nếu số lượng khác 489 thì dừng lại, ghi rõ số mới và lý do thay đổi vào
`notes` của file tuần, và báo cáo sự thay đổi này trong báo cáo cuối lần
chạy. Không tự ý giữ số 489 cũ khi kho nguyên liệu đã đổi, cũng không tự
ý đổi phạm vi sang một nhóm con cho dễ làm.

Xác định tuần ISO bằng lệnh `date +%G-W%V` theo Asia/Saigon. Kiểm tra xem
file tuần đó đã tồn tại chưa. Nếu đã tồn tại thì không ghi đè. Đọc file
đã có, chỉ bổ sung những dòng còn thiếu trong cùng ngày thu thập đó, và
ghi rõ trong `notes` là lần chạy này bổ sung, không phải tạo mới. Không
bao giờ tạo hai file cho cùng một tuần, và không bao giờ bổ sung giá của
một ngày khác vào file tuần đã có.

### Bước 2: thu thập theo kênh, ghi sự thật trước, tính sau

Thu thập theo thứ tự kênh trong mục 2, và theo nhóm mặt hàng, không chỉ
chuyển đổi nhóm đã có từ snapshot tuần trước. Bài học từ tuần 2026-W45:
file W45 chỉ thu được 76/489 khóa, trong đó 0 khóa trái cây và 0 khóa
thủy sản, vì snapshot 2026-11-04 gốc vốn không có nhóm trái cây và thủy
sản — thiếu không phải vì siêu thị không bán mà vì lần thu đó không tìm
nhóm đó. Vì vậy mỗi lần chạy phải tìm đủ các nhóm sau trên từng kênh đã
thử, dùng tên nguyên liệu và tên gọi khác của nó (ví dụ tìm cả tên rau,
tên quả và tên nguyên liệu): thịt, thủy sản, rau củ, trái cây, rau gia
vị và lá đặc sản, gia vị khô, tinh bột và bánh, sữa và trứng, dầu mỡ và
nước chấm, nhóm khác. Nhóm nào tìm rồi mà không thấy thì ghi `missing`
kèm kênh đã tìm; nhóm nào chưa tìm thì ghi vào `notes` là chưa tìm, không
được để `missing` ghi chung chung như thể đã tìm hết.

Bước 2b: tìm kiếm web dự phòng, chỉ cho các khóa đã tìm hết kênh chính
mà vẫn thiếu. Với mỗi khóa thiếu, tìm trên mạng trong đúng ngày thu thập
bằng tên nguyên liệu và tên gọi khác, kèm từ khóa giá và cỡ gói nếu có.
Làm đúng thứ tự ba bước trong mục 2: trang bán chính thức khác trước,
bảng giá chính thức có ngày sau, bài báo ghi rõ ngày và người bán cuối
cùng. Ghi dòng dự phòng với `channel` là `web-search`, không ghi dưới tên
kênh chính, và ghi rõ trong `source_note` là dòng dự phòng. Khi tìm kiếm
web cũng không đủ điều kiện thì giữ nguyên `missing`, không ước tính.

Với mỗi sản phẩm, ghi đúng bốn nhóm thông tin theo thứ tự sau, không đảo
ngược:

1. Sự thật từ trang bán: tên đúng như trang ghi, giá niêm yết, giá gốc
   nếu có, khuyến mãi đúng như trang ghi, cỡ gói đúng như trang ghi.
2. Nguồn: tên trang bán hoặc tên chợ, đường dẫn trang đã đọc hoặc ghi chú
   khảo sát tại chợ, ngày thu thập.
3. Kênh và miền đúng như đã chọn, không suy đoán.
4. Số suy ra: chỉ sau khi đã ghi xong ba nhóm trên mới tính giá/kg,
   giá/lít hoặc giá trên một đơn vị theo đúng công thức trong `SCHEMA.md`
   mục 2. Không copy giá/kg in sẵn trên trang bán.

Với mỗi sản phẩm không thu được, ghi ngay vào `missing` kèm lý do trong
danh sách cho phép ở `SCHEMA.md` mục 1, không để cuối lần chạy mới nhớ
lại. Với mỗi biến thể khác thương hiệu hoặc khác cỡ gói, ghi thành dòng
riêng với `variant_key` riêng, không gộp hai biến thể thành một dòng
trung bình.

### Bước 3: kiểm tra bằng script, sửa sự thật chứ không sửa giá cho qua

Chạy lệnh sau từ gốc repo, thay tuần bằng tuần thu thập:

```bash
python3 docs/04-data-sources/prices/validate.py docs/04-data-sources/prices/weekly/YYYY-Www.json
```

Khi script báo lỗi, quay lại trang bán để đối chiếu sự thật ghi nhận hoặc
sửa công thức tính. Không bao giờ sửa giá niêm yết, cỡ gói hoặc số suy ra
chỉ để script chạy qua. Khi script chạy qua, vẫn đối chiếu lại một lượt
các dòng có cờ lệch giá in sẵn và các dòng thùng, lốc, mớ, hộp, vì script
chỉ kiểm tra hình thức và toán học, không kiểm tra được giá có đúng với
trang bán hay không.

### Bước 4: commit riêng, không commit kèm file khác

Trước khi commit, chạy `git status --porcelain` và kiểm tra xem có file
nào khác đang sửa dở hay không. Chỉ đưa đúng một file vào commit: file
tuần của tuần đó, bằng lệnh `git add` với đường dẫn đầy đủ của file đó.
Không dùng `git add -A`, không dùng `git commit -a`, không commit kèm bất
kỳ file nào khác, kể cả file thuộc chat hoặc task khác đang sửa dở.

Thông điệp commit viết theo mẫu sau, thay số liệu thật của lần chạy:

```text
gia: snapshot tuan YYYY-Www, thu thap YYYY-MM-DD (thu duoc X/489 khoa, thieu Y khoa, kenh: ...)
```

Không push lên git trong lần chạy này. Push là quyết định riêng của chủ
dự án, theo quy tắc bundle của dự án.

### Bước 5: báo cáo ngắn, nói rõ phần chưa xong

Báo cáo cuối lần chạy phải có đúng các mục sau, không thêm nhận xét chung
chung, không bỏ mục nào:

- Tuần ISO, ngày thu thập và đường dẫn file tuần.
- Mẫu số: tổng số khóa trong phạm vi, số dòng đã thu được, số khóa còn
  thiếu, số dòng có cờ lệch giá in sẵn.
- Phân tầng theo kênh: mỗi kênh trong mục 2 thu được bao nhiêu dòng,
  kênh nào không đọc được và vì sao, kênh nào chưa thử đọc trong tuần
  này, và riêng số dòng `web-search` dự phòng là bao nhiêu, trong đó bao
  nhiêu dòng là trang bán trực tiếp tìm qua tìm kiếm, bao nhiêu dòng là
  qua bài báo. Không báo một kênh là không có hàng khi thực ra chưa thử
  kênh đó, và không gộp dòng `web-search` vào số dòng kênh chính.
- Danh sách các nhóm còn thiếu nhiều nhất, ví dụ sữa, gạo Japonica ở
  Bách Hóa Xanh, kênh chợ, mớ rau thơm, kèm lý do thiếu.
- Commit của lần chạy, hoặc lý do chưa commit khi script chưa chạy qua
  hoặc file tuần chưa hoàn chỉnh.
- Câu nói rõ ràng rằng lần chạy này có hoàn chỉnh hay không. Khi chưa thu
  đủ toàn bộ sản phẩm thì phải nói là chưa hoàn chỉnh, không được báo là
  đã cập nhật xong toàn bộ sản phẩm.

## 4. Khi nào phải dừng lại thay vì làm tiếp

Dừng kênh đang đọc và ghi kênh đó vào `missing` khi gặp một trong các
trường hợp sau, không thử đường vòng khác:

- Trang bán chặn truy cập, yêu cầu đăng nhập để xem giá, hoặc hiển thị
  vòng tải kéo dài mà không hiện giá. Không dùng mirror chưa xác minh,
  không dùng ảnh chụp màn hình không rõ nguồn, không dùng giá từ bài báo
  trích lại khi trang gốc không đọc được.
- Trang bán không ghi cỡ gói, không ghi đơn vị, hoặc ghi cỡ gói mập mờ tới
  mức không tính được giá/kg, giá/lít hay giá trên một đơn vị theo
  `SCHEMA.md`. Khi đó giữ nguyên giá niêm yết kèm ghi chú, để trống số
  suy ra, không suy đoán cỡ gói.
- Phát hiện một mặt hàng có tên gần trùng với mặt hàng khác nhưng chưa
  chắc có phải cùng một sản phẩm hay không. Giữ nguyên hai dòng như trang
  hiện, không tự gộp, ghi nghi vấn vào `notes`.
- Hết thời gian chạy trong tuần mà vẫn còn sản phẩm chưa thu. Dừng lại,
  báo cáo đúng số đã thu và số còn thiếu, không mang phần còn thiếu sang
  tuần sau rồi ghi chung vào file tuần sau. Tuần sau bắt đầu lại từ đầu
  với file tuần mới.

Khi toàn bộ kênh đều không đọc được, vẫn tạo file tuần với `entries` rỗng
hoặc chỉ có những dòng đã thu được, `missing` ghi đầy đủ lý do, không
commit file đó như một lần cập nhật hoàn chỉnh, và báo cáo rõ là lần chạy
thất bại ở khâu thu thập.

## 5. Những việc cấm tuyệt đối trong task này

- Bịa giá, ước tính giá, lấy giá tuần trước, lấy giá kênh khác, lấy giá
  miền khác, lấy giá mặt hàng tương tự để lấp chỗ trống.
- Copy giá/kg in sẵn trên trang bán làm giá/kg trong kho.
- Đổi quả sang kg, con sang kg, mớ sang kg, ml sang kg khi chưa có khối
  lượng hoặc mật độ kèm nguồn của đúng mặt hàng đó.
- Gộp hai biến thể khác thương hiệu hoặc khác cỡ gói thành một dòng.
- Ghi đè file tuần đã có, ghi đè giá tuần thành giá trung bình, sửa lặng
  lẽ một dòng giá cũ mà không ghi chú đính chính.
- Seed file tuần vào database hoặc `data/` của app, hiển thị giá tuần
  lên trang web, hoặc tính tổng tiền tuần ăn từ kho này, khi chưa có quyết
  định riêng của chủ dự án cho từng việc đó.
- Dùng task này để mua hàng, đăng nhập tài khoản mua sắm, hoặc gửi thông
  tin tài khoản, mật khẩu, mã xác thực cho bất kỳ bên nào.

## 6. Cách một Dev hoặc Agent kế nhiệm chạy task này bằng tay

Không cần cron, chỉ cần làm đúng các bước trong mục 3 theo thứ tự, từ gốc
repo `shake-hungry`. Đọc `README.md` và `SCHEMA.md` trước, tạo file tuần
theo `weekly/TEMPLATE.json`, chạy `validate.py`, commit riêng file tuần
đó, báo cáo theo mục 3 bước 5. Khi không chắc về một mặt hàng, một cỡ
gói hay một công thức, dừng lại và hỏi chủ dự án, không tự chọn một
phương án rồi báo là xong.

## 7. Cách task tự động được gắn vào dự án

Task tự động là cron có mã `weekly-price-update`, chạy mỗi tuần một lần
vào sáng thứ Hai theo giờ Asia/Saigon, với nội dung là toàn bộ tài liệu
này. Cron không chứa logic thu thập riêng, nó chỉ gọi Agent chạy đúng
tài liệu này. Khi muốn đổi giờ chạy, đổi ngày chạy, đổi kênh, đổi phạm
vi hoặc đổi công thức, phải sửa tài liệu này và sửa nội dung cron trong
cùng một lần, rồi đọc lại cron bằng `cron.view` để xác nhận. Khi muốn
dừng task, tắt cron bằng `cron.update` với `enabled: false` hoặc xóa
bằng `cron.remove`, không chỉ xóa file tài liệu, vì xóa file không dừng
được lịch đã lưu.
