# Biên bản pha sửa dữ liệu sau tầm soát — 2026-10-31

**Nguồn nhiệm vụ:** báo cáo tầm soát toàn bộ dữ liệu 2026-10-31 (968 bản ghi: 386 nguyên liệu,
500 món, 82 hồ sơ quả — 5 vấn đề 🔴, kiểm chứng 2 lượt độc lập). Chủ dự án đã duyệt triển khai
hướng hoàn thiện (chất lượng là trọng điểm) và **chốt gộp cả 2 cặp biến thể** (mít non, xôi bắp/ngô).
**Nền:** commit `ebd92bd` (Phương án A — quy ước dầu món chiên — đã xong).

**Kết quả tổng:** kho món **500 → 496 file** (476 món chuẩn + 20 biến thể có `parent_dish_slug`);
`validate-data` **964 bản ghi — 0 lỗi 🔴, 22 🟡**; `migrate:fresh --seed` dựng lại đúng 496 món;
`recompute-calories`: **491/496 món tính được** (5 món chưa tính được giữ nguyên như trước:
gỏi ngó sen, canh rau nhút, mắm nêm ×2, mắm ruột — vướng thiếu dữ liệu thật, không liên quan đợt này);
**Pest 106/106 xanh**.

---

## 1. Gộp cặp mít non — `nom-mit-non` thành biến thể của `goi-mit-non`

Theo kiến nghị §2.3.1 của báo cáo tầm soát (phép thử EDITORIAL-GUIDE §3: Lắc gợi ý cả hai trong
cùng tuần chắc chắn thấy trùng — cùng lõi mít non 150 g, cùng cấu trúc trộn chua ngọt).

- **Giữ slug chuẩn `goi-mit-non`**; `nom-mit-non` đặt `parent_dish_slug = goi-mit-non` (cơ chế
  biến thể sẵn có — engine gợi ý tầng 2 đã loại trừ cùng họ biến thể).
- **Sửa cả hai tên tiếng Anh sai miền (M-2):** bản chuẩn `…southern style` → **"young jackfruit
  salad, Quang Nam style"**; bản biến thể `…northern style` → **"young jackfruit salad with pork
  shoulder, Quang Nam style"**. Cả hai món đều là miền Trung (đặc sản Quảng Nam) — nhãn miền cũ
  sai ở cả hai và sai ngược nhau.
- **Region thống nhất:** bản chuẩn `vn-central` → `vn-quang-nam` (khớp biến thể và gốc đặc sản).
- **Thành phần:** giữ nguyên định lượng của từng bản (không đổi số — QT-1). Khác biệt của biến
  thể được ghi rõ trong `import_note` của cả hai file: biến thể dùng **thịt lợn nạc vai 60 g**
  (bản chuẩn: tai heo 60 g), tôm thẻ 50 g, ăn kèm bánh tráng 20 g, thêm vừng rang và húng quế.
- Alias hai chiều gỏi ↔ nộm vốn đã có từ ALIAS-001, giữ nguyên.

## 2. Gộp cặp xôi — `xoi-ngo` thành biến thể miền Bắc của `xoi-bap`

Theo kiến nghị §2.3.2 (gộp có điều kiện: 1 món, 2 biến thể vùng có định lượng riêng).

- **Gốc hồ sơ là `xoi-bap`** — bản đã được chủ dự án duyệt 2026-10-31; giữ nguyên toàn bộ dấu
  vết duyệt (`needs_review=false`, `reviewed_by/at/note`).
- `xoi-ngo` đặt `parent_dish_slug = xoi-bap`, vẫn `needs_review=true` (chưa duyệt — không thay đổi).
- **Không xoá thông tin vùng:** biến thể giữ region `vn-north`, tên chính "Xôi ngô", alias
  "Xôi bắp (miền Nam)"; món chuẩn giữ alias "xôi ngô (miền Bắc)". Khác biệt định lượng ghi trong
  `import_note` hai phía: bản Bắc tối giản (không đậu xanh, không đường, cốt dừa 15 g, serving
  230 g) so với bản Nam (đậu xanh 40 g, đường 8 g, cốt dừa 30 g, serving 300 g). Định lượng cả
  hai bản giữ nguyên, không đổi số.
