# Roadmap & Task Tracking — Lắc Đói

**Ngày bắt đầu:** 2026-08-31
**Cập nhật lần cuối:** 2026-10-31 — ứng dụng hoàn thiện end-to-end; kho dữ liệu **657 món /
406 nguyên liệu** (300 công thức đầu đã duyệt; 361 công thức chờ duyệt = 200 DISH-004 + 160 DISH-005
cả 3 đợt + 1 món `dau-phu-chien-sa-ot` biên soạn sau hiệu chuẩn đậu phụ CALC-004; đã gộp tầm soát
2026-10-31: −4 món nước mắm địa danh trùng tuyệt đối, 2 cặp biến thể vùng liên kết parent); **CALC-004** (hệ số dầu theo nhóm)
và **LEGAL-002** (license MIT + CC BY 4.0/ODbL) đã chốt; repo chuyển hẳn sang **GitHub**
(`origin` = github.com/tsnam1110/shake-hungry, không còn remote GitLab) — CI là GitHub Actions
đã đúng nền nhưng chưa chạy vì code chưa push (chờ credential). 115 test Pest xanh. **LV2-001 đợt 1
xong cùng ngày: 47/50 món nền tảng đạt Mức 2 thật** (3 món giữ Mức 1 có lý do — xem
[tasks/LV2-001.md](./tasks/LV2-001.md)). Tiếp nối cùng ngày: **Phần A sửa bất thường LV2-001**
(9 món thêm/bớt/đổi dòng nguyên liệu theo nguồn đồng thuận) và **LV2-002 đợt 2: 50/50 món
thường ngày tiếp theo đạt Mức 2 thật — tổng 97/657 món Mức 2** (xem
[tasks/LV2-002.md](./tasks/LV2-002.md)). Tiếp nối: **phân xử Phần A của LV2-002**
(26 món sửa dòng/phân loại, commit riêng) và **LV2-003 đợt 3: 37/50 món tiếp theo
đạt Mức 2 thật — tổng 134/657 món Mức 2** (13 món giữ Mức 1 có lý do, phần lớn
vì provider tra cứu chặn 429 không gom đủ nguồn đã mở — xem
[tasks/LV2-003.md](./tasks/LV2-003.md)). Tiếp nối: **LV2-004 — Phần A phân xử
toàn bộ bất thường LV2-003** (21 món sửa dòng; biên soạn 5 nguyên liệu mới đều
dạng null + hồ sơ: bot-chien-gion, me, giam-bong, nuoc-mau, gio-song; thử lại
8 món 429 — 5 món đủ nguồn) và **Phần B đợt 4: 50/50 món đạt Mức 2 thật — tổng
184/657 món Mức 2** (xem [tasks/LV2-004.md](./tasks/LV2-004.md)). **LV2-005 là
đợt chốt chương trình Mức 2:** Phần A phân xử bất thường LV2-004 (vùng
ca-tram-hap-xi-dau chuyển Bắc theo quy ước gốc xuất xứ; 5 ca định danh phân xử
xong 3, giữ 2 chờ quyết cùng bản phân tích) và **Phần B: +32 món — tổng
216/657 món Mức 2, VƯỢT mốc 200 của chương trình** (13 ứng viên giữ Mức 1 có
hồ sơ: 11 món do 429, com-tho thiếu nguồn đúng biến thể, com-hen do phân xử
nguồn forum không đạt chuẩn). Danh sách quyết định còn chờ chủ dự án gom một
chỗ tại [tasks/LV2-005.md](./tasks/LV2-005.md) §4. **Chủ dự án chỉ đạo
2026-10-31: MỞ RỘNG chương trình — nâng TOÀN BỘ món lên Mức 2, chia nhỏ
nhiều đợt, chất lượng tuyệt đối ưu tiên.** **LV2-006 là đợt đầu của chương
trình mở rộng:** Phần A phân xử 6 ca bất thường LV2-005 (com-hen nâng Mức 2
nhờ nguồn sạch thay thế; vit-xao-mang đổi method sang `nau`; bun-mam giữ +
hồ sơ chờ số mắm cá) và **Phần B: 39/50 món đạt Mức 2 thật — tổng 256/657
món Mức 2** (11 món giữ Mức 1 có hồ sơ: 10 do 429, thit-bam-rim thiếu
nguồn định lượng) — xem [tasks/LV2-006.md](./tasks/LV2-006.md). Còn 401 món
Mức 1 cho các đợt LV2-007+. **LV2-007:** Phần A phân xử toàn bộ bất thường
LV2-006 (7 ca: giữ tương cà nui xào bò, giữ sữa đặc chè Thái + bổ sung trân
châu, giữ mỡ lợn xôi xéo, mắm ruốc lên vùng Trung…) và xử lý hàng đợi biên
soạn nguyên liệu (rau đắng + trân châu CÓ SỐ; mắm cá sặc/linh + heo quay giữ
null có hồ sơ; muối tôm/củ nén slug mới null) — `bun-mam`/`lau-mam` vẫn chờ
vì mắm cá chưa có số; **Phần B: 45/50 món đạt Mức 2 thật — tổng 301/657 món
Mức 2** (thử lại 429 đợt 6: 9/10 nâng; 5 món giữ có hồ sơ; biên soạn thêm 6
nguyên liệu, kho 420) — xem [tasks/LV2-007.md](./tasks/LV2-007.md). Còn 356
món Mức 1 cho các đợt tiếp theo. **LV2-008:** Phần A phân xử bất thường
LV2-007 (`cha-bong` thử tra nhãn — VẪN null, `xoi-man` giữ Mức 1;
`hung-lui` slug mới CÓ SỐ USDA 173475 + bổ sung dòng cho `goi-xoai`;
khép 2 bất thường chờ phân xử sau) và **Phần B: 45/60 món xét đạt Mức 2
thật — tổng 346/657 món Mức 2** (15 món giữ có hồ sơ: 5 do 429,
bun-nuoc-leo chạm trần mắm cá null như bun-mam, ca-bong-om-chuoi chờ
chủ dự án xác nhận món có thật, canh-rau-den-tep nhập nhóm chờ quyết
tép khô/tươi; kho nguyên liệu 421) — xem
[tasks/LV2-008.md](./tasks/LV2-008.md). Còn 311 món Mức 1 cho các đợt
tiếp theo. **LV2-009:** Phần A phân xử bất thường LV2-008 §3 (biên soạn
4 slug thiếu — `cha-com`, `bong-sung`, `ngo-om`, `ngai-bun` — cả 4 đều
null + hồ sơ; bổ sung dòng cho `bun-dau-mam-tom` và
`canh-ca-linh-bong-dien-dien`; thay dòng sai loài `ngo-gai` → `ngai-bun`
ở `bun-nuoc-leo`; cần tàu dùng slug sẵn có `rau-can-ta`) — kho 425
nguyên liệu. **Phần B đợt 9: 27/55 món xét đạt Mức 2** (2 vòng tra cứu vì
429 ở vòng 1) — **tổng 373/657 món Mức 2, còn 284 món Mức 1**; bổ sung
5 món bánh (4 đạt, 1 giữ-429) → **tổng cuối 377/657, còn 280 món Mức 1**;
28+1 món giữ
có hồ sơ theo nhóm (12 GIỮ-429 làm lại được, 5 thiếu nguồn đúng biến thể,
3 chờ quyết định danh, 3 trần nguyên liệu/thiếu họ nguồn, 5 không đủ nguồn
đạt chuẩn); bất thường mới + 3 ca chờ quyết định danh mới gom tại
[tasks/LV2-009.md](./tasks/LV2-009.md) §B.3.
**LV2-010:** Phần A phân xử bất thường LV2-009 §B.3/§B.5 (áp: vùng +
nhãn `dac-san:ha-noi` cho `banh-goi`, `che-dau-trang` đổi bột nếp →
gạo nếp; giữ có lý do: bột `chuoi-chien`, rau đắng `lau-ca-keo`, hai
nhãn dac-san ngoài TAXONOMY) + biên soạn 8 slug hàng đợi — 5 có số
(`sach-bo`, `bo-vien`, `thit-bo-ba-chi`, `cu-nang`, `vani`), 3 null +
hồ sơ (`gan-bo-tendon`, `tuong-hat`, `mia`) — kho 425 → **433 nguyên
liệu**; bổ sung dòng cho `lau-bo` (+4), `che-com` và
`che-hat-sen-long-nhan` (+vani); đính chính tía tô `chao-luon-nghe-an`
chỉ 1/3 họ nên không thêm dòng — xem
[tasks/LV2-010.md](./tasks/LV2-010.md) Phần A. **Phần B đợt 10: 23/50
món xét đạt Mức 2 (19 vòng 1 + 4 vòng 2 mở nguồn tuần tự) — TỔNG
400/657 món Mức 2, còn 257 Mức 1**; 27 món giữ có hồ sơ (11 biến thể,
9 còn 429, 6 thiếu nguồn, 1 trần nguyên liệu — `bun-suong` đã áp đủ
định lượng đã kiểm chứng, chỉ chờ `tuong-hat` có số); bất thường mới
(parent của `sui-din`, quy mô khẩu phần `banh-ran`, định danh
`che-tao-soan`…) gom tại [tasks/LV2-010.md](./tasks/LV2-010.md) §B.3.
Kiểm chứng: validate 0 🔴 (kể cả DB), Pest 127/127, đối chiếu calo
cách ly bằng worktree tại `cd6f7d9` — 0 món ngoài phạm vi đổi >1%.
**Phần C — gói quyết định A1–A4 của chủ dự án (duyệt 2026-10-31
~22:28):** áp bộ sửa `canh-ba-roi-nau-dau` → Mức 2; **xoá 3 món
không xác nhận được tồn tại** (`canh-nghe-ca-ro`, `ca-bong-om-chuoi`,
`canh-ca-bong`) — **kho còn 654 món**; A3 chốt các ca cấp món
(`banh-bo` men nở khô → Mức 2, `bau-xao-tep`/`canh-rau-den-tep` →
tép khô, `dau-phu-kho-nam` → nấm rơm, `com-nam-huong` giữ dạng tự
biên soạn; `chao-dau-xanh`/`chao-ca-loc-trung` giữ nguyên chờ chỉ
định phương án); A4 áp 4 quy ước cấp kho (rau xào bỏ nêm mắm — 12
món + 2 ngoại lệ; cá kho bỏ thốt nốt — 11 món; quy ước lượng sợi
tại EDITORIAL-GUIDE §14; `mong-gio` 150 g) + nhóm rang muối đổi
chiên + bột rang muối (slug mới `bot-rang-muoi` null) — **cả 3 món
nâng Mức 2. TỔNG CUỐI: 406/654 món Mức 2, còn 248 Mức 1; 434
nguyên liệu.** Kiểm chứng Phần C: validate 0 🔴, recompute 406/406
tính đủ trên DB kiểm chứng riêng, đối chiếu calo cách ly — 0 món
ngoài phạm vi đổi >1%, Pest 127/127. Chi tiết:
[tasks/LV2-010.md](./tasks/LV2-010.md) mục PHẦN C.

**LV2-011:** Phần A rà bất thường LV2-010 §B.3 — **không thay đổi dữ
liệu**: 11/12 mục thuộc danh sách chờ quyết của chủ dự án (giữ
nguyên), 2 mục chuyển Phần B xét bằng nguồn; `dau-bap-xao-toi` hoãn
áp A4.1 lần nữa vì file vẫn đang dở của phiên song song; danh sách
chờ quyết tổng hợp cập nhật tại [tasks/LV2-011.md](./tasks/LV2-011.md)
§A.3. **Phần B đợt 11: 15/50 món xét đạt Mức 2 thật — TỔNG 421/654
món Mức 2, còn 233 Mức 1** (35 món giữ có hồ sơ: 22 thiếu nguồn quy
đổi được, 11 biến thể, 2 trần nguyên liệu định danh null; đợt này
không món nào gặp 429). Kiểm chứng: validate 0 🔴 cả file lẫn DB
kiểm chứng, recompute 421/421 tính đủ, đối chiếu calo cách ly 0 món
ngoài phạm vi đổi số, Pest 127/127, rà độc lập 9/10 nguồn mẫu khớp
trực tiếp (1 trang lỗi mạng khi mở lại). Bất thường mới đáng chú ý:
`lau-ngan` nghi sáng tạo biên tập — đề xuất danh sách xác nhận tồn
tại; cặp `goi-oc`/`nom-chuoi-xanh` chờ quyết định danh hoa chuối vs
quả chuối xanh. Chi tiết: [tasks/LV2-011.md](./tasks/LV2-011.md).

**LV2-012 — ĐỢT VÉT CUỐI, CHƯƠNG TRÌNH MỞ RỘNG MỨC 2 ĐÓNG:** lập bảng kê
toàn bộ 233 món Mức 1 (173 chưa từng xét + 60 đã giữ có hồ sơ), xét toàn bộ
nhóm chưa từng xét + thử lại có chọn lọc 12 món nhóm đã giữ + vòng 2 đọc lại
nhịp chậm: **54 món nâng Mức 2 — TỔNG 475/654 món Mức 2 (72,6%), còn 179
Mức 1** đều có hồ sơ lý do theo nhóm (19 chờ quyết, 44 thiếu nguồn, 33 bị
chặn 429 chưa kết luận, 38 biến thể, 23 trần nguyên liệu, 12 không có công
thức đúng định danh, còn lại: thử lại không họ mới/chưa biên soạn/đã quyết
giữ/chờ điều kiện). Danh sách chờ quyết BẢN CUỐI 42 mục tại
[tasks/LV2-012.md](./tasks/LV2-012.md) §4; `dau-bap-xao-toi` vẫn chờ áp A4.1
bổ sung vì file còn dở của phiên song song (ghi nhận lần cuối, LV2-012 §5).
Kiểm chứng: validate 0 🔴, recompute trên DB kiểm chứng 475/475 tính đủ,
đối chiếu calo cách ly đúng 54 dòng mới / 0 dòng cũ đổi, Pest 127/127, rà
độc lập mẫu 10/10 khớp. Bước tiếp theo đã xếp hàng: rà soát toàn bộ (census)
sau chương trình. Chi tiết: [tasks/LV2-012.md](./tasks/LV2-012.md).

**AUDIT-002 — RÀ SOÁT TOÀN BỘ (CENSUS) SAU CHƯƠNG TRÌNH MỨC 2 ĐÃ XONG:**
census 100% toàn kho (654 món, 434 nguyên liệu, 82 quả, 1.087 ảnh khớp 3
chiều tuyệt đối, 2.185 URL nguồn — 407 URL mở lại bằng trình duyệt ở lượt 2
độc lập: 333 sống đúng, 10 sai nội dung, 24 chết hẳn, 40 chưa mở được do lỗi
công cụ). Cấu trúc/hồ sơ Mức 1/quy ước A4 đều sạch sau khi loại dương tính
giả; validate 0 🔴, recompute 475/475, Pest 127/127. Phát hiện gom thành
**danh sách phân xử PX-01…PX-35 chờ chủ dự án quyết**: link rot làm 16 món
Mức 2 rớt cổng nguồn (`bau-xao-tep` 0/3 là nặng nhất); nhóm nguồn sai
loài/họ kỹ thuật chờ một quyết định quy ước chung (trừ `ca-ro-dong-chien-xu`
không công khai — PX-17); cặp canh hến trùng tên chính (PX-26); link rot
FDC 260 URL (PX-28, số đã kiểm chứng độc lập). Sửa cơ học duy nhất: 6 file
nguyên liệu sửa `oil_absorption_pct` theo CALC-004. Chi tiết:
[tasks/AUDIT-002.md](./tasks/AUDIT-002.md) + báo cáo rà soát toàn bộ
2026-10-31.

**AUDIT-FIX — PHA KHẮC PHỤC SAU AUDIT-002 ĐÃ XONG (2026-11-01):** xử lý
đúng 4 nhóm được giao. Nhóm A (PX-01…PX-16): 13/16 món khôi phục cổng 3
nguồn bằng nguồn thay thế đã mở kiểm chứng (đối chiếu lại định lượng —
không món nào phải sửa số); **3 món hạ Mức 2 → 1 có hồ sơ**
(`canh-khoai-so`, `dau-phu-kho-chao`, `canh-rau-lang-tom`); `bau-xao-tep`
khôi phục từ 0/3. PX-17: loại nguồn sai loài cá rô phi khỏi
`ca-ro-dong-chien-xu` (công khai trong hồ sơ), sửa tỉ lệ bột theo nguồn
đúng loài (bột xù 25 → 50 g + bột mì 25 g). PX-34: mở lại toàn bộ URL
chưa kết luận của 23 món — 9 kết luận sống, 0 chết, không món nào rớt
cổng. PX-28: 286 URL FDC (260 ID) chuyển sang mẫu API chính thức của
USDA NAL — kiểm chứng 11/11 ID trước khi sửa, mở lại 260/260 sau sửa,
số dinh dưỡng không đổi. **Tổng Mức 2 sau khắc phục: 472/654**; validate
0 🔴, recompute 472/472, đối chiếu calo cách ly chỉ 1 món đổi (PX-17),
Pest 127/127, rà độc lập mở lại 100% nguồn thay thế mới (19/22 đạt, 3
ghi truy vết). Các PX còn lại (PX-18…PX-27, PX-29…PX-33, PX-35) giữ
nguyên trạng — PX-25/PX-26 vẫn chờ chủ dự án. Chi tiết:
[tasks/AUDIT-FIX.md](./tasks/AUDIT-FIX.md).

