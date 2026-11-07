# DEPLOY — Production VPS

> ⚠️ **Đính chính 2026-11-07:** hướng dẫn cũ dùng MySQL host và script hỏi
> mật khẩu `lakdoi` nhưng giữ `DB_USERNAME=root`; chờ 5 giây rồi báo thành công.
> Quy trình thay thế dưới đây dùng MySQL riêng của Lắc Đói trong Docker,
> không publish cổng 3306, không dùng tài khoản/database của dự án khác.
> Nội dung cũ được giữ trong lịch sử Git.

## Triển khai hoặc cập nhật

Yêu cầu: Bash/Linux, Docker Engine đang chạy, Docker Compose hỗ trợ `up --wait` và
`--wait-timeout`, `openssl`, `curl`, `flock` (gói util-linux trên Ubuntu).
Script kiểm tra khả năng Compose trước khi thay container. Cần đủ RAM/đĩa để
build Node/PHP và chạy MySQL; xem `free -h` và `df -h` trước lần build đầu.

Tại thư mục repo trên VPS (ví dụ `/var/www/shake-hungry`):

```bash
bash deploy.sh
```

Lần đầu script hỏi URL HTTPS và email admin; sinh mật khẩu ngẫu nhiên riêng
cho MySQL root, user `lakdoi`, admin và lưu `.env.production` với quyền 600.
Nếu `.env.docker` tồn tại thì giữ APP_KEY cũ; không đọc mật khẩu database cũ.
File `.env.production` đã tồn tại được giữ nguyên khi chạy lại.
Thông tin đăng nhập admin nằm trong `ADMIN_EMAIL`/`ADMIN_PASSWORD` của file này;
đọc trực tiếp trên VPS, không gửi file lên chat hoặc commit.
Sau đăng nhập đầu có thể để trống ADMIN_PASSWORD để ngừng tạo admin tự động.
`golive.sh` và `update.sh` là lệnh tương thích, đều gọi `deploy.sh`.

Script khóa `.deploy.lock` để ngăn deploy đồng thời; build trước, khởi động bằng
`docker-compose.deploy.yml`, chờ MySQL tối đa 4 phút, PHP-FPM tối đa 15 phút,
web tối đa 90 giây. DB không bị tạo lại nếu cấu hình/image không đổi; app và web
được tạo lại cùng quy trình để Nginx không giữ IP app cũ. Script yêu cầu HTTP 200
từ host ở `/up`, `/` và `/admin`, không chấp nhận redirect thay thành công.
Không báo thành công nếu có bước thất bại. Thông báo thành công chỉ xác nhận
ứng dụng nội bộ; HTTPS bên ngoài cần proxy và DNS đúng.

## Database và dữ liệu hiện có

MySQL riêng có hostname `db`, cổng nội bộ 3306, database `shake_hungry`,
user `lakdoi`; volume mới `shake-hungry_production_db_data`.
Cổng 3306 của `educms-mysql` và các dự án khác giữ nguyên.
Không tự import dữ liệu người dùng từ MySQL cũ: đây là database mới, seed từ
`data/` khi trống. Nếu DB cũ đã có tài khoản/nhật ký/đóng góp cần giữ,
hãy sao lưu và chuyển dữ liệu trước khi chuyển dịch vụ; script không làm thay.
Các volume storage/logs của stack `shake-hungry` được giữ lại.

`data/` được bind trực tiếp từ repo VPS vào app. Entrypoint cấp quyền www-data
cho `data/combinations`, `data/vegetables`, `data/drinks` vì API quản trị ghi JSON
trực tiếp ở ba nhóm này. Mọi sửa đổi phải được kiểm tra bằng `git diff -- data`,
biên soạn/commit và đồng bộ về repo theo QT-3; không để chúng chỉ tồn tại trên VPS.
`deploy.sh` không tự pull hoặc ghi đè những file này. Trước lần chuyển từ Docker
cũ, nếu đã sửa module trong admin, sao lưu data của container cũ bằng `docker cp`
để đối chiếu trước khi tạo lại app; sửa đổi trong lớp image cũ không tự nhập về repo.
Đọc trang công khai sau biên tập module cần chạy `php artisan lacdoi:reload-modules`
trong container theo quy trình biên tập; deploy không tự ghi đè DB từ file.

Không xoá volume, không chạy `down -v`/`migrate:fresh` trên production.
Không đổi DB_PASSWORD/DB_ROOT_PASSWORD trong env của volume đã khởi tạo:
MySQL chỉ đọc các biến tạo tài khoản khi volume rỗng. Đổi mật khẩu phải qua SQL
và cập nhật cấu hình tương ứng, không xoá volume để sửa lỗi đăng nhập.
Giữ `.env.production` để bảo toàn APP_KEY và mật khẩu qua các lần cập nhật.
File `.env.docker` và `docker-compose.yml` vẫn dành cho cấu hình dev/host DB;
không trộn các lệnh đó với stack production mới.

Không export DB_PASSWORD/DB_ROOT_PASSWORD từ shell khi gọi Compose trực tiếp:
chúng có thể ưu tiên hơn `--env-file` ở bước nội suy. `deploy.sh` tự loại bỏ
hai biến này để DB và app dùng cùng cấu hình. File `.env.production` do script
sinh dùng key `DB_HOST=db`, `DB_USERNAME=lakdoi`, `DB_DATABASE=shake_hungry`
không dấu nháy/comment cuối dòng; script kiểm tra chính xác ba dòng này.

