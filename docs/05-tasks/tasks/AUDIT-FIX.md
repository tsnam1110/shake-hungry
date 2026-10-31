# AUDIT-FIX — Pha khắc phục sau AUDIT-002 (nhóm A · PX-17 · PX-34 · PX-28)

**Trạng thái:** ✅ HOÀN TẤT — **472/654 món Mức 2** (hạ 3 món có hồ sơ, xem §1); validate 0 🔴; recompute 472/472; Pest 127/127.
**Phụ thuộc:** AUDIT-002 (HEAD `dde8645`; kho 654 món — 475 Mức 2 / 179 Mức 1; danh sách phân xử PX-01…PX-35 là nguồn sự thật).
**Phạm vi:** chỉ 4 nhóm được giao — nhóm A (PX-01…PX-16), PX-17, PX-34 (+ các URL chưa kết luận liên quan), PX-28. Các PX còn lại thuộc loại ghi nhận/quy ước (PX-18…PX-27, PX-29…PX-33, PX-35) **không sửa** — PX-25 (quy ước họ nguồn) và PX-26 (cặp canh hến) vẫn chờ chủ dự án chốt.
**Commit:** `c4c971f` (nhóm A) · `2eea21a` (PX-17) · `55097ea` (PX-34) · `aa284e4` (PX-28) · `afd5b08` (truy vết rà độc lập) · commit tài liệu này. Mọi phân xử chi tiết nằm trong `import_note` từng file món (truy vết nguồn chết/nguồn sai loài giữ nguyên ở đó).

---

## 1. Nhóm A — thay nguồn cho 16 món rớt cổng vì link rot (PX-01…PX-16)

Chuẩn áp dụng như các đợt LV2: nguồn thay thế phải **mở đọc được thật, đúng món/biến thể, độc lập** với các nguồn còn sống của món; đối chiếu lại định lượng theo bộ nguồn mới (nguyên liệu chính lệch >30% so với đồng thuận nguồn thì sửa + ghi lý do; còn lại giữ nguyên số). **Kết quả: 13 món khôi phục cổng, 3 món hạ Mức 2 → 1 có hồ sơ. Không món nào phải sửa định lượng** (mọi lệch đều ≤30% hoặc có căn cứ nguồn còn sống mạnh hơn — từng ca ghi trong `import_note`).

