# T-08…T-12 — XỬ LÝ CÁC VIỆC TỒN ĐỌNG CÒN LẠI

**Ngày mở:** 2026-11-02 · **Chỉ đạo:** chủ dự án "xử lý toàn bộ việc tồn đọng" (2026-11-02)
**Baseline:** HEAD `66646c5` · **Danh sách gốc:** `viec-ton-dong-va-phieu-quyet-dinh-2026-11-01.md` Phần A (bàn giao chủ dự án)

---

## T-08 — Chuẩn hoá quy trình duyệt `needs_review` ✅ XONG phần chuẩn bị

### Rà 3 tầng file ↔ DB ↔ admin

| Tầng | Kết quả rà |
|---|---|
| File `data/` | Món: 358/654 `needs_review=true`, 296 đã có `reviewed_by`/`reviewed_at`. Nguyên liệu: 434/434 `true`, 0 có vết duyệt. Quả: 82/82 `true`, 0 có vết duyệt |
| DB (seed) | Seeder của cả 3 loại mang đủ 4 trường `needs_review`/`reviewed_by`/`reviewed_at`/`review_note` hai chiều file↔DB; exporter viết ngược lại — vòng seed→export là điểm cố định (ExportStabilityTest) nên số đếm 3 tầng khớp nhau theo cơ chế |
| Admin | Cả 3 controller (Dish/Ingredient/Fruit) có cùng thao tác `review` + `bulk approve/reopen`: duyệt ⇒ `needs_review=false` + `reviewed_by` = email người duyệt + `reviewed_at` = now + ghi `AdminAuditLog`. Dashboard trả `pending_review` **riêng từng loại** |
| Ràng buộc | R10 (CHECK: hạ cờ phải có đủ vết duyệt) áp cho `ingredients` + `dishes` (migration 2026-09-01). Bảng `fruits` tạo sau (2026-10-31) có đủ 3 cột nhưng **không có CHECK R10**; `reviewed_at` của quả là `date` (tới ngày), hai bảng kia là `timestamp` |

**Kết luận:** cơ chế 3 tầng nhất quán; cái lệch là *ngữ nghĩa hàng đợi*: hàng đợi món là hàng đợi
sống thật (296 món đã duyệt qua admin), còn nguyên liệu/quả là hai hàng đợi **chưa bắt đầu** —
mọi kiểm chứng trước đây với chúng là kiểm chứng cấp chương trình (census/audit theo file task),
không phải duyệt từng bản ghi. Cộng gộp 358 + 434 + 82 thành một con số "chờ duyệt" là vô nghĩa.

### Quy ước đã áp dụng

Ghi thành văn bản tại [DATA-MODEL §Quy ước ngữ nghĩa `needs_review`](../../03-data-model/DATA-MODEL.md)
(bổ sung cùng commit): (1) một ngữ nghĩa duy nhất cho 3 loại — chưa qua duyệt biên tập của người
theo từng bản ghi; chỉ hạ cờ qua admin; (2) kiểm chứng chương trình ≠ duyệt, không hạ cờ thay;
(3) báo số hàng đợi theo từng loại, cấm gộp; (4) duyệt món theo gói rủi ro. **Không tự duyệt hộ
bất kỳ bản ghi nào** — 0 cờ bị hạ trong đợt này.

### Gói duyệt đã chuẩn bị

Đo lại tại HEAD 2026-11-02: hàng đợi món vẫn **358** món (Mức 3: 53 · Mức 2 thường: 204 ·
Mức 2 (có note): 57 · Mức 1: 44). Đã chia **17 gói × ~25 món** theo tầng rủi ro
(R1 = 101 món Mức 1 + nâng bù → 5 gói; R2 = 53 món Mức 3 → 3 gói; R3 = 204 món Mức 2 thường →
9 gói), mỗi gói có bảng slug/tên/mức/basis/confidence/số nguồn/số dòng nguyên liệu + cột
"Điểm cần mắt người nhìn" suy từ dữ liệu (dòng còn `llm_suggested`, nâng bù, thiếu ghi chú,
hồ sơ còn chờ quyết/429). Lưu tại thư mục bàn giao: `goi-duyet/README.md` + `goi-01-R1.md` …
`goi-17-R3.md`. Việc duyệt cuối vẫn là của chủ dự án trong `/admin`.

---

## T-09 — Q7: nguồn hệ số hao hụt thuỷ sản độc lập với USDA ✅ HỒ SƠ XONG (chưa đổi hệ số)

