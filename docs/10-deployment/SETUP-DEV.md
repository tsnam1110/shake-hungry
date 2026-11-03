# SETUP-DEV — Cài môi trường dev Lắc Đói trên máy khác

**Cập nhật lần cuối:** 2026-11-02 — lập mới (task DEV-SETUP-DOC). Mọi phiên bản và lệnh trong tài
liệu này đã đối chiếu với file thật của repo tại baseline `a310c22`: `composer.json`,
`package.json`, `.env.example`, `phpunit.xml`, `database/seeders/`, `config/admin.php`,
[SETUP-004](../05-tasks/tasks/SETUP-004.md) và CLAUDE.md §0.
**Bản gốc:** file này trong repo là bản gốc. Bản sao bàn giao cho chủ dự án (thư mục goal
`files/`) có nội dung giống hệt.
**Quan hệ với tài liệu cũ:** [DEPLOYMENT.md](DEPLOYMENT.md) §1–§5 (viết 2026-08-31) là bản
hướng dẫn dựng máy cũ — còn đúng ở khung quy trình nhưng đã thiếu nhiều chi tiết hiện hành
(extension PHP, tài khoản admin, `ADMIN_QUICK_LOGIN`, Pest, quy tắc build). **Từ 2026-11-02,
cài dev theo tài liệu này.** DEPLOYMENT.md vẫn giữ §5 (checklist bàn giao) và §6 (production).

---

## 1. Tổng quan kiến trúc (bản rút gọn)

| Lớp | Công nghệ | Nguồn đối chiếu |
|---|---|---|
| Backend | Laravel **13** (`laravel/framework ^13.17`) trên PHP **^8.3** | `composer.json` |
| CSDL | **MySQL 8** — không dùng SQLite/PostgreSQL cho dự án này (quyết định SETUP-004) | `composer.json`, `.env.example` |
| Trang công khai | Blade + Vite 8 + Tailwind CSS 4 (`resources/css/app.css`, `resources/js/app.js`) | `package.json`, `vite.config.js` |
| Trang quản trị `/admin` | React **19** + Ant Design (antd) **6** (`resources/js/admin/main.jsx`) | `package.json`, `vite.config.js` |
| Dữ liệu | **File trong `data/` là nguồn sự thật** (QT-3). Database chỉ là bản dựng lại bằng seeder — máy mất DB thì seed lại, không mất dữ liệu | CLAUDE.md §3 QT-3 |

Chuỗi chạy tối thiểu: MySQL 8 → `migrate:fresh --seed` → `npm run build` → `php artisan serve`
(cổng 8000).

---

## 2. Điều kiện tiên quyết

