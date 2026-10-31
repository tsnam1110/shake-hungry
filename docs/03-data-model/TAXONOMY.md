# Hệ phân loại và nhãn (Taxonomy) — Lắc Đói

> Mục đích: khi kho món/nguyên liệu lớn lên (hàng trăm → hàng nghìn), người dùng và
> hệ thống phải **lọc chính xác** theo phân loại và nhãn thay vì tìm kiếm văn bản tự do.
> Nguyên tắc thiết kế: **nhãn nào suy ra được từ dữ liệu thì PHẢI suy ra bằng quy tắc**
> (kiểm chứng được 100%), nhãn biên tập thì có định nghĩa viết rõ và áp dụng nhất quán.
> Đăng ký nhãn (nguồn sự thật của từ vựng): `config/taxonomy.php`. Kiểm chứng:
> `lacdoi:validate-data` mục V16.

## 1. Nguyên liệu

Phân loại chính: trường `category` sẵn có (1 giá trị): `thit`, `thuy-san`, `rau-cu`,
`rau-gia-vi`, `trai-cay`, `tinh-bot`, `gia-vi`, `dau-mo`, `khac`.

Nhãn trong trường `tags` của file nguyên liệu (seeder nạp vào `ingredient_tags`):

| Tiền tố | Tên nhóm | Giá trị | Cách gán |
|---|---|---|---|
| `origin:` | Nguồn gốc | `dong-vat`, `thuc-vat`, `nam`, `khoang-chat`, `tong-hop` | Quy tắc theo category + bảng ngoại lệ (§3) — đúng 1 giá trị/nguyên liệu |
| `allergen:` | Chất gây dị ứng | `dau-phong`, `tom`, `cua`, `dong-vat-co-vo`, `ca`, `trung`, `sua`, `dau-nanh`, `lua-mi`, `me`, `mam-tom` | Bảng ánh xạ rà soát tay (§4) — 0..n giá trị |
| `state:` | Trạng thái | `tuoi`, `kho`, `len-men`, `dong-hop`, `che-bien` | Quy tắc theo tên/slug + ngoại lệ — đúng 1 giá trị |

## 2. Món ăn

Phân loại sẵn có: `region_code`, `meal_slots`, `cooking_method`, `is_eat_out`,
`is_home_cook`, món cha/biến thể. Nhãn nạp vào `dish_tags`:

| Tiền tố | Tên nhóm | Giá trị | Cách gán |
|---|---|---|---|
| `loai:` | Loại món (hình thức) | `bun-pho-mi`, `com`, `banh`, `chao-sup`, `canh`, `lau`, `xoi`, `goi-nom`, `che-trang-mieng`, `mon-chinh`, `nuoc-cham` | **Biên tập** theo định nghĩa §5 — đúng 1 giá trị/món |
| `diet:` | Chế độ ăn | `chay` (thuần chay), `chay-trung-sua`, `man` | **Suy từ thành phần** (§6) — đúng 1 giá trị |
| `protein:` | Đạm chính | `thit-bo`, `thit-lon`, `gia-cam`, `ca`, `tom-cua`, `muc`, `oc`, `trung`, `dau-phu-dau`, `hon-hop`, `khong` | **Suy từ thành phần** (§6) — đúng 1 giá trị |
| `allergen:` | Chứa chất gây dị ứng | cùng từ vựng §4 | **Suy từ thành phần**: hợp nhất nhãn dị ứng của mọi nguyên liệu trong món |

## 3. Quy tắc nguồn gốc nguyên liệu

