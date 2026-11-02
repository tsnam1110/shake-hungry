# Lắc Đói — Dockerfile đa tầng (DOCKER-001)
# Tầng assets: Node 22 biên dịch frontend (Vite: trang công cộng + admin antd/React).
# Tầng app:    PHP 8.3 FPM + mã nguồn + vendor (composer --no-dev) + asset đã biên dịch.
# Tầng web:    nginx phục vụ public/ và chuyển PHP sang app:9000.
# Chọn FPM + nginx (thay vì Apache mod_php): tách tiến trình tĩnh/động rõ ràng,
# nginx nhẹ khi phục vụ ảnh/tài nguyên tĩnh, và khớp cách vận hành production phổ biến.
# syntax=docker/dockerfile:1

# ---------- Tầng 1: build frontend ----------
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---------- Tầng 2: ứng dụng PHP ----------
# Ghim đúng bản vá bảo mật: 8.3.36 là bản vá PHP2Shell (CVE-2026-109618/
# CVE-2026-109619, RCE không cần đăng nhập, 21/10/2026). KHÔNG hạ xuống
# tag trôi php:8.3 — image kéo trước 21/10/2026 là bản có lỗ hổng.
FROM php:8.3.36-fpm-bookworm AS app
WORKDIR /var/www/html

# Thư viện hệ thống cho các extension (ảnh gd, intl, zip).
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev libonig-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
        unzip \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring intl gd exif bcmath zip pcntl \
    && docker-php-ext-enable opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Cài vendor trước (tận dụng cache lớp khi chỉ đổi mã nguồn).
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Mã nguồn (gồm data/ — nguồn sự thật của dữ liệu, QT-3).
COPY . .
COPY --from=assets /app/public/build ./public/build

# Chuẩn hoá quyền đọc cho www-data (php-fpm) và nginx bất kể umask của máy
# build: COPY mang theo quyền của cây nguồn (vd 0660 trên máy dev) khiến
# worker không đọc được mã nguồn → FPM trả "File not found." (DOCKER-001).
RUN chmod -R a+rX /var/www/html

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-lacdoi.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-lacdoi.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

# ---------- Tầng 3: kiểm thử (chỉ dùng khi chạy Pest trong container) ----------
FROM app AS test
RUN composer install --optimize-autoloader --no-interaction --no-scripts
# .env rỗng để phpdotenv không cảnh báo thiếu file khi chạy test (biến thật
# truyền qua môi trường tiến trình); tầng này không dùng để phục vụ web.
RUN touch .env

# ---------- Tầng 4: web (nginx) ----------
# 1.31.7: bản mainline đã vá loạt CVE nginx công bố 14/10/2026 và
# CVE-2026-42533 (tràn bộ đệm ở map regex) — 1.27 đã cũ, xem DOCKER-001 §4.2.
FROM nginx:1.31.7-alpine AS web
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public