### Vấn đề

TEST-002: nhóm `hap` lệch **−21% đồng loạt** (hệ số khối lượng ngầm 0,79). Bảng USDA Cooking
Yields R2 đã nhập có **0 dòng thuỷ sản**; suy từ SR Legacy là vòng tròn vì USDA chính là vế
tham chiếu. Cần nguồn hao hụt thuỷ sản **đo độc lập** với USDA.

### Các nguồn tìm được (mở kiểm chứng 2026-11-02)

| # | Nguồn | Nội dung dùng được | Tính độc lập |
|---|---|---|---|
| 1 | **FSANZ** (Food Standards Australia New Zealand) — bảng *Weight change factors* chính thức cho Nutrition Panel Calculator: https://www.foodstandards.gov.au/business/labelling/nutrition-panel-calculator/weight-change-factors | Cá vây (fin fish): phi lê **hấp/luộc/nhúng = −16%** (yield **0,84**); nguyên con nướng lò/vi sóng −21% (0,79); nguyên con/phi lê nướng vỉ −24% (0,76); chiên −30% (0,70) | Cơ quan quốc gia Úc/NZ; bảng tự ghi "drawn from numerous local and overseas sources" — **có thể có chung tổ tiên một phần với AH-102**, nên coi là độc lập cấp cơ quan, chưa phải độc lập đo lường tuyệt đối |
| 2 | **MEXT Nhật Bản** — *Standard Tables of Food Composition in Japan*, Chương 1 **Bảng 15 重量変化率** (tỉ lệ thay đổi khối lượng khi nấu) cho từng thực phẩm, gồm cá `水煮` (luộc) và `蒸し` (hấp): https://www.mext.go.jp/component/a_menu/science/detail/__icsFiles/afieldfile/2015/12/24/1365334_1-0102.pdf | Bảng hao hụt theo từng loài/phương pháp của hệ thống FCT quốc gia Nhật — chính MEXT là nguồn tầng 6 đã được dự án chấp nhận cho số dinh dưỡng (Q6) | **Độc lập đo lường sạch nhất** trong các ứng viên: hệ đo quốc gia riêng, không suy từ USDA |
| 3 | **Singhato et al.** (Thái Lan, nghiên cứu ICP-MS/MS trên 20 loài thuỷ sản, công bố tạp chí, bảng số qua ResearchGate) | Yield factor đo thực nghiệm: cá nước ngọt **luộc 0,8–0,9**, cá biển **luộc 0,6–0,8**; mất ẩm khi luộc chỉ 1,1–6,6% (phần lớn chênh yield đến từ khâu bỏ phần không ăn được) | Đo thực nghiệm độc lập, loài châu Á gần với kho món VN; n nhỏ (3 mẫu/loài), yield gồm cả yếu tố phần ăn được nên không chép thẳng |
| 4 | **EuroFIR** — *Report on recipe calculation procedures* + hướng dẫn tính công thức 2016: https://www.eurofir.org/report-on-collection-of-rules-on-use-of-recipe-calculation-procedures-including-the-use-of-yield-and-retention-factors-for-imputing-nutrient-values-for-composite-foods/ | Khung phương pháp luận yield/retention factor ở cấp nguyên liệu vs cấp công thức; hệ số tổng hợp từ các FCT quốc gia châu Âu | Nguồn khung/kiểm chứng chéo, không phải bảng số sơ cấp |

### So sánh & khuyến nghị

- FSANZ phi lê hấp/luộc **0,84** và dải cá nước ngọt luộc của Thái **0,8–0,9** nhất quán nhau;
  cá biển/nguyên con thấp hơn rõ (0,6–0,8 Thái; 0,76–0,79 FSANZ nguyên con). Con số ngầm 0,79
  hiện tại khớp nhóm *nguyên con* chứ không khớp nhóm *phi lê* — gợi ý lỗi nằm ở việc áp một
  hệ số chung cho mọi dạng thuỷ sản hấp/luộc.
- **Khuyến nghị cho vòng hiệu chuẩn QA riêng (chưa áp ở đợt này):** đăng ký trước theo tiền lệ
  CALC-004 — thử hệ số ứng viên **0,84 cho cá phi lê hấp/luộc** và giữ dải thấp hơn cho cá
  nguyên con, đối chiếu trên tập tham chiếu độc lập (ưu tiên trích Bảng 15 của MEXT cho các
  loài gần kho VN + neo FSANZ), ngưỡng sửa như các vòng QA-002 trước. Đổi hệ số engine chỉ
  sau khi vòng đó đạt cổng — đợt này **không sửa hệ số nào**.

