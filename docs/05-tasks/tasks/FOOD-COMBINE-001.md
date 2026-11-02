# [FOOD-COMBINE-001] — Lớp thông tin "Kết hợp cần lưu ý"

**Mã task:** FOOD-COMBINE-001 · **Loại:** Tính năng + dữ liệu
**Status:** ✅ ĐỢT 1 XONG (2026-11-02, theo uỷ quyền hoàn thiện) — 23 bản ghi, hiển thị
trang nguyên liệu/quả/món, validator V21, test riêng. **Đợt 2 (FOOD-COMBINE-002)
ĐÃ XONG cùng ngày** theo duyệt "triển khai toàn bộ bản ghi" của chủ dự án:
38 bản ghi tổng — xem [FOOD-COMBINE-002.md](./FOOD-COMBINE-002.md).
**Ngày tạo:** 2026-11-02 · **Hồ sơ nghiên cứu:** `nghien-cuu-cam-ket-hop-2026-11-02.md`
(báo cáo nghiên cứu 49 cặp, thang bằng chứng E0–E3, thiết kế §7 — lưu ở hồ sơ dự án)

---

## Thiết kế đã áp (theo §7 của hồ sơ nghiên cứu)

- **Ba tầng hiển thị, suy ra từ `evidence_class` bằng hàm thuần — không lưu tay:**
  Tầng 1 "Tránh kết hợp" (E1) · Tầng 2 "Không nên kết hợp" (E2, bắt buộc có
  `condition_vi`) · Tầng 3 "Kiêng kỵ theo dân gian / đông y" (E3, bắt buộc có nguồn
  truyền thống + trạng thái khoa học; cặp đã bác ghi rõ "không cần kiêng").
  **Không dùng chữ "Cấm" làm tên tầng** (chỉ trích nguyên văn "chống chỉ định" trong
  nội dung nhãn thuốc khi nhãn đặt ở mục Contraindications).
- **Dữ liệu:** một file JSON một cặp trong `data/combinations/` (QT-3), dựng vào bảng
  `combinations` qua `CombinationSeeder`. Phía của cặp: `ingredient` / `fruit` /
  `food_group` (có `members` để gắn trang) / `drug_group` (chỉ có nhãn, không có trang).
- **Hiển thị:** trang nguyên liệu + trang quả (mục "Kết hợp cần lưu ý", hai chiều từ
  một bản ghi); trang món — cặp tầng 1–2 mà món chứa cả hai phía hiện ở khối thông tin,
  cặp tầng 3 nằm trong mục thu gọn "Kiêng kỵ dân gian về món này". Tuyến thuốc × thực
  phẩm chỉ ở trang nguyên liệu/quả, **không gắn vào món/Lắc**. Mọi nơi hiển thị kèm
  dòng miễn trừ cố định (`CombinationDisplay::DISCLAIMER`).
- **Validator V21** (`lacdoi:validate-data`): schema, slug phía thực phẩm tồn tại,
  member tồn tại, khớp class ↔ science_status, E2 phải có điều kiện, E3 phải có
  nguồn truyền thống, refuted phải có nguồn bác, chống ghi trùng một cặp hai bản ghi.

## Nguyên tắc đỏ (không được phá ở mọi đợt sau)

1. Chỉ là lớp THÔNG TIN: không lọc cứng, không chặn Lắc, không loại/trừ điểm món,
   không dùng khung/màu cảnh báo dị ứng. (Đối lập có chủ đích với lọc dị ứng.)
2. Không tư vấn y tế cá nhân — chỉ khung trích dẫn ("Theo nhãn thuốc FDA…",
   "Theo đông y…", "Thí nghiệm năm 1935 cho thấy…") + dòng miễn trừ cố định.
3. Mỗi bản ghi có nguồn đã mở kiểm chứng (URL + ngày truy cập) và `needs_review`
   như mọi dữ kiện biên soạn (QT-1) — đợt 1 đang `needs_review=true`, chờ chủ dự án duyệt.
4. Tầng 3 luôn kèm trạng thái khoa học; đã bác thì chốt "không cần kiêng".
5. Tuyến D (an toàn thực phẩm: tiết canh, hải sản sống) KHÔNG vào bảng này.
6. Không nhập các bảng "tương khắc" có sẵn trên mạng (QT-2) — mỗi cặp phải truy
   được tới nguồn gốc cụ thể như hồ sơ nghiên cứu đã làm.

## Phân xử các điểm §7.7 (uỷ quyền hoàn thiện, chủ dự án có quyền phủ quyết)

1. Tên tầng 1: **"Tránh kết hợp"** theo đề xuất hồ sơ (không dùng "Cấm").
2. Tuyến thuốc × thực phẩm: **có** đưa vào — đó là nơi duy nhất có bằng chứng E1
   thật; trình bày tách bạch, không cá nhân hoá (app không thu thập thông tin thuốc).
3. Tầng 3 ở trang món: **mặc định thu gọn** theo đề xuất.
4. Phạm vi đợt 1: theo §7.6 (~20) — thực tế **23 bản ghi** vì tách statin theo từng
   thuốc (đúng nguyên tắc "không gộp nhóm thuốc") và tách levothyroxin thành hai
   phía thực phẩm riêng cho sạch schema.

## Đợt 1 — 23 bản ghi

- **Tầng 2 thực phẩm (5):** cua + hồng giòn (điều kiện ở hồng chát); trà + sắt
  non-heme; cà phê + sắt non-heme; trứng + trà; gan lợn + giá đỗ (hao vitamin C
  khi nấu chung lâu).
- **Thuốc × thực phẩm (10):** bưởi × simvastatin / atorvastatin / chẹn kênh canxi;
  rau lá xanh × warfarin ("giữ ổn định"); sữa–canxi × tetracyclin + ciprofloxacin
  (cách giờ, mỗi thuốc một quy tắc); canxi × levothyroxin (≥4 giờ); đậu nành ×
  levothyroxin; cà phê × ciprofloxacin; rượu × benzodiazepine + paracetamol;
  rượu × metronidazol (E2 — kèm ghi chú bằng chứng đang tranh luận).
- **Tầng 3 (8):** cua + cà chua (đã bác), rau bina + đậu phụ (đã bác — cơ chế
  ngược), trứng + sữa đậu nành (đã bác ở dạng đã nấu), mật ong + hành (đã bác
  1935), cá chép + thịt gà (cổ thư thật), sữa + cá (hai truyền thống độc lập),
  thịt gà + kinh giới (dân gian VN), trứng + tỏi (Âm Thiện — điều kiện "tỏi sống").
- **Hoãn có hồ sơ:** MAOI × tyramin và cam thảo × thuốc — phía thực phẩm chưa có
  slug/nhóm chuẩn hoá trong kho (hồ sơ §6); không ép dữ liệu.

## Kiểm chứng đợt 1

- `lacdoi:validate-data`: 3.365 bản ghi (gồm 23 cặp), **0 🔴** — V21 sạch ngay lần đầu.
- API thật trên DB dev: `/api/fruits/buoi` trả 3 cặp tầng 1; `/api/ingredients/gan-lon`
  trả cặp tầng 2; `/api/dishes/bun-rieu-cua` → folk chứa cua–cà chua, notes rỗng;
  `/api/dishes/hu-tieu` → notes chứa gan–giá đỗ.
- Test: `tests/Feature/CombinationTest.php` — tầng suy ra, hiển thị hai chiều,
  món notes/folk, và bất biến "không lọc món khỏi catalog + không gắn tuyến thuốc
  vào món".
