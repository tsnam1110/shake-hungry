# Quy trình sản xuất dữ liệu — Lắc Đói

**Cập nhật lần cuối:** 2026-10-31
**Trạng thái:** ✅ Quy trình đang vận hành thật — công cụ nhập liệu (admin antd) đã xong
(ADM-001…005), các lệnh `lacdoi:*` chạy trong quy trình hằng ngày.

Đây là tài liệu quan trọng nhất về vận hành dự án. Nếu quy trình này chậm hoặc mệt, dự án sẽ chết ở
giai đoạn sản xuất dữ liệu — đó là cách các dự án cùng loại chết.

---

## 1. Thứ tự sản xuất — không được đảo

```
  1. Bảng tham chiếu        → nhập máy, một lần (USDA, Wikidata)
           ↓
  2. Nguyên liệu (~250)     → biên soạn tay, TỐN CÔNG NHẤT, ĐÒN BẨY LỚN NHẤT
           ↓
  3. Engine tính calo       → code, test bằng dữ liệu FNDDS + Nutrition5k
           ↓
  4. Công cụ nhập liệu      → code, nghiệm thu bằng chỉ số ≤5 phút/món
           ↓
  5. Món ăn (150 → 500)     → biên soạn hàng loạt, nhanh vì 1–4 đã xong
           ↓
  6. Nút Lắc                → ~60–200 dòng
```

**Vì sao nguyên liệu trước món:** khoảng 250 nguyên liệu phủ gần hết món Việt thường ngày. Làm sạch
250 nguyên liệu đó — mỗi cái có kcal/100g, quy đổi đơn vị, hệ số hao hụt, hệ số hấp thụ dầu — thì
**mọi món sau đó tự tính được calo**. Món thứ 400 gần như không tốn công thêm.

Nếu làm ngược (nhập theo món, mỗi món tra dinh dưỡng riêng), bạn sẽ làm lại cùng một việc 400 lần và
số liệu sẽ mâu thuẫn nhau. Đây là sai lầm được ghi ở [CLAUDE.md §7](../../CLAUDE.md).

---

## 2. Vai trò của LLM — ranh giới cứng

> **Nguyên tắc: LLM sinh ra ánh xạ và định lượng. LLM không bao giờ sinh ra con số dinh dưỡng.**

### LLM được làm

| # | Việc | Đầu ra | Kiểm chứng thế nào |
|---|---|---|---|
| 1 | **Ánh xạ nguyên liệu**: "thịt ba chỉ" → dòng nào trong `reference_foods`? | Top-3 ứng viên kèm `external_id` và lý do | Người biên soạn nhìn tên + kcal/100g, chọn hoặc bác |
| 2 | **Đề xuất danh sách nguyên liệu** cho một món | Danh sách nguyên liệu + gram ước lượng | Người biên soạn đối chiếu với hiểu biết thực tế |
| 3 | **Định lượng**: "một ít nước mắm" → bao nhiêu gram | Khoảng min–max | Người biên soạn duyệt |
| 4 | **Chuẩn hoá tên**: gộp "phở bò tái" / "Phở Bò Tái" về một món chuẩn | Đề xuất gộp | Người biên soạn quyết |
| 5 | **Dịch** tên món sang tiếng Anh | Bản dịch | Người biên soạn duyệt |

### LLM bị cấm

| Việc | Vì sao cấm |
|---|---|
| Sinh ra giá trị kcal, protein, lipid, glucid | Không truy nguyên được, không kiểm chứng được, và **phá thẳng mục tiêu chính của dự án** |
| Sinh ra hệ số hao hụt / hấp thụ dầu | Phải tra bảng USDA có nguồn |
| Điền vào chỗ dữ liệu thiếu | Thiếu thì để thiếu và hiển thị "chưa đủ dữ liệu" |
| Chọn một con số khi các nguồn mâu thuẫn mà không ghi rõ | Xem **QT-9** (CLAUDE.md §3): thông tin không rõ ràng thì kèm nhãn lưu ý, không có thông tin thì bỏ trống, tuyệt đối không bịa; biên soạn từ nhiều nguồn; thời gian là thứ yếu so với độ chính xác |
| Tự động duyệt bản ghi của chính nó | Vòng lặp không có kiểm soát |