Mặc định theo category: `thit`/`thuy-san` → `dong-vat`; `rau-cu`/`rau-gia-vi`/
`trai-cay` → `thuc-vat`; `dau-mo` → `thuc-vat`; `tinh-bot`/`gia-vi` → `thuc-vat`
TRỪ bảng ngoại lệ (mắm và chế phẩm từ động vật, mì trứng, gia vị tổng hợp...).
Nấm (mộc nhĩ, nấm rơm, nấm hương...) → `nam`. Muối → `khoang-chat`. Sản phẩm công
nghiệp phối trộn nhiều nguồn (bột canh, hạt nêm, mì trứng, bánh phồng tôm...) →
`tong-hop`. Nước tương — kể cả công nghiệp — gốc đậu nành lên men đơn nguồn nên
là `thuc-vat`, không phải `tong-hop`. Bảng ngoại lệ đầy đủ nằm trong `App\Taxonomy\TaggingRules` và được
validate-data đối chiếu: file nào lệch quy tắc + ngoại lệ là lỗi 🔴.

Quy ước `state:` (rút ra từ rà soát độc lập 2026-10-31 — 6/7 lỗi chắc chắn của
đợt rà soát đầu là state bị "mặc định tươi" nuốt mất): slug kết thúc bằng `-kho`
tự động là `kho` (măng khô, mực khô...); trà/cà phê trong kho là dạng NƯỚC PHA
(theo tên gọi và kcal ≈ 1) nên là `che-bien`, không phải hàng khô; cùi dừa nạo
và cốm là sản phẩm tươi ẩm → `tuoi`. **Khi thêm nguyên liệu mới:** nếu tên chứa
"khô / muối / đóng hộp / đã luộc / pha sẵn" thì phải khai `state` tường minh
trong `STATE_BY_SLUG` của `TaggingRules` — đừng để rơi về mặc định.

## 4. Từ vựng dị ứng (khớp nhóm dị ứng trên giao diện Lắc)

`dau-phong` (lạc), `tom` (tôm/tép), `cua`, `dong-vat-co-vo` (nghêu/sò/ốc/hến/mực...
— nhóm vỏ cứng và thân mềm), `ca` (mọi cá + nước mắm + mắm cá), `trung`, `sua`
(sữa và chế phẩm), `dau-nanh` (đậu nành, đậu phụ, tàu hũ, tương đậu nành, nước
tương), `lua-mi` (bột mì, mì, bánh mì, mì căn — gluten), `me` (vừng/mè, dầu mè),
`mam-tom` (nhóm riêng theo thói quen VN: mắm tôm, mắm ruốc, mắm nêm lên men từ
tôm/ruốc). Nguyên liệu lên men từ cá (nước mắm, các loại mắm cá) mang `allergen:ca`;
mắm tôm mang cả `allergen:tom` và `allergen:mam-tom`.

Quy ước chiều an toàn cho **chế phẩm phối trộn** (đã phân xử sau rà soát độc lập
2026-10-31): gắn allergen theo thành phần ĐIỂN HÌNH của nhóm sản phẩm, vì thiếu
nhãn dị ứng là lỗi an toàn còn thừa nhãn chỉ làm hẹp lựa chọn — giò lụa →
`ca` (nước mắm là thành phần chuẩn), xúc xích công nghiệp → `dau-nanh` +
`lua-mi` (đạm đậu nành và bột mì làm chất kết dính/độn điển hình trên nhãn),
chả cá viên thương mại → thêm `lua-mi`, pâté gan → `sua` + `lua-mi` (bánh mì
ngâm sữa kiểu Pháp–Việt), tương đen (hoisin) → thêm `lua-mi`. Ngược lại, thành
phần chỉ xuất hiện ở MỘT SỐ biến thể thì không gắn: lạp xưởng (nước tương tuỳ
công thức), bột canh (bột tôm chỉ ở biến thể bột canh tôm).
`lua-mi` hiểu theo nghĩa gluten: bia (gluten lúa mạch) cũng mang nhãn này.
Rươi gây dị ứng theo kinh nghiệm dân gian nhưng là giun nhiều tơ (annelid) —
không thuộc 9 nhóm trên, nên KHÔNG gắn nhãn; ghi nhận tại đây để khỏi tranh luận lại.

## 5. Định nghĩa loại món (biên tập)