**AUDIT-ALLERGEN — RÀ SOÁT CENSUS TOÀN BỘ THÔNG TIN DỊ ỨNG ĐÃ XONG
(2026-11-01, chỉ đọc — không sửa dữ liệu/code):** census 100% (654 món /
434 nguyên liệu / 82 quả / 11 nhóm) bằng 2 phương pháp độc lập khớp tuyệt
đối: **tầng cơ học sạch 0 lệch** (file ↔ luật `TaggingRules`, DB ↔
`DishTagDeriver`, validate 0 🔴). **Cờ cá được giải thích trọn vẹn:** 388
món có cờ — 109 có cá thật, **274 chỉ vì nước mắm/mắm cá (chính sách đã
ghi ở TAXONOMY §4, chờ chủ dự án quyết ALG-Q1)**, 5 vì giò/chả theo quy
ước, 0 không giải thích được. **🔴 Phát hiện an toàn chính nằm ở tầng
lọc:** preset slug viết tay của Lắc/Hồ sơ đóng băng từ REC-001 — **không
có chip Cá (388 món) / Động vật có vỏ (56) / Mè (53)** và rò rỉ đã đo
bằng `Recommender::pool()` thật (đậu nành 36 món lọt, tôm 22, lúa mì 11,
mắm tôm 9, trứng 7, sữa 5, cua 3; dùng toàn bộ slug có nhãn thì rò 0).
Gắn thiếu xác nhận duy nhất ở tầng dữ liệu: `cha-bong` thiếu cờ cá (hồ sơ
tự ghi có nước mắm; hiện 0 món dùng). Đề xuất: FIX-A1…A4 (sinh preset từ
luật + thêm 3 chip + test khoá; gắn `cha-bong`; hardening luật tiền tố;
sửa câu chữ TAXONOMY) và ALG-Q1…Q4 chờ chủ dự án (chính sách cờ cá nước
mắm, cờ từ dòng tuỳ chọn, thêm nhóm Hạt cây, phân xử sa-te/sứa biển).
Chi tiết: [tasks/AUDIT-ALLERGEN.md](./tasks/AUDIT-ALLERGEN.md) + báo cáo
AUDIT-ALLERGEN 2026-11-01.

**ALLERGEN-FIX — KHẮC PHỤC FIX-A1…A4 ĐÃ XONG (2026-11-01):** preset slug
viết tay ở Lắc/Hồ sơ bị xoá, thay bằng `App\Taxonomy\AllergenPreset` **sinh
từ `TaggingRules`** (nguồn sự thật duy nhất) — đủ **11 chip** ở cả hai trang
(thêm Cá/Động vật có vỏ/Mè + mọi slug có nhãn như `nuoc-tuong`,
`tom-cang-xanh`, `ghe`). **Đo lại bằng `Recommender::pool()` trên toàn kho:
rò 0 cho cả 11 nhóm** (trước: đậu nành 36, tôm 22, lúa mì 11, mắm tôm 9,
trứng 7, sữa 5, cua 3), loại oan 0; test khoá `AllergenExclusionTest` (5
test) giữ tính chất này không tái phát. `cha-bong` đã gắn cờ cá (0 món bị
ảnh hưởng); luật tiền tố `ca-` được phủ test liệt kê toàn catalog (cá thật
đều có cờ, `ca-chua`/`ca-phe-den`... không bao giờ thành cá); TAXONOMY §4
sửa câu chữ mắm nêm (lên men từ cá cơm → nhóm cá). Kiểm chứng: validate
0 🔴, ExportStabilityTest đạt, **Pest 167/167**. ALG-Q1…Q4 vẫn chờ chủ dự
án. Chi tiết: [tasks/AUDIT-ALLERGEN.md](./tasks/AUDIT-ALLERGEN.md) §6.

**AUDIT-004 — KIỂM TRA ĐỘC LẬP ĐỘ CHÍNH XÁC GIÁ TRỊ TÍNH/VỊ/NGŨ HÀNH
ĐÃ XONG (2026-11-01, chỉ đọc — không sửa dữ liệu/code):** tái suy dẫn
**mù** 227 nguyên liệu (9 agent độc lập chỉ nhận định danh; lô thịt tra
2 lượt bằng 2 họ kênh) → đối chiếu 413 dòng nhãn; dòng chưa khớp cùng
nguồn đi lượt 2 mở đúng `source_ref` (85 URL). Kết quả: **sai giá trị
0/413** — đã xác minh 413/413 (208 khớp mù · 183 lượt 2 · 20 suy diễn
qua gốc đã xác minh · 2 giá trị đúng nhưng trích dẫn khuyết điểm);
13 dòng bị cờ "mâu thuẫn" lượt 1 đều đúng theo cổ thư chúng trích
(trang nguồn đa truyền thống). Script độc lập (không dùng code engine):
P-DERIVE **20/20**, element **413/413**, tính lại tổng hợp **0/648 món
lệch**; API hiển thị chính **0 lỗi/881 endpoint**. 🟠 Duy nhất:
`dau-do-hat` — giá trị đúng theo Dược điển nhưng trang tcmfans dẫn làm
bản kiểm chứng lại ghi biến thể ngược (FIX-TCM4-1). Đề xuất thêm:
TCM-001d gieo bổ sung ~14 truyền thống có thật chưa gieo (FIX-TCM4-2),
quy ước về bản EN dịch máy của zhongyifangji (FIX-TCM4-3), 1 ca link
404 chuyển AUDIT-003 (FIX-TCM4-4). Chi tiết:
[tasks/AUDIT-004.md](./tasks/AUDIT-004.md) + báo cáo AUDIT-004
2026-11-01.

**FIX-TCM4 — KHẮC PHỤC SAU AUDIT-004 ĐÃ XONG (2026-11-01):**
FIX-TCM4-1: trích dẫn `dau-do-hat` đã thay liên kết kiểm chứng tcmfans
(mang biến thể ngược) bằng bản tái bản A+ 赤小豆 khớp Dược điển — giá trị
không đổi. FIX-TCM4-2 = **TCM-001d: +23 dòng nhãn cho 13 nguyên liệu**
(mọi nguồn mở lại kiểm chứng tại lúc gieo; gieo song song) → tổng **436
dòng**, 227/434 NL có nhãn; `rau-mui` chờ có hồ sơ (nguồn chỉ cho tính,
không kèm vị). Ảnh hưởng: độ phủ không đổi (500/98/50, 0 món qua ngưỡng
khoá <30%); `has_conflicts` 220 → 259 (+39 món — hệ quả đúng thiết kế);
24 món confidence medium → low; 11 món đổi nhãn tính hiển thị.
FIX-TCM4-3: quy ước bản EN dịch máy đã ghi tại NGU-HANH-AM-DUONG §10.
Kiểm chứng: validate 0 🔴, Pest 167/167, ExportStabilityTest đạt. Chi
tiết: [tasks/TCM-001.md](./tasks/TCM-001.md) mục TCM-001d +
[tasks/AUDIT-004.md](./tasks/AUDIT-004.md) §5.

> Mỗi task có file chi tiết riêng tại [`tasks/`](./tasks/). Click mã task để xem đầy đủ.
> Tạo task mới: copy [`TASK-TEMPLATE.md`](./TASK-TEMPLATE.md) vào `tasks/`, đặt tên theo mã task.
> Trước khi đánh dấu DONE: đối chiếu [`DEFINITION-OF-DONE.md`](./DEFINITION-OF-DONE.md).

---

## Trạng thái

| Icon | Trạng thái |
|---|---|
| ⬜ | TODO — chưa bắt đầu |
| 🔵 | IN PROGRESS — đang làm |
| ✅ | DONE — hoàn thành |
| ⏸️ | BLOCKED — bị chặn, ghi rõ chặn bởi cái gì |
| ❌ | CANCELLED — huỷ, ghi rõ lý do |

---

## Tổng quan các giai đoạn

**Thứ tự này không được đảo.** Lý do ở [DATA-PIPELINE §1](../04-data-sources/DATA-PIPELINE.md).

| GĐ | Tên | Mục tiêu | Cổng chặn để sang giai đoạn sau | Trạng thái |
|---|---|---|---|---|
| **0** | Thủ tục & khởi tạo | Gửi đề nghị cấp phép, chốt các quyết định nền, dựng repo | Repo chạy được, các quyết định đã chốt | 🔵 IN PROGRESS — repo chạy được (Laravel 13 + MySQL 8, `migrate:fresh --seed` xanh); Q1 đã chốt "Lắc Đói" 2026-10-31, [LEGAL-002](./tasks/LEGAL-002.md) đã chốt 2026-10-31 |
| **1** | Xương sống dữ liệu | Schema + bảng tham chiếu + **250 nguyên liệu** + engine calo | Engine calo qua kiểm chứng FNDDS/Nutrition5k | ✅ DONE — schema, engine, **313 nguyên liệu**, SR Legacy, CY R2, FNDDS, Wikidata xong; kiểm chứng đạt 89,7%; nhóm chiên đã tính được sau CALC-004 (2026-10-31) |
| **2** | Trình nhập liệu | Công cụ nhập món (trang quản trị antd) + trợ lý LLM | **≤5 phút/món** — cổng chặn cứng | 🔵 IN PROGRESS — lõi xong (ADM-001…005 ✅); còn ADM-006 (trợ lý LLM, chờ API key) và ADM-007 (đo giờ nhập — cổng ≤5 phút/món chưa đo chính thức) |
| **3** | Sản xuất dữ liệu | 150 món (dùng được) → 500 món (đủ dùng) | ≥500 món Mức 1; đo độ chính xác đạt ≥80% | 🔵 IN PROGRESS — **656 món** (DISH-004 đạt mốc đủ dùng 500 món 2026-10-31; gộp tầm soát cùng ngày −4 món nước mắm trùng → 496, thêm 2 liên kết biến thể; DISH-005 đợt 1 +60 món canh/rau → 556, canh cả 3 miền đều ≥20; DISH-005 đợt 2 +65 món (mặn nhanh 40 + lõi Trung 25) → 621; DISH-005 đợt 3 +35 món (chay 20 + sáng 15) → 656 — **DISH-005 đóng, đủ mọi quota**; **hiệu chuẩn đậu phụ CALC-004 → +1 món hoãn được biên soạn (`dau-phu-chien-sa-ot`) → 657 món**; **LV2-001 đợt 1: 47/50 món nền tảng lên Mức 2 thật** (đối chiếu ≥3 nguồn công thức thật/món; 3 món giữ Mức 1 có lý do); **Phần A sửa bất thường LV2-001 (9 món) + LV2-002 đợt 2: 50/50 món đạt Mức 2 — tổng 97 món Mức 2**; **Phần A phân xử LV2-002 (26 món) + LV2-003 đợt 3: 37/50 món đạt Mức 2 — tổng 134 món Mức 2** (13 món giữ Mức 1 có lý do, chủ yếu do provider tra cứu chặn 429); **Phần A phân xử toàn bộ bất thường LV2-003 (21 món) + 5 nguyên liệu mới null-hồ sơ + LV2-004 đợt 4: 50/50 món đạt Mức 2 — tổng 184 món Mức 2**; QA-002: lần 1 **76,9%**, lần 2 sau Phương án A **77,5%**; lần 3 với tham chiếu chiên độc lập (SR Legacy): **98,1% — ĐẠT mục tiêu ≥80%** (nhóm chiên 92,5%), hiệu chuẩn CALC-004 (thịt/đạm 8→4, củ/quả 12→9) → lần 4: **98,8%**; **LV2-005 đợt 5 (đợt chốt): Phần A phân xử bất thường LV2-004 + 32 món đạt Mức 2 — tổng 216 món Mức 2, CHƯƠNG TRÌNH MỨC 2 ĐỢT 1 HOÀN TẤT (vượt mốc 200)**; **LV2-006 — đợt đầu CHƯƠNG TRÌNH MỞ RỘNG toàn kho theo chỉ đạo chủ dự án: Phần A phân xử bất thường LV2-005 (com-hen nâng) + 39/50 món — tổng 256 món Mức 2**; **LV2-007: Phần A phân xử bất thường LV2-006 + hàng đợi nguyên liệu (rau đắng/trân châu có số; mắm cá + heo quay giữ null) + 45/50 món — tổng 301 món Mức 2, kho 420 nguyên liệu**; còn duyệt 361 công thức mới |
| **4** | Engine gợi ý | Nút Lắc bản chính thức, 3 tầng, có embedding | Độ phủ kho món ≥60% trong 30 ngày | ✅ DONE — REC-001 3 tầng chạy thật ở trang chủ (embedding REC-002 hoãn sang giai đoạn sau, không chặn) |
| **5** | Người dùng | Hồ sơ, TDEE, lịch sử bữa ăn | Gợi ý có tính tới bữa trước và TDEE | ✅ DONE — USR-001…004 chạy thật |
| **6** | Lớp phụ | Đóng góp cộng đồng + ngũ hành (tuỳ chọn) | — | 🔵 IN PROGRESS — đóng góp cộng đồng ✅ (CTR-001); **chương trình Mức 3 đã duyệt 2026-11-01 (chủ dự án uỷ quyền tự chủ triển khai): nền TCM-000 ✅; gieo nhãn TCM-001a ✅ (tầng 1–2); TCM-001b ✅ (thí điểm tầng 3–4: 65 NL/91 dòng, coverage ≥60% đạt 227 món); **TCM-001c đợt 1 ✅ (P-DERIVE + rau/củ/quả/nấm: 149 NL/267 dòng, coverage ≥60% đạt 401 món); TCM-001c đợt 2 ✅ (đạm & thuỷ sản + components cho nguyên liệu chế biến: 195 NL/349 dòng sau hai lần bổ sung, coverage ≥60% đạt 477 món); **TCM-001c đợt 3 ✅ + bổ sung ✅ — ĐỢT VÉT CUỐI, ĐÓNG TCM-001: 227 NL/413 dòng (393 thật + 20 suy diễn), components 27 NL/39 liên kết, 180 NL chưa nhãn đều có bản đồ lý do (57 không tồn tại phân loại · 84 hàng đợi nguồn · 10 lệch định danh · 29 trần chính sách) + 27 NL xử lý bằng components; coverage ≥60% đạt 500 món, 135 món Mức 1 đạt tiêu chí phủ (chỉ thống kê); **VN-001 đợt 1 ✅ (ghi chú văn hoá cho nhóm ứng viên nâng bù: 87/135 món đã có `cultural_note` đạt chuẩn sau census nguồn, 48 chờ có lý do; 87 món đủ 2 tiêu chí nâng bù — chỉ thống kê, chưa nâng); **TCM-PROMOTE-001 ✅ — ĐÃ ÁP ĐƯỜNG NÂNG BÙ: 87 món nâng lên "Mức 2 (có note)" (`level_basis='tcm_compensation'` + `level_note` riêng từng món từ hồ sơ LV2); toàn kho: 472 Mức 2 thường + 87 Mức 2 (có note) + 95 Mức 1; validate 0 🔴, Pest 157/157)**; **VN-001 đợt 2 ✅ (ghi chú văn hoá cho 472 món Mức 2 thường: đã viết 250/465 món trong phạm vi thực tế — 7 file đang dở của phiên song song bỏ qua; 215 chờ có lý do A 201/B 9/C 5; census mở lại 100% URL nguồn 0 chết 0 vi phạm khung, khắc phục 29 cắt/sửa + 23 thay nguồn + 4 gỡ về chờ; thống kê đủ cổng Mức 3 chỉ thống kê: 187/472 Mức 2 thường, toàn kho 274 món; toàn kho 337/654 món có `cultural_note`; validate 0 🔴, Pest 157/157)**; **VN-001 đợt 3 ✅ — ĐÓNG TUYẾN GHI CHÚ: 51/95 món Mức 1 còn lại + 75/82 hồ sơ quả có `cultural_note` (chờ có lý do: món A 23/B 19/C 2, quả 7); 7 file Mức 2 phiên song song giữ tiếp tục bỏ qua; census 128 URL: 0 chết, khắc phục cắt câu 18 file, rà diễn đạt 0 vi phạm; toàn kho 388/654 món + 75/82 quả có ghi chú; đủ cổng Mức 3 (chỉ thống kê): 187 Mức 2 thường, 274 tính cả nâng bù, 301 phủ ≥60% + ghi chú mọi mức; validate 0 🔴, ExportStabilityTest đạt, Pest 160/162 (2 đỏ SwapTrayTest của phiên song song)**; **TCM-PROMOTE-002 ✅ — GÁN MỨC 3 + NÂNG BÙ ĐỢT 2: 187 món Mức 2 thường đủ cổng lên Mức 3 (`completeness_level=3`; 0 dòng code phải sửa — `levelLabel`/bộ lọc admin đã hỗ trợ mức 3, kiểm hiển thị thật qua API); 27 món Mức 1 mới đủ 2 tiêu chí nâng bù lên "Mức 2 (có note)" (`level_note` riêng từng món từ hồ sơ); toàn kho: 187 Mức 3 + 285 Mức 2 thường + 114 Mức 2 (có note) + 68 Mức 1 — khớp 3 cách file · DB · recompute; validate 0 🔴, Pest 162/162 (SwapTrayTest đã xanh), ExportStabilityTest đạt** |

### Vì sao thứ tự này

```
nguyên liệu → engine calo → công cụ nhập → dữ liệu món → nút Lắc
```

Khoảng **250 nguyên liệu** phủ gần hết món Việt thường ngày. Làm sạch chúng trước thì mọi món sau đó
tự tính được calo — món thứ 400 gần như không tốn công thêm. Làm ngược lại là lặp cùng một việc hàng
trăm lần.

**Ước lượng công sức GĐ3:** 500 món × 5 phút ≈ **40 giờ công thuần** — khả thi cho một người làm buổi
tối trong vài tháng. Cũng 500 món đó mà công cụ nhập liệu tệ (15 phút/món) thì thành **125 giờ**, và
dự án sẽ bỏ dở. Đó là toàn bộ lý do GĐ2 tồn tại và có cổng chặn cứng.