| Thành phần | Phiên bản | Ghi chú |
|---|---|---|
| Git | bất kỳ | Clone repo GitHub `tsnam1110/shake-hungry`, nhánh `dev` |
| PHP | **8.3+** (`"php": "^8.3"` trong `composer.json`; máy dự án đang chạy 8.3.6) | Cần các extension của Laravel 13: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml` + **`pdo_mysql`** và `curl`. Kiểm bằng `php -m` |
| Composer | 2.x | |
| Node.js + npm | Node **≥ 20.19** hoặc **≥ 22.12** (yêu cầu engine của Vite 8 — `package.json`: `vite ^8.0.0`); máy dự án đang chạy Node 24 / npm 10 | Chỉ để build asset và chạy Vite dev |
| MySQL | **8.0+** (máy dự án: 8.0.46) | Xem §3 — có hai điều kiện bắt buộc riêng của dự án |

Theo hệ điều hành:

- **Windows:** Laragon (bản full) là môi trường gốc của dự án — có sẵn PHP, MySQL, và tài khoản
  `root` không mật khẩu đúng quy ước §3. Node cài riêng từ nodejs.org.
- **macOS:** `brew install php@8.3 mysql node composer` rồi `brew services start mysql`.
- **Linux (Ubuntu/Debian):** `apt install php8.3 php8.3-mysql php8.3-mbstring php8.3-xml
  php8.3-curl php8.3-bcmath php8.3-zip mysql-server nodejs npm composer`.

> **Docker:** ⚠️ CHƯA HIỆN THỰC tại thời điểm viết (kiểm repo ngày 2026-11-02: chưa có
> `Dockerfile`/`docker-compose.yml`). Gói Docker phục vụ go-live đang được bổ sung riêng —
> khi có, tài liệu production bằng Docker sẽ nằm cùng thư mục `docs/10-deployment/` và
> DEPLOYMENT.md §6 sẽ trỏ tới. **Đừng tự chế Dockerfile cho dev** — quy trình §3–§6 dưới đây
> là đường chính thức.
>
> **Đính chính 2026-11-02 (DOCKER-001):** gói Docker nay ĐÃ có ở gốc repo (`Dockerfile`,
> `docker-compose.yml`, `docker-compose.prod.yml`) và đã tự kiểm thử đạt — xem
> [DOCKER-001](../05-tasks/tasks/DOCKER-001.md). Đường dev chính thức vẫn là §3–§6 dưới đây;
> Docker là đường đóng gói/go-live (dev bằng Docker khi cần: `cp .env.docker.example .env.docker`
> rồi `docker compose up -d --build`, mở http://localhost:8000).

---

## 3. Cài MySQL 8 đúng chuẩn dự án

Hai điều kiện bắt buộc (SETUP-004 + CLAUDE.md đính chính lần 4):

1. Database tạo với collation **`utf8mb4_vi_0900_ai_ci`** — tìm kiếm không dấu và sắp xếp
   tiếng Việt đúng ở tầng database. Tạo sai collation là phải tạo lại (xem §8).
2. Server bật **`log_bin_trust_function_creators = 1`** khi có binary logging — nếu không,
   migration tạo trigger (R6/R8) sẽ lỗi **1419**.

Tài khoản database dùng **mặc định `root`, không mật khẩu** (kiểu Laragon) — quyết định của
chủ dự án, **không tạo user database riêng** cho dự án.

```sql
-- Chạy bằng tài khoản root của MySQL:
CREATE DATABASE shake_hungry      CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
CREATE DATABASE shake_hungry_test CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
SET GLOBAL log_bin_trust_function_creators = 1;
```

`SET GLOBAL` mất hiệu lực khi MySQL khởi động lại — ghi bền vững vào cấu hình server:
`my.cnf`/`my.ini` (mục `[mysqld]`): `log_bin_trust_function_creators = 1`.

Kiểm tra sau khi tạo:

```sql
SELECT DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA
 WHERE SCHEMA_NAME = 'shake_hungry';   -- phải là utf8mb4_vi_0900_ai_ci
```

---

## 4. Clone và cấu hình

```bash
git clone git@github.com:tsnam1110/shake-hungry.git
cd shake-hungry
git checkout dev
composer install
cp .env.example .env        # Windows cmd: copy .env.example .env
php artisan key:generate
```

`.env.example` đã chứa sẵn các giá trị đúng cho dev (`DB_CONNECTION=mysql`,
`DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=shake_hungry`, `DB_USERNAME=root`,
`DB_PASSWORD=` trống, `APP_URL=http://localhost:8000`). Các biến cần biết:

| Biến | Mặc định | Lưu ý |
|---|---|---|
| `APP_KEY` | trống → `key:generate` điền | Thiếu biến này app báo lỗi 500 ngay trang đầu |
| `APP_URL` | `http://localhost:8000` | Đổi khi chạy cổng khác / qua tunnel (§6) |
| `ADMIN_QUICK_LOGIN` | **không có trong `.env.example`** → mặc định `false` (đọc tại `config/admin.php`) | Xem cảnh báo dưới đây |

> ⚠️ **`ADMIN_QUICK_LOGIN=true` — CHỈ bật trên máy dev cá nhân, tuyệt đối không bật ở bất kỳ
> môi trường nào người khác chạm tới được.** Khi bật, trang đăng nhập admin hiện nút vào thẳng
> tài khoản admin đầu tiên **không cần mật khẩu** — ai mở được trang đăng nhập cũng thành
> admin cao nhất. Mỗi lần dùng đều ghi log cảnh báo (`ADMIN QUICK-LOGIN đã được dùng` kèm IP).
> File `.env` không được commit. `phpunit.xml` ép biến này về `false` khi chạy test.

---

## 5. Dựng dữ liệu và tài khoản

```bash
php artisan migrate:fresh --seed
php artisan lacdoi:validate-data    # kỳ vọng: 0 lỗi 🔴
```

Seeder chạy theo thứ tự khai trong `database/seeders/DatabaseSeeder.php`:
regions → reference foods → cooking yields → FNDDS → ingredients → dishes → fruits —
toàn bộ đọc từ `data/`.

**Số lượng kỳ vọng** (baseline 2026-11-02, nguồn: CLAUDE.md §0 + census AUDIT-ALLERGEN;
kho còn đang được bổ sung nên đối chiếu bằng cách đếm file như cột phải):

