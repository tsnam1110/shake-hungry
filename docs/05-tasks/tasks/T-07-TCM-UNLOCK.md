# T-07 — Tính vị theo điểm mở khoá: chấm điểm hàng đợi 84 nguyên liệu chờ nguồn + gieo đợt mở khoá 2026-11-02

**Trạng thái:** ✅ HOÀN TẤT ĐỢT (2026-11-02) — chấm điểm đủ 84/84; nghiên cứu theo thứ tự điểm; gieo **5 nguyên liệu / 11 dòng nhãn**; **6 món lên Mức 3**, **1 món nâng bù "Mức 2 (có note)"**; coverage ≥60%: **500 → 512 món**.
**Phụ thuộc:** TCM-001 (bản đồ chờ 84 mục, nhóm (ii)), TCM-001d, AUDIT-004, TCM-PROMOTE-002/003.
**Căn cứ:** tồn đọng T-07 trong danh sách tồn đọng + phiếu quyết định 2026-11-01; chủ dự án chỉ đạo xử lý toàn bộ tồn đọng 2026-11-02. Kỷ luật QT-1/QT-6 + kế hoạch Mức 3 §2: nhãn chỉ từ nguồn đạt chuẩn đã mở kiểm chứng tận nơi; xung đột liên nguồn gieo song song; ánh xạ chi/bộ phận phải ghi rõ + confidence trần low; **không mở tiền lệ `flavor=null`** (QD-14 đã chốt HOÃN — `rau-mui` không thuộc hàng đợi này và giữ nguyên chờ).

---

## 1. Phương pháp chấm điểm (Bước 1)

Điểm mở khoá tính lại từ file `data/` bằng đúng logic của engine (`TcmAggregator` + `DishNutritionService::resolveGrams`: bỏ dòng `is_discarded`, dòng không quy đổi được gram loại khỏi cả tử và mẫu; nguyên liệu "có nhãn" = có ≥1 dòng trait với `nature` khác null):

- **Cột A — số món phủ <60%** mà nguyên liệu tham gia (điểm chính theo brief).
- **Cột B — tổng khối lượng đóng góp (g)** trong các món ở cột A.
- **Cột C — số món vượt cổng ≥60% nếu CHỈ riêng nguyên liệu này được gieo** (cột phụ do đợt này tính thêm để ưu tiên nghiên cứu).
- Cột D/E: tổng số món + tổng khối lượng toàn kho (bối cảnh).

Đối chiếu độc lập: phân bố phủ tính từ file **khớp tuyệt đối** DB kiểm chứng riêng `shake_hungry_t07` sau seed + `lacdoi:recompute-tcm` ở baseline đầu đợt: **500 món ≥60% · 98 món 30–<60% · 50 món <30%** (648 món có tổng hợp), bức tranh mức **194 Mức 3 · 286 Mức 2 thường · 107 Mức 2 (có note) · 67 Mức 1 = 654**.

## 2. Bảng điểm 84 nguyên liệu (xếp hạng giảm dần theo cột A, sau đó cột B)

