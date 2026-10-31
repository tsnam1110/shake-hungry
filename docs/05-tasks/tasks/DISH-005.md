# DISH-005 — Lấp ô trống có chọn lọc + làm sâu kho món tự nấu

**Mã task:** DISH-005
**Giai đoạn:** GĐ3
**Loại:** Dữ liệu
**Status:** ✅ HOÀN THÀNH SẢN XUẤT cả 3 đợt 2026-10-31 (đợt 3 chay + sáng xong cùng ngày) — 360 công thức chờ chủ dự án duyệt, trong đó 160 của DISH-005; các món hoãn chờ nguồn đạt chuẩn cho nguyên liệu null
**Ngày tạo:** 2026-10-31
**Ngày bắt đầu:** 2026-10-31
**Ngày hoàn thành:** 2026-10-31 (sản xuất; duyệt công thức là việc của chủ dự án)
**Phụ thuộc:** DISH-004, pha sửa dữ liệu sau tầm soát (2026-10-31), Phương án A (quy ước dầu món chiên)

---

## Mục tiêu

Sau mốc "đủ dùng" 500 món của DISH-004, độ phủ lệch hẳn về món ăn ngoài/ăn vặt (bánh + bún-phở-mì +
chè + xôi ≈ 46%) trong khi lõi mâm cơm tự nấu còn mỏng: canh chỉ 43 món và lệch miền (Trung gần như
trắng ở món chính + canh), món chính chay suy từ thành phần chỉ ≈3 món, toàn bộ món vẫn Mức 1.
Task này **không tăng số lượng thô** mà lấp đúng các ô trống đã đo được, theo kế hoạch đã được chủ
dự án duyệt 2026-10-31 (báo cáo tầm soát + kế hoạch DISH-005/Mức 2 trong thư mục files của goal):

1. **Canh +30** — phân bổ để cả 3 miền đều đạt tối thiểu 20 món canh.
2. **Rau/xào/luộc +30** — nhóm rau ăn kèm mâm cơm.
3. **Món mặn nhanh ≤30 phút +40** (✅ đợt 2).
4. **Lõi miền Trung +25** (✅ đợt 2; đợt 1 đã đóng góp phần canh Trung).
5. **Món chính chay +20** (✅ đợt 3).
6. **Bữa sáng tự nấu +15** (✅ đợt 3).
7. Song song: nâng Mức 2 thật cho danh sách 50 món đợt 1 của kế hoạch (task riêng khi triển khai).

Mọi công thức mới đều là **bản nháp chờ duyệt** (`needs_review=true`, dòng nguyên liệu
`source=llm_suggested` theo đúng quy ước DISH-001…004) — task này không tự duyệt công thức nào.

## Bối cảnh

- Kế hoạch nguồn (danh sách món từng đợt, tiêu chí chất lượng, cổng G1–G7):
  `ke-hoach-dish-005-va-nang-muc-2.md` trong thư mục files của goal Lắc Đói.
- Quy tắc áp dụng: **QT-1** (không bịa số — nguyên liệu không có nguồn đạt chuẩn thì để null kèm hồ
  sơ và hoãn món phụ thuộc), **QT-2** (chỉ nguồn trong thang DATA-PIPELINE; tầng 6 kèm cờ chưa kiểm
  định), **QT-3** (dữ liệu dạng file trong `data/`), **QT-7** (cập nhật ROADMAP + CLAUDE §0 cùng
  commit), cổng **V12** (Mức 2 cần ≥3 `recipe_sources` — đợt này giữ Mức 1, không tự tạo liên kết),
  phép thử biến thể ở [EDITORIAL-GUIDE](../../09-standards/EDITORIAL-GUIDE.md) §3 và quy ước dầu
  món chiên §12 (đợt 1 không có món chiên ngập dầu; món xào kê dầu xào bình thường).
- Phân loại theo [TAXONOMY](../../03-data-model/TAXONOMY.md): nhãn `loai:` là biên tập duy nhất;
  diet/protein/allergen **suy** từ thành phần bằng `DishTagDeriver` — không gán tay.

## Việc cần làm

### Đợt 1 — canh + rau (✅ đã sản xuất 2026-10-31, chờ duyệt)

**Nguyên liệu biên soạn trước: 11 file mới** trong `data/ingredients/`:

| Slug | Kết quả | Nguồn / lý do |
|---|---|---|
| `su-su` | ✅ 19 kcal | USDA SR Legacy 170402 (chayote) |
| `cai-cay` | ✅ 27 kcal | USDA SR Legacy 169256 (mustard greens, *Brassica juncea*) |
| `dau-dua` | ✅ 47 kcal | USDA SR Legacy 169222 (yardlong bean) |
| `ngon-bi` | ✅ 19 kcal | USDA SR Legacy 169272 (pumpkin leaves) — ngọn gồm lá+cuống+tua, ánh xạ medium, ghi rõ giới hạn trong file |
| `hoa-bi` | ✅ 15 kcal | USDA SR Legacy 169270 (pumpkin flowers) — slug MỚI, xem cạm bẫy về `bong-bi` |
| `ca-hu` | ✅ 66 kcal (58–76) | Literature: phân tích phi lê cá tra nuôi VN của Nutraqua (*Pangasianodon hypophthalmus* — đúng loài cá hú); tra chéo Matvaretabellen Na Uy 63 kcal khớp ~5%. Tầng 6 → confidence low |
| `ca-chuon` | ✅ 97 kcal | USDA Branded: nhãn Beaver Street Fisheries 113 g = 110 kcal/24 g đạm/72 mg natri → quy 100 g (đạm 21,2 / béo 0,9 / natri 64) |
| `ca-dia` | ⬜ null + hồ sơ | Không có bảng thịt tươi /100 g cho *Siganus* ở USDA/FNRI/MEXT/Taiwan FDA; văn liệu chỉ có % chất khô — không quy đổi (QT-1) |
| `mang-chua` | ⬜ null + hồ sơ | USDA chỉ có măng tươi/đóng hộp — sản phẩm khác, không mượn số. Đồng thời thêm `'mang-chua' => 'len-men'` vào `TaggingRules::STATE_BY_SLUG` |
| `ngong-cai` | ⬜ null + hồ sơ | Pak-choi của USDA khác giống/phần ăn — không mượn số |
| `la-he` | ⬜ null + hồ sơ | USDA chỉ có *A. schoenoprasum* ≠ hẹ *A. tuberosum*; MEXT có nira đúng loài nhưng chưa kiểm chứng được trị số tại nguồn trong đợt tra này |

Không biên soạn vì đợt này không dùng tới (để đợt liên quan): cá bạc má, cá thu, nấm đùi gà,
củ cải trắng.

**Món mới: 60 file** trong `data/dishes/vn/` — 30 canh + 30 rau/xào/luộc, mọi món `nau`/`luoc`/`xao`,
`meal_slots` trưa+tối, định lượng theo khẩu phần 1 người ăn (tô canh 300–350 g, đĩa rau ~140 g).

- Canh Bắc (`vn-north`) +8: đậu phụ cà chua, trứng cà chua, rau ngót thịt băm, mướp thịt băm,
  khoai tây sườn, su hào thịt, dọc mùng sườn, cải cúc thịt băm.
- Canh Trung (`vn-central`) +18: rau lang tôm, hến rau lang, cá chuồn mít non, chua cá nục,
  bầu nghêu, thơm cá nục, bí đỏ tôm, rau má tôm, cải cay cá rô, khoai môn sườn, cá nục lá giang,
  mồng tơi tôm, rau dền tép, bầu tôm khô, hến nấu chua, bầu hến, cá rô khế, cá nục nấu ngót.
- Canh Nam (`vn-south`) +4: chua cá hú, chua cá lóc, bí rợ sườn, khổ qua cá lóc.
- Rau +30 (23 `vn`, 3 Bắc, 4 Nam): 6 món luộc, 24 món xào — đủ các nguyên liệu mới có số
  (su su, đậu đũa, ngọn bí, bông bí).

**Số canh từng miền sau đợt 1** (đếm từ thư mục `data/dishes/vn`, gộp mã vùng con vào miền):
Bắc 33 (13 `vn` + 18 `vn-north` + 2 `vn-ha-noi`) · Trung 20 · Nam 20 (19 `vn-south` + 1 `vn-an-giang`)
— **cả 3 miền đều ≥20** như mục tiêu kế hoạch. Tổng canh toàn kho: 73.

**Món hoãn / thay thế so với danh sách kế hoạch** (mọi thay thế ghi cả trong `import_note` của món):

| Món kế hoạch | Xử lý | Lý do |
|---|---|---|
| Canh cá dìa nấu thơm (Trung) | HOÃN → thay bằng `canh-bau-hen` | `ca-dia` null (không có nguồn đạt chuẩn) |
| Canh măng chua nấu cá (Trung) | HOÃN → thay bằng `canh-ca-ro-khe` | `mang-chua` null |
| Canh cá lóc rau đắng (Trung) | HOÃN → thay bằng `canh-ca-nuc-nau-ngot` | `rau-dang` vẫn null (hồ sơ đã có từ trước) |
| Canh rau đắng cá lóc (Nam) | HOÃN → thay bằng `canh-chua-ca-loc` | như trên |
| Ngồng cải xào tỏi | HOÃN → thay bằng `cai-ngot-luoc` | `ngong-cai` null |
| Giá xào hẹ | HOÃN → thay bằng `dau-bap-xao-toi` | `la-he` null (chưa kiểm chứng MEXT tại nguồn) |
| Rau nhút xào tỏi | HOÃN → kích hoạt món dự phòng chính thức `doc-mung-xao-tep` | `rau-nhut` null (đã biết từ trước) |
| Canh bí rợ nấu tôm (Nam) | ĐỔI thành `canh-bi-ro-suon` | Bản bí đỏ + tôm đã biên soạn ở quota Trung (`canh-bi-do-tom`); theo phép thử biến thể EDITORIAL-GUIDE §3, hai file chỉ khác nhãn miền là không đủ độc lập — bản Nam đổi sang nấu sườn |
| Cải bó xôi xào tỏi | Giữ, dùng slug `rau-bina` | Theo §4 kế hoạch: `rau-bina` đã xác nhận là spinach — không tạo slug trùng |