---

## GIAI ĐOẠN 0 — Thủ tục & khởi tạo

**Mục tiêu:** khởi động những việc có thời gian chờ dài (giấy phép) và chốt các quyết định mà mọi
thứ khác phụ thuộc vào.

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [DOC-001](./tasks/DOC-001.md) | Viết bộ tài liệu nền của dự án | Tài liệu | ✅ | — | 2026-08-31 |
| [DEC-001](./tasks/DEC-001.md) | Chốt định nghĩa "độ chính xác calo >80%" | Quyết định | ✅ | DOC-001 | 2026-08-31 |
| [LEGAL-001](./tasks/LEGAL-001.md) | ~~Gửi đề nghị xin phép sử dụng dữ liệu Viện Dinh dưỡng~~ | Thủ tục | ❌ | — | Huỷ 2026-08-31 |
| [LEGAL-002](./tasks/LEGAL-002.md) | Chốt giấy phép mã nguồn & dữ liệu của dự án | Quyết định | ✅ | — | 2026-10-31 |
| [SETUP-001](./tasks/SETUP-001.md) | Khởi tạo Laravel + MySQL | Backend | ✅ | — | 2026-08-31 |
| [SETUP-002](./tasks/SETUP-002.md) | Dựng cấu trúc `data/` + `.gitignore` + MANIFEST | Backend | ✅ | SETUP-001 | 2026-08-31 |

**DoD GĐ0:** `php artisan --version` chạy được; kết nối được MySQL 8 với collation `utf8mb4_vi_0900_ai_ci`;
thư mục `data/` đúng cấu trúc; câu hỏi Q1/Q2 ở [PRD §9](../01-prd/PRD.md) đã có câu trả lời.

> ## ⚠️ Đính chính 2026-08-31 — đổi hướng về dữ liệu dinh dưỡng Việt Nam
>
> Bản đầu của ROADMAP ghi *"LEGAL-001 phải gửi ngay trong tuần đầu... đây là rủi ro có thời gian chờ
> dài nhất của dự án."* **Điều đó không còn đúng.**
>
> Chủ dự án đã quyết định **không xin phép Viện Dinh dưỡng**, mà **tự tra cứu từng nguyên liệu, từng
> công thức từ nguồn công khai**, chấp nhận tốn thời gian hơn để đổi lấy độ chính xác và quyền sở
> hữu dữ liệu.
>
> **Hệ quả:** rủi ro "chờ giấy phép" biến mất, thay bằng **chi phí công sức tra cứu** dồn vào
> ING-001 → ING-003. Đổi lại, dự án **sở hữu hoàn toàn** dữ liệu mình biên soạn, không phụ thuộc
> giấy phép của ai. Quy trình tra cứu bắt buộc: [DATA-PIPELINE §7](../04-data-sources/DATA-PIPELINE.md).
>
> Chi tiết quyết định: [LEGAL-001](./tasks/LEGAL-001.md).

---

## GIAI ĐOẠN 1 — Xương sống dữ liệu

**Mục tiêu:** có nền móng để mọi món nhập sau đó tự tính được calo.

### 1a. Schema và dữ liệu tham chiếu

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [SETUP-003](./tasks/SETUP-003.md) | Migration toàn bộ schema theo DATA-MODEL | Backend | ✅ (cột embedding hoãn tới GĐ4) | SETUP-001 | 2026-08-31 |
| [SETUP-004](./tasks/SETUP-004.md) | Thiết lập nền tảng CSDL MySQL 8 + tài khoản mặc định | Backend | ✅ 255/61/7.793, 40/40 test | SETUP-003 | 2026-10-30 |
| [DATA-001](./tasks/DATA-001.md) | Biên soạn cây `regions` Việt Nam | Dữ liệu | ✅ | SETUP-003 | 2026-08-31 |
| [DATA-006](./tasks/DATA-006.md) | Lệnh `lacdoi:import-usda` + `lacdoi:validate-data` | Backend | ✅ | SETUP-003 | 2026-08-31 |
| [DATA-002](./tasks/DATA-002.md) | Nhập USDA SR Legacy + Foundation Foods | Dữ liệu | 🔵 SR Legacy xong (7.793 dòng, 116 ánh xạ khớp/0 lệch); Foundation chưa | DATA-006 | — |
| [DATA-003](./tasks/DATA-003.md) | Nhập USDA FNDDS + SR Links | Dữ liệu | ✅ 333 Food Code / 1.810 dòng | DATA-006 | 2026-08-31 |
| [DATA-004](./tasks/DATA-004.md) | Nhập bảng hệ số hao hụt khi nấu | Dữ liệu | 🔵 thịt/gia cầm xong (163 dòng CY R2, 9 yield suy có nguồn); còn hút nước tinh bột + retention | DATA-006 | — |
| [DATA-005](./tasks/DATA-005.md) | Nhập món Việt từ Wikidata (SPARQL) | Dữ liệu | ✅ 61 món, status=draft | SETUP-003 | 2026-08-31 |
| [DATA-007](./tasks/DATA-007.md) | Lệnh `lacdoi:export-data` — JSON ổn định | Backend | ✅ | SETUP-003 | 2026-08-31 |
| [DATA-008](./tasks/DATA-008.md) | Bảng hệ số phần ăn được (nguyên liệu có xương/vỏ) | Dữ liệu | ✅ khung + 3 hệ số có nguồn; 16 hệ số null chờ phép cân | — | 2026-08-31 |
| [DATA-009](./tasks/DATA-009.md) | Chuẩn hoá tên món: tên chính tiếng Việt có dấu + tên tiếng Anh đúng nghĩa | Dữ liệu | ✅ | ALIAS-001 | 2026-10-31 |
| [DATA-010](./tasks/DATA-010.md) | Vùng miền món chỉ còn 3 miền chính; đặc sản tỉnh/thành thành facet `dac-san:` | Dữ liệu | ✅ | TAXONOMY | 2026-10-31 (137 món cấp tỉnh → miền cha + nhãn đặc sản; API/admin chặn region cấp tỉnh cho món) |

### 1b. Nguyên liệu — phần tốn công nhất

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [ING-001](./tasks/ING-001.md) | 50 nguyên liệu cốt lõi (gạo, thịt, mắm, rau nền) | Dữ liệu | 🔵 | — | Biên soạn xong **55 nguyên liệu** 2026-08-31; **3 tiêu chí DoD chưa kiểm được** vì thiếu công cụ |
| [ING-002](./tasks/ING-002.md) | Mở rộng lên 150 nguyên liệu | Dữ liệu | 🔵 | ING-001 | ✅ **150/150** đạt mốc 2026-08-31; 3 tiêu chí DoD chưa kiểm được vì thiếu công cụ; 5 mục giữ ngoài repo chờ thẩm định MEXT |
| [ING-003](./tasks/ING-003.md) | Mở rộng lên 250 nguyên liệu | Dữ liệu | 🔵 **255/250 đạt mốc 2026-08-31** (125 high/64 medium/28 low/38 null); còn rà soát độc lập + duyệt needs_review | ING-002 | — |
| [ING-004](./tasks/ING-004.md) | Dấu vết duyệt + ràng buộc R10 (cơ chế duyệt dữ liệu) | Backend | ✅ | SETUP-003 | 2026-09-01 |

### 1c. Engine tính calo

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [CALC-001](./tasks/CALC-001.md) | Engine tính dinh dưỡng (hàm thuần tuý) | Backend | ✅ | ING-001 | 2026-08-31 |
| [CALC-002](./tasks/CALC-002.md) | Hệ số hao hụt khi nấu + hấp thụ dầu khi chiên | Backend | 🔵 code xong, chờ dữ liệu DATA-004/008 | CALC-001, DATA-004 | — |
| [CALC-003](./tasks/CALC-003.md) | Hiệu chuẩn khoảng min–max (QT-5) — engine 1.1.0 | Backend | ✅ 1,0% → 80,4% tham chiếu nằm trong khoảng | CALC-001, TEST-002 | 2026-09-01 |
| [CALC-004](./tasks/CALC-004.md) | Hệ số hấp thụ dầu theo nhóm thực phẩm cho món chiên | Backend/Dữ liệu | ✅ | CALC-002 | 2026-10-31 (297/300 món tính được) |
| [TEST-001](./tasks/TEST-001.md) | Test các ràng buộc toàn vẹn R1–R9 | Test | ✅ (remote GitHub đã có; CI sẽ kiểm khi code được push) | SETUP-003 | 2026-08-31 |
| [TEST-002](./tasks/TEST-002.md) | Kiểm chứng engine trên FNDDS + Nutrition5k | Test | 🔵 lần 1 xong: **89,7% trong ±20%** (tiêu chí chính ĐẠT); nhóm chiên chưa đo được → chưa đóng | CALC-002, DATA-003 | — |

### 1d. Có thứ nhìn thấy được

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [UI-001](./tasks/UI-001.md) | Nút Lắc bản thô sơ — giữ động lực | Frontend | ✅ | DATA-005 | 2026-10-30 |

**DoD GĐ1:** 250 nguyên liệu có ánh xạ dinh dưỡng và quy đổi đơn vị; engine tính calo chạy và **qua
kiểm chứng TEST-002**; `migrate:fresh --seed` dựng lại đúng toàn bộ dữ liệu từ `data/`.

> **UI-001 cố ý xấu.** Mục đích duy nhất là có thứ nhìn thấy được sau tháng đầu. Đừng đầu tư vào nó —
> bản chính thức là [UI-002](./tasks/UI-002.md) ở GĐ4.

---

## GIAI ĐOẠN 2 — Trình nhập liệu

**Mục tiêu:** biến việc nhập món từ cực hình thành việc làm được hàng loạt.
**Đây là sản phẩm quan trọng nhất của dự án ở thời điểm này** — quan trọng hơn giao diện người dùng cuối.

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [ADM-001](./tasks/ADM-001.md) | Dựng trang quản trị antd + đăng nhập + phân quyền cơ bản | Backend | ✅ | SETUP-003 | 2026-10-30 |
| [ADM-002](./tasks/ADM-002.md) | Màn hình quản lý nguyên liệu + quy đổi đơn vị | Backend | ✅ | ADM-001 | 2026-10-30 |
| [ADM-003](./tasks/ADM-003.md) | Màn hình nhập món Mức 1 | Backend | ✅ | ADM-001 | 2026-10-30 |
| [ADM-004](./tasks/ADM-004.md) | Màn hình nhập nguyên liệu: bàn phím + calo cập nhật trực tiếp | Backend | ✅ | ADM-003, CALC-002 | 2026-10-30 |
| [ADM-005](./tasks/ADM-005.md) | Cảnh báo trùng món bằng độ giống trigram ngay khi gõ | Backend | ✅ | ADM-003 | 2026-10-30 |
| [ADM-006](./tasks/ADM-006.md) | Tích hợp LLM đề xuất nguyên liệu + ánh xạ | Backend | ⬜ | ADM-004 | — |
| [ADM-007](./tasks/ADM-007.md) | **Đo và đạt ≤5 phút/món** — cổng chặn sang GĐ3 | Nghiệm thu | ⬜ | ADM-006 | — |
| [ADM-008](./tasks/ADM-008.md) | Thống kê số lượng bản ghi + bộ lọc danh sách + trang tổng quan | Backend | ✅ | ADM-001 | 2026-10-31 |

**DoD GĐ2:** đạt toàn bộ 8 yêu cầu Y1–Y8 ở [DATA-PIPELINE §4](../04-data-sources/DATA-PIPELINE.md).
**Chưa đạt Y1 (≤5 phút/món) thì không được mở GĐ3.**

---

