# [TCM-PROMOTE-001] — Nâng bù "Mức 2 (có note)" cho 87 món đủ điều kiện (đường nâng bù Mức 3)

**Mã task:** TCM-PROMOTE-001 · **Giai đoạn:** GĐ6 · **Loại:** Dữ liệu
**Status:** ✅ DONE · **Ngày:** 2026-11-01 · **Phụ thuộc:** TCM-000 (cơ chế `level_basis`/`level_note` + cổng V20 + miễn trừ R8), TCM-001 (coverage), VN-001 đợt 1 (ghi chú văn hoá)

**Căn cứ:** đường nâng bù trong kế hoạch Mức 3 §1 (chốt của chủ dự án 2026-11-01): sản phẩm
đang Mức 1, không đạt cổng Mức 2 thường nhưng **đủ thông tin Mức 3** (độ phủ tính vị ≥60%
+ có ghi chú văn hoá) thì nâng lên **"Mức 2 (có note)"** — trạng thái phân biệt rõ với
Mức 2 đầy đủ qua `level_basis='tcm_compensation'` + `level_note` bắt buộc ghi rõ cổng
chưa đạt và căn cứ nâng.

---

## Phạm vi và đối chiếu danh sách

Nhóm đích = món `completeness_level=1` có `coverage_pct ≥ 60` trong `dish_tcm_summary`
và đã có `cultural_note`. Đối chiếu **3 chiều, khớp tuyệt đối, không chênh lệch**:

1. **File `data/`:** đúng 87 file món có `completeness_level=1` + khối `cultural_note`.
2. **VN-001.md:** danh sách đích 135 món Mức 1 đạt phủ ≥60% trừ 48 món chờ (31 nhóm A +
   12 nhóm B + 5 nhóm C) = đúng 87 món, từng slug khớp với (1).
3. **DB kiểm chứng riêng** (`shake_hungry_verify`, seed lại từ `data/` +
   `lacdoi:recompute-tcm`): truy vấn level=1 + coverage ≥60 + `cultural_note` NOT NULL
   = **87** (trên nền 182 món Mức 1, 135 món đạt phủ ≥60%). Coverage từng món lấy từ DB
   này (làm tròn 1 chữ số thập phân, khớp bảng coverage của VN-001 ở cả 87 món).

## Cách soạn `level_note`

Mẫu chung: *"Chưa đạt cổng Mức 2 thường vì \<lý do cụ thể của món\>. Được nâng theo
đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị \<X\>% ≥ 60% và
đã có ghi chú văn hoá."*

Lý do của từng món lấy từ **hồ sơ giữ Mức 1 trong `import_note` của chính file món**
(các mục [LV2-…]/[AUDIT-FIX…] đã ghi qua các đợt xét) — rà lại từng món, không suy
đoán; món cùng nhóm lý do dùng cùng khung câu nhưng nội dung lý do khớp hồ sơ riêng.
Kiểm chứng chéo cơ học sau khi soạn: mọi note nhắc "429" đều có "429" trong hồ sơ của
món; mọi note khai nguyên liệu định danh null/chưa có slug đều đối chiếu đúng trạng
thái file nguyên liệu (kcal null có hồ sơ, hoặc chưa tồn tại slug: `men-ruou`,
`ca-phao`, `dau-van`, `oc-vu-nang`).

### Phân bố theo nhóm lý do

| Nhóm lý do chưa đạt cổng Mức 2 thường | Số món |
|---|---|
| 429 — nguồn bị chặn 429, chưa kết luận | 14 |
| TRAN-NL — trần nguyên liệu định danh (null/chưa có slug) | 20 |
| THIEU-NGUON — thiếu nguồn/không đồng thuận/không quy đổi được | 23 |
| BIEN-THE — biến thể/chờ quyết định danh | 21 |
| KHONG-CO-CT — không có công thức đúng định danh/chưa biên soạn | 8 |
| LINK-ROT — nguồn đúng món đã chết link | 1 |
| **Tổng** | **87** |

## Thay đổi dữ liệu

