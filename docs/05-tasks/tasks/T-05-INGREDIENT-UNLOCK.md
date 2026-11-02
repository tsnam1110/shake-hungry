# T-05 — Nghiên cứu nguyên liệu khoá món (LV2-012 §6)

> Đợt xử lý tồn đọng theo danh sách [việc tồn đọng 2026-11-01], mục T-05.
> Chỉ đạo: chất lượng cao nhất; QT-1 tuyệt đối không bịa số — không có nguồn
> đạt chuẩn thì giữ `null` + hồ sơ. Mọi con số dưới đây đều trích từ nguồn
> đã MỞ kiểm chứng tận nơi trong đợt này, ghi URL + giá trị nguyên văn vào
> `mapping_note` của từng file. Một nguồn duy nhất cho mỗi nguyên liệu (QT-4).
> Ngày thực thi: 2026-11-02. Kiểm chứng trên DB riêng `shake_hungry_t05`.

## 1. Nguồn quyết định của đợt: bản toàn văn Bảng TPTP VN 2007

Các vòng trước (LV2-007/LV2-008/LV2-010) chỉ mở được bản 2017 bị cắt ở
~trang 100/300 (SlideShare `(S-038)`) nên nhóm 13 và nhiều mục khác chưa
kiểm từng mục được — đó là "hướng duy nhất còn mở" trong hồ sơ của
`mam-ca-sac`/`mam-ca-linh`.

Đợt này đã mở được **bản toàn văn Bảng thành phần thực phẩm Việt Nam
(Viện Dinh dưỡng, NXB Y Học 2007 — 526 thực phẩm, 14 nhóm)** tại:

`https://quantri.longbien.shieldix.app/uploadfoldernew/sgdlongbien/data/mnhongtien/bang-thanh-phan-dinh-duong-cua-thuc-pham-viet-nam-2007_01042020_01042020.pdf`

và kiểm TỪNG MỤC các nhóm liên quan: nhóm 13 (23 mục 13001–13023),
nhóm 4 rau (126 mục 4001–4126), nhóm 7 thịt (82 mục), nhóm 8 thuỷ sản
(59 mục), nhóm 12 (mục mạch nha 12025). Bảng VN là nguồn **thứ cấp**
(tầng 6, `vn_nin` — cờ "chưa kiểm định" khi hiển thị) theo thang
DATA-PIPELINE §7.2 và quyết định chủ dự án 2026-10-31.

## 2. Nguyên liệu ĐÃ CÓ SỐ (4)

| Slug | Mục bảng VN | Giá trị nguyên văn /100g | Món liên quan |
|---|---|---|---|
| `cha-bong` | 07074 "Ruốc thịt lợn" (nhóm 7, STT 352) | nước 25,8 · **369 kcal** · đạm 46,6 · béo 20,3 · glucid 0,0 · xơ 0,0 · tro 7,3 · Ca 29mg · Fe 0,30mg · P 16mg · natri: bảng trống → null. Atwater 369,1 ✓ | `xoi-man` (nâng — §3), `kho-qua-xao-trung`, `xoi-chien` |
| `ngo-sen` | 04059 "Ngó sen" (nhóm 4, STT 141; thải bỏ 11% → edible 89%) | nước 82,9 · **61 kcal** · đạm 1,0 · béo 0,1 · glucid 13,9 · xơ 1,2 · Ca 19 · Fe 0,50 · P 51 · K 556 · Na 40 · vitC 25. Atwater 60,5 ✓ | `goi-ngo-sen`, `goi-tom-thit` |
| `rau-nhut` | 04089 "Rau rút" (nhóm 4, STT 171; thải bỏ 55% → edible 45%) | nước 90,4 · **28 kcal** · đạm 5,1 · glucid 1,8 · xơ 1,9 · tro 0,8 · Ca 180 · P 59 · béo + natri: bảng trống → null. Atwater không béo 27,6 ✓ | `canh-rau-nhut-tom-the` |
| `tuong-ban` | 13020 "Tương nếp" (nhóm 13, STT 507) | nước 68,3 · **86 kcal** · đạm 4,3 · béo 0,7 · glucid 15,7 · xơ 0,0 · tro 11,0 · natri: bảng trống → null. Atwater 86,3 ✓ | `bi-cuon-chay`, `bun-mam-chay`, `ca-kho`, `nem-cuon` |

