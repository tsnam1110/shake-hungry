# QD-ADJUDICATE-1 — Triển khai QD-06…QD-14 (phiếu quyết định 2026-11-01)

- **Trạng thái:** ✅ HOÀN TẤT (2026-11-02)
- **Căn cứ:** chủ dự án duyệt **toàn bộ khuyến nghị** của phiếu quyết định
  [viec-ton-dong-va-phieu-quyet-dinh-2026-11-01] ngày 2026-11-02 ("Tôi duyệt các đề
  xuất đang chờ của bạn" + chỉ đạo xử lý toàn bộ tồn đọng). QD-01…QD-05 đã làm ở
  QD-ALLERGEN (`66646c5`); QD-15 thuộc đợt phân xử riêng.
- **Baseline:** `dev@66646c5`. Kiểm chứng trên worktree sạch tại baseline + patch của
  đợt, DB kiểm chứng riêng `shake_hungry_qd1`, DB test riêng `shake_hungry_qd1test`
  (nhiều phiên song song cùng cây — không seed DB dev, không dùng chung DB test).

---

## QD-06 · PX-25 — quy ước họ nguồn = A (theo bài; mỗi họ tối đa 1/3 nguồn cổng)

**Quy ước đã ghi văn bản:** [DATA-PIPELINE §5 — mục "Quy ước họ nguồn tính cổng
Mức 2"](../../04-data-sources/DATA-PIPELINE.md): nguồn độc lập tính theo **bài**; mỗi
**họ/domain** (và mỗi họ kỹ thuật khác loài) tối đa **1 trong 3** nguồn tính cổng
Mức 2; phân xử cổng công khai trong `import_note` từng món. Hệ quả thực hành: món
đạt cổng phải có nguồn thuộc **≥3 họ độc lập**.

**Đếm lại 23 món có ≥2 bài Điện Máy XANH trong `recipe_sources`** (đếm theo domain,
khớp danh sách hồ sơ PX-25 của AUDIT-002):

*Giữ mức (3 món — vẫn đủ 3 họ độc lập):* `ca-me-nuong-rieng` (amthucmoimien +
dienmayxanh + huongnghiepaau), `che-dau-do` (cookpad + dienmayxanh +
nauankhongkho), `xoi-dau-xanh` (cookpad + dienmayxanh + eco-mart). Đã ghi truy vết
đếm lại vào `import_note`. (Riêng `ca-me-nuong-rieng`: phán quyết này là theo đếm họ
domain của QD-06; hồ sơ PX-18 về nguồn họ kỹ thuật khác loài của món **giữ nguyên
trạng công khai**, không thuộc phạm vi đếm lại của phiếu này.)

*Hạ Mức 3 → "Mức 2 (có note)" (11 món — chỉ còn 2 họ độc lập, nhưng đủ điều kiện
đường nâng bù: phủ TCM ≥60% + có ghi chú văn hoá):* `banh-canh` (82.15%),
`banh-chung` (97.25%), `bo-sot-vang` (71.44%), `chao-ga` (97.53%), `chao-long`
(78.88%), `gia-cay` (89.86%), `lau-rieu-cua` (73.10%), `pho-bo` (96.66%),
`pho-cuon` (93.56%), `sup-cua` (89.72%), `thit-kho-tau` (91.86%). Cả 11 món đặt
`level_basis='tcm_compensation'` + `level_note` nêu rõ cổng chưa đạt theo QD-06;
`import_note` ghi phân xử đầy đủ. **Không xoá dữ kiện nguồn nào** — mọi bài viết ở
lại trong `recipe_sources`.

*Hạ Mức 2 → Mức 1 (9 món — 2 họ độc lập và KHÔNG đủ điều kiện nâng bù):*
`banh-chuoi` (phủ 82.43% nhưng chưa có ghi chú), `canh-moc` (96.73%, chưa có ghi
chú), `chao-thit-bam` (91.65%, chưa có ghi chú), `suon-nuong` (87.92%, chưa có ghi
chú), `sup-hai-san` (79.15%, chưa có ghi chú), `banh-cuon` (58.54% <60%),
`banh-uot` (47.77%), `mi-quang` (32.26%), `cha-ca-thac-lac` (21.88%). Tiền lệ áp
dụng: AUDIT-FIX (hạ mức có hồ sơ, giữ nguyên dữ kiện nguồn + định lượng; nâng lại
khi bổ sung ≥1 nguồn thuộc họ độc lập mới đã mở kiểm chứng).

## QD-07 · PX-26 — gộp cặp canh hến = A

`canh-hen-nau-chua` → **biến thể của `canh-hen`** (`parent_dish_slug='canh-hen'`,
tiền lệ `nom-mit-non` → `goi-mit-non`, `xoi-ngo` → `xoi-bap`). Món chính `canh-hen`
là bản đã được chủ dự án duyệt 2026-10-31, giữ nguyên toàn bộ; bản biến thể giữ
định lượng/hồ sơ riêng (miền Trung: hến 60 g, hành tím, không ớt). Dự án **chưa có
cơ chế redirect slug riêng cho món** — slug biến thể vẫn mở được trang biến thể nằm
dưới món chính qua cơ chế parent (đây là cơ chế "chuyển hướng" hiện hữu của dự án);
hồ sơ gộp ghi trong `import_note` cả hai file. Tổng số món không đổi (654 — file
biến thể vẫn là một bản ghi như mọi biến thể khác trong kho).

## QD-08 · `chao-dau-xanh` = A — đổi tên theo nội dung thịt bằm

Tên vi: **"Cháo đậu xanh thịt bằm"**; tên en: "Mung bean porridge with minced
pork"; tên cũ "Cháo đậu xanh" giữ làm **tên gọi khác** (alias, tìm kiếm được).
Định lượng **giữ nguyên toàn bộ**: file vốn đã là bản thịt bằm (thịt xay 50 g);
họ thịt bằm đã kiểm chứng (Nấu Ăn Không Khó: đậu 100 g, thịt 200 g, gạo ~200 g
theo mẻ) **không ghi số khẩu phần** nên không có bộ số theo khẩu phần đã kiểm chứng
để áp — không tự chế số mới (QT-1). Mức giữ **"Mức 2 (có note)"**; `level_note` đã
cập nhật: vướng mắc định danh đã khép, phần còn lại là cổng nguồn (mới 2 họ thuộc
hai biến thể loại trừ nhau).

## QD-09 · `chao-ca-loc-trung` = B — cháo bột Quảng Trị

Định danh lại toàn bộ thành **Cháo bột cá lóc Quảng Trị** ("cháo vạt giường"):
slug mới `chao-bot-ca-loc-quang-tri`, gỡ `parent_dish_slug` (không còn là biến thể
của `chao-ca-loc`), thêm alias "Cháo vạt giường". Thay dòng nguyên liệu đúng theo
phương án đã chốt ở LV2-009 §B.3: `gao-te` → `bot-gao` (kho chưa có slug sợi bột
gạo riêng), bỏ `bot-nghe` (thuộc định danh bịa cũ), thêm `cu-nen` 5 g; steps viết
lại theo đúng món. Mọi dòng vẫn `llm_suggested/low`, `needs_review` giữ nguyên —
**chưa có họ nguồn Quảng Trị nào đã mở kiểm chứng cho định lượng**, món giữ Mức 1
và hồ sơ ghi rõ điều này (không khẳng định bộ số nào là đã kiểm chứng). Ảnh AI cũ
(tô cháo gạo vàng nghệ — định danh cũ) đã gỡ khỏi món + xoá file + gỡ mục MANIFEST.
Slug cũ không còn tồn tại; dự án chưa có cơ chế redirect slug — món đang
draft/chưa công khai, truy vết đầy đủ trong `import_note` + file này.

## QD-10 · `mam-ruoi` = A — đổi tên theo nội dung file

Tên mới **"Rươi kho thịt"** (en: "Braised sandworms with pork belly"), slug mới
`ruoi-kho-thit`; công thức **giữ nguyên không sửa một dòng** (định lượng đã đối
chiếu họ rươi kho ở LV2-012). Hai hệ quả định danh xử lý cùng đợt: (1)
`external_ref` **Q10796550 gỡ** — mã Wikidata đó là của mắm rươi lên men, không
còn là món này; (2) ảnh Wikimedia chụp chai "Mắm rươi Ngọc Khánh — đặc sản Tứ
Kỳ" (sản phẩm lên men) **gỡ khỏi món + xoá file + gỡ mục MANIFEST** (nguồn gốc
vẫn truy được tại Commons, ghi trong `import_note`). Rà thẻ/từ khoá: `loai:mon-chinh`
vẫn đúng; ngoài tên cũ, món không còn từ khoá "mắm" nào sai (dòng `nuoc-mam-truyen-thong`
là gia vị nêm — giữ). Mắm rươi lên men đúng nghĩa là sản phẩm khác, chưa có trong
kho — biên soạn mới cần đợt riêng.

## QD-11 · `mam-ca-ro` = A — sửa định danh sang cá kình con

Theo nguồn VietKings trong chính ghi chú văn hoá của món (họ nội dung
kyluc.vn/xedihue.com đã rà ở T-04): cá rò = **cá kình con**. Đã sửa: tên Anh (bỏ
"climbing perch" → "Fermented baby kinh fish paste, Hue style"), bước 1 (bỏ chú
thích sai "cá rô con"), bước 2 (thời gian ủ 1–2 tháng theo VietKings thay "15–20
ngày" không nguồn; mắm giữ nguyên con). **Nút thắt nguyên liệu (ghi rõ trong
`import_note`):** dòng nguyên liệu vẫn là `ca-ro-dong` — kho chưa có slug cá kình
(con) và QT-1 cấm tự tạo số dinh dưỡng; số tính của món hiện là xấp xỉ theo
`ca-ro-dong`, chờ biên soạn nguyên liệu cá kình có nguồn đạt chuẩn. Ghi chú văn
hoá vốn đã đúng cá kình con — không sửa. Mức không đổi.

**ĐÓNG NÚT THẮT (CONSOLIDATE-FINAL, 2026-11-02):** đã biên soạn nguyên liệu
`ca-kinh` (cá kình — *Siganus canaliculatus* (Park, 1797); định danh theo bài
báo Tạp chí Khoa học ĐH Sư phạm Huế 3(63)/2022 về cá kình phá Tam Giang) với
số dinh dưỡng từ **TKPI** (Tabel Komposisi Pangan Indonesia, Bộ Y tế
Indonesia) mục **GR011 "Ikan baronang, segar"** — 112 kcal, đạm 20,0, béo 1,3,
nước 73,7 — ánh xạ ở **cấp chi** *Siganus* spp., nguồn tầng 6, confidence low;
hồ sơ đầy đủ (kể cả các kênh đã tra không có số: Bảng TPTP VN 2007 toàn văn,
MEXT, văn liệu proximate) nằm trong `mapping_note` của
`data/ingredients/ca-kinh.json`. Dòng nguyên liệu của món đã đổi
`ca-ro-dong` → `ca-kinh` 100 g và tính lại (món mang cờ chưa kiểm định qua
`ca-kinh`). **Hệ quả mức (khác dòng "Mức không đổi" ở trên — ghi đính chính rõ
ràng):** nhãn tính vị cũ là của `ca-ro-dong` (loài khác hẳn) nên không mang
sang được; `ca-kinh` chưa có nhãn tính vị đạt chuẩn (đã tra Dược điển
TQ/CMRO/A+) → độ phủ đo lại **14,5%** < 60% → đường nâng bù
`tcm_compensation` rút lại, món về **Mức 1** (cổng nguồn công thức Mức 2
thường vốn cũng chưa đạt). Đường khôi phục ghi trong `import_note` của món
(kho chỉ giữ `level_note` cho món còn `level_basis` — exporter chuẩn hoá).
QD-11 đóng hoàn toàn.

## QD-12 · TAXONOMY = A — thêm `dac-san:phu-quoc` + `dac-san:quang-binh`

Đã thêm vào `docs/03-data-model/TAXONOMY.md` §2 (từ vựng 14 → 16 giá trị) và
`config/taxonomy.php` (đăng ký V16 + facet). Gắn nhãn đợt đầu — **chỉ khi định
danh/vùng đã xác lập trong hồ sơ file**:

| Món | Nhãn | Căn cứ trong file |
|---|---|---|
| `goi-ca-trich` | `dac-san:phu-quoc` | 3/3 họ nguồn khẳng định gốc Phú Quốc (LV2-009); hồ sơ cũ ghi rõ chỉ chờ TAXONOMY có mã |
| `ghe-hap` | `dac-san:phu-quoc` | Ghi chú văn hoá: ghẹ Hàm Ninh, đặc sản Phú Quốc, nhãn hiệu tập thể 2022 |
| `chao-canh` | `dac-san:quang-binh` | Tên chính "Cháo canh Quảng Bình" + ghi chú xác lập món đặc trưng Quảng Bình |
| `chao-hau` | `dac-san:quang-binh` | Ghi chú: đặc sản thị trấn Quán Hàu, Quảng Bình |

Đã rà nhưng **không gắn** (ghi để đợt sau không phải rà lại): `bun-ken` (định danh
theo ghi chú là Châu Đốc/Cần Thơ; biến thể Phú Quốc thuộc QD-15.7 chưa chốt),
`nuoc-mam` (món generic đa vùng — gắn một tỉnh là sai định danh), `goi-ca` (định
danh đang treo QD-15.38), `ca-trich-kho` (ghi chú cấp loài, không xác lập Phú
Quốc), `canh-chua-ca-nuc` (Quảng Bình chỉ là một biến thể nhắc trong hồ sơ),
`banh-mat` (đã có `dac-san:nghe-an` — tối đa 1 nhãn).

## QD-13 · Q10 = đóng hồ sơ (A — giữ nguyên)

Giữ hàng rào `serving_grams < 80 g` của `Recommender::pool` cho vai món chính;
**không đổi code, không đổi dữ liệu**. Đã ghi quyết vào: ROADMAP (hàng Q10 →
✅ ĐÃ CHỐT 2026-11-02), [DATA-AUDIT-FIXES §5](../../04-data-sources/DATA-AUDIT-FIXES-2026-10-31.md)
(khối Đính chính), LV2-012 §4 (khối trạng thái).

## QD-14 · rau-mui = đóng hồ sơ (A — hoãn tiền lệ)

Chủ dự án duyệt **hoãn**: không mở tiền lệ dòng tính vị `flavor = null` cho ca đơn
lẻ `rau-mui`; không đổi schema/dữ liệu. Đã ghi quyết định vào hồ sơ TCM-001d tại
[TCM-001.md](./TCM-001.md) (mục phân xử `rau-mui`).

---

## Kiểm chứng đợt

- **Validate** (DB riêng `shake_hungry_qd1`, seed từ worktree sạch = baseline +
  patch của đợt): **0 🔴** / 23 🟡 (toàn lớp đã biết: V9 rau luộc, V8, V7 hai cặp
  tên quen thuộc — gồm chính cặp canh hến nay là cha/biến thể).
- **Bức tranh mức:** trước **194 Mức 3 · 286 Mức 2 thường · 107 Mức 2 (có note) ·
  67 Mức 1** → sau **183 Mức 3 · 277 Mức 2 thường · 118 Mức 2 (có note) · 76 Mức 1**
  (tổng 654 không đổi; khớp đếm file · DB · recompute).
- **Recompute:** calo tính 578/578 món ≥ Mức 2 (trước 587 — chênh đúng 9 món hạ
  Mức 1); tổng hợp TCM không đổi (648 món, phủ 500/98/50). **Đối chiếu calo cách
  ly:** ngoài 9 dòng dinh dưỡng bị gỡ theo hạ mức, **0 món nào đổi một con số**.
- **ExportStability:** seed → export trên DB kiểm chứng viết lại đúng
  `data/dishes/_index.json` (đổi slug/tên theo QD-08/09/10) — ngoài ra **0 file
  dữ liệu bị viết lại** (mọi file sửa tay đều đúng khuôn exporter).
- **Pest:** lượt chạy cô lập (worktree tại baseline + patch của đợt, DB test riêng
  `shake_hungry_qd1test`): **177 passed / 0 failed (719 assertions)** — gồm
  ExportStabilityTest đạt cả hai test điểm cố định. Ghi nhận minh bạch: lượt chạy
  cô lập đầu tiên cho 153 passed / 24 failed dưới tải song song cực cao (ba phiên
  khác cùng seed/test trên cùng MySQL); chạy lại nguyên trạng cùng bộ thay đổi ở
  cửa sổ sạch hơn cho kết quả xanh tuyệt đối ở trên — đúng tổng số test/assertions
  của các lượt xanh đã chốt (QD-ALLERGEN 177/177 · 719). Không test nào tham chiếu
  trực tiếp các slug/mức đã đổi (đã grep toàn bộ `tests/`).