| Đối tượng | Kỳ vọng | Đối chiếu |
|---|---|---|
| Món | **654** | `ls data/dishes/vn/*.json \| wc -l` và `SELECT count(*) FROM dishes;` |
| Nguyên liệu | **434** | 435 file trong `data/ingredients/` trừ `_index.json`; `SELECT count(*) FROM ingredients;` |
| Quả | **82** | `ls data/fruits/*.json \| wc -l` |
| Món tính được dinh dưỡng | **649/654** | CLAUDE.md §0 (5 món còn thiếu dữ liệu thật) |
| Trigger trong DB | **5** (R6/R8) | `SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA='shake_hungry';` |

Số không khớp = có dữ liệu chỉ nằm trong DB của máy gốc chưa export ra `data/` — vi phạm
QT-3, xử lý theo DEPLOYMENT.md §3.

### Tài khoản admin

**Seeder KHÔNG tạo tài khoản nào.** Sau khi seed, tạo admin đầu tiên bằng tinker
(`users.role` nhận `admin` / `editor` / `user`):

```bash
php artisan tinker
>>> \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@lacdoi.local', 'password' => bcrypt('MAT-KHAU-CUA-BAN'), 'role' => 'admin']);
```

Đặt lại mật khẩu khi quên cũng bằng tinker:
`User::where('email', '...')->first()->update(['password' => bcrypt('...')])`.

Lưu ý: nút đăng nhập nhanh (§4) chỉ hoạt động khi **đã có ít nhất một user `role=admin`** —
chưa có thì API trả 422. Tài khoản người dùng cuối tự đăng ký qua giao diện web, không cần tạo tay.

---

## 6. Chạy dự án

```bash
npm install
npm run build
php artisan serve --port=8000
```

- Trang chủ: http://localhost:8000 · Trang quản trị: http://localhost:8000/admin
- Khi đang phát triển frontend, chạy thêm `npm run dev` (Vite HMR) ở một terminal khác.
  (Repo có sẵn script `composer run setup` làm install + `.env` + key + migrate + npm install
  + build một lượt — nhưng với dự án này vẫn phải chạy `migrate:fresh --seed` theo §5 để có dữ liệu.)
- `.npmrc` của repo đã đặt `ignore-scripts=true` — giữ nguyên, đừng xoá.

> ### QUY TẮC BẮT BUỘC — sửa frontend là phải build
> Sau khi sửa **bất kỳ** file nào trong `resources/css/**` hoặc `resources/js/**`, **phải**
> chạy `npm run build` rồi kiểm chứng: (1) bundle mới xuất hiện trong `public/build/assets/`
> với hash mới, (2) trang thật tham chiếu đúng hash đó (xem nguồn trang hoặc
> `public/build/manifest.json`). `php artisan view:cache` chỉ biên dịch Blade — **không**
> thay được bước build. Bài học sự cố UI-020 (2026-11-01): thiếu build làm class CSS mới
> không tồn tại ở bản phục vụ, ảnh hiển thị kích thước gốc tràn trang.

### Mở link test tạm ra ngoài (khi cần cho người khác xem)

```bash
ssh -p 443 -R0:localhost:8000 a.pinggy.io
```

Lệnh in ra một link dạng `https://*.free.pinggy.net` — **link miễn phí hết hạn sau 60 phút**
và chỉ sống khi cửa sổ SSH còn mở. Cần link dài hạn thì phải có tài khoản Pinggy (token)
hoặc triển khai thật — xem DEPLOYMENT.md §6.

> ### PROXY-001 — vì sao trang qua tunnel từng vỡ thành HTML trần (2026-11-03)
> Pinggy chấm dứt TLS ở ngoài rồi chuyển tiếp bằng HTTP tới `localhost:8000`, kèm
> `X-Forwarded-Proto: https` và `X-Forwarded-Host` là domain Pinggy. Trước bản sửa này,
> `bootstrap/app.php` không cấu hình tin proxy, nên Laravel tưởng request là `http://`
> và sinh URL asset Vite dạng `http://<domain-pinggy>/build/…`. Trình duyệt mở trang
> bằng `https` chặn hết CSS/JS đó vì **mixed content** — giao diện vỡ đúng như ảnh chụp
> 2026-11-03: không còn CSS, logo SVG phình to, nav chỉ còn link trần, dù bản thân file
> CSS tải trực tiếp bằng `https` vẫn trả 200 và đủ 57.500 byte.
>
> Cách sửa (đã nằm trong code, không cần đổi `.env` khi đổi tunnel): `bootstrap/app.php`
> gọi `$middleware->trustProxies(at: '*', …)` cho 4 header `X-Forwarded-*`, để scheme/host
> của URL sinh ra khớp với những gì người xem thực sự thấy. Kiểm chứng sau mỗi lần mở
> tunnel mới: mở nguồn trang public, asset CSS/JS phải bắt đầu bằng `https://`, còn mở
> `http://localhost:8000` trực tiếp thì vẫn là `http://` như cũ. **Không cần đổi `APP_URL`
> thành domain tunnel** chỉ để sửa asset — `APP_URL` chỉ dùng cho URL tuyệt đối trong
> email/redirect, còn asset theo host đã forward. Lưu ý: tunnel qua HTTP egress proxy của
> VM Muse phải dùng `ProxyCommand` như §6 của runbook vận hành, SSH trực tiếp bị reset.