### Đợt 2 — mặn nhanh +40, lõi Trung +25 (✅ đã sản xuất 2026-10-31, chờ duyệt)

**Nguyên liệu biên soạn trước: 6 file mới** trong `data/ingredients/`:

| Slug | Kết quả | Nguồn / lý do |
|---|---|---|
| `ca-bac-ma` | ✅ 103 kcal (low) | DKBM/TKPI EA023 qua bản đăng lại organisasi.org (đạm 22, béo 1, carb 0, nước 76). USDA không có chi *Rastrelliger*; mapping_note ghi xung đột chưa phân xử với một bản "TKPI 2017" (125 kcal) và việc KHÔNG áp tỉ lệ ăn được 80% của DKBM (phần ăn được để null theo kỷ luật DATA-008) |
| `long-ga` | ✅ 124 kcal (medium) | USDA SR Legacy 171057 (chicken giblets, raw) |
| `banh-uot` | ✅ 108 kcal (low) | USDA SR Legacy 168914 — cùng tiền lệ ánh xạ sợi gạo tươi của `bun-tuoi`/`banh-pho-tuoi` |
| `heo-quay` | ⬜ null + hồ sơ | Không có nguồn đạt chuẩn cho heo quay thành phẩm; số thịt sống không còn đúng sau quay → món phụ thuộc HOÃN |
| `mam-tom-chua` | ⬜ null + hồ sơ | SR 172238 là tôm lên men nguyên con Thái — khác bản chất mắm xay Huế; tỉ lệ tôm/cơm/muối mỗi nhà mỗi khác |
| `oc-hut` | ⬜ null + hồ sơ | Tên gọi chung nhiều loài ốc biển nhỏ — không bảng thành phần tương ứng |

`nam-dui-ga` không biên soạn (không món nào của §3.3/§3.4 dùng); `cu-cai-trang` đã có trong kho.
Đồng thời bổ sung luật `TaggingRules` cho `mam-tom-chua` (dong-vat / len-men / dị ứng tom +
mam-tom) và `oc-hut` (dị ứng dong-vat-co-vo).

**Mặn nhanh +40/40** — đủ số theo §3.3; tiêu chí ≤30 phút ghi trong từng `recipe_steps` (tổng
thời lượng từng món đều ≤30′). 5 món chiên mới theo **Phương án A** (không kê dầu chiên chính).
**5 món thay được phân xử** (ghi cả trong `import_note` của món thay):

| Món kế hoạch | Xử lý | Lý do |
|---|---|---|
| Đậu phụ sốt cà chua | → `trung-khuay-ca-chua` | Trùng `dau-phu-sot-ca` đã có — tên đầy đủ của nó chính là "Đậu phụ sốt cà chua" (phép thử §3). Ứng viên thay đầu tiên `dau-phu-kho-nam-huong` cũng trùng `dau-phu-kho-nam` đã có — V7 phát hiện, đã huỷ |
| Tôm rim thịt ba chỉ | → `nam-kho-tieu` | Đã có trong kho ở slug `tom-rim` với thành phần gần như trùng — V7 phát hiện ở vòng rà soát, đã huỷ bản trùng; món thay là món chay (nêm nước tương) |
| Heo quay kho cải chua | → `suon-rang-muoi` | `heo-quay` null (xem trên) |
| Cá thu chiên sốt cà | → `bo-xao-la-lot` | `ca-thu-sot-ca` đã có chính là món này dạng kho; `ca-thu` chưa có `oil_absorption_pct` nên dạng chiên chưa tính được phần dầu |
| Chả trứng hấp | → `thit-xao-mam-ruoc` | `cha-trung` đã có |

`banh-khoai` (nhóm Trung) đổi nhân `thit-lon-ba-chi` thay `thit-lon-nac-vai` của kế hoạch:
nac-vai chưa có `oil_absorption_pct` — món chiên không được thiếu hệ số cho nguyên liệu chính.

**Lõi Trung +25/25** — mọi món mang mã vùng Trung, không món nào gán `vn` chung chung:
vn-hue 9 · vn-central 9 · vn-quang-nam 5 · vn-nghe-an 2.

- **4 biến thể đúng cơ chế** `parent_dish_slug` (kế hoạch §3.4 dự phòng sẵn):
  `banh-canh-ca-loc-quang-tri`, `ca-bong-kho-tieu-quang`, `chao-ca-loc-trung`, `mit-tron-thit`
  (tạo sau khi cặp gỏi/nộm mít non đã quyết xong ở pha tầm soát). Cây vùng chưa có mã Quảng
  Trị/Bình Định → vn-central.
- **19 món độc lập**, gồm `com-hen-xao` (khác `com-hen` trộn nguội ở method — phân xử ghi tại
  file), đôi `sup-luon` + `chao-luon-nghe-an`, `mam-ca-ro` (weight bữa để 2 theo bài học M-4
  của pha tầm soát), `tam-huu` (bổ sung đủ quota).
