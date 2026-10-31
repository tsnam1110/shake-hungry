# FRUIT-005 — Module quản trị /admin/hoa-qua

- **Trạng thái:** ✅ (2026-10-31 — Admin\FruitController REST + review, mục "Hoa quả" trong SPA,
  exporter xuất ngược data/fruits/*.json dạng compact tất định; 5 test FruitPagesTest đạt)
- Form gồm: phân loại/nguồn gốc/kiểu mùa, 3 lưới chọn tháng (có hàng/vụ chính/2 cửa sổ) có chặn
  V17 phía server, vùng trồng, lưu ý kết hợp kèm mức bằng chứng, và danh sách dòng giá
  (miền × tháng × khoảng giá × nguồn × ngày thu thập — thiếu nguồn là 422).
- **Phụ thuộc:** FRUIT-001
- **Nhánh:** Hoa quả (FRUIT-MODULE)

Mục Hoa quả riêng trong SPA admin (antd 6 + React 19): danh sách + lọc, form hồ sơ (lưới 12 tháng cho mùa vụ), ma trận giá miền × tháng có cảnh báo thiếu nguồn/lệch bất thường, quy trình duyệt `needs_review` như kho món.