## GIAI ĐOẠN 3 — Sản xuất dữ liệu

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [DISH-001](./tasks/DISH-001.md) | 50 món đầu tiên | Dữ liệu | 🔶 | ADM-007 (cổng này được vượt theo chỉ đạo riêng của chủ dự án — kho món đã sản xuất trước khi đo giờ nhập) | 2026-10-31 (món đã có + đã duyệt) |
| [DISH-002](./tasks/DISH-002.md) | Lên 150 món — **mốc app dùng được** | Dữ liệu | 🔶 | DISH-001 | 2026-10-31 (đã lên 200 món; công thức đã được chủ dự án duyệt 2026-10-31) |
| [DISH-003](./tasks/DISH-003.md) | Lên 300 món | Dữ liệu | 🔶 | DISH-002 | 2026-10-31 (đã lên 300 món + hệ nhãn phân loại; công thức đã được chủ dự án duyệt 2026-10-31) |
| [DISH-004](./tasks/DISH-004.md) | Lên 500 món — **mốc đủ dùng** | Dữ liệu | 🔶 | DISH-003 | 2026-10-31 (đã lên 500 món + 20 nguyên liệu mới; chờ duyệt công thức; sau gộp tầm soát: kho còn **496 món** — xem [DATA-AUDIT-FIXES](../04-data-sources/DATA-AUDIT-FIXES-2026-10-31.md)) |
| [DISH-005](./tasks/DISH-005.md) | Lấp ô trống có chọn lọc (canh/rau/mặn nhanh/Trung/chay/sáng) | Dữ liệu | ✅ | DISH-004 | 2026-10-31 (đợt 1 xong: +11 nguyên liệu — 7 có số, 4 null có hồ sơ; +60 món = 30 canh (Bắc 8/Trung 18/Nam 4 — canh 3 miền đều ≥20) + 30 rau; 60/60 tính được; 7 món hoãn/đổi vì nguyên liệu chưa có số đạt chuẩn, ghi rõ trong file task. Đợt 2 xong cùng ngày: +6 nguyên liệu — 3 có số (ca-bac-ma DKBM low, long-ga SR, banh-uot SR), 3 null có hồ sơ (heo-quay, mam-tom-chua, oc-hut); +65 món = mặn nhanh 40 (5 thay phân xử: 4 trùng món đã có + 1 nguyên liệu null) + lõi Trung 25 (Huế 9/vn-central 9/Quảng Nam 5/Nghệ An 2; 4 biến thể parent, 3 thay vì nguyên liệu null); 65/65 tính được; nhóm chiên mới 5/5 trong ±20% theo tham chiếu độc lập QA-002 lần 3; chờ duyệt công thức cùng 200 món DISH-004 — tổng 325). **Đợt 3 xong cùng ngày — ĐÓNG DISH-005:** +3 nguyên liệu (nam-dui-ga SR cùng chi + tra chéo MEXT, suon-chay SR TVP low, cha-lua-chay null có hồ sơ); +35 món = chay 20 (**kiểm chứng DB: 20/20 suy đúng diet:chay** — cổng cứng của nhóm thiếu nhất kho) + sáng tự nấu 15 (1 biến thể của xoi-xeo); 35/35 tính được; G5 5 món chiên mới trong ±20% (lệch −0,2%…−0,6%); G6 rà soát độc lập bắt 8 nhóm lỗi chắc chắn, đã sửa hết trước khi đóng (nổi bật: mi-can là mì căn/seitan chứ không phải sợi mì; cháo đậu đen ngọt về loai che-trang-mieng); 2 món hoãn (cha-chay-kho-dua — nguyên liệu null; dau-phu-chien-sa-ot — hệ số dầu đậu phụ 15 chưa được tham chiếu độc lập hậu thuẫn, lệch +24%, để lại cho hiệu chuẩn CALC-004). **Tổng DISH-005: 496 → 656 món, +160 món, nguyên liệu 386 → 406; 360 công thức chờ chủ dự án duyệt** |
| [LV2-002](./tasks/LV2-002.md) | Nâng Mức 2 đợt 2 — 50 món thường ngày + sửa bất thường LV2-001 (Phần A) | Dữ liệu | ✅ (50/50; Phần A 9 món) | LV2-001 | 2026-10-31 — **Phần A:** phân xử 12 bất thường của LV2-001: 9 món sửa (thêm thịt ba chỉ vào ca-tram-kho-rieng, rau đay vào canh-cua-mong-toi, hành tây vào muc-xao-can-toi, cà chua vào canh-mang-ca, đổi nền chua canh-hen sang khế, bì/chả com-tam, gỏi + lòng gà com-ga-hoi-an, hoa hồi/quế bo-kho, bỏ lá lốt ech-xao-sa-ot); 2 ca giữ nguyên có lý do (măng chua chưa có số đạt chuẩn; rau thơm ca-tram-kho-rieng chỉ 1 nguồn); calo toàn kho đúng 9 món đổi, lớn nhất ca-tram-kho-rieng +85,1%. **Đợt 2: 50/50 món đạt Mức 2 thật** (22 chính + 12 canh + 12 bún/phở/mì + 4 cơm; 150 nguồn đã mở kiểm chứng; 14 dòng định lượng sửa ở 13 món, ghi phân xử trong `import_note`; giữ `needs_review` + `source`/`confidence` từng dòng). Kiểm chứng: validate 1.145 bản ghi 0 🔴 (V12: **97 món Mức 2**), đối chiếu calo toàn kho đúng 13 món đổi số — 6 món lệch >10% đều do phân xử định lượng, Pest 115/115, rà độc lập 10/10 nguồn mẫu khớp. ~35 bất thường mới ghi trong LV2-002 (nổi bật: ca-dieu-hong-sot-ca sai method kho/chiên; bun-bo-nam-bo nghi sai vùng miền; banh-tam-bi thiếu bột nếp 3/3 nguồn). **Phần A phân xử (2026-10-31, commit riêng):** 26 món sửa dòng/phân loại — ca-dieu-hong-sot-ca đổi method → chien (+gán oil_abs 9 cho ca-dieu-hong; kiểm tham chiếu độc lập QA-002 lần 3: lệch +17,0%, trong ±20%), bun-bo-nam-bo đổi vùng → vn-north + dac-san:ha-noi (quy ước vùng gốc, 4 nguồn độc lập), thêm dòng đồng thuận 3/3 và 2/3 (banh-tam-bi, hu-tieu, ca-ro-kho-to +40 g ba chỉ, mam-kho, bun-rieu-cua…), sửa ánh xạ bun-cha đu đủ xanh, vit-om-sau khoai sọ; còn lại giữ nguyên có lý do (mẻ/giấm bỗng/nước màu/giò sống chờ biên soạn nguyên liệu). Calo đổi đúng 26 món, lớn nhất ca-ro-kho-to +52,7%; validate 0 🔴; Pest 121/121 |
| [LV2-003](./tasks/LV2-003.md) | Phân xử bất thường LV2-002 (Phần A) + Nâng Mức 2 đợt 3 — 50 món thường ngày | Dữ liệu | ✅ (Phần A đủ; đợt 3: 37/50) | LV2-002 | 2026-10-31 — **Phần A:** xem dòng LV2-002 (commit riêng `57fb628`). **Đợt 3: 37/50 món đạt Mức 2 thật — tổng 134/657 món Mức 2** (26 chính + 12 canh + 5 cơm + 7 bún/mì chọn mới; ~150 nguồn đã mở kiểm chứng qua 10 nhóm nghiên cứu song song; 22 món sửa định lượng theo phân xử ≥2 nguồn — lớn nhất bun-thit-nuong thịt 70→140 g, bo-xao-can-toi bò 120→60 g; giữ `needs_review` + `source`/`confidence` từng dòng). **13 món giữ Mức 1 có lý do:** 8 món bị provider tra cứu chặn 429 nên không đủ 3 nguồn đã mở (muc-rim-me, suon-non-kho-tieu, suon-rang-muoi, thit-vien-sot-ca, trung-duc-thit, hen-xao, long-lon-xao-dua-chua, canh-rau-ma-tom), 2 món chờ phân xử nhóm "rang muối" (ga-rang-muoi-sa, tom-rang-muoi), 3 món thiếu nguồn đúng biến thể/độc lập (dau-phu-kho-nam, canh-ca-ro-khe, com-chien-dua-trung). Kiểm chứng: validate 1.145 bản ghi 0 🔴 (V12: **134 món Mức 2**), đối chiếu calo 50 món — 11 món lệch >10% đều do phân xử định lượng, 13 món giữ Mức 1 đổi 0,0%, Pest 121/121, rà độc lập 10/10 mẫu khớp. Bất thường mới (thiếu mắm ruốc ở bun-bo-gio-heo 4/4 nguồn, cần tàu ở canh-ca-nuc-nau-ngot 3/3, nhóm rang muối, 2 nguyên liệu cần biên soạn mới: bạc hà/dọc mùng + bột chiên giòn) ghi trong LV2-003 chờ pha phân xử |
| [LV2-004](./tasks/LV2-004.md) | Phân xử bất thường LV2-003 (Phần A) + Nâng Mức 2 đợt 4 — 50 món thường ngày | Dữ liệu | ✅ (Phần A đủ; đợt 4: 50/50) | LV2-003 | 2026-10-31 — **Phần A (commit riêng `0daf1e0`):** phân xử toàn bộ §4 của LV2-003 — 21 món sửa dòng (mắm ruốc ở bun-bo-gio-heo 4/4, cần tàu 3/3, bắp cải 3/3, gừng 3/3…), đính chính dọc mùng đã có số từ DISH-005, biên soạn **5 nguyên liệu mới đều null + hồ sơ** (bot-chien-gion, me, giam-bong, nuoc-mau, gio-song — không số nào đạt chuẩn từ nguồn đã mở), xử lý các ca chờ (cha-ca-la-vong/gia-cay + mẻ, bun-oc → giấm bỗng), thử lại 8 món 429: 5 món đủ nguồn, suon-rang-muoi đủ nguồn nhưng chờ quyết nhóm, hen-xao + long-lon-xao-dua-chua vẫn 429. **Đợt 4: 50/50 món đạt Mức 2 thật — tổng 184/657 món Mức 2** (5 món A.3 + 38 chọn mới + 7 dự phòng; 3 món đạt có điều kiện về họ nguồn đã ghi caveat). 12 ứng viên giữ Mức 1 có lý do (5 ca lệch định danh chờ quyết, 4 món thiếu nguồn đúng món, 3 món 429). Phân xử lớn nhất: dau-phu-rim-nam đổi sang đậu phụ chiên (+125,7% calo), thit-vien-sot-ca đổi method sang chiên, ca-trich-kho sửa bước kho 15 phút → 1,5–2 giờ. Kiểm chứng: validate 0 🔴 (V12: **184 món Mức 2**), recompute **652/657**, đối chiếu calo bằng baseline tái dựng từ commit Phần A — cách ly sạch, 21/50 món đổi >10% đều truy về dòng phân xử, Pest **124/124**, rà độc lập 10/10 mẫu khớp (sửa 1 trích dẫn sai ở ca-doi-kho-tieu). Bất thường mới chờ quyết: mẫu hình rau xào nêm mắm toàn kho, đường thốt nốt ở cá kho, vùng ca-tram-hap-xi-dau, quy ước lượng sợi, nhóm rang muối (đã đủ hồ sơ cả 3 món) |
| [LV2-005](./tasks/LV2-005.md) | Phân xử bất thường LV2-004 (Phần A) + Nâng Mức 2 đợt 5 — ĐỢT CHỐT 200 món | Dữ liệu | ✅ (Phần A đủ; đợt 5: 32 món; **tổng 216/657 — chương trình Mức 2 đợt 1 HOÀN TẤT, vượt mốc 200**) | LV2-004 | 2026-10-31 — **Phần A (commit riêng `1267570`):** vùng ca-tram-hap-xi-dau → Bắc theo quy ước gốc xuất xứ (4/4 nguồn kiểu Bắc, nguồn ghi nhận là thương hiệu Hà Nam); 5 ca định danh: thit-bam-rim xác nhận có thật (giữ Mức 1 chỉ vì 429), ga-rim-nuoc-dua phân xử nhánh nước mắm ≠ rôti + nâng Mức 2, com-tam-long-xuyen thay cấu trúc Sài Gòn bằng khìa thịt nạc vai + nước dừa (kèm nâng), bau-xao-tep + com-nam-huong giữ chờ chủ dự án kèm phân tích; long-lon-xao-dua-chua thử lại thành công (+cà chua 3/3, lòng 150→100g); canh-hen-rau-lang bỏ gừng 0/3 nguồn + viết lại quy trình đãi hến. **Đợt 5: 32 món đạt Mức 2 thật — tổng 216/657** (14 dòng định lượng sửa ở 11 món). 13 ứng viên giữ Mức 1 có hồ sơ: 11 món 429, com-tho (vẫn thiếu nguồn đúng biến thể), com-hen (gom đủ 3 nguồn nhưng nguồn thứ ba là bài forum trên đường dẫn bị chèn spam — phân xử không tính đạt chuẩn; chỉ còn thiếu 1 nguồn sạch). Kiểm chứng: validate 1.796 bản ghi 0 🔴 (V12: **216 món Mức 2**), recompute 216 món có khoảng, đối chiếu calo baseline tái dựng từ commit Phần A — 186 món cũ 0 món đổi điểm giữa, Pest **124/124**, rà độc lập 10/10. Quyết định còn chờ chủ dự án gom tại LV2-005 §4 (5 quy ước cấp kho + 4 ca cấp món) |
| [LV2-006](./tasks/LV2-006.md) | Phân xử bất thường LV2-005 (Phần A) + Nâng Mức 2 đợt 6 — ĐỢT ĐẦU chương trình mở rộng toàn kho | Dữ liệu | ✅ (Phần A đủ; đợt 6: 39/50; **tổng 256/657**) | LV2-005 | 2026-10-31 — **Phần A (commit riêng `a9c48d0`):** com-hen NÂNG MỨC 2 (nguồn thứ ba sạch thay nguồn forum bị chèn spam SEO; +dọc mùng 24g 3/3, bỏ tép 0/3, mắm ruốc 5→12g); vit-xao-mang method xao→nau (3/3 nguồn là món nấu) + gừng/ngò gai; bun-ca +bột năng/giấm bỗng; banh-canh-cua +sườn 65g, trứng cút → tuỳ chọn; mi-quang-ga +sả 8g; bun-mam GIỮ + hồ sơ (mắm cá chưa có số — hàng đợi biên soạn nguyên liệu). **Đợt 6: 39/50 món đạt Mức 2 thật — tổng 256/657** (thử lại 429 đợt 5: 6/13 nâng; bắt đầu nhóm bánh/xôi/chè: 13 món). 11 món giữ Mức 1 có hồ sơ: 10 do 429 (nổi bật bun-doc-mung thiếu hẳn mọc), thit-bam-rim chỉ 1 nguồn định lượng. Kiểm chứng: validate 1.996 bản ghi 0 🔴 (V12: **256 món Mức 2**), recompute 256 món có khoảng 0 thiếu, đối chiếu baseline 216 món — chỉ 4 món Phần A đổi >1% đều truy về dòng phân xử, Pest **124/124**, rà độc lập 7 nguồn mở lại khớp + 5 món xôi/chè có toàn văn nguồn từ pha nghiên cứu. Danh sách chờ quyết LV2-005 §4 không bị chạm |
| [LV2-007](./tasks/LV2-007.md) | Phân xử bất thường LV2-006 + hàng đợi nguyên liệu (Phần A) + Nâng Mức 2 đợt 7 — chương trình mở rộng toàn kho | Dữ liệu | ✅ (Phần A đủ; đợt 7: 45/50; **tổng 301/657**) | LV2-006 | 2026-10-31 — **Phần A (commit riêng `0cf3bfc`):** phân xử 7 ca bất thường LV2-006 (nui-xao-bo giữ tương cà; che-thai giữ sữa đặc 30g/ly theo biến thể trong file + bổ sung tran-chau 20g; xoi-xeo giữ mỡ lợn; muc-rim-me +tương ớt 5g; canh-kho-qua-ca-loc giữ trường phái (b) + rau mùi; canh-rau-ma-thit-bo bò xay→thăn + hành tím — thay đổi này bị commit của phiên IMG song song cuốn vào HEAD, đã kiểm chứng; thit-xao-mam-ruoc vùng → Trung + tỏi) và hàng đợi nguyên liệu §3: rau-dang CÓ SỐ (FNRI PhilFCT, *Glinus oppositifolius*), tran-chau CÓ SỐ (USDA 169717), mam-ca-sac/mam-ca-linh/heo-quay giữ null có hồ sơ (linh sửa lỗi định danh *Henicorhynchus siamensis*), muoi-tom + cu-nen slug mới null, "củ sắn" = alias của cu-dau (không tạo slug); bun-mam/lau-mam VẪN chưa thêm dòng mắm cá (hai mắm còn null). **Đợt 7: 45/50 món đạt Mức 2 thật — tổng 301/657** (không gặp 429; thử lại 429 đợt 6 đạt 9/10 — chỉ dau-phu-kho-gung giữ vì biến thể chay/mặn chờ quyết; sửa định danh lớn: chao-ngheu nghêu 100→40g cơ sở thịt, goi-tom-thit củ sen→ngó sen, banh-duc xác định là bản nóng + vùng Bắc, banh-tieu Phương án A + tạo slug men-no/bot-no có số USDA; biên soạn 6 nguyên liệu mới: bot-khoai, gelatin, kem-whipping, men-no, bot-no có số + cha-bong null — kho 420 nguyên liệu). 5 món giữ Mức 1 có hồ sơ (dau-phu-kho-gung biến thể, sup-ca định danh, banh-bo chờ chọn chất nở, xoi-man chờ số cha-bong, kho-qua-luoc thiếu nguồn đúng món). Kiểm chứng: validate 2.285 bản ghi 0 🔴 (V12: **301 món Mức 2**), recompute 301 món tính đủ, đối chiếu calo cách ly — trong 256 món có số từ trước chỉ che-thai đổi >10% đúng phân xử Phần A, Pest **127/127**, rà độc lập 10/10 mẫu khớp, needs_review nguyên vẹn 50/50. Quyết định mới chờ chủ dự án gom tại LV2-007 §3 (15 mục); danh sách LV2-005 §4 không bị chạm |
| [LV2-008](./tasks/LV2-008.md) | Phân xử bất thường LV2-007 (Phần A) + Nâng Mức 2 đợt 8 — chương trình mở rộng toàn kho | Dữ liệu | ✅ (Phần A đủ; đợt 8: 45/60 xét; **tổng 346/657**) | LV2-007 | 2026-10-31 — **Phần A (commit riêng `9628ad1`):** `cha-bong` thử tra nhãn sản phẩm đạt chuẩn (SEO vô danh tự mâu thuẫn Atwater, Wikipedia không trích dẫn, nhãn OFF là chà bông Đài Loan nhiều đường) — **VẪN null, `xoi-man` giữ Mức 1**; `hung-lui` biên soạn mới CÓ SỐ (USDA SR 173475) + bổ sung dòng 3g cho `goi-xoai`; khép 2 bất thường chờ phân xử sau (bun-mang-ga giữ nấm hương 5g, banh-bot-loc giữ hành lá 3g — nguồn đối trọng đều chỉ 1 nguồn); rà lại hàng đợi §3.14 (hanh-tay/nam-huong-kho đã có số từ trước). **Đợt 8: 45/60 món xét đạt Mức 2 thật — tổng 346/657** (50 chính + 10 dự phòng; sửa cấu trúc lớn: bun-bung thêm dọc mùng/giò sống/mọc, bun-ngan sửa loài vịt→ngan, com-dua-bo định danh cơm rang + method chien, cha-chia thêm mực khô định danh; tránh toàn bộ file phiên IMG song song đang sửa dở). 15 món giữ Mức 1 có hồ sơ: 5 do 429, bun-nuoc-leo trần mắm cá null (nhập hàng đợi cùng bun-mam/lau-mam), 7 thiếu nguồn đúng biến thể, ca-bong-om-chuoi chờ chủ dự án xác nhận có thật (nghi bịa DISH-004), canh-rau-den-tep nhập nhóm chờ quyết tép khô/tươi. Kiểm chứng: validate 2.542 bản ghi 0 🔴 (V12: **346 món Mức 2**), recompute 346 món tính đủ 0 thiếu, đối chiếu calo cách ly — 34 món đợt đổi điểm giữa đều truy về dòng phân xử, **0 món ngoài đợt đổi**, Pest **127/127**, rà độc lập 10/10 mẫu khớp. Bất thường mới chờ quyết gom tại LV2-008 §3 (thiếu slug chả cốm/bông súng/ngải bún; bột chiên giòn vs bột mì; serving nhóm cá chiên khúc) |
| [LV2-009](./tasks/LV2-009.md) | Phân xử bất thường LV2-008 (Phần A) + Nâng Mức 2 đợt 9 — chương trình mở rộng toàn kho | Dữ liệu | 🔵 (Phần A ✅; Phần B đang triển khai) | LV2-008 | 2026-10-31 — **Phần A:** phân xử toàn bộ §3 của LV2-008: biên soạn 4 slug thiếu đều **null + hồ sơ** (`cha-com` — USDA Branded 0 kết quả, cụm số web gãy cân bằng khối lượng; `bong-sung` — văn liệu chỉ có số mẫu sấy khô, lệch nhau ~30 lần ở xơ; `ngo-om` — không FCT nào có mục tươi; `ngai-bun` — danh tính chốt *Boesenbergia rotunda*, bộ số "krachai" trên web là số của gừng sao chép); bổ sung dòng chả cốm 75 g cho `bun-dau-mam-tom`, bông súng 65 g + ngò om 3 g cho `canh-ca-linh-bong-dien-dien`; **thay dòng sai loài** `ngo-gai` → `ngai-bun` 5 g ở `bun-nuoc-leo` (món vẫn Mức 1 — trần mắm cá); cần tàu dùng slug sẵn có `rau-can-ta`, lá gừng giữ ghi nhận không biên soạn; 4 câu hỏi cấp kho §3.4 giữ nguyên chờ chủ dự án. Kho 421 → **425 nguyên liệu**. Kiểm chứng Phần A: validate 2.760 bản ghi 0 🔴, recompute 346 món tính đủ (DB xác nhận missing mới), Pest **127/127** |
| [LV2-010](./tasks/LV2-010.md) | Phân xử bất thường LV2-009 (Phần A) + Nâng Mức 2 đợt 10 — chương trình mở rộng toàn kho | Dữ liệu | ✅ XONG | LV2-009 | 2026-10-31 — **Phần A:** phân xử §B.3/§B.5 của LV2-009: áp `banh-goi` → vn-north + `dac-san:ha-noi` (ghi rõ tranh chấp gốc Hải Phòng), `che-dau-trang` bot-nep → gao-nep; giữ có lý do: bột chuoi-chien (không quy đổi được khẩu phần), rau đắng lau-ca-keo (1/3 họ), nhãn dac-san:phu-quoc/quang-binh (ngoài 14 giá trị TAXONOMY — chờ chủ dự án). Biên soạn 8 slug hàng đợi: CÓ SỐ `sach-bo` (USDA SR 170599, low), `bo-vien` (Branded 2623419, low, + allergen:trung), `thit-bo-ba-chi` (SR 174740, low, khoảng 194–371), `cu-nang` (SR 170066, high), `vani` (SR 173471, high, dạng chiết xuất — liều vết); NULL + hồ sơ `gan-bo-tendon`, `tuong-hat`, `mia`. Bổ sung dòng: lau-bo +4 (gân null vào missing theo tiền lệ phụ null, món giữ Mức 2), che-com + vani 1 g, che-hat-sen-long-nhan + vani 0,5 g (quy ước liều vết); đính chính tia-to chao-luon-nghe-an chỉ 1/3 họ → không thêm. Kho 425 → **433 nguyên liệu**. Validate mức file 3.336 bản ghi 0 🔴. **Phần B:** 23/50 món đạt Mức 2 (gồm 4 món cứu ở vòng 2 mở nguồn tuần tự) — **TỔNG 400/657 Mức 2**; 27 giữ có hồ sơ theo nhóm; validate đầy đủ cả DB 0 🔴, Pest 127/127, đối chiếu calo cách ly sạch |
| [LV2-011](./tasks/LV2-011.md) | Phân xử bất thường LV2-010 §B.3 (Phần A) + Nâng Mức 2 đợt 11 — chương trình mở rộng toàn kho | Dữ liệu | ✅ XONG | LV2-010 | 2026-10-31 — **Phần A:** rà 12 mục §B.3: không ca nào đủ bằng chứng hồ sơ để phân xử (11 ca chờ chủ dự án, 2 ca chuyển Phần B); `dau-bap-xao-toi` hoãn A4.1 vì file còn dở của phiên song song; tổng hợp danh sách chờ quyết 21 mục tại §A.3. **Phần B:** 15/50 món đạt Mức 2 (4 thử lại 429 đợt 10 + 11 món gom đủ họ lần đầu) — **TỔNG 421/654 Mức 2**; 13 dòng định lượng sửa, 3 thêm, 2 bớt — mọi thay đổi truy về đồng thuận ≥2 họ; 35 món giữ có hồ sơ (22 thiếu nguồn, 11 biến thể, 2 trần nguyên liệu); validate 0 🔴, recompute 421/421, Pest 127/127, đối chiếu calo cách ly sạch |
| [LV2-012](./tasks/LV2-012.md) | Đợt vét cuối + tổng kết chương trình mở rộng Mức 2 toàn kho | Dữ liệu | ✅ XONG — CHƯƠNG TRÌNH ĐÓNG | LV2-011 | 2026-10-31 — Bảng kê 233 món Mức 1 (173 chưa từng xét / 60 đã giữ có hồ sơ); xét toàn bộ nhóm chưa từng xét + thử lại 12 món + vòng 2 nhịp chậm: **54 món đạt Mức 2 — TỔNG 475/654 Mức 2**; 131 món giữ có hồ sơ mới (33 bị chặn 429 chưa kết luận); bổ sung `oil_absorption_pct` 9 cho nguyên liệu `be-be` theo CALC-004 nhóm hải sản; danh sách chờ quyết bản cuối 42 mục + hàng đợi nguyên liệu null tại LV2-012 §4/§6; validate 0 🔴, recompute 475/475, đối chiếu calo cách ly sạch, Pest 127/127, rà độc lập 10/10 |
| [AUDIT-002](./tasks/AUDIT-002.md) | Rà soát toàn bộ (census) sau chương trình Mức 2 — 100% món/nguyên liệu/quả/media/URL nguồn, kiểm 2 lượt | QA / Dữ liệu | ✅ XONG (phần rà soát) — danh sách phân xử PX-01…PX-35 chờ chủ dự án quyết | LV2-012 | 2026-10-31 — 2.185 URL census + 407 URL mở lại lượt 2 (333 đúng / 10 sai / 24 chết / 40 chưa mở được); 16 món rớt cổng nguồn vì link rot (PX-01…16), nhóm họ nguồn chờ quy ước chung (PX-17…25), cặp canh hến trùng tên (PX-26), FDC rot 260 URL (PX-28); sửa cơ học duy nhất: 6 file nguyên liệu `oil_absorption_pct` theo CALC-004 (đúng 5 món đổi khoảng kcal); validate 0 🔴, recompute 475/475, Pest 127/127 |
| [AUDIT-FIX](./tasks/AUDIT-FIX.md) | Pha khắc phục sau AUDIT-002 — nhóm A (PX-01…16), PX-17, PX-34, PX-28 | QA / Dữ liệu | ✅ XONG — **tổng 472/654 Mức 2** | AUDIT-002 | 2026-11-01 — Nhóm A: 13 món khôi phục cổng bằng nguồn thay thế đã mở kiểm chứng (không sửa định lượng), 3 món hạ Mức 1 có hồ sơ (`canh-khoai-so`, `dau-phu-kho-chao`, `canh-rau-lang-tom`); PX-17: loại nguồn sai loài rô phi + sửa tỉ lệ bột theo Memart (đúng 1 món đổi kcal cách ly: 756,7 → 1.027,0 điểm giữa); PX-34: mở lại toàn bộ — 9 URL kết luận sống, 0 chết; PX-28: 286 URL FDC → mẫu API USDA NAL (kiểm chứng 11/11 trước sửa, 260/260 sau sửa, số không đổi); validate 0 🔴, recompute 472/472, Pest 127/127, rà độc lập 22/22 nguồn mới (19 đạt, 3 truy vết) |
| [AUDIT-ALLERGEN](./tasks/AUDIT-ALLERGEN.md) | Rà soát census toàn bộ thông tin dị ứng (654 món / 434 NL / 82 quả / 11 nhóm) + nguyên nhân gốc cờ cá — chỉ đọc, không sửa | QA / Dữ liệu | ✅ XONG — rà soát + **ALLERGEN-FIX (FIX-A1…A4) đã áp**; ALG-Q1…Q4 chờ chủ dự án quyết | TCM-PROMOTE-002 | 2026-11-01 — 2 phương pháp độc lập khớp tuyệt đối, tầng cơ học 0 lệch, validate 0 🔴. Cờ cá 388 món = 109 cá thật + **274 chỉ nước mắm/mắm cá (chính sách TAXONOMY §4 → ALG-Q1)** + 5 giò/chả quy ước + 0 không giải thích. 🔴 Rò rỉ lọc cứng: preset slug Lắc/Hồ sơ đóng băng từ REC-001 — thiếu chip Cá/Động vật có vỏ/Mè; đo `pool()` thật còn lọt đậu nành 36, tôm 22, lúa mì 11, mắm tôm 9, trứng 7, sữa 5, cua 3 (danh sách đầy đủ theo luật thì rò 0). Gắn thiếu dữ liệu duy nhất: `cha-bong` thiếu cờ cá (0 món bị ảnh hưởng hiện tại). **FIX (2026-11-01): preset sinh từ TaggingRules qua `AllergenPreset`, đủ 11 chip; đo lại pool() rò 0 cả 11 nhóm; `cha-bong` đã gắn cá; luật tiền tố `ca-` phủ test liệt kê; TAXONOMY §4 sửa câu mắm nêm; Pest 167/167** |
| [AUDIT-004](./tasks/AUDIT-004.md) | Kiểm tra độc lập độ chính xác giá trị tính/vị/ngũ hành bằng tái suy dẫn mù + tính lại tổng hợp độc lập — chỉ đọc, không sửa | QA / Dữ liệu | ✅ XONG (phần rà soát) — **FIX-TCM4-1/2/3 ĐÃ ÁP** (FIX-TCM4-4 thuộc AUDIT-003) | TCM-PROMOTE-002 | 2026-11-01 — Mù 223/227 NL (~490 findings); đối chiếu 413 dòng: **sai giá trị 0**, xác minh 413/413 (208 khớp mù · 183 lượt 2 mở đúng ref · 20 P-DERIVE · 2 giá trị đúng/trích dẫn khuyết); 13 dòng cờ mâu thuẫn lượt 1 đều đúng theo cổ thư đã trích. Script độc lập: P-DERIVE 20/20, element 413/413, tổng hợp 0/648 lệch (phủ 500/98/50); API hiển thị 0/881 lỗi. 🟠 `dau-do-hat`: trang tcmfans dẫn làm bản kiểm chứng ghi ngược biến thể — giá trị vẫn đúng theo Dược điển |
| [LINK-ROT](../../tools/link-rot-check/README.md) | Quét link nguồn định kỳ chống link rot — toàn bộ URL nguồn trong `data/`, kỷ luật 2 lượt của AUDIT-002, so sánh thay đổi qua `state.json`, cảnh báo món Mức 2 rớt cổng 3 nguồn sống | QA / Dữ liệu | 🔵 ĐANG VẬN HÀNH — baseline 2026-11-01 | AUDIT-002 | 2026-11-01 — script `tools/link-rot-check/link_rot_check.py` (Python 3 + curl, chỉ báo cáo, không sửa dữ liệu); lệnh chạy 1 lần và cách đọc báo cáo tại `tools/link-rot-check/README.md`; lịch định kỳ đăng ký bằng cron của Muse sau khi nghiệm thu baseline; **LINK-ROT-FIX 2026-11-01: 4 món rớt cổng vì rot mới của baseline (banh-uot-long-ga, canh-bi-ro-suon, canh-rau-ngot-thit-bam, mien-mang-ga) đã thay nguồn — cả 4 khôi phục đủ 3 nguồn xác nhận sống, 0 món đổi định lượng, tổng Mức 2 giữ nguyên 472/654 — xem [AUDIT-FIX](./tasks/AUDIT-FIX.md) §8** |
| [EXPORT-STABILITY](./tasks/EXPORT-STABILITY.md) | Điểm cố định vòng seed→export toàn cây `data/` — chẩn đoán + sửa exporter phía món | Hạ tầng dữ liệu | ✅ XONG — **0/1.176 file lệch** | TCM-001a | 2026-11-01 — census 1.176 file JSON: phía nguyên liệu/quả vốn đã điểm cố định; phía món lệch do exporter (sắp lại thứ tự dòng theo nội dung thay vì thứ tự biên tập/`id`, DECIMAL+float in `480.0`, rớt `is_optional: false` ở 5.306 dòng) + 2 lỗi phía file (`dau-phu-kho-gung` lệch khuôn, `dishes/_index.json` lỗi thời từ thời 61 món). Đã sửa exporter + chuẩn hoá cơ học 23 file (ngữ nghĩa không đổi); test toàn cục trong `ExportStabilityTest` là cổng bắt buộc trước mọi export toàn cục; validate 0 🔴, Pest 154/154 |
| [LV2-001](./tasks/LV2-001.md) | Nâng Mức 2 đợt 1 — 50 món nền tảng đối chiếu nguồn công thức thật | Dữ liệu | ✅ (47/50) | DISH-005 | 2026-10-31 — **47/50 món đạt Mức 2 thật**: mỗi món ≥3 nguồn công thức đã mở kiểm chứng, định lượng đối chiếu ≥2 nguồn (9 dòng sửa ở 6 món, đều có lý do trong `import_note`), bước nấu viết lại 6–10 bước, 1 sửa phân loại (banh-trang-cuon-thit-heo song→luoc); giữ `needs_review` + `source`/`confidence` từng dòng chờ chủ dự án duyệt cuối. **3 món giữ Mức 1 có lý do:** com-hen (chỉ 2 nguồn đạt chuẩn có định lượng), canh-nghe-ca-ro (không tồn tại nguồn đúng món — chờ chủ dự án xác nhận), com-tho (thiếu 3 nguồn cho đúng biến thể). Kiểm chứng: validate 1.145 bản ghi 0 🔴 (V12 đạt), đối chiếu calo trước/sau toàn kho đúng 7 món đổi số — 5 món lệch >10% đều do phân xử định lượng đã ghi, Pest 115/115, rà độc lập 10/10 nguồn mẫu khớp. 12 bất thường (thiếu dòng nguyên liệu ở nguồn đồng thuận, đầu bảng: ca-tram-kho-rieng thiếu thịt ba chỉ) ghi trong file task chờ chủ dự án phân xử. Đi kèm: hiệu chuẩn đậu phụ CALC-004 (15→10) + biên soạn `dau-phu-chien-sa-ot` — kho 657 món, 361 chờ duyệt |
| [QA-001](./tasks/QA-001.md) | Rà soát chất lượng định kỳ mỗi 50 món | QA | 🔵 đợt rà toàn kho 500 món xong 2026-10-31 (task lặp lại, không đóng) | DISH-001 | — |
| [QA-002](./tasks/QA-002.md) | Chạy đo độ chính xác calo theo ACCURACY-SPEC | QA | ✅ 2026-10-31 — lần 1: **76,9%, chưa đạt** (nhóm chiên 7,5%, dầu tính hai lần); Phương án A + lần 2: **77,5% — vẫn chưa đạt** (tham chiếu nguyên văn không tương thích định nghĩa); chủ dự án duyệt làm cả hai bước tiếp → lần 3 (tham chiếu chiên **độc lập** từ SR Legacy): **98,1% — ĐẠT** (nhóm chiên 92,5%); rà soát CALC-004: thịt/đạm 8→4, củ/quả 12→9; lần 4 (hiệu chuẩn): **98,8%**, nhóm chiên 95,0% — **mục tiêu DEC-001 đạt**; **lần 5 (2026-11-01, mẫu MỚI seed 20261101 trên kho cuối 654 món): 98,1% (157/160) — TÁI XÁC NHẬN ĐẠT** (không chiên 100%; chiên 92,5% với dư sau cấu trúc −0,21 điểm % ⇒ không hiệu chuẩn) | DISH-003 | 2026-11-01 |
| [FRUIT-001](./tasks/FRUIT-001.md) | Schema + hạ tầng dữ liệu nhánh hoa quả | Dữ liệu | ✅ | — | 2026-10-31 (hạ tầng + dữ liệu FRUIT-003/004 hoàn tất) |
| [FRUIT-002](./tasks/FRUIT-002.md) | Bổ sung nguyên liệu quả còn thiếu (53 loại — danh sách chốt khi triển khai) | Dữ liệu | ✅ | — | 2026-10-31 (85 nguyên liệu trai-cay: 79 có số, 6 null có hồ sơ) |
| [FRUIT-003](./tasks/FRUIT-003.md) | Soạn hồ sơ hoa quả (82 hồ sơ) | Dữ liệu | ✅ | FRUIT-001, FRUIT-002 | 2026-10-31 (validate 0 🔴; needs_review chờ duyệt) |
| [FRUIT-004](./tasks/FRUIT-004.md) | Thu thập giá mùa vụ 3 miền × 12 tháng | Dữ liệu | ✅ | FRUIT-003 | 2026-10-31 (1.387 dòng giá có nguồn, 78/82 hồ sơ; 4 hồ sơ để trống có lý do) |
| [FRUIT-005](./tasks/FRUIT-005.md) | Module quản trị /admin/hoa-qua | Backend | ✅ | FRUIT-001 | 2026-10-31 |
| [FRUIT-006](./tasks/FRUIT-006.md) | Trang người dùng /hoa-qua | Frontend | ✅ | FRUIT-003, FRUIT-004 | 2026-10-31 (+ khối "Tháng này nên ăn gì") |
| [ALIAS-001](./tasks/ALIAS-001.md) | Tên gọi khác (tên vùng miền) cho món + nguyên liệu/quả, tìm kiếm được | Dữ liệu | ✅ | — | 2026-10-31 (19 quả + 66 món có tên gọi khác; 2 cặp nghi trùng chờ chủ dự án quyết gộp) |

