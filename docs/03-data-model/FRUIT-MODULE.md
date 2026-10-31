# FRUIT-MODULE — Thiết kế nhánh Hoa quả

> Trạng thái: **THIẾT KẾ — chờ chủ dự án chốt** (QT-8) · Soạn 2026-10-31 theo yêu cầu của chủ dự án.
> Nhánh này mở một mảng dữ liệu + sản phẩm mới bên cạnh kho món ăn: hồ sơ hoa quả phục vụ quyết định
> "nên ăn quả gì, vào thời điểm nào" — tối ưu đồng thời **giá** và **dinh dưỡng**.

## 1. Nguyên tắc kiến trúc: lớp hồ sơ phủ lên nguyên liệu, không trùng dữ liệu

Kho hiện có **32 nguyên liệu nhóm `trai-cay`, tất cả đã có số dinh dưỡng có nguồn** (xoài, sầu riêng,
măng cụt, chôm chôm, vải, nhãn, mít, bưởi, cam, quýt, thanh long, dưa hấu, bơ, nho, táo, lê...).

Nhánh hoa quả **không tạo bộ dinh dưỡng thứ hai**. Mỗi hồ sơ quả (`fruit profile`) tham chiếu đúng
một `ingredient slug` để lấy dinh dưỡng + dị ứng (do hệ nhãn hiện có suy ra). Phần mới của nhánh là
lớp thông tin **thị trường — mùa vụ — canh tác — kết hợp thực phẩm** mà tầng nguyên liệu không có.

```
ingredients (dinh dưỡng, dị ứng)  ──1:1──  fruits (hồ sơ quả)
                                              ├─ phân loại, xuất xứ, vùng trồng
                                              ├─ mùa vụ: tháng có hàng, vụ chính
                                              ├─ cửa sổ dinh dưỡng tốt nhất
                                              ├─ cửa sổ giá hời nhất
                                              ├─ lưu ý kết hợp thực phẩm (có mức bằng chứng)
                                              └─ fruit_prices: giá theo miền × tháng (có nguồn)
```

Mọi dữ liệu vẫn là file trong `data/` (QT-3): `data/fruits/<slug>.json` + giá nằm trong cùng file
(khối `prices`), DB chỉ là bản dựng lại.

## 2. Mô hình dữ liệu — `data/fruits/<slug>.json`

| Trường | Nội dung |
|---|---|
| `ingredient_slug` | trỏ sang nguyên liệu (nguồn dinh dưỡng/dị ứng duy nhất) |
| `classification` | `group`: cit-xit/có múi, mong (quả mọng), hach (quả hạch), nhiet-doi, dua (họ dưa), le-thao...; `family` họ thực vật; `origin`: `trong-nuoc` / `nhap-khau` / `ca-hai` |
| `grown_in` | vùng/tỉnh trồng tiêu biểu ở VN (quả nhập: nước xuất xứ chính) — mảng chuỗi có cấu trúc `{region, note}` |
| `availability` | `months`: 1–12 tháng có hàng trên thị trường VN; `main_season_months`: vụ chính; `availability_type`: `theo-mua` / `quanh-nam` / `quanh-nam-nhap-khau` |
| `best_nutrition_window` | `{months, note}` — mùa quả chín tự nhiên tại cây/vụ chính: tươi nhất, ít xử lý bảo quản, dinh dưỡng đạt đỉnh theo bản chất mùa vụ |
| `best_value_window` | `{months, note}` — mùa giá hời nhất (thường trùng/đầu vụ chính khi nguồn cung dồi dào), suy từ dữ liệu giá §3 |
| `pairing_cautions` | mảng `{with, level, note}`: thực phẩm không nên ăn cùng. `level` ∈ `co-so` (có cơ sở: vd tannin trong hồng + đạm/hải sản gây khó tiêu — có tài liệu) / `dan-gian` (kinh nghiệm dân gian, chưa kiểm chứng — hiển thị đúng nhãn, không khẳng định y khoa) |
| `storage_note`, `selection_note` | cách chọn quả ngon + bảo quản (biên tập, ngắn) |
| `prices` | xem §3 |
| `needs_review`, `reviewed_*` | cùng quy ước kho món |