Tailscale Funnel: chỉ dùng được khi máy dev là **máy cá nhân có Tailscale đầy đủ**
(`tailscale funnel 8000`). VM Muse của dự án là client chỉ-chiều-ra (không nhận kết nối
vào) nên **không** host Funnel từ VM đó được — đừng mất thời gian thử.

---

## 7. Kiểm thử

```bash
php artisan test          # hoặc: vendor/bin/pest
php artisan lacdoi:validate-data
```

- Test chạy trên **MySQL thật**, database riêng `shake_hungry_test` — `phpunit.xml` đã ép
  `DB_DATABASE=shake_hungry_test` và `ADMIN_QUICK_LOGIN=false`. Lý do: các ràng buộc
  R1–R10 (CHECK, trigger) phải kiểm ở tầng database, SQLite không tái hiện được (TEST-001).
  Nhờ vậy chạy test **không phá** DB dev `shake_hungry`.
- ⚠️ **Không chạy hai tiến trình artisan nặng đồng thời trên cùng máy** (ví dụ vừa
  `migrate:fresh --seed` vừa chạy Pest, hoặc hai phiên cùng test): các đợt làm việc song
  song của dự án đã từng làm hỏng DB kiểm chứng theo cách này. Cần chạy thì chờ tiến trình
  kia kết thúc — kiểm bằng `ps aux | grep artisan`.
- **Không chạy `lacdoi:export-data` bừa.** Vòng seed → export của dự án là **điểm cố định**
  (seed từ `data/` rồi export phải ra đúng từng byte — có `ExportStabilityTest` khoá việc
  này). Export chỉ chạy khi chủ đích đồng bộ DB → file theo quy trình DATA-REPOSITORY;
  sửa dữ liệu thì **sửa file trong `data/` rồi seed lại**, không sửa tay trong DB rồi export đè.

## 8. Troubleshooting — các lỗi dự án đã gặp thật

| Triệu chứng | Nguyên nhân | Cách xử lý |
|---|---|---|
| Migration lỗi `1419 — You do not have the SUPER privilege and binary logging is enabled` | Server chưa bật `log_bin_trust_function_creators` | Làm §3 (cả `SET GLOBAL` lẫn ghi vào `my.cnf`/`my.ini`), chạy lại `migrate:fresh --seed` |
| Tìm kiếm/sắp xếp tiếng Việt sai ở tầng DB | DB tạo sai collation | Tạo lại DB với `utf8mb4_vi_0900_ai_ci` (§3) rồi seed lại — dữ liệu nằm ở `data/` nên không mất gì |
| Trang không ăn CSS mới; ảnh hiển thị kích thước gốc tràn trang | Sửa `resources/css\|js` nhưng chưa `npm run build` (sự cố UI-020) | Build lại + kiểm hash bundle theo quy tắc §6 |
| Trang qua tunnel/proxy HTTPS vỡ thành HTML trần, logo phình to, nav chỉ còn link trần (PROXY-001, 2026-11-03) | Laravel không tin header `X-Forwarded-*` nên sinh asset dạng `http://` bị trình duyệt chặn vì mixed content | Đã sửa trong `bootstrap/app.php` (`trustProxies`); kiểm chứng: nguồn trang public phải có asset `https://`, local vẫn `http://` — xem §6 |
| DB trống / mất toàn bộ dữ liệu sau khi đổi máy hoặc VM bị thay mới | DB chỉ là bản dựng lại (QT-3) | Tạo 2 DB (§3) → `migrate:fresh --seed` → tạo lại admin (§5) → `npm run build` |
| `Connection refused` khi migrate/serve | MySQL chưa chạy, hoặc sai `DB_PORT` | Khởi động MySQL; kiểm `DB_HOST/DB_PORT` trong `.env` (mặc định 127.0.0.1:3306) |
| Lỗi 500 ngay trang đầu, log nhắc `APP_KEY` | Chưa chạy `key:generate` | `php artisan key:generate` |
| Cổng 8000 bị chiếm (`Address already in use`) | Tiến trình serve cũ còn chạy | Tìm và tắt tiến trình cũ (`lsof -i :8000` trên macOS/Linux; `netstat -ano \| findstr :8000` trên Windows), hoặc serve cổng khác và sửa `APP_URL` tương ứng |
| Nút đăng nhập nhanh trả 403 | `ADMIN_QUICK_LOGIN` đang tắt | Chỉ bật trong `.env` của máy dev cá nhân (§4) — đây là hành vi đúng ở môi trường khác |
| Nút đăng nhập nhanh trả 422 | Chưa có user `role=admin` nào | Tạo admin theo §5 trước |
| `composer install` báo thiếu extension | PHP thiếu extension so với §2 | Đối chiếu `php -m` với danh sách §2, cài extension còn thiếu rồi chạy lại |
| `npm run build` lỗi ngay khi khởi động Vite | Node quá cũ so với Vite 8 | Nâng Node lên ≥ 20.19 hoặc ≥ 22.12 (§2) |