**DoD GĐ3:** ≥500 món Mức 1; QA-002 cho kết quả ≥80% số món trong ±20%; nhật ký đo đã ghi vào
[ACCURACY-SPEC §6](../06-nutrition/ACCURACY-SPEC.md).

> Đây là giai đoạn dài nhất và **là lúc dự án dễ chết nhất**. Hai thứ bảo hiểm: cổng chặn GĐ2, và mốc
> dùng được sớm ở 150 món để thấy thành quả trước khi hết hứng.

---

## GIAI ĐOẠN 4 — Engine gợi ý

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [REC-001](./tasks/REC-001.md) | Engine gợi ý 3 tầng | Backend | ✅ | DISH-002 | 2026-10-30 |
| [REC-002](./tasks/REC-002.md) | Sinh embedding + tìm món tương tự bằng embedding vector | Backend | ⬜ | REC-001 | — |
| [REC-003](./tasks/REC-003.md) | Lắc hai chế độ: ăn ngoài 1 món / mâm cơm tự nấu 1–4 món (mặc định 3, không tính cơm) | Backend + Frontend | ✅ | REC-001 | 2026-10-31 (theo yêu cầu trực tiếp của chủ dự án: mâm gom theo vai trò chính + canh + rau; tuỳ chọn lưu trong hồ sơ; lọc chay dùng thêm nhãn diet:man) |
| [REC-004](./tasks/REC-004.md) | Tuỳ chọn "Bữa cơm bình dân" cho mâm cơm: chỉ món dễ nấu, nguyên liệu phổ biến dễ kiếm | Backend + Frontend | ✅ | REC-003 | 2026-10-31 (tiêu chí suy từ kho: mọi nguyên liệu có mặt ở ≥4 món + ≤8 bước nấu; pool thật 209/306 món; lưu hồ sơ tray_binh_dan) |
| [IMG-001](./tasks/IMG-001.md) | Ảnh cho mọi sản phẩm (món/nguyên liệu/hoa quả): Wikimedia CC đã kiểm chứng trực quan + AI minh hoạ gắn nhãn cho phần thiếu | Backend + Frontend + Dữ liệu | 🔶 | DATA-010, UI-012 | 2026-10-31 (chủ dự án chốt hướng Wikimedia + AI; hạ tầng media mở rộng cho nguyên liệu, route /media, validator V19, hiển thị + ghi công; đợt 1 Wikimedia đã rà soát loại 379 ảnh sai/không tin được từ search; đợt AI lấp phần còn lại chạy theo đợt con) |
| [UI-002](./tasks/UI-002.md) | Nút Lắc bản chính thức | Frontend | ✅ | REC-001 | — |
| [UI-003](./tasks/UI-003.md) | Trang chi tiết món (công thức, dinh dưỡng, độ tin cậy) | Frontend | ✅ | UI-002 | — |
| [UI-004](./tasks/UI-004.md) | Vỏ ứng dụng + hệ thiết kế v2 (Apple HIG) | Frontend | ✅ | UI-003 | 2026-10-31 |
| [UI-005](./tasks/UI-005.md) | Trang chủ: sân khấu Lắc theo bản mẫu v2 | Frontend | ✅ | UI-004 | 2026-10-31 |
| [UI-006](./tasks/UI-006.md) | Khám phá món dạng danh sách biên tập | Frontend | ✅ | UI-004 | 2026-10-31 |
| [UI-007](./tasks/UI-007.md) | Chi tiết món: khối Nguồn gốc con số mở sẵn | Frontend | ✅ | UI-004 | 2026-10-31 |
| [UI-008](./tasks/UI-008.md) | Hồ sơ — trung tâm quản trị của người dùng | Frontend | ✅ | UI-004 | 2026-10-31 |
| [UI-009](./tasks/UI-009.md) | Trang Hoa quả theo cùng hệ thiết kế | Frontend | ✅ | UI-004 | 2026-10-31 |
| [UI-010](./tasks/UI-010.md) | QA giao diện v2 + tài liệu | Frontend | ✅ | UI-005…UI-009 | 2026-10-31 |
| [UI-011](./tasks/UI-011.md) | Khối Lắc: tuỳ chọn trên nút, gợi ý bữa theo giờ, tuỳ chọn lưu ở hồ sơ | Frontend + Backend | ✅ | UI-005, UI-008 | 2026-10-31 |
| [UI-012](./tasks/UI-012.md) | Thẻ đồng nhất cho món ăn, nguyên liệu, hoa quả (sẵn sàng cho ảnh thật) | Frontend | ✅ | UI-004 | 2026-10-31 |
| [UI-013](./tasks/UI-013.md) | Khối Lắc: lựa chọn loại trừ, chip Không ăn được, kcal gợi ý theo TDEE | Frontend | ✅ | UI-011 | 2026-10-31 |
| [UI-014](./tasks/UI-014.md) | Yêu thích + thống kê số lượng món/quả đã ăn | Frontend + Backend | ✅ | UI-012 | 2026-10-31 |
| [UI-015](./tasks/UI-015.md) | Trang Khám phá và trang Nhật ký chuyên dụng (bản dùng chung chỉ để dùng nhanh) | Frontend + Backend | ✅ | UI-005, UI-008 | 2026-10-31 |
| [UI-016](./tasks/UI-016.md) | Hồ sơ cho khách chưa đăng nhập + 4 trang chính sách theo luật | Frontend | ✅ | UI-008 | 2026-10-31 |
| [UI-017](./tasks/UI-017.md) | Mục Nguyên liệu cho người dùng: danh sách tra cứu + trang chi tiết | Frontend + Backend | ✅ | UI-012, UI-015 | 2026-10-31 |

