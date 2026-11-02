# GOLIVE-DOCKER — Go-live production Lắc Đói bằng Docker

**Cập nhật lần cuối:** 2026-11-02 — lập mới (task GOLIVE-DOC). Mọi lệnh, tên biến, cổng và
hành vi trong tài liệu này đã đối chiếu với file thật của repo tại baseline `5044696`:
`Dockerfile`, `docker-compose.yml`, `docker-compose.prod.yml`, `docker/entrypoint.sh`,
`docker/nginx.conf`, `docker/mysql-init.sql`, `.env.production.example`,
`.env.docker.example`, và nhật ký kiểm thử [DOCKER-001](../05-tasks/tasks/DOCKER-001.md).
**Soát lại 2026-11-02 (REVIEW-GOLIVE-DOC, HEAD `a3bff4f`):** đối chiếu lại toàn bộ —
chạy thật `docker compose config` cho cả 3 tổ hợp (dev · prod overlay · prod + file
override §6.2) đều render đúng như mô tả; sửa lệnh mẫu §1.2 (thiếu `--env-file` — chính
bẫy §4.3), cập nhật số nghiệm thu §7.2 theo census cuối đợt xử lý tồn đọng, bổ sung 3
trang pháp lý vào smoke test §7.4, làm rõ lệnh nhận bundle §3.1. Chi tiết ở task file
[GOLIVE-DOC](../05-tasks/tasks/GOLIVE-DOC.md).
**Bản gốc:** file này trong repo là bản gốc. Bản sao bàn giao cho chủ dự án (thư mục goal
`files/`) có nội dung giống hệt.
**Đối tượng đọc:** chủ dự án tự go-live trên VPS của mình, không cần hỏi lại. Tài liệu này
**không** tự deploy — mọi lệnh do người vận hành chạy trên máy chủ của mình.

> ⚠️ **Đọc trước — giới hạn trung thực của gói (từ DOCKER-001 §4):** VM của dự án là
> sandbox lồng nhau, **không chạy được Docker Engine thật**. Gói Docker đã được kiểm thử
> đạt toàn bộ mục bằng **Podman 4.9** (cùng ngữ nghĩa Dockerfile/Compose; thích ứng mạng
> host + cổng dịch chuyển chỉ nằm ở harness kiểm thử, không nằm trong file bàn giao) và
> cả hai file compose đã qua `docker compose config`. **Lần chạy `docker compose` thật
> đầu tiên là trên máy chủ của bạn** — hãy dành thêm thời gian cho lần chạy đầu và dùng
> §13 Troubleshooting khi gặp lỗi; các lỗi có khả năng gặp nhất đã được liệt kê kèm cách xử lý.

> ⚠️ **Một mục phải chốt trước khi mở công khai** (không phải lỗi kỹ thuật): chính sách
> hiển thị công khai đối với dữ liệu nguồn hạn chế — xem §11.3. Về kỹ thuật hệ thống chạy
> được ngay; về chính sách, đừng mở link công khai cho tới khi mục đó được chốt.

---

## 0. Lộ trình nhanh (toàn cảnh — chi tiết ở các mục sau)

1. Chuẩn bị VPS + cài Docker Engine/Compose ≥ 2.24 (§2).
2. Đưa mã nguồn lên server bằng git bundle hoặc clone (§3).
3. Tạo `.env.production` từ file mẫu, sinh `APP_KEY`, đặt mật khẩu mạnh (§4).
4. `docker compose ... up -d --build` — entrypoint tự migrate + seed lần đầu (§5).
5. Trỏ DNS `lacdoi.tsnam.name.vn` → VPS, dựng HTTPS bằng Caddy (§6).
6. Chạy checklist nghiệm thu (§7) — đạt hết mới coi như go-live xong.
7. Nắm vận hành: log/restart (§8), cập nhật & rollback (§9), backup (§10), bảo mật (§11).

Từ đây về sau, mọi lệnh compose trong tài liệu đều dùng biến tắt sau (định nghĩa một lần
ở §5.1):

```bash
DC="docker compose --env-file .env.production -f docker-compose.yml -f docker-compose.prod.yml"
```

Cờ `--env-file .env.production` là **bắt buộc** — lý do ở §4.3, bỏ qua là DB khởi tạo sai
mật khẩu và app không kết nối được.

---

## 1. Kiến trúc production

### 1.1. Sơ đồ service

```
Internet ──HTTPS──> Caddy (host, :443/:80) ──HTTP──> [web] nginx :80
                                                          │ FastCGI :9000
                                                          v
                                                     [app] PHP 8.3-FPM
                                                          │ MySQL :3306 (mạng nội bộ Docker)
                                                          v
                                                     [db] MySQL 8.0
```

| Service | Image / build | Vai trò | Cổng |
|---|---|---|---|
| `web` | Build từ `Dockerfile` target `web` (nginx 1.27-alpine + `public/` đã build) | Phục vụ file tĩnh (asset Vite, ảnh trong `public/`), chuyển request PHP sang `app:9000` (`docker/nginx.conf`) | Host **80** (bản prod). Khi dùng Caddy/nginx trên host: đổi thành `127.0.0.1:8080` theo §6.2 |
| `app` | Build từ `Dockerfile` target `app` (PHP 8.3-FPM + vendor `--no-dev` + mã nguồn + `data/`) | Chạy Laravel; entrypoint lo migrate/seed/cache (§5.2). FPM để `clear_env = no` nên đọc được biến từ `env_file` | Chỉ nội bộ Docker: 9000 (không publish) |
| `db` | `mysql:8.0` | CSDL `shake_hungry` (+ `shake_hungry_test` tạo sẵn bởi `docker/mysql-init.sql`) | **Không publish ở production** (xem §1.2) |

`Dockerfile` đa tầng: `assets` (Node 22: `npm ci` + `npm run build`) → `app` (PHP) →
`web` (nginx) → `test` (chỉ để chạy Pest trong container, không dùng ở production).
Extension PHP trong image: `pdo_mysql, mbstring, intl, gd, exif, bcmath, zip, pcntl` + OPcache.

### 1.2. Khác biệt bản production (`docker-compose.prod.yml` chồng lên bản gốc)

Chạy production **luôn** là lệnh hai file (file prod là lớp chồng, không đứng một mình):

