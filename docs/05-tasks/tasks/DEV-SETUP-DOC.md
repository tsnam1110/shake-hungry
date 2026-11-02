# DEV-SETUP-DOC — Tài liệu setup môi trường dev trên máy khác

**Mã task:** DEV-SETUP-DOC
**Phase:** Ngoài giai đoạn — tài liệu vận hành (điều kiện cho chủ dự án tự triển khai)
**Trạng thái:** ✅ HOÀN TẤT 2026-11-02
**Phụ thuộc:** SETUP-001…004 (nền cài đặt), DEPLOYMENT.md (tài liệu dựng máy 2026-08-31)
**Căn cứ:** chủ dự án giao 2026-11-02 — "Viết tài liệu setup môi trường dev của dự án ở
máy khác", cùng đợt với gói Docker go-live (task riêng).

## Kết quả

Tài liệu chính: [docs/10-deployment/SETUP-DEV.md](../../10-deployment/SETUP-DEV.md) — cài
từ số 0 trên Windows/macOS/Linux: điều kiện tiên quyết, MySQL 8 đúng chuẩn dự án
(collation `utf8mb4_vi_0900_ai_ci` + `log_bin_trust_function_creators`), clone + `.env`
(cảnh báo `ADMIN_QUICK_LOGIN`), seed + số kỳ vọng, tạo tài khoản admin bằng tinker
(seeder không tạo user), chạy + quy tắc bắt buộc build sau khi sửa `resources/css|js`,
link test Pinggy, kiểm thử Pest trên DB test riêng, troubleshooting từ các lỗi thật của
dự án, QT-1…QT-8 rút gọn + kỷ luật commit pathspec.

Mọi phiên bản/lệnh đã đối chiếu file thật tại baseline `a310c22`: `composer.json`
(PHP ^8.3, Laravel ^13.17), `package.json` (Vite ^8 → Node ≥20.19/≥22.12; React ^19.3,
antd ^6.6), `.env.example` (root không mật khẩu, `APP_URL` cổng 8000), `phpunit.xml`
(DB test `shake_hungry_test`, ép `ADMIN_QUICK_LOGIN=false`), `config/admin.php` +
`AuthController::quickLogin` (vào admin đầu tiên, 422 khi chưa có admin),
`database/seeders/DatabaseSeeder.php` (thứ tự 7 seeder, không seeder user), CLAUDE.md
§0 (654 món · 434 nguyên liệu · 82 quả · 649/654 tính được · 5 trigger).

Bản sao bàn giao giống hệt bản gốc đặt tại thư mục goal `files/`:
`tai-lieu-setup-dev-may-khac-2026-11-02.md`.

## Ghi chú

- DEPLOYMENT.md §1–§5 (2026-08-31) được SETUP-DEV thay thế ở phần hướng dẫn cài dev;
  DEPLOYMENT.md giữ §5 checklist bàn giao + §6 production. Đính chính ghi tại CLAUDE §0
  (lần 38).
- Docker: tại thời điểm viết repo chưa có Dockerfile/compose — tài liệu ghi rõ
  "⚠️ CHƯA HIỆN THỰC" và trỏ sang gói Docker go-live đang bổ sung, không tự chế.
