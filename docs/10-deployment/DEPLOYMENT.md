# Quản lý Môi trường & Triển khai — Lắc Đói

> ⚠️ **Đính chính 2026-11-07:** production dùng MySQL riêng và compose riêng; mô tả trước đây về một compose/env dùng chung đã hết hiệu lực. Hai hướng dẫn thao tác hiện hành:

1. **[SETUP.md](SETUP.md)**: Hướng dẫn cài đặt và thiết lập môi trường phát triển (Local & Dev), chạy native hoặc bằng Docker, dùng chung MySQL host cổng 3306 với các dự án khác.
2. **[DEPLOY.md](DEPLOY.md)**: Hướng dẫn chi tiết triển khai lên Production / VPS bằng Docker (`deploy.sh` + `docker-compose.deploy.yml`), cấu hình reverse proxy Caddy HTTPS, nghiệm thu và backup vận hành.

---

### Tóm tắt cấu hình Docker & File Môi trường

- Local/dev: [`docker-compose.yml`](../../docker-compose.yml) + `.env.docker`.
- Production: [`docker-compose.deploy.yml`](../../docker-compose.deploy.yml) + `.env.production`, tạo bởi [`deploy.sh`](../../deploy.sh).
- File env mẫu:
  - [`.env.example`](../../.env.example): Dành cho chạy trực tiếp (Native / Laragon).
  - [`.env.docker.example`](../../.env.docker.example): Dành cho Docker local/dev; production được deploy.sh tạo cấu hình riêng.
- Production: MySQL `db:3306` chỉ trong mạng Docker của Lắc Đói, user `lakdoi`, không publish cổng DB; web cố định `127.0.0.1:1113`.
- Local native có thể dùng MySQL Laragon và tài khoản root không mật khẩu. Không dùng thông tin này cho production.
