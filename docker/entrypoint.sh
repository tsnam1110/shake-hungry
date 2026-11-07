#!/bin/sh
# Entrypoint container app Lac Doi (DOCKER-001):
#   cho DB -> .env toi thieu -> APP_KEY -> migrate -> seed NEU DB trong ->
#   tinh dinh duong (recompute) ngay sau seed lan dau -> tao admin NEU duoc
#   cau hinh -> storage:link -> cache (production) -> php-fpm.
# Seed chi chay khi bang dishes trong: khoi dong lai KHONG bao gio ghi de du lieu.
set -e
cd /var/www/html

# .env: compose env_file bom bien moi truong vao tien trinh (php-fpm de
# clear_env=no nen Laravel doc duoc). File .env chi la noi key:generate ghi -
# neu thieu, dung tu ban mau di kem image.
if [ ! -f .env ]; then
    cp .env.docker.example .env
fi

# APP_KEY: production bat buoc khai san trong .env.production (mat key = hong
# phien/mat khau da ma hoa). Ngoai production, tu sinh khi chua co.
# LUU Y: bien moi truong rong (env_file khai APP_KEY= trong) duoc Laravel uu
# tien hon file .env - nen sau khi sinh khoa phai EXPORT lai vao moi truong
# cua tien trinh de php-fpm (tien trinh con) thua huong dung khoa da sinh.
if [ -z "${APP_KEY}" ]; then
    if [ "${APP_ENV}" = "production" ]; then
        echo "LOI: APP_KEY chua duoc dat cho production - khai trong .env.production roi tao lai container." >&2
        exit 1
    fi
    if ! grep -q '^APP_KEY=base64:' .env; then
        php artisan key:generate --force
    fi
    APP_KEY=$(sed -n 's/^APP_KEY=//p' .env | head -n 1)
    export APP_KEY
fi

# Cho MySQL san sang (toi da ~120 giay).
echo "Dang cho co so du lieu ${DB_HOST:-db}:${DB_PORT:-3306}..."
php -r '
$h=getenv("DB_HOST")?:"db"; $p=(int)(getenv("DB_PORT")?:"3306");
$u=getenv("DB_USERNAME")?:"root"; $pw=getenv("DB_PASSWORD")?:"";
$deadline=time()+120;
$lastCode="unknown";
while (time()<$deadline) {
    try { new PDO("mysql:host=$h;port=$p", $u, $pw, [PDO::ATTR_TIMEOUT=>5]); exit(0); }
    catch (PDOException $e) {
        $lastCode=$e->errorInfo[1] ?? $e->getCode();
        if (in_array((int)$lastCode, [1045, 1044], true)) {
            fwrite(STDERR, "MySQL tu choi dang nhap/quyen truy cap (ma $lastCode). Kiem tra DB_USERNAME/DB_PASSWORD va quyen cua tai khoan.\n");
            exit(1);
        }
        sleep(2);
    }
}
fwrite(STDERR, "Khong ket noi duoc co so du lieu sau 120 giay (ma $lastCode).\n"); exit(1);
'

# Cache cau hinh cu khong duoc che bien moi truong moi khi restart container.
php artisan config:clear
php artisan migrate --force

# Seed tu data/ - CHI khi chua co mon nao (DB moi). Khoi dong lai khong seed de.
SEED_PENDING=storage/app/.initial-seed-in-progress
if [ -f "$SEED_PENDING" ]; then
    echo "LOI: lan seed dau bi ngat. Kiem tra log va khoi phuc seed theo DEPLOY.md; khong tu bo qua hoac reset database." >&2
    exit 1
fi
DISH_COUNT=$(php -r '
require "vendor/autoload.php";
$app=require "bootstrap/app.php";
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo \App\Models\Dish::count();
')
case "$DISH_COUNT" in
    ''|*[!0-9]*) echo "LOI: khong doc duoc so mon; dung khoi dong." >&2; exit 1;;
    0) echo "Co so du lieu trong - dang seed tu data/..."
       touch "$SEED_PENDING"
       php artisan db:seed --force
       # Seed chi tinh san dinh duong cho mot phan mon; recompute de lan
       # khoi dong dau tien dat do phu day du nhu ban dev (DOCKER-001).
       echo "Dang tinh dinh duong cho toan bo mon..."
       php artisan lacdoi:recompute-calories --with-ingredients
       rm -f "$SEED_PENDING";;
    *) echo "Da co $DISH_COUNT mon - bo qua seed.";;
esac

# Tao tai khoan quan tri lan dau neu ADMIN_EMAIL/ADMIN_PASSWORD duoc khai
# (chi tao khi chua ton tai; khong doi mat khau cua tai khoan da co).
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
    echo "Da tao tai khoan quan tri: $email\n";
} else {
    echo "Tai khoan quan tri da ton tai - giu nguyen.\n";
}'
fi

php artisan storage:link 2>/dev/null || true

# Cache toi uu cho production (moi truong khac bo qua de sua code khong can xoa cache).
if [ "${APP_ENV}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
# Cac module nay bien tap file truc tiep, khong ghi thay vao DB.
# Production bind data/ ve repo de thay container khong mat sua doi (QT-3).
for data_dir in data/combinations data/vegetables data/drinks; do
    chown -R www-data:www-data "$data_dir"
done

exec "$@"
