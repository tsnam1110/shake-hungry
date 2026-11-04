# Task hàng ngày: cập nhật giá cho toàn bộ sản phẩm

> Tài liệu này là định nghĩa duy nhất của task hàng ngày. Task tự động
> (cron `daily-price-update`) và người làm thủ công đều chạy đúng các bước
> trong tài liệu này, không tự thêm bước, không tự bớt bước, không tự đổi
> phạm vi, kênh hay công thức. Khi tài liệu này và một quyết định mới của
> chủ dự án mâu thuẫn nhau, quyết định mới thắng nhưng phải sửa tài liệu
> này trong cùng lần thay đổi, không sửa miệng.

## 1. Task này là gì

Mỗi ngày, vào buổi sáng theo giờ Asia/Saigon, tạo một file giá ngày mới
trong kho `docs/04-data-sources/prices/daily/`, cập nhật file tuần tương
ứng trong `weekly/`, kiểm tra bằng script, rồi commit riêng file ngày đó
và file tuần đó. Task chỉ tạo tài liệu nghiên cứu, không seed vào app,
không hiển thị cho người dùng, không tự động push lên git.

## 2. Phạm vi không được tự đổi

- Sản phẩm: 489 khóa nguyên liệu, trong đó rau mùa và hoa quả đã được khử
  trùng lặp qua `ingredient_slug` (xem `README.md`). Món ăn và đồ uống
  không thuộc phạm vi.
- Kênh mặc định: đọc trực tiếp từ trang online của Bách Hóa Xanh và
  WinMart, chỉ đọc, không đăng nhập, không mua hàng, giỏ hàng giữ bằng 0.
- Kênh chợ: chỉ ghi khi có khảo sát trực tiếp tại chợ trong đúng ngày đó.
  Khi không đi chợ được thì ghi kênh chợ vào `missing` với lý do
  `can-khao-sat-truc-tiep`, không lấy giá siêu thị thay cho giá chợ.
- Miền: chỉ ghi `bac`, `trung`, `nam` khi vị trí giao hàng hoặc địa điểm
  chợ đã được chọn. Khi đọc online mà chưa chọn vị trí giao thì ghi
  `online-chua-chon-vi-tri`.
- Ngày: mọi giá trong một file ngày phải là giá đọc được trong đúng ngày
  đó. Không mang giá ngày trước sang, không dùng giá của bài báo không ghi
  ngày, không dùng giá của trang đã lưu từ ngày khác.

## 3. Các bước trong một lần chạy

### Bước 0: đọc luật trước khi thu thập

Đọc `README.md` và `SCHEMA.md` trong cùng thư mục kho. Đọc lại mục 2 của
`SCHEMA.md` mỗi khi gặp một dạng gói mới: gói theo g, theo kg, theo ml,
theo lít, theo cái, quả, con, hộp, mớ, thùng, lốc. Không bắt đầu thu thập
khi chưa đọc xong hai file đó trong lần chạy này.

### Bước 1: lập danh sách phạm vi của ngày hôm đó

Chạy lệnh sau từ gốc repo để lấy danh sách khóa nguyên liệu hiện có, rồi
đối chiếu với `scope_total` trong file ngày:

```bash
python3 -c "import json, glob; print(len([f for f in glob.glob('data/ingredients/*.json') if not f.endswith('_index.json')]))"
```

Nếu số lượng khác 489 thì dừng lại, ghi rõ số mới và lý do thay đổi vào
`notes` của file ngày, và báo cáo sự thay đổi này trong báo cáo cuối lần
chạy. Không tự ý giữ số 489 cũ khi kho nguyên liệu đã đổi, cũng không tự
ý đổi phạm vi sang một nhóm con cho dễ làm.

Kiểm tra xem file ngày hôm nay đã tồn tại chưa. Nếu đã tồn tại thì không
ghi đè. Đọc file đã có, chỉ bổ sung những dòng còn thiếu trong cùng ngày
đó, và ghi rõ trong `notes` là lần chạy này bổ sung, không phải tạo mới.
Không bao giờ tạo hai file cho cùng một ngày.