DB (dựng từ file): bảng `fruits` + `fruit_prices` (fruit_id, region_code, month, price_min, price_max,
unit `vnd/kg`, source_name, source_url, collected_at).

## 3. Kỷ luật dữ liệu giá — phần khó nhất của nhánh

Giá hoa quả biến động theo ngày và theo chợ; một con số "giá hôm nay" không có nguồn cấp trực tiếp
sẽ thành số bịa — vi phạm tinh thần QT-1/QT-5. Giai đoạn 1 chốt mô hình sau:

- **Đơn vị hiển thị:** khoảng giá tham khảo (min–max, VND/kg) theo **3 miền** (`bac`, `trung`, `nam`)
  × **tháng** — đủ để trả lời "mùa nào hời" mà không giả vờ chính xác theo ngày.
- **Nguồn thu thập:** bản tin giá nông sản của Sở Công Thương / chợ đầu mối (Thủ Đức, Bình Điền,
  Hóc Môn, Đền Lừ...), báo cáo thị trường của Bộ NN&PTNT, trang giá của hệ thống siêu thị —
  **mỗi dòng giá bắt buộc có `source_name` + `collected_at`**; giá là dữ liệu biên soạn có trích dẫn,
  LLM không tự sinh giá (mở rộng QT-1 cho dữ liệu giá).
- **Hiển thị:** luôn kèm "giá tham khảo mùa vụ, cập nhật <tháng/năm>" + miền. Không hiển thị giá
  như cam kết.
- Nâng cấp sau (ngoài giai đoạn 1): cơ chế cập nhật giá định kỳ bán tự động khi có nguồn ổn định.

## 4. Mùa vụ & hai "cửa sổ" quyết định

- `best_nutrition_window`: theo vụ chín tự nhiên (vd vải: 5–7; nhãn: 7–9; sầu riêng miền Tây: 5–8...).
  Quả trái vụ/quanh năm do nhập khẩu hoặc xử lý ra hoa trái vụ: ghi rõ tính chất trong `note`.
- `best_value_window`: rút từ `prices` — tháng có khoảng giá thấp nhất theo miền; khi 3 miền lệch
  nhau, hiển thị theo miền người dùng chọn.
- Trang chi tiết quả tổng hợp thành **khuyến nghị thời điểm**: "Ngon + bổ nhất: tháng X–Y ·
  Hời nhất: tháng X–Y · Hiện tại (tháng này): đang vào vụ / trái vụ / hàng nhập".

## 5. Module quản trị riêng — `/admin/hoa-qua`

Trong SPA admin hiện có (antd 6 + React 19), thêm mục **Hoa quả** tách riêng khỏi Nguyên liệu/Món ăn:

- Danh sách quả: lọc theo nhóm, xuất xứ, trạng thái duyệt, "đang vào mùa".
- Form hồ sơ: phân loại, vùng trồng, tháng mùa vụ (chọn 12 tháng dạng lưới), 2 cửa sổ, lưu ý
  kết hợp (thêm/xoá dòng + mức bằng chứng), ghi chú chọn/bảo quản.
- **Ma trận giá:** lưới miền × tháng nhập khoảng giá + nguồn + ngày thu thập; cảnh báo khi thiếu
  nguồn hoặc giá lệch bất thường so với tháng kề.
- Quy trình duyệt như kho món (`needs_review`, vai trò admin/editor giữ nguyên).

## 6. Phía người dùng

- `/hoa-qua`: danh sách + lọc (nhóm, xuất xứ, **đang vào mùa tháng này**, **đang hời**, dinh dưỡng
  theo mục tiêu: ít đường/giàu vitamin C... qua số của nguyên liệu).
- `/hoa-qua/{slug}`: dinh dưỡng + dị ứng (từ nguyên liệu), biểu đồ mùa vụ 12 tháng (có hàng /
  vụ chính / ngon nhất / hời nhất), giá theo miền, vùng trồng, lưu ý kết hợp có nhãn mức bằng chứng.
- Khối gợi ý theo tháng ở trang chủ: "Tháng này nên ăn gì" (vào vụ + giá hời) — nối vào engine
  gợi ý ở mức hiển thị, không đổi REC-001 của món ăn.

## 7. Phạm vi giai đoạn 1 — toàn bộ hoa quả lưu hành tại VN (gồm nhập khẩu)

