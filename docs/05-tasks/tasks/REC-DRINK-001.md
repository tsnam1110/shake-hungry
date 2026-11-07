# REC-DRINK-001 — Nước uống kèm khi lắc món

**Status:** ✅ DONE
**Ngày tạo / bắt đầu:** 2026-11-07
**Ngày hoàn thành:** 2026-11-07
**Phụ thuộc:** REC-001/003, thư viện DRINK-MODULE, UI-SHAKE-UX-001 (đã có).

## Yêu cầu và phân tích trước triển khai

Chủ dự án yêu cầu thêm lựa chọn nước uống; cơm nhà chọn nước có thể tự pha như
chanh/cam. Không thêm số dinh dưỡng, không biên soạn hoặc sửa data/, không đổi
schema. Quy định cồn vẫn giữ: không gợi ý alcohol/cocktail/bia, kể cả có cờ tuổi.

Thêm công tắc Kèm nước uống (mặc định tắt, chỉ áp dụng lượt lắc hiện tại),
request `include_drink` boolean. DrinkRecommender chọn kho riêng từ Drink,
không nhập Drink vào Dish pool hoặc ảnh hưởng số món/kcal/nhật ký món ăn.
Mục tiêu kcal vẫn dành cho món ăn; thẻ nước ghi rõ giới hạn này.

Điều kiện cơm nhà dựa vào dữ liệu hiện có: pha-che, thuộc giải khát/nước ép/
sinh tố, difficulty dễ, prep_minutes 1–20, có nguyên liệu và bước pha.
Ngưỡng 20 phút là quy tắc tiện dụng, không phải dữ liệu dinh dưỡng. Loại thao
tác ép ở nhóm giải khát (nước mía cần máy chuyên dụng); nước ép trái cây có
thể dùng dụng cụ gia đình. Không có ứng viên thì giữ món ăn và báo thiếu nước.

Chỉ dùng hồ sơ đã duyệt, không cồn hoặc mức cồn >0;
không chọn cocktail/mocktail/bia/rượu/vang. Trường uncertain/uncertain_note
được giữ và hiển thị nhãn lưu ý theo QT-9, không suy số dinh dưỡng. Ăn ngoài có thể chọn pha chế hoặc
đóng gói không cồn. Nếu có dị ứng hoặc ăn chay, yêu cầu mọi dòng nguyên liệu
ánh xạ được, loại garnish chưa ánh xạ và đồ đóng gói; kiểm cả cùng nhóm dị ứng
và thành phần con bằng TaggingRules. Không suy đoán từ tên, không nới lọc nước.
Vì thế nước chanh thiếu ánh xạ nước cốt có thể không được chọn khi có dị ứng;
không tự tạo ánh xạ cho đủ pool.

## Triển khai và kiểm chứng

- Service DrinkRecommender; API CatalogController thêm dữ liệu drink riêng
  chỉ khi bật include_drink; dùng dị ứng/chay đã gộp hồ sơ từ shakeEngineInput.
- UI một công tắc, mô tả theo mode, thẻ link tới /do-uong/{slug}, ảnh và bước
  chi tiết từ thư viện, hiển thị safety_flags; giữ nước khi đổi từng món trong mâm.
- Tái lập lựa chọn theo seed, không ghi thay đổi kho. Nạp eager media và
  thành phần nguyên liệu theo lô, không truy vấn theo từng ly.
- Lượt chốt: **27/27 test, 89 assertions** — 11 ShakeDrink mới + 16
  Recommendation/ShakeTray hiện có — đạt trên MySQL 8.4.3 riêng cổng 33316.
  Không thay MySQL/cấu hình dev hoặc VPS. Chạy full suite trên DB test riêng;
  DB QA và DB baseline khác nhau, không đồng thời dùng chung schema.
- `npm run build`, PHP lint và Pint cho hai file mới, `git diff --check`: đạt.
  Pint ba file PHP cũ còn cảnh báo format y hệt bản HEAD (đã đối chiếu riêng);
  không format lại code ngoài phạm vi. Bundle admin lớn là cảnh báo build có sẵn.
- DB QA mới: migrate + seed từ data/ dựng được 647 món, 489 nguyên liệu,
  230 nước uống; validate **3.700 bản ghi, 0 lỗi, 21 cảnh báo**.
- HTTP Kernel + route/controller/engine thật trên DB QA: cơm nhà tắt/bật nước
  vẫn 3 món, ăn ngoài tắt/bật vẫn 1 món; nước là trường riêng. Lấy mẫu 100 seed
  có cả nuoc-chanh và nuoc-ep-cam; hồ sơ cam giữ uncertain/uncertain_note.
- Edge/Playwright 360px và 1440px, Blade thật + phản hồi lấy từ API QA thật:
  switch mặc định tắt, hint đổi theo mode, payload đúng, có link chi tiết nước,
  giữ nước khi đổi món trong mâm, nhãn uncertain hiển thị, không tràn ngang và
  không lỗi JS. Ảnh đã mở kiểm trực quan; không thêm dependency vào package.json.
- Full Pest lượt đầu: **251/253 đạt**, 2 thất bại. Một test nước còn kỳ vọng cũ
  loại uncertain do đã khởi động full suite trước khi chốt cách hiển thị nhãn;
  đã sửa test và chạy lại toàn nhóm liên quan 27/27 đạt. Thất bại còn lại là
  `ExportStabilityTest` vòng seed→export toàn cục.
- Đã tái lập baseline: dữ liệu HEAD được archive vào thư mục riêng, dùng DB
  baseline riêng và các seeder/exporter không thay đổi; vòng seed→export làm
  đổi **86 file JSON**, chứng minh bất ổn tồn tại trước tính năng này. Không
  sửa exporter hoặc chuẩn hoá dữ liệu ngoài phạm vi. File data/ trong repo đã
  khôi phục sau tác dụng phụ của test, diff data/ sạch; patch tác dụng phụ và
  báo cáo baseline lưu dưới backups/ (gitignored) để truy vết.

## Phần còn lại và triển khai

Chức năng đã thực hiện đủ yêu cầu; task chưa đánh DONE theo DoD backend vì
full suite còn lỗi ExportStability nền. Không tự sửa dữ liệu/nguồn dinh dưỡng
để làm test xanh. Cần một đợt riêng sửa vòng export trước khi chốt cổng này.
Không đổi schema: cập nhật source và build/deploy theo quy trình hiện hành,
không cần reset DB hay seed lại. Chưa push/deploy hoặc sửa dữ liệu production.

## Đính chính chốt DoD — 2026-11-07

[CLEANUP-001](CLEANUP-001.md) đã sửa exporter giữ safety_note, chuẩn hoá cơ học
86 JSON và cô lập test export. Ghi nhận lỗi/chưa hoàn tất phía trên là lịch sử
của lượt trước, không còn là trạng thái hiện tại. Full suite đạt **254/254 test,
1.120 assertions**; chạy lại trên dependency đã vá cũng đạt cùng tổng (33 test
ghi file tuần tự + 221 test còn lại). Browser QA và build chạy lại đạt.
Đóng REC-DRINK-001; không push/deploy và không reset dữ liệu production.
