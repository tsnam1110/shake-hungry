# AUDIT-ALLERGEN — Rà soát census toàn bộ thông tin dị ứng + nguyên nhân gốc

**Trạng thái:** ✅ HOÀN TẤT phần rà soát (2026-11-01) — **không sửa dữ liệu/code trong đợt**; đầu ra: 4 mục sửa cơ chế FIX-A1…A4 + 4 mục chờ chủ dự án quyết chính sách ALG-Q1…Q4 (báo cáo §7).
**Phụ thuộc:** TCM-PROMOTE-002 (HEAD `665c327`; kho 654 món / 434 nguyên liệu / 82 quả).
**Căn cứ:** chủ dự án giao trực tiếp 2026-11-01 ~16:50, chỉ đạo chất lượng cao nhất, không giới hạn thời gian. Phát hiện mở đầu của chủ dự án: *rất nhiều món không có cá vẫn ghi dị ứng cá* — yêu cầu rà toàn bộ nhóm dị ứng trên mọi sản phẩm + tìm nguyên nhân gốc.
**Báo cáo đầy đủ:** `~/workspace/goals/l-c-i-shake-hungry-development/files/bao-cao-audit-allergen-2026-11-01.md`.

---

## 1. Bao phủ & phương pháp

654/654 món · 434/434 nguyên liệu · 82/82 quả · 11/11 nhóm dị ứng. Hai phương pháp độc lập khớp tuyệt đối: (1) PHP dùng class thật (`TaggingRules`, `DishTagDeriver`) trên DB kiểm chứng riêng `shake_hungry_allergen` seed mới; (2) Python tính lại từ file thô. Rò rỉ lọc đo bằng chính `Recommender::pool()`. Mọi ca nghi ngờ đều mở hồ sơ nguyên liệu; phát hiện 🔴 qua kiểm chứng lượt 2 độc lập mới kết luận. Validate trên DB kiểm chứng: **0 🔴**.

## 2. Kết quả chính

- **Tầng cơ học sạch tuyệt đối:** file ↔ luật 0 lệch (434 NL), nhãn DB ↔ bộ suy 0 lệch (654 món). Không có trôi dữ liệu; mọi vấn đề nằm ở luật/chính sách/preset.
- **Cờ cá (câu hỏi của chủ dự án) — phân rã 388 món có cờ:** 109 món có cá thật · **274 món chỉ vì nước mắm/mắm cá** (nước mắm truyền thống xuất hiện ở 358 món; 62 món nhóm này chỉ dùng ≤5 g mắm) · 5 món chỉ vì giò/chả theo quy ước thành phần điển hình · **0 món không giải thích được**. Cơ chế = **chính sách đã ghi văn bản** (TAXONOMY §4: nhóm cá gồm nước mắm + mắm cá) + bộ suy hợp nhất không trọng lượng + badge hiển thị đúng một chữ "Cá". Không phải lỗi code/dữ liệu → **ALG-Q1 chờ quyết**.
- **🔴 PHÁT HIỆN AN TOÀN CHÍNH — rò rỉ ở tầng lọc Lắc:** lọc cứng giao slug nguyên liệu với **preset slug viết tay** ở `shake.blade.php`/`profile.blade.php` (giống hệt nhau, đóng băng từ REC-001, không cơ chế đồng bộ với `TaggingRules`):
  - **Không có chip cho Cá (388 món có cờ), Động vật có vỏ (56), Mè (53)** — người dị ứng cá không khai báo được.
  - Chọn preset vẫn lọt (đo `pool()` thật): **đậu nành 36 món, tôm 22 (gồm `banh-canh-tom-cang`), lúa mì 11, mắm tôm 9, trứng 7, sữa 5, cua 3**; đậu phộng 0. Preset thiếu cả `nuoc-tuong` (nguồn cờ đậu-nành lớn nhất, 70 món).
  - Đối chứng: dùng toàn bộ slug có nhãn → rò rỉ **0** mọi nhóm; preset ⊆ nhãn 100% → 0 loại oan. Cơ chế lọc + nhãn đúng; chỉ preset sai/thiếu.
  - Lọc ở Thư viện (`allergen_free` theo nhãn) đúng đủ 11 nhóm — lệch chỉ ở Lắc/Hồ sơ.
