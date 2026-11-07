# CLEANUP-001 — Quét và làm sạch mã nguồn

**Status:** ✅ DONE
**Ngày tạo / bắt đầu:** 2026-11-07
**Ngày hoàn thành:** 2026-11-07
**Phụ thuộc:** nền ứng dụng hiện có; giữ nguyên thay đổi REC-DRINK-001 chưa commit.

## Phân tích và phạm vi trước triển khai

Chủ dự án yêu cầu quét, dọn dẹp và làm sạch dự án. Không đổi công nghệ,
schema, thuật toán tính dinh dưỡng, API nghiệp vụ hoặc dữ liệu người dùng.

1. Quét PHP syntax, debug/TODO/merge markers, định dạng bằng Pint, cấu hình
   build/deploy, thư mục sinh tạm và liên kết tài liệu. Lưu snapshot diff đầu vào.
2. Pint đang quét nhầm backups/; thêm cấu hình loại artefact/dependency. Chỉ
   chuẩn hoá các file source đã được báo lỗi (không format vendor/data/Blade).
3. ExportStability có lỗi nền: thiếu newline, index lỗi thời, exporter bỏ
   safety_note, optional media/tcm_traits rỗng lệch quy ước. Sửa exporter giữ
   cảnh báo an toàn; chuẩn hoá cơ học JSON và index, đối chiếu nội dung trước/sau.
   Không sửa hay sinh số dinh dưỡng, tên món, công thức, nguồn hoặc cờ duyệt.
4. Cô lập test export vào cây data tạm, không để test sửa data/ của người dùng.
   Giữ nguyên kiểm tra hash từng byte; không vô hiệu/loại bỏ test để làm xanh.
5. Dừng MySQL thử sau kiểm chứng; chỉ đề nghị dọn artefact do phiên làm việc
   tạo ra, giữ backup patch/report/ảnh kiểm chứng và mọi file của người dùng.
6. Kiểm chứng format/syntax/build, deploy checks, migration/seed/validate và
   toàn bộ Pest trên MySQL riêng. Chạy kiểm tra có lỗi trước khi sửa để chứng
   minh cổng chất lượng không luôn xanh; không truy cập hoặc reset VPS.

## Kết quả ban đầu

- 228 PHP files không lỗi cú pháp; không thấy debug dump/console log hoặc
  merge markers trong source được quét.
- Pint báo 57 file: 51 source + 6 PHP fixture dưới backups/; chưa có pint.json.
- Entry resources/js/app.js chỉ chứa comment, vẫn bị build và tải ở mọi trang;
  đã bỏ entry rỗng và tham chiếu tương ứng, giữ JavaScript inline/entry admin.
- Artefact MySQL do agent tạo chiếm khoảng 349 MiB, không phải DB dev/VPS.
- Diff export đã lưu: 30 file chỉ khác format, phần còn lại chủ yếu empty
  optional arrays/index; riêng safety_note của tiet-canh bị bỏ — phải sửa code
  trước khi chuẩn hoá, tuyệt đối không xoá cảnh báo để khớp exporter lỗi.

## Kiểm chứng cuối

- Full suite trước cập nhật dependency: **254/254 test, 1.120 assertions đạt**.
- Sau cập nhật: nhóm ghi file chạy tuần tự **33/33 test, 129 assertions đạt**;
  phần còn lại **221/221 test, 991 assertions đạt** trên ba schema MySQL riêng.
  Tổng **254/254 test, 1.120 assertions đạt** trên các dependency mới; không song
  song test ghi file. Package discovery sau cài thư viện cũng đạt.
- PHP syntax 228 file, Python syntax 3 script, Pint toàn source và cổng Pint
  cố ý thử file sai định dạng: đạt; `git diff --check` sạch.
- `npm run build` và browser QA 360/1440px đạt sau khi bỏ JS entry rỗng;
  cảnh báo bundle admin lớn có sẵn vẫn còn, không thay chức năng admin.
- DB thử mới từ trắng migrate/seed thành công: 647 món, 489 nguyên liệu,
  82 quả, 99 rau theo mùa, 230 nước uống, 38 cặp kết hợp. Validate **3.700
  bản ghi, 0 lỗi, 21 cảnh báo dữ liệu có sẵn**; không xoá cảnh báo bằng sửa nội dung.
- Đối chiếu sâu 86 JSON: 31 format/key-order, 53 bỏ optional arrays rỗng,
  2 index sinh lại; toàn bộ giá trị dinh dưỡng và safety_note giữ nguyên.
- Composer audit sau cập nhật **0 advisory, 0 abandoned**; npm production
  audit **0 vulnerability**; composer validate strict/platform requirements đạt.
- Offline deploy checks đạt cả luồng thành công, lỗi build/container/HTTP,
  cấu hình ban đầu và bảo toàn APP_KEY; không dùng Docker Engine/VPS thực.
- Rà 188 Markdown, 736 liên kết nội bộ thực: không thiếu đích.
- MySQL thử riêng cổng 33316 đã tắt bằng mysqladmin; không chạm DB dev/VPS.
- Report/snapshot ở backups/cleanup-review (gitignored). Chưa commit/push/deploy.

## Bổ sung phạm vi sau quét dependency

Composer audit phát hiện 11 advisory trên 6 package; npm production audit 0.
Đã đối chiếu advisory gốc Laravel/CommonMark/Symfony. Cập nhật tương thích,
không đổi PHP/major framework hoặc schema: Laravel 13.29.0→13.30.0 (bản tối
thiểu vá XSS debug), CommonMark 2.10.0→2.10.7, Symfony Foundation/Kernel/Routing
7.4.18→7.4.23, Mime 7.4.18→7.4.22. Dry-run xác nhận đúng 6 cập nhật, không
thêm/bỏ package. Ghi lock trước; chỉ cài vendor sau khi lượt test cũ kết thúc,
rồi chạy lại full suite trên dependency mới. Không tắt audit để che cảnh báo.

Nguồn chính thức:
[Laravel advisory](https://github.com/laravel/framework/security/advisories/GHSA-jh5r-qr3c-85q8),
[CommonMark advisory](https://github.com/thephpleague/commonmark/security/advisories/GHSA-3q6v-r5mr-hxv8),
[Symfony advisory](https://symfony.com/cve-2026-108082).

## Giới hạn dọn artefact

Bộ kiểm soát tự động từ chối thao tác Remove-Item -Recurse trên ba thư mục
backups/export-baseline, backups/drink-lint-baseline, backups/ui-review/node_modules
(với thông báo blocked by policy), dù đã kiểm đường dẫn trong workspace.
Không thử vượt chặn; giữ nguyên artefact cùng DB scratch/report dưới backups/.
Các thư mục này không vào Git, image Docker hoặc Pint. Không xoá dependency
vendor/node_modules đang dùng, không xóa file dữ liệu hay log của người dùng.

**Đối chiếu DoD:** backend, dữ liệu và frontend áp dụng đều đạt; không đổi
schema/nguồn/dinh dưỡng. Artefact bị chặn xóa được giữ và công khai ở trên,
không coi là đã dọn dung lượng đĩa.