| # | Nguyên liệu | A: món <60% | B: khối lượng (g) | C: tự vượt cổng | D: tổng món | E: tổng KL (g) |
|---|---|---|---|---|---|---|
| 1 | `chanh` | 24 | 202 | 2 | 63 | 519 |
| 2 | `bot-mi` | 15 | 531 | 11 | 20 | 656 |
| 3 | `bot-nang` | 11 | 300 | 8 | 66 | 921 |
| 4 | `trung-cut` | 10 | 174 | 3 | 24 | 483 |
| 5 | `la-dua` | 9 | 8 | 1 | 42 | 54 |
| 6 | `ca-nuc` | 7 | 890 | 7 | 7 | 890 |
| 7 | `bap-chuoi` | 6 | 430 | 3 | 17 | 785 |
| 8 | `ca-troi` | 5 | 740 | 5 | 6 | 850 |
| 9 | `ca-keo` | 5 | 705 | 5 | 5 | 705 |
| 10 | `khoai-mi` | 5 | 600 | 5 | 6 | 630 |
| 11 | `thi-la` | 5 | 19 | 0 | 26 | 100 |
| 12 | `thit-ngan` | 4 | 610 | 4 | 4 | 610 |
| 13 | `ca-bong` | 4 | 570 | 4 | 4 | 570 |
| 14 | `mong-toi` | 4 | 420 | 4 | 5 | 460 |
| 15 | `tai-lon` | 4 | 240 | 0 | 6 | 350 |
| 16 | `dua-thom` | 4 | 200 | 1 | 15 | 635 |
| 17 | `ngo-gai` | 4 | 8 | 0 | 20 | 44 |
| 18 | `ca-trich` | 3 | 390 | 3 | 3 | 390 |
| 19 | `ca-linh` | 3 | 315 | 2 | 3 | 315 |
| 20 | `la-giang` | 3 | 80 | 1 | 4 | 120 |
| 21 | `ca-dieu-hong` | 2 | 360 | 2 | 2 | 360 |
| 22 | `sup-lo-xanh` | 2 | 220 | 2 | 2 | 220 |
| 23 | `long-non-lon` | 2 | 200 | 1 | 3 | 240 |
| 24 | `ca-thac-lac` | 2 | 180 | 2 | 4 | 248 |
| 25 | `ruoi` | 2 | 180 | 2 | 2 | 180 |
| 26 | `rau-day` | 2 | 110 | 1 | 2 | 110 |
| 27 | `ca-com` | 2 | 108 | 1 | 2 | 108 |
| 28 | `sau-rieng` | 2 | 60 | 2 | 2 | 60 |
| 29 | `rau-ngo` | 2 | 6 | 0 | 8 | 26 |
| 30 | `oc-len` | 1 | 300 | 1 | 1 | 300 |
| 31 | `be-be` | 1 | 250 | 1 | 2 | 330 |
| 32 | `ca-bac-ma` | 1 | 150 | 1 | 1 | 150 |
| 33 | `ca-hu` | 1 | 150 | 1 | 1 | 150 |
| 34 | `ca-thu` | 1 | 150 | 1 | 3 | 280 |
| 35 | `nam-dui-ga` | 1 | 130 | 1 | 2 | 150 |
| 36 | `ca-mai` | 1 | 120 | 1 | 1 | 120 |
| 37 | `ngon-bi` | 1 | 120 | 1 | 1 | 120 |
| 38 | `ca-chuon` | 1 | 90 | 0 | 1 | 90 |
| 39 | `bong-dien-dien` | 1 | 80 | 0 | 3 | 130 |
| 40 | `ngo-sen` | 1 | 80 | 1 | 2 | 170 |
| 41 | `rau-nhut` | 1 | 80 | 1 | 1 | 80 |
| 42 | `bong-sung` | 1 | 65 | 0 | 1 | 65 |
| 43 | `dau-ngu` | 1 | 40 | 1 | 1 | 40 |
| 44 | `mit-chin` | 1 | 30 | 0 | 2 | 150 |
| 45 | `tran-chau` | 1 | 20 | 0 | 1 | 20 |
| 46 | `duong-thot-not` | 1 | 13 | 1 | 24 | 266 |
| 47 | `dau-me` | 1 | 5 | 0 | 6 | 20 |
| 48 | `hanh-nhan` | 1 | 5 | 1 | 1 | 5 |
| 49 | `ngo-om` | 1 | 3 | 0 | 1 | 3 |
| 50 | `ca-basa` | 0 | 0 | 0 | 2 | 130 |
| 51 | `ca-dia` | 0 | 0 | 0 | 0 | 0 |
| 52 | `ca-duoi` | 0 | 0 | 0 | 1 | 120 |
| 53 | `ca-hoi` | 0 | 0 | 0 | 0 | 0 |
| 54 | `ca-ngu-van` | 0 | 0 | 0 | 0 | 0 |
| 55 | `ca-ro-phi` | 0 | 0 | 0 | 1 | 80 |
| 56 | `oc-hut` | 0 | 0 | 0 | 0 | 0 |
| 57 | `chim-cut` | 0 | 0 | 0 | 0 | 0 |
| 58 | `duoi-bo` | 0 | 0 | 0 | 0 | 0 |
| 59 | `luoi-lon` | 0 | 0 | 0 | 0 | 0 |
| 60 | `me-ga` | 0 | 0 | 0 | 1 | 30 |
| 61 | `bot-bang` | 0 | 0 | 0 | 5 | 47 |
| 62 | `bot-khoai` | 0 | 0 | 0 | 4 | 50 |
| 63 | `la-cam` | 0 | 0 | 0 | 1 | 2 |
| 64 | `la-gai` | 0 | 0 | 0 | 1 | 10 |
| 65 | `la-khuc` | 0 | 0 | 0 | 1 | 10 |
| 66 | `la-nguyet-que` | 0 | 0 | 0 | 2 | 1 |
| 67 | `la-suong-sam` | 0 | 0 | 0 | 0 | 0 |
| 68 | `nam-kim-cham` | 0 | 0 | 0 | 3 | 90 |
| 69 | `sup-lo-trang` | 0 | 0 | 0 | 2 | 45 |
| 70 | `chanh-day` | 0 | 0 | 0 | 0 | 0 |
| 71 | `coc` | 0 | 0 | 0 | 0 | 0 |
| 72 | `dua-bo` | 0 | 0 | 0 | 0 | 0 |
| 73 | `mang-cut` | 0 | 0 | 0 | 0 | 0 |
| 74 | `mit-to-nu` | 0 | 0 | 0 | 0 | 0 |
| 75 | `nhot` | 0 | 0 | 0 | 0 | 0 |
| 76 | `qua-sim` | 0 | 0 | 0 | 0 | 0 |
| 77 | `tam-bop` | 0 | 0 | 0 | 0 | 0 |
| 78 | `tao-meo` | 0 | 0 | 0 | 0 | 0 |
| 79 | `tao-ta` | 0 | 0 | 0 | 0 | 0 |
| 80 | `thot-not` | 0 | 0 | 0 | 0 | 0 |
| 81 | `vu-sua` | 0 | 0 | 0 | 0 | 0 |
| 82 | `hat-dieu-mau` | 0 | 0 | 0 | 2 | 2 |
| 83 | `hat-doi` | 0 | 0 | 0 | 3 | 7 |
| 84 | `mac-khen` | 0 | 0 | 0 | 4 | 8 |
Đọc bảng: nhóm điểm cao nhất bị khoá không phải vì thiếu nguồn mà vì **cấu trúc** — `bot-mi` (C=11) và cụm `bot-nang`/`khoai-mi` (C=8/5) có trần mở khoá lớn nhất nhưng hồ sơ nguồn đã kết luận dứt điểm ở TCM-001 (xem §4). Nhóm cá VN điểm cao (`ca-nuc`, `ca-troi`, `ca-keo`, `ca-bong`) chưa có chuyên khảo truy cập được cho đúng loài — giữ nguyên hồ sơ TCM-001, không tra lại vô ích trong đợt này. Đợt này tập trung nghiên cứu các mục điểm cao **chưa có kết luận dứt điểm**: `chanh`, `trung-cut`, `bap-chuoi`, `mong-toi`, `dua-thom`, `thi-la`, `tai-lon`, `long-non-lon`, `sup-lo-xanh`, `sup-lo-trang`, `sau-rieng`, `ca-thu`, `ruoi`, `ngon-bi`, `nam-kim-cham`, `nam-dui-ga`.

