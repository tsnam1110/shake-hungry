# Kế hoạch thu thập dữ liệu dinh dưỡng — đợt 2026-10-31

> Chủ dự án phê duyệt ngày 2026-10-31: thực hiện đến khi đạt các mức ở §2, mức do
> người thực hiện tự xác định, kết quả phải đạt tiêu chuẩn của dự án (QT-1…QT-8,
> DATA-PIPELINE §7). Mọi dữ liệu kết nạp phải nằm trong `data/` của repo — dựng
> lại DB ở bất kỳ máy nào cũng cho cùng kết quả.

## 1. Hiện trạng đầu đợt

- Nguyên liệu có số dinh dưỡng có nguồn: **218/255 (85,5%)**; 37 nguyên liệu
  `kcal = null` (danh sách ở phụ lục A của DISH-001 / truy vấn trực tiếp).
- Món đã soạn tính được: **48/57**; 9 món `unknown` (thiếu nguyên liệu chính có
  số) + 2 món chiên chờ hệ số hấp thụ dầu (nhóm CALC-004).
- 4 món chưa có công thức vì thiếu nguyên liệu cốt lõi trong bộ 255: chả rươi,
  don, mắm rươi, mắm ruột An Giang (con rươi, con don).

## 2. Mục tiêu của đợt (ngưỡng "đủ" do người thực hiện xác định)

| # | Chỉ số | Đầu đợt | Mục tiêu |
|---|---|---|---|
| M1 | Nguyên liệu có số có nguồn | 218/255 (85,5%) | **≥ 95% (≥ 243/255)** |
| M2 | Món đã soạn tính được dinh dưỡng | 48/57 | **57/57 (0 món unknown)** |
| M3 | Món chưa có công thức vì thiếu nguyên liệu | 4 | **≤ 2** (chỉ còn món vướng loài thật sự không có nguồn nào — phải có hồ sơ tra cứu ghi rõ) |
| M4 | Chất lượng | — | validate-data **0 🔴**, Pest **100% đạt**, mọi số đều có `external_id` hoặc URL + thương hiệu đã truy cập thật |

Không đạt bằng cách hạ chuẩn: cấm số từ trí nhớ, cấm suy loài khác cho nguyên
liệu chính, cấm dùng bản đóng hộp siro cho quả tươi, bản pha loãng cho nguyên
chất, món chế biến cho nguyên liệu thô (QT-1, DATA-PIPELINE §7.3).

## 3. Thứ tự nguồn (theo DATA-PIPELINE §7.2 — dùng nguồn cao nhất tra được)

1. USDA FoodData Central: SR Legacy / Foundation (`usda_sr_legacy`,
   `usda_foundation`) — tra qua API chính thức, gồm cả bản **Survey/FNDDS
   2024-10-31** (đã cho kết quả thanh long ở đợt tra 2026-10-31 sáng) và
   `usda_survey`.
2. ANSES-Ciqual (`ciqual`, Licence Ouverte).
3. Canadian Nutrient File (`cnf`, OGL-Canada) — nguồn dự án đã định danh nhưng
   chưa dùng lần nào; đợt này dùng lần đầu nếu tra được.
4. Nhãn dinh dưỡng sản phẩm thương mại bán tại VN (`nhan_san_pham`, medium,
   bắt buộc tên thương hiệu + URL) — qua Open Food Facts và trang nhà sản
   xuất/siêu thị. Đây là đường chính thống cho nhóm đặc thù VN mà §7.2 nêu đích
   danh: nước mắm, mắm tôm, giò lụa, cá lóc…
5. Tự đo (`tu_do`) — ngoài khả năng đợt này (không có mẫu vật), không dùng.

## 4. Phân đợt thu thập

- **Đợt A — USDA FDC:** bì lợn, đuôi bò, nghệ tươi, riềng, ngò gai, tía tô,
  măng khô, mì căn, tàu hũ ky, trứng vịt lộn, hoa hồi, ngũ vị hương, lá chanh,
  rau ngót, rau má, rau cần ta.
- **Đợt B — Nhãn sản phẩm (OFF/trang hãng):** cá lóc, mắm tôm, mắm nêm, bột
  canh, hạt nêm, sa tế, tương bần, hành phi, nước mắm truyền thống (một thương
  hiệu cốt cá cơm làm đại diện, khoảng min–max bao phủ độ đạm của nhóm), rượu
  trắng (nhãn ghi %vol → tính kcal theo §7.2 nguồn 4 + công thức cồn đã ghi
  trong file), cua đồng (sản phẩm mắm cáy/rạm), khảo sát don + rươi.
- **Đợt C — Ciqual + CNF:** bắp chuối, chôm chôm tươi, nghệ tươi, nước mía
  nguyên chất, lươn đồng, rau diếp cá, rau răm, lá lốt, kinh giới, lá dứa,
  thảo quả, riềng.

## 5. Làm sạch trước khi kết nạp (bắt buộc cho mọi dòng)

1. Đối chiếu loài (tên khoa học), bộ phận, trạng thái (sống/chín/khô) với file
   nguyên liệu; lệch trạng thái ⇒ loại hoặc hạ `low` kèm lý do.
2. Kiểm tra chéo kcal bằng công thức Atwater từ đạm/béo/carb — lệch > 15% ⇒
   điều tra lại, không ghi.
3. Nguyên liệu quan trọng tra chéo ≥ 2 nguồn (§7.3.6); lệch > 20% ⇒ ghi cả
   hai vào `mapping_note`, hạ confidence, đặt `kcal_min/kcal_max` bao phủ.