- `bun-pho-mi`: món nước hoặc trộn mà thành phần trung tâm là sợi bún/phở/mì/hủ tiếu/bánh canh/bánh đa (bún bò, phở, mì Quảng, hủ tiếu, bánh canh...).
- `com`: trung tâm là cơm hạt rời (cơm tấm, cơm rang, cơm chiên, cơm niêu...) — xôi không tính vào đây.
- `xoi`: trung tâm là gạo nếp đồ/hấp (xôi gấc, xôi xéo, xôi mặn...).
- `banh`: bánh từ bột — chiên/hấp/nướng/tráng (bánh xèo, bánh cuốn, bánh bèo, bánh pía, bánh mì kẹp...).
- `chao-sup`: cháo và súp sánh lỏng ăn bằng muỗng, không sợi (cháo gà, súp cua...).
- `canh`: canh nước trong ăn kèm cơm (canh chua, canh khổ qua...) — món nước có sợi thì thuộc `bun-pho-mi`.
- `lau`: ăn chung nồi lẩu tại bàn (lẩu mắm, lẩu cá kèo...).
- `goi-nom`: gỏi/nộm trộn chua ngọt, không qua lửa hoặc thành phần chín trộn lạnh.
- `che-trang-mieng`: chè và món tráng miệng ngọt (chè, sương sáo, tàu phớ, bánh ngọt tráng miệng...). Bánh ăn chính (bánh xèo...) ở `banh`; bánh ngọt tráng miệng ở đây.
- `nuoc-cham`: nước chấm/mắm pha dùng kèm món khác, khẩu phần nhỏ (nước mắm pha, mắm nêm pha sẵn, muối tôm...).
- `mon-chinh`: còn lại — món mặn ăn với cơm (kho, rim, nướng, hấp, xào, luộc, chiên...) không thuộc các nhóm trên.

Khi phân vân: ưu tiên **thành phần trung tâm theo khối lượng và vai trò trong bữa ăn**,
không theo tên gọi vùng miền. Các quy ước hoà giải đã áp thống nhất trên toàn kho
(phân xử 2026-10-31 sau 4 lượt phân loại độc lập):

- **Ngọt:** món ngọt ăn bằng muỗng kèm nước/sánh lỏng (chè, cháo ngọt, thạch,
  tào phớ, flan, sủi dìn/bánh trôi chan nước đường) → `che-trang-mieng`; bánh
  ngọt định hình khối/miếng ăn cầm tay (bánh bò, bánh da lợn, chuối chiên,
  chuối nếp nướng) → `banh`. Cốm ăn vặt kèm chuối/dừa → `che-trang-mieng`.
- **Cuốn:** cuốn bánh tráng ăn lạnh kiểu salad (gỏi cuốn, bò bía, bì cuốn) →
  `goi-nom`; cuốn bằng lá phở (phở cuốn) → `bun-pho-mi`; bánh cuốn/bánh ướt
  tráng bột ăn nóng → `banh`; nem rán (vỏ định hình món như bánh gối) → `banh`.
- **Mắm:** mắm/nước chấm dùng kèm khẩu phần nhỏ (kể cả mắm nguyên chất chưa pha:
  mắm tép, mắm tôm chua, mắm cá lóc...) → `nuoc-cham`; mắm nguyên con ăn trực
  tiếp với cơm như món đạm (mắm ba khía, mắm chua) hoặc mắm đã nấu thành món
  (mắm kho, mắm chưng, mắm rươi kho) → `mon-chinh`. Kho quẹt — dù có thịt/tôm —
  chức năng là đồ chấm rau nên → `nuoc-cham`.
- **Nếp:** khối nếp đồ/hấp còn nguyên hạt là trung tâm (xôi, bánh chưng, bánh
  tét, bánh khúc, cơm lam) → `xoi`; nếp đã giã/xay thành bột dẻo định hình →
  xét theo dạng ăn (bánh giày → `banh`).