Mỗi file món: `completeness_level` 1 → 2, chèn `level_basis: "tcm_compensation"` và
`level_note` ngay sau `completeness_level` (đúng vị trí exporter TCM-000 quy định);
không đổi bất kỳ trường nào khác (diff từng file đúng +3/−1 dòng).

**Thống kê mức toàn kho sau nâng — khớp 3 cách (đếm file · DB kiểm chứng · recompute):**

| Trạng thái | Trước | Sau |
|---|---|---|
| Mức 2 thường (`level_basis` null) | 472 | 472 |
| Mức 2 (có note) (`level_basis='tcm_compensation'`) | 0 | **87** |
| Mức 1 | 182 | **95** |
| Tổng món | 654 | 654 |

## Kiểm chứng (cổng đợt)

- `lacdoi:validate-data` trên DB kiểm chứng riêng: **0 🔴** / 22 🟡 (3.344 bản ghi) —
  cổng V20 cho nâng bù đạt ở cả 87 món (không món nào thiếu `level_note`/`cultural_note`;
  kiểm trực tiếp trong DB: 0 thiếu note, 0 thiếu ghi chú văn hoá). 22 🟡 đều là lớp đã
  biết từ trước (V9 kcal/khẩu phần ở món khẩu phần nhỏ, V7, V8, V10), không 🟡 nào do
  đợt này sinh ra.
- **Pest 157/157** (610 assertions), trong đó `ExportStabilityTest` đạt — vòng
  seed → export vẫn là điểm cố định với 87 file đã sửa (khuôn exporter giữ nguyên từng
  byte) và `TcmFoundationTest` đạt (25 test ở hai file cổng, chạy riêng xác nhận lại).
- **Hiển thị thật** (server tạm trên DB kiểm chứng): API `/api/dishes/banh-dap` trả
  `level_label = "Mức 2 (có note)"` kèm `level_note` đầy đủ; món Mức 2 thường
  (`pho-ga`) trả "Mức 2"; món còn Mức 1 (`canh-khoai-so`) trả "Mức 1". Admin
  `Dishes.jsx` gắn thẻ vàng "Mức 2 (có note)" theo đúng trường `level_basis` này.
- File ↔ DB: `level_note` trong DB sau seed khớp từng ký tự với file ở cả 87 món.

## Danh sách 87 món đã nâng (kèm `level_note` đầy đủ)

