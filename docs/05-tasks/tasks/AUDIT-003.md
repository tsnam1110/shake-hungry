# AUDIT-003 — RÀ SOÁT CENSUS TOÀN LỚP MỨC 3 (nguồn sống/chết, tổng hợp, cổng mức, ghi chú, components, hiển thị)

**Trạng thái:** ✅ HOÀN TẤT (2026-11-01) — **không sửa dữ liệu/code trong đợt**; đầu ra: nhóm 🔴 trích dẫn AX3-01…03 (4 URL / 6 dòng), nhóm 🟡 AX3-05…10, danh sách khắc phục AUDIT-003-FIX (báo cáo §9). Đã tiếp nhận và phân xử ca chuyển **FIX-TCM4-4** từ AUDIT-004 (ca-doi — xem AX3-02).
**AUDIT-003-FIX: ✅ ĐÃ THỰC THI XONG (2026-11-01)** — toàn bộ danh sách §3 đã xử lý, kết quả tại §5 + phụ lục quét bù TCM-001d tại §6. Mọi thay đổi chỉ trong `source_ref`/diễn đạt ghi chú + danh sách nguồn của ghi chú — 0 thay đổi giá trị tính/vị/hành, định lượng, mức.
**Phụ thuộc:** TCM-PROMOTE-002 (kho 654 món / 434 nguyên liệu / 227 NL có nhãn / 413 dòng nhãn tại baseline rà).
**Căn cứ:** chủ dự án giao 2026-11-01 — chặng cuối chương trình Mức 3, chuẩn AUDIT-002: census 100%, hai lượt, phát hiện 🔴 kiểm chứng độc lập lượt 2 trước khi kết luận.
**Báo cáo đầy đủ:** `~/workspace/goals/l-c-i-shake-hungry-development/files/bao-cao-audit-003-ra-soat-lop-muc-3-2026-11-01.md`.

---

## 1. Bao phủ & phương pháp

- **Baseline:** trích xuất census trên trạng thái 413 dòng nhãn. Giữa đợt, FIX-TCM4 (`a0414e2`/`a7fcb56`) nâng corpus lên 436 dòng (TCM-001d) — phán quyết tổng hợp/cổng gắn với baseline 413; 23 dòng mới chưa qua census nguồn của đợt này (báo cáo §0b).
- **Môi trường:** DB kiểm chứng riêng `shake_hungry_audit3` (migrate:fresh --seed từ `data/` + recompute-tcm → 648 tổng hợp: 500/98/50). Validate 0 🔴 / 24 🟡 / 3.344 bản ghi. Pest tại thời điểm chạy: 162/162 (gồm ExportStabilityTest + TcmFoundationTest đạt).
- **Tổng hợp độc lập:** Python viết lại từ tài liệu thiết kế (không dịch từ PHP) đối chiếu DB: **0/648 lệch** ở cả 5 trường. API: món <30% → `tcm: null`; món 59,67% → `is_partial`, confidence low — quy tắc ngưỡng đạt.
- **Cổng mức hai chiều (coverage độc lập):** 187/187 Mức 3 đạt; 114/114 nâng bù vẫn ≥60% (0 trôi), số % trong level_note khớp 114/114; 0 ca sót đủ điều kiện ở cả hai chiều ngược.
- **Census URL hai kênh:** curl 596/596 URL (131 nhãn + 465 ghi chú); mở trang đọc nội dung bởi 7 kiểm chứng viên độc lập — nhãn 131/131 có lần mở, ghi chú đọc được 359/465 (106 URL bị dịch vụ mở trang chặn 429/lỗi fetch → chưa kết luận, có lượt curl phủ trạng thái).
- **Ghi chú:** rà khung y tế bị cấm (2 bộ mẫu) trên 463/463 ghi chú: **0 vi phạm thật**. Phân tích hợp tập nguồn theo ghi chú: ≥12 cụm phủ chéo xác nhận.
- **Components:** 39/39 liên kết tồn tại + hợp lý ngữ nghĩa; traits∩components = ∅.
- **Hiển thị:** render DOM thật 55/55 ca đạt chuẩn TCM-003 (3 dòng màu/icon + popup) trên mọi trạng thái.

## 2. Kết quả chính

