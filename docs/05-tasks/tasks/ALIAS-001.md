# ALIAS-001 — Tên gọi khác (tên vùng miền) cho món ăn và nguyên liệu/quả

- **Trạng thái:** ✅ (2026-10-31 — hạ tầng + điền dữ liệu xong; validate 0 lỗi 🔴, Pest 85/85)
- **Yêu cầu của chủ dự án (2026-10-31):** nhiều quả có tên gọi khác theo vùng miền — giữ nguyên tên chính, thêm tên gọi khác và tên gọi khác phải tìm kiếm được; tương tự với món ăn; ghi vào tài liệu hệ thống và nâng cấp tính năng liên quan nếu cần.
- **Thiết kế:** DATA-MODEL (mục `dish_names.note` + `ingredient_aliases`), luật kiểm tra **V18** (DATA-PIPELINE §5).

## Đã làm (hạ tầng)

- Món ăn: dùng cơ chế có sẵn `dish_names.is_alias` (seeder/exporter/quản trị đã hỗ trợ từ trước); bổ sung cột `note` ghi nhãn vùng miền (migration `2026_10_31_100004`). Lưu qua quản trị giữ nguyên `note` của alias còn tồn tại.
- Nguyên liệu: bảng mới `ingredient_aliases` (name + note, FULLTEXT ngram); khoá file `aliases: [{name, note?}]`; seeder + exporter vòng tròn đủ.
- Tìm kiếm: danh mục món (đã tra mọi dòng `names` — alias tự có hiệu lực), API nguyên liệu công khai + quản trị nguyên liệu tra thêm `aliases`; trang chi tiết món hiển thị "Tên gọi khác: X (miền Nam), ..."; biểu mẫu quản trị nguyên liệu có mục sửa tên gọi khác; trang hoa quả (FRUIT-006) tra theo tên chính + tên gọi khác ngay từ đầu.
- Kiểm chứng: `tests/Feature/AliasSearchTest.php` — 3 test (tìm món theo tên miền Nam "khổ qua" ra món "mướp đắng", chi tiết món trả alias + nhãn vùng, tìm nguyên liệu theo "Khóm" ra dứa thơm).

## Điền dữ liệu — KẾT QUẢ (2026-10-31)

- **Quả: 19/85 nguyên liệu có trường `aliases`** (19 tên gọi khác: Thơm/Khóm đã nằm trong
  name_vi của dứa nên không tách; các ca tiêu biểu: roi → Mận (miền Nam), táo tây → Bom
  (miền Nam), vải → Lệ chi (tên cũ), nho → Bồ đào (tên cũ), kiwi → Dương đào, chuối... ).
  Nhiều tên mẫu đã nằm sẵn trong `name_vi` dạng ngoặc đơn (dứa "thơm, khóm", quất "tắc",
  lêkima "quả trứng gà", mãng cầu "na") — tìm kiếm vẫn khớp qua name_vi, không tách để
  tránh động vào tên chính. Các ca bị loại đúng kỷ luật: cá-na ✗ "Trám" (Canarium khác
  loài), táo mèo ✗ "Sơn tra" (Crataegus khác loài), khế ✗ "khế tàu" (bilimbi khác loài).
- **Món: 66/500 món có tên gọi khác** (66 mục): gỏi ↔ nộm hai chiều, nem rán → chả giò,
  nem cuốn → gỏi cuốn, khổ qua ↔ mướp đắng, heo ↔ lợn, cá lóc ↔ cá quả, khoai mì ↔ sắn,
  bắp ↔ ngô, đậu phụ ↔ đậu hũ, ngao ↔ nghêu, bề bề ↔ tôm tít, cơm rang ↔ cơm chiên...
  6 món gỏi cá sống KHÔNG bị ép thành "nộm" (miền Bắc vẫn gọi là gỏi).
- **Cặp nghi trùng lặp — chờ chủ dự án quyết gộp/giữ** (phát hiện nhờ đối chiếu hai chiều):
  (1) `goi-mit-non` ↔ `nom-mit-non` — cùng món mít non trộn, hai file theo hai tên vùng;
  (2) `xoi-bap` ↔ `xoi-ngo` — bắp (Nam) = ngô (Bắc), gần như chắc chắn một món hai file;
  (3) nghi nhẹ: `banh-phu-the` ↔ `banh-xu-xe`, `banh-bao-banh-vac` ↔ `banh-hoa-hong-trang`
  (nội dung gần như trùng nhưng không phải cặp tên vùng miền).
- Nguyên tắc: chỉ ghi tên gọi khác **có thật** — cùng một món/quả, khác tên theo vùng miền hoặc thời kỳ (vd khổ qua = mướp đắng; thơm = khóm = dứa). Không gộp đối tượng chỉ tương tự (bánh cuốn ≠ bánh ướt). Món/quả không có tên gọi khác đáng tin thì để trống.
- Nguyên liệu ngoài nhóm quả: cơ chế áp dụng chung, điền dần theo nhu cầu.