| # | Slug | Phủ % | Nhóm lý do | level_note |
|---|---|---|---|---|
| 1 | `banh-bao-banh-vac` | 83.4 | 429 | Chưa đạt cổng Mức 2 thường vì lượt mở nguồn đối chiếu bị chặn 429, mới mở được 1 họ có định lượng — chưa đủ 3 họ độc lập cho đúng món (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 83.4% ≥ 60% và đã có ghi chú văn hoá. |
| 2 | `banh-beo-bi` | 80.1 | 429 | Chưa đạt cổng Mức 2 thường vì lượt mở nguồn đầu tiên đã bị chặn 429; các nguồn về đúng biến thể chợ Búng chỉ liệt kê nguyên liệu, không công bố định lượng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 80.1% ≥ 60% và đã có ghi chú văn hoá. |
| 3 | `banh-duc-nong` | 89.4 | 429 | Chưa đạt cổng Mức 2 thường vì cả 3 trang nguồn mở đọc đều bị chặn 429, chưa kiểm chứng được họ nào (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 89.4% ≥ 60% và đã có ghi chú văn hoá. |
| 4 | `banh-gai` | 100.0 | 429 | Chưa đạt cổng Mức 2 thường vì hai lần mở nguồn đều bị chặn 429, đồng thời lá gai — nguyên liệu định danh của vỏ bánh — chưa có số dinh dưỡng trong kho (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 5 | `banh-gat-gu` | 88.2 | 429 | Chưa đạt cổng Mức 2 thường vì hai lần mở nguồn đều bị chặn 429, chưa có họ định lượng độc lập nào được mở đọc kiểm chứng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 88.2% ≥ 60% và đã có ghi chú văn hoá. |
| 6 | `banh-hoa-hong-trang` | 79.4 | 429 | Chưa đạt cổng Mức 2 thường vì nguồn có định lượng bị chặn 429 khi mở, nguồn mở được chỉ mô tả, không định lượng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 79.4% ≥ 60% và đã có ghi chú văn hoá. |
| 7 | `banh-khoai` | 100.0 | 429 | Chưa đạt cổng Mức 2 thường vì bị chặn 429 ngay lần mở nguồn đầu tiên, chưa kiểm chứng được họ nào (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 8 | `don` | 82.8 | 429 | Chưa đạt cổng Mức 2 thường vì lượt mở nguồn bị chặn 429, chưa kiểm chứng được họ nào bằng mở đọc thật (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 82.8% ≥ 60% và đã có ghi chú văn hoá. |
| 9 | `goi-ca-me` | 86.0 | 429 | Chưa đạt cổng Mức 2 thường vì lượt mở nguồn bị chặn 429 nên chưa kiểm chứng được họ định lượng nào (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 86.0% ≥ 60% và đã có ghi chú văn hoá. |
| 10 | `hu-tieu-ca` | 71.9 | 429 | Chưa đạt cổng Mức 2 thường vì lượt thử lại bị chặn 429 khi mở họ nguồn mới; các họ đã mở trước đó đều không có định lượng quy đổi tin cậy được về khẩu phần (LV2-011, LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 71.9% ≥ 60% và đã có ghi chú văn hoá. |
| 11 | `kho-nhai` | 100.0 | 429 | Chưa đạt cổng Mức 2 thường vì lần mở nguồn đầu tiên bị chặn 429, và món còn cần quyết định định danh riêng trước khi xét Mức 2 (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 12 | `mam-ba-khia` | 95.7 | 429 | Chưa đạt cổng Mức 2 thường vì hai lần mở nguồn ứng viên đều bị chặn 429, chưa đếm được họ nào chính thức (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.7% ≥ 60% và đã có ghi chú văn hoá. |
| 13 | `mam-ca-loc` | 96.5 | 429 | Chưa đạt cổng Mức 2 thường vì mới mở đọc được 1 họ; hai họ ứng viên khác bị chặn 429 khi mở trực tiếp (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 96.5% ≥ 60% và đã có ghi chú văn hoá. |
| 14 | `muc-kho-nuong` | 77.3 | 429 | Chưa đạt cổng Mức 2 thường vì lượt mở đọc nguồn bị chặn 429, chưa kiểm chứng được họ nào (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 77.3% ≥ 60% và đã có ghi chú văn hoá. |
| 15 | `banh-dap` | 64.6 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì mắm nêm — nguyên liệu định danh của món — chưa có số dinh dưỡng đạt chuẩn trong kho (hồ sơ null), đồng thời không họ nguồn nào cho định lượng quy đổi được về 1 phần (LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 64.6% ≥ 60% và đã có ghi chú văn hoá. |
| 16 | `banh-it-la-gai` | 93.0 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì lá gai — nguyên liệu định danh tạo màu vỏ — chưa có số dinh dưỡng trong kho (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 93.0% ≥ 60% và đã có ghi chú văn hoá. |
| 17 | `banh-khuc` | 97.3 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì lá khúc — nguyên liệu định danh làm nên tên món — chưa có số dinh dưỡng đạt chuẩn trong kho và file chưa có dòng lá khúc (LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 97.3% ≥ 60% và đã có ghi chú văn hoá. |
| 18 | `bun-nuoc-leo` | 89.9 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì mắm cá linh và mắm cá sặc — nguyên liệu định danh làm nên nước lèo — đều chưa có số dinh dưỡng đạt chuẩn trong kho (LV2-008). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 89.9% ≥ 60% và đã có ghi chú văn hoá. |
| 19 | `bun-suong` | 96.7 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì tương hột — nguyên liệu định danh của nước lèo — đã chốt không có số dinh dưỡng đạt chuẩn trong kho (LV2-010). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 96.7% ≥ 60% và đã có ghi chú văn hoá. |
| 20 | `ca-cham-mam-tom` | 83.0 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì kho chưa có nguyên liệu cà pháo — nguyên liệu định danh của món — nên file đang dùng tạm cà tím (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 83.0% ≥ 60% và đã có ghi chú văn hoá. |
| 21 | `cha-lua-chay` | 88.9 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì nguyên liệu thành phẩm chả lụa chay chưa có số dinh dưỡng trong kho và biến thể chuẩn (đậu phụ + tàu hũ ky hay đơn nền tàu hũ ky) chưa được chốt (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 88.9% ≥ 60% và đã có ghi chú văn hoá. |
| 22 | `che-dau-van` | 92.1 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì đậu ván chưa có nguyên liệu trong kho; dòng đậu trắng trong file là proxy khác loài chưa được duyệt ánh xạ (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 92.1% ≥ 60% và đã có ghi chú văn hoá. |
| 23 | `com-ruou` | 99.2 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì men rượu — nguyên liệu quyết định định danh món — chưa có nguyên liệu trong kho (men nở bánh mì không thay thế được) (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 99.2% ≥ 60% và đã có ghi chú văn hoá. |
| 24 | `ga-kho-hat-doi` | 87.4 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì hạt dổi — nguyên liệu định danh — chưa có số dinh dưỡng đạt chuẩn ở bất kỳ bảng thành phần nào, và biến thể kho chưa có công thức độc lập xác nhận tồn tại (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 87.4% ≥ 60% và đã có ghi chú văn hoá. |
| 25 | `ga-nuong-mac-khen` | 91.4 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì mắc khén — nguyên liệu định danh — chưa có số dinh dưỡng đạt chuẩn ở nguồn công khai nào, dù công thức đã có nguồn tốt (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 91.4% ≥ 60% và đã có ghi chú văn hoá. |
| 26 | `kho-ca-loc` | 100.0 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì món là sản phẩm khô ăn trực tiếp nhưng dòng nguyên liệu duy nhất là cá lóc tươi — kho chưa có nguyên liệu khô cá lóc đúng định danh (LV2-009). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 27 | `lau-ga-hat-doi` | 95.3 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì hạt dổi — nguyên liệu định danh — không có số dinh dưỡng ở mọi nguồn đã tra, đồng thời không tìm thấy công thức lẩu gà hạt dổi có định lượng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.3% ≥ 60% và đã có ghi chú văn hoá. |
| 28 | `lau-mam-ca-linh` | 75.9 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì mắm cá linh — nguyên liệu định danh — vẫn chưa có số dinh dưỡng đạt chuẩn trong kho (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 75.9% ≥ 60% và đã có ghi chú văn hoá. |
| 29 | `mam-chung` | 92.3 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì mắm cá linh/mắm cá sặc — nguyên liệu định danh — đều chưa có số dinh dưỡng đạt chuẩn trong kho, dù công thức đã đối chiếu được 2 họ độc lập (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 92.3% ≥ 60% và đã có ghi chú văn hoá. |
| 30 | `oc-vu-nang` | 100.0 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì kho chưa có nguyên liệu ốc vú nàng đúng loài — dòng ốc bươu trong file là tạm tính sai loài — và chưa có nguồn định lượng cho món (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 31 | `suon-lon-nuong-mac-khen` | 90.1 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì mắc khén — nguyên liệu định danh — chưa có số dinh dưỡng ở bất kỳ bảng tham chiếu nào, dù định lượng của file đã nằm trong vùng các họ nguồn (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 90.1% ≥ 60% và đã có ghi chú văn hoá. |
| 32 | `suong-sam` | 94.7 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì lá sương sâm — nguyên liệu định danh — chưa có số dinh dưỡng ở nguồn đạt chuẩn nào và file còn thiếu hẳn dòng lá này (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 94.7% ≥ 60% và đã có ghi chú văn hoá. |
| 33 | `xoi-la-cam` | 99.5 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì lá cẩm — nguyên liệu định danh — chưa có số dinh dưỡng đạt chuẩn trong kho, dù công thức đã đủ 3 họ và tỉ lệ của file nằm gần dải nguồn (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 99.5% ≥ 60% và đã có ghi chú văn hoá. |
| 34 | `xoi-man` | 68.3 | TRAN-NL | Chưa đạt cổng Mức 2 thường vì chà bông — topping định danh có ở 3/3 nguồn đã mở — chưa có số dinh dưỡng đạt chuẩn trong kho (LV2-007). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 68.3% ≥ 60% và đã có ghi chú văn hoá. |
| 35 | `banh-chay` | 95.4 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì mới có 2 họ có định lượng và hai họ lệch nhau hơn 2 lần ở đậu xanh và đường theo khẩu phần, không đủ đồng thuận để chốt định lượng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.4% ≥ 60% và đã có ghi chú văn hoá. |
| 36 | `banh-gia` | 100.0 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì chưa đủ 3 họ có định lượng quy đổi được — họ mở được đầy đủ mới 1 họ, họ còn lại ghi theo chén/bát và không ghi số bánh (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 37 | `banh-giay-quanh-ganh` | 71.5 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì biến thể Quánh Gánh không có công thức định lượng công khai đạt chuẩn — mới có 2 họ đã mở và cả hai đều là cách pha bột nếp hiện đại, không đủ 3 họ cho đúng biến thể (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 71.5% ≥ 60% và đã có ghi chú văn hoá. |
| 38 | `banh-in` | 100.0 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì tỉ lệ đường/bột giữa các họ đã mở phân kỳ 0,4–1,0, không đồng thuận, và không họ nào cho trọng lượng cái để quy đổi về khẩu phần (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 39 | `banh-ong-la-dua` | 75.9 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì mới có 2 họ định lượng độc lập, chưa đủ 3 họ theo cổng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 75.9% ≥ 60% và đã có ghi chú văn hoá. |
| 40 | `banh-re` | 82.6 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì chỉ có 1 họ định lượng đạt dạng công thức, các nguồn biên tập còn lại chỉ mô tả quy trình không gram (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 82.6% ≥ 60% và đã có ghi chú văn hoá. |
| 41 | `banh-xeo-chay` | 82.8 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì chỉ 1 họ quy đổi được định lượng về 1 cái; các họ còn lại dùng bột pha sẵn theo gói và không ghi số cái/khẩu phần (LV2-011, LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 82.8% ≥ 60% và đã có ghi chú văn hoá. |
| 42 | `canh-ca-ro-khe` | 97.1 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì nguồn đúng món quy về một họ duy nhất (hai trang trùng từng chữ); các nguồn độc lập đã mở đều nấu khế với cá khác, không đủ tư cách phân xử định lượng (LV2-008). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 97.1% ≥ 60% và đã có ghi chú văn hoá. |
| 43 | `canh-hen-rau-lang` | 91.6 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì chỉ có 1 nguồn đúng món và nguồn đó không định lượng — chưa có nguồn đúng món nào cho gram/khẩu phần (LV2-004 đến LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 91.6% ≥ 60% và đã có ghi chú văn hoá. |
| 44 | `chao-hat-sen` | 95.3 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì thiếu nguồn đạt chuẩn có khẩu phần cho đúng biến thể chay nấm rơm–cà rốt của file — các họ đã mở hoặc không khẩu phần, hoặc nấm chính khác biến thể (LV2-009 đến LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.3% ≥ 60% và đã có ghi chú văn hoá. |
| 45 | `chao-trai` | 92.0 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì 3 họ quy đổi được về khẩu phần nhưng định lượng gạo và trai lệch nhau 3–4 lần, không đạt đồng thuận từ 2 họ trở lên (LV2-011, LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 92.0% ≥ 60% và đã có ghi chú văn hoá. |
| 46 | `com-lam` | 100.0 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì chỉ có 2 họ định lượng độc lập và hai họ lệch nhau 43% ở lượng nếp mỗi ống, chưa phân xử được (LV2-010, LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 47 | `com-tho` | 82.8 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì không tìm được nguồn công thức có định lượng nào cho đúng biến thể của file (cơm hấp cách thuỷ trong thố với tổ hợp nhân thịt xay/tôm/lạp xưởng/nấm hương) — 0/3 nguồn đúng biến thể (LV2-005). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 82.8% ≥ 60% và đã có ghi chú văn hoá. |
| 48 | `goi-ca-tram` | 86.3 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì chỉ có 2 họ độc lập và cả hai định lượng theo con cá nguyên, không đối chiếu được gram/khẩu phần (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 86.3% ≥ 60% và đã có ghi chú văn hoá. |
| 49 | `lau-ngan` | 95.4 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì không tìm thấy trang công thức lẩu ngán nào có định lượng và món có dấu hiệu là sáng tạo biên tập theo khuôn lẩu nghêu — định danh chờ phân xử (LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.4% ≥ 60% và đã có ghi chú văn hoá. |
| 50 | `mam-tep` | 100.0 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì chỉ xác định được 2 họ có định lượng, đều là nguồn thương mại/SEO yếu và lệch nhau lớn ở tỉ lệ muối/thính — chưa đạt chuẩn 3 họ biên tập độc lập (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 51 | `pho-chua` | 93.4 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì chỉ có 1 họ định lượng và đó là bản rút gọn thiếu các topping định danh của file; chưa có nguồn định lượng cho bản đầy đủ (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 93.4% ≥ 60% và đã có ghi chú văn hoá. |
| 52 | `pho-kho-gia-lai` | 87.4 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì 4 họ đã mở chỉ có 1 họ ghi số khẩu phần; định lượng theo mẻ của các họ còn lại không quy đổi chắc chắn được về 1 tô (LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 87.4% ≥ 60% và đã có ghi chú văn hoá. |
| 53 | `tam-huu` | 92.3 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì hai họ báo chí đã mở đều không cho gram/khẩu phần, họ công thức có gram duy nhất tra được lại bị chặn 429 khi mở (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 92.3% ≥ 60% và đã có ghi chú văn hoá. |
| 54 | `thit-lon-gac-bep` | 98.8 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì chỉ có 1 họ có gram (kênh bán hàng, số tự mâu thuẫn giữa các bài); các họ còn lại xác nhận cấu trúc nhưng không định lượng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 98.8% ≥ 60% và đã có ghi chú văn hoá. |
| 55 | `tiet-canh` | 95.4 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì không họ biên tập nào công bố định lượng gram cho tiết canh lợn (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.4% ≥ 60% và đã có ghi chú văn hoá. |
| 56 | `xoi-dau-den` | 98.3 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì định lượng tuyệt đối mỗi khẩu phần giữa các họ lệch nhau khoảng 2 lần, không đạt đồng thuận từ 2 họ trở lên cho cơ sở đĩa của file (LV2-011, LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 98.3% ≥ 60% và đã có ghi chú văn hoá. |
| 57 | `xoi-hat-sen` | 100.0 | THIEU-NGUON | Chưa đạt cổng Mức 2 thường vì phần lớn trang web thuộc một họ sao chép lẫn nhau; các họ độc lập còn lại không ghi số khẩu phần — chưa đủ 3 họ quy đổi được về khẩu phần (LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 58 | `banh-cong` | 94.6 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì cả 3 họ đã mở đều có nhân thịt heo là thành phần định danh mà file thiếu — cần quyết định tái cấu trúc biến thể trước khi nâng (LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 94.6% ≥ 60% và đã có ghi chú văn hoá. |
| 59 | `banh-hoi` | 72.2 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì không họ nguồn nào khớp biến thể của file (chả lụa là thành phần bắt buộc) và câu hỏi định danh chả lụa là thành phần hay món ăn kèm còn chờ phân xử (LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 72.2% ≥ 60% và đã có ghi chú văn hoá. |
| 60 | `banh-it` | 98.5 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì định danh của file còn lẫn biến thể (bước làm nhắc lá gai nhưng nguyên liệu là nhân đậu xanh–dừa vỏ thường), cần chốt định danh trước khi nâng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 98.5% ≥ 60% và đã có ghi chú văn hoá. |
| 61 | `banh-khao` | 100.0 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì file chưa chọn biến thể nhân (đậu xanh/mứt bí hay đậu phộng/mỡ đường) — mới có 2 họ độc lập, mỗi họ một biến thể, chờ chốt biến thể (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 62 | `banh-mat` | 100.0 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì file trộn hai truyền thống cùng tên (bản ngào mật mía Nghệ An và bản hấp gói lá Bắc Bộ/Quảng Bình), cần chốt giữ bản nào trước khi nâng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 63 | `banh-ngao` | 100.0 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì file lệch biến thể định danh chuẩn (bánh nếp ngào mật mía) và kho chưa có nguyên liệu mật mía để sửa đúng định danh (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 64 | `banh-quai-vac` | 72.0 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì file (chiên, vỏ bột gạo/năng, nhân tôm thịt) không khớp trọn vẹn họ biên tập nào — cần chốt chuẩn hoá về bản Phan Thiết luộc hay giữ bản chiên (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 72.0% ≥ 60% và đã có ghi chú văn hoá. |
| 65 | `banh-tam-ca-ri` | 61.4 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì các nguồn đạt chuẩn đã mở đều là bản cà ri gà Cà Mau, trong khi file là bản heo + xíu mại — cần phân xử định danh trước khi nâng (LV2-008). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 61.4% ≥ 60% và đã có ghi chú văn hoá. |
| 66 | `bun-be-be` | 70.5 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì 3 họ đã mở tách thành 2 biến thể không đồng thuận định lượng, cần chốt biến thể chuẩn trước khi nâng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 70.5% ≥ 60% và đã có ghi chú văn hoá. |
| 67 | `bun-ken` | 74.4 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì chưa chốt biến thể (cá sòng Phú Quốc hay cá lóc đất liền) và cá định danh của các biến thể chưa có nguyên liệu/số dinh dưỡng trong kho (LV2-010). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 74.4% ≥ 60% và đã có ghi chú văn hoá. |
| 68 | `chao-dau-den` | 100.0 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì cả 3 họ đã mở đều dùng gạo nếp làm gạo chủ lực, không đủ 3 họ cho đúng biến thể gạo tẻ thuần của file (LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 69 | `chao-dau-xanh` | 94.1 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì định danh còn chờ quyết của chủ dự án — (a) đổi tên thành Cháo đậu xanh thịt bằm hay (b) bỏ thịt về bản thuần; hai họ đã mở thuộc hai biến thể loại trừ nhau (LV2-009, LV2-010 Phần C). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 94.1% ≥ 60% và đã có ghi chú văn hoá. |
| 70 | `che-dua` | 96.2 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì biến thể trong file (cùi dừa nấu nước dừa tươi với bột năng) không có công thức biên tập nào xác nhận — có thể là sáng tạo biên tập, cần xác nhận định danh (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 96.2% ≥ 60% và đã có ghi chú văn hoá. |
| 71 | `che-duong-nhan` | 98.7 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì file thiếu toàn bộ nhóm nguyên liệu định danh của chè dưỡng nhan chuẩn (nhựa đào, tuyết yến, bồ mễ, nấm tuyết — chưa biên soạn) và định danh món cần được chốt lại (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 98.7% ≥ 60% và đã có ghi chú văn hoá. |
| 72 | `com-hen-xao` | 91.2 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì không nguồn biên tập nào khớp biến thể xào chung của file — mọi nguồn đã mở đều là cơm hến trộn hoặc xào topping để riêng; định danh chờ xác nhận (LV2-009, LV2-010). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 91.2% ≥ 60% và đã có ghi chú văn hoá. |
| 73 | `lau-ca-duoi` | 60.8 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì file lai hai biến thể (me và măng chua) trong khi dòng măng là măng tươi và thiếu lá giang — chờ phân xử biến thể (LV2-010). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 60.8% ≥ 60% và đã có ghi chú văn hoá. |
| 74 | `lau-tom-cang` | 95.4 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì không họ nguồn nào trùng nền lẩu Tom Yum tự pha của file (các họ là nền cà ri–cốt dừa, nước dừa–me hoặc nước lẩu Thái sẵn) — định danh bất đồng hoàn toàn (LV2-010). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.4% ≥ 60% và đã có ghi chú văn hoá. |
| 75 | `mam-tom-chua` | 100.0 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì mọi công thức đã tra đều định lượng theo mẻ ủ, không quy đổi đáng tin được về khẩu phần thành phẩm, và tỉ lệ nguyên liệu lệch hơn 2 lần giữa các họ (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 76 | `tom-cang-nuong` | 95.4 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì định danh chờ chủ dự án chốt (nướng mọi thuần, sa tế, muối ớt hay phô mai) — biến thể mỡ hành của file không có họ đạt chuẩn nào ủng hộ (LV2-009, LV2-010). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.4% ≥ 60% và đã có ghi chú văn hoá. |
| 77 | `xoi-dau-xanh-mo-hanh` | 100.0 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì biến thể tối giản của file (không nghệ, mỡ hành từ hành lá, không hành phi) không gom đủ 3 họ đúng biến thể — các họ đạt chuẩn đều thuộc hệ xôi xéo; chờ quyết giữ biến thể biên tập hay nhập về hệ xôi xéo (LV2-010, LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 78 | `xoi-ngo` | 97.7 | BIEN-THE | Chưa đạt cổng Mức 2 thường vì định danh chờ phân xử: sửa theo họ miền Bắc thì món gần trùng xôi bắp đã Mức 2, giữ cấu trúc hiện tại thì phải đổi định danh thành xôi ngô ngọt (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 97.7% ≥ 60% và đã có ghi chú văn hoá. |
| 79 | `banh-da-do` | 100.0 | KHONG-CO-CT | Chưa đạt cổng Mức 2 thường vì file là bản nhập Wikidata chưa qua biên soạn và món là sợi làng nghề sản xuất theo mẻ, không có công thức biên tập định lượng theo khẩu phần đạt chuẩn (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 80 | `banh-pho` | 100.0 | KHONG-CO-CT | Chưa đạt cổng Mức 2 thường vì định danh trong file là bánh phở tươi thành phẩm, không tồn tại công thức biên tập cho đúng định danh này (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 81 | `bim-bim` | 100.0 | KHONG-CO-CT | Chưa đạt cổng Mức 2 thường vì định danh là sản phẩm snack thương mại phổ thông, không có công thức biên tập định lượng cho đúng định danh (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 82 | `com` | 100.0 | KHONG-CO-CT | Chưa đạt cổng Mức 2 thường vì file là bản nhập Wikidata chưa qua biên soạn (dòng gạo nếp ghi tạm tính) và cốm làng Vòng là sản phẩm làng nghề theo mẻ, không có công thức biên tập định lượng theo khẩu phần (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 83 | `mam-ca-ro` | 100.0 | KHONG-CO-CT | Chưa đạt cổng Mức 2 thường vì mắm cá rò chỉ có quy trình ủ sản xuất theo mẻ của một họ nội dung sao chép, không có từ 3 họ công thức biên tập định lượng theo khẩu phần cho đúng định danh (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 84 | `mam-cay` | 100.0 | KHONG-CO-CT | Chưa đạt cổng Mức 2 thường vì mắm cáy chỉ có công thức sản xuất theo mẻ với tỉ lệ sao chép lẫn nhau, không có công thức biên tập định lượng theo khẩu phần (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 85 | `mam-chua` | 100.0 | KHONG-CO-CT | Chưa đạt cổng Mức 2 thường vì cả 3 trang đã tra về mắm chua Vĩnh Hưng đều thuộc một họ sao chép và chỉ kể quy trình ủ, không một con số gram (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 86 | `mam-cong` | 100.0 | KHONG-CO-CT | Chưa đạt cổng Mức 2 thường vì các nguồn báo chí đã tra chỉ kể quy trình ủ, không một định lượng gram nào — không tồn tại công thức biên tập theo khẩu phần cho định danh này (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| 87 | `dau-phu-kho-chao` | 78.2 | LINK-ROT | Chưa đạt cổng Mức 2 thường vì một nguồn công thức đúng món đã chết link 404 và không tìm được nguồn thay thế đúng món đạt chuẩn — hai nguồn còn lại có một nguồn chỉ là họ kỹ thuật kho chao, chưa đủ 3 nguồn đúng món (AUDIT-FIX, PX-14). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 78.2% ≥ 60% và đã có ghi chú văn hoá. |