## 9. Quy tắc làm việc tối thiểu cho dev mới

**Đọc [CLAUDE.md](../../CLAUDE.md) trước khi làm bất cứ việc gì** — đó là nguồn đầy đủ.
Tóm tắt 8 quy tắc tuyệt đối (§3 của CLAUDE.md):

| Quy tắc | Một dòng |
|---|---|
| QT-1 | LLM không bao giờ sinh con số dinh dưỡng — chỉ đề xuất ánh xạ/định lượng/tên; calo do hàm thuần tuý tính từ bảng tham chiếu; bản ghi LLM mang `source='llm_suggested'` + `needs_review=true` |
| QT-2 | Không nhập dữ liệu license bẩn (danh sách cấm trong LICENSE-COMPLIANCE) |
| QT-3 | Mọi dữ liệu nằm dạng file trong `data/`; DB chỉ là bản dựng lại |
| QT-4 | Một bảng thành phần thực phẩm duy nhất cho mỗi phép tính — không trộn nguồn |
| QT-5 | Không hiển thị con số chính xác giả — dinh dưỡng luôn là khoảng + độ tin cậy |
| QT-6 | Tính vị/ngũ hành gán ở tầng **nguyên liệu**, là lớp văn hoá phụ, không phải tư vấn sức khoẻ |
| QT-7 | Sửa code/dữ liệu phải sửa tài liệu **trong cùng commit**; tài liệu sai thì chèn khối "Đính chính", không xoá lặng lẽ |
| QT-8 | Việc lớn/khó đảo ngược/đổi hướng: phân tích và trình phương án, chờ chủ dự án chốt rồi mới làm |

Kỷ luật khi nhiều người/nhiều phiên làm cùng lúc trên một cây làm việc:

- **Commit theo pathspec từng file của mình** (`git add <file> ...`), **không `git add -A`** —
  cây làm việc thường xuyên có file dở của phiên khác; cuốn nhầm file của người khác vào
  commit là sự cố đã được dự án ghi nhận và cấm.
- Trước khi commit, `git status` + xem diff đã staged để chắc chắn chỉ gồm việc của mình.
- Sửa `resources/css|js` → `npm run build` trong cùng commit (quy tắc §6).
- Chạy test/seed: kiểm không có tiến trình artisan khác đang chạy (§7).

## 10. Checklist cài xong

- [ ] `php -v` ≥ 8.3, `php -m` đủ extension §2; `node -v` đạt §2; MySQL 8 đang chạy
- [ ] Hai DB tồn tại với collation `utf8mb4_vi_0900_ai_ci`; `log_bin_trust_function_creators = 1`
- [ ] `composer install` xong; `.env` đã tạo từ `.env.example`; `key:generate` đã chạy
- [ ] `migrate:fresh --seed` xong; `lacdoi:validate-data` → **0 🔴**
- [ ] Số đếm khớp bảng §5 (654 món · 434 nguyên liệu · 82 quả · 5 trigger)
- [ ] Đã tạo tài khoản admin và đăng nhập được `/admin`
- [ ] `npm install` + `npm run build` xong; http://localhost:8000 mở được, giao diện đúng
- [ ] `php artisan test` xanh trên DB `shake_hungry_test`