- **3 món thay**: tom-chua-thit-luoc → `hen-xao` (`mam-tom-chua` null); oc-hut-xao-dua →
  `chao-ngheu` (`oc-hut` null); tre-tron → `nem-lui` (`rieng` null — tré phụ thuộc riềng theo
  khối lượng đáng kể).

**Cổng G1–G7 đợt 2** (đo thật 2026-10-31):

- **G1** validate: **1.106 bản ghi — 0 🔴**, 38 🟡 đều thuộc các nhóm cảnh báo cũ (V9-DB:
  món nhỏ dưới 50 kcal/khẩu phần như nước chấm/món luộc — validator đọc DB nên số 🟡 thay
  đổi theo trạng thái recompute của DB, đợt 1 đã ghi nhận nhóm này; V10 mỡ 902; V8 nguyên
  liệu chưa món nào dùng — gồm 3 hồ sơ null mới lập có chủ đích; 2 cặp V7 tên giống nhau cũ).
- **G2** seed + recompute: kho **621 món / 403 nguyên liệu** (363 có số); **616/621 món tính
  được** — 5 món không tính được vẫn là đúng 5 hồ sơ thiếu dữ liệu thật cũ. **65/65 món mới
  tính được (100%)**. Món dùng nguyên liệu tầng 6 (`ca-bac-ma`) tự mang cờ chưa kiểm định.
- **G3** Pest: **114/114 xanh** ở lần chạy cuối trên HEAD `207685c`. Lưu ý vận hành: giữa
  đợt, phiên song song (REC-003) sửa dở `Recommender` chưa commit làm test lắc USR-004 đỏ
  thoáng qua 1 lần — không liên quan dữ liệu đợt này (test đó tự dựng fixture, không đọc
  `data/`); sau khi phiên kia commit REC-003, toàn bộ 114 test xanh với dữ liệu đợt 2.
- **G4** kiểm tay 10 món (5/nhóm): đạm chính 100–180 g/khẩu phần khớp tiền lệ kho; chênh
  serving ↔ tổng nguyên liệu nằm trong biên độ tiền lệ (món nước không kê nước dùng;
  `thit-ngam-mam` ghi chú nước ngâm không ăn hết tại file).
- **G5** nhóm chiên mới vs tham chiếu độc lập QA-002 lần 3 (Variant A: trứng 4,2 · thịt 4 ·
  tôm thịt 7 · vỏ bột gạo 16): **5/5 trong ±20%**, lệch −2,7%…0,0% (trung-chien-thit-bam
  −0,6%, cha-la-lot 0,0%, trung-duc-thit −0,5%, trung-chien-hanh −0,9%, banh-khoai −2,7%).
- **G6** rà phân loại ĐỘC LẬP 65/65 (agent riêng, 8 tiêu chí): **0 lỗi chắc chắn**; 3 ca
  biên được phân xử: (1) `tam-huu` đổi `loai:goi-nom` → `mon-chinh` cho nhất quán tiền lệ
  món cuốn trong kho (`banh-trang-cuon-thit-heo`, `nem-lui`) — đã sửa; (2) `banh-uot-long-heo`
  không có slot an-vat — giữ nguyên vì khớp hệt tiền lệ `banh-uot-cha-lua`; (3) `thit-ngam-mam`
  method `luoc` — giữ (từ vựng method không có "ngâm"). Vòng rà soát này cũng phát hiện 2
  món trùng món đã có (xem bảng thay thế) — đã huỷ và thay món khác cùng quota. Diet suy
  kiểm chứng trong DB: 3 món chay mới (`nam-kho-tieu`, `dau-phu-rim-nam` — mặn nhanh;
  `banh-it-la-gai` — Trung) đúng `diet:chay`; món nêm nước mắm suy mặn đúng; `mam-ca-ro`
  đúng nhóm nuoc-cham weight 2.
- **G7** tài liệu cùng commit: file task này + ROADMAP + CLAUDE §0 + AGENTS.md.

Sau đợt 2: **325 công thức chờ chủ dự án duyệt** (200 DISH-004 + 60 đợt 1 + 65 đợt 2).
Món hoãn chờ nguồn: heo-quay-kho-cai-chua, tom-chua-thit-luoc, oc-hut-xao-dua, tre-tron.

### Đợt 3 — món chính chay +20, bữa sáng tự nấu +15 (✅ đã sản xuất 2026-10-31, chờ duyệt) — ĐỢT CUỐI

**Nguyên liệu biên soạn trước: 3 file mới** trong `data/ingredients/`:

| Slug | Kết quả | Nguồn / lý do |
|---|---|---|
| `nam-dui-ga` | ✅ 33 kcal | USDA SR Legacy 168580 "Mushrooms, oyster, raw" (*Pleurotus ostreatus* — cùng chi với nấm đùi gà *P. eryngii*); tra chéo độc lập MEXT cho đúng loài eryngii = 31 kcal (~6%, chỉ tra chéo trong `mapping_note`, không trộn số — QT-4); confidence medium. `scientific_name` ghi đúng loài của bản ghi tham chiếu theo phân xử G6 |
| `suon-chay` | ✅ 311 kcal (low) | USDA SR Legacy 174268 "Meat extender" — TVP đậu nành khô, tương đương sườn non chay khô bán tại VN. Số cho dạng KHÔ: công thức phải ghi gram khô |
| `cha-lua-chay` | ⬜ null + hồ sơ | Không tìm được nhãn nhà sản xuất có bộ số kiểm chứng được (Âu Lạc/An Nhiên không công bố) — để null đúng QT-1; món phụ thuộc hoãn |

Sửa hạ tầng kèm theo: `TaggingRules.php` (origin `nam` cho nam-dui-ga qua bảng ngoại lệ; `STATE_BY_SLUG` kho/che-bien và allergen dau-nanh + lua-mi cho suon-chay/cha-lua-chay); hệ số hấp thụ dầu theo tiền lệ CALC-004 — `dau-phu` giữ quy tắc "hệ số để dành cho chủ thể chiên", `banh-mi` 15→**0** và `xuc-xich` →**0** (không phải chủ thể chiên: bánh mì là thành phẩm nướng, xúc xích là thành phẩm hâm/chiên lại — tiền lệ `lap-xuong` = 0).

**20 món chính chay** (quota thiếu nhất của kho — trước DISH-005 chỉ ≈3 món): dau-phu-kho-rau-cu,
nam-bao-ngu-kho-sa, dau-que-kho-dau-phu, dau-phu-rim-sa-ot, kho-qua-kho-chay, ca-tim-kho-dau-phu,
mit-non-kho, chuoi-xanh-kho-dau, dau-phu-kho-gung, bong-cai-xao-nam-chay, mang-kho-chay,
bi-do-kho-chay, dau-phu-kho-chao, nam-dui-ga-kho, rau-cu-xao-chay, mien-xao-chay, mi-xao-chay,
dau-phu-chien-kho-dua, dau-que-xao-chao, suon-chay-rim. **Cổng cứng đạt: kiểm chứng trực tiếp
trong DB sau seed — 20/20 món suy đúng `diet:chay`** (không nước mắm/mắm/hạt nêm/bột canh nguồn
động vật, không trứng/sữa; gia vị chỉ nước tương/muối/chao/tương. mi-xao-chay dùng `mi-an-lien` —
theo file nguyên liệu có origin thực vật, không allergen trứng; `mi-trung-kho` bị loại vì chỉ
đạt chay-trứng-sữa).

**15 món sáng tự nấu:** banh-mi-trung-op-la, com-chien-trung-toi, mi-goi-xao-trung-rau,
banh-mi-chao, chao-thit-bam, com-nam-muoi-vung, nui-xao-trung, bun-gao-xao-trung,
khoai-lang-luoc-trung, xoi-dau-xanh-mo-hanh (BIẾN THỂ của `xoi-xeo`), chao-dau-den,
banh-cuon-trung, mi-tron-kho, com-chien-dua-trung, trung-cuon-com. Đều là món làm tại nhà thật,
`meal_slots` sang là chính; đa số ≤15 phút chủ động.

**Thay / hoãn / phân xử trong đợt (lý do đầy đủ trong `import_note` từng món):**

- `nam-rom-kho-tieu` trùng tuyệt đối `nam-kho-tieu` của đợt 2 (V7 phát hiện) → thay
  `nam-bao-ngu-kho-sa`.
- `rau-cu-kho-chay` trùng khái niệm + tên EN với `kho-chay` đã có → thay `dau-que-kho-dau-phu`.
- `chao-trang-trung-muoi` (kế hoạch) trùng `chao-trang` đã có (serving_desc đã ghi kèm trứng
  vịt muối) → thay `chao-thit-bam`.
- `dau-phu-sot-nam` trùng cụm với cả `dau-phu-kho-nam` và `dau-phu-rim-nam` (G6 kết luận) →
  thay `dau-phu-kho-gung` (đổi hẳn trục vị sang gừng, không dùng nấm).
- `cha-chay-kho-dua` HOÃN vì `cha-lua-chay` null → thay bằng `dau-phu-chien-kho-dua`.
- `dau-phu-chien-sa-ot` HOÃN: kiểm tham chiếu độc lập lệch **+24%** — hệ số hấp thụ dầu của đậu
  phụ (15) chưa được hậu thuẫn (cặp SR Legacy 172475↔172451 suy cân bằng protein ≈ 9,8). Để lại
  cho quy trình hiệu chuẩn CALC-004/QA-002; thay bằng `dau-phu-rim-sa-ot` (kho).
  → **ĐÃ XỬ LÝ SAU KHI ĐÓNG TASK (2026-10-31, đợt hiệu chuẩn đậu phụ của CALC-004):** hệ số
  `dau-phu` đã hiệu chuẩn 15 → **10** theo đúng ngưỡng đăng ký (tỉ lệ 15/9,80 = 1,53, ngoài
  [0,80; 1,25]; neo cặp trực tiếp của đúng thực phẩm). Món đã được biên soạn (Mức 1,
  `llm_suggested`, `needs_review`): engine lệch **+8,9%** so với tham chiếu độc lập
  310,3 kcal (phản chứng ở hệ số cũ 15: **+31,9%**); 0 món cũ đổi số. Kho thành **657 món**,
  công thức chờ duyệt thành **361**. Xem [CALC-004](./CALC-004.md) mục bổ sung hiệu chuẩn
  đậu phụ.