Ghi chú phân xử:

- **`cha-bong`:** cụm số web "369 kcal, đạm 53, béo 20,4, nước 19,3" mà
  LV2-008 loại vì tự mâu thuẫn nay rõ nguồn gốc — sao chép méo mó chính
  mục 07074 (đúng kcal/béo, sai đạm/nước). Theo bản gốc, đúng tiền lệ
  cá mè/hến (bản đăng lại sai cột).
- **`ngo-sen`:** bộ số bảng VN (61/1,0/13,9) KHÁC hẳn bộ số củ sen USDA
  (74/2,6/17,2) mà hồ sơ cũ cảnh báo — chỉ trùng natri 40 mg. Đây là mục
  riêng của bảng quốc gia cho đúng tên "ngó sen" (tên Anh: "Lotus, stem
  underground"), không phải số củ sen gán nhầm. Lưu ý định danh còn dở
  đã ghi trong `mapping_note`.
- **`rau-nhut`:** "rau rút" là tên miền Bắc của *Neptunia oleracea*
  (PROSEA xác nhận cặp tên; tên Anh của mục ghi đúng chi Neptunia).
  Việc cần làm trong hồ sơ cũ ("kiểm chứng bản in TPTP VN") đã khép.
- **`tuong-ban`:** tương Bần/Cự Đà lên men bằng mốc nuôi trên xôi nếp —
  khớp biến thể "tương nếp" của bảng; biến thể 13019 "tương ngô"
  (75 kcal, đạm 3,9, béo 0,1, carb 14,5) không dùng vì khác cơ chất mốc.
  Miso/doenjang vẫn bị loại (đặc/mặn ~2×); bộ số mới khẳng định độ
  loãng của tương Việt.

Cả 4 nguyên liệu: `fct_source='vn_nin'`, `mapping_confidence='low'`,
confidence file `low`, món dùng chúng mang cờ CHƯA KIỂM ĐỊNH theo cơ
chế SourceTier sẵn có. Không đụng tags/allergen của `cha-bong`
(cờ cá do ALLERGEN-FIX giữ nguyên).

## 3. Món được nâng: `xoi-man` → Mức 2 THƯỜNG

Điều kiện tiên quyết trong chính `import_note` của món ("Khi cha-bong
có số: bổ sung ~12g và nâng Mức 2 ngay, không cần tra lại") và phân xử
LV2-007 §2/§3.7 đã đủ căn cứ. Đã áp:

- Bổ sung dòng `cha-bong` 12 g (Điện Máy XANH: 50 g/3–4 người ≈
  12,5–16,7 g/khẩu phần; 12 g ở cận dưới, khớp dải 10–14 g của LV2-007).
- Ghi 3 `recipe_sources` đã mở kiểm chứng lại tận nơi 2026-11-02:
  Điện Máy XANH (định lượng đầy đủ), Cookpad NgaLe 3518055,
  VnExpress/Bùi Thủy 2023 — 3 nhà xuất bản độc lập, cả 3 đều có
  chà bông trong thành phần/bước trình bày.
- Gỡ `level_basis='tcm_compensation'` + `level_note`;
  `completeness_level` giữ 2 → Mức 2 thường.
- Kiểm chứng trên DB `shake_hungry_t05`: món tính đủ khoảng
  (missing rỗng), validate V12 đạt.

Bức tranh mức toàn kho đổi: **187 Mức 3 · 286 Mức 2 thường · 113 Mức 2
(có note) · 68 Mức 1 = 654** (xoi-man chuyển từ nhóm nâng bù sang
thường; tổng không đổi).

## 4. Nguyên liệu GIỮ NULL (đã khép hướng tra chính)

| Slug | Kết quả T-05 |
|---|---|
| `tuong-hat` (ưu tiên 1) | Kiểm từng mục nhóm 13 bản toàn văn: chỉ có tương ngô/tương nếp/tương ớt — **không có tương hột**. Tra lại nhãn Cholimex: không nơi nào công bố bảng/100g đầy đủ (chỉ thành phần; một con số lẻ "~205 kcal" vô danh; "bảng" dạng khoảng ước lượng của trang bán hàng). → `bun-suong` **giữ "Mức 2 (có note)"**, `level_note` vẫn đúng nguyên văn; đã ghi truy vết T-05 vào `import_note` của món. Chỉ còn: nhãn in bao bì thật (`nhan_san_pham`) hoặc tự đo (`tu_do`). |
| `mam-ca-sac`, `mam-ca-linh` | Nhóm 13 đủ 23 mục chỉ có mắm tôm/mắm tép chua/nước mắm; nhóm 8 đủ 59 mục không có mắm cá → **bảng quốc gia không có sản phẩm mắm cá nguyên con ở bất kỳ nhóm nào**; hướng FCT VN chấm dứt. Nhãn hãng vẫn chỉ có thành phần. Chỉ còn nhãn số nhà sản xuất hoặc tự đo. Họ bún mắm/lẩu mắm + `bun-nuoc-leo` giữ trạng thái hiện tại. |
| `la-khuc` | Mục lục nhóm 4 (126 mục) + toàn sách: không có rau khúc/*Pseudognaphalium*. Giữ null. (File `banh-khuc` hiện không mang dòng `la-khuc` — lá khúc là hương định danh theo tên món nhưng công thức đã soạn không có dòng riêng; không tự thêm dòng trong đợt này.) |
| `la-gai` | Nhóm 4 không có lá gai; nguồn văn liệu cũ vẫn chỉ một phần (đạm/béo lá khô, thiếu carb/kcal). Giữ null. |
| `mac-khen`, `hat-doi` | Nhóm 13 đủ 23 mục gia vị: không có. Giữ null (tác động thấp — vài gram/món, đã ghi trong hồ sơ). |
| `la-suong-sam` | Nhóm 4 không có. Giữ null. |
| `cha-lua-chay` | Nhóm 7 chỉ có chả lợn/chả quế lợn; sản phẩm phối trộn theo hãng, nhãn hãng vẫn không có bảng số kiểm chứng được. Giữ null. |
| men rượu (slug riêng) | Không tạo slug trong đợt này: việc chọn chất nở cho `banh-bo` (`men-no` ~1g hay `com-ruou` ~20g) là phiếu chờ chủ dự án (LV2-007 §3.6); tạo slug men rượu đơn lẻ không mở khoá món nào khi chưa có quyết đó, và không FCT nào có mục men rượu VN. |

## 5. Tác động lên các món khác (recompute trên DB kiểm chứng)

- `kho-qua-xao-trung`, `xoi-chien`: hết thiếu `cha-bong` (missing rỗng).
- `goi-tom-thit` (Mức 2 thường): hết thiếu `ngo-sen`; còn thiếu
  `rau-ram` (5 g — nguyên liệu khác, ngoài phạm vi §6).
- `ca-kho` (Mức 3), `nem-cuon` (Mức 3), `bun-mam-chay` (Mức 2 có note):
  hết thiếu `tuong-ban` (`ca-kho` còn thiếu `rieng`, `nem-cuon` còn
  thiếu `la-he` — ngoài phạm vi §6).
- `canh-rau-nhut-tom-the`, `bi-cuon-chay`: nay **0% khối lượng thiếu
  số** nhưng vẫn Mức 1 vì `recipe_sources = 0` — nút thắt đã chuyển
  sang biên tập nguồn công thức (hàng đợi LV2 thường), không còn là
  nguyên liệu.
- `goi-ngo-sen`: chỉ còn thiếu `rau-ram` 5 g (2,1% khối lượng) +
  `recipe_sources = 0` → giữ Mức 1.
- `bun-suong`: vẫn thiếu `tuong-hat` 7 g (tính được khoảng, thiếu
  1,6% khối lượng) — giữ "Mức 2 (có note)".

## 6. Phát hiện phụ (ghi nhận, không áp trong đợt này)

- **Mạch nha** — Bảng VN có mục 12025 "Mạch nha" (Malt): nước 15,0 ·
  331 kcal · đạm 0,2 · béo 0,0 · glucid 82,5 · xơ 0,0 · tro 2,3
  (đã mở kiểm chứng). Kho chưa có slug `mach-nha` và §6 chỉ liệt kê
  dạng "chưa có slug, phụ — không chặn"; để sẵn bộ số này cho đợt tạo
  slug sau.
- **Cà pháo** — Bảng VN có mục 04006 "Cà pháo" (Egg plant - small):
  nước 92,5 · 20 kcal · đạm 1,5 · glucid 3,6 · xơ 1,6 · Na 7 · Ca 12 ·
  Fe 0,70 · vitC 3 (béo: bảng trống). Chưa có slug; liên quan mục §6
  `ca-cham-mam-tom` khi tạo slug.

## 7. Kiểm chứng

- DB kiểm chứng riêng `shake_hungry_t05`: `migrate:fresh --seed` +
  `lacdoi:recompute-calories` (586 món tính khoảng, 0 món chưa đủ dữ
  liệu, 68 Mức 1) + `lacdoi:recompute-tcm` (phủ không đổi: 500 món
  ≥60%).
- `lacdoi:validate-data`: **0 🔴**, 24 🟡 (toàn bộ là V9/V8/V7 đã biết;
  không cảnh báo nào thuộc file đã sửa).
- Pest: **173/173 đạt (873 assertions)**, gồm ExportStabilityTest 3/3.
  Lượt chạy đầu có 3 lỗi thoáng qua ở ExportStabilityTest trên file
  không thuộc đợt này (`chao-long`, `tom-rang-me`...) do các phiên
  song song commit/ghi file giữa lúc chạy (HEAD di chuyển a310c22 →
  ed2dd71 trong cửa sổ chạy); chạy lại cửa sổ sạch đạt toàn bộ — đúng
  mẫu đã ghi nhận ở AUDIT-003.
- Không chạm: 7 file media của phiên song song (đã được phiên đó tự
  commit trong lúc đợt này chạy), file của nhóm T-04 ngoài `bun-suong`
  (chỉ thêm truy vết vào `import_note`), tags/allergen của `cha-bong`.

> **Đính chính (2026-11-02, sau khi đối chiếu mọi thông báo hoàn thành
> chạy nền):** Dòng "Pest: 173/173 đạt (873 assertions)" ở trên dựa trên
> MỘT kết quả chạy nền được chuyển về trong cửa sổ làm việc. Các thông
> báo hoàn thành khác của những lượt chạy toàn bộ trong cùng cửa sổ MÂU
> THUẪN với nó: một lượt 1 lỗi (ExportStabilityTest), một lượt 1 lỗi
> (AllergenExclusionTest), và một lượt chạy lại trên DB cô lập
> `shake_hungry_t05` cho 3 lỗi / 173 đạt với chỉ 673 assertions, thời
> lượng 525 giây. Nguyên nhân: suốt cửa sổ đó các phiên song song
> (T-02, T-04, QD-01, TCM-PROMOTE-003) dùng chung DB test
> `shake_hungry_test` và sửa/commit cây làm việc giữa lúc chạy (HEAD di
> chuyển nhiều lần; tổng assertions dao động 673–873) — các lượt chạy
> nhiễu lẫn nhau; lỗi quan sát được đều nằm ở test thuộc luồng công
> việc song song, không thuộc file T-05 đã sửa. **Phần kiểm chứng chắc
> chắn của riêng T-05:** ExportStabilityTest chạy riêng ở foreground
> 3/3; `lacdoi:validate-data` **0 🔴** trên DB riêng; seed + recompute
> trên DB riêng khớp kỳ vọng. Bằng chứng hạ lưu: TCM-PROMOTE-003
> (`7ad160d`, `61819bc`) quét toàn kho trên nền `fe876aa` và nâng
> `xoi-man` lên Mức 3 (phủ TCM 66,01%) — việc này đòi hỏi dữ liệu T-05
> seed/tính sạch. **Việc còn mở:** chạy lại toàn bộ Pest một lượt ở cửa
> sổ thật sạch (không phiên song song) để chốt kiểm chứng toàn bộ; cho
> tới lúc đó con số "173/173" phải được đọc là CHƯA kiểm chứng chắc
> chắn. ROADMAP (mục T-05) cũng ghi "Pest 173/173" nhưng đang bị phiên
> song song giữ bản sửa chưa commit nên chưa chèn đính chính vào file
> đó được; khối này + đính chính ở CLAUDE §0 lần 39 là bản ghi chuẩn.

## 8. Commit

- Dữ liệu: 13 file nguyên liệu + `xoi-man` + `bun-suong` (pathspec).
- Tài liệu: file này + bổ sung LV2-012 §6 + ROADMAP + CLAUDE §0
  (QT-7, cùng đợt commit).