| PX | Món | Kết quả | Nguồn thay thế đã ghi (đều đã mở đọc) |
|---|---|---|---|
| PX-01 | `bau-xao-tep` | ✅ Khôi phục (ưu tiên cao nhất, từ 0/3) | Vào Bếp Wiki (bầu 125 g, tép khô 12,5 g/khẩu phần — ủng hộ quyết định tép khô A3 và số 15 g của file); PDF sách dạy nấu Duy Tân (tỉ lệ tôm khô/bầu 12,5%); YummyDay (biến thể tôm khô ~100 g/mẻ). Định lượng giữ nguyên |
| PX-02 | `canh-khoai-so` | ⬇ **Hạ Mức 1** | Không tìm được nguồn thay thế đạt chuẩn: mọi trang đúng món có gram + khẩu phần đều thuộc họ đã loại (memart/cholaithieu/saigonesebaguette) hoặc thiếu định lượng. Còn 1 nguồn sống (Memart) |
| PX-03 | `banh-canh-nam-pho` | ✅ Khôi phục | Điện Máy XANH (tôm 40, thịt băm 60, giò sống 40 g/khẩu phần — tổng đạm 140 g ≈ 150 g của file, giữ số); Pasgo (biến thể có cua, tỉ lệ 1:1:1). Trang Tripi là bản sao của cùng công thức — không ghi để tránh tính trùng độc lập |
| PX-04 | `canh-khoai-tay-suon` | ✅ Khôi phục | Cookidoo (sườn 100 g khớp tuyệt đối; khoai 125 so với 100 của file = lệch 25%, giữ); Tripi (cà rốt 50–67 g ủng hộ số 50 của file) |
| PX-05 | `suon-rang-muoi` | ✅ Khôi phục | Bếp Mina (đúng kỹ thuật chiên + xóc bột rang muối; sườn 150 g/khẩu phần); TipHay (sườn 125–167 g; ghi TipHay thay vì FPT Shop vì cùng dòng nội dung). Nguồn congthucmonngon giữ trạng thái chưa kết luận. Bột rang muối trong nguồn tính bằng muỗng — nhất quán quy ước 5–10 g của chủ dự án; sườn 180 g của file trong biên 30% |
| PX-06 | `oc-buou-xao-sa-ot` | ✅ Khôi phục | bloganngon **slug mới** trên cùng domain (trang cũ chết; trang mới sống, đúng món — mục nghệ: thịt ốc 300 g, sả 3 cây); nauchuananngon (thịt ốc 75 g/khẩu phần so với 100 của file = lệch 25%, giữ theo phân xử Bếp Trưởng). Đã gỡ entry BlueStone 404 |
| PX-07 | `mi-xao-gion` | ✅ Khôi phục | Medium/Toronto Pho (nguồn độc lập ngoài nước; hải sản 33–50 g/khẩu phần); nauankhongkho (đúng biến thể có gan heo — sau rà độc lập hạ xuống *chưa kết luận*, xem §6). Nguồn ĐMX giữ trạng thái chưa kết luận có ghi chú |
| PX-08 | `canh-nam-rom-thit-bam` | ✅ Khôi phục | PDF sách dạy nấu Duy Tân (biến thể có đậu hũ non + hẹ, **công khai trong ghi chú**; tỉ lệ nấm:thịt 2:1 khớp đúng file 100:50) |
| PX-09 | `thit-bo-xao-khe` | ✅ Khôi phục | Cookpad VN (đúng món; bò 200 g/mẻ, khế tính theo trái — giới hạn đã ghi chú) |
| PX-10 | `canh-bau-ngheu` | ✅ Khôi phục | TipHay/Bách Hóa XANH (ghi rõ syndication; ngao nguyên vỏ 500 g/3–4 khẩu phần → thịt ~31–50 g, bao phủ số 37 g của file) |
| PX-11 | `ca-tim-xao-thit-bam` | ✅ Khôi phục | Điện Máy XANH mục 2 (cà 125 g ≈ 130 của file; thịt 50 so với 60 = lệch 17%, giữ; dầu ~8–9 g khớp) |
| PX-12 | `trung-cut-kho-thit` | ✅ Khôi phục | Bếp Xưa (thịt 125 so với 100 = lệch 25%, giữ; nước dừa 100 ml ≈ 90 g khớp). Lệch trứng (Bếp Xưa 7,5 quả/khẩu phần so với ~4 quả của file) đã ghi nhận nhưng **không** lật số: 2 nguồn còn sống (HNAA + Pasgo) ủng hộ file, Pasgo là căn cứ tỉ lệ trứng gốc |
| PX-13 | `vit-rang-gung` | ✅ Khôi phục | congthucgiadinh "vịt kho gừng" (gừng thuần, **không sả** — khớp hồ sơ món; tỉ lệ gừng/vịt 6,7% so với 8,3% của file). Đã ghi chú chồng lấn tên gọi kho/rang |
| PX-14 | `dau-phu-kho-chao` | ⬇ **Hạ Mức 1** | Không có nguồn thay thế đạt chuẩn: trang Memart còn lại chỉ tính theo miếng (không gram/khẩu phần); trang ĐMX là đậu phụ **hấp** chao ≠ kho. Còn 2 nguồn ghi nhận, không đủ cổng |
| PX-15 | `bun-mam` | ✅ Khôi phục | Báo Người Lao Động (độc lập, đúng món; tổng mắm 120 g/khẩu phần là ngoại lệ cao so với đồng thuận ~43–45 g của 2 nguồn còn sống ĐMX + Cookpad — ghi làm biến thể giàu topping, giữ số file) |
| PX-16 | `canh-rau-lang-tom` | ⬇ **Hạ Mức 1** | Thêm 2 nguồn mở được (Chợ Lái Thiêu — khoảng số bao phủ file; Dân Việt — đúng món, định lượng yếu) nhưng 2 URL cũ (ĐMX + bản AMP giaothonghanoi) **vẫn không mở được** ở cả vòng nghiên cứu lẫn vòng này (lỗi fetch, không có HTTP status — chưa kết luận, không phải chết). Tổng nguồn xác nhận sống = 2 < 3 → hạ mức trung thực, cả 2 nguồn mới vẫn ghi trong file để nâng lại khi 1 URL cũ kết luận được |

Hai file `banh-canh-nam-pho` + `mi-xao-gion` trong commit nhóm A đồng thời mang trường `media` do phiên song song đã ghi vào working tree (webp + MANIFEST đã có trong repo, khớp 3 chiều) — đã nêu trong commit message.

## 2. PX-17 — `ca-ro-dong-chien-xu`: loại nguồn sai loài, sửa tỉ lệ bột