## 3. Kết quả gieo (Bước 2 + 3) — 5 nguyên liệu / 11 dòng

Mọi trang nguồn dưới đây đều do phiên gieo **tự mở kiểm chứng nguyên văn trong phiên** (2026-11-02).

| Nguyên liệu | Dòng gieo | Nguồn đã mở kiểm chứng | Confidence / ghi chú phạm vi |
|---|---|---|---|
| `mong-toi` | hàn/cam, hàn/toan | A+醫學百科 mục 落葵：《中華本草》「甘；酸；性寒」; trang ghi Latin *Basella alba* L. — **khớp đúng loài + bộ phận** (lá/toàn cây) của file | medium |
| `mong-toi` | lương/cam | Cùng trang, phần 食療價值 dẫn 《本草綱目》「落葵，甘，微寒，冷滑」(微寒 = lương) — lệch một bậc với 《中華本草》, giữ song song | medium |
| `chanh` | lương/toan | Y văn VN: dieutri.vn, mục Chanh (*Citrus aurantifolia* — đúng loài chanh ta):「Quả có vị chua, tính mát」; câu chữ được thaythuoccuaban.com chép đồng nhất | medium |
| `chanh` | binh/toan, binh/cam | thaythuoccuaban.com, mục vị thuốc Chanh: dòng tổng kết「Chanh vị chua ngọt, tính bình」— cấp cây, khác dòng riêng cho quả; giữ song song | low |
| `ruoi` | on/cam | A+醫學百科 mục 禾蟲：《中華本草》「味甘；性温」, Latin *Tylorrhychus heterochaetus* — **khớp đúng loài** của file (*Tylorrhynchus heterochaetus*); 《中藥大辭典》dẫn 《本草求原》「甘，温」nhất quán | medium |
| `bap-chuoi` | luong/cam, luong/tan | A+醫學百科 mục 芭蕉花：《中華本草》「味甘；微辛；性凉」 | **low** — chuyên khảo gắn loài *Musa basjoo*, file là *Musa* spp. nói chung: **ánh xạ cấp chi**, ghi rõ trong `source_ref` |
| `ngon-bi` | luong/cam, luong/kho | A+醫學百科 mục 南瓜藤：《本草再新》「味甘苦，性微寒，無毒。」Loài *Cucurbita moschata* khớp file | **low** — chuyên khảo gắn với thân/dây, file ăn ngọn non (thân non + lá): ghi rõ phạm vi bộ phận trong `source_ref`. Hộp thông tin trang ghi 藥性寒 không rõ xuất xứ — không lập dòng riêng |

