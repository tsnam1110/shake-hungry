# Phương án link test ổn định cho Lắc Đói (2026-11-03)

> Trạng thái: **chưa chốt phương án, chưa mua/thuê gì**. Tài liệu này chỉ phân tích
> để chủ dự án quyết. Link Pinggy miễn phí hiện tại chỉ dùng để kiểm chứng local,
> **không phải link ổn định** (xem §1).

## 1. Vì sao Pinggy miễn phí không phải đáp án

- Hết hạn sau 60 phút; khởi động lại là đổi URL ngẫu nhiên mới.
- Đã đứt sớm nhiều lần trong ngày 2026-11-03 (lỗi Broken pipe sau ~9–11 phút) dù
  server local vẫn trả 200 — link gửi đi có thể chết trước khi người nhận mở.
- URL ngẫu nhiên, không gắn được tên miền riêng, không kiểm soát được uptime.

## 2. Các phương án

### A. Deploy Docker lên VPS (khuyến nghị nếu muốn link thật sự ổn định)

- Cách làm: thuê một VPS (Ubuntu), cài Docker Engine + Compose plugin, copy repo,
  điền `.env.production` theo `GOLIVE-DOCKER.md` §4, chạy `$DC up -d --build`,
  trỏ tên miền (hoặc dùng IP) về VPS, bật HTTPS theo §6 (Caddy tự cấp chứng chỉ
  hoặc nginx host + certbot).
- Ưu: link cố định, uptime do mình kiểm soát, đúng stack đã chuẩn bị sẵn
  (`Dockerfile`, `docker-compose.yml`, `GOLIVE-DOCKER.md`); không phụ thuộc tunnel.
- Nhược: tốn phí thuê VPS hàng tháng; cần người vận hành (cập nhật, backup DB,
  gia hạn chứng chỉ — Caddy tự động phần này); lần build thật đầu tiên chưa từng
  chạy thành công trên VM này (xem `GOLIVE-DOCKER.md` §13.0: kéo image Docker Hub
  bị timeout trong VM — trên VPS có mạng bình thường thì không gặp).
- Chi phí: **không ghi con số cụ thể ở đây** vì giá VPS thay đổi theo nhà cung cấp/
  cấu hình/region — chủ dự án chọn nhà cung cấp rồi đối chiếu bảng giá tại thời
  điểm chốt (mức phổ thông cho app này là VPS nhỏ nhất chạy được MySQL 8 + PHP-FPM
  + nginx; không cần cấu hình lớn).
- Cần chủ dự án cung cấp/chốt: nhà cung cấp VPS + tài khoản, region, tên miền
  (nếu muốn dùng tên miền riêng), ngân sách tháng tối đa.

### B. Pinggy Pro (tunnel trả phí, giữ nguyên cách chạy hiện tại)

- Cách làm: nâng cấp tài khoản Pinggy, cấu hình subdomain cố định + tunnel chạy
  nền ổn định thay vì tunnel miễn phí.
- Ưu: nhanh nhất, không cần server riêng, không đổi quy trình chạy local.
- Nhược: vẫn phụ thuộc một dịch vụ tunnel + máy local phải luôn bật (tắt máy là
  mất link); phí thuê bao hàng tháng; không phải môi trường production thật.
- Chi phí: theo bảng giá Pinggy tại thời điểm chốt (không ghi số cố định ở đây).
- Cần chủ dự án cung cấp/chốt: tài khoản Pinggy, subdomain muốn dùng, ngân sách.

### C. Cloudflare Tunnel (tunnel miễn phí, cần tên miền qua Cloudflare)

- Cách làm: cài `cloudflared` trên máy chạy app (hoặc một máy chủ nhỏ), tạo tunnel
  trỏ tới `127.0.0.1:8000` (hoặc tới stack Docker), gắn subdomain qua Cloudflare DNS.
- Ưu: miễn phí (không tính phí tunnel), URL cố định theo subdomain, có HTTPS tự
  động qua Cloudflare, không cần mở port trên router.
- Nhược: cần tên miền đã đưa nameserver về Cloudflare; vẫn phụ thuộc máy chạy app
  phải luôn bật (nếu chạy trên máy local) — muốn ổn định thật thì vẫn cần một máy
  chủ luôn bật (khi đó gần như phương án A nhưng nhẹ hơn); cấu hình ban đầu phức
  tạp hơn Pinggy.
- Chi phí: tunnel miễn phí; chỉ tốn phí tên miền (nếu chưa có).
- Cần chủ dự án cung cấp/chốt: tên miền + quyền chỉnh DNS Cloudflare, máy nào sẽ
  chạy tunnel 24/7.

## 3. Đề xuất thứ tự quyết

1. Nếu mục tiêu là **link test cho người khác mở trong vài ngày/tuần**: chọn **C**
   (nếu đã có tên miền) hoặc **B** (nếu muốn nhanh nhất).
2. Nếu mục tiêu là **đưa Lắc Đói lên production thật, dùng lâu dài**: chọn **A** —
   toàn bộ tài liệu deploy đã viết cho phương án này, chỉ còn bước chạy thật trên
   server có mạng Docker Hub bình thường.
3. Dù chọn phương án nào: **đừng gửi link Pinggy miễn phí như link chính thức**;
   mỗi lần gửi phải kiểm tra tunnel còn sống trước (lệnh `curl` tới URL public).