### Chốt chặn kỹ thuật

Không dựa vào kỷ luật con người. Cài ba chốt:

1. **Ràng buộc CSDL R1:** `CHECK (dish_nutrition.source <> 'llm_suggested')` — LLM không thể ghi vào
   bảng dinh dưỡng, kể cả khi code có lỗi.
2. **Mọi đề xuất của LLM** mang `source='llm_suggested'` và `needs_review=true` khi tạo.
3. **Test tự động** quét toàn bộ `dish_nutrition` tìm bản ghi có nguồn LLM — chạy trong CI. Task
   [TEST-001](../05-tasks/tasks/TEST-001.md).

### Vì sao thiết kế này thoả mãn yêu cầu "không được đoán bừa"

Yêu cầu gốc của chủ dự án: *"thông tin mà LLM tạo ra phải dựa trên thông tin nào đó chứ không được
đoán bừa, tạo dữ liệu sai."*

Hiện thực: LLM chỉ **trỏ tới** dữ liệu đã có (ánh xạ sang một dòng cụ thể trong bảng tham chiếu), và
chỉ **ước lượng khối lượng** — thứ có thể kiểm chứng bằng mắt thường. Con số dinh dưỡng cuối cùng do
một hàm cộng lại từ các dòng dữ liệu có nguồn. Kết quả **truy vết được tới từng dòng gốc**, và có thể
tính lại bất cứ lúc nào.

---

## 3. Quy trình nhập một món — từng bước

### Bước 1 — Xác định món chuẩn hay biến thể

Gõ tên món. Hệ thống dùng độ giống trigram + tìm kiếm không dấu (collation tiếng Việt) để tìm món gần giống **ngay lúc gõ**.

- Trùng hoàn toàn → sửa món cũ, không tạo mới.
- Gần giống → quyết định: biến thể (`parent_dish_id`) hay món độc lập? Quy tắc:
  [EDITORIAL-GUIDE §3](../09-standards/EDITORIAL-GUIDE.md).
- Không giống gì → món mới.

> Bước này phải nằm **trước** mọi bước khác. Phát hiện trùng sau khi đã nhập xong công thức là mất
> trắng 5 phút.

### Bước 2 — Điền Mức 1

Quốc gia, vùng miền, bữa phù hợp (có trọng số), ăn ngoài / tự nấu, cách chế biến, ảnh.

**Món ăn ngoài có thể dừng tại đây.** Điền `estimated_kcal` dạng khoảng kèm `source_note` bắt buộc,
rồi lưu. Xong món.

### Bước 3 — Nguyên liệu (chỉ món tự nấu, Mức 2)

1. Bấm "Đề xuất nguyên liệu" → LLM trả danh sách + gram, mọi dòng hiện màu cảnh báo `chưa duyệt`.
2. Người biên soạn duyệt từng dòng bằng phím: `Enter` nhận, `Del` bỏ, số để sửa gram.
3. Nguyên liệu chưa có trong `ingredients` → mở luồng tạo nguyên liệu mới (Bước 3b).
4. Calo hiển thị **cập nhật trực tiếp** bên cạnh khi duyệt từng dòng — người biên soạn thấy ngay
   con số vô lý.

### Bước 3b — Tạo nguyên liệu mới

1. LLM đề xuất 3 dòng ứng viên trong `reference_foods`, kèm tên và kcal/100g.
2. Người biên soạn chọn, hoặc chọn "không có dòng nào phù hợp".
3. Nếu không có dòng nào phù hợp → `reference_food_id = null`. **Đây là kết quả hợp lệ**, không phải
   lỗi. Món chứa nguyên liệu này sẽ có `missing_ingredients` khác rỗng và không được `confidence=high`.