- **Giá trị tính vị: 0 sai** (census nguồn khớp nguyên văn; hội tụ với AUDIT-004: 413/413 xác minh, sai 0/413).
- **🔴 AX3-01 `ca-chim`:** URL trích dẫn sai loài — ký tự 鯇 (cá trắm cỏ, 溫/甘) thay 鯧 (cá chim, 平/甘).
- **🔴 AX3-02 `ca-doi` ×2 (= FIX-TCM4-4):** URL trượt ký tự （鱸魚 thay 鯔魚） trả 404 hai kênh; bản phồn thể đúng ký tự cũng rot (AUDIT-004); bản giản thể cùng mục còn nguyên văn.
- **🔴 AX3-03 lớp "URL dính dấu chấm cuối" (3 dòng):** dau-do-hat ×2 (URL tcmfans tại census; FIX-TCM4-1 đã đổi sang A+ 赤小豆 nhưng URL mới vẫn dính dấu chấm) + rau-diep-ca #2 (zhongyifangji `id/394.`). Token trích ra được → 404; đích không-dấu-chấm tồn tại đúng nội dung; giá trị gieo đều đúng.
- **🟡 AX3-05…10:** 8 dòng không-URL (nội dung kiểm chứng 8/8 tại trang mục); hat-tieu-den chưa kết luận nội dung; 106 URL ghi chú chưa kết luận (6 ghi chú có nguồn then chốt: thit-rang-chay-canh, bun-ken, canh-cua-mong-toi, lekima, che-ba-ba, bun-hen); chi tiết phụ chưa có căn cứ đã mở trong một số ghi chú (AX3-08); diễn đạt chè Thái (AX3-09); corroboration định danh mam-ca-ro từ nguồn của chính ghi chú (AX3-10).

## 3. Khắc phục đề xuất (AUDIT-003-FIX — ✅ đã thực thi, kết quả §5)

Theo báo cáo §9: sửa ký tự URL ca-chim; thay URL ca-doi bằng bản giản thể 鯔魚； tách dấu chấm cuối ở 3 token; bổ sung URL cho 8 dòng không-URL; kiểm lại hat-tieu-den; census bổ sung 106 URL ghi chú; xử lý chi tiết dư AX3-08 + diễn đạt chè Thái; quét lại cổng + census cho 23 dòng TCM-001d. Mọi thay đổi chỉ trong `source_ref`/diễn đạt ghi chú — không đụng giá trị, định lượng, mức.

## 4. Kiểm chứng cuối

- Đối chiếu phân loại cuối: nhãn 413/413 dòng có phán quyết; ghi chú 463/463 có phán quyết ở tầng cấu trúc + diễn đạt, 359/465 URL có phán quyết nội dung (phần còn lại ghi rõ chưa kết luận + lý do công cụ).
- Không sửa dữ liệu/code trong đợt; DB kiểm chứng `shake_hungry_audit3` giữ để tái kiểm.

## 5. AUDIT-003-FIX — kết quả khắc phục (2026-11-01)

Nguyên tắc giữ nguyên: chỉ sửa `source_ref`/diễn đạt ghi chú; mọi URL đích đều mở lại kiểm chứng nguyên văn TRƯỚC khi ghi.