- **Census nguyên liệu (120 dòng gán / 109 NL):** gắn thừa sai định danh **0** (mọi nhãn lạ đều là chính sách đã ghi: giò/chả → cá, xúc xích, pate gan, bia, bò viên…). **Gắn thiếu xác nhận duy nhất: `cha-bong`** — hồ sơ tự ghi thành phần có nước mắm + `components` liệt kê `nuoc-mam-truyen-thong` nhưng bảng luật không gắn `ca`; hiện **0 món** dùng cha-bong nên tác động = trang nguyên liệu + rủi ro tiềm ẩn.
- **🟡 chờ phân xử:** `sa-te` (logic chay coi là động vật "vì tôm khô" nhưng bảng dị ứng im lặng, hồ sơ ghi dầu+ớt+sả; 3/6 món dùng sa-te không có cờ tôm); `sua-bien` không nhãn (sứa — ngoài các nhóm chuẩn quốc tế); **hạt cây ngoài từ vựng** (`hat-dieu-rang`, `hanh-nhan` không nhãn — TAXONOMY §4 đã ghi nhận, chờ quyết có thêm nhóm không); cờ chỉ từ dòng tuỳ chọn 49 cặp (mè 16 — vừng rắc). `mam-ruot-an-giang` là bản nháp Wikidata 0 dòng nguyên liệu (hàng đợi biên soạn sẵn có). Quả: 0/82, không phát hiện.

## 3. Đề xuất khắc phục (chưa thực thi — chờ duyệt)

- **FIX-A1 (ưu tiên 1):** sinh danh sách loại trừ từ `TaggingRules` thay preset viết tay (dùng chung Lắc + Hồ sơ), bổ sung 3 chip Cá/Động vật có vỏ/Mè, test Pest khoá lệch preset ↔ luật.
- **FIX-A2:** `'cha-bong' => ['ca']` vào `ALLERGENS_BY_SLUG` + đồng bộ tags file.
- **FIX-A3:** hardening luật tiền tố `ca-` (test liệt kê slug `ca-` ngoài thuỷ sản / thuỷ sản không cờ, hoặc bảng tường minh hoàn toàn).
- **FIX-A4:** sửa câu chữ TAXONOMY §4 về mắm nêm ở facet mắm tôm.

## 4. Chờ chủ dự án quyết (ALG-Q)

> **ĐÃ QUYẾT 2026-11-02** — chủ dự án duyệt toàn bộ khuyến nghị; ALG-Q1…Q4
> triển khai thành QD-01…QD-05, xem §7. Mục này giữ nguyên văn để tra cứu.

- **ALG-Q1:** cờ cá cho 274 món chỉ có nước mắm/mắm cá — (1) giữ nguyên; (2) giữ cờ, đổi hiển thị tách nghĩa "Cá / nước mắm" *(đợt rà khuyến nghị — nước mắm chứa đạm cá, chuẩn ghi nhãn EU/FDA vẫn xếp nhóm cá; bỏ cờ không an toàn)*; (3) tách facet hiển thị riêng, lọc vẫn gộp.
- **ALG-Q2:** cờ chỉ từ dòng tuỳ chọn có hiển thị phân biệt "có thể có" không (lọc giữ chiều an toàn).
- **ALG-Q3:** thêm nhóm "Hạt cây" vào từ vựng dị ứng?
- **ALG-Q4:** `sa-te` gắn `tom`? · `sua-bien` vào nhóm động-vật-có-vỏ?

## 5. Kiểm chứng cuối

Census lượt cuối chạy lại toàn bộ sau sự cố VM thay mới giữa đợt (khôi phục PHP/MySQL, seed lại DB kiểm chứng): 434/654/82, 0 lệch cơ học, validate 0 🔴, số liệu hai phương pháp khớp nhau. Không chạy Pest (không cần cho kết luận + tránh tranh tiến trình với AUDIT-003/AUDIT-004 song song). Không seed DB dev, không chạm 7 file món dở của phiên song song. Script rà lưu tại `~/workspace/goals/l-c-i-shake-hungry-development/hidden_files/audit-allergen/` (census.php, census_files.py, analyze.py, identity_scan.py, preset_gap.py, pool_check.php).