Stack giữ các phiên bản nền hiện có của dự án: PHP 8.3.36, nginx 1.31.7,
MySQL 8.0. Đợt sửa triển khai này không nâng MySQL 8.4 hoặc đổi dữ liệu volume;
nâng phiên bản DB cần backup và kiểm thử tương thích riêng.

## Reverse proxy trên host

Web production cố định `127.0.0.1:1113`. Nếu đang có Nginx thì giữ proxy hiện tại
và xác nhận `proxy_pass http://127.0.0.1:1113;`; không cần cài thêm Caddy.
Nếu dùng Caddy:

```caddyfile
lacdoi.tsnam.name.vn {
    reverse_proxy 127.0.0.1:1113
    encode gzip
}
```

## Kiểm tra và xử lý lỗi

```bash
docker compose --env-file .env.production -f docker-compose.deploy.yml ps -a
docker compose --env-file .env.production -f docker-compose.deploy.yml logs --tail=100 db app web
curl -f http://127.0.0.1:1113/up
curl -I https://lacdoi.tsnam.name.vn/
```

Nếu boot đầu mất hơn 15 phút, xem log trước rồi chạy lại `bash deploy.sh`;
không reset DB. Migration/cache chạy trong entrypoint; seed chỉ khi chưa có món.
Đổi container app được thực hiện cùng `up` để web nhận IP app mới.

Nếu có báo `.initial-seed-in-progress`, lần nạp dữ liệu đầu đã bị ngắt.
Entrypoint dừng để tránh phục vụ DB chỉ có một phần dữ liệu. Xem log xác định lỗi
và sửa nguyên nhân trước. Không xoá marker hoặc chạy seed lại trên DB đã nhận dữ
liệu người dùng; sao lưu và đánh giá khôi phục riêng. Với DB mới hoàn toàn,
chưa có người dùng/chỉnh sửa admin, có thể hoàn tất seed từ cùng bản mã nguồn:

```bash
DC="docker compose --env-file .env.production -f docker-compose.deploy.yml"
$DC stop web app
$DC run --rm --no-deps --entrypoint sh app -c 'php artisan db:seed --force && php artisan lacdoi:recompute-calories --with-ingredients && rm -f storage/app/.initial-seed-in-progress'
bash deploy.sh
```

Kiểm chứng sau triển khai (không reset database):

```bash
docker compose --env-file .env.production -f docker-compose.deploy.yml exec -T app php artisan lacdoi:validate-data
curl -fsS https://lacdoi.tsnam.name.vn/admin/api/login-options
```

Kỳ vọng validate không có lỗi, login-options trả `quick_login:false`; đăng nhập
admin, kiểm tra ảnh và thử nút Lắc trong trình duyệt. Sau restart, log phải ghi
bỏ qua seed. `/up` xanh không thay thế các kiểm tra chức năng này.

## Backup

Tạo thư mục backup trước, chạy trong Bash tại repo:

```bash
mkdir -p backups
chmod 700 backups
umask 077
set -o pipefail
docker compose --env-file .env.production -f docker-compose.deploy.yml exec -T db sh -c \
  'MYSQL_PWD="$MYSQL_PASSWORD" exec mysqldump -u lakdoi --single-transaction --no-tablespaces --routines --triggers shake_hungry' \
  | gzip > "backups/shake_hungry-$(date +%F-%H%M%S).sql.gz"
```

Sao lưu cả `.env.production`, volume storage và bản SQL ra nơi an toàn.
Sao lưu thêm toàn bộ `data/` (gồm sửa đổi chưa commit) trước khi cập nhật.
Kiểm `gzip -t` với file vừa tạo và kiểm tra mã thoát của pipeline; không dùng
file backup từ một lệnh thất bại. Script deploy không tự pull code, không tự
backup DB, không thực hiện rollback và không đảm bảo cập nhật không gián đoạn.

Khôi phục/rollback phải dừng ghi dữ liệu, sao lưu hiện trạng trước, chọn đúng
bản mã tương thích migration rồi mới import SQL. Không tự checkout code cũ và
chạy deploy khi schema đã thay đổi. Không chạy `down -v` để rollback.

## Kiểm chứng bộ triển khai trước khi đưa lên VPS

```bash
python3 tools/check-deploy.py
```

Kiểm tra này dùng Compose CLI thật để parse cấu hình và mô phỏng các nhánh script;
không thay cho build/boot MySQL/PHP/nginx thật hoặc nghiệm thu HTTPS trên VPS.
Tham chiếu cơ chế: [Compose environment precedence](https://docs.docker.com/compose/how-tos/environment-variables/envvars-precedence/),
[Compose up](https://docs.docker.com/reference/cli/docker/compose/up/),
[MySQL official image](https://hub.docker.com/_/mysql).

> **Cap nhat 2026-11-07 - hien thi terminal:** thong bao, cau hoi va loi do
> deploy.sh/golive.sh/update.sh va docker/entrypoint.sh phat ra dung tieng Viet
> khong dau (ASCII). APP_NAME van giu "Lắc Đói" de khong doi ten tren website.
> Khong chuyen doi du lieu data/ hoac thong bao cua thu vien/Artisan.
> Anh VPS chu du an gui cho thay build app/web va health db/app/web da dat;
> van can nghiem thu HTTPS, dang nhap va chuc nang. Chua danh dau DONE.
