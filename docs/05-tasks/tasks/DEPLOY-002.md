# DEPLOY-002 — Sửa go-live 502 và tách MySQL production

**Status:** 🔵 IN PROGRESS — mã nguồn và kiểm tra offline hoàn tất; chờ nghiệm thu container trên VPS.
**Ngày tạo / bắt đầu:** 2026-11-07
**Ngày hoàn thành:** —
**Phụ thuộc:** DOCKER-001 (đã hoàn tất).

## Yêu cầu và quyết định

Chủ dự án yêu cầu sửa Docker và deploy.sh sau lỗi 502: app dùng root trong khi
golive hỏi mật khẩu lakdoi; MySQL 3306 thuộc container của dự án khác.
Dùng MySQL riêng trong Docker, không publish cổng DB; web giữ 127.0.0.1:1113.
Không thay schema/API/dữ liệu data/. Không chạy hay sửa production trong phiên này.

## Thực hiện

- Thêm deploy.sh và docker-compose.deploy.yml: .env.production riêng, sinh
  mật khẩu ngẫu nhiên, giữ APP_KEY từ .env.docker cũ, giữ file khi deploy lại.
- golive.sh/update.sh gọi chung deploy.sh. Build trước; chờ health DB/app/web,
  kiểm HTTP /up; tạo lại container để Nginx nhận IP app mới; không prune image chung.
- Volume production_db_data mới tránh ghi đè DB cũ; không import người dùng cũ.
- Entrypoint báo mã lỗi xác thực MySQL ngay, không in mật khẩu.
- Cập nhật DEPLOY.md; bỏ hướng dẫn cũ sai bằng đính chính có lưu lịch sử Git.

## Kiểm chứng và giới hạn

- `python tools/check-deploy.py`: kiểm cú pháp 4 shell files; Compose CLI thật
  parse cấu hình; xác nhận không publish DB và đúng cổng web; mô phỏng thành công,
  lỗi container, lỗi HTTP, bảo toàn env cũ, tạo cấu hình mới giữ APP_KEY.
- `git diff --check`: chạy trước bàn giao.
- Chưa build/boot thực tế vì Docker Engine trên máy làm việc chưa chạy.
  Không tuyên bố đã sửa xong website VPS; cần chủ dự án chạy bash deploy.sh và
  kiểm HTTP/HTTPS, đăng nhập admin, seed, restart không seed đè theo DoD.
- Dữ liệu người dùng đã ghi vào MySQL cũ cần backup/import riêng trước chuyển dịch vụ.
  Không xoá volume hay reset DB để xử lý lỗi.

## Rà soát mở rộng 2026-11-07

- Đối chiếu toàn bộ tham chiếu triển khai trong README/CLAUDE/AGENTS, docs,
  compose, Dockerfile, CI và scripts. Không thẩm định lại nguồn/dinh dưỡng của
  từng hồ sơ data/ trong đợt rà soát hạ tầng này.
- Sửa tài liệu local cổng 1113, pcntl trên Windows, phân biệt root local và
  lakdoi production; thay link tài liệu đã xoá, gắn nhãn lịch sử cho task cũ.
- Ngăn cache bootstrap/*.php, backup SQL và storage/app local lọt vào image.
  Tầng test COPY tests trở lại; tầng production không giữ tests.
- Giữ DB khi cập nhật không đổi cấu hình; app/web tạo lại tuần tự, có health.
  Khóa deploy đồng thời; loại shell DB_PASSWORD/DB_ROOT_PASSWORD; xác nhận
  HTTP 200 trên /up, /, /admin thay vì coi redirect là thành công.
- DB root password không truyền sang PHP. Bind data/ từ repo VPS, cấp quyền
  cho ba thư mục JSON được admin ghi trực tiếp; đây là bản sửa lỗi bảo toàn file
  khi recreate, phù hợp QT-3. Backup cần cả data/ và DB + storage + env.
- Chặn khởi động khi seed đầu bị ngắt; lỗi count không còn bị nuốt. Quy trình
  phục hồi có điều kiện (chỉ DB mới, chưa biên tập) ghi trong DEPLOY.md.
- CI thêm job kiểm tra triển khai offline riêng, không phụ thuộc database test.

Kết quả kiểm tra bổ sung:
- Quét 180 file Markdown, 727 link nội bộ có đích tồn tại (bỏ code fences và
  inline code mẫu); không kiểm toàn bộ anchor hoặc URL nguồn dữ liệu ngoài.
- PHP lint đạt 3 đoạn PHP nhúng trong entrypoint; shell syntax đạt 4 files.
- Compose CLI thật parse production; mô phỏng build lỗi, container lỗi, HTTP lỗi,
  redirect, cấu hình cũ thiếu key; giữ env/key và không dùng mật khẩu shell.
- Registry xác nhận manifest php:8.3.36-fpm-bookworm và mysql:8.0; nginx registry
  timeout. Không kết luận image nginx lỗi hoặc không tồn tại từ timeout này.
- Docker Engine local không kết nối được; chưa chạy build/boot, nginx -t,
  seed thật, phục hồi backup hoặc kiểm thử chức năng trên stack production mới.
- Các bản PHP/nginx hiện có giữ nguyên; các khẳng định CVE trong lịch sử chưa
  được tái thẩm định trong đợt này. Nâng MySQL khỏi 8.0 là công việc riêng.

> **Cap nhat 2026-11-07 - hien thi terminal:** thong bao, cau hoi va loi do
> deploy.sh/golive.sh/update.sh va docker/entrypoint.sh phat ra dung tieng Viet
> khong dau (ASCII). APP_NAME van giu "Lắc Đói" de khong doi ten tren website.
> Khong chuyen doi du lieu data/ hoac thong bao cua thu vien/Artisan.
> Anh VPS chu du an gui cho thay build app/web va health db/app/web da dat;
> van can nghiem thu HTTPS, dang nhap va chuc nang. Chua danh dau DONE.
