# DOCKER-001 — Đóng gói Docker chuẩn bị go-live

**Trạng thái:** ✅ ĐÓNG 2026-11-02 — gói đã tự kiểm thử đạt toàn bộ mục (nhật ký §4).
**Phạm vi:** chỉ đóng gói + kiểm thử. **Chưa deploy** — tài liệu go-live production đầy đủ là đợt riêng sau khi gói đạt (chủ dự án tự golive theo tài liệu đó).

## 1. Thiết kế

| Thành phần | Lựa chọn | Lý do |
|---|---|---|
| Kiểu phục vụ | **PHP-FPM + nginx riêng** (không dùng Apache mod_php) | Tách tiến trình tĩnh/động; nginx nhẹ khi phục vụ ảnh/asset; khớp vận hành production phổ biến |
| `Dockerfile` đa tầng | `assets` (Node 22: `npm ci` + `npm run build`) → `app` (PHP 8.3 FPM + composer `--no-dev`) → `web` (nginx + `public/` từ tầng app) → `test` (tầng phụ, vendor kèm dev để chạy Pest) | Image production không chứa node_modules/dev-deps; sửa code tận dụng cache lớp vendor |
| Extension PHP | `pdo_mysql, mbstring, intl, gd, exif, bcmath, zip, pcntl` + OPcache | Đối chiếu `composer.json` (PHP ^8.3) + bộ module của môi trường dev hiện tại |
| MySQL | `mysql:8.0`, `--collation-server=utf8mb4_vi_0900_ai_ci`, `--log-bin-trust-function-creators=1`, native password | Theo quyết [SETUP-004](../tasks/SETUP-004.md); init SQL tạo sẵn cả `shake_hungry` + `shake_hungry_test` (`docker/mysql-init.sql`) |
| Biến môi trường | `env_file` bơm thẳng vào tiến trình (FPM để `clear_env = no` để Laravel đọc được); `.env.docker` (dev) / `.env.production` (prod, **không commit** — đã có trong `.gitignore`) từ hai file mẫu ở gốc repo | Một cơ chế cấu hình duy nhất, không chép `.env` vào image |
| Volume | `db_data` (MySQL), `app_storage` (`storage/app`), `app_logs` (`storage/logs`) | Ảnh món nằm trong `data/media/` thuộc image (QT-3) nên không cần volume media |

## 2. Hành vi khởi động (entrypoint `docker/entrypoint.sh`)

Chờ DB (tối đa 120 giây) → nếu thiếu `.env` thì dựng từ `.env.docker.example` →
**APP_KEY**: production chưa khai APP_KEY thì **dừng ngay kèm thông báo** (mất khoá = hỏng phiên/dữ liệu mã hoá);
ngoài production thì tự sinh và **export lại vào môi trường tiến trình** (biến env rỗng từ env_file được
Laravel ưu tiên hơn file `.env` — không export là FPM không thấy khoá, lỗi đã gặp và đã sửa, xem §4 vòng 2) →
`migrate --force` → **seed từ `data/` CHỈ KHI bảng `dishes` trống** (khởi động lại không bao giờ seed đè) →
tạo tài khoản admin lần đầu nếu `ADMIN_EMAIL`/`ADMIN_PASSWORD` được khai (chỉ tạo khi chưa có, không đổi mật khẩu cũ) →
`storage:link` → production: `config:cache` + `route:cache` + `view:cache` → `php-fpm`.

Bảo mật: `ADMIN_QUICK_LOGIN` trong cả hai file env mẫu đều **`false`** kèm cảnh báo in đậm;
đã kiểm chứng tại runtime production: `GET /admin/api/login-options` trả `{"quick_login":false}`.

## 3. Cách chạy nhanh