- Tên en của biến thể chuẩn hoá: `xoi ngo (corn sticky rice)` → **"sticky rice with corn,
  northern style"** (đúng miền, đồng nhất phong cách với tên en của món chuẩn).

## 3. Các lỗi 🔴 còn lại

| Mục | Sửa | Bằng chứng / căn cứ |
|---|---|---|
| **N-1** `mam-tom` và `mam-ruoc` trùng `name_en` tuyệt đối "Fermented shrimp paste" | `mam-tom` → **"Fermented shrimp paste (mam tom)"**; `mam-ruoc` → **"Fermented acetes shrimp paste (mam ruoc)"** (mắm ruốc làm từ con ruốc — *Acetes* spp.; mắm tôm làm từ tép/moi biển). Ghi đính chính vào `mapping_note` cả hai file. | Đọc trực tiếp 2 file (hai sản phẩm khác nhau: 95 kcal vs 83 kcal, nguồn khác nhau); phong cách name_en có ngoặc đơn chú thích đã là quy ước sẵn của kho (vd `mam-nem` → "Fermented anchovy sauce (mam nem)"). |
| **N-2** `tiet-lon`: kcal trung tâm 82,1 nằm ngoài khoảng tự khai 73–82 (lệch 0,1) | `kcal_max` **82 → 82,1** — đúng số nguồn Ciqual 40600; giá trị trung tâm giờ nằm trong khoảng. Ghi đính chính vào `mapping_note`. | Số nguồn là 82,1 (Ciqual); cận trên cũ bị làm tròn xuống. Không đổi giá trị trung tâm (số của nguồn), chỉ sửa cận cho đúng nguồn. |
| **Q-1** 6 dòng giá `dua-bo` thiếu `source_url` (cả 6 dòng giá của hồ sơ) | Bổ sung URL thật, đã mở kiểm chứng nội dung bài khớp số giá trong dòng: dòng tháng 4 (20–25k) → **Dân Việt 22/04/2026** (Cổ Đạm, Hà Tĩnh — dưa bở đầu vụ 20.000–25.000 đ/kg): `danviet.vn/day-la-loai-qua-ngon-mua-he-an-mat-tan-chan-rang-dang-ban-duoc-gia-tot-o-ha-tinh-d1420422.html`; dòng tháng 5–6 (15–20k) → **CafeF 04/05/2020** (dưa bở ven quốc lộ Thanh Oai, Hà Nội 20k/kg; "cuối vụ cuối tháng 5,6 … chỉ khoảng 15-20 ngàn đồng/kg"): `cafef.vn/dau-mua-dua-le-dua-bo-ban-day-quoc-lo-gia-chi-20-35-ngan-dong-kg-hut-khach-di-duong-dung-lai-mua-ca-xe-o-to-20200504135622545.chn` | Gán theo quy ước sẵn của kho giá quả: `source_url` trỏ tới nguồn **mang con số giá của dòng đó** (tiền lệ: các dòng cherry có `source_name` gộp hai nguồn nhưng một URL). Không bịa URL nào; cả hai bài đều được mở đọc trực tiếp khi sửa. |

## 4. Cụm 5 món nước mắm đồng nhất (M-1) → hợp nhất còn 1 món

Cả 5 file có công thức giống hệt tuyệt đối (1 dòng `nuoc-mam-truyen-thong` 15 g, cùng serving
15 g, cùng slot) — theo EDITORIAL-GUIDE §3 không đủ điều kiện là món độc lập (khác biệt duy nhất
là xuất xứ sản phẩm).

- **Giữ món chuẩn `nuoc-mam`** (region `vn` — toàn quốc). Xoá 4 file: `nuoc-mam-phu-quoc`,
  `nuoc-mam-phan-thiet`, `nuoc-mam-cat-hai`, `nuoc-mam-cua-khe`.
