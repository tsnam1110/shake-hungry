# [TCM-PROMOTE-003] — Quét lại cổng toàn kho sau T-02: 7 món lên Mức 3, nâng bù 0 món mới

**Mã task:** TCM-PROMOTE-003 · **Giai đoạn:** GĐ6 · **Loại:** Dữ liệu
**Status:** ✅ DONE · **Ngày:** 2026-11-02 · **Phụ thuộc:** TCM-PROMOTE-002 (tiền lệ cổng), VN-001 đợt bổ sung T-02 (6 ghi chú mới), T-05 (`fe876aa` — `xoi-man` về Mức 2 thường)

**Căn cứ:** bước 5 của gói tồn đọng T-02 (chỉ đạo chủ dự án 2026-11-02 — xử lý toàn bộ tồn đọng): sau khi 7 file món bị giữ được giải toả và viết ghi chú, quét lại cổng Mức 3 (Mức 2 thường + phủ TCM ≥60% + `cultural_note` → Mức 3) và đường nâng bù (Mức 1 + phủ ≥60% + `cultural_note`) trên TOÀN KHO theo đúng tiền lệ TCM-PROMOTE-002, trên DB kiểm chứng riêng `shake_hungry_t02` (worktree sạch tại baseline, seed từ `data/` + `lacdoi:recompute-tcm`; không đụng DB dev/test của các phiên song song).

---

## Phần A — 6 món lên Mức 3 nhờ ghi chú mới của T-02 (commit `7ad160d`)

Baseline quét chính: `62e48f5` (đã gồm media `9ea4ad3`, A4.1 `555f04c`, T-13 `1f5d9a1`, ghi chú `62e48f5`). Kết quả truy vấn cổng khớp tuyệt đối với kỳ vọng: ứng viên Mức 3 = đúng 6 món vừa có ghi chú, không món nào khác; ứng viên nâng bù = 0. Thay đổi duy nhất ở mỗi file: `completeness_level` 2 → 3 (diff đúng +1/−1 dòng/file, kiểm bằng `git diff --numstat`).

| # | Slug | Tên món | Phủ tính vị (%) |
|---|---|---|---:|
| 1 | banh-canh-ca-loc | Bánh canh cá quả | 94,54 |
| 2 | bun-thang | Bún thang | 84,25 |
| 3 | canh-bap-cai-cuon-thit | Canh bắp cải cuốn thịt | 81,74 |
| 4 | com-bo-kho | Cơm bò kho | 96,45 |
| 5 | com-ga-tam-ky | Cơm gà Tam Kỳ | 94,86 |
| 6 | rau-muong-luoc | Rau muống luộc | 100,00 |

`dau-bap-xao-toi` (món thứ 7 được giải toả) có phủ 93,15% nhưng **chưa có `cultural_note`** (để chờ nhóm A ở đợt bổ sung VN-001/T-02) → không qua cổng, giữ Mức 2 — đúng nguyên tắc cổng hai tiêu chí.

## Phần B — 1 món bổ sung ngoài baseline quét chính: `xoi-man` (commit `61819bc`)

Khi đối chiếu lại trên HEAD `7ad160d`, phát hiện `xoi-man` đủ cổng nhưng không nằm trong baseline quét chính: tại `62e48f5` món còn là "Mức 2 (có note)" (`level_basis='tcm_compensation'`, chưa có dòng `cha-bong`); commit `fe876aa` của T-05 (đất sau baseline, trước commit gán mức đợt này) đã bổ sung chà bông 12 g có số `vn_nin` và đưa món về **Mức 2 thường** (gỡ basis). Trên seed tại `7ad160d`: `xoi-man` = Mức 2 thường + đã có `cultural_note` + phủ **66,01%** ≥ 60% → đủ cổng. File đã sạch trong cây làm việc (T-05 đã commit xong phần của món) → gán Mức 3 tại đợt này kèm truy vết này, diff đúng +1/−1.

## Phần C — Đường nâng bù: 0 món mới

Truy vấn toàn kho ở cả hai baseline (`62e48f5` và HEAD cuối): 0 món Mức 1 có đồng thời phủ ≥60% + `cultural_note` mà chưa được nâng — 21 món Mức 1 đạt phủ ≥60% còn lại vẫn chưa có ghi chú văn hoá đạt chuẩn (hàng đợi VN-001), không đổi so với sau TCM-PROMOTE-002.

## Thống kê mức toàn kho (DB kiểm chứng, khớp file)

| Trạng thái | Sau TCM-PROMOTE-002 + T-05 | Sau đợt này |
|---|---:|---:|
| Mức 3 | 187 | **194** |
| Mức 2 thường | 286 | **279** |
| Mức 2 (có note) | 113 | **113** |
| Mức 1 | 68 | **68** |
| **Tổng** | **654** | **654** |

(Cột "trước" = bức tranh sau khi T-05 đưa `xoi-man` về Mức 2 thường: 187 · 286 · 113 · 68. Đợt này: +7 Mức 3 ở Phần A+B, −7 Mức 2 thường.)

Toàn vẹn cổng sau đợt (truy vấn DB kiểm chứng tại HEAD cuối): 0 món Mức 3 thiếu `cultural_note`; 0 món Mức 3 có phủ <60%; 0 ứng viên Mức 3 còn sót; 0 ứng viên nâng bù còn sót. Tổng số món có `cultural_note`: **394/654**.

## Kiểm chứng

- `lacdoi:validate-data` trên DB kiểm chứng: **0 🔴** (25 🟡 thuộc các lớp cảnh báo đã biết — V9 khẩu phần rất nhỏ, V10, V8, V7; mốc TCM-PROMOTE-002 là 24 🟡).
- Pest tại checkout chính (DB test mặc định, cửa sổ sạch): lượt chốt **187/187 xanh**, gồm ExportStabilityTest đạt — vòng seed→export vẫn là điểm cố định sau khi sửa 13 file món của gói T-02. Lượt chạy ngay trước đó có 1 test đỏ **không tái lập** tại `SwapTrayTest` (đổi món random trong mâm — test phụ thuộc ngẫu nhiên của tính năng REC-005 do phiên song song phát triển, từng đỏ không ổn định từ VN-001 đợt 3; không đổi dữ liệu/code giữa hai lượt, không liên quan dữ liệu gói này).
- Phạm vi file đợt này: đúng 7 file món (6 Phần A + `xoi-man` Phần B), mỗi file chỉ đổi 1 dòng `completeness_level`.

## Còn lại sau đợt

- 68 món Mức 1: 21 món chỉ thiếu ghi chú (phủ ≥60%) + 47 món chưa đạt phủ — nâng bù/Mức 3 tiếp tục phụ thuộc nguồn tương lai (T-06/T-07).
- 279 món Mức 2 thường chưa lên Mức 3: 172 món chỉ thiếu ghi chú (178 mốc cũ − 6 món đợt này) + 107 món phủ <60%.
- `dau-bap-xao-toi`: phủ 93,15% — chỉ còn thiếu ghi chú văn hoá đạt chuẩn là đủ cổng Mức 3 ở lần quét kế tiếp.