```bash
docker compose --env-file .env.production -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

(Lệnh đầy đủ luôn mang `--env-file .env.production` — thiếu nó là dính bẫy nội suy
mật khẩu DB ở §4.3. Từ §5.1 tài liệu dùng biến tắt `$DC` cho đúng lệnh này.)

| Khác biệt | Giá trị ở prod | Ý nghĩa |
|---|---|---|
| Cổng `db` | `ports: !override []` | MySQL **không lộ cổng ra host/Internet** — chỉ `app` trong mạng Docker chạm được |
| `env_file` của `app` | `!override` → `.env.production` | Thay `.env.docker` của bản dev |
| `web` | `ports: !override` → `"80:80"` | Bản dev là 8000; prod nghe 80 |
| `restart` | `unless-stopped` cả 3 service | Tự lên lại khi VPS reboot |
| Log | `json-file`, `max-size 10m` × `max-file 3` mỗi container | Chống đầy đĩa vì log |

> **Yêu cầu Docker Compose ≥ 2.24** — cú pháp `!override` chỉ có từ bản này. Kiểm bằng
> `docker compose version`. Compose cũ hơn sẽ báo lỗi parse khi đọc file prod (xử lý ở
> §13). Bản dev (`docker-compose.yml` một mình) mở cổng MySQL 3306 ra host cho tiện
> debug — **tuyệt đối không dùng một mình cho production**.

### 1.3. Volume và dữ liệu

| Volume | Gắn vào | Nội dung | Mất volume này thì sao |
|---|---|---|---|
| `db_data` | `db:/var/lib/mysql` | Toàn bộ CSDL | Mất dữ liệu người dùng production (tài khoản, nhật ký, ticket…). Dữ liệu biên soạn (món/nguyên liệu/quả) dựng lại được từ `data/` (§10.3) |
| `app_storage` | `app:/var/www/html/storage/app` | File người dùng tải lên qua app | Mất file tải lên |
| `app_logs` | `app:/var/www/html/storage/logs` | Log Laravel | Không quan trọng |

Ảnh món (1.090 file, ~107 MB trong `data/media/`) nằm **trong image** cùng mã nguồn
(QT-3: `data/` là nguồn sự thật) — không cần volume media riêng. Tên volume thật trên
server có tiền tố tên thư mục dự án (mặc định `shake-hungry_db_data`…), xem bằng
`docker volume ls`.

Tham số MySQL đã cố định trong compose theo quyết định SETUP-004: collation
`utf8mb4_vi_0900_ai_ci`, `--log-bin-trust-function-creators=1`, xác thực
`mysql_native_password`. Script `docker/mysql-init.sql` chạy **một lần khi volume
`db_data` còn trống**: tạo sẵn cả hai database đúng collation và cấp quyền cho user app.

---

## 2. Chuẩn bị máy chủ

### 2.1. Cấu hình VPS tối thiểu

| Hạng mục | Tối thiểu | Khuyến nghị | Căn cứ |
|---|---|---|---|
| CPU | 2 vCPU | 2–4 vCPU | FPM pool tối đa 20 worker (`docker/php-fpm.conf`) |
| RAM | **4 GB** | 4–8 GB | Bước `docker build` (Node build frontend + composer) là lúc ngốn RAM nhất; MySQL 8 + FPM khi chạy thường trú ~1–1,5 GB. Máy 2 GB rất dễ OOM lúc build — nếu chỉ có 2 GB thì thêm swap 2 GB hoặc build image ở máy khác rồi `docker save`/`docker load` chuyển lên |
| Đĩa | 40 GB SSD | 60–80 GB SSD nếu giữ backup tại chỗ | Image `app` ~744 MB (đã đo ở DOCKER-001, gồm `data/` 116 MB) + `web` ~50 MB + `mysql:8.0` ~800 MB + build cache vài GB; CSDL hiện tại nhỏ (kho biên soạn 654 món) nhưng cần dư địa cho log, backup, image cũ khi cập nhật |
| OS | Ubuntu 22.04/24.04 LTS hoặc Debian 12 | Ubuntu 24.04 LTS | Lệnh cài Docker ở §2.2 viết cho hai họ này |

### 2.2. Cài Docker Engine + Compose plugin (lệnh chính thức)

```bash
# Cập nhật hệ thống + gói phụ trợ
sudo apt-get update && sudo apt-get upgrade -y
sudo apt-get install -y ca-certificates curl

# Khoá GPG + kho apt chính thức của Docker (Ubuntu — Debian thay "ubuntu" bằng "debian")
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Cài Engine + Compose plugin
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Kiểm tra — Compose PHẢI ≥ 2.24 (cú pháp !override, §1.2)
docker compose version
sudo docker run --rm hello-world

# Cho user hiện tại quyền chạy docker không cần sudo (đăng nhập lại để có hiệu lực)
sudo usermod -aG docker "$USER"
```

### 2.3. User, SSH, firewall

- Tạo user vận hành riêng (vd `deploy`) nếu VPS đang chỉ có root; mọi lệnh trong tài
  liệu này chạy bằng user đó (đã thuộc nhóm `docker`).
- SSH: đăng nhập bằng khoá; sau khi xác nhận khoá hoạt động, tắt đăng nhập bằng mật
  khẩu (`PasswordAuthentication no` trong `/etc/ssh/sshd_config`, rồi
  `sudo systemctl restart ssh`).
- Firewall — chỉ mở **22 (SSH), 80 (HTTP), 443 (HTTPS)**:

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

> Lưu ý về Docker + ufw: Docker quản lý iptables riêng cho các cổng publish, có thể đi
> vòng qua ufw. Với gói này điều đó an toàn vì bản prod **chỉ publish cổng web** (80,
> hoặc 127.0.0.1:8080 theo §6.2); MySQL không publish nên không tới được từ Internet
> trong mọi trường hợp. Kiểm chứng độc lập ở §11.2.

Đặt thêm swap nếu VPS ít RAM (tuỳ chọn):

```bash
sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile
sudo mkswap /swapfile && sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

---

## 3. Đưa mã nguồn lên server

Mã nguồn nên nằm ở một thư mục cố định của user vận hành, vd `~/shake-hungry` (tên thư
mục này cũng là tiền tố tên volume Docker). Có hai đường:

### 3.1. Đường git bundle (đường chuẩn của dự án — khuyến nghị)

Dự án phát hành code bằng **một bundle gộp duy nhất** từ head đã push gần nhất (VM phát
triển bị chặn SSH tới GitHub; chủ dự án là người chuyển bundle). Trên máy dev, bundle
được tạo kèm ref nhánh rõ ràng; kiểm tra ref trong bundle trước khi nhận:

```bash
# Trên server, sau khi đã nhận file bundle (scp từ máy cá nhân, hoặc tải về thư mục home):
git bundle list-heads ~/shake-hungry-dev-update-N.bundle     # thấy refs/heads/dev
git bundle verify ~/shake-hungry-dev-update-N.bundle        # phải báo "The bundle is okay"
                                                             # và nêu base mà nó yêu cầu
```

Lần đầu (chưa có thư mục dự án):

```bash
git clone ~/shake-hungry-dev-update-N.bundle ~/shake-hungry
cd ~/shake-hungry
git checkout dev        # nếu bundle mang ref dev; nếu clone đã ở đúng head thì bỏ qua
git log --oneline -3    # đối chiếu head với thông báo bàn giao bundle
```