4. `mapping_confidence` khác `high` ⇒ **bắt buộc** viết `mapping_note`.
5. Nhập quy đổi đơn vị đời thường (muỗng canh, chén, quả…).

### Bước 4 — Tính calo

Bấm "Tính". Engine chạy, hiển thị:

```
≈ 420 – 520 kcal / khẩu phần   ·   tin cậy: trung bình
Đạm 24 g · Béo 12 g · Bột đường 58 g
⚠ Thiếu dữ liệu: bánh phở (chưa ánh xạ)
Nguồn bảng thành phần: usda_sr_legacy
```

Nếu `missing_ingredients` khác rỗng, hệ thống **không cho** đặt `confidence = high`.

### Bước 5 — Lưu và export

Lưu vào DB → cuối phiên chạy `php artisan lacdoi:export-data` → commit file trong `data/`.

**Chưa export thì coi như chưa tồn tại** (QT-3, [DATA-REPOSITORY §6](DATA-REPOSITORY.md)).

---

## 4. Yêu cầu với công cụ nhập liệu (GĐ2)

Đây là đặc tả nghiệm thu, không phải mong muốn.

| # | Yêu cầu | Đo thế nào |
|---|---|---|
| Y1 | Nhập trọn một món Mức 2 trong **≤5 phút** | Bấm giờ 10 món thật, lấy trung vị |
| Y2 | Nhập món Mức 1 trong **≤90 giây** | như trên |
| Y3 | Thao tác được **hoàn toàn bằng bàn phím**, không cần chuột | Thử nhập 1 món không chạm chuột |
| Y4 | Cảnh báo trùng hiện ra **trong khi gõ**, không phải sau khi lưu | Gõ "bún bò huế" khi đã có món đó |
| Y5 | Calo tính lại **ngay** khi sửa một dòng nguyên liệu | Sửa gram, thấy số đổi |
| Y6 | Đề xuất LLM trả về trong **≤5 giây** | Đo |
| Y7 | Mọi đề xuất LLM phân biệt trực quan với dữ liệu đã duyệt | Nhìn thấy được |
| Y8 | Không mất dữ liệu khi mất mạng giữa chừng | Rút mạng lúc đang nhập |

> **Y1 là cổng chặn giữa GĐ2 và GĐ3.** Chưa đạt thì không được bắt đầu sản xuất hàng loạt. Chênh lệch
> giữa 5 phút và 15 phút mỗi món, nhân với 500 món, là chênh lệch giữa **40 giờ và 125 giờ** — tức là
> giữa một dự án hoàn thành và một dự án bỏ dở.

---

## 5. Kiểm soát chất lượng

### Tự động — `php artisan lacdoi:validate-data`

Chạy trước mỗi commit dữ liệu, và trong CI:

| # | Kiểm tra | Mức |
|---|---|---|
| V1 | Không có bản ghi `dish_nutrition` nào có `source='llm_suggested'` | 🔴 Lỗi |
| V2 | Mọi ảnh trong `data/media/` có mục trong MANIFEST kèm license | 🔴 Lỗi |
| V3 | `mapping_confidence` ≠ `high` mà thiếu `mapping_note` | 🔴 Lỗi |
| V4 | `completeness_level ≥ 2` mà không có nguyên liệu nào | 🔴 Lỗi |
| V5 | `kcal_min > kcal_max` | 🔴 Lỗi |
| V6 | Món có `missing_ingredients` mà `confidence='high'` | 🔴 Lỗi |
| V7 | Trùng slug, hoặc hai món có tên giống nhau >90% theo trigram | 🟡 Cảnh báo |
| V8 | Nguyên liệu không được món nào dùng | 🟡 Cảnh báo |
| V9 | Món có kcal/khẩu phần ngoài khoảng 50–2000 | 🟡 Cảnh báo — thường là sai đơn vị |
| V10 | Nguyên liệu có kcal/100g > 900 | 🟡 Cảnh báo — chỉ dầu mỡ nguyên chất mới vượt |
| V11 | `ingredient_slug` trong file món không tồn tại trong `data/ingredients/` | 🔴 Lỗi — bắt slug lỗi thời sau khi nguyên liệu bị tách/đổi tên |
| V12 | Món `completeness_level >= 2` mà có <3 `recipe_sources` | 🔴 Lỗi (ràng buộc R8) |
| V13 | Nguyên liệu có `density_g_per_ml` mà thiếu `density_note` | 🔴 Lỗi — phải ghi rõ đo hay suy ra |
| V14 | Giá trị dinh dưỡng có mà thiếu `source_url` | 🔴 Lỗi — mọi con số phải truy nguyên được |
| V15 | `needs_review = false` mà thiếu `reviewed_by` / `reviewed_at`, hoặc chỉ có một trong hai, hoặc `reviewed_at` sai dạng `YYYY-MM-DD` | 🔴 Lỗi (ràng buộc R10) — duyệt không có bằng chứng thì không phải duyệt |
| V16 | Nhãn trong file nguyên liệu/món khác nhãn suy ra từ `TaggingRules`/`DishTagDeriver` | 🔴 Lỗi — nhãn là suy ra, không gán tay (TAXONOMY.md) |
| V17 | Hồ sơ hoa quả `data/fruits/` sai cấu trúc (slug/nhóm/nguồn gốc, cửa sổ không nằm trong tháng có hàng, dòng giá thiếu nguồn/ngày thu thập) | 🔴 Lỗi (FRUIT-MODULE.md) |
| V18 | Tên gọi khác (ALIAS-001): alias trùng tên chính, trùng nhau trong cùng món/nguyên liệu, alias món đồng thời là tên chính, hoặc thiếu tên | 🔴 Lỗi |

### Thủ công — rà soát định kỳ

Cứ mỗi **50 món mới**, dừng lại và:

1. Rút ngẫu nhiên 5 món, kiểm tra calo bằng tay theo [CALORIE-ENGINE](../06-nutrition/CALORIE-ENGINE.md).
2. Xem lại danh sách cảnh báo 🟡 tồn đọng.
3. Ghi kết quả vào [ROADMAP](../05-tasks/ROADMAP.md) mục "Nhật ký chất lượng dữ liệu".

Không bỏ qua bước này để chạy cho nhanh. Sai sót có hệ thống (ví dụ nhầm đơn vị nguyên liệu hay dùng)
lan ra hàng trăm món trước khi ai đó nhận ra.

---

### Quy ước họ nguồn tính cổng Mức 2 (PX-25 → QD-06, chủ dự án chốt 2026-11-02)

Cổng Mức 2 (V12/R8: ≥3 `recipe_sources`) được đếm theo quy ước sau — áp cho mọi đợt
nâng mức và rà soát từ 2026-11-02:

1. **Nguồn độc lập tính theo BÀI viết** — mỗi bài là một nguồn; không gộp bài khác nhau
   thành một chỉ vì cùng trang.
2. **Mỗi họ nguồn tối đa 1 trong 3 nguồn tính cổng.** "Họ" gồm: (a) cùng domain/nhà
   xuất bản (ví dụ hai bài Điện Máy XANH khác nhau vẫn là một họ `dienmayxanh.com`);
   (b) cùng họ kỹ thuật — nguồn khác món/khác loài chỉ ủng hộ kỹ thuật chế biến (nhóm
   PX-18…PX-24 của AUDIT-002). Hệ quả: một món đạt cổng phải có nguồn thuộc **ít nhất
   3 họ độc lập**; bài thứ hai cùng họ vẫn giữ trong `recipe_sources` làm dữ kiện đối
   chiếu nhưng **không tính** vào 3 nguồn cổng.
3. **Công khai trong hồ sơ món:** mọi phân xử cổng (đạt / rớt theo quy ước này) ghi vào
   `import_note` của file món kèm danh sách họ đã đếm.