- `mi-xao-chay`: bản soạn đầu dùng nhầm `mi-can` — G6 phát hiện `mi-can` trong kho là MÌ CĂN
  (seitan, khối gluten ướt đã hấp), không phải sợi mì → đổi sợi sang `mi-an-lien`, diet vẫn chay.
- `mi-trung-xao-rau` đổi tên/slug thành `mi-goi-xao-trung-rau` (thành phần là mì ăn liền, tên cũ
  gây hiểu nhầm dùng mì trứng).
- `xoi-dau-xanh-mo-hanh`: parent đổi từ `xoi-dau-xanh` (bản ngọt cốt dừa) sang `xoi-xeo` theo
  G6 — cùng hệ nếp + đậu xanh + mỡ hành Hà Nội, chỉ thiếu nghệ.
- Ca biên đã phân xử giữ nguyên: `banh-cuon-trung` độc lập (đặc sản Cao Bằng, trứng là cấu trúc
  bánh); `com-chien-dua-trung` độc lập với `com-rang-dua` (bản có thịt bò xay);
  `dau-phu-kho-rau-cu` độc lập với `kho-chay` (nguyên liệu dẫn khác nhau); `trung-cuon-com` giữ
  `loai:mon-chinh`.

**Kết quả cổng đợt 3:**

- **G1** validate-data: **1.144 bản ghi — 0 🔴**. Cảnh báo 🟡: 4 ở phía file (V10 mo-lon-nuoc, V8
  nguyên liệu chưa dùng gồm các hồ sơ null có chủ đích, 2 cặp V7 cũ) và — khi DB đã recompute —
  34 cảnh báo V9 phía DB cho các món phụ khẩu phần nhỏ (<50 kcal/khẩu phần) có sẵn từ các đợt
  trước; **không món mới nào của đợt 3 bị V9**.
- **G2** `migrate:fresh --seed` dựng sạch: **656 món / 406 nguyên liệu** (365 có số, 41 null);
  recompute (`--with-ingredients`) **651/656 món tính được** — 5 món không tính được vẫn là 5 hồ
  sơ cũ vướng dữ liệu thật; **35/35 món mới tính được (100%)**.
- **G3** Pest toàn bộ: **115/115 xanh**.
- **G4** Kiểm tay 10 món (5 món/nhóm): tỉ lệ serving/tổng gram nguyên liệu 0,91–1,01; hai ngoại
  lệ đã giải thích trong file — `suon-chay-rim` 1,76 (cân khô → ngâm nở, ghi rõ trong bước nấu),
  cháo không kê nước nấu theo tiền lệ mọi món cháo trong kho.
- **G5** 5 món chiên mới (banh-mi-trung-op-la, banh-mi-chao, com-chien-trung-toi,
  com-chien-dua-trung, trung-cuon-com — trứng có hệ số hấp thụ 4 theo CALC-004) so với tham chiếu
  độc lập Variant A của QA-002 lần 3: **5/5 trong ±20%**, lệch −0,2%…−0,6%. Dầu cơm chiên kê 3 g
  trong công thức theo tiền lệ `com-chien-trung-muoi`.
- **G6** Rà soát phân loại độc lập 100% món mới + 3 nguyên liệu (agent riêng, chỉ đọc): vòng đầu
  phát hiện **8 nhóm lỗi chắc chắn — đã sửa toàn bộ trước khi đóng đợt**: mi-can không phải sợi
  mì (A1); tên/slug mì trứng mâu thuẫn thành phần (A2); `chao-dau-den` là cháo NGỌT → đổi
  `loai:che-trang-mieng` theo quy ước hoà giải TAXONOMY §5 (A3); 3 món rau xào phụ weight 8→7 +
  khẩu phần về tiền lệ kho 140–150 g (A4); lượng nước trong bước nấu của 2 món cháo về ~300 ml/
  khẩu phần theo EDITORIAL-GUIDE §6 (A5); trùng cụm đậu phụ sốt nấm (A6); thời gian tự mâu thuẫn
  ở bánh cuốn trứng (A7); `scientific_name` của nấm đùi gà (A8). Nhãn diet của cả 20 món chay
  được vòng rà soát tự suy độc lập khớp 20/20 với DB.
- **G7** Tài liệu cùng commit: file task này + ROADMAP + CLAUDE §0 + AGENTS.md.

Sau đợt 3: **360 công thức chờ chủ dự án duyệt** (200 DISH-004 + 160 DISH-005 cả 3 đợt).
Món hoãn đợt 3: cha-chay-kho-dua, dau-phu-chien-sa-ot.