- **Bán thành phẩm / sản phẩm khô:** phân loại theo bản chất — sợi khô (bánh đa
  đỏ, bánh phở khô, mì thanh long) → `bun-pho-mi`; tờ bột (bánh tráng) → `banh`;
  đạm khô (khô cá lóc, khô nhái) → `mon-chinh`.
- **Ăn kèm không kéo loại:** món đạm trung tâm ăn kèm bún/bánh mì vẫn là
  `mon-chinh` (chả cá Lã Vọng, bò kho); nồi om đạm là trung tâm, nước không
  trong (ốc nấu chuối đậu, giả cầy) → `mon-chinh`, không phải `canh`.

## 6. Quy tắc suy nhãn món từ thành phần

Thực thi tại `App\Taxonomy\DishTagDeriver`, dùng ở seeder, validate-data (V16) và test:

- `diet:` — xét nhãn `origin:` của mọi nguyên liệu KHÔNG tuỳ chọn (optional vẫn tính
  nếu là thành phần đặc trưng? Không: chỉ xét nguyên liệu bắt buộc + nguyên liệu
  tuỳ chọn có mặt trong công thức chuẩn — đơn giản và an toàn: xét TẤT CẢ dòng
  nguyên liệu của món):
  - Có nguyên liệu `origin:dong-vat` thuộc nhóm thịt/hải sản/trứng/sữa (qua category
    `thit`/`thuy-san` hoặc nhãn `allergen:trung`/`allergen:sua`) hoặc nước mắm/mắm
    các loại ⇒ `man`. Cụ thể: bất kỳ nguyên liệu nào có `origin:dong-vat` đều làm
    món thành `man`, NGOẠI TRỪ trứng và sữa (xem dòng dưới).
  - Không có thịt/hải sản/mắm nhưng có trứng hoặc sữa ⇒ `chay-trung-sua`.
  - Không có bất kỳ nguyên liệu `origin:dong-vat` nào ⇒ `chay`.
  - Nguyên liệu `origin:tong-hop`: xét thành phần gốc theo bảng
    `COMPOSITE_ANIMAL` trong `TaggingRules` — bột canh/hạt nêm/bánh phồng tôm
    tính như động vật mức `man`; mì trứng/mayonnaise tính mức `trung-sua`;
    mì chính/cola không tính động vật.
- `protein:` — nguyên liệu đạm có khối lượng lớn nhất trong nhóm: thịt bò/gia cầm/
  lợn (theo slug/category), cá, tôm-cua, mực, ốc, trứng, đậu phụ/đậu (thực vật).
  Hai nhóm đạm động vật cùng ≥ 25% tổng đạm ⇒ `hon-hop`. Không có nhóm nào ≥ 15g
  đạm thô/khẩu phần ⇒ `khong`.
- `allergen:` — hợp nhất (union) nhãn `allergen:` của mọi nguyên liệu trong món.
  Món còn nguyên liệu chưa có dữ liệu thành phần đầy đủ vẫn suy từ các dòng hiện có
  (cờ dị ứng là "có chứa", không phải "an toàn tuyệt đối" — giao diện lọc dị ứng của
  nút Lắc giữ cơ chế lọc cứng riêng theo slug như cũ).

## 7. Lọc và tìm kiếm (API công khai)

- `GET /api/dishes?tag=loai:com&tag=diet:chay` — giao (AND) giữa các facet, hợp
  (OR) trong cùng facet: `tag=protein:ca,tom-cua`, và viết gộp một tham số cũng
  tương đương: `tag=loai:com,diet:chay`. (PHP nuốt tham số lặp nên máy chủ tự
  gom mọi `tag=` từ query string gốc — `App\Taxonomy\TagFilter`.)
- `GET /api/dishes?allergen_free=ca,dau-phong` — loại mọi món chứa các chất đó.
- `GET /api/ingredients?category=thuy-san&tag=state:kho` — lọc nguyên liệu.
- `GET /api/tags` — toàn bộ từ vựng + số lượng món/nguyên liệu theo từng nhãn
  (nguồn cho chip lọc trên giao diện).