Đợt áp dụng đầu tiên (QD-ADJUDICATE-1, 2026-11-02): đếm lại 23 món có ≥2 bài Điện Máy
XANH trong `recipe_sources` — 3 món vẫn đủ 3 họ (giữ mức), 11 món Mức 3 hạ xuống
"Mức 2 (có note)" qua đường nâng bù, 9 món Mức 2 hạ Mức 1 (không đủ điều kiện nâng
bù). Chi tiết từng món: [QD-ADJUDICATE-1](../05-tasks/tasks/QD-ADJUDICATE-1.md).

## 6. Đóng góp từ người dùng (GĐ6 — kênh phụ)

Ở dự án này đóng góp cộng đồng là **phụ**, không phải nguồn dữ liệu chính. Vì vậy giữ đơn giản, không
xây hệ thống wiki đầy đủ:

- Một form gửi đề xuất → ghi vào bảng `contributions` với `status='pending'`.
- Admin duyệt trong trang quản trị antd: xem, sửa, chấp nhận hoặc từ chối kèm lý do.
- Khi chấp nhận → ghi sang `dishes`/`ingredients` với `source='curated'` (không phải `community`) —
  vì đã qua tay người biên soạn thì nó chịu cùng chuẩn chất lượng.
- **Không** có auto-approve, **không** có cấp bậc uy tín, **không** có lịch sử phiên bản kiểu wiki ở
  v1. Nếu sau này lượng đóng góp lớn tới mức duyệt tay không xuể, hãy mở lại thiết kế — nhưng đừng
  xây trước khi có vấn đề đó.

> Đóng góp về **địa điểm quán ăn** cũng đi qua đúng luồng này (`type='new_place'`). Đây là cách dự
> án có dữ liệu quán mà không phải ingest OSM — xem [DATA-SOURCES §5](DATA-SOURCES.md).

---

## 7. Quy trình tra cứu dinh dưỡng cho nguyên liệu Việt Nam

**Thêm 2026-08-31**, sau khi chốt hướng **tự tra cứu thay vì xin phép Viện Dinh dưỡng**
([LEGAL-001](../05-tasks/tasks/LEGAL-001.md)).

### 7.1 Ranh giới pháp lý

| Được làm | Không được làm |
|---|---|
| Tra từng nguyên liệu từ nhiều nguồn công khai, ghi nguồn cho **từng con số** | Số hoá hay chép có hệ thống **bảng của Viện Dinh dưỡng** (kể cả từ PDF trôi nổi) |
| Đọc nhãn dinh dưỡng sản phẩm thương mại và ghi lại kèm tên thương hiệu | Chép lại **cấu trúc, thứ tự, cách phân nhóm** của một bảng có bản quyền |
| Tự đo, tự cân, ghi lại phương pháp | Lấy số từ nguồn không ghi được xuất xứ |

**Lý do:** giá trị dinh dưỡng là **dữ kiện**, không phải tác phẩm. Cái được bảo hộ là **bảng biên
soạn** — sự lựa chọn, sắp xếp và tập hợp. Biên soạn độc lập từ nhiều nguồn, mỗi con số có xuất xứ
riêng, là hoạt động hợp lệ và cho ra một cơ sở dữ liệu mà dự án **thực sự sở hữu**.

### 7.2 Thứ tự nguồn — dùng nguồn cao nhất tra được