### ĐÓNG DISH-005 — tổng kết cả 3 đợt (2026-10-31)

- **Kho món: 496 → 656 món** (631 chuẩn + 25 biến thể). DISH-005 đóng góp **+160 món**: đợt 1
  +60 (canh 30 + rau 30), đợt 2 +65 (mặn nhanh 40 + lõi Trung 25), đợt 3 +35 (chay 20 + sáng 15).
  Mọi quota của kế hoạch đều đạt đủ; canh cả 3 miền ≥20 ngay từ đợt 1; nhóm món chính chay —
  ô trống sâu nhất — đi từ ≈3 lên 23 món suy đúng `diet:chay`.
- **Nguyên liệu: 386 → 406 file** (+20 qua 3 đợt: 12 có số, 8 null có hồ sơ — cá dìa, măng chua,
  ngồng cải, lá hẹ, heo quay, mắm tôm chua, ốc hút, chả lụa chay).
- **360 công thức chờ chủ dự án duyệt** (200 DISH-004 + 160 DISH-005), toàn bộ `needs_review=true`,
  dòng nguyên liệu `llm_suggested` — task này không tự duyệt công thức nào.
- **Món hoãn cuối cùng** (chỉ mở lại khi có nguồn đạt chuẩn cho nguyên liệu null, hoặc khi hiệu
  chuẩn CALC-004 kết luận về hệ số đậu phụ): các món phụ thuộc ca-dia / mang-chua / ngong-cai /
  la-he / rau-dang / rau-nhut (đợt 1), heo-quay-kho-cai-chua, tom-chua-thit-luoc, oc-hut-xao-dua,
  tre-tron (đợt 2), cha-chay-kho-dua, dau-phu-chien-sa-ot (đợt 3).
- Việc còn lại ngoài phạm vi task: duyệt 360 công thức (chủ dự án) và nâng Mức 2 cho danh sách
  50 món của kế hoạch (task riêng).

### Các đợt còn lại (chưa làm — giữ nguyên phạm vi kế hoạch)

- [x] Món mặn nhanh +40 (đợt 2 — kèm biên soạn `ca-bac-ma`, `long-ga`, `banh-uot`; `nam-dui-ga`
  không món nào dùng nên chưa biên soạn).
- [x] Lõi miền Trung +25 (đợt 2 — món chính là chủ yếu; canh Trung đã đạt quota ở đợt 1).
- [x] Món chính chay +20 (đợt 3 — kèm `nam-dui-ga`, `suon-chay`; cổng phụ đạt: 100% món chay mới
  suy ra `diet:chay`, kiểm chứng trong DB).
- [x] Bữa sáng tự nấu +15 (đợt 3).
- [ ] Khi có nguồn đạt chuẩn cho `ca-dia` / `mang-chua` / `ngong-cai` / `la-he` / `rau-dang` /
  `rau-nhut`: biên soạn lại các món đã hoãn ở trên.

## Definition of Done

DoD chung: [DEFINITION-OF-DONE](../DEFINITION-OF-DONE.md). Riêng đợt 1 — cổng G1–G7 của kế hoạch,
kết quả đo thật 2026-10-31:

- [x] **G1** validate-data: **1.035 bản ghi — 0 🔴**, 22 🟡 (toàn bộ là cảnh báo cũ: nhóm nước chấm
  V9, mỡ lợn V10, V8 nguyên liệu chưa dùng — 4 nguyên liệu null mới thuộc nhóm này đúng dự kiến).
- [x] **G2** `migrate:fresh --seed` dựng sạch: **556 món / 397 nguyên liệu** (359 có số); recompute
  toàn kho **551/556 món tính được** — 5 món không tính được là đúng 5 món vướng dữ liệu thật đã
  biết (gỏi ngó sen, canh rau nhút tôm thẻ, mắm nêm ×2, mắm ruột). **60/60 món mới tính được**;
  4 món chua có ghi chú thiếu `ngo-gai` (rau thơm tùy chọn 2 g chưa có số) — giống hệt tiền lệ
  `canh-chua-ca-doi` trong kho. Món dùng nguyên liệu tầng 6 (`ca-nuc`, `ca-hu`, `ca-loc`)
  tự mang cờ chưa kiểm định.
- [x] **G3** Pest toàn bộ: **106/106 xanh** (kiểm tra tiến trình php khác trước khi chạy — chỉ có
  server cổng 8000).
- [x] **G4** Kiểm tay 5 món/nhóm: đối chiếu định lượng với khẩu phần gia đình chuẩn và tiền lệ trong
  kho (khung canh chua theo `canh-chua-ca-doi`, canh bầu theo `canh-bau-tom`): đậu phụ 80 g + cà
  chua 60 g/khẩu phần, sườn 70 g + khoai 100 g, cá hú 100 g + đậu bắp 25 g + dọc mùng 30 g,
  khổ qua 110 g + 1 trứng 50 g, đậu đũa 120 g + thịt băm 40 g, cải chua 110 g + lòng 60 g,
  bông bí 100 g — đều trong khoảng công thức gia đình phổ biến; kcal engine cho ra hợp lý
  (khổ qua luộc ~15 kcal/đĩa, canh chua cá hú 114–196 kcal/tô, su su xào tỏi 88–130 kcal/đĩa).
  Lưu ý trung thực: vòng này đối chiếu theo khẩu phần chuẩn + tiền lệ kho, **chưa** gắn
  `recipe_sources` — nguồn công thức kiểm chứng là việc của đợt nâng Mức 2 (cổng V12).