- **AX3-01 `ca-chim` ✅:** URL sửa 鯇→鯧 (`%E9%AF%A7%E9%AD%9A` — 鯧魚， *Pampus argenteus*). Trang đích: 「味甘；性平」+ infobox 藥性平/藥味甘 — khớp giá trị đã lưu (bình/cam).
- **AX3-02 `ca-doi` ×2 ✅:** cả 2 dòng thay sang bản giản thể cùng mục A+ (`a-hospital.com/w/%E9%B2%BB%E9%B1%BC` — 鲻鱼， *Mugil cephalus*): infobox 药性平/药味甘， 食疗作用「鲻鱼肉性平，味甘咸」— khớp; đoạn giải thích thay URL ghi kèm trong ref (bản phồn thể đúng ký tự cũng đã rot).
- **AX3-03 dấu chấm cuối ✅:** tách ở đúng 3 token (`dau-do-hat` ×2 — tách khỏi cả URL A+ 赤小豆 mới; `rau-diep-ca` #2 id/394); đích không-dấu-chấm mở sống đúng nội dung. Quét lại TOÀN corpus (mọi chuỗi trong `data/**/*.json`, regex token URL): **0 token còn dính dấu chấm câu ở cuối URL**.
- **AX3-05 ✅:** bổ sung URL đã mở kiểm chứng nguyên văn cho 8/8 dòng, ghi chú "(bổ sung theo AUDIT-003-FIX 2026-11-01)" trong ref: `so-diep` ×4 → 乾貝 (`cht.a-hospital.com/w/%E4%B9%BE%E8%B2%9D`); `dau-trang` ×2 → 白扁豆； `ca-phe-den` → 咖啡； `cha-la` → 無漏子.
- **AX3-06 `hat-tieu-den` ✅ KẾT LUẬN:** trang tcmfans nay mở được: "Property and flavor: heat in nature, tastes pungent" = nhiệt/tân — **khớp giá trị đã lưu, không sửa**.
- **AX3-07 census bổ sung 106 URL ghi chú ✅:** kết quả **99 alive-match · 5 alive-mismatch · 1 dead · 1 inconclusive** → tỉ lệ URL ghi chú có phán quyết nội dung toàn cục: **464/465 (99,8%)** (từ 359/465 = 77,2%). 6 ghi chú then chốt: `thit-rang-chay-canh` + `canh-cua-mong-toi` ✅ (Báo Phụ nữ Thủ đô sống, khớp cả "công thức vàng" và gạch cua chưng); `lekima` ✅ (Báo Quảng Trị khớp nguyên văn; phần mâm ngũ quả Tết do nguồn Dân Trí đỡ); `che-ba-ba` ✅ (Dân Việt); `bun-ken`: Dân Việt ✅ đỡ sự kiện chính, **wiki Bún kèn CHẾT xác nhận** (curl HTTP 404 — phân xử dứt điểm mâu thuẫn hai kênh của AUDIT-003) → đã gỡ nguồn chết khỏi ghi chú + gọn chi tiết Hà Tiên–Phú Quốc không có trong Dân Việt (xem AX3-08); `bun-hen`: nguồn chính vietnamtourism đã kết luận ở AUDIT-003, PDF huetc là **tồn cuối duy nhất** (xem dưới).
- **5 ca alive-mismatch — đều khép ở tầng hợp tập nguồn** (mở kiểm chứng nguồn còn lại của chính ghi chú): `bo-kho` — en.wiki Bò kho đỡ toàn bộ (Pháp thuộc, pot-au-feu/bœuf bourguignon, phân xuất xứ Pháp–Hoa–Việt, không nước màu/nền cà chua–sả); `dua` — wiki Festival Dừa đỡ nguyên văn nửa đầu (5 kỳ 2009–2019, quy mô quốc gia từ 2012, Bến Tre diện tích dừa lớn nhất); `xoi-man` — Dân Việt "Xôi Sài Gòn" đỡ toàn bộ (hai nhóm mặn/ngọt, xôi mặn muộn cho người tứ xứ, thành phần, thay bữa chính); `goi-ga` — wiki Nộm đỡ gà xé phay (gà luộc, bắp cải, hành tây, rau răm) + "món nộm truyền thống trong cỗ Tết miền Bắc"; `mam-tom-chua` — Thương hiệu & Công luận đỡ nguyên văn tích Gò Công/Từ Dụ/Thiệu Trị/Tự Đức/ngự thiện, Người Lao Động đỡ chôn vại + đường Đặng Trần Côn. **Không ghi chú nào còn đứng trên khẳng định thiếu căn cứ.**
- **AX3-08 chi tiết phụ ✅:** GỌN diễn đạt (bỏ chi tiết không nguồn nào đỡ, giữ sự kiện chính): `oc-len-xao-dua` (bỏ chặt đuôi/cách hút; tên "linh hoa" + Năm Căn/Gành Hào + rừng ngập mặn do wiki Ốc len đỡ), `ca-keo-kho-tieu` (bỏ "khan hiếm dần"; kết bằng "món ăn gợi nhớ quê xưa" theo đúng Thanh Niên), `bun-mam` (tôm/mực → cá lóc, nấm rơm, cà tím theo VnExpress), `nho` (bỏ "hai vụ trong năm"), `dua-thom` (bỏ câu gọi tên khóm/thơm tuỳ vùng), `banh-khoai-mi-nuong` (bỏ "vườn nhà"), `me-chin` (bỏ mùa chín rộ), `nhan` (bỏ mùa 7–9 + vùng trồng ngoài Hưng Yên), `lau-ca-me` (bỏ "ít nhất 88 quốc gia" + gốc bắc/đông bắc Á), `bo-qua` (bỏ "đất bazan"), `cha-la` quả (bỏ gốc Lưỡng Hà–Ai Cập). BỔ NGUỒN: `mien-luon` thêm VietnamNet "Lươn đồng xứ Nghệ" (alive-match ở census bổ sung, có Yên Thành "thủ phủ lươn đồng") cho chi tiết Yên Thành. CÓ CĂN CỨ — KHÔNG SỬA: `canh-cua` (gạch chưng có nguyên văn trong nguồn), `com-ruou` ("quan trọng thứ hai"/"Tết Nửa năm" nguyên văn Thanh Niên), `xoi-la-cam` (wiki Xôi: "nước sắc của lá cẩm để lấy màu tím đỏ"), `hen-xao` (Thanh Niên đỡ toàn bộ tích Cồn Hến — dẫn "Theo Thanh Niên" là đúng), `lekima`, `hong-gion`, `banh-cam`. KHÔNG TỒN TẠI TRONG GHI CHÚ ĐÃ LƯU (kiểm lại trực tiếp file + bản gán census): `mam-ca-loc` — 4 chi tiết bị gắn cờ (tơ hồng, thính 3 ngày 3 đêm, rau đắng/bầu, cá sặc) không có trong văn bản ghi chú; mọi khẳng định hiện có đã khớp Tuổi Trẻ ở AUDIT-003; `bun-mam-chay` — chi tiết chao chỉ nằm ở `import_note` nội bộ (hồ sơ LV2-012), không ở ghi chú văn hoá.
- **AX3-09 chè Thái ✅:** ghi chú là văn bản cấp MÓN; đối chiếu bài Thanh Niên "chè tứ xứ": xuất xứ Thái Lan, mít/thốt nốt/nhãn/rau câu, nước cốt dừa + sữa tươi, sầu riêng, dãy quán Nguyễn Tri Phương Q10 — khớp; đã chuẩn hoá "sữa" → "sữa tươi" theo nguồn. Sự kiện cấp QUÁN Ý Phương ("tự nghĩ ra", sữa đặc) **không có trong ghi chú và không được đưa vào** — hai tầng món/quán tách bạch.
- **AX3-10:** không đụng (chỉ ghi nhận ở AUDIT-003, chờ chủ dự án quyết định danh).

**Kiểm chứng sau sửa (DB kiểm chứng riêng `shake_hungry_audit3`, seed lại từ `data/`):** validate **0 🔴** / 24 🟡 / 3.344 bản ghi; recompute-tcm **648 món: 500 ≥60% · 98 ở 30–60% · 50 <30% — không đổi** so với trước sửa (sửa ref/ghi chú không làm lệch tổng hợp); Pest **167/167** (gồm ExportStabilityTest đạt — vòng seed→export vẫn là điểm cố định trên toàn bộ file đã sửa).

**Tồn cuối (1 URL):** PDF Tập san CĐ Du lịch Huế (huetc.edu.vn) cho `bun-hen` — 3 kênh (dịch vụ mở trang ×2 lượt, curl) đều lỗi vận chuyển, chưa từng nhận HTTP status nên **không đủ cơ sở kết luận chết**; ghi chú `bun-hen` không đứng trên nguồn này một mình (nguồn Cục Du lịch Quốc gia đã kết luận sống-khớp). Giữ nguyên trong danh sách nguồn kèm trạng thái chưa kết luận; rà lại ở vòng LINK-ROT hằng tuần.

## 6. Phụ lục — quét bù TCM-001d (23 dòng / 13 nguyên liệu, corpus 436 dòng)

Census nguồn mở nguyên văn **8/8 URL duy nhất, 23/23 dòng khớp**: A+ 蛤蜊 (ngheu ×2: 「咸，寒」+ 《飲膳正要》「味甘，大寒」); A+ 人面子 (qua-sau ×6: 「味甘、酸，性涼」+ 《廣志》平 + 《本草求原》寒 + 《嶺南採藥錄》平， Latin *Dracontomelon duperreanum*); Sức khỏe & Đời sống đậu bắp (dau-bap: "vị ngọt, tính mát" — lương y Minh Phúc); CMRO Hồng Kông (bi-dao 「甘淡微寒」; can-tay 「甘寒」); Wikisource 《飲膳正要》菜品 (冬瓜「味甘、平、微寒」→ dòng bi-dao thứ hai; 黃瓜「味甘、平、寒」→ dua-leo ×2; 藕「味甘、平」→ cu-sen; 茄子「味甘、寒」→ ca-tim); Wikisource 《本草綱目》蟲之四 (蛙「甘，寒，無毒」→ ech) + 獸之一 (腸「甘，微寒，無毒」→ long-lon); Tuệ Tĩnh (nhathuocngocanh: Gia ác "vị ngọt, tính mát hơi độc" → thit-vit ×2; Trư tâm "vị mặn ngọt, tính hàn" → tim-lon ×2).

**Quét cổng + tổng hợp lại trên corpus 436 dòng (sau FIX, DB `shake_hungry_audit3`):** bức tranh mức **187 Mức 3 · 285 Mức 2 · 114 Mức 2 (có note) · 68 Mức 1 = 654** — không đổi; 187/187 Mức 3 đạt phủ ≥60% + đủ ghi chú; 114/114 nâng bù vẫn ≥60% (0 trôi); 0 món Mức 1 đủ cả hai tiêu chí bị sót; `has_conflicts` = 259 món; 436 dòng nhãn / 227 nguyên liệu có nhãn; ghi chú 388/654 món + 75/82 quả (bun-ken giữ ghi chú sau khi gỡ nguồn chết).