---

## T-10 — canh-bi-ro-suon ở cận −30% ✅ HỒ SƠ ĐÃ ĐỦ, KHÔNG SỬA DỮ LIỆU

Xác nhận hai nơi ghi hồ sơ đều đầy đủ và khớp nhau:

- `import_note` của chính file món (mục `[LINK-ROT-FIX 2026-11-01]`): nguồn thay thế Bếp Mina
  100 g sườn/người → file 70 g/tô = **−30%, đúng ngưỡng chưa vượt**; bí 120 g/tô trong dải;
  quy tắc "chỉ sửa khi lệch quá 30% so với đồng thuận nguồn sống" được trích dẫn; kết luận
  giữ nguyên định lượng, "ghi nhận cận sườn để rà soát ở đợt hiệu chuẩn sau".
- [AUDIT-FIX §8](AUDIT-FIX.md): cùng kết luận, kèm nguồn BlueStone 400 g/mẻ đối chiếu.

Không thiếu mục nào cần bổ sung; món giữ Mức 2, định lượng không đổi. Việc còn lại là của vòng
hiệu chuẩn sau (cùng vòng với khuyến nghị T-09 nếu chủ dự án mở vòng đó).

---

## T-11 — Thử lại URL chưa kết luận ✅ ĐÃ THỬ LẠI 2026-11-02 — VẪN CHƯA KẾT LUẬN

Thử lại qua dịch vụ fetch ngày 2026-11-02, cả 5 URL đều **lỗi vận chuyển, không URL nào trả
HTTP status** — đúng mẫu các vòng 2026-11-01, không đủ cơ sở kết luận chết:

| URL | Món | Kết quả thử lại |
|---|---|---|
| bachhoaxanh.com/…/cach-nau-canh-bong-tha-1272102 | canh-bong-tha | lỗi mạng qua kênh fetch |
| dienmayxanh.com/vao-bep/cach-nau-canh-bong-moc-son-22394 | canh-bong-tha | lỗi mạng qua kênh fetch |
| bloganngon.edu.vn/canh-bong-tha | canh-bong-tha | không phân giải được qua kênh fetch |
| nauchuananngon.vn/cach-lam-banh-phu-the | banh-xu-xe | không phân giải được qua kênh fetch |
| huetc.edu.vn/…/CƠM-HẾN-…pdf | bun-hen (nguồn 2 của ghi chú) | lỗi vận chuyển, chưa từng có HTTP status |

Đã ghi nhận lượt thử lại vào `import_note` của cả 3 món (kèm commit dữ liệu đợt này).
Trạng thái giữ nguyên: `canh-bong-tha` hồ sơ yếu 0/3 đọc được; `banh-xu-xe` 2 sống + 1 chưa
kết luận; `bun-hen` ghi chú vẫn có nguồn chính (Cục Du lịch Quốc gia) đã kết luận sống-khớp.
**Cron LINK-ROT hằng tuần tiếp tục theo dõi cả 5 URL.**

---

## T-12 — 7 quả chờ ghi chú văn hoá ✅ 2 VIẾT MỚI, 5 GIỮ CHỜ CÓ HỒ SƠ

Tra lại theo chuẩn VN-001 (nguồn biên tập/bách khoa mở kiểm chứng tận nơi; cấm khung sức khoẻ,
cấm nguồn thương mại/SEO) ngày 2026-11-02:

