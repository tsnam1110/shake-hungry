# FRUIT-004 — Thu thập giá mùa vụ 3 miền × 12 tháng

- **Trạng thái:** ✅ (2026-10-31 — **1.387 dòng giá** có nguồn cho 78/82 hồ sơ; validate 0 lỗi 🔴)
- Độ phủ: quả phổ biến đủ 3 miền × tháng có hàng theo mô hình 2 mức (vào vụ/trái vụ); quả đặc sản vùng
  trồng chỉ có miền có nguồn đáng tin; hàng nhập dùng dải siêu thị toàn quốc ghi rõ trong source_name.
- 4 hồ sơ để trống giá có lý do: bưởi chùm (không giá lẻ nhập đáng tin), dưa gang (chỉ có giá hàng
  muối), mơ nhập (không giá lẻ công bố), quả điều (giá công bố là của hạt, không phải quả).
- Đối chiếu cửa sổ giá bằng dữ liệu thật đã sửa 10 cửa sổ `best_value_window` (bòn bon, dừa, hồng
  giòn, đào, táo mèo, sầu riêng, nhãn, mít tố nữ, mãng cầu, quýt), sửa mùa vụ dưa bở (8–10 → 4–6,
  hồ sơ đầu ghi sai) + mở mùa bình bát, giữ cửa sổ mít chín kèm ghi chú năm bất thường 2026.
- Mọi dòng giá: source_name + source_url (khi có) + collected_at 2026-10-31; giá là khoảng tham
  khảo mùa vụ theo FRUIT-MODULE §3, không phải giá theo ngày.
- **Phụ thuộc:** FRUIT-003
- **Nhánh:** Hoa quả (FRUIT-MODULE)

Khoảng giá tham khảo VND/kg theo miền (bac/trung/nam) × tháng cho ~110 quả; mỗi dòng giá có `source_name` + `collected_at` (FRUIT-MODULE.md §3). Nguồn ưu tiên: bản tin giá Sở Công Thương, chợ đầu mối, báo cáo thị trường Bộ NN&PTNT, giá siêu thị.
