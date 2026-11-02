# [TCM-PROMOTE-004] — Quét lại cổng toàn kho sau T-06: 26 món lên Mức 3, nâng bù 1 món

**Mã task:** TCM-PROMOTE-004 · **Giai đoạn:** GĐ6 · **Loại:** Dữ liệu
**Status:** ✅ DONE · **Ngày:** 2026-11-02 · **Phụ thuộc:** TCM-PROMOTE-003 (tiền lệ cổng), VN-001 đợt bổ sung T-06 (32 ghi chú mới), T-04 (`27ac691` — 6 món về Mức 2 thường bằng đường công thức)

**Căn cứ:** bước quét cổng của gói T-06 (chỉ đạo chủ dự án 2026-11-02 — xử lý toàn bộ tồn đọng): quét lại cổng Mức 3 (Mức 2 thường + phủ TCM ≥60% + `cultural_note` → Mức 3) và đường nâng bù (Mức 1 + phủ ≥60% + `cultural_note` → "Mức 2 (có note)") trên TOÀN KHO, theo đúng tiền lệ TCM-PROMOTE-002/003. Kiểm chứng trên worktree sạch tại HEAD `734781a` + đúng 38 file món của đợt, DB kiểm chứng riêng `shake_hungry_t06` (seed từ `data/` + `lacdoi:recompute-tcm` + `lacdoi:recompute-calories`); không đụng DB dev/test của các phiên song song.

---

## Phần A — 20 món lên Mức 3 nhờ ghi chú mới của T-06

| # | Slug | Phủ tính vị (%) |
|---|---|---:|
| 1 | long-lon-luoc | 100,00 |
| 2 | com-nieu | 99,21 |
| 3 | banh-canh-ghe | 98,71 |
| 4 | cua-rang-muoi | 97,08 |
| 5 | bun-mang-vit | 96,60 |
| 6 | canh-bau-hen | 93,91 |
| 7 | banh-it-tran | 92,79 |
| 8 | bun-rieu-chay | 90,44 |
| 9 | com-rang | 89,77 |
| 10 | long-xao-nghe | 89,09 |
| 11 | lau-de | 88,49 |
| 12 | ga-kho-gung | 85,87 |
| 13 | com-chien-ca-man | 85,39 |
| 14 | com-tam-chay | 84,96 |
| 15 | lau-bo | 84,86 |
| 16 | che-khoai-mon | 83,37 |
| 17 | lau-nam-chay | 79,68 |
| 18 | goi-tom-thit | 73,56 |
| 19 | banh-duc-la-dua | 70,22 |
| 20 | banh-da-ca | 68,32 |

Thay đổi ở mỗi file: khối `cultural_note` mới (VN-001 đợt T-06) + `completeness_level` 2 → 3.

## Phần B — 6 món đủ cổng qua quét toàn kho: hệ quả hạ lưu của T-04

T-04 (`27ac691` + đính chính `fc0be27`) đưa 6 món từ "Mức 2 (có note)" về **Mức 2 thường** bằng đường công thức (bỏ `level_basis='tcm_compensation'`). Các món này vốn đã có `cultural_note` (từ đường nâng bù) và phủ ≥60% → trở thành ứng viên Mức 3 ngay tại thời điểm T-04 đóng, nhưng TCM-PROMOTE-003 đã đóng trước đó nên chưa được quét. Đợt quét toàn kho này gán Mức 3 kèm truy vết này; thay đổi duy nhất ở mỗi file: `completeness_level` 2 → 3.

| # | Slug | Phủ tính vị (%) |
|---|---|---:|
| 1 | banh-khoai | 100,00 |
| 2 | khoai-lang-luoc-trung | 100,00 |
| 3 | rau-den-luoc | 100,00 |
| 4 | rau-lang-luoc | 100,00 |
| 5 | mam-ba-khia | 94,29 |
| 6 | banh-duc-nong | 61,11 |

## Phần C — Đường nâng bù: 1 món

`banh-kep-la-dua` (Mức 1, phủ **60,61%** ≥ 60%) có ghi chú mới của T-06 → nâng lên "Mức 2 (có note)": `completeness_level` 1 → 2, `level_basis='tcm_compensation'`, `level_note` ghi rõ lý do chưa đạt cổng Mức 2 thường (công thức còn dạng nháp `llm_suggested`, DISH-004 đợt 2) + căn cứ nâng bù + độ phủ. Sau gán: truy vấn toàn kho còn **0** ứng viên nâng bù sót.

11 ghi chú còn lại của T-06 viết cho các món phủ <60% (`doi` 47,4 · `pho-mai-que` 15,6 · `hu-tieu-chay` 50,4 · `long-lon-xao-dua-chua` 59,2 · `mien-ga` 57,4 · `banh-da-tron` 46,0 · `banh-mi-chao` 43,6 · `banh-bao-chien` 49,2 · `che-khoai-mi` 39,7 · `ca-bong-chien-gion` 1,0 · `ca-com-rim` 13,0) — chỉ bổ sung ghi chú, không đổi mức, đúng nguyên tắc cổng hai tiêu chí.

## Thống kê mức toàn kho