| # | Nguồn | `fct_source` | Độ tin cậy ánh xạ |
|---|---|---|---|
| 1 | **USDA FoodData Central** — SR Legacy / Foundation | `usda_sr_legacy`, `usda_foundation` | `high` nếu dòng khớp chính xác |
| 2 | **ANSES-Ciqual** (Pháp, Licence Ouverte) | `ciqual` | `high`–`medium` |
| 3 | **Canadian Nutrient File** (OGL-Canada) | `cnf` | `high`–`medium` |
| 4 | **Nhãn dinh dưỡng sản phẩm thương mại** bán tại VN — bắt buộc ghi **tên thương hiệu + URL** | `nhan_san_pham` | `medium` |
| 5 | **Tự đo / tự cân** — ghi rõ phương pháp, ngày, dụng cụ | `tu_do` | `medium` |
| 6 | **Nguồn THỨ CẤP — không ưu tiên** (xem ghi chú ngay dưới): Bảng TPTP Việt Nam (Viện Dinh dưỡng), bảng FCT nước ngoài khác (MEXT Nhật, IFCT Ấn Độ, Thai FCT, FNRI), bài báo/tài liệu khoa học có phân tích thành phần | `vn_nin`, `mext_jp`, `ifct_in`, `thai_fct`, `fnri_ph`, `literature`, `tbca_br`, `tkpi_id` | `low` — và bị đánh dấu **chưa kiểm định** |
| 6b | **Proxy mức thấp đã duyệt** (chủ dự án, 2026-11-03): số mượn mà mã nguồn thật không thuộc tầng thứ cấp (nhãn thương mại qua Open Food Facts, trang dẫn lại USDA, mục USDA chung cho cả nhóm loài) — `mapping_note` phải ghi rõ proxy từ đâu và còn thiếu gì | `proxy` | `low` — và bị đánh dấu **chưa kiểm định** (`App\Nutrition\SourceTier`) |
| 7 | Không tra được | `null` | — |

> ⚠️ **Nguồn 4 và 5 là ngoại lệ có kiểm soát của QT-4.** Chúng phá nguyên tắc "một bảng thành phần
> duy nhất", nên chỉ dùng cho **nguyên liệu đặc thù Việt Nam mà các bảng ở mục 1–3 không phủ** (nước
> mắm, mắm tôm, bánh phở, giò lụa, cá lóc…). Món dùng chúng phải hạ `confidence`, và
> `dish_nutrition.fct_source` ghi là `mixed` kèm danh sách nguồn đã dùng.

> ⚠️ **Tầng 6 — nguồn thứ cấp (quyết định của chủ dự án, 2026-10-31).** Các nguồn ở §2.2 của
> [LICENSE-COMPLIANCE](LICENSE-COMPLIANCE.md) và Bảng TPTP Việt Nam **được mở lại ở chế độ
> không ưu tiên**: chỉ được tra **từng nguyên liệu một** cho nguyên liệu mà tầng 1–5 đã xác nhận
> không có, mỗi con số ghi rõ bảng gốc + trang đã mở trong `mapping_note`. Ba ranh giới KHÔNG đổi:
> (1) vẫn **cấm nhập/chép hàng loạt** bất kỳ bảng hay dataset nào (kể cả Bảng TPTP VN và các
> dataset ở §2.1–2.2) — đây là tra cứu từng mục có trích dẫn, không phải sao chép có hệ thống;
> (2) số thứ cấp **không bao giờ thay thế** số đã có từ tầng cao hơn; (3) mọi nguyên liệu thuộc
> tầng này bị hệ thống đánh dấu **"chưa kiểm định"** (`App\Nutrition\SourceTier` là nơi định
> nghĩa duy nhất): `dish_nutrition.unverified_ingredients` ghi lại và giao diện công khai hiển
> thị cảnh báo trên món dùng chúng, cho đến khi được thay bằng nguồn chính thống.

### 7.2a Hai quy ước đã chốt (chủ dự án, 2026-11-03) — không hỏi lại

