# Kế hoạch thu thập giá rau + quả — Lắc Đói

> Trạng thái ngày 2026-11-03 (hoàn thiện mục 4): **không seed giá mới nào** trong đợt này,
> theo QT-9. Lý do và phương án dưới đây là quyết định có căn cứ, không phải việc bỏ sót
> không ghi chép.

## 1. Trạng thái hiện tại (đã kiểm chứng từ DB, 2026-11-03)

| Nhóm | Trạng thái giá |
|---|---|
| Rau theo mùa (99 mục) | **Chưa có giá nào.** Model `Vegetable` không có quan hệ giá, trang `/rau-mua/{slug}` không hiện giá. |
| Quả (82 mục) | Snapshot **duy nhất ngày 2026-10-31**: 1.387 dòng `fruit_prices` cho 78/82 quả. 4 quả chưa có giá: `buoi-chum`, `dua-gang`, `mo`, `qua-dieu`. Giá cũ KHÔNG được gán sang ngày mới — trang quả phải luôn hiện `collected_at` kèm nguồn (hiện đã làm). |

## 2. Vì sao đợt 2026-11-03 không thu thập giá mới

Đợt khảo sát nguồn ngày 2026-11-03 (tìm kiếm web) cho thấy giá rau quả Việt Nam **không có
một nguồn tổng hợp đáng tin, cùng ngày, cùng đơn vị** để seed cho 99 rau + 82 quả:

- Giá rải rác ở bảng của từng cửa hàng (ví dụ một shop nông sản), bài báo "giá hôm nay" không
  ghi rõ ngày thu thập hoặc trộn nhiều ngày (cùng một kết quả tìm kiếm đã lẫn bài 13/7, 19/11,
  5/4, 23/8 — không có ngày thống nhất để gán `collected_at`).
- Cùng một mặt hàng, giá chợ và giá siêu thị chênh nhau 2–3 lần trong cùng thời điểm
  (ví dụ cải ngọt: chợ ~16.000–20.000 đ/kg, siêu thị 30.000–44.000 đ/kg; cà chua: chợ
  ~20.000–25.000 đ/kg, siêu thị 40.000–62.000 đ/kg). Seed một con số duy nhất mà không ghi
  kênh bán sẽ là số giả tạo (QT-5/QT-9).
- Đơn vị bán không thống nhất: rau lá/rau thơm bán theo mớ/bó (không có khối lượng chuẩn),
  rau củ bán theo kg, quả bán theo kg/quả/nải. Không có bảng quy đổi mớ→kg đã kiểm chứng
  thì không thể seed giá/kg cho rau thơm.
- Giá rau quả biến động theo tuần/mùa vụ. Một snapshot lấy từ bài báo cũ, dù chỉ vài tuần,
  cũng đã sai thời điểm — seed nó mà ghi ngày mới là bịa ngày thu thập.

Kết luận: **thà bỏ trống + ghi rõ trạng thái còn hơn seed giá chắp vá.** Đây chính là trường
hợp QT-9 quy định: không có thông tin đáng tin thì bỏ trống, tuyệt đối không bịa.

## 3. Phương án thu thập (để chủ dự án chốt trước khi làm)

### 3.1. Thiết kế snapshot

- **Một ngày thu thập duy nhất** cho cả đợt (ghi `collected_at` đúng ngày đó, không gán lại).
- **Hai kênh riêng biệt, không trộn:** (a) chợ đầu mối/bán lẻ tại Hà Nội + TP.HCM
  (giá chợ), (b) 2 chuỗi siêu thị có niêm yết online (giá siêu thị). Mỗi dòng giá ghi rõ
  kênh + địa điểm + nguồn (tên + URL trang sản phẩm/bài báo + ngày truy cập).
- **Đơn vị chuẩn:** đồng/kg cho rau củ/quả; rau thơm bán theo mớ thì ghi cả giá/mớ VÀ khối
  lượng mớ cân thực tế tại điểm bán (cân 3 mớ lấy trung bình) rồi mới quy ra đồng/kg —
  nếu không cân được thì chỉ ghi giá/mớ, không quy đổi mù.
- **Khoảng giá, không điểm:** mỗi mặt hàng ghi `price_min`/`price_max` theo các điểm bán
  trong cùng kênh + ngày; không lấy giá một shop làm giá thị trường.
- **Tần suất:** snapshot hàng tháng (cùng tuần mỗi tháng) + snapshot đột xuất khi giá biến
  động mạnh theo mùa vụ; các snapshot cũ giữ nguyên, không ghi đè ngày.

### 3.2. Nguồn ưu tiên (theo thứ tự)

1. Niêm yết online của siêu thị (Bách Hóa Xanh, WinMart, Co.opmart) — có giá + khối lượng
   gói rõ ràng, truy vết được bằng URL.
2. Bảng giá chợ đầu mối do Sở Công Thương/đơn vị quản lý chợ công bố (nếu có).
3. Khảo sát trực tiếp tại chợ (ghi rõ người thu thập, ngày, chợ, sạp) — chỉ khi không có
   hai nguồn trên.
4. Bài báo "giá hôm nay" — chỉ dùng khi bài ghi rõ ngày + chợ/siêu thị cụ thể; không dùng
   bài không ngày hoặc bài tổng hợp nhiều ngày.

### 3.3. Việc cần làm theo thứ tự

1. Chủ dự án chốt: thu thập cho toàn bộ 99 rau + 82 quả, hay chỉ nhóm rau củ/quả phổ biến
   trước (đề xuất: nhóm phổ biến trước vì rau thơm/đặc sản vùng miền khó có giá chuẩn).
2. Tạo bảng `vegetable_prices` theo đúng mẫu `fruit_prices` (region_code, month,
   price_min, price_max, source_name, source_url, collected_at) + seeder + hiển thị
   trên trang rau (kèm ngày thu thập + nguồn, như trang quả).
3. Thu thập theo §3.1–3.2, seed, validate, ghi ROADMAP. Quả thiếu giá (`buoi-chum`,
   `dua-gang`, `mo`, `qua-dieu`) ưu tiên tìm trong cùng đợt.
4. Nếu một mặt hàng vẫn không có nguồn sau đợt thu thập thì giữ trống + ghi rõ
   "chưa thu thập được", không lấp bằng giá mặt hàng tương tự.

## 4. Quy tắc không đổi

- Không bao giờ lấy giá cũ gán sang ngày mới; không bao giờ lấy giá một kênh (chợ hoặc
  siêu thị) trình bày như giá chung; không bao giờ quy đổi mớ→kg khi chưa cân thực tế.
- Mọi dòng giá phải có `source_name` + `source_url` + `collected_at` — thiếu một trong ba
  thì không seed (tương tự V14 cho kcal).
