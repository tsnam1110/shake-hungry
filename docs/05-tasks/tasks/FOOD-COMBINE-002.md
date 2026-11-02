# [FOOD-COMBINE-002] — Lớp "Kết hợp cần lưu ý": triển khai toàn bộ bản ghi còn lại

**Mã task:** FOOD-COMBINE-002 · **Loại:** Dữ liệu (mở rộng FOOD-COMBINE-001)
**Status:** ✅ XONG (2026-11-02) — chủ dự án duyệt thiết kế + duyệt triển khai
toàn bộ bản ghi cùng ngày 2026-11-02.
**Ngày tạo:** 2026-11-02 · **Hồ sơ nghiên cứu:** `nghien-cuu-cam-ket-hop-2026-11-02.md`
(49 cặp duy nhất: E1=13, E2=8, E3=23, E0=5 — lưu ở hồ sơ dự án)
**Tiền lệ:** [FOOD-COMBINE-001.md](./FOOD-COMBINE-001.md) (đợt 1: 23 bản ghi,
schema, validator V21, nguyên tắc đỏ — tất cả giữ nguyên ở đợt này).

---

## Kết quả

**Tổng bản ghi: 23 → 38** (theo tầng hiển thị suy ra từ `evidence_class`:
tầng 1 "Tránh kết hợp" 9 → **10** · tầng 2 "Không nên kết hợp" 6 → **9** ·
tầng 3 "Kiêng kỵ theo dân gian / đông y" 8 → **19**). Mọi bản ghi mới
`needs_review=true`, nguồn dùng đúng URL đã mở kiểm chứng trong hồ sơ
nghiên cứu (truy cập 2026-11-02), không chế nguồn mới.

### 15 bản ghi xây mới ở đợt này

| Slug | Cặp (mã hồ sơ) | Tầng / trạng thái |
|---|---|---|
| `tyramin--maoi` | B13 — thực phẩm giàu tyramin × thuốc MAOI | E1 / supported |
| `toi-bo-sung--warfarin-aspirin` | B14 — tỏi **dạng viên bổ sung** × warfarin/aspirin | E2 / conditional |
| `nghe-bo-sung--warfarin` | B15 — nghệ **dạng bổ sung** × warfarin | E2 / conditional |
| `cam-thao--thuoc-ha-ap` | B18 — cam thảo × hạ áp/lợi tiểu/digoxin | E2 / conditional |
| `hai-san--vitamin-c` | A8 — hải sản × vitamin C | E3 / **refuted** |
| `cua--tra-xanh` | A3 (nửa trà) — cua × trà xanh | E3 / unproven |
| `rau-bina--sua-tuoi` | A4 (nửa sữa) — rau bina × sữa | E3 / **refuted** (cơ chế ngược lời đồn) |
| `sua-tuoi--chanh` | A10 — sữa × đồ chua | E3 / unproven |
| `sua-tuoi--chuoi` | A15 — sữa × chuối | E3 / unproven |
| `dua-leo--ca-chua` | A14 — dưa leo × cà chua | E3 / unproven |
| `hong-gion--khoai-lang` | A20 — hồng giòn × khoai lang | E3 / unproven |
| `cua--luu` | A21a — cua × lựu | E3 / **refuted** (thí nghiệm 1935) |
| `cua--dua-hau` | A21b — cua × dưa hấu | E3 / unproven |
| `gan-lon--ca` | C11 — gan lợn × cá | E3 / unproven |
| `dau-xanh--thuoc-dong-y` | A18 (nửa "giã thuốc") — đậu xanh × thuốc đông y | E3 / unproven, tuyến thuốc |

Đối chiếu hồ sơ: 23 bản ghi đợt 1 phủ 25/49 mục (B3+B4 gộp trong
`buoi--chen-kenh-canxi`; B7+B8 gộp trong `ruou-trang--an-than-paracetamol`;
B9+B10 gộp trong `sua-canxi--khang-sinh`). 15 bản ghi đợt 2 phủ thêm 15 mục
(A4 chỉ lấy nửa sữa — nửa đậu phụ đã có ở đợt 1; A18 chỉ lấy nửa niềm tin
thuốc — nửa thực phẩm của A18 thuộc E0, xem dưới). Tổng phủ: **40/49 mục**;
9 mục còn lại là loại trừ có chủ đích hoặc hoãn có lý do (mục dưới).

## Hai mục hoãn của đợt 1 — cách xử lý

- **MAOI × tyramin (B13)** — ĐÃ XÂY (`tyramin--maoi`, tầng 1). Phía thực
  phẩm là `food_group` `thuc-pham-giau-tyramin` với members là các slug
  THẬT trong kho và đúng giàu tyramin theo nhãn FDA: `bia`, `ruou-vang-do`,
  `sua-chua`, `gan-lon`, `gan-ga`. Hai slug bị LOẠI sau khi mở file kiểm
  chứng: `pho-mai` (file là phô mai chế biến/con bò cười — không phải phô
  mai ủ chín giàu tyramin) và `xuc-xich` (xúc xích tiệt trùng kiểu
  frankfurter — không phải salami lên men khô). Các dạng nguy cơ cao nhất
  theo nhãn (phô mai ủ, bia hơi/rượu chưa lọc, chiết xuất men, thịt/cá
  hun khói–lên men–hỏng) được nêu đích danh trong `condition_vi` kèm ghi
  chú chưa có slug riêng trong danh mục. Nguồn: nhãn FDA của phenelzine
  (Nardil) trên DailyMed — mục Chống chỉ định.
