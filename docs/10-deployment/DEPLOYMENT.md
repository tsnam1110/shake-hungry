# Dựng lại dự án trên máy mới — Lắc Đói

**Cập nhật lần cuối:** 2026-08-31 — cập nhật theo lần cài đặt thật (SETUP-001), thêm §2b các chỗ vướng
**Trạng thái:** Quy trình §2 đã chạy thật trên máy gốc (Laragon/Windows). Chưa kiểm trên máy trắng
thứ hai — làm điều đó khi đóng GĐ1 (DoD giai đoạn).

---

## 1. Yêu cầu

| Thành phần | Phiên bản | Ghi chú |
|---|---|---|
| PHP | 8.3+ | Laragon có sẵn |
| Composer | 2.x | |
| **MySQL** | **8.0+** | Nền tảng chính từ 2026-10-30 — xem [ARCHITECTURE §4](../02-architecture/ARCHITECTURE.md) và [SETUP-004](../05-tasks/tasks/SETUP-004.md) |
| Node.js | 20+ | Chỉ để build asset |
| Git | | |

Không cần extension nào. Điều kiện bắt buộc phía MySQL: database tạo với collation `utf8mb4_vi_0900_ai_ci`, và server bật `log_bin_trust_function_creators` (nếu có binary logging) để user thường tạo được trigger — xem §2.

---

## 2. Cài đặt

```bash
git clone <repo> shake-hungry
cd shake-hungry
composer install
cp .env.example .env
php artisan key:generate
```

Tạo database (đúng charset/collation tiếng Việt):

```sql
CREATE DATABASE shake_hungry CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
CREATE DATABASE shake_hungry_test CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
-- Nếu server bật binary logging mà chưa có dòng này, tạo trigger sẽ lỗi 1419:
SET GLOBAL log_bin_trust_function_creators = 1;  -- và ghi vào my.cnf: log_bin_trust_function_creators = 1
```

Tài khoản database dùng **mặc định `root`, không mật khẩu** (kiểu Laragon) — không
tạo user riêng cho dự án.

Sửa `.env`: `DB_CONNECTION=mysql`, host, cổng (3306), tên DB, `DB_USERNAME=root`,
`DB_PASSWORD` để trống.

```bash
php artisan migrate:fresh --seed
php artisan lacdoi:validate-data
npm install && npm run build
```

## 3. Kiểm chứng cài đặt thành công

Đây là tiêu chí nghiệm thu của QT-3: **clone về máy trắng phải ra đúng dữ liệu của máy gốc.**

```bash
php artisan lacdoi:validate-data     # không được có lỗi 🔴
```

Kiểm bằng SQL:

```sql
SELECT count(*) FROM ingredients;      -- phải khớp số file trong data/ingredients/
SELECT count(*) FROM dishes;           -- phải khớp số file trong data/dishes/**/
SELECT count(*) FROM reference_foods;  -- phải khớp MANIFEST
```

Kiểm điều kiện nền tảng MySQL:

```sql
SELECT DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA
 WHERE SCHEMA_NAME = 'shake_hungry';   -- phải là utf8mb4_vi_0900_ai_ci
SELECT COUNT(*) FROM information_schema.TRIGGERS
 WHERE TRIGGER_SCHEMA = 'shake_hungry';  -- 5 trigger R6/R8
```

**Nếu số lượng không khớp**, nghĩa là có dữ liệu chỉ tồn tại trong database của máy gốc mà chưa được
export ra `data/`. Đó là vi phạm QT-3 — quay lại máy gốc chạy `lacdoi:export-data` và commit.

---

## 4. Dữ liệu tham chiếu lớn

File USDA gốc (~1,6 GB) **không nằm trong repo**. Nếu cần nhập lại từ đầu:

1. Xem `data/reference/MANIFEST.json` để biết URL nguồn, tên bản phát hành, và **checksum SHA-256**.
2. Tải đúng bản đó về `storage/imports/`.
3. Đối chiếu checksum.
4. `php artisan lacdoi:import-usda`.

**Việc này thường không cần thiết** — bản đã lọc trong `data/reference/` đủ để chạy. Chỉ cần khi muốn
mở rộng phạm vi lọc.

---

## 5. Danh sách kiểm khi bàn giao dự án

Người bàn giao chạy hết danh sách này trước khi bàn giao:

- [ ] `git status` sạch — không có dữ liệu chưa commit
- [ ] Đã chạy `lacdoi:export-data`, không còn dữ liệu chỉ nằm trong DB
- [ ] `lacdoi:validate-data` không có lỗi 🔴
- [ ] Clone sang thư mục trắng, chạy đủ quy trình §2, kết quả khớp §3
- [ ] [ROADMAP](../05-tasks/ROADMAP.md) phản ánh đúng trạng thái thực tế của mọi task
- [ ] Bảng trạng thái giai đoạn ở [CLAUDE.md §0](../../CLAUDE.md) đã cập nhật
- [ ] Mọi câu hỏi chưa trả lời đã nằm ở mục "Câu hỏi mở" của ROADMAP
- [ ] Tài liệu này đã cập nhật với các chỗ vướng thực tế gặp phải

---

## 6. Môi trường production

> **Đính chính 2026-11-02 (DOCKER-001):** đoạn "chưa thiết kế" dưới đây đã hết hạn.
> Gói đóng gói Docker đã hoàn thành và tự kiểm thử đạt (xem
> [DOCKER-001](../05-tasks/tasks/DOCKER-001.md)): `Dockerfile` đa tầng + `docker-compose.yml`
> + `docker-compose.prod.yml` ở gốc repo; migration + seed chạy tự động có điều kiện
> (chỉ seed khi DB trống — production vẫn **không** là nguồn sự thật, `data/` mới là nguồn).
> Chủ dự án đã tự khởi xướng chuẩn bị go-live bằng Docker; **tài liệu go-live production
> đầy đủ là đợt riêng tiếp theo**, viết trên nền gói này. Đoạn cũ giữ nguyên bên dưới
> để tra cứu lịch sử quyết định.
>
> **Đính chính 2026-11-02 (GOLIVE-DOC):** tài liệu go-live production bằng Docker nay
> ĐÃ có tại [GOLIVE-DOCKER.md](GOLIVE-DOCKER.md) — bản hiện hành để chủ dự án tự
> go-live (kiến trúc prod, chuẩn bị VPS, `.env.production`, HTTPS cho
> `lacdoi.tsnam.name.vn`, nghiệm thu, vận hành, backup/rollback, bảo mật). Từ nay
> go-live theo tài liệu đó; mục này chỉ còn giá trị lịch sử.

⚠️ **Chưa thiết kế.** GĐ4 đã qua nhưng production vẫn chưa chốt — đang chờ chủ dự án chọn phương án go-live (VPS + subdomain / Laravel Cloud / PC cá nhân + Tailscale Funnel). Những thứ cần quyết khi đó: hosting, cách chạy migration
và seed trên môi trường thật, sao lưu, và cách đồng bộ `data/` với database production (một chiều —
production **không** được là nguồn sự thật).