- **Đã loại** nguồn Điện Máy XANH (trang cá rô **phi**/tilapia) khỏi `recipe_sources`; việc loại + lý do **ghi công khai trong `import_note`** đúng như yêu cầu.
- Chứng minh tỉ lệ cũ đi theo nguồn sai loài: tỉ lệ file cũ (bột xù 25 g/250 g cá = 10 g/100 g cá) khớp đúng họ công thức rô phi — kiểm chứng độc lập: FPT Shop và Ẩm Thực Mọi Miền đều ghi rô phi tẩm bột 100 g bột xù + 2 trứng/1 kg cá.
- Căn cứ mới là nguồn đúng loài duy nhất có định lượng, **Memart** (cá rô bí = cá rô đồng: cá 500 g, bột xù 100 g, bột mì 50 g, trứng 1 quả) → quy đổi khẩu phần 250 g cá: **bột chiên xù 25 → 50 g, thêm bột mì 25 g** (quy trình 3 lớp bột mì → trứng → bột xù mà ToyenXin mô tả định tính), trứng giữ 25 g.
- Đã rà thêm: ngoài Memart/ToyenXin, không tồn tại nguồn đúng loài rô đồng tẩm bột xù có định lượng nào khác trên web mở (các nguồn "cá rô đồng chiên xù" độc lập khác đều là chiên trần không bột — gồm nguồn ATMM đã ghi trong file). Tỉ lệ bột vì vậy dựa trên một họ nguồn đúng loài duy nhất → giữ `confidence` low, đã ghi rõ. Cổng 3 nguồn vẫn đứng (ATMM + Memart + ToyenXin), giữ Mức 2.

## 3. PX-34 — mở lại toàn bộ URL chưa kết luận của 23 món

Phương pháp: census vòng mới bằng curl tuần tự **toàn bộ 69 URL nguồn của 23 món**, sau đó mở kiểm chứng bằng trình duyệt **mọi URL không trả 200** (26 URL duy nhất). Kết quả:

- **9 URL kết luận SỐNG đúng món:** giavihanhphuc ×3 (`banh-xu-xe`, `com-hai-san`, `kho-qua-xao-trung`), cholaithieu (`canh-kho-qua-ca-loc` — mục 2 đúng biến thể cá lóc), Foodeli (`pho-bo-sot-vang`), VinID (`cao-lau`), hc.com.vn (`che-hat-sen-long-nhan`), ĐMX ×2 (`com-hai-san`, `suon-nuong`). Các mã 404/403/502 qua curl đều là chặn bot hoặc lỗi tạm thời — thêm bằng chứng curl không đáng tin cho các domain này.
- **0 URL xác nhận chết** → không món nào rớt cổng vì PX-34; không món nào phải thay nguồn/hạ mức ở nhóm này.
- Phần còn lại giữ **chưa kết luận** (lỗi mạng/không phân giải được qua dịch vụ fetch, không có HTTP status) kèm ngày thử lại 2026-11-01 trong `import_note` từng món. Hai hồ sơ yếu được ghi rõ: `canh-bong-tha` (0/3 đọc được nội dung — bachhoaxanh trả HTTP 200 thân rỗng, 2 URL còn lại lỗi mạng) và `banh-xu-xe` (1 xác nhận sống + 2 chưa kết luận) — ưu tiên thử lại đợt sau.
- `kho-qua-xao-trung` và `pho-bo-sot-vang` nay đủ 3/3 nguồn xác nhận sống.

## 4. PX-28 — sửa 260 URL FoodData Central 404

- **Xác định mẫu mới:** mẫu cũ `fdc.nal.usda.gov/food-details/<id>/nutrients` 404 với mọi ID (cả dạng bỏ đuôi `/nutrients` cũng 404 — FDC đã thiết kế lại site). Mẫu `fdc-app.html#/food-details/<id>/nutrients` lịch sử chỉ trả vỏ ứng dụng JS, không kiểm chứng được nội dung. Mẫu đạt chuẩn là **endpoint API chính thức của USDA NAL: `https://api.nal.usda.gov/fdc/v1/food/<FDC ID>?api_key=DEMO_KEY`** — trả bản ghi JSON đầy đủ của đúng thực phẩm.
- **Kiểm chứng TRƯỚC khi sửa hàng loạt (điều kiện ≥10 ID):** mở 11 ID — 171711 (việt quất) kiểm chứng đầu tiên, cộng 10 ID trải đều trên toàn dải (167744 ốc bươu, 168267 tim heo, 168878 cơm trắng, 169382 nấm kim châm, 169997 rau mùi, 170917 lá nguyệt quế, 171890 cà phê đen, 173430 bơ, 174687 mít, 2727576 thịt ba chỉ — bản ghi Foundation 2025): **11/11 đúng thực phẩm, Energy khớp tuyệt đối** số đã ghi trong file nguyên liệu.
- **Đã sửa:** 286 URL (260 FDC ID duy nhất) trong `data/ingredients/*.json`, chỉ thay khuôn URL, **số dinh dưỡng không đổi**.
- **Kiểm chứng SAU sửa trên 100%:** ID trong URL == `external_id` đã ghi: **286/286**; kcal khớp bản trích SR Legacy cục bộ cho cả **268** ID thuộc SR Legacy (18 ID còn lại thuộc loại dữ liệu mới hơn — Foundation/market — nằm ngoài bản trích); mở trực tiếp toàn bộ 260 URL đã sửa: **260/260 HTTP 200 kèm đúng tên thực phẩm**.
- Ghi nhận riêng: 8 mã NDB đời cũ nêu ở PX-28 (trai, oc-len, khoai-mo, la-e, du-du-xanh, vo-hoanh-thanh, nui-kho, hanh-nhan) **không nằm trong tập URL vừa sửa** (không URL food-details nào chứa mã 5 chữ số — kiểm kê toàn kho); việc chuẩn hoá các mã đó nằm ngoài phạm vi đợt này, số liệu của chúng không đổi. DEMO_KEY là khoá demo công khai chính thức của USDA (có giới hạn tần suất khi gọi hàng loạt; dùng làm liên kết trích dẫn tới đúng bản ghi là ổn định).

