# [DATA-010] — Vùng miền của món chỉ còn 3 miền chính; đặc sản tỉnh/thành thành phân loại riêng

**Mã task:** DATA-010 · **Giai đoạn:** GĐ3 · **Loại:** Dữ liệu + Backend
**Status:** ✅ DONE · **Ngày xong:** 2026-10-31 · **Ngày tạo:** 2026-10-31 · **Phụ thuộc:** TAXONOMY (b3431a2)
**Nguồn yêu cầu:** chủ dự án, 2026-10-31 — "vùng miền của món ăn hãy giới hạn vào 3 vùng miền
chính nhé, còn nếu món đó thuộc đặc sản của tỉnh thành nào đó thì cho nó vào ghi chú hoặc
thêm 1 dạng phân loại".

---

## Quyết định hình thức

Chủ dự án cho hai lựa chọn: ghi chú tự do **hoặc** thêm một dạng phân loại. Đã chọn
**phân loại** — thêm facet biên tập `dac-san:` vào hệ taxonomy sẵn có: tra cứu/lọc được
như nhãn thường (trang Khám phá, API `?tag=dac-san:hue`), hiển thị thành huy hiệu
"Đặc sản Huế" ở trang món; ghi chú tự do không làm được cả hai việc đó.

## Thay đổi

### Dữ liệu (137 món)

- Mọi món có `region_code` cấp tỉnh → đổi thành miền cha (`vn-north`/`vn-central`/
  `vn-south` theo `regions.parent_id`) và thêm nhãn `dac-san:<tỉnh>` vào `tags`
  trong file. Phân bố: +51 Bắc, +54 Trung, +32 Nam.
- 210 món đang ở mức cả nước (`vn`) **giữ nguyên**: chúng phổ biến toàn quốc,
  ép vào một miền cụ thể là bịa dữ liệu. Sau chuyển đổi: 0 món ở region cấp tỉnh,
  446 món ở 3 miền, 210 món cả nước; 137 món có nhãn đặc sản (Huế 29, Hà Nội 42…).
- Đồng bộ vào DB đang chạy bằng đúng logic của DishSeeder (region + sync nhãn
  gồm nhãn suy) vì DishSeeder toàn phần đang vấp lỗi dữ liệu dở dang của đợt
  LV2 (R6 ở món 22) — không liên quan thay đổi này; lần seed toàn phần tiếp
  theo sẽ cho cùng kết quả từ file.

### Hạ tầng

- `config/taxonomy.php`: đăng ký 14 nhãn `dac-san:` (đủ các tỉnh từng có món).
- Validator V16: file món được mang nhãn biên tập `loai:` (đúng 1) + `dac-san:`
  (tối đa 1); nhãn suy vẫn cấm ghi tay.
- Exporter: xuất ngược cả `dac-san:` — trước đây chỉ giữ `loai:`, chạy export
  sẽ âm thầm xoá nhãn đặc sản khỏi file (đúng lớp lỗi đã gặp ở ALIAS-001).
- API vùng miền công khai + API region của admin: chỉ trả `country` +
  `macro_region`. Admin khi lưu món **chặn** `region_id` cấp tỉnh
  (rule exists kèm điều kiện level).

## Kiểm chứng

- `lacdoi:validate-data`: **1.145 bản ghi, 0 🔴** sau chuyển đổi.
- API thật: `/api/regions` còn đúng 4 mục; bún bò Huế hiện region "Trung Bộ"
  + nhãn `dac-san:hue`; lọc `?tag=dac-san:hue` ra đúng 29 món.
- Test mới `tests/Feature/RegionSpecialtyTest.php` (3 test): API regions
  không còn cấp tỉnh; lọc món theo nhãn đặc sản; admin bị chặn gán tỉnh.
- Ghi chú commit: 11 file món đang có sửa dở của đợt LV2 trong cây làm việc
  cũng đã được áp thay đổi region/nhãn ngay trên đĩa (để dữ liệu nhất quán)
  nhưng không nằm trong commit này — chúng sẽ đi cùng commit của đợt LV2.
