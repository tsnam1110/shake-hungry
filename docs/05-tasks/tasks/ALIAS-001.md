# ALIAS-001 — Tên gọi khác (tên vùng miền) cho món ăn và nguyên liệu/quả

- **Trạng thái:** 🔶 (hạ tầng xong 2026-10-31; đang điền dữ liệu — xem mục Kết quả)
- **Yêu cầu của chủ dự án (2026-10-31):** nhiều quả có tên gọi khác theo vùng miền — giữ nguyên tên chính, thêm tên gọi khác và tên gọi khác phải tìm kiếm được; tương tự với món ăn; ghi vào tài liệu hệ thống và nâng cấp tính năng liên quan nếu cần.
- **Thiết kế:** DATA-MODEL (mục `dish_names.note` + `ingredient_aliases`), luật kiểm tra **V18** (DATA-PIPELINE §5).

## Đã làm (hạ tầng)

- Món ăn: dùng cơ chế có sẵn `dish_names.is_alias` (seeder/exporter/quản trị đã hỗ trợ từ trước); bổ sung cột `note` ghi nhãn vùng miền (migration `2026_10_31_100004`). Lưu qua quản trị giữ nguyên `note` của alias còn tồn tại.
- Nguyên liệu: bảng mới `ingredient_aliases` (name + note, FULLTEXT ngram); khoá file `aliases: [{name, note?}]`; seeder + exporter vòng tròn đủ.
- Tìm kiếm: danh mục món (đã tra mọi dòng `names` — alias tự có hiệu lực), API nguyên liệu công khai + quản trị nguyên liệu tra thêm `aliases`; trang chi tiết món hiển thị "Tên gọi khác: X (miền Nam), ..."; biểu mẫu quản trị nguyên liệu có mục sửa tên gọi khác; trang hoa quả (FRUIT-006) tra theo tên chính + tên gọi khác ngay từ đầu.
- Kiểm chứng: `tests/Feature/AliasSearchTest.php` — 3 test (tìm món theo tên miền Nam "khổ qua" ra món "mướp đắng", chi tiết món trả alias + nhãn vùng, tìm nguyên liệu theo "Khóm" ra dứa thơm).

## Điền dữ liệu

- 85 nguyên liệu trái cây (đợt ALIAS-001a).
- 500 món (đợt ALIAS-001b).
- Nguyên tắc: chỉ ghi tên gọi khác **có thật** — cùng một món/quả, khác tên theo vùng miền hoặc thời kỳ (vd khổ qua = mướp đắng; thơm = khóm = dứa). Không gộp đối tượng chỉ tương tự (bánh cuốn ≠ bánh ướt). Món/quả không có tên gọi khác đáng tin thì để trống.
- Nguyên liệu ngoài nhóm quả: cơ chế áp dụng chung, điền dần theo nhu cầu.