## 5. Kiểm chứng cuối (DB kiểm chứng riêng — không seed DB dev)

- **Validate** trên `shake_hungry_verify`: **0 🔴 / 11 🟡** (3.344 bản ghi; 11 🟡 toàn thuộc lớp đã biết: V9 món nhỏ, V10 mỡ nguyên chất, V8 nguyên liệu chưa dùng, V7 hai cặp tên gồm PX-26 — không thuộc phạm vi sửa).
- **Tổng Mức 2 sau khắc phục: 472/654** — khớp 3 cách độc lập: đếm `completeness_level` trên file (472 Mức 2 / 182 Mức 1), recompute DB kiểm chứng (**472 món tính đủ, 0 món thiếu dữ liệu**), đối chiếu với DB nền tại `dde8645` (475 tính đủ).
- **Đối chiếu calo cách ly** (worktree sạch tại `dde8645` + DB `shake_hungry_verify_old`): tập món tính được chỉ khác đúng **3 món hạ mức** rời tập; **đúng 1 món đổi khoảng kcal: `ca-ro-dong-chien-xu`** — điểm giữa 756,7 → 1.027,0 kcal (+270,2), truy về đúng thay đổi bột ở PX-17 (bột xù +25 g, thêm bột mì 25 g/khẩu phần + dầu hấp thụ mô hình CALC-004 trên lớp bột dày hơn). Ngoài ra 0 món đổi.
- **Pest: 127/127 (494 assertions)** — gồm test validate xanh trên dữ liệu thật hiện tại của repo.
- **Rà độc lập nguồn thay thế:** một vòng rà độc lập mở lại **100% (22/22) URL nguồn thay thế mới ghi** của nhóm A: **19 mở được, đúng món**; 3 URL không fetch lại được đã ghi truy vết vào `import_note` (commit `afd5b08`): PDF Duy Tân của `bau-xao-tep` (lỗi mạng — cùng lớp chưa kết luận PX-34; 2 nguồn còn lại của món đều xác nhận 2 lần độc lập), Cookidoo của `canh-khoai-tay-suon` (HTTP 403 chống bot khi rà lại — nội dung đã đọc đầy đủ ở vòng nghiên cứu), nauankhongkho của `mi-xao-gion` (lỗi mạng — hạ xuống chưa kết luận; cổng món dựa trên 2 nguồn đã xác nhận + 2 chưa kết luận có truy vết).

## 6. Ghi chú môi trường & phạm vi không chạm

- Trong lúc thực thi, VM bị thay mới: MySQL mất toàn bộ database (kể cả DB dev) và auth root bị reset — đã áp cách khôi phục chuẩn của dự án (root native password không mật khẩu) chỉ để chạy kiểm chứng. Theo đúng nhiệm vụ, **chỉ tạo/seed các DB kiểm chứng riêng** (`shake_hungry_verify`, `shake_hungry_verify_old`, `shake_hungry_test` cho Pest); DB dev **không** được seed ở đợt này — phiên chính cần dựng lại dev như thường lệ khi tiếp tục.
- Không chạm 7 file món của phiên song song còn dở trong working tree (media); commit chỉ pathspec của đợt.
- Các PX còn lại (PX-18…PX-27, PX-29…PX-33, PX-35): giữ nguyên trạng — PX-25/PX-26 vẫn chờ quyết của chủ dự án; không thay đổi nào ở các nhóm trên tác động tới chúng.
