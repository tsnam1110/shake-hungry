# GOLIVE-RUNBOOK — Lắc Đói lên VPS, chạy lệnh theo thứ tự

Bản thực hành của [GOLIVE-DOCKER.md](GOLIVE-DOCKER.md): chỉ gồm lệnh để chạy.
Giải thích, lý do và xử lý lỗi sâu nằm ở file đó — file này không lặp lại.
Chỗ phải thay: `<EMAIL-ADMIN>`, `<IP-VPS>`, tên file bundle. Ngoài ra cứ chạy nguyên khối lệnh.

Điều kiện trước khi bắt đầu: VPS Ubuntu 22.04/24.04, ≥ 2 vCPU / 4 GB RAM / 40 GB đĩa,
đăng nhập được bằng SSH key; quản lý được DNS của `tsnam.name.vn`; đã nhận file bundle
bản mới nhất từ phiên phát triển. **Chưa chốt chính sách hiển thị nguồn hạn chế
(GOLIVE-DOCKER §11.3) thì làm hết tới Bước 7 rồi dừng — chưa công khai link.**

Định nghĩa một lần ở mỗi phiên shell mới (sau khi có Caddy ở Bước 7 thì dùng dòng thứ hai):

```bash
DC="docker compose --env-file .env.production -f docker-compose.yml -f docker-compose.prod.yml"
# Sau Bước 7:
DC="docker compose --env-file .env.production -f docker-compose.yml -f docker-compose.prod.yml -f docker-compose.host.yml"
```

---

## Bước 1 — Cài Docker + firewall

```bash
sudo apt-get update && sudo apt-get install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
docker compose version        # → phải ≥ 2.24
sudo docker run --rm hello-world
sudo usermod -aG docker "$USER"   # đăng xuất/đăng nhập lại sau lệnh này

sudo ufw allow 22/tcp && sudo ufw allow 80/tcp && sudo ufw allow 443/tcp
sudo ufw enable
```

## Bước 2 — Nhận mã nguồn bằng bundle

```bash
git bundle verify ~/shake-hungry-dev-update-N.bundle   # → "The bundle is okay"
git clone ~/shake-hungry-dev-update-N.bundle ~/shake-hungry
cd ~/shake-hungry && git checkout dev
git log --oneline -3        # → đối chiếu head với thông báo khi nhận bundle
git status --short          # → không in gì (cây sạch)
```

## Bước 3 — Tạo `.env.production` (mật khẩu tự sinh)

```bash
cd ~/shake-hungry
cp .env.production.example .env.production && chmod 600 .env.production
APPKEY=$(docker run --rm php:8.3.36-cli php -r 'echo "base64:" . base64_encode(random_bytes(32));')
DBPASS=$(openssl rand -base64 24 | tr -d '/+=' | head -c 28)
ROOTPASS=$(openssl rand -base64 24 | tr -d '/+=' | head -c 28)
ADMINPASS=$(openssl rand -base64 18 | tr -d '/+=' | head -c 22)
sed -i "s|^APP_KEY=.*|APP_KEY=$APPKEY|" .env.production
sed -i "s|^APP_URL=.*|APP_URL=https://lacdoi.tsnam.name.vn|" .env.production
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DBPASS|" .env.production
sed -i "s|^DB_ROOT_PASSWORD=.*|DB_ROOT_PASSWORD=$ROOTPASS|" .env.production
sed -i "s|^ADMIN_EMAIL=.*|ADMIN_EMAIL=<EMAIL-ADMIN>|" .env.production
sed -i "s|^ADMIN_PASSWORD=.*|ADMIN_PASSWORD=$ADMINPASS|" .env.production
echo "APP_KEY=$APPKEY"; echo "DB=$DBPASS"; echo "ROOT=$ROOTPASS"; echo "ADMIN=$ADMINPASS"
grep -c "false" .env.production   # ADMIN_QUICK_LOGIN=false phải còn nguyên
```

**Lưu ngay 4 dòng vừa in ra vào trình quản lý mật khẩu.** Mất APP_KEY là dữ liệu
đã mã hoá không đọc lại được, kể cả khi khôi phục từ backup.

## Bước 4 — Chạy hệ thống lần đầu

```bash
cd ~/shake-hungry
$DC config --quiet
$DC up -d --build            # lần đầu vài phút: tải image nền + build + seed
$DC logs -f app              # chờ tới dòng "Đã tạo tài khoản quản trị", Ctrl+C thoát
$DC ps                       # → db healthy · app healthy · web Up
curl -s -o /dev/null -w "%{http_code}\n" http://localhost/            # → 200
curl -s http://localhost/admin/api/login-options                      # → {"quick_login":false}
```

## Bước 5 — Đổi mật khẩu admin, xoá mật khẩu khỏi file env

```bash
$DC exec app php artisan tinker --execute='\App\Models\User::where("email","<EMAIL-ADMIN>")->first()->update(["password"=>bcrypt("<MAT-KHAU-MOI-CUA-ANH>")]);'
sed -i "s|^ADMIN_PASSWORD=.*|ADMIN_PASSWORD=|" .env.production
$DC up -d --force-recreate app
```

Đăng nhập thử `http://localhost/admin` bằng mật khẩu mới → vào được là xong bước này.

## Bước 6 — Trỏ DNS

Tại trang quản lý DNS của `tsnam.name.vn`, thêm bản ghi: Type `A` · Name `lacdoi` ·
Value `<IP-VPS>` · TTL `300`. Chờ rồi kiểm:

```bash
dig +short lacdoi.tsnam.name.vn   # → đúng IP của VPS mới sang Bước 7
```