**DoD GĐ4:** bấm Lắc ra món hợp bữa, không lặp món vừa ăn, hiển thị dinh dưỡng đúng quy tắc QT-5.

---

## GIAI ĐOẠN 5 — Người dùng

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [USR-001](./tasks/USR-001.md) | Đăng ký / đăng nhập người dùng | Backend | ✅ | UI-002 | — |
| [USR-002](./tasks/USR-002.md) | Hồ sơ thể trạng + tính khoảng TDEE | Backend | ✅ | USR-001 | — |
| [USR-003](./tasks/USR-003.md) | Ghi nhật ký bữa ăn (`meal_logs`) | Backend | ✅ | USR-001 | — |
| [USR-004](./tasks/USR-004.md) | Đưa lịch sử + TDEE vào engine gợi ý | Backend | ✅ | USR-002, USR-003, REC-001 | — |

---

## GIAI ĐOẠN 6 — Lớp phụ

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [CTR-001](./tasks/CTR-001.md) | Form đóng góp + hàng đợi duyệt trong trang quản trị | Backend | ✅ | ADM-001 | — |
| [TCM-000](./tasks/TCM-000.md) | Nền kỹ thuật lớp Mức 3: định dạng file, engine tổng hợp, cổng V20, hiển thị "Tính vị:", đường nâng bù | Backend | ✅ | — | 2026-11-01 |
| [TCM-001](./tasks/TCM-001.md) | Bảng `ingredient_tcm_traits` + gieo mầm từ dược thực đồng nguyên | Dữ liệu | ✅ ĐÓNG (TCM-001c đợt 3, 2026-11-01) — TCM-001a ✅ (tầng 1–2: 25 NL/35 dòng); TCM-001b ✅ (tầng 3–4: +40 NL/56 dòng → 65 NL/91 dòng; coverage ≥60%: 4→227); TCM-001c đợt 1 ✅ (P-DERIVE: 12 dòng suy diễn/10 NL + gieo rau/củ/quả/nấm: +74 NL/164 dòng → 149 NL/267 dòng; coverage ≥60%: 401 món; 106 món Mức 1 đủ tiêu chí phủ — chỉ thống kê); TCM-001c đợt 2 ✅ (đạm & thuỷ sản: +43 NL/+75 dòng, hai lần bổ sung cùng ngày +3 NL/+7 dòng → 195 NL/349 dòng; components cho 14 NL chế biến/25 liên kết; kết luận nam-dui-ga = *P. eryngii* — đã sửa metadata định danh, số giữ proxy *P. ostreatus* có đính chính; coverage ≥60%: 477 món; 122 món Mức 1 đủ tiêu chí phủ — chỉ thống kê); **TCM-001c đợt 3 ✅ + bổ sung ✅ — ĐÓNG CHƯƠNG TRÌNH GIEO NHÃN (gia vị/thảo mộc/chế biến + vét toàn kho: +28 NL/+56 dòng; bổ sung sau báo cáo nghiên cứu, tự kiểm chứng lại từng trang: +4 NL/+8 dòng (cha-la, dau-trang, so-diep low, ca-phe-den low) → 227 NL/413 dòng (393 thật + 20 suy diễn); components 27 NL/39 liên kết; thử lại cua-dong THÀNH CÔNG (+2 dòng Tuệ Tĩnh/Lãn Ông); 180 NL chưa nhãn có bản đồ lý do từng mục (i) 57 · (ii) 84 · (iii) 10 · (iv) 29; coverage ≥60%: 500 món; 135 món Mức 1 đủ tiêu chí phủ — chỉ thống kê)** | ING-003 | 2026-11-01 |
| [TCM-002](./tasks/TCM-002.md) | Tổng hợp tính vị từ nguyên liệu lên món | Backend | ✅ (trong TCM-000) | TCM-001 | 2026-11-01 |
| [TCM-003](./tasks/TCM-003.md) | Hiển thị lớp tính vị (mặc định tắt, khung văn hoá) | Frontend | 🔵 một phần — hiển thị đã dựng trong TCM-000 theo quyết mới (mặc định bật); **2026-11-01 chiều: bản gọn — giá trị chính + popup "Nguồn & chi tiết"; bổ sung ~15:29: giá trị chính tách 3 dòng Tính:/Vị:/Ngũ hành: có màu + icon động**; còn chấm điểm gợi ý | TCM-002 | — |
| [VN-001](./tasks/VN-001.md) | Ghi chú văn hoá (`cultural_note`) theo đợt — chương trình Mức 3 | Dữ liệu | ✅ ĐỢT 1 (2026-11-01) — 135 món Mức 1 đạt phủ ≥60%: **đã viết 87** (census mở lại 100% URL nguồn; 7 URL chết/thay nội dung → 6 món viết lại từ nguồn thay thế đã kiểm chứng; rà diễn đạt 0 câu khung y tế bị cấm), **48 chờ có lý do** (31 không có nguồn biên tập đạt chuẩn viết riêng · 12 lệch định danh/chờ quyết · 5 chất liệu chủ yếu khung sức khoẻ); **KHÔNG đổi `completeness_level`** — 87 món đủ 2 tiêu chí nâng bù chỉ thống kê; validate 0 🔴, Pest 157/157. ✅ ĐỢT 2 (2026-11-01) — 472 món Mức 2 thường: **đã viết 250/465** món trong phạm vi thực tế (7 file đang dở của phiên song song bị bỏ qua, ghi rõ), **215 chờ có lý do** (A 201 không có nguồn biên tập đạt chuẩn · B 9 lệch định danh/chờ quyết · C 5 chỉ có khung sức khoẻ); census mở lại 100% URL nguồn: 0 chết, 0 vi phạm khung trong text ghi chú; khắc phục census: 29 slug cắt/sửa chi tiết, 23 slug thay nguồn + viết lại, 4 slug gỡ ghi chú chuyển chờ; **thống kê đủ cổng Mức 3 (chỉ thống kê, chưa gán): 187/472 Mức 2 thường — toàn kho 274 món**; toàn kho 337/654 món có `cultural_note`; validate 0 🔴, Pest 157/157. ✅ ĐỢT 3 — ĐÓNG TUYẾN (2026-11-01): 51/95 món Mức 1 còn lại đã viết (chờ 44: A 23 · B 19 · C 2), 75/82 hồ sơ quả đã viết (chờ 7), 7 file Mức 2 bị giữ bỏ qua lần 2; census 128 URL 0 chết, 18 file cắt câu ngoài nguồn, rà diễn đạt 0 vi phạm; **toàn tuyến: 388/654 món + 75/82 quả có `cultural_note`**; đủ cổng Mức 3 (chỉ thống kê): 187/274/301; validate 0 🔴, ExportStabilityTest đạt, Pest 160/162 (2 đỏ SwapTrayTest — phiên song song) | TCM-000, TCM-001 | 2026-11-01 |
| [TCM-PROMOTE-001](./tasks/TCM-PROMOTE-001.md) | Nâng bù "Mức 2 (có note)" cho 87 món đủ thông tin Mức 3 (đường nâng bù) | Dữ liệu | ✅ (2026-11-01) — 87 món Mức 1 đủ 2 tiêu chí (phủ ≥60% + `cultural_note`) nâng lên Mức 2 với `level_basis='tcm_compensation'` + `level_note` riêng từng món soạn từ hồ sơ LV2 (phân bố lý do: 23 thiếu nguồn/không đồng thuận · 21 biến thể/chờ quyết · 20 trần nguyên liệu định danh · 14 bị chặn 429 · 8 không có công thức đúng định danh · 1 link rot); đối chiếu danh sách khớp 3 chiều (file · VN-001 · DB kiểm chứng); **toàn kho sau nâng: 472 Mức 2 thường + 87 Mức 2 (có note) + 95 Mức 1**; validate 0 🔴, Pest 157/157, ExportStabilityTest đạt | TCM-000, TCM-001, VN-001 | 2026-11-01 |
| [TCM-PROMOTE-002](./tasks/TCM-PROMOTE-002.md) | Gán **Mức 3** cho món Mức 2 thường đủ cổng + nâng bù đợt 2 cho 27 món Mức 1 mới đủ điều kiện | Dữ liệu | ✅ (2026-11-01) — Phần A: **187 món** Mức 2 thường (phủ ≥60% + `cultural_note`, đối chiếu VN-001 khớp tuyệt đối) → `completeness_level=3`; món "Mức 2 (có note)" không gán Mức 3 theo nguyên tắc nền Mức 2 thường; grep toàn bộ nơi dùng mức: **0 dòng code phải sửa** (`levelLabel` động, bộ lọc admin đã có Mức 3), kiểm hiển thị thật API/trang món trên DB kiểm chứng. Phần B: **27 món** Mức 1 mới đủ 2 tiêu chí → nâng bù `tcm_compensation` + `level_note` riêng từng món (11 thiếu nguồn · 7 biến thể/chờ quyết · 5 chặn 429 · 2 link rot AUDIT-FIX · 1 trần nguyên liệu · 1 không có công thức web); **toàn kho sau đợt: 187 Mức 3 + 285 Mức 2 thường + 114 Mức 2 (có note) + 68 Mức 1**, khớp 3 cách; validate 0 🔴 (24 🟡, +2 V9 giải thích được), Pest 162/162, ExportStabilityTest đạt | TCM-000, TCM-001, VN-001, TCM-PROMOTE-001 | 2026-11-01 |

---

## Câu hỏi mở

Ghi mọi câu hỏi chưa có lời giải ở đây. **Không đoán rồi làm tiếp.**

| # | Câu hỏi | Chặn task nào | Ai trả lời | Trạng thái |
|---|---|---|---|---|
| Q1 | Tên chính thức: "Lắc Đói" hay "Lắc Đối"? | — (chỉ ảnh hưởng hiển thị) | Chủ dự án | ✅ **Đã chốt 2026-10-31: "Lắc Đói"** — lắc món ăn khi đói; phương án "Lắc Đối" bị loại |
| Q2 | Giấy phép mã nguồn & dữ liệu của dự án? | Việc nhập Open Food Facts và Wikibooks | Chủ dự án | ✅ Đã chốt 2026-10-31 ([LEGAL-002](./tasks/LEGAL-002.md)): mã nguồn MIT; dữ liệu collective database — tự biên soạn CC BY 4.0, dòng có nguồn OFF giữ ODbL 1.0 |
| ~~Q3~~ | ~~Viện Dinh dưỡng có cấp phép không?~~ | — | — | ❌ **Đã đóng 2026-08-31** — chọn hướng tự tra cứu, không xin phép. Xem [LEGAL-001](./tasks/LEGAL-001.md) |
| Q4 | Ảnh món lấy từ đâu? Tự chụp / mua / CC BY? | DISH-001 trở đi; ảnh hưởng kích thước repo | Chủ dự án | ⬜ Chờ |
| Q5 | Có cần Git LFS cho ảnh không? | Phụ thuộc Q4 | Chủ dự án | ⬜ Chờ |
| Q10 | Trọng số bữa (meal weight) của nhóm `loai:nuoc-cham`: 21/23 món chấm mang weight 8 ở trưa/tối trong khi EDITORIAL-GUIDE §5 không có quy ước trần theo loại món. Engine Lắc đã chặn món khẩu phần <80 g khỏi gợi ý bữa chính (`Recommender::pool`) nên rủi ro thực tế đã được chặn ở tầng engine — quyết: (a) giữ nguyên, (b) hạ weight nhóm nước chấm xuống mức tượng trưng cho dữ liệu tự nhất quán, hay (c) loại nhóm này khỏi slot bữa chính ở tầng dữ liệu? | Dữ liệu nhóm nước chấm (tầm soát M-4) | Chủ dự án | ⬜ Chờ ([DATA-AUDIT-FIXES §5](../04-data-sources/DATA-AUDIT-FIXES-2026-10-31.md)) |
| **Q7** | **Nguồn nào cho hệ số hao hụt khi nấu của THUỶ SẢN?** Bảng USDA Cooking Yields R2 đã nhập chỉ có thịt và gia cầm — **0 dòng thuỷ sản**. Không thể suy từ SR Legacy (raw ÷ cooked) vì chính USDA là vế tham chiếu của TEST-002 ⇒ phép kiểm thành vòng tròn. Ứng viên: USDA Agriculture Handbook 102 (bản quét 1975, chưa có lớp text) | Nhóm `hap` của TEST-002; mọi món cá hấp/luộc VN | Chủ dự án + tra cứu | ⬜ Chờ ([DATA-004](./tasks/DATA-004.md)) |
| ~~Q8~~ | ~~Repo có đẩy lên remote không, và ở đâu?~~ | — | — | ✅ **Đã đóng 2026-09-01** — `origin` = `git@gitlab.com:Tran-Son-Nam/shake-hungry.git`, branch `dev` đã đẩy (8 commit, 513 file). Rủi ro mất trắng đã gỡ |
| ~~Q9~~ | ~~CI chạy trên nền nào?~~ | — | — | ✅ **Đã đóng 2026-10-31** — tiền đề đã đảo ngược: repo nay ở **GitHub** (không còn remote GitLab) nên `.github/workflows/tests.yml` đã đúng nền. CI vẫn chưa chạy lần nào vì code **chưa push** — sẽ tự chạy ở lần push đầu (đang chờ credential) |
| ~~Q6~~ | ~~Có chấp nhận bảng MEXT (Nhật) làm nguồn thứ hai không?~~ | — | — | ✅ **Đã đóng 2026-10-31** — quyết định **tầng 6** của chủ dự án bao quát cả MEXT/FNRI/IFCT/Bảng VN/văn liệu: chỉ tra từng mục khi nguồn chính thống không có, kèm trích dẫn + cờ "chưa kiểm định" (xem [DATA-PIPELINE §7.2](../04-data-sources/DATA-PIPELINE.md)). Xung đột QT-4 được xử lý bằng cơ chế `mixed` có kiểm soát + hạ confidence |

---

## Nhật ký chất lượng dữ liệu

Ghi kết quả mỗi lần rà soát định kỳ (mỗi 50 món — xem [DATA-PIPELINE §5](../04-data-sources/DATA-PIPELINE.md)).