## 6. Khắc phục ALLERGEN-FIX — ĐÃ XONG (2026-11-01)

Chỉ các mục đã kết luận; ALG-Q1…Q4 giữ nguyên chờ chủ dự án.

- **FIX-A1 ✅** — Xoá preset slug viết tay ở `shake.blade.php`/`profile.blade.php`;
  thêm `App\Taxonomy\AllergenPreset` sinh danh sách loại trừ **từ `TaggingRules`**
  trên toàn bộ catalog nguyên liệu (nguồn sự thật duy nhất; thứ tự + tên chip lấy
  từ `config/taxonomy.php` — trùng tên badge "Dị ứng: ..." ở trang chi tiết).
  Cả hai trang nay hiển thị **đủ 11 chip** (có thêm Cá, Động vật có vỏ, Mè) và mọi
  slug có nhãn (nhóm đậu nành 12 slug gồm `nuoc-tuong`; tôm 12 gồm
  `tom-cang-xanh`; cua có `ghe`...). Cơ chế preset của hồ sơ
  (`excluded_ingredients`) và cách `Recommender` nhận danh sách slug giữ nguyên —
  chỉ thay NGUỒN danh sách. Khoá bằng `tests/Feature/AllergenExclusionTest.php`:
  preset sinh ra ≡ tập slug có nhãn theo `TaggingRules` cho từng nhóm (chệch là
  test đỏ) và hai trang nhúng đúng danh sách đó.
- **Đo lại rò rỉ (mục tiêu của §5):** test hành vi chạy `Recommender::pool()` thật
  trên toàn kho đã seed — với danh sách sinh ra, **0 món mang cờ của nhóm còn lọt
  cho cả 11 nhóm** (trước: đậu nành 36, tôm 22, lúa mì 11, mắm tôm 9, trứng 7,
  sữa 5, cua 3; nhóm Cá/Động vật có vỏ/Mè trước đó không chọn được). Loại oan = 0
  theo cấu trúc (preset = đúng tập slug có nhãn; kiểm đối chứng ở đợt rà: danh
  sách đầy đủ cho rò 0 và preset ⊆ nhãn). Ca hồi quy `banh-canh-tom-cang` bị
  loại đúng khi chọn Tôm.
- **FIX-A2 ✅** — `cha-bong` thêm `['ca']` vào `ALLERGENS_BY_SLUG` + `allergen:ca`
  vào tags của `data/ingredients/cha-bong.json` (đúng thứ tự `tagsFor`, validator
  V16 khớp). Kiểm trên DB kiểm chứng seed mới: cha-bong mang đủ 3 tags, **0 món
  dùng cha-bong → 0 món bị ảnh hưởng**; tổng nguyên liệu có cờ cá 38 → 39.
- **FIX-A3 ✅** — Báo cáo đề xuất giữ luật tiền tố + test liệt kê phủ ca biên
  (không đổi sang bảng tường minh toàn phần cho cá — xem §phân tích lượt 1):
  test chạy trên toàn catalog thật — mọi slug `ca-` thuộc `thuy-san` đều có cờ
  `ca`; mọi slug `ca-` ngoài thuỷ sản (gồm ca biên `ca-chua`, `ca-rot`, `ca-tim`,
  `ca-na`, `ca-phe-den`) không bao giờ có cờ `ca`; thuỷ sản không bắt đầu bằng
  `ca-` chỉ có cờ `ca` khi nằm tường minh trong bảng luật (`luon`, `cha-ca-vien`).
  Luật trong `TaggingRules` không đổi.
- **FIX-A4 ✅** — TAXONOMY §4: bỏ "mắm nêm lên men từ tôm/ruốc" khỏi nhóm
  `mam-tom`; ghi mắm nêm (lên men từ cá cơm) vào câu nguyên liệu lên men từ cá
  mang `allergen:ca`. Chỉ sửa câu chữ, không đổi dữ liệu (dữ liệu vốn đã đúng).

