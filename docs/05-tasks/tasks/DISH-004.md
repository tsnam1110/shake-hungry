# DISH-004 — Lên 500 món — mốc đủ dùng

- **Trạng thái:** 🔶 (2026-10-31 — đã đạt 500 món, tích hợp + kiểm chứng xong; chờ chủ dự án duyệt công thức như các đợt trước)
- **Phụ thuộc:** DISH-003 ✅
- **Bối cảnh:** Chủ dự án đặt câu hỏi về độ phủ nguyên liệu (300 món / 313 nguyên liệu) ngày
  2026-10-31. Kiểm chứng: phân bố tần suất đúng cấu trúc "nòng cốt + đuôi dài" (35 nguyên liệu
  dùng cho >20 món; 2.557 dòng / 300 món), nhưng rà theo tên phát hiện thiếu hụt thật ở đuôi dài:
  cá trắm/trôi/mè, tôm thẻ/tôm càng xanh, mắc khén/hạt dổi, chuối xanh, khế, quất, mít non,
  lòng lợn... Chủ dự án **xác nhận mở rộng kho dữ liệu** cùng ngày → task này.

## Đã làm (2026-10-31, 2 đợt soạn + 2 vòng tra cứu nguyên liệu)

- **Đợt 1 (+100 món):** canh +20, món chính +20, lẩu +10, gỏi/nộm +10, bún-phở-mì +12,
  bánh +8, chè +6, cơm +5, cháo-súp +3, xôi +3, nước chấm +3 — ưu tiên nhóm mỏng và các món
  dùng nguyên liệu còn thiếu.
- **Đợt 2 (+100 món):** món chính +20, bánh +20, bún-phở-mì +12, canh +8, chè +10, xôi +5,
  nước chấm +5, cơm +8, cháo-súp +6, gỏi +6.
- **+20 nguyên liệu mới** (313 → 333): 17 có số — cá trắm 91 / cá trôi 127 / cá mè 144
  (Bảng TPTP VN 08029/08032/08014), cá bống (FNRI G061), cá chim (Bảng VN), cá linh (văn liệu
  JIRCAS), cá đối (SR 175123, high), cá khô (SR cod khô muối, low + cảnh báo mạnh), tôm thẻ +
  tôm càng xanh (SR 175179), lòng lợn (SR chitterlings), khế (SR 171715), quất (SR 168154),
  chuối xanh (SR plantain xanh), mít non (FNRI D104), thịt ngan (SR vịt nhà, medium),
  khoai sọ (SR taro); 3 để null có hồ sơ: mắc khén, hạt dổi (đính chính: *Michelia
  tonkinensis*, không phải Amomum), rau nhút (đính chính: *Neptunia oleracea*).
- **Sửa chất lượng kèm theo:** 2 món om chuối đậu chuyển chuối chín → chuối xanh;
  `canh-khoai-so` (trùng "canh khoai sọ nấu sườn") viết lại thành biến thể thịt băm dùng
  khoai sọ thật; phân biệt tên EN 4 cặp suýt trùng (V7); món "cốm" đặt tên đầy đủ
  "Cốm làng Vòng"; bổ sung hệ số dầu CALC-004 cho cá kèo/cá rô đồng/cua biển (9%),
  thịt bò xay (8%), dưa cải muối (0%); TaggingRules thêm state:kho cho mắc khén/hạt dổi
  và allergen:tom cho 2 tôm mới.

## Kiểm chứng

- validate-data: **0 🔴** / 26 🟡 (đa số là V9 cho nhóm nước chấm khẩu phần nhỏ — đặc tính
  nhóm, không phải lỗi số liệu); Pest **77/77**.
- **495/500 món tính được dinh dưỡng**; 5 món chưa tính được đều do thiếu dữ liệu thật:
  gỏi ngó sen, canh rau nhút tôm thẻ (nguyên liệu chính để null có hồ sơ), mắm nêm,
  mắm nêm pha sẵn, mắm ruột An Giang.
- 134/495 món mang cờ "chưa kiểm định" (nguyên liệu mới đa phần từ nguồn tầng 6 — hiển thị
  minh bạch theo cơ chế sẵn có).
- Phân bố diet: 105 chay / 20 chay-trứng-sữa / 375 mặn; kiểm tra đối kháng: 0 món chay
  chứa mắm/thịt. Phân bố loai cân hơn hẳn mốc 300: canh 15→43, lẩu 10→20, cơm 9→22.
- Nguyên liệu có số: **306/333**; 51 nguyên liệu chưa món nào dùng (dự trữ đã tra cứu).

## Còn lại để đóng task

- Chủ dự án duyệt 200 công thức mới (hiện `needs_review=true`) — như đợt duyệt 2026-10-31.
- QA-001 chạy rà soát kỳ đầu cho mốc 500; QA-002 đo độ chính xác.
- Dài hạn: tách `ca-kho` thành khô theo loài (khô cá lóc, khô cá sặc...) — khuyến nghị đã
  ghi trong mapping_note của file.