Trên baseline kiểm chứng cô lập của đợt (worktree tại HEAD `734781a` + 38 file của đợt, DB `shake_hungry_t06`, khớp file):

| Trạng thái | Trước đợt (HEAD `66646c5`) | Sau đợt này |
|---|---:|---:|
| Mức 3 | 194 | **220** |
| Mức 2 thường | 286 | **260** |
| Mức 2 (có note) | 107 | **108** |
| Mức 1 | 67 | **66** |
| **Tổng** | **654** | **654** |

**Trạng thái land thực tế:** trong lúc đợt chạy, các phiên song song đã land QD-ADJUDICATE-1 (QD-06…QD-14, tới `abef695`), QD-15 (`1eca5a6` + `4d53cec`) và DOCKER-001 (`5044696`) — bức tranh tại HEAD `5044696` là 183 Mức 3 · 277 Mức 2 thường · 118 Mức 2 (có note) · 76 Mức 1 (đo trực tiếp từ file đã commit). Trước khi commit đợt này, trạng thái gốc của cả 38 file đã đối chiếu lại với HEAD hiện tại: diff từng file so với HEAD chỉ chứa đúng thay đổi của đợt này (0 dòng bị xoá ngoài `completeness_level` ở các món thăng hạng; không file nào thuộc phạm vi QD bị chạm; QD-06 không đếm lại 38 món này). Cộng delta của đợt (+26 Mức 3, −26 Mức 2 thường, +1 Mức 2 (có note), −1 Mức 1) lên HEAD hiện tại, bức tranh sau khi land: **209 Mức 3 · 251 Mức 2 thường · 119 Mức 2 (có note) · 75 Mức 1 = 654**, ghi chú **426/654** (đo lại bằng overlay 38 file lên HEAD, khớp số học).

Toàn vẹn cổng sau đợt (truy vấn DB kiểm chứng): 0 món Mức 3 thiếu `cultural_note`; 0 món Mức 3 phủ <60%; 0 ứng viên Mức 3 còn sót; 0 ứng viên nâng bù còn sót. Tổng số món có `cultural_note`: **426/654**. Recompute: 648 món có tổng hợp TCM (500 đạt phủ ≥60%); 588 món tính được calo, 66 món Mức 1 chỉ có estimated.

## Kiểm chứng

- `lacdoi:validate-data` trên DB kiểm chứng `shake_hungry_t06`: **0 🔴**, 23 🟡 (các lớp cảnh báo đã biết — V9 khẩu phần rất nhỏ, V10, V8 nguyên liệu chưa dùng, V7 tên gần trùng có từ trước) trên 3.344 bản ghi.
- Pest tại worktree sạch (DB test riêng `shake_hungry_t06pest`, bộ test tại HEAD `734781a`): **177/177 xanh tuyệt đối** (719 assertions), gồm ExportStabilityTest 3/3 (vòng khép kín toàn cục đạt — seed→export là điểm cố định trên cả 38 file mới). Lượt chạy đầu cho 10 test đỏ, tất cả đều là artifact môi trường/contention, không phải dữ liệu: 9 test trang 500 vì worktree thiếu `public/build` (Vite manifest — output build không nằm trong git) và ExportStability toàn cục lệch do 3 phiên Pest của các phiên song song chạy đồng thời trên cùng MySQL (đúng kiểu nhiễu đã ghi nhận ở T-08); bổ sung `public/build` + chạy lại: xanh tuyệt đối, và ExportStability chạy riêng trước đó cũng 3/3.
- Phạm vi file đợt này: 38 file món (32 ghi chú T-06 + 6 thăng hạng Phần B) + 2 file tài liệu (VN-001.md, file này). Commit pathspec riêng; không `git add -A` (cây làm việc còn WIP của các phiên song song).

## Tài liệu đi kèm (QT-7)

ROADMAP + CLAUDE §0 đã cập nhật trong cùng commit của đợt này (hai file đã sạch tại thời điểm commit — các phiên song song đã land xong): bức tranh mức sau land **209 Mức 3 · 251 Mức 2 thường · 119 Mức 2 (có note) · 75 Mức 1**, ghi chú 426/654, T-06 + TCM-PROMOTE-004 ✅ DONE.

## Còn lại sau đợt

- 228 món chưa có `cultural_note` (trạng thái sau land): 182 Mức 2 thường + 46 Mức 1; theo phân tầng phủ của baseline đầu đợt: 152 món Mức 2 chỉ thiếu ghi chú và 20 món Mức 1 đủ phủ chờ ghi chú cho nâng bù (6 món thuộc phạm vi phân xử QD) — QD-ADJUDICATE-1 đổi công thức/mức một số món sau mốc đo phủ, phân tầng chính xác đo lại ở đợt quét kế tiếp. Chi tiết phán quyết chờ: mục T-06 trong VN-001.md.
- `dau-bap-xao-toi`: phủ 93,15%, vẫn chờ ghi chú nhóm A — đủ cổng Mức 3 ngay khi có nguồn đạt chuẩn.
- `bun-bo-chay`: phủ 77,63%, chỉ chờ khôi phục/census được nguồn — ứng viên Mức 3 sáng nhất cho đợt ghi chú kế tiếp.
