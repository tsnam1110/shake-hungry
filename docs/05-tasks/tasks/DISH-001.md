# [DISH-001] — 50 món đầu tiên

**Mã task:** DISH-001 · **Giai đoạn:** GĐ3 · **Loại:** Dữ liệu
**Status:** 🔶 ĐANG LÀM (đợt soạn thảo xong, chờ người duyệt) · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** ADM-007

---

## Mục tiêu

Mẻ món đầu tiên. Ngoài việc tạo dữ liệu, mẻ này còn để **kiểm chứng quy trình trong thực tế** trước
khi tăng tốc.

## Việc cần làm

Chọn 50 món phổ biến nhất, cân đối:

- Theo bữa: sáng ~15, trưa ~20, tối ~15
- Theo hình thức: ăn ngoài ~30, tự nấu ~20 (nhiều món thuộc cả hai)
- Theo vùng: Bắc / Trung / Nam đều có
- Theo chế biến: **bắt buộc có món chiên/rán** — để phát hiện sớm lỗi hệ số dầu

Món ăn ngoài dừng ở Mức 1. Món tự nấu làm tới Mức 2.

## Definition of Done

- [ ] 50 món, tất cả `status = 'published'`, không còn `needs_review`
- [ ] **Món Mức 2 đều có ≥3 `recipe_sources`** (ràng buộc R8) — xem
      [RESEARCH-PROTOCOL §11](../../09-standards/RESEARCH-PROTOCOL.md)
- [ ] **`recipe_steps` viết bằng lời của mình**, không chép từ nguồn nào
- [ ] Mọi `ingredient_slug` trỏ tới nguyên liệu có thật (ràng buộc R9)
- [ ] Món Mức 2 đều có calo tính được (hoặc `missing_ingredients` được giải thích)
- [ ] `lacdoi:validate-data` sạch lỗi 🔴
- [ ] `lacdoi:export-data` → commit → `migrate:fresh --seed` dựng lại đủ 50 món
- [ ] Đã chạy rà soát chất lượng [QA-001](./QA-001.md) lần đầu
- [ ] Thời gian trung bình mỗi món ghi vào Ghi chú (đối chiếu với ADM-007)

## Cạm bẫy

- **Mẻ đầu là lúc phát hiện sai sót có hệ thống** — nhầm đơn vị nguyên liệu hay dùng, hệ số dầu sai,
  quy tắc đặt tên không nhất quán. Dừng lại sửa gốc ngay, đừng để lan sang 450 món sau.
- Nếu thời gian thực tế cao hơn ADM-007 nhiều, quay lại sửa công cụ chứ đừng cắn răng nhập tiếp.

## Ghi chú

---

## Tiến độ đợt 1 (2026-10-30) — soạn thảo bằng AI, chờ người duyệt

Toàn bộ 61 món vỏ Wikidata đã được xử lý trong `data/dishes/vn/`:

- **57 món có định lượng** (285 dòng nguyên liệu): vùng miền, cách chế biến, bữa
  ăn, khẩu phần, các bước nấu cơ bản. Mọi dòng đều `source=llm_suggested`,
  `confidence=low`, món giữ `needs_review=true` — đây là **bản nháp chờ biên
  tập viên duyệt trong trang quản trị**, đúng quy trình QT-1 (máy không tự duyệt).
- **48/57 món tính được dinh dưỡng** bằng engine (`lacdoi:recompute-calories
  --with-ingredients` — cờ mới cho dữ liệu nháp); khoảng kcal ra hợp lý khi đối
  chiếu thường thức (bún chả ≈372–689, phở gà ≈232–430, sườn nướng ≈312–506).
- **9 món engine từ chối tính** (confidence=unknown, QT-5): bánh rế, bim bim,
  chả cá Lã Vọng, khô cá lóc, mắm cá lóc, mắm cáy, mắm chua, mắm nêm, nộm hoa
  chuối — vì nguyên liệu chính (cá lóc, khoai môn, cua đồng, bắp chuối, mắm nêm…)
  chưa có dữ liệu dinh dưỡng trong bảng tham chiếu. Đây là việc của nhóm task
  ING (bổ sung ánh xạ có nguồn), không tự chế số.