Cập nhật ở các lần sau (đã có thư mục + bundle mới). Các bundle gần đây (từ bundle #6)
được tạo **kèm ref `refs/heads/dev` rõ ràng** — lệnh nhận chuẩn là `fetch` theo tên nhánh:

```bash
cd ~/shake-hungry
git fetch ~/shake-hungry-dev-update-M.bundle dev
git merge --ff-only FETCH_HEAD
git log --oneline -3
```

(Ngoại lệ lịch sử — bài học bundle #4: bundle cũ tạo bằng dải commit chỉ chứa ref
`HEAD` thì `fetch … dev` báo "couldn't find remote ref dev"; với bundle như vậy dùng
`git fetch <file> HEAD` rồi vẫn `merge --ff-only FETCH_HEAD`. Kiểm bằng
`git bundle list-heads <file>` trước khi nhận là biết ngay bundle thuộc loại nào.)

> Nếu `merge --ff-only` thất bại (lịch sử lệch nhau): **dừng lại, không force**, báo lại
> phiên phát triển. Server production không bao giờ tự sửa lịch sử git.

Ưu điểm của đường bundle: server không cần credential GitHub nào.

### 3.2. Đường clone trực tiếp (nếu server ra được Internet và có quyền đọc repo)

```bash
git clone -b dev https://github.com/tsnam1110/shake-hungry.git ~/shake-hungry
```

Repo là repo riêng — clone HTTPS cần token có quyền đọc, clone SSH cần deploy key.
Token/key đặt trên server là một bề mặt rủi ro thêm; chỉ dùng đường này khi có lý do,
và thu hồi credential sau khi xong nếu không cần cập nhật thường xuyên.

### 3.3. Sau khi có mã nguồn

```bash
cd ~/shake-hungry
ls Dockerfile docker-compose.yml docker-compose.prod.yml .env.production.example
git status --short        # kỳ vọng: sạch (server không sửa code trực tiếp)
```

File `*.bundle` đã nằm trong `.dockerignore` và `.env.*` (trừ hai file `.example`)
cũng bị loại khỏi image — bundle và env thật đặt cùng thư mục dự án vẫn an toàn.

---

## 4. Cấu hình `.env.production`

### 4.1. Tạo file và điền giá trị

```bash
cd ~/shake-hungry
cp .env.production.example .env.production
chmod 600 .env.production      # file chứa mật khẩu — chỉ chủ sở hữu đọc được
nano .env.production
```

`.env.production` đã có trong `.gitignore` — **không commit file này, không gửi qua
kênh chat/email**. Các biến phải điền (đối chiếu `.env.production.example`):

| Biến | Giá trị production | Ghi chú |
|---|---|---|
| `APP_ENV` | `production` | Đã đúng sẵn trong file mẫu; entrypoint dựa vào đây để bắt buộc có APP_KEY và bật cache |
| `APP_KEY` | Khoá thật sinh theo §4.2 | Để nguyên dòng chữ placeholder `DIEN-KHOA-THAT-VAO-DAY` là app **không boot được đúng** — phải thay |
| `APP_DEBUG` | `false` | Bật debug ở production lộ stack trace + biến môi trường cho người lạ |
| `APP_URL` | `https://lacdoi.tsnam.name.vn` | Đúng domain thật, có `https://` — sai giá trị này là link/redirect/asset sinh sai |
| `DB_DATABASE` | `shake_hungry` | Giữ nguyên — khớp compose + `mysql-init.sql` |
| `DB_USERNAME` | `lakdoi` | Giữ nguyên như file mẫu (khớp giá trị mặc định phía compose) |
| `DB_PASSWORD` | Mật khẩu mạnh tự đặt | Xem §4.3 — giá trị này phải khớp cả hai nơi đọc |
| `DB_ROOT_PASSWORD` | Mật khẩu mạnh khác, tự đặt | Chỉ dùng cho quản trị MySQL + backup (§10) |
| `ADMIN_NAME` / `ADMIN_EMAIL` | Email quản trị thật của bạn | Entrypoint tạo tài khoản admin ở lần khởi động đầu (§5.2) |
| `ADMIN_PASSWORD` | Mật khẩu mạnh tự đặt | Chỉ dùng để **tạo** tài khoản lần đầu; đổi lại sau lần đăng nhập đầu (§5.4) |
| `ADMIN_QUICK_LOGIN` | **`false`** | Xem cảnh báo §4.4 — bắt buộc |
| `SESSION_SECURE_COOKIE` | `true` | Đã đúng sẵn trong file mẫu (cookie chỉ qua HTTPS) |
| `LOG_LEVEL` | `warning` | Đã đúng sẵn trong file mẫu |

Sinh mật khẩu mạnh ngay trên server, không cần dịch vụ ngoài:

```bash
openssl rand -base64 24     # chạy 3 lần cho DB_PASSWORD, DB_ROOT_PASSWORD, ADMIN_PASSWORD
```

### 4.2. Sinh `APP_KEY`

APP_KEY mã hoá session/cookie/dữ liệu mã hoá của Laravel — mất hoặc đổi khoá là toàn
bộ phiên đăng nhập và dữ liệu đã mã hoá hỏng. Production **bắt buộc khai sẵn**: thiếu
khoá, entrypoint dừng ngay kèm thông báo (hành vi đã kiểm chứng ở DOCKER-001). Sinh
khoá không cần chạy app:

```bash
php -r 'echo "base64:" . base64_encode(random_bytes(32)) . PHP_EOL;'
# Nếu server chưa có PHP ngoài Docker:
docker run --rm php:8.3-cli php -r 'echo "base64:" . base64_encode(random_bytes(32)) . PHP_EOL;'
```

Dán kết quả vào dòng `APP_KEY=` trong `.env.production`. **Lưu một bản sao APP_KEY vào
trình quản lý mật khẩu của bạn** cùng hai mật khẩu DB — khôi phục thảm hoạ (§10) cần
đúng khoá này để dữ liệu mã hoá còn đọc được.

### 4.3. ⚠️ Bẫy nội suy biến của Compose — vì sao mọi lệnh đều có `--env-file`

Đây là điểm dễ vấp nhất ở lần chạy đầu, đọc kỹ:

- Service `app` đọc cấu hình qua `env_file` — bản prod là `.env.production` (§1.2). ✅
- Nhưng service `db` trong `docker-compose.yml` lấy mật khẩu qua **biến nội suy** của
  Compose: `MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-...}`,
  `MYSQL_PASSWORD: ${DB_PASSWORD:-...}`, `MYSQL_USER: ${DB_USERNAME:-lakdoi}`.
  Compose nội suy các biến này từ **môi trường shell + file `.env` mặc định ở thư mục
  dự án** — **không** tự đọc `.env.production`.

Hệ quả nếu chạy lệnh compose trơn (không `--env-file`): MySQL khởi tạo với mật khẩu
*mặc định trong compose* trong khi `app` dùng mật khẩu mạnh từ `.env.production` →
app chờ DB 120 giây rồi thoát, vòng lặp khởi động lại. Cách tránh — một trong hai:

1. **(Dùng trong toàn bộ tài liệu này)** thêm `--env-file .env.production` vào mọi lệnh
   compose: `docker compose --env-file .env.production -f docker-compose.yml -f docker-compose.prod.yml ...`
   Khi đó nội suy lấy đúng giá trị trong `.env.production`.
2. Hoặc xuất biến ra shell trước khi chạy: `set -a; . ./.env.production; set +a`.

Thêm hai lưu ý sống còn:

- Mật khẩu MySQL chỉ được áp **lần khởi tạo đầu tiên của volume `db_data`**. Đổi
  `DB_PASSWORD` trong `.env.production` về sau **không** tự đổi mật khẩu trong MySQL —
  phải `ALTER USER` trong MySQL (hoặc xoá volume và seed lại, chỉ làm trước go-live).
- Đừng tạo thêm file `.env` ở thư mục dự án với giá trị khác — Compose sẽ ưu tiên nó
  cho nội suy và gây lệch khó hiểu.

### 4.4. ⚠️⚠️ `ADMIN_QUICK_LOGIN` — bắt buộc `false` ở production

Khi biến này là `true`, trang đăng nhập admin hiện nút **vào thẳng tài khoản admin
không cần mật khẩu** — bất kỳ ai mở được trang đăng nhập cũng thành quản trị viên cao
nhất. Tính năng này chỉ dành cho dev cục bộ sau cờ môi trường. Ở production:

- Giữ `ADMIN_QUICK_LOGIN=false` trong `.env.production` (file mẫu đã để sẵn `false`
  kèm cảnh báo).
- Sau khi deploy, **kiểm chứng tại runtime** chứ không chỉ nhìn file: gọi
  `GET /admin/api/login-options` phải trả `{"quick_login":false}` (checklist §7).
- Mỗi lần nút đăng nhập nhanh được dùng đều ghi log cảnh báo — thấy log này ở
  production là sự cố bảo mật, xử lý ngay.

---

## 5. Khởi chạy lần đầu

### 5.1. Lệnh chạy

```bash
cd ~/shake-hungry
DC="docker compose --env-file .env.production -f docker-compose.yml -f docker-compose.prod.yml"
$DC config --quiet        # kiểm tra cú pháp compose gộp (bắt lỗi !override/Compose cũ sớm)
$DC up -d --build
$DC ps
$DC logs -f app           # theo dõi entrypoint; Ctrl+C để thoát xem log (không dừng container)
```

Lần build đầu tải image nền (node, php, nginx, mysql) và chạy `npm build` + composer —
mất vài phút tuỳ mạng/VPS, các lần sau nhanh hơn nhờ cache lớp.

### 5.2. Entrypoint làm gì (đối chiếu `docker/entrypoint.sh`)

Container `app` mỗi lần khởi động chạy đúng thứ tự này:

1. Thiếu `.env` trong container thì dựng từ `.env.docker.example` (file này chỉ là nơi
   `key:generate` ghi khi cần — cấu hình thật đến từ `env_file`, FPM `clear_env = no`).
2. **APP_KEY:** production mà `APP_KEY` trống → **dừng ngay** kèm thông báo tiếng Việt.
   Ngoài production mới tự sinh (và export lại vào môi trường tiến trình — lỗi vòng 2
   của DOCKER-001: biến env rỗng từ env_file được Laravel ưu tiên hơn file `.env`).
3. Chờ MySQL tối đa **120 giây**.
4. `php artisan migrate --force` — chạy mọi migration chưa áp.
5. **Seed từ `data/` CHỈ KHI bảng `dishes` đang trống.** DB đã có món → in
   `Đã có N món — bỏ qua seed.` và không đụng dữ liệu. Khởi động lại **không bao giờ**
   seed đè (đã xác nhận 2 lần trong kiểm thử DOCKER-001).
6. **Tính dinh dưỡng ngay sau seed lần đầu:** `lacdoi:recompute-calories
   --with-ingredients`. Seed chỉ tính sẵn cho món ≥ Mức 2; bước này nốt phần còn lại
   để độ phủ dinh dưỡng đầy đủ ngay từ lần boot đầu (thêm ở vòng kiểm chứng lại
   2026-11-02 — §13.2). Khởi động lại không chạy lại bước này.
7. `ADMIN_EMAIL`/`ADMIN_PASSWORD` có khai → tạo tài khoản admin **nếu email đó chưa
   tồn tại**; đã tồn tại thì giữ nguyên, không đổi mật khẩu.
8. `storage:link`; production thì thêm `config:cache` + `route:cache` + `view:cache`.
9. `exec php-fpm`.

Kỳ vọng log lần đầu: `Cơ sở dữ liệu trống — đang seed từ data/…` → (seed toàn bộ kho
biên soạn + các bảng tham chiếu, mất một lúc — đừng ngắt giữa chừng) → `Đã tính N
món…` (recompute) → `Đã tạo tài khoản quản trị: <email của bạn>`. Healthcheck: `db` khoẻ sau ~20 giây, `app` sau tối đa ~60 giây
(`start_period` đã chừa thời gian cho seed lần đầu ở mức cơ bản; nếu `$DC ps` vẫn hiện
`app` ở trạng thái starting trong vài phút đầu kèm log seed đang chạy là bình thường).

### 5.3. Mở thử nội bộ trước khi có domain

```bash
curl -s -o /dev/null -w "%{http_code}\n" http://localhost/          # kỳ vọng 200
curl -s http://localhost/admin/api/login-options                    # kỳ vọng {"quick_login":false}
```

### 5.4. Việc phải làm ngay sau lần đăng nhập admin đầu tiên

1. Đăng nhập `/admin` bằng `ADMIN_EMAIL`/`ADMIN_PASSWORD` đã khai.
2. **Đổi mật khẩu admin** trong trang quản trị. Nếu giao diện chưa có chỗ đổi, dùng
   tinker qua container:

   ```bash
   $DC exec app php artisan tinker --execute='\App\Models\User::where("email","EMAIL-ADMIN-CUA-BAN")->first()->update(["password"=>bcrypt("MAT-KHAU-MOI-MANH")]);'
   ```

3. Sau khi đã đổi, **xoá dòng `ADMIN_PASSWORD=` khỏi `.env.production`** (hoặc để trống):
   entrypoint chỉ tạo tài khoản khi chưa tồn tại nên việc này không ảnh hưởng vận
   hành, và giảm bề mặt lộ mật khẩu. Giữ lại `ADMIN_EMAIL` cũng được.
4. Vì `.env.production` vừa đổi, nạp lại cho `app`: `$DC up -d --force-recreate app`.

---

## 6. HTTPS + domain `lacdoi.tsnam.name.vn`

### 6.1. DNS

Tại nhà quản lý DNS của `tsnam.name.vn`, thêm bản ghi:

| Trường | Giá trị |
|---|---|
| Type | `A` |
| Name/Host | `lacdoi` |
| Value | Địa chỉ IPv4 của VPS |
| TTL | 300 (lúc mới trỏ, sau ổn định nâng 3600) |

Kiểm tra đã lan truyền: `dig +short lacdoi.tsnam.name.vn` phải trả đúng IP VPS.
**Trỏ DNS và xác nhận xong mới làm HTTPS** — Let's Encrypt kiểm tra qua chính domain này.

### 6.2. Phương án A — Caddy reverse proxy (khuyến nghị)

Caddy tự xin + gia hạn chứng thư Let's Encrypt, cấu hình ngắn nhất, và để cổng 80/443
ở tầng host (nơi firewall quản lý được rõ ràng). Vì Caddy chiếm 80/443 trên host,
container `web` phải lùi vào `127.0.0.1:8080` bằng **một file override đặt tại server**
(không sửa file compose trong repo):

```bash
cat > ~/shake-hungry/docker-compose.host.yml <<'EOF'
# File CHỈ đặt tại server production (không commit): nhường 80/443 cho Caddy trên host.
services:
  web:
    ports: !override
      - "127.0.0.1:8080:80"
EOF

# Từ nay lệnh compose có thêm file này ở CUỐI:
DC="docker compose --env-file .env.production -f docker-compose.yml -f docker-compose.prod.yml -f docker-compose.host.yml"
$DC up -d
```

Cài Caddy (kho chính thức):

```bash
sudo apt-get install -y debian-keyring debian-archive-keyring apt-transport-https curl
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt-get update && sudo apt-get install -y caddy
```

File `/etc/caddy/Caddyfile` chỉ cần:

```caddyfile
lacdoi.tsnam.name.vn {
	reverse_proxy 127.0.0.1:8080
	encode gzip
}
```

```bash
sudo systemctl reload caddy
curl -s -o /dev/null -w "%{http_code}\n" https://lacdoi.tsnam.name.vn/    # kỳ vọng 200
```

Caddy tự chuyển HTTP → HTTPS và tự gia hạn chứng thư. Lưu ý: `APP_URL` trong
`.env.production` phải là `https://lacdoi.tsnam.name.vn` (§4.1); nếu vừa sửa env,
nạp lại app: `$DC up -d --force-recreate app` (entrypoint chạy lại `config:cache`).

### 6.3. Phương án B — nginx trên host + certbot (thay thế)

Dùng khi đã quen nginx hoặc host đã có nginx. Vẫn cần file override §6.2 để `web`
nghe `127.0.0.1:8080`.

```bash
sudo apt-get install -y nginx certbot python3-certbot-nginx
```

`/etc/nginx/sites-available/lacdoi`:

```nginx
server {
    listen 80;
    server_name lacdoi.tsnam.name.vn;

    client_max_body_size 20m;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/lacdoi /etc/nginx/sites-enabled/lacdoi
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d lacdoi.tsnam.name.vn    # xin chứng thư + tự thêm cấu hình 443 + redirect
```

Certbot cài sẵn lịch gia hạn (`systemctl list-timers | grep certbot`).

### 6.4. Không dùng reverse proxy trên host thì sao?

Bản prod gốc cho `web` nghe thẳng cổng 80 (HTTP). **Không để production chạy HTTP
trần**: không có HTTPS thì cookie phiên không an toàn và trình duyệt cảnh báo. Nếu
tạm thời phải chạy trước khi có domain, chỉ mở cho chính bạn kiểm thử qua SSH tunnel
(`ssh -L 8080:127.0.0.1:80 <vps>`), không công khai link.

---

## 7. Nghiệm thu sau deploy (checklist chạy lệnh thật)

Chạy toàn bộ từ server (trừ mục ghi rõ chạy từ máy ngoài). **Đạt hết mới coi là go-live.**

### 7.1. Trạng thái stack

```bash
$DC ps        # db: healthy · app: healthy · web: Up
$DC logs --tail=50 app    # không còn vòng lặp lỗi; thấy dòng "Đã có N món — bỏ qua seed"
                          # (lần đầu: "Đã tạo tài khoản quản trị" + seed hoàn tất)
```

### 7.2. Số lượng dữ liệu — tiêu chí là **file ↔ DB khớp nhau trên đúng commit đã deploy**

Số sẽ trôi theo các đợt dữ liệu, nên nghiệm thu dùng tiêu chí khớp + lệnh đếm lại,
không dùng một con số tuyệt đối. Số tham chiếu mới nhất — **đo lại ở vòng kiểm
chứng Docker 2026-11-02 trên đúng stack production** (file + DB trong container):
**652 món = 215 Mức 3 · 245 Mức 2 thường · 119 Mức 2 (có note) · 73 Mức 1**;
**436 nguyên liệu** · **82 quả** · **38 bản ghi kết hợp**; dòng `dish_nutrition`
sau lần boot đầu = **649** (seed tính sẵn 579 món ≥ Mức 2, entrypoint recompute
nốt 70 món còn lại — §5.2 mục 6). Nếu bạn
deploy ở một commit khác, phân bố mức/nguyên liệu có thể đã dịch chuyển (QT-7 tại
CLAUDE §0 là nguồn mới nhất) — tổng số món và nguyên tắc file↔DB khớp trên đúng
commit đã deploy là phần bất biến để đối chiếu.

```bash
# Đếm từ file (nguồn sự thật QT-3) trên server:
ls data/dishes/vn/*.json | grep -v _index | wc -l        # món
ls data/ingredients/*.json | grep -v _index | wc -l      # nguyên liệu
ls data/fruits/*.json | wc -l                            # quả

# Đếm trong DB production:
$DC exec app php artisan tinker --execute='echo \App\Models\Dish::count(),"/",\App\Models\Ingredient::count(),"/",\App\Models\Fruit::count(),PHP_EOL;'

# Hai dãy số phải bằng nhau. Lệch = seed chưa xong hoặc sai commit — dừng, xem log app.
```

### 7.3. Kiểm định dữ liệu trong container

```bash
$DC exec app php artisan lacdoi:validate-data     # kỳ vọng: 0 🔴 (có thể còn 🟡 đã biết)
```

### 7.4. Smoke test các URL chính (qua domain HTTPS)

```bash
BASE=https://lacdoi.tsnam.name.vn
for p in / /thu-vien /hoa-qua /mon/xoi-man /admin /api/dishes /api/dishes/xoi-man /api/fruits /api/ingredients /media/dishes/xoi-xeo.webp /nguon-du-lieu /chinh-sach-bao-mat /mien-tru-y-te; do
  printf "%-28s %s\n" "$p" "$(curl -s -o /dev/null -w '%{http_code}' $BASE$p)"
done
# Kỳ vọng: tất cả 200.

# Lắc món (endpoint chính của sản phẩm):
curl -s -X POST $BASE/api/shake -H 'Content-Type: application/json' -d '{}' | head -c 300
# Kỳ vọng: JSON có món trả về, HTTP 200.

# Cờ đăng nhập nhanh PHẢI tắt:
curl -s $BASE/admin/api/login-options             # kỳ vọng đúng: {"quick_login":false}
```

Mở thêm bằng trình duyệt thật: trang chủ hiển thị đúng giao diện + ảnh món; bấm Lắc ra
mâm; trang món có khối "Tính vị:"; đăng nhập `/admin` bằng tài khoản đã đổi mật khẩu.

### 7.5. Kiểm tra bảo mật nhanh (chi tiết ở §11)

```bash
curl -s -o /dev/null -w "%{http_code}\n" http://lacdoi.tsnam.name.vn/   # phải redirect sang https (301/308) nếu dùng Caddy/certbot
# Từ một máy NGOÀI server — cổng MySQL phải không tới được:
nc -vz <IP-VPS> 3306        # kỳ vọng: failed/timeout
$DC port db 3306            # kỳ vọng: không in gì (db không publish cổng)
```

---

## 8. Vận hành hằng ngày

```bash
cd ~/shake-hungry   # và định nghĩa lại biến $DC như §5.1/§6.2 ở mỗi phiên shell mới

# Xem log (có sẵn giới hạn 10 MB × 3 file/container ở bản prod):
$DC logs -f app           # Laravel/entrypoint
$DC logs -f web           # nginx access/error
$DC logs -f db
$DC exec app tail -n 100 storage/logs/laravel.log

# Trạng thái + sức khoẻ:
$DC ps
docker stats --no-stream

# Khởi động lại:
$DC restart app                       # chỉ app
$DC down && $DC up -d                 # cả stack — dữ liệu trong volume giữ nguyên

# Vào container khi cần chẩn đoán:
$DC exec app bash
$DC exec app php artisan tinker
$DC exec db mysql -ulakdoi -p shake_hungry    # mật khẩu DB trong .env.production
```

> ⛔ **Không bao giờ chạy `$DC down -v` trên production** — cờ `-v` xoá volume, tức
> xoá luôn `db_data`. Cũng không chạy `php artisan migrate:fresh`, `db:seed` hay
> `lacdoi:export-data` trên production: dữ liệu biên soạn thuộc về `data/` ở máy phát
> triển (QT-3), production không phải nguồn sự thật và không sửa dữ liệu tại chỗ.

Nhật ký nên xem định kỳ: log cảnh báo `ADMIN QUICK-LOGIN` (không được xuất hiện —
§4.4), lỗi 500 tăng bất thường trong log `web`, và dung lượng đĩa (`df -h`,
`docker system df`).

---

## 9. Cập nhật phiên bản mới và rollback

### 9.1. Cập nhật (quy trình chuẩn)

```bash
# 0. BACKUP DB TRƯỚC — bắt buộc, xem §10.1. Không backup thì không cập nhật.

# 1. Nhận code mới (§3.1 — fetch bundle + merge --ff-only, hoặc git pull theo §3.2):
cd ~/shake-hungry
git fetch ~/shake-hungry-dev-update-M.bundle HEAD
git merge --ff-only FETCH_HEAD
git log --oneline -3        # ghi lại head mới + head cũ (cần cho rollback §9.2)

# 2. Build lại + lên bản mới:
$DC up -d --build
$DC logs -f app             # entrypoint tự chạy migration mới; seed bị bỏ qua vì DB đã có món
```

Những gì bản cập nhật **không** đụng tới: dữ liệu trong `db_data` (tài khoản người
dùng, hồ sơ, nhật ký, yêu thích, ticket, đóng góp) và volume `app_storage` — entrypoint
không seed đè, migration chỉ thay đổi cấu trúc theo hướng đã viết trong code. Ngược
lại, thay đổi dữ liệu biên soạn (món/nguyên liệu mới trong `data/`) **không tự chảy
vào DB đang chạy** — muốn cập nhật dữ liệu biên soạn trên production là một quyết
định vận hành riêng (đồng bộ một chiều `data/` → DB), không làm bừa bằng
`migrate:fresh --seed` (lệnh đó xoá dữ liệu người dùng). Khi có đợt cập nhật dữ liệu
biên soạn cho production, phiên phát triển sẽ bàn giao kèm hướng dẫn riêng.

Sau cập nhật, chạy lại **§7.2–§7.4** (đếm file↔DB theo commit mới, validate, smoke test).

### 9.2. Rollback

```bash
# 1. Quay code về head cũ đã ghi ở §9.1:
git checkout <head-cu>
$DC up -d --build

# 2. Nếu bản mới đã chạy migration đổi cấu trúc mà code cũ không tương thích:
#    khôi phục cả DB từ bản backup trước cập nhật (§10.2), rồi mới cho app lên.
```

Nguyên tắc: **code và DB rollback cùng nhau** khi có migration. Chỉ quay code mà để
DB mới là tổ hợp chưa từng được kiểm thử — đừng làm. Sau rollback, chạy lại §7.4.

Giữ lại ít nhất 2–3 image cũ để rollback nhanh: đừng `docker image prune -a` ngay sau
khi cập nhật; để qua một ngày vận hành ổn định.

---

## 10. Backup và khôi phục

### 10.1. Backup CSDL (việc quan trọng nhất)

Container `db` đã có sẵn biến `MYSQL_ROOT_PASSWORD` trong môi trường của nó — dump
ngay trong container, không cần lộ mật khẩu ra lệnh shell:

```bash
mkdir -p ~/backups
$DC exec -T db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --triggers shake_hungry' \
  | gzip > ~/backups/shake_hungry-$(date +%F-%H%M).sql.gz
ls -lh ~/backups/
```

Lịch gợi ý: cron trên host mỗi ngày một bản, giữ 7 bản ngày + 4 bản tuần, và **chép
một bản ra khỏi VPS** (máy cá nhân / lưu trữ đối tượng) — backup nằm cùng đĩa với hệ
thống không chống được mất VPS:

```cron
30 3 * * * cd /home/deploy/shake-hungry && docker compose --env-file .env.production -f docker-compose.yml -f docker-compose.prod.yml -f docker-compose.host.yml exec -T db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --triggers shake_hungry' | gzip > /home/deploy/backups/shake_hungry-$(date +\%F).sql.gz
```

(Đường dẫn + danh sách file compose trong cron phải khớp cách bạn đang chạy thật —
nếu không dùng Caddy thì bỏ `-f docker-compose.host.yml`.)

### 10.2. Khôi phục CSDL từ backup

```bash
# DB đích sẽ bị GHI ĐÈ — chỉ làm khi đã chắc chắn, và dump thêm một bản hiện trạng trước.
gunzip -c ~/backups/shake_hungry-YYYY-MM-DD.sql.gz \
  | $DC exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" shake_hungry'
$DC restart app
```

**Diễn tập khôi phục ít nhất một lần trước go-live** (khôi phục vào một stack thử
hoặc một database tạm) — backup chưa từng khôi phục thử là backup chưa được kiểm chứng.

### 10.3. Backup volume + nguyên tắc nguồn sự thật

- `app_storage` (file người dùng tải lên): sao lưu theo cùng nhịp backup DB —

  ```bash
  docker run --rm -v shake-hungry_app_storage:/src:ro -v ~/backups:/dst alpine \
    tar czf /dst/app_storage-$(date +%F).tar.gz -C /src .
  ```

  (Tên volume có tiền tố theo thư mục dự án — kiểm bằng `docker volume ls`.)
- `app_logs`: không cần backup.
- **Nguyên tắc QT-3 là lưới an toàn cuối cùng:** toàn bộ dữ liệu *biên soạn* (654 món,
  nguyên liệu, quả, bảng tham chiếu) sống dạng file trong `data/` ở git — VPS cháy
  hoàn toàn vẫn dựng lại được một bản đầy đủ bằng: máy mới → §2 → §3 → §4 → §5 (seed
  tự chạy). Thứ **chỉ có ở production, mất là mất hẳn** là dữ liệu người dùng thật
  (tài khoản, hồ sơ/TDEE, nhật ký ăn, yêu thích, ticket góp ý, đóng góp cộng đồng) —
  đó là lý do §10.1 là bắt buộc chứ không phải tuỳ chọn. Kèm theo: bản sao `APP_KEY`
  (§4.2) phải nằm trong trình quản lý mật khẩu, nếu không bản backup DB khôi phục về
  cũng không đọc được dữ liệu đã mã hoá.

---

## 11. Checklist bảo mật trước khi công khai

### 11.1. Kỹ thuật — phải đạt hết

- [ ] `ADMIN_QUICK_LOGIN=false` trong `.env.production` **và** kiểm chứng runtime:
      `curl https://lacdoi.tsnam.name.vn/admin/api/login-options` → `{"quick_login":false}`
- [ ] `APP_DEBUG=false`, `APP_ENV=production` (trang lỗi không lộ stack trace/biến env)
- [ ] Mật khẩu admin **đã đổi** sau lần đăng nhập đầu (§5.4); dòng `ADMIN_PASSWORD`
      trong `.env.production` đã xoá/để trống sau khi tạo tài khoản
- [ ] `APP_KEY` là khoá thật (không phải placeholder), có bản sao trong trình quản lý
      mật khẩu; `.env.production` quyền `600`, không nằm trong git (`git status` sạch)
- [ ] MySQL không lộ cổng: `$DC port db 3306` không in gì + `nc` từ máy ngoài thất bại (§7.5)
- [ ] HTTPS hoạt động, HTTP tự redirect sang HTTPS, chứng thư còn hạn (Caddy/certbot tự gia hạn)
- [ ] Firewall chỉ mở 22/80/443 (§2.3); SSH chỉ đăng nhập bằng khoá; mật khẩu SSH đã tắt
- [ ] Hệ điều hành đã `apt upgrade`; cân nhắc bật `unattended-upgrades` cho bản vá bảo mật
- [ ] Image được build từ đúng commit đã nghiệm thu (§7.2 khớp file↔DB theo commit đó)
- [ ] Đã có ít nhất một bản backup DB (§10.1) **và đã diễn tập khôi phục** (§10.2)
- [ ] Không còn tài khoản mặc định nào ngoài admin của bạn (kiểm tra trang quản trị > người dùng)

### 11.2. Kiểm chứng cổng từ bên ngoài (làm từ máy cá nhân)

```bash
nmap -Pn -p 22,80,443,3306,8000,9000 <IP-VPS>
# Kỳ vọng: 22/80/443 open; 3306/8000/9000 filtered hoặc closed.
```

### 11.3. ⚠️ Chính sách hiển thị nguồn hạn chế — PHẢI CHỐT TRƯỚC KHI MỞ CÔNG KHAI

Hồ sơ quyết định 2026-11-01 (CLAUDE §0, đính chính lần 16, mục (6); LICENSE-COMPLIANCE
§5b): một số dòng nhãn tính vị TCM đến từ nguồn uy tín nhưng **điều khoản sử dụng hạn
chế** (điển hình: ấn phẩm của CMRO Hồng Kông) được dự án **lưu vào thư viện kèm cờ
`usage_restricted` — ghi rõ "không được dùng", ở chế độ **lưu trữ/lưu hành nội bộ**;
văn bản quyết định ghi nguyên văn: *"chính sách hiển thị công khai chốt trước go-live"*.

Tại thời điểm viết tài liệu này, chính sách hiển thị đó **chưa được chốt**, trong khi
khối "Tính vị:" trên trang công khai đang **hiển thị mặc định**. Vì vậy:

- [ ] **Chủ dự án quyết chính sách hiển thị** đối với các dòng nhãn mang cờ
      `usage_restricted` (nằm trong khối `tcm_traits` của file nguyên liệu trong
      `data/ingredients/`): hiển thị cả giá trị, chỉ hiển thị khi đã lọc các dòng hạn
      chế, hay ẩn lớp tính vị cho tới khi có quyết định khác.
- [ ] Quyết định được ghi thành hồ sơ trong repo (task file + CLAUDE §0 theo QT-7)
      **và** triển khai/kiểm chứng xong, rồi mới công khai link production.
- Tài liệu go-live này **không tự quyết** thay mục trên — đây là điểm dừng có chủ đích,
  không phải thiếu sót kỹ thuật: hệ thống chạy được bình thường trong lúc chờ chốt
  (chưa công khai link là chưa phát sinh rủi ro hiển thị).

Các trang pháp lý phía người dùng đã có sẵn trên site (`/nguon-du-lieu` công bố giấy
phép theo LEGAL-002 — mã nguồn MIT, dữ liệu tự biên soạn CC BY 4.0, dòng nguồn OFF
giữ ODbL 1.0 + ghi công; `/chinh-sach-bao-mat`; `/mien-tru-y-te`) — kiểm tra chúng mở
được ở §7.4 khi nghiệm thu.

---

## 12. Giới hạn đã biết (ghi trung thực)

1. **Chưa từng chạy bằng Docker Engine thật.** Toàn bộ vòng kiểm thử của DOCKER-001
   chạy bằng Podman 4.9 trên VM sandbox của dự án (Docker daemon không tạo được
   container trong sandbox lồng nhau): build 3 target đạt, seed khớp baseline,
   smoke HTTP + `POST /api/shake` 200, validate 0 🔴, restart không seed đè,
   Pest trong container 167/167, diễn tập production đạt (`quick_login:false`,
   thiếu APP_KEY bị chặn boot). File compose đã qua `docker compose config`.
   → **Lần `docker compose` thật đầu tiên là trên VPS của bạn**; rủi ro còn lại thấp
   nhưng khác 0 — xem mục "lần chạy đầu" trong §13.
2. **Scheduler và queue chưa có service riêng.** Compose hiện có đúng 3 service.
   `QUEUE_CONNECTION=database` trong env mẫu — job xếp hàng vào DB sẽ **không có
   worker xử lý** cho tới khi bổ sung service `queue:work`; Laravel scheduler
   (`schedule:run`) cũng chưa chạy trên server. Hiện tại các tính năng production
   không phụ thuộc job nền để phục vụ request; riêng **cron quét link-rot hằng tuần
   của dự án chạy ở phía hệ thống của Muse, không chạy trên server production** và
   không ảnh hưởng vận hành site. Khi cần job nền thật, bổ sung hai service dùng
   chung image `app` với command `php artisan queue:work --tries=3` và
   `php artisan schedule:work` — việc đó là một đợt đóng gói riêng, chưa thuộc gói này.
3. **Một server duy nhất.** Không có HA/cân bằng tải; MySQL cùng host với app.
   Phù hợp quy mô hiện tại; điểm nghẽn đầu tiên khi tải tăng là RAM của MySQL + FPM.
4. **Kích thước image `app` ~744 MB** (gồm `data/` 116 MB) — build/pull lần đầu chậm
   hơn các ứng dụng Laravel thông thường; đã chấp nhận ở DOCKER-001.
5. **Cập nhật dữ liệu biên soạn ≠ cập nhật code** (§9.1): code mới tự vào khi rebuild,
   dữ liệu biên soạn mới cần quy trình đồng bộ riêng có bàn giao — không tự ý seed đè.

---

## 13. Troubleshooting

### 13.1. Lỗi có khả năng gặp ở lần `docker compose` thật đầu tiên

| Triệu chứng | Nguyên nhân | Cách xử lý |
|---|---|---|
| Compose báo lỗi parse quanh thẻ YAML `!override` trong file prod | Docker Compose **< 2.24** | Nâng Compose plugin theo §2.2 (`sudo apt-get install -y docker-compose-plugin` từ kho Docker chính thức) rồi `docker compose version` kiểm lại. Đường vòng cuối cùng: sao `docker-compose.yml` thành bản riêng tại server và xoá thủ công khối `ports` của `db` trong bản đó (chấp nhận file lệch repo — ghi chú lại, không commit) |
| Container `web` không lên: `port 80 is already allocated` | Host đã có nginx/apache khác nghe 80, hoặc stack cũ chưa tắt | `sudo lsof -i :80` tìm tiến trình; tắt dịch vụ đó (`sudo systemctl stop apache2`/`nginx`) hoặc chuyển hẳn sang mô hình §6.2 (web nghe 127.0.0.1:8080, Caddy/nginx host nghe 80/443) |
| `app` thoát ngay, log: `LỖI: APP_KEY chưa được đặt cho production…` | `APP_KEY` trống/placeholder bị xoá trắng | Sinh khoá theo §4.2, điền vào `.env.production`, `$DC up -d --force-recreate app` |
| `app` chờ DB 120 giây rồi thoát, lặp lại | Lệch mật khẩu DB do bẫy nội suy §4.3 (MySQL đã khởi tạo bằng mật khẩu mặc định) | Kiểm lệnh chạy có `--env-file .env.production` chưa. Nếu volume đã khởi tạo sai và **chưa go-live**: `$DC down -v` (chỉ lúc này mới được dùng `-v`) rồi lên lại đúng lệnh. Nếu đã có dữ liệu: giữ nguyên mật khẩu MySQL đang có và sửa `.env.production` khớp theo, hoặc `ALTER USER` trong MySQL |
| 502 Bad Gateway từ nginx/Caddy trong vài phút đầu | `app` vẫn đang migrate/seed lần đầu | Xem `$DC logs -f app`; seed lần đầu là việc phút-scale — chờ tới khi log yên + `$DC ps` báo healthy rồi thử lại |
| Trang tải được nhưng asset CSS/JS 404, giao diện vỡ | Image `web` cũ/khác commit với `app` | `$DC up -d --build` build lại cả hai target cùng commit; hard-refresh trình duyệt |
| Lỗi quyền ghi `storage` (log Laravel báo `Permission denied` ở `storage/…`) | Volume `app_storage`/`app_logs` bị tạo với chủ sở hữu lạ (thường do từng chạy container bằng root thủ công) | `$DC exec -u root app chown -R www-data:www-data storage bootstrap/cache` rồi `$DC restart app` (entrypoint cũng tự `chown` khi khởi động) |
| Site chạy nhưng redirect/link vẫn `http://` hoặc lỗi mixed-content sau khi có HTTPS | `APP_URL` trong `.env.production` chưa đúng `https://…` hoặc config cache cũ | Sửa `APP_URL`, `$DC up -d --force-recreate app` (entrypoint tự `config:cache` lại ở production) |
| Đăng nhập admin báo sai mật khẩu dù đúng `ADMIN_PASSWORD` | Tài khoản đã được tạo ở lần khởi động trước với mật khẩu khác — entrypoint **không** ghi đè tài khoản đã có | Đổi mật khẩu bằng tinker theo §5.4 |
| Hết đĩa sau nhiều lần build | Image/cache Docker tích tụ | `docker system df` xem; `docker image prune` (chỉ xoá image không dùng — giữ lại image của bản đang chạy và bản rollback gần nhất, §9.2) |

### 13.2. Lỗi đã gặp thật trong vòng kiểm thử DOCKER-001 (đã sửa trong gói — ghi lại để nhận diện)

| Sự cố | Nguyên nhân gốc | Cách gói hiện tại đã xử lý |
|---|---|---|
| Mọi trang 500 `MissingAppKeyException` dù entrypoint vừa `key:generate` (vòng 2) | Biến `APP_KEY=` **rỗng** từ env_file được Laravel ưu tiên hơn giá trị trong file `.env`, nên FPM không thấy khoá vừa sinh | Entrypoint export khoá vừa sinh vào môi trường tiến trình trước khi chạy FPM (§5.2 mục 2). Gặp lại triệu chứng này ở production thì gần như chắc chắn do `APP_KEY` trong `.env.production` bị để trống — xử lý như lỗi APP_KEY ở §13.1 |
| 152/167 test kèm warning `file_get_contents(/var/www/html/.env)` khi chạy Pest trong container (vòng Pest 1) | Image không chứa `.env` (đúng thiết kế — env đến từ env_file), phpdotenv cảnh báo thiếu file | Tầng `test` của Dockerfile tự tạo `.env` rỗng. Chỉ liên quan khi chạy test trong container, không ảnh hưởng production |
| Boot production thiếu APP_KEY **không** bị chặn ở các bản nháp đầu | Kiểm tra khoá đặt sau bước migrate | Bản cuối: kiểm tra khoá là bước đầu của entrypoint và **thoát ngay** — đây là hành vi đúng hiện tại, gặp thông báo này là hệ thống đang bảo vệ bạn (§13.1) |
| Mọi lệnh artisan crash `Class "Laravel\Pail\PailServiceProvider" not found` (vòng kiểm chứng lại 2026-11-02) | Build từ cây nguồn đang chạy dev: hai file cache sinh sẵn `bootstrap/cache/packages.php` + `services.php` bị COPY vào image, trỏ tới package dev mà vendor `--no-dev` không có. Bản sạch từ git vốn không chứa hai file này | `.dockerignore` loại hẳn hai file này khỏi context build |
| Mọi trang trả `File not found.` (FPM 404) dù file có trong image (vòng kiểm chứng lại 2026-11-02) | Cây nguồn trên máy build mang quyền 0660 (umask hạn chế); COPY giữ nguyên quyền nên worker `www-data` và nginx không đọc được mã nguồn | Dockerfile chuẩn hoá `chmod -R a+rX /var/www/html` ngay sau khi COPY |
| Sau boot đầu, `dish_nutrition` chỉ có 579 dòng thay vì 649 (vòng kiểm chứng lại 2026-11-02) | Entrypoint chỉ seed; seed tính sẵn dinh dưỡng cho món ≥ Mức 2, phần còn lại xưa nay do lệnh recompute chạy tay ở bản dev đảm nhiệm | Entrypoint chạy `lacdoi:recompute-calories --with-ingredients` ngay sau seed lần đầu (§5.2 mục 6) |
| MySQL chết ngay ở init: `10-init.sql: Permission denied`, chạy lại vẫn lỗi (vòng kiểm chứng lại 2026-11-02) | File `docker/mysql-init.sql` ở nơi checkout không cho user khác đọc (0660) — tiến trình init trong container chạy bằng user `mysql` không đọc được file mount. Init dở để lại volume "độc": khởi động lại không chạy lại init | Bản sạch từ git là 0644 nên không gặp. Nếu gặp: `chmod 644 docker/mysql-init.sql`, rồi `$DC down -v` và chạy lại từ đầu |

### 13.3. Lỗi vận hành thường gặp khác

| Triệu chứng | Cách xử lý |
|---|---|
| `$DC ps` trống rỗng / "no configuration file provided" | Đang đứng sai thư mục hoặc chưa định nghĩa lại biến `$DC` ở phiên shell mới — `cd ~/shake-hungry` và định nghĩa lại theo §5.1/§6.2 |
| Sửa `.env.production` nhưng app không đổi hành vi | `env_file` chỉ nạp khi container được tạo lại: `$DC up -d --force-recreate app` |
| MySQL chậm/OOM trên VPS nhỏ | Kiểm `docker stats`; cân nhắc nâng RAM. Không giảm buffer pool bừa — ghi nhận triệu chứng và báo phiên phát triển |
| Chứng thư HTTPS báo lỗi/không gia hạn (phương án B) | `sudo certbot renew --dry-run`; kiểm cổng 80 còn mở và DNS còn trỏ đúng. Phương án Caddy: `sudo journalctl -u caddy` xem log xin chứng thư |
| Muốn xem DB bằng công cụ ngoài (TablePlus/DBeaver…) | Đừng mở cổng 3306 ra Internet. Dùng SSH tunnel: `ssh -L 3307:127.0.0.1:3306 <vps>` — nhưng bản prod không publish 3306 ngay cả trên host; đường đúng là tunnel vào container qua `docker exec` hoặc tạm thêm override publish `127.0.0.1:3307:3306` cho `db` trong `docker-compose.host.yml`, dùng xong xoá |

---

## 14. Checklist go-live tổng (một trang)

- [ ] VPS đạt §2.1; Docker Engine + **Compose ≥ 2.24** đã cài (§2.2); user thuộc nhóm `docker`
- [ ] Firewall chỉ mở 22/80/443; SSH bằng khoá, mật khẩu SSH đã tắt (§2.3)
- [ ] Mã nguồn ở đúng commit bàn giao (bundle đã `verify` / clone đúng nhánh `dev`) (§3)
- [ ] `.env.production` đã điền đủ: APP_KEY thật, APP_URL đúng domain, 3 mật khẩu mạnh,
      `APP_DEBUG=false`, **`ADMIN_QUICK_LOGIN=false`**; file quyền `600` (§4)
- [ ] Mọi lệnh compose đều mang `--env-file .env.production` + đủ file `-f` (§4.3, §5.1)
- [ ] Stack lên đủ 3 service; log entrypoint có tạo admin + seed xong (§5)
- [ ] Đã đăng nhập admin lần đầu và **đổi mật khẩu**; đã xoá `ADMIN_PASSWORD` khỏi env (§5.4)
- [ ] DNS `lacdoi.tsnam.name.vn` trỏ đúng IP; HTTPS phương án A hoặc B hoạt động,
      HTTP redirect sang HTTPS (§6)
- [ ] Nghiệm thu §7 đạt hết: đếm file↔DB khớp, `validate-data` **0 🔴**, smoke test
      toàn 200, `POST /api/shake` trả món, `login-options` trả `quick_login:false`
- [ ] Backup DB bản đầu tiên đã tạo + đã diễn tập khôi phục; cron backup đã đặt (§10)
- [ ] Checklist bảo mật §11.1 đạt hết; quét cổng từ ngoài đạt kỳ vọng §11.2
- [ ] **Chính sách hiển thị nguồn hạn chế (§11.3) đã được chốt + ghi hồ sơ** — mục
      cuối cùng trước khi công khai link production

---

*Tài liệu này thuộc bộ `docs/10-deployment/`: cài dev ở máy khác xem
[SETUP-DEV.md](SETUP-DEV.md); chi tiết đóng gói + nhật ký kiểm thử xem
[DOCKER-001](../05-tasks/tasks/DOCKER-001.md); checklist bàn giao tổng quát giữ ở
[DEPLOYMENT.md](DEPLOYMENT.md) §5.*
