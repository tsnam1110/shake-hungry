# DISH-005 — Lấp ô trống có chọn lọc + làm sâu kho món tự nấu

**Mã task:** DISH-005
**Giai đoạn:** GĐ3
**Loại:** Dữ liệu
**Status:** 🔵 IN PROGRESS — đợt 1 (canh + rau) đã sản xuất xong 2026-10-31, chờ duyệt công thức; các đợt còn lại chưa làm
**Ngày tạo:** 2026-10-31
**Ngày bắt đầu:** 2026-10-31
**Ngày hoàn thành:** —
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
3. **Món mặn nhanh ≤30 phút +40** (chưa làm).
4. **Lõi miền Trung +25** (chưa làm; đợt 1 đã đóng góp phần canh Trung).
5. **Món chính chay +20** (chưa làm).
6. **Bữa sáng tự nấu +15** (chưa làm).
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

### Các đợt còn lại (chưa làm — giữ nguyên phạm vi kế hoạch)

- [ ] Món mặn nhanh +40 (kèm biên soạn `ca-bac-ma`, `ca-thu`, `nam-dui-ga` nếu món dùng tới).
- [ ] Lõi miền Trung +25 (món chính — canh Trung đã đạt quota ở đợt 1).
- [ ] Món chính chay +20 (kèm `cu-cai-trang`; cổng phụ: 100% món chay mới phải suy ra `diet:chay`).
- [ ] Bữa sáng tự nấu +15.
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