- Tên địa danh giữ làm **alias** của món chuẩn (cả vi và en, có `note` ghi địa danh + việc gộp):
  nước mắm Phú Quốc / Phan Thiết / Cát Hải / Cửa Khè. Bổ sung tên en chính "fish sauce" cho món
  chuẩn (trước đó chỉ có tên vi). Kiểm chứng trong DB: tìm theo alias "nước mắm Phú Quốc" ra
  đúng món `nuoc-mam`.
- Mã Wikidata của 4 món đã xoá (Q7188272, Q10802093, Q10802091, Q30919660) ghi lại trong
  `import_note` của món chuẩn để truy vết; không mất thông tin nguồn gốc bản ghi.
- Kiểm tra tham chiếu trước khi xoá: ngoài `data/dishes/_index.json` (file máy sinh của
  `lacdoi:export-data`, đã cũ từ thời 61 món — không sửa tay theo quy ước của chính file đó) và
  các tài liệu/báo cáo lịch sử, không có code/test/seed nào tham chiếu 4 slug này.

## 5. Trọng số bữa của nhóm `loai:nuoc-cham` (M-4) — KHÔNG tự sửa, ghi chờ quyết

Rà soát phát hiện: 21/23 món chấm mang `weight` 8 ở slot trưa/tối. **Không có quy ước trọng số
theo loại món nào trong EDITORIAL-GUIDE §5** (trọng số là mức phù hợp theo bữa, thang 1–10,
không có trần theo nhóm) — nên theo kỷ luật của nhiệm vụ, **không tự đặt số mới**.

Bằng chứng giảm nhẹ quan trọng, ghi nhận từ code: engine Lắc (`Recommender::pool`) **đã chặn
món có `serving_grams < 80` khỏi gợi ý ở các bữa chính** (sáng/trưa/chiều/tối) — toàn bộ 23 món
nhóm nước chấm có serving 11–55 g nên hiện **không thể** bị gợi ý như một bữa chính, bất kể
weight. Phơi nhiễm còn lại chỉ ở slot ăn vặt (hiện chỉ `muoi-tom` có slot này, weight 8 — món
chấm ăn vặt là hợp lệ) và ở trang danh mục.

→ Đã ghi thành câu hỏi mở **Q10** ở ROADMAP: chủ dự án quyết (a) giữ nguyên vì engine đã chặn
theo khẩu phần, (b) hạ weight nhóm nước chấm xuống mức tượng trưng để dữ liệu tự nhất quán,
hay (c) loại hẳn nhóm này khỏi slot bữa chính ở tầng dữ liệu.

> **Đính chính 2026-11-02 — Q10 ĐÃ CHỐT (QD-13):** chủ dự án duyệt phương án **(a) giữ
> nguyên** — giữ hàng rào khẩu phần `serving_grams < 80 g` của `Recommender::pool` cho vai
> món chính như hiện tại; không hạ weight, không thiết kế vai phụ riêng cho nước chấm ở
> đợt này, không đổi code/dữ liệu. Đóng hồ sơ Q10 (QD-ADJUDICATE-1).

## 6. Ngoài phạm vi đợt này (giữ nguyên, đã có chủ sở hữu ở báo cáo tầm soát §5)

M-5 (`tuong-ot-nha-lam` tỉ lệ định lượng/serving), M-6 (`mam-ruot-an-giang` vỏ rỗng — biên soạn
thật hay tạm ẩn), M-8 (cách hiểu `source` của dòng nguyên liệu sau duyệt), M-9 (gắn parent cho
10 cặp gần trùng còn lại), N-3 (quy ước năng lượng xơ tầng 6), N-5 (chính sách để-null cho
13 lá/gia vị vi lượng), N-7 (58 hệ số phần ăn được chờ cân thật), Q-2 (giá phẳng quả nhập),
Q-3 (phủ giá miền Trung) — chờ quyết định của chủ dự án / xếp vào DISH-005 như báo cáo đã đề xuất.
