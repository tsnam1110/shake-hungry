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

- **ALG-Q1:** cờ cá cho 274 món chỉ có nước mắm/mắm cá — (1) giữ nguyên; (2) giữ cờ, đổi hiển thị tách nghĩa "Cá / nước mắm" *(đợt rà khuyến nghị — nước mắm chứa đạm cá, chuẩn ghi nhãn EU/FDA vẫn xếp nhóm cá; bỏ cờ không an toàn)*; (3) tách facet hiển thị riêng, lọc vẫn gộp.
- **ALG-Q2:** cờ chỉ từ dòng tuỳ chọn có hiển thị phân biệt "có thể có" không (lọc giữ chiều an toàn).
- **ALG-Q3:** thêm nhóm "Hạt cây" vào từ vựng dị ứng?
- **ALG-Q4:** `sa-te` gắn `tom`? · `sua-bien` vào nhóm động-vật-có-vỏ?

## 5. Kiểm chứng cuối

Census lượt cuối chạy lại toàn bộ sau sự cố VM thay mới giữa đợt (khôi phục PHP/MySQL, seed lại DB kiểm chứng): 434/654/82, 0 lệch cơ học, validate 0 🔴, số liệu hai phương pháp khớp nhau. Không chạy Pest (không cần cho kết luận + tránh tranh tiến trình với AUDIT-003/AUDIT-004 song song). Không seed DB dev, không chạm 7 file món dở của phiên song song. Script rà lưu tại `~/workspace/goals/l-c-i-shake-hungry-development/hidden_files/audit-allergen/` (census.php, census_files.py, analyze.py, identity_scan.py, preset_gap.py, pool_check.php).