```bash
# Dev/test:
cp .env.docker.example .env.docker
docker compose up -d --build          # http://localhost:8000

# Production (chi tiết đầy đủ ở tài liệu go-live — đợt riêng):
cp .env.production.example .env.production   # điền khoá/mật khẩu thật
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

Khác biệt bản prod (lớp `docker-compose.prod.yml` chồng lên): MySQL **không mở cổng ra ngoài**
(`ports: !override []` — cần Docker Compose ≥ 2.24), web mở cổng 80, `restart: unless-stopped`,
giới hạn log 10 MB × 3 file/container. Healthcheck: db = `mysqladmin ping`, app = kiểm tra cổng FPM 9000.

## 4. Nhật ký tự kiểm thử (2026-11-02)

**Điều kiện kiểm thử — đọc kỹ:** VM chạy phiên này là sandbox lồng nhau (nspawn): Docker daemon gốc
không tạo được container (thiếu CAP_NET_ADMIN cho iptables + runc lỗi session keyring; rootless bị chặn
ở `newuidmap`/setns). Kiểm thử thực tế chạy bằng **Podman 4.9 (rootful, driver vfs, cgroupfs)** —
cùng Dockerfile/compose semantics — với các container dùng chung mạng host và cổng dịch chuyển
(MySQL 3307, web 8080) để không đụng môi trường dev đang chạy song song. Các file đóng gói bàn giao
**không** chứa các thích ứng này; trên máy/VPS bình thường `docker compose` chạy nguyên trạng.
Bản kiểm thử dựng từ `git archive` của HEAD **`ae1a5d3`** (cây chính lúc đó đang có phiên song song sửa dữ liệu).

| # | Mục | Kết quả |
|---|---|---|
| 1 | Build sạch 3 target (`app`, `web`, `test`) | ✅ app 744 MB; web/test build thành công |
| 2 | Mọi container lên + sẵn sàng | ✅ MySQL ready (cả 2 DB đúng collation, `log_bin_trust_function_creators=1`); FPM ready; nginx phục vụ |
| 3 | Seed + đối chiếu số tại HEAD `ae1a5d3` | ✅ **654 món = 194 Mức 3 · 279 Mức 2 · 113 Mức 2 (có note) · 68 Mức 1** (khớp từng ô với đếm từ file trong bản archive); **434 nguyên liệu**; **82 quả**; 586 dòng `dish_nutrition` (= đúng số món ≥ Mức 2); admin được tạo theo `ADMIN_*` |
| 4 | HTTP smoke qua nginx | ✅ 200: `/`, `/thu-vien`, `/hoa-qua`, `/mon/xoi-man`, `/admin` (SPA + bundle `build/assets/main-*.js`), `/api/dishes`, `/api/dishes/{slug}` (có `tcm_traits` tính/vị/ngũ hành theo nguyên liệu), `/api/fruits`, `/api/ingredients`, `/media/dishes/xoi-xeo.webp` (200, `image/webp`); **POST `/api/shake` → 200** có món trả về (`rau-muong-luoc`) |
| 5 | `lacdoi:validate-data` trong container | ✅ **0 🔴** / 3.344 bản ghi (24 🟡 là cảnh báo đã biết) |
| 6 | Khởi động lại toàn stack | ✅ Dữ liệu nguyên vẹn (654 món, tài khoản giữ nguyên); log entrypoint: "Đã có 654 món — bỏ qua seed" — **không seed đè** (đã xác nhận 2 lần: tạo lại container app và tắt/bật cả stack) |
| 7 | Pest trong container (DB test riêng `shake_hungry_test`) | ✅ **167/167 passed** (693 assertions, 429 giây) — đúng số test tại HEAD đã archive |
| + | Diễn tập production | ✅ Boot với `APP_ENV=production`: config cache chạy, trang/API 200, `quick_login:false`; boot production thiếu APP_KEY **bị chặn** với thông báo tiếng Việt |

**Vòng lặp lỗi đã sửa:**
- *Vòng 2 (APP_KEY):* mọi trang 500 `MissingAppKeyException` dù entrypoint đã `key:generate` —
  nguyên nhân: env_file khai `APP_KEY=` rỗng, Laravel ưu tiên biến tiến trình rỗng hơn file `.env`.
  Sửa: entrypoint export khoá vừa sinh vào môi trường tiến trình (§2). Sau sửa: 200 toàn bộ.
- *Vòng Pest 1:* 167 test đều đạt nhưng 152 test kèm warning `file_get_contents(/var/www/html/.env)`
  (phpdotenv không thấy file `.env` trong image; trên máy dev không có warning này vì có `.env`).
  Sửa: tầng `test` tạo `.env` rỗng (biến thật truyền qua môi trường). Chạy lại: **167/167, 0 warning**.

**Giới hạn còn lại:**
- Chưa chạy bằng Docker Engine thật trên máy sạch (VM này không chạy được Docker — xem đầu §4);
  lần kiểm chứng đầu tiên bằng `docker compose` thật sẽ là khi chủ dự án chạy thử theo tài liệu go-live.
  Rủi ro còn lại thấp vì Dockerfile/compose theo chuẩn phổ thông và đã qua Podman với cùng ngữ nghĩa;
  cú pháp cả hai file compose đã được `docker compose config` xác thực (bản prod: db không còn cổng, web :80).
- Scheduler Laravel (`schedule:run`) và queue worker chưa đóng gói thành service riêng — ghi vào tài liệu go-live.
- Ảnh `app` 744 MB (gồm `data/` 116 MB + vendor + PHP) — chấp nhận được; tối ưu kích thước không thuộc phạm vi đợt này.

## 5. File bàn giao

`Dockerfile`, `docker-compose.yml`, `docker-compose.prod.yml`, `.dockerignore`,
`.env.docker.example`, `.env.production.example`, `docker/` (`entrypoint.sh`, `nginx.conf`,
`php.ini`, `php-fpm.conf`, `mysql-init.sql`) + task file này + đính chính tại
[DEPLOYMENT.md](../../10-deployment/DEPLOYMENT.md) §6. Dòng ROADMAP + mục CLAUDE §0
**để bổ sung ở cửa sổ sạch** (hai file đang giữ thay đổi chưa commit của các phiên
QD song song — cùng cách xử lý như QD-15), bản thảo sẵn ở §6 dưới đây.

## 6. Bản thảo chờ bổ sung vào ROADMAP + CLAUDE §0

Dòng ROADMAP (bảng GĐ0, sau dòng DEV-SETUP-DOC):

> | [DOCKER-001](./tasks/DOCKER-001.md) | Đóng gói Docker chuẩn bị go-live (Dockerfile đa tầng FPM+nginx, compose dev/prod, entrypoint migrate + seed có điều kiện) + tự kiểm thử toàn bộ | Backend | ✅ | SETUP-004, DEV-SETUP-DOC | 2026-11-02 |

Mục CLAUDE §0 (Bổ sung lần tiếp theo):

> **DOCKER-001 ĐÃ ĐÓNG (2026-11-02):** dự án có gói Docker hoàn chỉnh ở gốc repo —
> `Dockerfile` đa tầng (Node build → PHP 8.3 FPM → nginx; chọn FPM+nginx thay Apache),
> `docker-compose.yml` (dev) + `docker-compose.prod.yml` (prod: MySQL không lộ cổng,
> restart, giới hạn log; cần Compose ≥ 2.24 cho `!override`), entrypoint tự chờ DB →
> migrate → **seed chỉ khi DB trống** → tạo admin theo `ADMIN_*` → cache production.
> `ADMIN_QUICK_LOGIN=false` trong cả hai file env mẫu kèm cảnh báo; production thiếu
> APP_KEY bị chặn khởi động. Tự kiểm thử trên bản archive HEAD `ae1a5d3`: build đạt,
> seed khớp baseline **654 món (194/279/113/68) · 434 NL · 82 quả**, smoke HTTP 200
> toàn bộ + POST `/api/shake` 200, validate **0 🔴**, restart không seed đè,
> **Pest trong container 167/167**, diễn tập production đạt (`quick_login:false`).
> ⚠️ Điều kiện kiểm thử: VM là sandbox lồng nhau không chạy được Docker Engine nên
> kiểm bằng Podman (cùng ngữ nghĩa Dockerfile/compose, mạng host + cổng dịch chuyển —
> thích ứng chỉ ở harness, không nằm trong file bàn giao); lần chạy `docker compose`
> thật đầu tiên là khi chủ dự án thử theo tài liệu go-live (đợt riêng). Chi tiết:
> [DOCKER-001](docs/05-tasks/tasks/DOCKER-001.md).
