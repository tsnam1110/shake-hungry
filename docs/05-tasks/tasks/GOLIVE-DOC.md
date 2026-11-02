# GOLIVE-DOC — Tài liệu go-live production bằng Docker

**Trạng thái:** ✅ ĐÓNG 2026-11-02 — tài liệu đã viết xong, đối chiếu file thật, bản sao
bàn giao nằm ở thư mục goal `files/`.
**Phạm vi:** chỉ viết tài liệu. **Chưa deploy** — chủ dự án tự go-live theo tài liệu;
quyết định go-live (A7) thuộc chủ dự án, không thuộc đợt này.

## 1. Bàn giao

- **Bản gốc:** [docs/10-deployment/GOLIVE-DOCKER.md](../../10-deployment/GOLIVE-DOCKER.md)
  (875 dòng) — kiến trúc prod, chuẩn bị VPS, đưa code bằng bundle/clone, `.env.production`,
  khởi chạy + hành vi entrypoint, HTTPS cho `lacdoi.tsnam.name.vn` (Caddy khuyến nghị /
  nginx + certbot), checklist nghiệm thu bằng lệnh thật, vận hành, cập nhật/rollback,
  backup/khôi phục, checklist bảo mật, giới hạn đã biết, troubleshooting, checklist
  go-live một trang.
- **Bản sao:** `tai-lieu-golive-production-docker-2026-11-02.md` trong thư mục bàn giao
  của chủ dự án (nội dung giống hệt bản gốc).
- Mục lục [docs/README.md](../../README.md) đã thêm dòng GOLIVE-DOCKER;
  [DEPLOYMENT.md](../../10-deployment/DEPLOYMENT.md) §6 đã trỏ sang tài liệu mới.

## 2. Điểm đối chiếu quan trọng rút ra từ file thật

1. Lệnh production đúng là **overlay hai file** (`-f docker-compose.yml -f
   docker-compose.prod.yml`) — file prod không đứng một mình; tài liệu chuẩn hoá thêm
   `--env-file .env.production` ở mọi lệnh qua biến `$DC`.
2. **Bẫy nội suy Compose:** service `db` lấy mật khẩu qua `${DB_*}` được Compose nội
   suy từ môi trường shell + file `.env` mặc định, **không** đọc `.env.production`
   (file đó chỉ là `env_file` của `app`). Không có `--env-file .env.production`,
   MySQL khởi tạo bằng mật khẩu mặc định trong compose còn app dùng mật khẩu trong
   `.env.production` → app chờ DB 120 giây rồi thoát. Tài liệu ghi thành §4.3 + mục
   troubleshooting riêng, kèm lưu ý mật khẩu MySQL chỉ áp ở lần khởi tạo volume đầu.
3. Số nghiệm thu ghi theo **tiêu chí file ↔ DB khớp trên commit đã deploy** + lệnh đếm
   lại; số tham chiếu tại thời điểm viết: 654 món · 435 nguyên liệu · 82 quả ·
   1.087 file media; baseline seed trong container đã kiểm chứng ở DOCKER-001
   (HEAD `ae1a5d3`): 194 Mức 3 · 279 Mức 2 · 113 Mức 2 (có note) · 68 Mức 1,
   586 dòng `dish_nutrition`.
4. Mật khẩu DB dump không cần lộ ra shell: `mysqldump` chạy trong container `db`
   dùng biến `MYSQL_ROOT_PASSWORD` sẵn có trong môi trường container (§10.1 của
   tài liệu).

## 3. Điểm dừng có chủ đích trong tài liệu

- **Chính sách hiển thị công khai nguồn hạn chế** (cờ `usage_restricted`, hồ sơ quyết
  định 2026-11-01 — CLAUDE §0 đính chính lần 16 mục (6)): tài liệu đặt thành mục
  checklist **phải chốt trước khi mở công khai** (§11.3) và **không tự quyết** thay
  chủ dự án.
- Giới hạn Podman/Docker Engine và scheduler/queue chưa có service riêng được ghi
  trung thực ở §12 của tài liệu, kèm troubleshooting lần chạy đầu ở §13.

## 4. QT-7

- ROADMAP: bổ sung dòng DOCKER-001 (theo bản thảo §6 của DOCKER-001) + dòng
  GOLIVE-DOC vào bảng GĐ0 — cùng commit với tài liệu này.
- CLAUDE §0: bổ sung lần 46 (DOCKER-001 đóng + GOLIVE-DOC) — cùng commit. (Số 45
  đã được phiên T-06 dùng cho TCM-PROMOTE-004 — phiên đó commit `a55937d` vào
  giữa đợt này, khi cây đã sạch trở lại thì phần bổ sung của đợt này vào số 46.)
- Tại thời điểm bắt đầu đợt, `ROADMAP.md` và `CLAUDE.md` đều **sạch** trong git
  (không bị phiên song song giữ thay đổi chưa commit) nên phần bổ sung làm luôn
  trong đợt này, không hoãn. Commit dùng pathspec riêng, không cuốn các file dữ
  liệu đang dở của các phiên T-07/QD song song.