> **Đính chính khi triển khai (2026-10-31):** chủ dự án đã duyệt thiết kế + quy mô toàn bộ.
> Rà soát danh sách theo *loại* quả lưu hành thực tế cho ra **85 hồ sơ** (32 nguyên liệu đã có +
> 53 loại mới) — con số ~110 trong ước lượng ban đầu gồm cả các mục tách theo giống/cultivar;
> các giống đáng chú ý (nho mẫu đơn, táo Envy, sầu riêng Ri6/Monthong...) được ghi trong hồ sơ
> ở giai đoạn 1, tách hồ sơ theo giống để giai đoạn 2. Danh sách chốt:
> `fruit-phase1-list` (lưu trong hồ sơ triển khai FRUIT-002).

Ước lượng ban đầu **~110 loại quả** (theo "loại", không tách giống/cultivar):

- **Đã có nền (32):** chỉ cần viết hồ sơ + giá.
- **Cần bổ sung nguyên liệu + hồ sơ (~78):** trong nước — măng cụt đã có? (chưa: cần thêm) mãng cầu
  xiêm, vú sữa, roi (mận nước), cóc, bình bát, dâu da, bòn bon, dâu tằm, sơ ri, lêkima (trứng gà),
  thị, hồng giòn, đào, mận hậu, táo ta, nho Ninh Thuận (tách khỏi nho nhập nếu cần), dưa lê, dưa
  gang, dừa, chà là tươi, sung, mây (salak), thanh trà, nhót, tầm bóp, mít tố nữ, đu đủ (đã có)...
  nhập khẩu — cherry, việt quất, mâm xôi, kiwi, nho mẫu đơn, nho ngón tay, táo Envy/Fuji (theo loại
  táo nói chung + ghi chú giống trong hồ sơ), lê Hàn, cam Navel, đào phẳng, xuân đào, mận nhập,
  mơ, chà là khô, lựu nhập, dâu tây Hàn/Nhật, hồng giòn nhập, dưa lưới Nhật, bưởi nhập...
  *(Danh sách chốt sẽ nằm trong FRUIT-002, rà soát chéo với thực tế lưu hành trước khi soạn.)*

## 8. Phân rã công việc (task FRUIT-*)

| Task | Nội dung | Phụ thuộc |
|---|---|---|
| FRUIT-001 | Schema + migration + seeder + model + validator cho `data/fruits/` | — |
| FRUIT-002 | Bổ sung ~78 nguyên liệu quả còn thiếu (theo thang nguồn hiện hành) | — |
| FRUIT-003 | Soạn hồ sơ mùa vụ/phân loại/vùng trồng/kết hợp cho ~110 quả | FRUIT-001, 002 |
| FRUIT-004 | Thu thập giá mùa vụ 3 miền × 12 tháng có nguồn cho ~110 quả | FRUIT-003 |
| FRUIT-005 | Module quản trị `/admin/hoa-qua` (hồ sơ + ma trận giá + duyệt) | FRUIT-001 |
| FRUIT-006 | Trang người dùng `/hoa-qua` + chi tiết + khối "tháng này nên ăn gì" | FRUIT-003, 004 |

## 9. Điểm chờ chủ dự án chốt (QT-8)

1. **Kiến trúc lớp hồ sơ phủ lên nguyên liệu** (§1) — khuyến nghị: duyệt (tránh trùng dinh dưỡng).
2. **Mô hình giá giai đoạn 1:** khoảng giá tham khảo theo miền × tháng, có nguồn + ngày thu thập,
   không làm "giá trực tiếp theo ngày" — khuyến nghị: duyệt (khả thi + trung thực); giá theo ngày
   cần nguồn cấp dữ liệu riêng, để giai đoạn sau.
3. **Quy mô giai đoạn 1 ~110 loại** (32 sẵn + ~78 mới) — duyệt quy mô này, hay chốt vòng đầu
   ~60 loại phổ biến nhất rồi mở tiếp?
4. **Lưu ý kết hợp thực phẩm gắn mức bằng chứng** (`co-so` / `dan-gian`) — khuyến nghị: bắt buộc,
   đúng triết lý minh bạch của dự án, tránh khẳng định y khoa vô căn cứ.
