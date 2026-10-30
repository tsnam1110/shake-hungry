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

Không cần extension nào (bản PostgreSQL cũ cần `pg_trgm`, `unaccent`, `vector`). Điều kiện bắt buộc phía MySQL: database tạo với collation `utf8mb4_vi_0900_ai_ci`, và server bật `log_bin_trust_function_creators` (nếu có binary logging) để user thường tạo được trigger — xem §2.

---

## 2. Cài đặt

```bash
git clone <repo> shake-hungry
cd shake-hungry
composer install
cp .env.example .env
php artisan key:generate
```

Tạo database (đúng charset/collation tiếng Việt) và user:

```sql
CREATE DATABASE shake_hungry CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
CREATE DATABASE shake_hungry_test CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
CREATE USER 'shake_hungry'@'localhost' IDENTIFIED BY '<mật khẩu>';
GRANT ALL PRIVILEGES ON shake_hungry.* TO 'shake_hungry'@'localhost';
GRANT ALL PRIVILEGES ON shake_hungry_test.* TO 'shake_hungry'@'localhost';
-- Nếu server bật binary logging mà chưa có dòng này, tạo trigger sẽ lỗi 1419:
SET GLOBAL log_bin_trust_function_creators = 1;  -- và ghi vào my.cnf: log_bin_trust_function_creators = 1
```

Sửa `.env`: `DB_CONNECTION=mysql`, host, cổng (3306), tên DB, user, mật khẩu.

```bash
php artisan migrate:fresh --seed
php artisan lacdoi:validate-data
npm install && npm run build
```

### 2b. Các chỗ vướng thực tế trên Windows/Laragon — gặp 2026-08-31

> ⚠️ **Hết áp dụng từ 2026-10-30:** mục này ghi lại thời dự án còn dùng PostgreSQL.
> Laragon có sẵn MySQL nên các vướng mắc dưới đây không còn xuất hiện. Giữ lại làm
> lịch sử theo QT-7, không xoá.

1. **Laragon không kèm PostgreSQL.** Cách đã dùng: tải bản **binaries portable** của
   EnterpriseDB (`postgresql-17.11-1-windows-x64-binaries.zip`, ~325 MB), giải nén vào
   `D:\laragon\bin\postgresql\postgresql-17.11-1`. Không cần cài service.
2. **Khởi tạo cluster với ICU tiếng Việt** (sắp xếp tên món đúng):
   ```bash
   initdb -D D:\laragon\data\postgresql-17 -U postgres -E UTF8 --locale-provider=icu --icu-locale=vi-VN --locale=C -A trust
   ```
   `-A trust` chỉ dành cho dev local (server chỉ nghe localhost). Production bắt buộc
   `scram-sha-256`.
3. **Khởi động / dừng:**
   ```bash
   pg_ctl -D D:\laragon\data\postgresql-17 -l D:\laragon\data\postgresql-17\server.log start
   ```
4. **PHP Laragon có sẵn DLL pgsql nhưng chưa bật** — thêm vào `php.ini`:
   `extension=pdo_pgsql` và `extension=pgsql`, rồi kiểm bằng `php -m | findstr pgsql`.
5. **psql trên PowerShell hỏng encoding tiếng Việt** khi truyền chuỗi qua `-c` — muốn chạy
   SQL có dấu, ghi ra file UTF-8 (không BOM) rồi `psql -f`.
6. **Tạo database:** `createdb -U postgres shake_hungry` và `createdb -U postgres shake_hungry_test`
   (bảng test cho Pest — phpunit.xml trỏ vào `shake_hungry_test`).

### Ghi chú về pgvector trên Windows

`pgvector` **không có sẵn** trong bản PostgreSQL tiêu chuẩn. Phải tải bản build khớp đúng phiên bản
PostgreSQL đang dùng.

Nếu chưa cài được: **GĐ1–GĐ3 vẫn chạy được không cần pgvector**. Chỉ GĐ4 ([REC-002](../05-tasks/tasks/REC-002.md))
mới cần. Có thể bỏ qua tạm, nhưng **phải ghi lại là đã bỏ qua**, không phải quên.

`pg_trgm` và `unaccent` có sẵn trong bản tiêu chuẩn — hai cái này **bắt buộc** từ GĐ2.

---

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

⚠️ **Chưa thiết kế.** Sẽ bổ sung khi tới GĐ4. Những thứ cần quyết khi đó: hosting, cách chạy migration
và seed trên môi trường thật, sao lưu, và cách đồng bộ `data/` với database production (một chiều —
production **không** được là nguồn sự thật).