1. **Một dòng bảng tham chiếu dùng chung cho nhiều nguyên liệu là ĐƯỢC PHÉP.**
   Một mục USDA/FCT là một *thực phẩm tham chiếu*, không phải một *nguyên liệu kho*:
   cùng một dòng có thể là ánh xạ tốt nhất hiện có cho nhiều nguyên liệu Việt
   khác nhau khi chúng chưa có dòng riêng đạt chuẩn, miễn mỗi nguyên liệu ghi
   rõ trong `mapping_note` vì sao dòng đó được chọn và còn thiếu gì. Ví dụ thật
   trong kho: USDA 169742 (bún gạo khô) → 4 nguyên liệu bún khô; USDA 168914
   → 6 nguyên liệu bún tươi (trong đó `bun-tuoi` dùng trong 63 món, chênh
   ~20% độ ẩm so với Ciqual — ghi rõ, không giấu); USDA 175179 (tôm chung)
   → `tep`, `tom-su`, `tom-the`, `tom-cang-xanh`; USDA 174204 (cua xanh)
   → `cua-bien`, `ghe`. Dùng chung không có nghĩa là đồng nhất: khi một trong
   các nguyên liệu đó có dòng riêng tốt hơn, chỉ thay riêng nó, không đổi cả
   cụm. Đây không phải vi phạm QT-4: mỗi phép tính vẫn dùng đúng một bảng
   tham chiếu cho mỗi nguyên liệu.
2. **Nguyên liệu không món nào dùng vẫn phải giữ — đó là thư viện nguyên liệu.**
   Validator V8 báo 102 nguyên liệu chưa được món nào dùng (trong đó 68 là
   nguyên liệu của module hoa quả, 34 còn lại thực sự chưa dùng ở đâu) — đây
   là **trạng thái kỳ vọng, không phải lỗi cần dọn**. Kho nguyên liệu là thư
   viện để tra cứu và để biên soạn món sau này (ví dụ `bach-tuoc`, `ca-hoi`,
   `heo-quay`, `cha-lua-chay`, `ca-dia`, `trung-vit-lon`, `nuoc-mia`,
   `tra-xanh`, `sua-chua` đều không có món nào dùng tại thời điểm chốt nhưng
   vẫn là nguyên liệu thật, có hồ sơ). Không xoá nguyên liệu chỉ vì V8 báo
   chưa dùng; chỉ xoá khi bản thân hồ sơ là vỏ rỗng sai định danh (tiền lệ
   duy nhất đã duyệt: `mam-ruot-an-giang`, xem ROADMAP 2026-11-03).

### 7.3 Quy tắc bắt buộc khi tra cứu

1. **Không con số nào được viết ra từ trí nhớ.** Áp dụng cho cả người lẫn AI agent. Mỗi giá trị phải
   kèm `external_id` (ví dụ `fdc_id`) hoặc URL cụ thể đã truy cập.
2. **Tra không ra thì để `null`.** Đây là kết quả hợp lệ, không phải thất bại. Nguyên liệu `null` làm
   món chứa nó không đạt `confidence = high` — đó là hành vi đúng (QT-1).
3. **Chú ý phần thịt và độ mỡ.** "Thịt lợn" trong USDA có hàng chục dòng, chênh nhau tới ba lần calo.
   Đây được ghi nhận là **nguồn sai số lớn nhất** trong tính toán công thức. Chọn sai một dòng ở đây
   là sai cả trăm món về sau.
4. **Nguyên liệu Việt trùng tên tiếng Anh chưa chắc trùng loài.** USDA có "water spinach" nhưng phải
   kiểm có đúng rau muống không. Không chắc thì hạ `confidence` và ghi rõ trong `mapping_note`.
5. **`mapping_note` viết cho người đọc trong tương lai**, không phải cho mình hôm nay. Mẫu tốt và
   mẫu tệ: [EDITORIAL-GUIDE §7](../09-standards/EDITORIAL-GUIDE.md).
6. **Nguyên liệu quan trọng nên tra chéo ít nhất hai nguồn.** Lệch >20% giữa hai nguồn ⇒ ghi lại cả
   hai trong `mapping_note` và hạ `confidence`.

### 7.4 Thứ tự làm việc

Vẫn giữ nguyên: **nguyên liệu trước, món sau**. Việc chuyển sang tự tra cứu **không** đổi thứ tự này
— nó chỉ làm tầng nguyên liệu tốn công hơn, và vì thế đòn bẩy của việc làm nguyên liệu trước càng
lớn: mỗi nguyên liệu tra một lần, dùng cho mọi món về sau.