| Ngày | Phạm vi | Mẫu kiểm | Phát hiện | Hành động |
|---|---|---|---|---|
| 2026-10-31 | Toàn kho 500 món (QA-001 đợt toàn kho) + QA-002 đo độ chính xác trên món Việt | validate-data 968 bản ghi; kiểm tay ngẫu nhiên 5 món; mẫu QA-002 160 món phân tầng | validate **0 🔴, 26 🟡** (đã phân loại hết: V9 chủ yếu là món chấm khẩu phần nhỏ — đề xuất dải riêng cho loại nước chấm; V8 gồm 67 nguyên liệu trái cây của module hoa quả). Kiểm tay: 2/5 món lệch >20%, cả hai là món chiên. QA-002: **76,9% trong ±20% — chưa đạt 80%**; 4 nhóm không chiên đạt 100%, nhóm chiên chỉ 7,5% (bias +39,8%) | Nguyên nhân gốc duy nhất: **dầu chiên tính hai lần** — công thức đã kê `dau-an` 8–20 g, engine cộng thêm mô hình hấp thụ CALC-004 (trung vị +100,5 kcal). Chờ chủ dự án chốt phương án sửa (dữ liệu/engine/cả hai) rồi đo lại trên cùng bộ mẫu; không nới định nghĩa. Báo cáo: [QA-001](../06-nutrition/accuracy-runs/2026-10-31-qa001-ra-soat-500-mon.md) · [QA-002](../06-nutrition/accuracy-runs/2026-10-31-vn-dishes.md) |
| 2026-10-31 | Phương án A (đổi quy ước dầu món chiên) + QA-002 lần 2 | Rà soát toàn bộ 46 món chiên; mẫu QA-002 160 món (danh sách đã lưu ở phụ lục báo cáo lần 2) | Chủ dự án chốt Phương án A: 33 món bỏ hẳn dòng `dau-an`, 7 món giảm còn 3 g (bước phụ có thật), 2 món giữ ngoại lệ (`xoi-chien`, `banh-chung-chien` — hệ số gạo nếp = 0 theo CALC-004), 4 món vốn không có dòng dầu. validate **0 🔴**, Pest **106/106**. QA-002 lần 2: **77,5% trong ±20% — vẫn chưa đạt**; nhóm chiên 10,0%, bias +49,0% nhưng chênh lệch tuyệt đối gần như không đổi (+143,7 → +137,0 kcal) | Phân tích lại chẩn đoán: khoảng cách engine ↔ tham chiếu ở nhóm chiên *là* dầu hấp thụ engine mô hình hoá; tham chiếu nguyên văn không chứa nó (lần 1 dòng `dau-an` vô tình làm đại diện). Cặp (quy ước A + tham chiếu nguyên văn) không tương thích về định nghĩa — chờ chủ dự án chốt: phương án C ở phía tham chiếu và/hoặc rà soát hệ số CALC-004 (báo cáo lần 2 §7). Không nới định nghĩa, không tự sửa engine. Báo cáo: [QA-002 lần 2](../06-nutrition/accuracy-runs/2026-10-31-vn-dishes-lan-2.md) |
| 2026-10-31 | Pha sửa dữ liệu sau tầm soát toàn kho (968 bản ghi) | Sửa đúng 5 vấn đề 🔴 của báo cáo tầm soát + 2 cặp gộp chủ dự án đã chốt; validate lại toàn kho, seed lại, Pest | validate **964 bản ghi — 0 🔴, 22 🟡**; seed **496 món**; recompute **491/496 tính được**; Pest **106/106**. Gộp: `nom-mit-non` → biến thể của `goi-mit-non` (sửa 2 tên en sai miền, region chuẩn → vn-quang-nam); `xoi-ngo` → biến thể Bắc của `xoi-bap` (giữ dấu vết duyệt + định lượng riêng); cụm 5 món nước mắm giống hệt → 1 món `nuoc-mam` + alias địa danh (xoá 4 file, giữ mã Wikidata trong import_note) | Sửa N-1 (trùng name_en mam-tom/mam-ruoc → thêm chú thích phân biệt), N-2 (tiet-lon kcal_max 82 → 82,1 đúng số Ciqual), Q-1 (6 dòng giá dua-bo bổ sung source_url thật đã kiểm chứng: Dân Việt 22/04/2026 + CafeF 04/05/2020). M-4 (weight nhóm nước chấm): không tự đặt số — engine đã chặn khẩu phần <80 g khỏi bữa chính; ghi câu hỏi mở **Q10** chờ chủ dự án quyết. Biên bản: [DATA-AUDIT-FIXES](../04-data-sources/DATA-AUDIT-FIXES-2026-10-31.md) |
| 2026-10-31 | QA-002 lần 3 (tham chiếu chiên độc lập) + hiệu chuẩn CALC-004 + lần 4 | Cùng mẫu 160 món; tham chiếu chiên suy từ cặp sống→chiên USDA SR Legacy (cân bằng protein/khối lượng), kiểm chứng đường ống bằng tái lập đúng số lần 2; sau sửa hệ số: validate, recompute, Pest, đo lại | Lần 3: **98,1% trong ±20% — ĐẠT** (nhóm chiên 92,5%, bias +12,2% gồm +9,6 điểm % cấu trúc điểm giữa khoảng); biến thể đối chứng 93,8%. Hiệu chuẩn: thịt/đạm **8→4**, củ/quả **12→9** (11 file nguyên liệu); giữ hải sản 9, phô mai 10, bột/vỏ 15, bột gạo 12 + giữ 2 ngoại lệ gạo nếp. validate 964 bản ghi **0 🔴**, recompute **491/496**, Pest **106/106**. Lần 4: **98,8%**, nhóm chiên 95,0% (38/40) | Phân rã bias cho thấy phần lớn lệch còn lại của nhóm chiên là cấu trúc điểm giữa của khoảng nới không đối xứng — không phải sai số mô hình. Hai món còn lệch đều là cá chiên trần sốt: ghi hồ sơ mở "hải sản chiên trần" (chưa đủ neo độc lập để tách hệ số ~6; QT-1 không bịa). Báo cáo: [QA-002 lần 3 + 4](../06-nutrition/accuracy-runs/2026-10-31-vn-dishes-lan-3.md) |
| 2026-10-31 | DISH-005 đợt 1 — canh + rau (lấp ô trống có chọn lọc) | Toàn bộ 60 món mới + 11 nguyên liệu mới; cổng G1–G7 của kế hoạch DISH-005 | validate **1.035 bản ghi — 0 🔴**; seed **556 món / 397 nguyên liệu**; recompute **551/556** (60/60 món mới tính được); Pest **106/106**; rà phân loại độc lập 100% món mới khớp (diet suy trong DB: 8 chay đúng thành phần, 52 mặn) | Canh 3 miền đều ≥20 (Bắc 33 / Trung 20 / Nam 20). 4 nguyên liệu để null kèm hồ sơ (cá dìa, măng chua, ngồng cải, hẹ) kéo theo 7 món hoãn/đổi sang món thay thế cùng nhóm/miền — danh sách đầy đủ trong [DISH-005](./tasks/DISH-005.md). Phát hiện cạm bẫy: slug `bong-bi` là bóng bì lợn, không phải bông bí — bông bí biên soạn ở slug mới `hoa-bi` |
| 2026-10-31 | DISH-005 đợt 2 — mặn nhanh + lõi miền Trung | 65 món mới + 6 nguyên liệu mới; cổng G1–G7 của kế hoạch DISH-005 (đợt có món chiên → G5 kích hoạt) | validate **1.106 bản ghi — 0 🔴**; seed **621 món / 403 nguyên liệu**; recompute **616/621** (65/65 món mới tính được); Pest **114/114** (trên HEAD có cả REC-003 của phiên song song); G5: 5 món chiên mới lệch −2,7%…0,0% vs tham chiếu độc lập QA-002 lần 3; G6 rà soát độc lập 0 lỗi, 1 ca biên đã sửa (tam-huu → mon-chinh) | 8 món thay/phân xử: 4 món kế hoạch trùng món đã có (đậu phụ sốt cà chua = dau-phu-sot-ca, tôm rim thịt ba chỉ = tom-rim, chả trứng hấp = cha-trung, cá thu chiên sốt cà = ca-thu-sot-ca dạng kho — hai vụ trùng do V7/G6 phát hiện ở vòng rà soát), 4 món hoãn vì nguyên liệu null (heo quay, mắm tôm chua, ốc hút, riềng của tré) — thay bằng món cùng quota/miền, ghi rõ trong [DISH-005](./tasks/DISH-005.md) |
| 2026-10-31 | DISH-005 đợt 3 — món chính chay + bữa sáng tự nấu (đợt cuối, đóng DISH-005) | 35 món mới + 3 nguyên liệu mới; cổng G1–G7 của kế hoạch DISH-005 (đợt có món chiên → G5 kích hoạt; cổng cứng diet chay kiểm chứng trong DB) | validate **1.144 bản ghi — 0 🔴**; seed **656 món / 406 nguyên liệu**; recompute **651/656** (35/35 món mới tính được); Pest **115/115**; diet: **20/20 món chay mới suy đúng `diet:chay`** trong DB; G5: 5 món chiên mới lệch −0,2%…−0,6% vs tham chiếu độc lập QA-002 lần 3; G6 rà soát độc lập: 8 nhóm lỗi chắc chắn, đã sửa toàn bộ trước khi đóng | 4 vụ thay do trùng (nam-rom-kho-tieu = nam-kho-tieu đợt 2; rau-cu-kho-chay ≈ kho-chay; chao-trang-trung-muoi ⊂ chao-trang; dau-phu-sot-nam trùng cụm 2 món đậu phụ kho nấm) + 2 món hoãn (cha-chay-kho-dua vì cha-lua-chay null; dau-phu-chien-sa-ot vì hệ số dầu đậu phụ chưa được hậu thuẫn — lệch +24%). Phát hiện đáng nhớ: `mi-can` là mì căn (seitan), không phải sợi mì. **DISH-005 đóng: kho 496 → 656 món (+160), nguyên liệu 386 → 406, 360 công thức chờ duyệt** |
| 2026-10-31 | LV2-001 đợt 1 — nâng Mức 2 cho 50 món nền tảng + hiệu chuẩn đậu phụ CALC-004 | 50 file món (recipe_sources/steps/import_note + phân xử định lượng) + `dau-phu` (hệ số dầu 15→10) + món mới `dau-phu-chien-sa-ot` | validate **1.145 bản ghi — 0 🔴**; seed **657 món / 406 nguyên liệu**; recompute **652/657**; DB **47 món Mức 2**; đối chiếu calo trước/sau: đúng 7 món đổi điểm giữa, lệch >10% (5 món) đều có lý do phân xử định lượng; Pest **115/115**; rà độc lập mở lại 10/10 nguồn mẫu — khớp ghi chép | 3 món giữ Mức 1 có lý do (com-hen, canh-nghe-ca-ro, com-tho); phân xử điều phối: long-lon-luoc giữ Mức 2 (nguồn đúng món, định lượng mẻ tương thích — ghi minh bạch trong [LV2-001](./tasks/LV2-001.md), chủ dự án có thể hạ). 12 bất thường thiếu/thừa dòng nguyên liệu so với nguồn chờ chủ dự án quyết (không tự thêm/bớt dòng). VM bị thay mới giữa đợt (lần 4) — khôi phục apt chỉ mirror azure + MySQL root không mật khẩu + dựng lại DB từ data/ |
| 2026-10-31 | Phần A (sửa bất thường LV2-001) + LV2-002 đợt 2 — nâng Mức 2 cho 50 món thường ngày | Phần A: 9 file món (thêm/bớt/đổi dòng nguyên liệu) + LV2-001.md; Đợt 2: 50 file món (recipe_sources/steps/import_note + phân xử định lượng) + LV2-002.md | validate **1.145 bản ghi — 0 🔴** sau cả hai phần; seed **657 món / 406 nguyên liệu**; recompute **652/657**; DB **97 món Mức 2** (V12); Phần A: calo đúng 9 món đổi (lớn nhất ca-tram-kho-rieng 190,6 → 352,8 do thêm 40 g ba chỉ theo nguồn); Đợt 2: calo đúng 13 món đổi — 6 món lệch >10% đều do 14 dòng định lượng đã phân xử; Pest **115/115** cả hai phần; rà độc lập đợt 2 mở lại 10/10 nguồn mẫu — khớp ghi chép | Đợt 2 không món nào giữ Mức 1; ~35 bất thường mới (thiếu/thừa dòng nguyên liệu, 2 ca phân loại/vùng miền) ghi trong LV2-002 chờ pha phân xử riêng — không tự sửa |
| 2026-10-31 | Phần A phân xử bất thường LV2-002 (commit riêng `57fb628`) | 43 file món + `ca-dieu-hong` (oil_absorption_pct 9) + LV2-002.md mục 5b + ROADMAP | validate **1.145 bản ghi — 0 🔴**; seed **657 món**; recompute **652/657**; calo đúng 26 món đổi, lớn nhất ca-ro-kho-to 296,9 → 453,3 (+52,7%, thêm 40 g ba chỉ); ca-dieu-hong-sot-ca kiểm tham chiếu độc lập QA-002 lần 3: lệch **+17,0%** (trong ±20%); Pest **121/121** | 16 ca giữ nguyên có lý do; đầu mối biên soạn nguyên liệu (mẻ, giấm bỗng, nước màu, giò sống, số mam-nem) chuyển pha ING |
| 2026-10-31 | LV2-003 đợt 3 — nâng Mức 2 cho 50 món thường ngày tiếp theo | 50 file món (37 món: recipe_sources/steps/import_note + phân xử định lượng + completeness_level 2; 13 món: ghi chú giữ Mức 1 trong import_note) + LV2-003.md + ROADMAP + CLAUDE §0 | validate **1.145 bản ghi — 0 🔴**; seed **657 món**; recompute **652/657**; DB **134 món Mức 2** (V12); đối chiếu calo 50 món: 22 món đổi số theo phân xử, 11 món lệch >10% đều có lý do, 13 món giữ Mức 1 và các món giữ định lượng đổi **0,0%**; Pest **121/121**; rà độc lập 10/10 mẫu khớp; kiểm cấu trúc tự động 37/37 | 13/50 giữ Mức 1 (8 món do provider tra cứu chặn 429 không đủ nguồn đã mở; 2 món chờ phân xử nhóm rang muối; 3 món thiếu nguồn đúng biến thể/độc lập). Bất thường mới ghi trong LV2-003 chờ pha phân xử; phiên nghiên cứu chạy song song 10 agent, có phiên song song REC-004 commit xen giữa — chỉ add file của nhiệm vụ |
| 2026-10-31 | LV2-004 Phần A — phân xử bất thường LV2-003 + biên soạn nguyên liệu mới + thử lại 8 món 429 | 21 file món sửa dòng + 5 file nguyên liệu mới (null + hồ sơ) + TaggingRules + LV2-004.md + LV2-003 (đánh dấu) | validate **0 🔴**; seed **657 món**; recompute **652/657**; calo: 7 món đổi >10% đều truy về dòng phân xử (lớn nhất canh-hen-nau-chua +52,6%); Pest **123/124** (1 đỏ ExportStabilityTest thuộc luồng media của phiên song song — không file nào của LV2-004) | Thử lại 429: 5/8 món đủ nguồn (phân xử áp ngay 3 món, 2 món gộp vào Phần B); suon-rang-muoi đủ nguồn chờ quyết nhóm; 2 món vẫn 429. Cả 5 nguyên liệu mới đều null — các dòng phụ thuộc giữ trạng thái chờ có số |
| 2026-10-31 | LV2-004 Phần B (đợt 4) — nâng Mức 2 cho 50 món thường ngày | 50 file món (recipe_sources/steps/import_note + phân xử định lượng + completeness_level 2) + 17 file món ghi hồ sơ giữ Mức 1 + LV2-004.md + ROADMAP + CLAUDE §0 + data/ingredients/_index.json (export sinh, phản ánh 5 nguyên liệu Phần A) | validate **0 🔴** (số bản ghi/🟡 dao động do phiên song song seed liên tục); seed **657 món**; recompute **652/657**; DB **184 món Mức 2** (V12); đối chiếu calo bằng baseline tái dựng từ commit `0daf1e0`: 0 món ngoài đợt đổi số, 21/50 món đổi >10% đều có lý do phân xử; Pest **124/124**; rà độc lập 10/10 mẫu khớp, sửa 1 trích dẫn URL sai (ca-doi-kho-tieu) | 12 ứng viên giữ Mức 1 có lý do ghi trong LV2-004 §2.1; bất thường mới §2.2 chờ chủ dự án quyết (rau xào nêm mắm, thốt nốt cá kho, vùng ca-tram-hap-xi-dau, quy ước sợi, định danh 5 món, nhóm rang muối). Commit đứng trên `845a738` (IMG-001 của phiên song song) — chỉ add file của nhiệm vụ |
| 2026-10-31 | LV2-005 Phần A — phân xử bất thường LV2-004 | 9 file món + LV2-004.md (đánh dấu) | validate **0 🔴** (file); vùng ca-tram-hap-xi-dau → Bắc + bỏ nhãn dac-san theo quy ước gốc xuất xứ; com-tam-long-xuyen sửa cấu trúc khìa (đi cùng nâng Mức 2 ở Phần B trong cùng file); long-lon-xao-dua-chua +cà chua/−lòng theo 3/3 nguồn; canh-hen-rau-lang bỏ gừng + viết lại quy trình; 5 ca định danh: 3 phân xử xong, bau-xao-tep + com-nam-huong giữ chờ quyết kèm phân tích | Commit riêng `1267570` trước Phần B; chỉ add file của nhiệm vụ |
| 2026-10-31 | LV2-005 Phần B (đợt 5 — ĐỢT CHỐT) — nâng Mức 2 cho 32 món, **tổng 216/657, chương trình Mức 2 đợt 1 hoàn tất vượt mốc 200** | 32 file món nâng (recipe_sources/steps/import_note + 14 dòng phân xử định lượng ở 11 món + completeness_level 2) + 13 file món ghi hồ sơ giữ Mức 1 + LV2-005.md (tổng kết toàn chương trình + danh sách quyết định chờ) + ROADMAP + CLAUDE §0 + AGENTS.md | validate **1.796 bản ghi — 0 🔴** (V12: **216 món Mức 2**); seed **657 món**; recompute **216 món có khoảng, 0 thiếu**; đối chiếu calo baseline tái dựng từ commit Phần A `1267570`: 186 món cũ **0 món đổi điểm giữa**, 30 hàng mới = món mới nâng; Pest **124/124**; rà độc lập 10/10 mẫu khớp | 13 ứng viên giữ Mức 1 có lý do (11 món 429; com-tho thiếu nguồn đúng biến thể; com-hen — nguồn thứ ba là bài forum trên đường dẫn bị chèn spam, phân xử không tính đạt chuẩn). VM bị thay mới giữa nhiệm vụ — khôi phục PHP/MySQL theo quy trình đã ghi, không mất dữ liệu file. Chỉ add file của nhiệm vụ |
| 2026-10-31 | LV2-011 — Phần A phân xử LV2-010 §B.3 (không đổi dữ liệu) + Phần B đợt 11 nâng Mức 2 cho 15/50 món, **tổng 421/654** | Phần A: LV2-011.md (mới) + LV2-010.md (đánh dấu); Phần B: 50 file món (15 nâng: recipe_sources/steps/import_note + phân xử định lượng; 35 giữ: chỉ import_note) + LV2-011.md + ROADMAP + CLAUDE §0 | validate **3.344 bản ghi — 0 🔴** (file) và **0 🔴 / 10 🟡** kèm DB kiểm chứng; seed DB kiểm chứng riêng **654 món**; recompute **421/421 Mức 2 tính đủ, 0 thiếu**; đối chiếu calo cách ly (worktree `d35a817` + DB riêng): **0 món cũ đổi khoảng, đúng 15 dòng dinh dưỡng mới**; Pest **127/127**; rà độc lập mẫu 10 món nâng: **9/10 khớp trực tiếp**, 1 trang ĐMX lỗi mạng khi mở lại (giữ kết quả vòng nghiên cứu) | 35 món giữ có hồ sơ theo nhóm (22 thiếu nguồn quy đổi được — gồm 2 ca biến thể đã có quyết định nhưng vẫn thiếu họ độc lập: dau-phu-kho-nam nấm rơm, canh-rau-den-tep tép khô; 11 biến thể; 2 trần nguyên liệu la-khuc/mam-nem). Bất thường mới §B.3: lau-ngan nghi sáng tạo biên tập → đề xuất danh sách xác nhận tồn tại; goi-oc/nom-chuoi-xanh chờ quyết hoa chuối; banh-cong thiếu nhân định danh. Đợt này không gặp 429. Chỉ add file của nhiệm vụ, không chạm 9 file phiên song song đang dở |
| 2026-10-31 | LV2-012 — ĐỢT VÉT CUỐI: xét toàn bộ 233 món Mức 1 còn lại (bảng kê đầy đủ), nâng 54 món, **tổng 475/654 Mức 2 — CHƯƠNG TRÌNH MỞ RỘNG MỨC 2 ĐÓNG** | 185 file món (54 nâng: recipe_sources/steps/import_note + phân xử định lượng + completeness_level 2; 131 giữ: chỉ import_note) + `data/ingredients/be-be.json` (bổ sung oil_absorption_pct 9 theo CALC-004) + LV2-012.md (mới — tài liệu đóng chương trình) + LV2-011.md (ghi chú thay thế §A.3) + ROADMAP + CLAUDE §0 | validate **3.344 bản ghi — 0 🔴 / 33 🟡**; seed DB kiểm chứng riêng **654 món**; recompute **475/475 Mức 2 tính đủ, 0 thiếu**; đối chiếu calo cách ly (worktree `d33af7d` + 2 DB riêng): **đúng 54 dòng dinh dưỡng mới, 0 dòng cũ đổi, 0 dòng mất**; Pest **127/127 (494 assertions)**; rà độc lập mẫu **10/10 khớp trực tiếp** + điều phối mở lại đủ 3 họ cho 3 ca cứu vòng 2 (banh-nam, cua-rang-muoi, mam-tep-chung-thit) | 179 món Mức 1 còn lại đều có hồ sơ theo nhóm (LV2-012 §3): 19 chờ quyết · 44 thiếu nguồn · 33 chặn 429 chưa kết luận · 38 biến thể · 23 trần nguyên liệu · 12 không có công thức đúng định danh · 4 thử lại không họ mới · 2 chưa biên soạn · 2 đã quyết giữ (A3) · 2 chờ điều kiện. Danh sách chờ quyết BẢN CUỐI 42 mục tại LV2-012 §4 (21 mục cũ + 21 phát sinh). `dau-bap-xao-toi`: file vẫn dở của phiên song song → A4.1 bổ sung ghi nhận tồn đọng lần cuối (LV2-012 §5). Chỉ add file của nhiệm vụ, không chạm 9 file phiên song song đang dở |
| 2026-10-31 | AUDIT-002 — RÀ SOÁT TOÀN BỘ (census, không lấy mẫu) sau chương trình Mức 2: toàn bộ món + nguyên liệu + hoa quả + media + URL nguồn, kiểm 2 lượt độc lập | 6 file nguyên liệu (sửa cơ học `oil_absorption_pct` theo CALC-004: `thit-bo-xay` 8→4, `ca-thu`→9, `thit-bo-than`/`thit-bo-nam`/`trung-vit-muoi`→4, `su-hao`→9) + AUDIT-002.md (mới) + ROADMAP + CLAUDE §0 (Đính chính lần 14). Báo cáo đầy đủ lưu ngoài repo (thư mục bàn giao của chủ dự án) | validate **0 🔴 / 11 🟡** (lớp đã biết) trên DB kiểm chứng riêng; seed lại + recompute sau sửa: đúng **5 món đổi khoảng kcal**, 475/475 Mức 2 tính đủ; Pest **127/127 (494 assertions)**; media 3 chiều **1.087 = 1.087 = 1.087**; lượt 2 mở lại 407 URL: 333 sống đúng / 10 sai nội dung / 24 chết / 40 chưa mở được (lỗi công cụ, không tính chết) | Phát hiện KHÔNG tự sửa — gom danh sách phân xử **PX-01…PX-35** chờ chủ dự án: 16 món rớt cổng nguồn vì link rot (`bau-xao-tep` 0/3); `ca-ro-dong-chien-xu` nguồn sai loài không công khai (PX-17); nhóm họ nguồn chờ quy ước chung (PX-25); cặp `canh-hen`/`canh-hen-nau-chua` trùng tên chính (PX-26); FDC rot 260 URL — số kiểm chứng trên trích xuất SR Legacy cục bộ 270/278 khớp tuyệt đối (PX-28); 23 món chờ 1 URL chưa mở được (PX-34). Chỉ add file của nhiệm vụ, không chạm 9 file phiên song song đang dở |
| 2026-11-01 | AUDIT-FIX — Pha khắc phục sau AUDIT-002: nhóm A (PX-01…PX-16), PX-17, PX-34, PX-28 | 16 file món nhóm A (13 khôi phục cổng + 3 hạ Mức 1) + 1 file món PX-17 (sửa định lượng bột) + 23 file món PX-34 (ghi hồ sơ) + 3 file món truy vết rà độc lập + 286 file nguyên liệu (đổi khuôn URL FDC) + AUDIT-FIX.md (mới) + AUDIT-002.md (§6) + ROADMAP + CLAUDE §0 (Đính chính lần 15) | validate **0 🔴 / 11 🟡** (lớp đã biết) trên DB kiểm chứng riêng `shake_hungry_verify`; recompute **472/472 Mức 2 tính đủ**; đối chiếu calo cách ly với worktree `dde8645`: chỉ 3 món hạ mức rời tập tính + **đúng 1 món đổi kcal** (`ca-ro-dong-chien-xu` +270,2 điểm giữa, do sửa bột PX-17); Pest **127/127 (494 assertions)**; PX-28: kiểm chứng mẫu API 11/11 ID trước sửa, mở lại **260/260 URL** sau sửa đều 200 đúng thực phẩm; rà độc lập mở lại **22/22** nguồn thay thế mới (19 đúng món, 3 ghi truy vết) | Tổng Mức 2: 475 → **472/654** (3 hạ mức là kết quả trung thực có hồ sơ, không giữ Mức 2 hình thức). PX-25 (quy ước họ nguồn) và PX-26 (cặp canh hến) vẫn chờ chủ dự án — không tự đụng. VM bị thay mới giữa đợt (mất toàn bộ DB + reset auth MySQL): chỉ dựng DB kiểm chứng riêng, **không seed DB dev** theo đúng nhiệm vụ. Không chạm 7 file media của phiên song song còn dở |
| 2026-08-31 | 55 nguyên liệu đầu tiên ([ING-001](./tasks/ING-001.md)) | Toàn bộ, tra chéo USDA / Ciqual / nhãn sản phẩm | 20 `high`, 21 `medium`, 12 `low`, **2 không tra được** (cá lóc, mắm tôm). Bốn ô sai số lớn: ba chỉ (380–518), nạm bò (155–345), đậu phụ (80–144), cơm trắng (130–155). Bẫy loài: USDA không có mục `water spinach` cho rau muống | Tách đôi 3 nguyên liệu (thăn lợn, nước mắm); ghi 6 nhóm bẫy vào [EDITORIAL-GUIDE §9](../09-standards/EDITORIAL-GUIDE.md); 4 nguyên liệu dùng OFF → **chặn bởi [LEGAL-002](./tasks/LEGAL-002.md)** |
| 2026-09-01 | Soát toàn dự án (code + dữ liệu + tài liệu) | Máy: chạy lại `validate-data`, 40 test Pest, `migrate:fresh --seed`, `accuracy-run`; đối chiếu từng con số của CLAUDE.md/ROADMAP với thực tế | Số liệu tài liệu **khớp thực tế**, trừ 2 chỗ đã đính chính. Bốn phát hiện mới: (1) engine trả khoảng **rộng bằng 0** cho ánh xạ `high` — vi phạm QT-5, đúng thứ chỉ số "1,0% tham chiếu trong khoảng" đã tố cáo từ lần đo 1 mà không ai đọc; (2) **255/255** nguyên liệu `needs_review=true` (tài liệu ghi 105) vì schema **không có chỗ ghi ai đã duyệt**; (3) `needs_review` mặc định **`false`** ở cả hai bảng — ngược QT-1, lỗ hổng mở đúng lúc GĐ2 ghi thẳng vào DB; (4) bảng Cooking Yields R2 **0 dòng thuỷ sản** nên nhóm `hap` không có nguồn hệ số độc lập | Engine 1.1.0 (bất định nền ±4%, hiệu chuẩn từ p90 nhóm bảo toàn khối lượng); R10 + V15 + 3 cột dấu vết duyệt; đảo mặc định `needs_review`; ghi Q7 (hệ số thuỷ sản) và Q8 (remote) vào Câu hỏi mở |
| 2026-08-31 | Đợt ING-003: 105 nguyên liệu mới (150→255) + đối chiếu toàn kho | Máy: đối chiếu snapshot ↔ bảng SR Legacy đã nhập (197 mục), validator V1–V14, V10 | **197/197 ánh xạ khớp tuyệt đối, 0 lệch.** Số liệu 105 file mới do script máy-chép từ USDA gốc theo fdc_id (không gõ tay). 38 mục null có tài liệu tra cứu. V10: chỉ mỡ nước vượt 900 kcal — đúng ngoại lệ. Bẫy mới ghi nhận: chili powder Mỹ là hỗn hợp; sake ≠ rượu trắng (cồn bằng nửa); lươn Anguilla ≠ Monopterus (béo ~6×) | Toàn bộ 105 file needs_review=true chờ người duyệt; 5 mục MEXT tiếp tục giữ ngoài repo (Q6) |