**Kiểm chứng đợt khắc phục:** validate 0 🔴 trên DB kiểm chứng riêng seed mới
(3.344 bản ghi); `ExportStabilityTest` đạt (export tái lập đúng cả file
cha-bong mới); **Pest 167/167 xanh** (162 cũ + 5 test mới) trong cửa sổ không
tiến trình PHP tranh chấp. Không cần `npm run build` (chỉ đổi JS inline trong
Blade, không chạm `resources/css|js`).

## 7. QD-ALLERGEN — TRIỂN KHAI QD-01…QD-05 (2026-11-02)

Chủ dự án duyệt TOÀN BỘ khuyến nghị QD-01…QD-05 ngày 2026-11-02 (phiếu tại
`vie-ton-dong-va-phieu-quyet-dinh-2026-11-01.md`). ALG-Q1…Q4 của §4 được phân
xử tương ứng: Q1 → QD-01, Q2 → QD-02, Q3 → QD-03, Q4 tách hai nửa → QD-04
(sa-te) và QD-05 (sua-bien).

**Cổng hồi quy lọc cứng (đo trước khi sửa, trên DB kiểm chứng riêng
`shake_hungry_qd` seed mới từ file):** chụp tập món bị loại theo từng nhóm
bằng `Recommender::pool()` + census cờ món. Baseline: pool gốc 565 món;
loại theo nhóm — đậu phộng 220, tôm 258, cua 201, động vật có vỏ 225, cá
423, trứng 248, sữa 202, đậu nành 256, lúa mì 222, mè 224, mắm tôm 203;
387 món mang cờ cá; 49 cặp (món, nhóm) chỉ từ dòng tuỳ chọn. (Số census cờ
cá 387 và tách nguồn dưới đây đo lại trên kho hiện tại — lệch nhẹ so với
388/274/114 của census 2026-11-01 vì dữ liệu công thức đã thay đổi ở các
đợt LINK-ROT-FIX/T-02/T-05 sau đó; cách phân loại cũng tính giò/chả/lươn
là "cá thật".)

- **QD-01 = B ✅ (chỉ hiển thị):** thêm `App\Taxonomy\AllergenDisplay` —
  suy nhãn trình bày từ dòng nguyên liệu + thành phần lồng nhau (cùng quy
  ước mở rộng components với `DishTagDeriver`), API chi tiết món trả thêm
  khoá `allergen_display`, trang món render theo đó. Nhóm cá: mọi đóng góp
  đều từ 5 slug mắm cá lên men (`nuoc-mam-cong-nghiep`,
  `nuoc-mam-truyen-thong`, `mam-ca-linh`, `mam-ca-sac`, `mam-nem`) → nhãn
  **"Nước mắm"**; còn lại → **"Cá"**. Kết quả trên toàn kho: **257 món hiển
  thị "Nước mắm" · 130 món hiển thị "Cá"** (tổng 387 = đúng số món mang cờ
  cá; test census khoá tập nhóm hiển thị ≡ tập cờ món cho 100% món). Cờ,
  preset và lọc cứng KHÔNG đổi — tập loại của nhóm cá sau sửa y hệt
  baseline (423 món). Trang nguyên liệu/quả hiển thị nhãn của chính nguyên
  liệu nên không có badge "Cá" cần tách; thẻ món ở Lắc/Thư viện không
  render badge dị ứng (đã kiểm) nên phạm vi hiển thị = trang chi tiết món
  + trang nguyên liệu liên quan.
- **QD-02 = B ✅ (chỉ hiển thị):** cùng lớp `AllergenDisplay`: nhóm mà mọi
  dòng đóng góp đều `is_optional` → `certainty: "possible"`, trang món
  render badge viền "Có thể có: ..." + dòng giải thích. Sau sửa: **51 cặp
  "có thể có"** (baseline 49; delta giải thích trọn: +3 cặp mới sinh từ
  QD-03/QD-04 — (che-khuc-bach, hạt cây) vì hạnh nhân là dòng tuỳ chọn,
  (lau-bo, tôm) và (vit-nuong-chao, tôm) vì cờ tôm mới chỉ từ dòng sa-te
  tuỳ chọn; −1 cặp (banh-trang-nuong, tôm) vì sa-te ở món này là dòng BẮT
  BUỘC nên cờ tôm thành chắc chắn). Lọc cứng giữ nguyên — xem cổng hồi
  quy bên dưới.
