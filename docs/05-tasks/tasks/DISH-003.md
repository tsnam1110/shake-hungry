# [DISH-003] — Lên 300 món

**Mã task:** DISH-003 · **Giai đoạn:** GĐ3 · **Loại:** Dữ liệu
**Status:** 🔶 ĐANG LÀM (mốc số lượng đã đạt 2026-10-31, chờ người duyệt công thức) · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** DISH-002

---

## Mục tiêu

Mở rộng chiều sâu vùng miền và món ít phổ biến hơn.

## Việc cần làm

- Món đặc trưng theo tỉnh (Huế, Nam Định, Hội An, miền Tây…)
- Món chay
- Món ăn vặt
- Món theo mùa

Đến mốc này, chạy [QA-002](./QA-002.md) — đo độ chính xác calo lần đầu, khi đã đủ mẫu để có ý nghĩa.

## Definition of Done

- [x] 300 món trong kho (draft, chờ duyệt — cùng trạng thái với DISH-001/002)
- [x] Có ≥20 món chay — 66 món `diet:chay` + 14 `diet:chay-trung-sua` theo nhãn suy từ thành phần (TAXONOMY.md)
- [x] Có ≥10 tỉnh/thành có món đặc trưng riêng (14 mã vùng cụ thể đang có món: Hà Nội, Nam Định, Hải Phòng, Thanh Hoá, Nghệ An, Huế, Đà Nẵng, Quảng Nam, TP.HCM, Tiền Giang, An Giang, Cần Thơ, Sóc Trăng, Vũng Tàu)
- [ ] QA-002 đã chạy, kết quả ghi vào [ACCURACY-SPEC §6](../../06-nutrition/ACCURACY-SPEC.md)
- [ ] QA-001 chạy đều mỗi 50 món

## Cạm bẫy

- Đây là quãng dài và nhàm nhất của dự án. Chia nhỏ mục tiêu theo tuần và tự theo dõi tiến độ.
- Nếu QA-002 không đạt ngưỡng, **dừng nhập món và sửa engine trước** — nhập thêm 200 món với engine
  sai chỉ tạo thêm 200 con số sai.

## Ghi chú

## Ghi chú

**Bổ sung 2026-10-31 — mốc 300 món đã đạt:** theo chỉ thị của chủ dự án (tiếp tục mở rộng + phân loại/nhãn), kho món lên **300 món** và kho nguyên liệu lên **313**. 100 món mới chọn có chủ đích để lấp nhóm mỏng theo phân loại mới: canh 2→15, cháo/súp 2→12, lẩu 3→10, gỏi/nộm 7→16, cơm 7→9, món chính +28 (thịt đông, vịt om sấu, heo quay, bò nhúng dấm...). Mọi món mới mang nhãn `loai:` ngay từ khi soạn; nhãn diet/protein/allergen suy tự động. **269/300 món tính được dinh dưỡng**; 30 món chưa tính được gồm 28 món chiên chờ CALC-004, gỏi ngó sen (ngó sen không có bảng dinh dưỡng đạt chuẩn ở mọi tầng nguồn — số đang lưu hành trên web là của củ sen, khác bộ phận), mắm nêm và mắm ruột An Giang (treo từ trước). 21 nguyên liệu mới đã tra cứu theo thang nguồn §7.2: 5 MATCH tầng 1 (khoai mỡ, vỏ hoành thánh, nui khô, hạnh nhân, cá diêu hồng), 3 nguồn thứ cấp có cờ chưa kiểm định (cá nục FNRI, cá đuối FNRI, cá rô đồng + quả sấu Bảng VN), các NEAR đều ghi rõ khác biệt trong mapping_note (ghẹ→blue crab có đối chứng văn liệu gần như trùng khớp; bò khô→jerky định hình Mỹ béo hơn hẳn hàng VN — cần thay khi có nhãn VN), 2 mục để null có hồ sơ (rau đắng đất, ngó sen). Trạng thái giữ 🔶 vì toàn bộ công thức mới vẫn là `llm_suggested` chờ người duyệt. QA-002 (đo độ chính xác calo) chưa chạy — là việc riêng cần mẫu kiểm chứng, không thuộc đợt này.

**Duyệt của chủ dự án 2026-10-31:** chủ dự án đã duyệt toàn bộ công thức trong kho
(300 món) qua quyết định trực tiếp tại phiên làm việc — toàn bộ file món đã ghi
`needs_review: false`, `reviewed_by`/`reviewed_at`/`review_note` theo đúng cơ chế
duyệt của admin. Rào cản "chờ người duyệt" của task này coi như đã gỡ; task giữ 🔶
chỉ vì các mục Definition of Done khác chưa thuộc đợt này (QA, dùng thử, đo thời gian nhập).
