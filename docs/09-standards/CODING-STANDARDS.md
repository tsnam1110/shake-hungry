# Chuẩn code — Lắc Đói

**Cập nhật lần cuối:** 2026-08-31 — bản đầu tiên

---

## 1. PHP / Laravel

- PSR-12, `declare(strict_types=1)` ở mọi file.
- Laravel Pint để format. Chạy trước khi commit.

> **Đính chính 2026-11-07 — CLEANUP-001:** cấu hình `pint.json` dùng preset
> Laravel, loại `backups`, cache, storage và dependency khỏi phạm vi format.
> Chạy `composer format` hoặc `composer format:check`; CI kiểm format PHP.
> Theo [tài liệu Pint](https://laravel.com/framework/docs/13.x/pint), `--test`
> trả mã lỗi khi phát hiện vi phạm. Cổng đã kiểm bằng file lỗi chủ động.
- Kiểu trả về tường minh cho mọi method public.
- Controller → FormRequest → Action/Service → Resource. **Không validate inline.**
- Logic nghiệp vụ nằm ở lớp Action/Service, không ở Controller, không ở Model.

## 2. Đặt tên database

- Bảng: `snake_case`, số nhiều.
- Cột: `snake_case`, **không prefix theo tên bảng**.

> **Vì sao không dùng prefix:** dự án `edu-crm-v2` dùng prefix (`brh_`, `cls_`, `stsc_`) nhưng áp
> dụng không đều — có bảng có, có bảng không — khiến tài liệu ghi sai tên cột trong nhiều tháng và
> người viết code phải tra migration mỗi lần. Dự án này bỏ hẳn prefix để không tái diễn.

- Khoá ngoại: `<bảng_số_ít>_id` (`dish_id`, `ingredient_id`).
- Boolean: `is_*` hoặc `has_*` (`is_eat_out`, `needs_review`).
- Thời gian: `*_at`, kiểu `timestamp`.

## 3. Ba cột bắt buộc trên mọi bảng dữ liệu biên soạn

`source` · `confidence` · `needs_review`

Đây **không phải metadata phụ** — chúng là cột hạng nhất, xuất hiện trong mọi truy vấn hiển thị. Xem
[DATA-MODEL §0](../03-data-model/DATA-MODEL.md).

## 4. Hàm tính toán

Mọi hàm tính dinh dưỡng phải **thuần tuý**: không HTTP, không LLM, không `now()`, không đọc cấu hình
toàn cục. Cùng đầu vào luôn cho cùng đầu ra.

Lý do: để test được, và để **tính lại được** khi bảng nguyên liệu thay đổi.

## 5. Không hardcode

- Khoá API → `.env`
- Hệ số dinh dưỡng → bảng dữ liệu hoặc hằng số có **trích dẫn nguồn** trong
  [CALORIE-ENGINE §9](../06-nutrition/CALORIE-ENGINE.md)
- Đường dẫn → `config()`, không dùng đường dẫn tuyệt đối của máy mình

## 6. Test

- Pest. Test file đặt cạnh cấu trúc thư mục của code.
- **Bắt buộc có test cho:** engine tính calo, 9 ràng buộc toàn vẹn, engine gợi ý, và lệnh
  `lacdoi:export-data` (tính ổn định của JSON).
- Test tên tiếng Việt không dấu để dễ đọc: `it('chan ban ghi dinh duong do LLM sinh ra')`.

> **Bổ sung 2026-11-07:** test có thao tác export phải dùng bản sao JSON/CSV
> trong thư mục tạm, giữ kiểm tra hash từng byte và dọn đúng thư mục của test.
> Không để test viết lại `data/` của checkout; nguồn JSON trước/sau lượt test
> phải không đổi. Fixture tạm không được đi vào image, commit hoặc kiểm Pint.

> **Quy tắc về cổng chất lượng:** thêm bất kỳ kiểm tra tự động nào cũng phải kiểm chứng rằng nó **đỏ
> được** — cố ý chèn một lỗi, xem lệnh có báo không, rồi mới tin. Một cổng luôn xanh là bằng chứng
> giả, tệ hơn không có cổng nào.

## 7. Commit

- Tiếng Việt, thể mệnh lệnh: `Thêm engine tính calo cho món chiên`
- **Thay đổi dữ liệu và thay đổi code nên tách commit riêng** — trừ khi thay đổi code bắt buộc kéo
  theo thay đổi dữ liệu.
- Commit sửa code mà đổi thiết kế thì **phải kèm sửa tài liệu trong cùng commit** (QT-7).

## 8. Xử lý giá trị thiếu

Quy tắc xuyên suốt dự án:

```
Thiếu dữ liệu  →  trả null + ghi vào missing[]  →  hiển thị "chưa đủ dữ liệu"
Thiếu dữ liệu  ✗  thay bằng giá trị mặc định/trung bình/đoán
```

Không dùng `?? 0` cho giá trị dinh dưỡng. `0 kcal` và `không biết` là hai điều hoàn toàn khác nhau,
và gộp chúng lại là cách âm thầm tạo ra dữ liệu sai.