4. Người kết nạp kiểm chứng độc lập lại 100% dòng MATCH bằng cách mở lại
   trang nguồn trước khi ghi file (không ghi theo báo cáo trung gian).

## 6. Kết nạp & nghiệm thu

Ghi vào `data/ingredients/*.json` (khối `reference_food` + `mapping_note` viết
cho người đọc tương lai) → `migrate:fresh --seed` → `recompute-calories
--with-ingredients` → `validate-data` → Pest → đối chiếu bảng §2 → commit kèm
cập nhật tài liệu (QT-7). Nguyên liệu mới (don, rươi) tạo file mới đúng lược
đồ, qua cùng cổng kiểm chứng.

## 6. Kết quả đợt — đóng sổ 2026-10-31

| # | Chỉ số | Đầu đợt | Kết quả | Đạt? |
|---|---|---|---|---|
| M1 | Nguyên liệu gốc có số có nguồn | 218/255 (85,5%) | **242/255 (94,9%)** | 🔶 Suýt đạt (thiếu đúng 1 nguyên liệu so với ngưỡng 243) — xem ghi chú dưới |
| M2 | Món đã soạn tính được (bộ gốc 57) | 48/57 | **54/57** | 🔶 3 món còn lại: bánh rế + bim bim chờ CALC-004 (hệ số dầu), 1 món thuộc nhóm 4 vỏ rỗng cũ |
| M3 | Món thiếu nguyên liệu cốt lõi | 4 | **1** (mắm ruột An Giang — ruột cá lên men không có ở bất kỳ nguồn nào) | ✅ |
| M4 | Chất lượng | — | validate-data **0 🔴** (492 bản ghi), Pest **70/70**, mọi số đều có external_id/URL đã mở | ✅ |
| — | Kho món (chỉ thị riêng của chủ dự án) | 61 món | **200 món**, 176/200 tính được dinh dưỡng | ✅ |
| — | Kho nguyên liệu | 255 | **292** (thêm 37: 29 có số, 1 có số một phần, 7 hồ sơ trống có lý do) | ✅ |
| — | Cờ "chưa kiểm định" (yêu cầu 2026-10-31) | — | 31 nguyên liệu tầng thứ cấp; **46/176 món** tính được mang cờ *(số tại thời điểm đóng đợt — sau mở rộng 300 món + CALC-004, hiện trạng là **73/297**)*; cảnh báo hiển thị ở trang món + thẻ Lắc + danh mục; 4 test riêng | ✅ |

**Ghi chú M1:** 13 nguyên liệu gốc còn trống — bột canh, đuôi bò, hạt nêm, kinh giới, lá chanh, lá dứa, mắm nêm, ngò gai, rau ngổ, rau răm, riềng, thảo quả, tương bần — đều đã tra đủ 3 vòng (chính thống → nhãn → thứ cấp) và có hồ sơ lý do trong `mapping_note` của từng file. Phần lớn là rau thơm/gia vị dùng 1–5g/món (ảnh hưởng calo không đáng kể) hoặc sản phẩm pha chế không nơi nào công bố bảng số (mắm nêm, hạt nêm, bột canh). Không hạ chuẩn để lấp số: nhiều bộ số lưu hành trên web đã bị loại vì sai loài (số "hoa hồi" phổ biến trên web thực ra là hồi hạt Pimpinella anisum — hoa hồi thật Illicium verum cuối cùng lấy được từ nhãn EU), sai trạng thái (bột riềng khô ≠ riềng tươi; măng ngâm ≠ măng khô) hoặc do chính trang bán hàng tự ghi "ước tính".

**Việc chuyển tiếp:**
1. **CALC-004 (22 món chiên) — ✅ ĐÃ XONG 2026-10-31:** chủ dự án duyệt đề xuất; hệ số theo nhóm đã áp cho 71 nguyên liệu, bim bim đã bỏ dòng dầu trùng — chi tiết tại [CALC-004](../05-tasks/tasks/CALC-004.md). Phần dưới là nguyên văn đề xuất lúc chờ duyệt: — toàn bộ số món chưa tính được còn lại (trừ mắm nêm và mắm ruột) đều vướng cổng hệ số hấp thụ dầu. Tài liệu engine (CALORIE-ENGINE §4) đã duyệt trước 2 nguồn (tổng quan PMC10660127; IJFST 43(8):1410) nhưng chỉ cho 4 điểm đo mẫu. Đề xuất chờ chủ dự án chốt: áp **hệ số theo nhóm thực phẩm** trích từ chính tổng quan đó (khoai chiên lát ~ theo "snack khoai", đồ áo bột chiên ngập, hải sản, đậu phụ...) ghi nguồn ở từng nguyên liệu, thay vì bịa hệ số cho từng nguyên liệu riêng lẻ. Riêng bim bim cần quyết thêm: công thức đã kê 8g dầu như một nguyên liệu — phải tránh tính dầu hai lần.
2. **Duyệt của người** (không đổi): toàn bộ công thức món vẫn `llm_suggested`/needs_review — DISH-001/DISH-002 giữ 🔶 chờ chủ dự án duyệt trong admin.
3. **Manh mối mở sẵn cho đợt sau:** nhóm 13 (gia vị/nước chấm) của PDF Bảng TPTP VN gốc chưa trích xuất được text — có thể chứa mục mắm cá sặc/cá linh; đối chiếu bản in cho 2 số natri (hến 86mg, mực khô 445mg) đang để null chờ kiểm.