- **Cam thảo × thuốc huyết áp/lợi tiểu/digoxin (B18)** — ĐÃ XÂY
  (`cam-thao--thuoc-ha-ap`, tầng 2) ở **mức dữ liệu thuần**: kho không có
  slug cam thảo và đợt này không biên soạn nguyên liệu mới, nên phía thực
  phẩm là `food_group` `cam-thao` với `members: []` (hợp lệ theo V21 —
  tiền lệ `sua-tuoi--ca` đợt 1). Bản ghi tồn tại trong dữ liệu + DB (phục
  vụ duyệt nội dung và dùng khi có slug), nhưng **không gắn vào trang
  nguyên liệu/quả nào** cho tới khi cam thảo có mặt trong kho — test riêng
  khoá hành vi này. `condition_vi` ghi rõ trạng thái đó. Nguồn: FDA
  Consumer Update (qua bản đăng lại drugs.com — bản gốc fda.gov đã 404
  như hồ sơ ghi) + NCCIH.

## Giữ hoãn / loại trừ (không xây, kèm lý do)

- **B16 gừng dạng bổ sung × chống đông — GIỮ HOÃN, cập nhật lý do:** mở
  lại trang NCCIH "Ginger" ngày 2026-11-02: chỉ có cảnh báo chung "thảo
  dược có thể tương tác với thuốc", KHÔNG có cảnh báo chống đông cụ thể
  (khác hẳn trang tỏi/nghệ vốn ghi đích danh). Mức bằng chứng của hồ sơ
  (E3 "bằng chứng yếu") không khớp schema tầng 3 (tầng dân gian, bắt buộc
  có nguồn truyền thống). Chưa đủ chuẩn nguồn để xây trung thực.
- **Hoãn vì một phía chưa có slug trong kho:** thịt chó + trà xanh
  (A12/C1 — không có thịt chó), cá diếc + tỏi (C2 — không có cá diếc),
  ba ba + rau sam (C3 — không có ba ba; biến thể cổ thư ba ba + rau dền
  cũng thiếu ba ba), lươn + thịt chó (C4), thịt chó + đậu xanh (C6 — nửa
  "giã thuốc" của nó đã được phủ bởi `dau-xanh--thuoc-dong-y`), thịt gà +
  mù tạt (C10 — không có mù tạt). Xây khi nguyên liệu tương ứng vào kho.
- **Loại trừ theo thiết kế đã duyệt (không bao giờ vào bảng):** 5 mục E0
  (A6/C13 mật ong + đậu phụ, A17 rau muống + sữa, A19 măng + đường nâu,
  C5 lươn + bí đỏ, B19 vitamin C liều cao × thuốc) và 2 mục an toàn thực
  phẩm tuyến D (tiết canh + rượu; hải sản sống + rượu).

## Kiểm chứng (cửa sổ sạch, kết quả đọc từ log riêng)

- `lacdoi:validate-data`: **0 🔴 trên 3.380 bản ghi** (3.365 đợt 1 + 15 mới).
- `CombinationTest`: **7/7** (thêm 3 test đợt 2: MAOI tầng 1 trên trang bia
  + cặp viên bổ sung tỏi chỉ áp dạng viên; cam thảo có trong DB nhưng
  không gắn trang nào; món cơm gà Tam Kỳ nhận cặp dưa leo–cà chua vào
  nhóm folk). Bất biến đợt 1 giữ nguyên: không cặp nào lọc món khỏi
  catalog, tuyến thuốc không gắn vào món.
- Pest toàn bộ: **186/186** (792 assertions), gồm ExportStabilityTest 3/3
  (data/combinations không thuộc vòng export — vòng seed→export không đổi).
- Kiểm API thật qua test: tầng 1 (`/api/ingredients/bia` →
  `tyramin--maoi`), tầng 2 (`/api/ingredients/toi` → cặp viên bổ sung),
  tầng 3 (`/api/dishes/com-ga-tam-ky` → folk `dua-leo--ca-chua`).
  Lưu ý kỹ thuật: API chi tiết nguyên liệu bọc payload trong khoá
  `ingredient` (`ingredient.combinations`) — khác API quả/món (phẳng).

## Đính chính quá trình

Lượt kiểm chứng đầu của đợt này cho kết quả sai lệch (test báo thiếu bản
ghi) do 10/15 file ghi song song không thực sự nằm trên đĩa khi chạy —
đã ghi lại từng file và xác minh độc lập bằng đếm file + seed thử trước
khi chạy lại toàn bộ cổng. Số liệu trong tài liệu này là của lượt cuối,
đọc từ log trên đĩa.
