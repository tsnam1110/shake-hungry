#!/bin/sh
# Entrypoint container app Lắc Đói (DOCKER-001):
#   chờ DB → .env tối thiểu → APP_KEY → migrate → seed NẾU DB trống →
#   tạo admin NẾU được cấu hình → storage:link → cache (production) → php-fpm.
# Seed chỉ chạy khi bảng dishes trống: khởi động lại KHÔNG bao giờ ghi đè dữ liệu.
set -e
cd /var/www/html

# .env: compose env_file bơm biến môi trường vào tiến trình (php-fpm để
# clear_env=no nên Laravel đọc được). File .env chỉ là nơi key:generate ghi —
# nếu thiếu, dựng từ bản mẫu đi kèm image.
if [ ! -f .env ]; then
    cp .env.docker.example .env
fi

# APP_KEY: production bắt buộc khai sẵn trong .env.production (mất key = hỏng
# phiên/mật khẩu đã mã hoá). Ngoài production, tự sinh khi chưa có.
# LƯU Ý: biến môi trường rỗng (env_file khai APP_KEY= trống) được Laravel ưu
# tiên hơn file .env — nên sau khi sinh khoá phải EXPORT lại vào môi trường
# của tiến trình để php-fpm (tiến trình con) thừa hưởng đúng khoá đã sinh.
if [ -z "${APP_KEY}" ]; then
    if [ "${APP_ENV}" = "production" ]; then
        echo "LỖI: APP_KEY chưa được đặt cho production — khai APP_KEY trong .env.production rồi khởi động lại." >&2
        exit 1
    fi
    if ! grep -q '^APP_KEY=base64:' .env; then
        php artisan key:generate --force
    fi
    APP_KEY=$(sed -n 's/^APP_KEY=//p' .env | head -n 1)
    export APP_KEY
fi

# Chờ MySQL sẵn sàng (tối đa ~120 giây).
echo "Đang chờ cơ sở dữ liệu ${DB_HOST:-db}:${DB_PORT:-3306}…"
php -r '
$h=getenv("DB_HOST")?:"db"; $p=(int)(getenv("DB_PORT")?:"3306");
$u=getenv("DB_USERNAME")?:"root"; $pw=getenv("DB_PASSWORD")?:"";
$deadline=time()+120;
while (time()<$deadline) {
    try { new PDO("mysql:host=$h;port=$p", $u, $pw); exit(0); }
    catch (Throwable $e) { sleep(2); }
}
fwrite(STDERR, "Không kết nối được cơ sở dữ liệu sau 120 giây.\n"); exit(1);
'

php artisan migrate --force

# Seed từ data/ — CHỈ khi chưa có món nào (DB mới). Khởi động lại không seed đè.
DISH_COUNT=$(php artisan tinker --execute='echo \App\Models\Dish::count();' 2>/dev/null | tail -n 1)
case "$DISH_COUNT" in
    ''|*[!0-9]*) echo "Không đọc được số món (bỏ qua seed): '$DISH_COUNT'";;
    0) echo "Cơ sở dữ liệu trống — đang seed từ data/…"; php artisan db:seed --force;;
    *) echo "Đã có $DISH_COUNT món — bỏ qua seed.";;
esac

# Tạo tài khoản quản trị lần đầu nếu ADMIN_EMAIL/ADMIN_PASSWORD được khai
# (chỉ tạo khi chưa tồn tại; không đổi mật khẩu của tài khoản đã có).
if [ -n "${ADMIN_EMAIL}" ] && [ -n "${ADMIN_PASSWORD}" ]; then
    ADMIN_EMAIL="$ADMIN_EMAIL" ADMIN_PASSWORD="$ADMIN_PASSWORD" ADMIN_NAME="${ADMIN_NAME:-Admin}" \
    php artisan tinker --execute='
$email=getenv("ADMIN_EMAIL");
if (!\App\Models\User::where("email",$email)->exists()) {
    \App\Models\User::create([
        "name"=>getenv("ADMIN_NAME")?:"Admin",
        "email"=>$email,
        "password"=>\Illuminate\Support\Facades\Hash::make(getenv("ADMIN_PASSWORD")),
        "role"=>"admin",
    ]);
    echo "Đã tạo tài khoản quản trị: $email\n";
} else {
    echo "Tài khoản quản trị đã tồn tại — giữ nguyên.\n";
}'
fi

php artisan storage:link 2>/dev/null || true

# Cache tối ưu cho production (môi trường khác bỏ qua để sửa code không cần xoá cache).
if [ "${APP_ENV}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

exec "$@"