| Quả | Kết quả | Căn cứ |
|---|---|---|
| `coc` | ✅ **Đã viết ghi chú** | Wikipedia tiếng Việt (Cóc — *Spondias dulcis*: ăn tươi, hayko + rojak ở Indonesia/Malaysia) + tài liệu ĐH Duy Tân (cóc chấm muối ớt — quà rong phổ biến trong giới học trò VN; quả bán khắp chợ) — cả hai mở kiểm chứng toàn văn |
| `nho-than-go` | ✅ **Đã viết ghi chú** | Dân Việt 26/11/2017 (mở toàn văn): jaboticaba Nam Mỹ trồng ở Đà Lạt, quả mọc kín thân, cây nhà ở phường 10 nhập giống Brazil, "cơn sốt" quả 200–250k/kg, cây giống tới 10 triệu |
| `viet-quat` | ⏸ Giữ chờ — nhóm A | Tra lại: vẫn không có chất liệu biên tập về việt quất trong đời sống VN; chỉ còn trang SEO/thương mại nhập khẩu |
| `mam-xoi` | ⏸ Giữ chờ — nhóm B | Bằng chứng mới đã xét: VietnamNet 19/10/2026 viết về mâm xôi **bản địa** *Rubus alceifolius* và phân biệt rõ với phúc bồn tử nhập *R. idaeus* trong file — bài lại mang khung dinh dưỡng. Viết ghi chú cho quả nhập từ bài về loài bản địa sẽ gây nhầm định danh; giữ chờ nguồn biên tập viết riêng về quả nhập |
| `mo` | ⏸ Giữ chờ — nhóm B/A | Tra lại: chất liệu biên tập VN vẫn áp đảo là mơ ta *Prunus mume* (kể cả các bài mới 10/2026 về mơ Hương Sơn); mơ nhập *P. armeniaca* chỉ có trang thương mại |
| `la-han` | ⏸ Giữ chờ — nhóm C | Tra lại: mọi nguồn vẫn là khung dược liệu/sức khoẻ hoặc trang SEO (kể cả tích tên gọi La Hán chỉ xuất hiện ở trang trại nội dung); không có nguồn biên tập đạt chuẩn |
| `dua-gang` | ⏸ Giữ chờ — nhóm B/C | Tra lại: nguồn VN vẫn lẫn dưa gang với dưa bở (các trang sức khoẻ viết "dưa gang (hay dưa bở)") trong khi file tách *C. melo* var. *conomon* riêng; không có nguồn biên tập văn hoá đạt chuẩn |

Toàn kho sau đợt: **77/82 quả có `cultural_note`** (trước 75/82). Hai ghi chú mới chỉ dùng
chi tiết có trong nguồn đã mở; rà diễn đạt 0 câu khung y tế bị cấm.

---

## Kiểm chứng đợt (2026-11-02)

- Seed DB kiểm chứng riêng `shake_hungry_t0812` từ cây làm việc + `lacdoi:validate-data`:
  **0 lỗi 🔴, 23 🟡** (3.341 bản ghi; các 🟡 đều là lớp đã biết). Đối chiếu DB: 2 ghi chú quả
  mới đã vào DB (`coc`, `nho-than-go`), tổng quả có ghi chú **77/82**; hàng đợi món trong DB
  khớp file **358**.
- ExportStabilityTest: **2 passed / 1 failed** — hai test ổn định phía nguyên liệu đạt;
  test điểm cố định TOÀN CỤC fail ở lượt chạy này. **ĐÍNH CHÍNH (cùng ngày, sau khi nhận đủ
  đầu ra):** bản bàn giao đầu ghi "đạt 3/3" do đọc nhầm đoạn tail lẫn đầu ra của phiên song
  song — số đúng là 2/1 như trên. Phân tích fail: lượt test export tại chỗ từ cây làm việc
  đang chứa dữ liệu dở của các phiên QD song song (hàng chục file đang sửa, 2 món đang
  xoá + 2 món mới chưa commit, các file `_index`/MANIFEST đang đổi) nên seed→export không
  thể là điểm cố định tại thời điểm đó. Bằng chứng phần của đợt này vô can: sau lượt export
  đó, **cả 5 file đợt này sửa (2 quả + 3 món) vẫn khớp byte với commit** — nếu lệch khuôn
  exporter, export đã viết lại chúng. Kết luận: thay đổi của T-11/T-12 là export-canonical;
  test toàn cục cần chạy lại ở cửa sổ sạch khi các phiên song song đã commit xong (cùng
  việc mở Pest toàn bộ ở dưới).
- **Pest toàn bộ: chưa chạy được ở cửa sổ sạch trong đợt này** — suốt phiên làm việc có 3–4
  phiên song song (QD-ADJUDICATE-1, QD-15, Docker) chạy Pest/seed trên DB test dùng chung
  `shake_hungry_test` (đúng mẫu nhiễu đã đính chính ở T-05 `85c26b7`). Cần một lượt Pest
  toàn bộ ở cửa sổ sạch cho trạng thái cây cuối — việc mở này ghi rõ để đợt chốt báo cáo
  tổng chạy bù; phần kiểm chứng riêng của đợt (validate DB cô lập + ExportStability) đã đạt.