---

## Nhật ký quyết định

Ghi lại các quyết định kỹ thuật quan trọng để người sau không lật lại vô ích.

| Ngày | Quyết định | Lý do tóm tắt | Tài liệu |
|---|---|---|---|
| 2026-08-31 | MySQL 8 | Khớp mờ tên món bằng trigram ở tầng PHP + FULLTEXT ngram; tìm không dấu/sắp xếp tiếng Việt bằng collation `utf8mb4_vi_0900_ai_ci`; embedding hoãn tới GĐ4 | [ARCHITECTURE §4](../02-architecture/ARCHITECTURE.md) |
| 2026-08-31 | Không dùng Python | `ingredient-parser-nlp` và `recipe-scrapers` đều chỉ hỗ trợ tiếng Anh, không có site VN → lợi thế hệ sinh thái Python biến mất | [ARCHITECTURE §3](../02-architecture/ARCHITECTURE.md) |
| 2026-08-31 | Không fork bất kỳ app quản lý công thức nào | Tandoor có Commons Clause (không phải OSS); Mealie/KitchenOwl/Nextcloud là AGPL copyleft mạng | [LICENSE-COMPLIANCE §2.4](../04-data-sources/LICENSE-COMPLIANCE.md) |
| 2026-08-31 | Hoãn dữ liệu địa điểm quán sang v2 | OSM chỉ có 1.845 nhà hàng ở lõi TP.HCM — quá mỏng; quán vỉa hè gần như không được map | [DATA-SOURCES §5](../04-data-sources/DATA-SOURCES.md) |
| 2026-08-31 | LLM không được sinh số dinh dưỡng | Không truy nguyên được; phá thẳng mục tiêu chính | [DATA-PIPELINE §2](../04-data-sources/DATA-PIPELINE.md) |
| 2026-08-31 | Nguyên liệu trước, món sau | ~250 nguyên liệu phủ hết món Việt thường ngày; làm ngược là lặp việc hàng trăm lần | [DATA-PIPELINE §1](../04-data-sources/DATA-PIPELINE.md) |
| 2026-08-31 | Định nghĩa độ chính xác = ≥80% món trong ±20% | "±10% so với phân tích hoá học" không khả thi — nhãn menu nhà hàng Mỹ còn lệch trung bình 18% | [ACCURACY-SPEC §2](../06-nutrition/ACCURACY-SPEC.md) |
| 2026-08-31 | **Không xin phép Viện Dinh dưỡng; tự tra cứu từng nguyên liệu từ nguồn công khai** | Chấp nhận đánh đổi thời gian lấy độ chính xác và quyền sở hữu dữ liệu. Giá trị dinh dưỡng là **dữ kiện**, biên soạn độc lập có ghi nguồn là hợp lệ; cái bị bảo hộ là **bảng biên soạn** của họ, và ta không chép nó | [LEGAL-001](./tasks/LEGAL-001.md) · [DATA-PIPELINE §7](../04-data-sources/DATA-PIPELINE.md) |
| 2026-10-31 | **License: mã nguồn MIT; dữ liệu dạng collective database** (tự biên soạn CC BY 4.0, dòng có nguồn Open Food Facts giữ ODbL 1.0 + ghi công) | Phụ lục kiểm kê cho thấy chốt lúc này không tốn chi phí; thực tế đã có vài dòng nhãn tra qua OFF từ đợt ING-001 nên mô hình tập hợp xử lý đúng thực tế thay vì xoá dữ liệu trụ cột hay ép toàn bộ dataset sang share-alike | [LEGAL-002](./tasks/LEGAL-002.md) · [LICENSE-COMPLIANCE §5](../04-data-sources/LICENSE-COMPLIANCE.md) |
| 2026-08-31 | MySQL 8 có sẵn trong Laragon, tài khoản mặc định `root`; **cột `embedding` hoãn tới GĐ4** | Không cần cài thêm CSDL ngoài mặc định của Laragon; GĐ1–GĐ3 không cần embedding | [SETUP-001](./tasks/SETUP-001.md) |
| 2026-08-31 | Nguyên liệu tuỳ chọn chỉ tính vào cận trên; `fct_source='mixed'` ép confidence ≤ medium; món chiên thiếu hệ số dầu ⇒ nguyên liệu vào `missing[]` | Đặc tả gốc không định nghĩa — chọn cách bảo toàn QT-1/QT-5 (không bịa, khoảng phản ánh bất định thật) | [CALORIE-ENGINE §5b](../06-nutrition/CALORIE-ENGINE.md) |
| 2026-08-31 | Lần export đầu chuẩn hoá lại 140 file nguyên liệu về định dạng canonical | Bắt buộc để export ổn định (DoD DATA-007). Kiểm chứng bằng so sánh ngữ nghĩa: 0 mất mát; phát hiện và sửa 1 lỗi tròn số (hạt sen 0,675 g) | [DATA-007](./tasks/DATA-007.md) |
| 2026-08-31 | FNDDS/SR Links để ở **bảng riêng** (`fndds_foods`, `fndds_food_inputs`), không vào `dishes` | Đây là món Mỹ, chỉ dùng kiểm chứng engine; trộn vào kho món Việt là làm bẩn dữ liệu (cạm bẫy DATA-003) | [DATA-003](./tasks/DATA-003.md) |
| 2026-08-31 | Kết quả Nutrition5k được gọi là **sanity check**, không phải "độ chính xác" | Kiểm tra thực tế: calo của bộ này tính từ chính bảng nguyên liệu của nó (100% khớp <0,5 kcal), nên phép thử là vòng tròn. Trích nó như độ chính xác sẽ là con số vô nghĩa | [ACCURACY-SPEC §4](../06-nutrition/ACCURACY-SPEC.md) |
| 2026-08-31 | **Không hiệu chỉnh hệ số** dù hai nhóm chưa đạt ngưỡng | Nguyên nhân đã truy được (thiếu yield factor cho cá hấp; ngoại lệ ở nhóm kho) — nới hệ số để đạt số đẹp là đúng thứ cạm bẫy #2 của TEST-002 cấm | [TEST-002](./tasks/TEST-002.md) |
