# Quản lý Môi trường & Triển khai — Lắc Đói

Tài liệu hướng dẫn triển khai của dự án đã được tinh gọn thành **2 file duy nhất**:

1. **[SETUP.md](SETUP.md)**: Hướng dẫn cài đặt và thiết lập môi trường phát triển (Local & Dev), chạy native hoặc bằng Docker, dùng chung MySQL host cổng 3306 với các dự án khác.
2. **[DEPLOY.md](DEPLOY.md)**: Hướng dẫn chi tiết triển khai lên Production / VPS bằng Docker (dùng chung file `docker-compose.yml`), cấu hình reverse proxy Caddy HTTPS, nghiệm thu và backup vận hành.

---

### Tóm tắt cấu hình Docker & File Môi trường

- File compose: Duy nhất [`docker-compose.yml`](../../docker-compose.yml).
- File env mẫu:
  - [`.env.example`](../../.env.example): Dành cho chạy trực tiếp (Native / Laragon).
  - [`.env.docker.example`](../../.env.docker.example): Dành cho chạy Docker (cả Dev và Production/VPS).
- Kết nối CSDL MySQL: Cổng `3306` dùng chung cho tất cả các dự án và quản lý qua Navicat / GUI.