- **4 món chưa soạn được** và đã ghi rõ lý do trong `import_note` của từng file:
  chả rươi, don, mắm rươi, mắm ruột An Giang — thiếu nguyên liệu cốt lõi
  (con rươi, con don) trong bộ 255.
- **Mức hoàn thiện giữ ở 1** cho cả 57 món theo cổng V12: Mức 2 đòi ≥3
  `recipe_sources` đã kiểm chứng/món. Không tự tạo liên kết nguồn; khi biên tập
  viên tra cứu nguồn thật và nhập vào, món mới lên Mức 2.
- Cảnh báo V9 cho 5 mục nước mắm là dương tính giả theo thiết kế khoảng
  50–2000 kcal: khẩu phần gia vị 15g cho ≈8 kcal là đúng thực tế.

**Bổ sung 2026-10-31 — tra lại nguồn cho nhóm nguyên liệu chưa có số (theo yêu cầu chủ dự án: kiến thức phải chính thức, nằm trong repo, dùng lại khi golive/chuyển máy):** tra trực tiếp USDA FoodData Central (API, mọi dataType gồm bản Survey/FNDDS phát hành 2024-10-31 — bản này ra SAU đợt biên soạn gốc nên có dòng mới):
- ** thanh-long: CÓ NGUỒN MỚI** — "Dragon fruit" FNDDS FDC 2709234 (68 kcal/100g) → đã điền `reference_food` (fct_source `usda_survey`, tin cậy medium). Hai món dùng thanh long (banh-mi-thanh-long, mi-thanh-long) tăng độ tin cậy low → medium sau tính lại.
- Tra lại vẫn KHÔNG có nguồn đạt chuẩn, giữ null và đã ghi phụ chú ngày 2026-10-31 vào `mapping_note` từng file: ca-loc (FDC mọi dataType 0 kết quả "snakehead"), bap-chuoi (chỉ có hoa bí/hoa điên điển — khác loài), mam-tom (Branded chỉ có bagoong xào Philippines), chom-chom (chỉ có bản đóng hộp siro), nuoc-mia (Branded GOYA pha loãng 35%).
- Các kết luận LOẠI của đợt gốc được xác nhận lại là đúng và giữ nguyên: luon ≠ Anguilla (khác bộ), tuong-ban ≠ miso (tương loãng hơn nhiều), cua-dong (đã quét USDA/Ciqual/MEXT), ruou-trang ≠ sake (chênh độ cồn có hệ thống).
- Sau nạp lại: validate-data 0 🔴, 66/66 test đạt; số món tính được giữ nguyên 48 (đợt này nâng CHẤT LƯỢNG số của 2 món, không tăng số lượng — các món còn "chưa đủ dữ liệu" đều vướng nguyên liệu đặc thù VN chưa có bảng license sạch, đúng kết luận LEGAL-001).

**Bổ sung 2026-10-31 (đợt thu thập dữ liệu + mở rộng kho món, theo lệnh chủ dự án):**
- Đợt thu thập 3 vòng (chính thống → nhãn sản phẩm → nguồn thứ cấp có đánh dấu chưa kiểm định) đã lấp số cho hầu hết nguyên liệu từng chặn các món của task này: cá lóc, mắm tôm, nước mắm truyền thống... Sau tính lại: **54/57 món đã soạn tính được dinh dưỡng** (đầu đợt 48). Ba món còn lại: bánh rế và bim bim chờ hệ số hấp thụ dầu (CALC-004), mắm ruột An Giang vẫn là vỏ rỗng (nguyên liệu ruột cá lên men không có ở bất kỳ nguồn nào đã tra).
- Ba món từng bị chặn vì thiếu nguyên liệu cốt lõi đã được soạn công thức: chả rươi, don, mắm rươi (rươi/don có dữ liệu nguồn thứ cấp — món mang cờ "chưa kiểm định"). Chả rươi là món chiên nên phần dinh dưỡng vẫn chờ CALC-004.
- Trạng thái task giữ 🔶: toàn bộ định lượng vẫn là `llm_suggested`, chờ người duyệt trong admin.