Tổng nhãn toàn kho sau đợt: **436 → 447 dòng**; nguyên liệu có nhãn **227 → 232/434** (đếm trên DB kiểm chứng `shake_hungry_t07`: 447 dòng, 232 NL phân biệt có dòng `nature`). Hàng đợi nguồn (ii) còn **79 mục**.

### Dịch chuyển coverage + mức (engine thật, DB `shake_hungry_t07`, trước → sau)

- Coverage: **≥60%: 500 → 512 (+12)** · 30–<60%: 98 → 92 · <30%: 50 → 44 (trên 648 món có tổng hợp; 6 món còn lại không có tổng hợp như trước).
- 12 món vượt cổng ≥60: `bun-dau-mam-tom` (60,94%), `canh-cua-mong-toi` (79,03%), `canh-mong-toi-nau-ngheu` (95,93%), `canh-mong-toi-tom` (94,20%), `cha-ruoi` (95,82%), `goi-bap-chuoi-tom` (96,41%), `goi-ngo-sen` (61,41%), `mam-ruoi` (94,24%), `mong-toi-xao-toi` (93,59%), `ngon-bi-xao-toi` (92,65%), `nom-hoa-chuoi` (96,52%), `nom-hoa-chuoi-tom-the` (84,37%).
- **Thăng Mức 3 (6 món)** — Mức 2 thường + phủ ≥60% + đã có ghi chú văn hoá, theo tiền lệ TCM-PROMOTE: `bun-dau-mam-tom`, `canh-cua-mong-toi`, `cha-ruoi`, `goi-bap-chuoi-tom`, `ngon-bi-xao-toi`, `nom-hoa-chuoi`.
- **Nâng bù "Mức 2 (có note)" (1 món):** `goi-ngo-sen` — Mức 1 + phủ 61,41% (dòng `chanh` 10g trong nước trộn, gieo đợt này) + có ghi chú văn hoá; `level_basis='tcm_compensation'` + `level_note` soạn từ hồ sơ giữ Mức 1 của chính món (cổng nguồn công thức V12 chưa đạt; trần `ngo-sen` trong hồ sơ cũ đã được T-05 gỡ nhưng không thay cổng nguồn).
- 5 món vượt cổng còn lại **không đổi mức**: `canh-mong-toi-nau-ngheu`, `canh-mong-toi-tom`, `mong-toi-xao-toi`, `nom-hoa-chuoi-tom-the` (Mức 2 thường, chưa có ghi chú văn hoá — chờ tuyến VN-001/T-06) và `mam-ruoi` (Mức 1, chưa có ghi chú).
- `has_conflicts`: **302 món** sau đợt (tăng do `mong-toi` và `chanh` mang hai truyền thống lệch tính — hệ quả đúng thiết kế, hiển thị mâu thuẫn thay vì ép chọn).
- **Bức tranh mức cuối: 200 Mức 3 · 280 Mức 2 thường · 108 Mức 2 (có note) · 66 Mức 1 = 654.**