## Bước 7 — HTTPS bằng Caddy

```bash
cd ~/shake-hungry
cat > docker-compose.host.yml <<'EOF'
services:
  web:
    ports: !override
      - "127.0.0.1:8080:80"
EOF
DC="docker compose --env-file .env.production -f docker-compose.yml -f docker-compose.prod.yml -f docker-compose.host.yml"
$DC up -d

sudo apt-get install -y debian-keyring debian-archive-keyring apt-transport-https curl
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt-get update && sudo apt-get install -y caddy
sudo tee /etc/caddy/Caddyfile > /dev/null <<'EOF'
lacdoi.tsnam.name.vn {
	reverse_proxy 127.0.0.1:8080
	encode gzip
}
EOF
sudo systemctl reload caddy
curl -s -o /dev/null -w "%{http_code}\n" https://lacdoi.tsnam.name.vn/   # → 200
```

## Bước 8 — Nghiệm thu (đạt hết mới xong)

```bash
# Số file phải bằng số DB:
ls data/dishes/vn/*.json | grep -v _index | wc -l; ls data/ingredients/*.json | grep -v _index | wc -l; ls data/fruits/*.json | wc -l
$DC exec app php artisan tinker --execute='echo \App\Models\Dish::count(),"/",\App\Models\Ingredient::count(),"/",\App\Models\Fruit::count(),PHP_EOL;'
$DC exec app php artisan lacdoi:validate-data    # → 0 🔴

BASE=https://lacdoi.tsnam.name.vn
for p in / /thu-vien /hoa-qua /mon/xoi-man /admin /api/dishes /api/fruits /api/ingredients /media/dishes/xoi-xeo.webp /nguon-du-lieu /chinh-sach-bao-mat /mien-tru-y-te; do printf "%-28s %s\n" "$p" "$(curl -s -o /dev/null -w '%{http_code}' $BASE$p)"; done
# → tất cả 200
curl -s $BASE/admin/api/login-options            # → {"quick_login":false}
curl -s -o /dev/null -w "%{http_code}\n" http://lacdoi.tsnam.name.vn/   # → 301/308 (tự sang https)
```

Mở trình duyệt vào trang chủ, bấm **Lắc** ra một mâm, mở một trang món thấy khối
"Tính vị:", đăng nhập `/admin` được. Xong.

## Bước 9 — Bật backup hằng ngày

```bash
mkdir -p ~/backups
$DC exec -T db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --triggers shake_hungry' | gzip > ~/backups/shake_hungry-$(date +%F-%H%M).sql.gz
ls -lh ~/backups/   # → có file dung lượng > 0
crontab -e          # dán dòng dưới, thay /home/<USER> bằng thư mục home của anh:
```

```cron
30 3 * * * cd /home/<USER>/shake-hungry && docker compose --env-file .env.production -f docker-compose.yml -f docker-compose.prod.yml -f docker-compose.host.yml exec -T db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --triggers shake_hungry' | gzip > /home/<USER>/backups/shake_hungry-$(date +\%F).sql.gz
```

Chép thêm các bản backup ra khỏi VPS (máy cá nhân/lưu trữ khác) theo nhịp tuần.

## Cập nhật bản mới (khi nhận bundle mới)

```bash
# 1. Backup DB trước (lệnh dump ở Bước 9). 2. Ghi lại head hiện tại để rollback:
cd ~/shake-hungry && git rev-parse --short HEAD
git fetch ~/shake-hungry-dev-update-M.bundle dev && git merge --ff-only FETCH_HEAD
$DC up -d --build
$DC logs -f app     # chờ log yên; chạy lại Bước 8
```

## Rollback

```bash
cd ~/shake-hungry && git checkout <head-cu-da-ghi>
$DC up -d --build
# Nếu bản mới đã đổi cấu trúc DB: khôi phục DB từ bản backup trước cập nhật rồi mới up:
# gunzip -c ~/backups/<file-truoc-cap-nhat>.sql.gz | $DC exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" shake_hungry'
```

## Lệnh vận hành hay dùng

```bash
$DC ps                              # trạng thái
$DC logs -f app                     # log app (web/db thay app bằng web/db)
$DC restart app                     # khởi động lại app
$DC down && $DC up -d               # khởi động lại cả stack (giữ dữ liệu)
```

⛔ **Không bao giờ** chạy `$DC down -v`, `migrate:fresh` hay `db:seed` trên server này —
xoá mất dữ liệu người dùng thật.

## Kẹt thì dừng ở đây

| Triệu chứng | Xử lý một lệnh |
|---|---|
| `app` thoát ngay, log nhắc APP_KEY | APP_KEY chưa điền: làm lại Bước 3 rồi `$DC up -d --force-recreate app` |
| `app` chờ DB 120 giây rồi thoát | Lệnh compose thiếu `--env-file`: luôn chạy qua biến `$DC`. Nếu là lần chạy đầu và chưa có dữ liệu: `$DC down -v` rồi làm lại Bước 4 |
| 502 trong vài phút đầu | App đang seed — xem `$DC logs -f app`, chờ healthy rồi thử lại |
| `port 80 is already allocated` | Host đang có nginx/apache: `sudo systemctl stop apache2 nginx` rồi `$DC up -d` |
| Giao diện vỡ, CSS/JS 404 | Build lại cả hai image cùng commit: `$DC up -d --build`, hard-refresh trình duyệt |

Ngoài 5 lỗi trên, gửi nguyên dòng log màu đỏ cho phiên phát triển — đừng tự mò tiếp.
