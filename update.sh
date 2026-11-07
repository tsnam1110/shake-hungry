#!/bin/sh
# Lắc Đói — Script cập nhật dự án trên VPS Production
# Tự động: pull code -> build tối ưu -> migrate -> refresh cache -> dọn rác disk
# Lưu ý: Tuyệt đối KHÔNG chạy db:seed tự động để bảo toàn dữ liệu chỉnh sửa trong admin.
set -e

echo "==> 1 Build và khởi chạy container (kích hoạt BuildKit tăng tốc)..."
DOCKER_BUILDKIT=1 docker compose --env-file .env.docker up -d --build

echo "==> 2 Tối ưu hoá cache cấu hình, routes và views..."
docker compose --env-file .env.docker exec -T app php artisan optimize:clear
docker compose --env-file .env.docker exec -T app php artisan config:cache
docker compose --env-file .env.docker exec -T app php artisan route:cache
docker compose --env-file .env.docker exec -T app php artisan view:cache

echo "==> 3 Thu hồi dung lượng ổ đĩa (xoá image Docker cũ)..."
docker image prune -f

echo "==> Hoàn tất! Ứng dụng đã được cập nhật thành công trên cổng 1113."