- **QD-03 = A ✅:** thêm `allergen:hat-cay` "Hạt cây" vào
  `config/taxonomy.php` (sau đậu phộng), gắn qua `TaggingRules` cho đúng
  **2 slug hạt cây thật** mà census toàn catalog tìm thấy: `hat-dieu-rang`,
  `hanh-nhan` (đã loại trừ có ghi lý do ở TAXONOMY §4: hạt điều màu là
  annatto, hạt sen/hạt dổi không thuộc nhóm, dừa ngoài danh mục FDA 2025).
  Chip "Hạt cây" tự xuất hiện ở Lắc + Hồ sơ (preset sinh từ luật — tổng
  12 chip). Hệ quả món: `che-khuc-bach` (món duy nhất dùng hạnh nhân) có
  thêm cờ `hat-cay`; `hat-dieu-rang` chưa món nào dùng. Đo pool(): nhóm
  mới loại 190 món (= nền loại-an-toàn vì thiếu nguyên liệu như mọi nhóm
  + che-khuc-bach), test rò 0 cho nhóm mới trong `AllergenExclusionTest`.
- **QD-04 = A ✅:** `'sa-te' => ['tom']` trong `TaggingRules` + tags file
  `sa-te.json`; hồ sơ sa-te bổ sung thành phần tôm khô vào `mapping_note`
  (định danh đã chốt — khớp `origin:dong-vat` vốn gán vì tôm khô). Trước →
  sau cho 6 món dùng sa-te: 3 món đã có cờ tôm từ nguồn khác
  (banh-trang-nuong, banh-trang-tron, com-hen — không đổi cờ); **3 món
  thiếu cờ nay có cờ tôm**: `banh-trang-phoi-suong` (trước [đậu phộng,
  trứng] → +tôm), `lau-bo` (trước [cá, đậu nành, trứng] → +tôm),
  `vit-nuong-chao` (trước [đậu nành] → +tôm). Ở tầng pool(): nhóm tôm
  +1 món bị loại trực tiếp (vit-nuong-chao); lau-bo vốn đã bị loại theo
  quy tắc an toàn thiếu-nguyên-liệu; banh-trang-phoi-suong chưa có bản ghi
  dinh dưỡng nên nằm ngoài pool gợi ý (nhưng đã bị lọc đúng ở Thư viện
  theo cờ món). Preset tôm: 12 → 13 slug (+sa-te).
- **QD-05 = A ✅ (chỉ tài liệu):** `sua-bien` giữ KHÔNG gắn nhóm nào — sứa
  biển là ruột khoang (Cnidaria), không phải vỏ/thân mềm hay giáp xác,
  ngoài các nhóm dị ứng chuẩn; hồ sơ quyết định ghi ở TAXONOMY §4 (cùng
  tiền lệ rươi) để khỏi rà lại. Không đổi dữ liệu/code.

**Cổng hồi quy SAU sửa (cùng DB kiểm chứng, seed lại):** tập món bị loại
của **10/11 nhóm cũ Y HỆT baseline**; nhóm tôm chỉ +1 chủ đích
(vit-nuong-chao — QD-04); nhóm hạt cây là nhóm mới (QD-03). Toàn kho chỉ
**4 món đổi cờ dị ứng** (3 món +tôm của QD-04, 1 món +hạt cây của QD-03),
0 món đổi cờ khác. Validate **0 🔴** (3.344 bản ghi); Pest toàn bộ
**177/177 (719 assertions, exit 0)** ở cửa sổ sạch — gồm ExportStabilityTest
đạt (3 file nguyên liệu sửa vẫn là điểm cố định byte) và 10 test mới/cập
nhật của đợt này (AllergenDisplayTest 9 + QD-03/QD-04 trong
AllergenExclusionTest). Kiểm chứng
hiển thị trực tiếp qua API: banh-beo/goi-ga → "Nước mắm",
canh-chua-ca-loc → "Cá", vit-nuong-chao → "Có thể có: Tôm · tép",
che-khuc-bach → "Có thể có: Hạt cây". Script đo lưu tại
`~/workspace/goals/l-c-i-shake-hungry-development/hidden_files/qd-allergen/`
(baseline_pool.json, after_pool.json, after_display.json).