## 4. Nghiên cứu nhưng GIỮ CHỜ — hồ sơ bổ sung đợt này

| Nguyên liệu | Kết luận đợt này |
|---|---|
| `trung-cut` (điểm #4) | A+ không có mục 鵪鶉蛋 (404 cả hai domain). Chỉ tìm thấy phát biểu hiện đại (dược sĩ trên HK01:「甘、平」) — không phải chuyên khảo/y văn có tên sách cho **trứng**; truyền thống 《本草綱目》 là cho thịt chim cút, không áp sang trứng. Giữ chờ. |
| `dua-thom` (#16) | A+ không có mục 菠蘿 (404 cả hai domain). Các bảng web thứ cấp mâu thuẫn nhau (平 vs 微寒) và không truy được về nguồn có thẩm quyền. Giữ chờ; ứng viên tương lai: ấn phẩm CMRO về trái cây nếu truy cập được. |
| `sau-rieng` (#28) | Trang thương mại (xiangha) ghi「辛、甘，熱」— không đạt chuẩn. Câu「《本草綱目》榴槤味甘溫」lan truyền trên web **không có trong văn bản cổ thư** (sầu riêng du nhập sau Lý Thời Trân) — bác làm căn cứ. Giữ chờ. |
| `sup-lo-xanh` (#22), `sup-lo-trang` (#69) | Chỉ có bảng dân gian liệt tính (không vị, không nguồn có tên). A+ không có mục 西蘭花 (404). Giữ chờ. |
| `ca-thu` (#34) | Đồ giám食材 Đài Loan (thương mại, tự khai có thể sai sót) cho *Scomberomorus niphonius*「性平，味甘」— cùng chi với file nhưng không phải nguồn YHCT có thẩm quyền; A+ không có mục 馬鮫魚 (404). Giữ chờ, ghi ứng viên. |
| `nam-kim-cham` (#68) | Mục 冬菇 (bản Baidu của dự án thuật ngữ Cục Quản lý YHCT TQ) liệt 金針菇 là biệt danh của 冬菇 *Flammulina velutipes*「味甘、咸，性寒」. File là *F. filiformis* — loài tách khỏi phức hợp *velutipes* năm 2018; cộng điểm mở khoá = 0 → không gieo, ghi ứng viên chờ phân xử loài. |
| `nam-dui-ga` (#35) | A+ không có mục 杏鮑菇 (404, khớp hồ sơ TCM-001); tìm kiếm web không ra nguồn tính vị đạt chuẩn. Giữ chờ. |
| `thi-la` (#11) | A+ không có mục 蒔蘿 (404 cả hai domain); truyền thống chỉ kiểm chứng được cho hạt. Giữ chờ (lá). |
| `tai-lon` (#15) | A+ không có mục 豬耳 (404); không tìm thấy chuyên khảo cho tai lợn. Giữ chờ. |
| `long-non-lon` (#23) | Không có chuyên khảo riêng cho ruột non; truyền thống 豬腸 là ruột già (đã dùng cho `long-lon`). Không áp chéo bộ phận. Giữ chờ. |
| `bot-mi` (#2), `khoai-mi` (#10), `bot-nang` (#3) + cụm bột sắn | Giữ nguyên kết luận dứt điểm của TCM-001: sổ tay HK mục 麥米 định danh mơ hồ; A+ mục 木薯 chỉ cho lá, củ không có tính vị trong nguồn đã tra toàn trang. Trần mở khoá lớn nhất kho nhưng **không có đường nguồn** — không tra lại, không hạ chuẩn. |
| `la-dua` (#5) | Điểm món cao (9) nhưng khối lượng 8,5g toàn kho — gieo cũng không đổi phủ thực chất (tự vượt cổng 1 món ở biên). Không có ứng viên nguồn nào ngoài hồ sơ TCM-001. Giữ chờ sau các mục có khối lượng thật. |

Các mục còn lại của bảng điểm (điểm ≤2 hoặc điểm 0) giữ nguyên hồ sơ TCM-001 §Bản đồ cuối — đợt này không tra lại; thứ tự nghiên cứu tương lai đi theo đúng bảng điểm ở §2.

## 5. Kiểm chứng đợt

- `lacdoi:validate-data` trên DB kiểm chứng riêng `shake_hungry_t07`: **0 🔴** / 26 🟡 (các lớp 🟡 đã biết; V20 đạt cho cả 11 dòng mới).
- `lacdoi:recompute-tcm` (DB riêng): 648 tổng hợp; phân bố phủ + danh sách vượt cổng khớp tuyệt đối với tính toán độc lập từ file ở §1.
- Pest toàn bộ trên DB kiểm chứng riêng `shake_hungry_t07test`: **177/177 xanh (719 assertions)**, gồm ExportStabilityTest "xanh trên dữ liệu thật hiện tại của repo". Lượt chạy đầu cho 10 test SwapTrayTest đỏ vì HEAD bị các phiên song song commit (QD-ADJUDICATE-1/DOCKER/T-06) thay đổi giữa chừng — chạy lại nguyên trạng ở cây ổn định xanh tuyệt đối, SwapTrayTest chạy riêng cũng 3/3.
- Commit pathspec riêng (dữ liệu 5 NL + 7 món + tài liệu đợt) — không chạm file của các phiên song song.
- ROADMAP + CLAUDE §0 (bổ sung lần 45) đã cập nhật trong commit tài liệu của đợt này (hai file chỉ được sửa sau khi phiên QD-ADJUDICATE-1 commit xong và nhả cây). Mục bổ sung trong TCM-001.md đã được phiên QD-ADJUDICATE-1 commit kèm ở `f0b991b` (họ sửa cùng file cho QD-14) — nội dung trùng khớp bản ở cây làm việc.
- **Bức tranh mức toàn kho tại thời điểm land T-07** (đếm trực tiếp từ file, cây đã gồm QD-ADJUDICATE-1 + T-06/TCM-PROMOTE-004): **215 Mức 3 · 245 Mức 2 thường · 120 Mức 2 (có note) · 74 Mức 1 = 654**. Số 200/280/108/66 ở §3 là bức tranh nếu chỉ áp delta T-07 lên baseline đầu phiên — giữ cả hai để đối chiếu chéo với báo cáo của các phiên song song.
