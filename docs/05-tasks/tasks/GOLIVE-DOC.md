# GOLIVE-DOC — Tài liệu go-live production bằng Docker

> ⚠️ **Đính chính 2026-11-07:** nội dung/lệnh bên dưới là lịch sử kiểm chứng, không phải runbook hiện hành. Production: [DEPLOY](../../10-deployment/DEPLOY.md), `bash deploy.sh` + `docker-compose.deploy.yml` + `.env.production`. Local: [SETUP](../../10-deployment/SETUP.md). Các compose prod/host overlay và file env mẫu cũ đã bỏ.

**Trạng thái:** ✅ ĐÓNG 2026-11-02 — tài liệu đã viết xong, đối chiếu file thật, bản sao
bàn giao nằm ở thư mục goal `files/`.
**Phạm vi:** chỉ viết tài liệu. **Chưa deploy** — chủ dự án tự go-live theo tài liệu;
quyết định go-live (A7) thuộc chủ dự án, không thuộc đợt này.

## 1. Bàn giao

- **Bản gốc:** [docs/10-deployment/GOLIVE-DOCKER.md](../../10-deployment/DEPLOY.md)
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

## 5. Soát lại 2026-11-02 (REVIEW-GOLIVE-DOC — theo yêu cầu chủ dự án trước deploy)

Soát toàn bộ tài liệu trên HEAD `a3bff4f` (cuối đợt xử lý tồn đọng), đối chiếu lại
mọi file nguồn sự thật (Dockerfile, 2 compose, entrypoint, nginx/php/fpm config,
mysql-init, 2 env mẫu, .dockerignore, .gitignore, routes).

**Đã chạy thật (không cần daemon):** `docker compose config` (Compose 2.40.3) cho 3
tổ hợp với env giả từ `.env.production.example` đã điền — (1) dev: db publish 3306,
web 8000; (2) prod overlay + `--env-file .env.production`: **db không publish cổng**,
web publish 80, restart `unless-stopped` cả 3, nội suy `MYSQL_*` của `db` và env của
`app` đều lấy đúng giá trị từ `.env.production`; (3) prod + `docker-compose.host.yml`
mẫu ở §6.2: web render đúng `127.0.0.1:8080:80`. Lệnh sinh APP_KEY bằng `php -r` ở
§4.2 chạy thật đạt trên PHP 8.3.6. Đếm lại từ `data/`: 654 món · 436 NL · 82 quả ·
1.090 file media — khớp census cuối đợt.

**Lỗi đã sửa (trong GOLIVE-DOCKER.md + đồng bộ bản sao goal `files/`):**
1. **[Gây lỗi vận hành]** §1.2: lệnh mẫu chạy production thiếu `--env-file
   .env.production` — copy nguyên văn là dính đúng bẫy §4.3. Đã bổ sung + ghi chú.
   Cùng lỗi ở comment đầu `docker-compose.prod.yml` — đã sửa, commit riêng (chỉ comment).
2. **[Số liệu trôi]** §7.2 còn tham chiếu baseline cũ của DOCKER-001 (194·279·113·68,
   435 NL, 586 dòng) → thay bằng census tại `a3bff4f`: **215·245·119·75**, **436 NL**,
   579 dòng `dish_nutrition`, ghi chú 426/654, media 654/654, phủ TCM ≥60% = 511.
   §1.3: 1.087 → 1.090 file media.
3. **[Gây nhầm lẫn]** §5.2: con số "7.793 dòng tham chiếu" không có nguồn trong nhật
   ký kiểm thử DOCKER-001 → bỏ con số, giữ mô tả định tính.
4. **[Thiếu khớp]** §11.3 nói kiểm 3 trang pháp lý ở §7.4 nhưng vòng smoke §7.4 chưa
   có chúng → bổ sung `/nguon-du-lieu`, `/chinh-sach-bao-mat`, `/mien-tru-y-te`
   (đã kiểm route tồn tại thật trong `routes/web.php`).
5. **[Gây nhầm lẫn]** §3.1: lệnh nhận bundle khi cập nhật còn theo mẫu cũ `fetch HEAD`
   trong khi bundle từ #6 mang ref `dev` rõ ràng → chuẩn hoá `fetch … dev`, giữ
   `fetch HEAD` làm ngoại lệ cho bundle cũ kèm cách kiểm `list-heads`.
6. **[Nhỏ]** §13.1: bỏ nhắc "!env_file tags" (thẻ YAML compose thực tế chỉ có
   `!override` trong file prod).

**Xác nhận đúng, không phải sửa:** thứ tự + nội dung 8 bước entrypoint (§5.2) khớp
`docker/entrypoint.sh` từng bước; biến admin là `ADMIN_NAME/ADMIN_EMAIL/ADMIN_PASSWORD`
và cơ chế chỉ-tạo-khi-chưa-có khớp script; endpoint kiểm chứng
`GET /admin/api/login-options` tồn tại thật và trả `{"quick_login":…}` theo
`config/admin.php`; backup §10 dùng đúng `MYSQL_ROOT_PASSWORD` trong container `db`;
`.env.production` có thật trong `.gitignore` (dòng 5); `.dockerignore` loại `.env.*` +
`*.bundle` đúng như §3.3; domain `lacdoi.tsnam.name.vn` nhất quán toàn tài liệu;
`clear_env = no` + pool 20 worker khớp `docker/php-fpm.conf`; mục scheduler/queue và
chính sách nguồn hạn chế §11.3/§12 còn đúng trạng thái.

**Chưa kiểm chứng được trên VM (giới hạn giữ nguyên như §12.1 của tài liệu):** mọi
hành vi cần Docker Engine thật (build/up/healthcheck/chạy container, `docker run
hello-world`, Caddy/certbot xin chứng thư) — VM là sandbox lồng không chạy được
Engine; chủ dự án tự kiểm ở lần chạy đầu bằng checklist §7 + troubleshooting §13.

**QT-7 của đợt soát:** mục này trong task file + sửa tài liệu, commit pathspec riêng.
CLAUDE §0 **hoãn có ghi nhận**: tại thời điểm soát, cây đang có thay đổi chưa commit
của phiên song song (ROADMAP + file QD-15/món) — theo đúng tiền lệ hoãn của
DOCKER-001/QD-15, bổ sung ở cửa sổ sạch kế tiếp.