### Bước 2: thu thập theo kênh, ghi sự thật trước, tính sau

Thu thập theo thứ tự kênh: Bách Hóa Xanh online trước, WinMart online sau,
chợ trực tiếp cuối cùng nếu có. Với mỗi sản phẩm, ghi đúng bốn nhóm thông
tin theo thứ tự sau, không đảo ngược:

1. Sự thật từ trang bán: tên đúng như trang ghi, giá niêm yết, giá gốc
   nếu có, khuyến mãi đúng như trang ghi, cỡ gói đúng như trang ghi.
2. Nguồn: tên trang bán hoặc tên chợ, đường dẫn trang đã đọc hoặc ghi chú
   khảo sát tại chợ, ngày thu thập.
3. Kênh và miền đúng như đã chọn, không suy đoán.
4. Số suy ra: chỉ sau khi đã ghi xong ba nhóm trên mới tính giá/kg,
   giá/lít hoặc giá trên một đơn vị theo đúng công thức trong `SCHEMA.md`
   mục 2. Không copy giá/kg in sẵn trên trang bán.

Với mỗi sản phẩm không thu được, ghi ngay vào `missing` kèm lý do trong
danh sách cho phép ở `SCHEMA.md` mục 1, không để cuối ngày mới nhớ lại.
Với mỗi biến thể khác thương hiệu hoặc khác cỡ gói, ghi thành dòng riêng
với `variant_key` riêng, không gộp hai biến thể thành một dòng trung bình.

### Bước 3: kiểm tra bằng script, sửa sự thật chứ không sửa giá cho qua

Chạy lệnh sau từ gốc repo, thay ngày bằng ngày thu thập:

```bash
python3 docs/04-data-sources/prices/validate.py docs/04-data-sources/prices/daily/YYYY-MM-DD.json
```

Khi script báo lỗi, quay lại trang bán để đối chiếu sự thật ghi nhận hoặc
sửa công thức tính. Không bao giờ sửa giá niêm yết, cỡ gói hoặc số suy ra
chỉ để script chạy qua. Khi script chạy qua, vẫn đối chiếu lại một lượt
các dòng có cờ lệch giá in sẵn và các dòng thùng, lốc, mớ, hộp, vì script
chỉ kiểm tra hình thức và toán học, không kiểm tra được giá có đúng với
trang bán hay không.

### Bước 4: cập nhật file tuần, không gộp giá

Xác định tuần ISO bằng lệnh `date +%G-W%V` theo Asia/Saigon. Mở hoặc tạo
file `weekly/YYYY-Www.md` theo mẫu trong `weekly/TEMPLATE.md`, cập nhật
bảng chỉ mục của tuần: ngày nào đã có file, mỗi ngày thu được bao nhiêu
dòng trên tổng số khóa, còn thiếu bao nhiêu, có ghi chú gì. Không tính
trung bình giá các ngày trong tuần, không chọn một ngày đại diện cho cả
tuần, không ghi giá tuần.

### Bước 5: commit riêng, không commit kèm file khác

Trước khi commit, chạy `git status --porcelain` và kiểm tra xem có file
nào khác đang sửa dở hay không. Chỉ đưa đúng hai file vào commit: file
ngày hôm nay và file tuần của tuần đó, bằng lệnh `git add` với đường dẫn
đầy đủ của từng file. Không dùng `git add -A`, không dùng `git commit -a`,
không commit kèm bất kỳ file nào khác, kể cả file thuộc chat hoặc task
khác đang sửa dở.

Thông điệp commit viết theo mẫu sau, thay số liệu thật của lần chạy:

```text
gia: snapshot YYYY-MM-DD (thu duoc X/489 khoa, thieu Y khoa, kenh: ...)
```

Không push lên git trong lần chạy này. Push là quyết định riêng của chủ
dự án, theo quy tắc bundle của dự án.

### Bước 6: báo cáo ngắn, nói rõ phần chưa xong

Báo cáo cuối lần chạy phải có đúng các mục sau, không thêm nhận xét chung
chung, không bỏ mục nào:

- Ngày thu thập và đường dẫn file ngày, đường dẫn file tuần.
- Mẫu số: tổng số khóa trong phạm vi, số dòng đã thu được, số khóa còn
  thiếu, số dòng có cờ lệch giá in sẵn.
- Phân tầng theo kênh: mỗi kênh thu được bao nhiêu dòng, kênh nào không
  đọc được và vì sao.
- Danh sách các nhóm còn thiếu nhiều nhất, ví dụ sữa, gạo Japonica ở
  Bách Hóa Xanh, kênh chợ, mớ rau thơm, kèm lý do thiếu.
- Commit của lần chạy, hoặc lý do chưa commit khi script chưa chạy qua
  hoặc file ngày chưa hoàn chỉnh.
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
- Hết thời gian chạy trong ngày mà vẫn còn sản phẩm chưa thu. Dừng lại,
  báo cáo đúng số đã thu và số còn thiếu, không mang phần còn thiếu sang
  ngày hôm sau rồi ghi chung vào file ngày hôm sau. Ngày hôm sau bắt đầu
  lại từ đầu với file ngày mới.

Khi toàn bộ kênh đều không đọc được, vẫn tạo file ngày với `entries` rỗng
hoặc chỉ có những dòng đã thu được, `missing` ghi đầy đủ lý do, không
commit file đó như một lần cập nhật hoàn chỉnh, và báo cáo rõ là lần chạy
thất bại ở khâu thu thập.

## 5. Những việc cấm tuyệt đối trong task này

- Bịa giá, ước tính giá, lấy giá ngày trước, lấy giá kênh khác, lấy giá
  miền khác, lấy giá mặt hàng tương tự để lấp chỗ trống.
- Copy giá/kg in sẵn trên trang bán làm giá/kg trong kho.
- Đổi quả sang kg, con sang kg, mớ sang kg, ml sang kg khi chưa có khối
  lượng hoặc mật độ kèm nguồn của đúng mặt hàng đó.
- Gộp hai biến thể khác thương hiệu hoặc khác cỡ gói thành một dòng.
- Ghi đè file ngày đã có, ghi đè giá tuần thành giá trung bình, sửa lặng
  lẽ một dòng giá cũ mà không ghi chú đính chính.
- Seed file ngày vào database hoặc `data/` của app, hiển thị giá ngày
  lên trang web, hoặc tính tổng tiền tuần ăn từ kho này, khi chưa có quyết
  định riêng của chủ dự án cho từng việc đó.
- Dùng task này để mua hàng, đăng nhập tài khoản mua sắm, hoặc gửi thông
  tin tài khoản, mật khẩu, mã xác thực cho bất kỳ bên nào.

## 6. Cách một Dev hoặc Agent kế nhiệm chạy task này bằng tay

Không cần cron, chỉ cần làm đúng các bước trong mục 3 theo thứ tự, từ gốc
repo `shake-hungry`. Đọc `README.md` và `SCHEMA.md` trước, tạo file ngày
theo `daily/TEMPLATE.json`, chạy `validate.py`, cập nhật file tuần theo
`weekly/TEMPLATE.md`, commit riêng hai file đó, báo cáo theo mục 3 bước 6.
Khi không chắc về một mặt hàng, một cỡ gói hay một công thức, dừng lại và
hỏi chủ dự án, không tự chọn một phương án rồi báo là xong.

## 7. Cách task tự động được gắn vào dự án

Task tự động là cron có mã `daily-price-update`, chạy mỗi ngày một lần vào
buổi sáng theo giờ Asia/Saigon, với nội dung là toàn bộ tài liệu này. Cron
không chứa logic thu thập riêng, nó chỉ gọi Agent chạy đúng tài liệu này.
Khi muốn đổi giờ chạy, đổi kênh, đổi phạm vi hoặc đổi công thức, phải sửa
tài liệu này và sửa nội dung cron trong cùng một lần, rồi đọc lại cron
bằng `cron.view` để xác nhận. Khi muốn dừng task, tắt cron bằng
`cron.update` với `enabled: false` hoặc xóa bằng `cron.remove`, không chỉ
xóa file tài liệu, vì xóa file không dừng được lịch đã lưu.