- [x] **G5** Không kích hoạt: đợt 1 không thêm món chiên nào (chỉ nau/luoc/xao; nhóm xào của QA-002
  đang đạt 100%) nên không phải đo lại QA-002 trên bộ mẫu.
- [x] **G6** Rà phân loại độc lập 100% món mới: đếm lại từ file — canh Bắc +8 / Trung +18 / Nam +4
  đúng phân bổ; `loai:`/region/meal_slots/phương pháp nấu khớp kế hoạch ở cả 60 món; diet suy
  kiểm chứng trực tiếp trong DB sau seed: **8 chay** (6 món luộc nêm muối + cải thìa xào nấm nước
  tương + rau muống xào chao — không món nào chứa nước mắm/thịt mà lọt chay), 52 mặn, 0
  chay-trứng-sữa (hai món có trứng đều nêm nước mắm → mặn, đúng quy ước "nước mắm = mặn").
- [x] **G7** Tài liệu cùng commit: file task này + ROADMAP (số liệu đầu trang, hàng GĐ3, hàng task,
  nhật ký thay đổi) + CLAUDE §0 + AGENTS.md.

## Cạm bẫy đã biết

- **Slug `bong-bi` trong kho là bóng bì (bì lợn khô rang phồng), KHÔNG phải bông bí.** Kế hoạch gọi
  món "bông bí xào tỏi" — nguyên liệu đúng là file mới `hoa-bi`. Ai nối nhầm sẽ biến món chay rau
  thành món có bì lợn.
- `data/ingredients/_index.json` do `lacdoi:export-data` sinh; đợt này cập nhật tay đúng định dạng
  của lệnh đó (sắp xếp theo slug, đủ 3 trường) và ExportStabilityTest vẫn xanh — nhưng **không**
  chạy export-data để lấy index, vì lệnh đó viết lại cả file món khác định dạng chuẩn (bài học
  DISH-004).
- Nguyên liệu tầng 6 mới (`ca-hu` literature, `ca-chuon` branded): khi có nguồn chính thống hơn
  (bảng FCT quốc gia có cá hú / cá chuồn) phải thay số và gỡ cờ low — ghi sẵn hướng tra chéo trong
  `mapping_note` của từng file.
- Không chạy Pest/seed song song với phiên làm việc khác trên cùng VM (seed DB test từng bị wipe
  lẫn nhau) — kiểm tra `ps aux | grep php` trước.

## Ghi chú

- Chủ dự án duyệt toàn bộ hướng DISH-005 ngày 2026-10-31 với chỉ đạo: chất lượng đầu ra là trọng
  điểm, thời gian không quan trọng.
- Hướng đã thử và KHÔNG dùng khi tra nguyên liệu đợt này: FDC qua trình duyệt trả 404 cho trang
  chi tiết (số USDA lấy từ bản trích SR Legacy trong `data/reference/` — nguồn chính thống tại máy);
  nhãn cá chuồn lấy qua trang đăng lại nhãn USDA Branded vì không mở được bản ghi gốc.
- 60 công thức đợt 1 cộng với 200 công thức DISH-004 = **260 công thức đang chờ chủ dự án duyệt**.
- Đợt 2 (2026-10-31): thêm 65 công thức → **tổng 325 công thức chờ duyệt**. Khi tra DKBM cho
  `ca-bac-ma`: tránh các bản đăng lại ghi lẫn "125 kcal" (trang hocnaumonngon ghi nguồn lẫn
  USDA cho cá ngừ) — bản organisasi.org giữ đúng bộ số EA023 (103 kcal) khớp phân tích
  proximate trên tạp chí IFRJ; xung đột đã ghi vào `mapping_note`, chờ bảng DKBM/TKPI chính
  thống đối chiếu khi có.
- Đợt 3 (2026-10-31): thêm 35 công thức → **tổng 360 công thức chờ duyệt**; DISH-005 đóng sau
  đợt này (xem mục tổng kết 3 đợt ở trên). Bài học đợt 3: (1) `mi-can` là mì căn (seitan) —
  tên nguyên liệu dễ gây hiểu nhầm thành sợi mì, phải đọc file nguyên liệu trước khi dùng;
  (2) món thay thế viết tay phải đối chiếu đủ trường bắt buộc của seeder (`country_code`,
  `source`/`confidence` cấp món lẫn cấp dòng nguyên liệu, `unit_name` — không phải `unit`);
  (3) lệnh tính dinh dưỡng cho món Mức 1 chờ duyệt là `lacdoi:recompute-calories
  --with-ingredients`.
