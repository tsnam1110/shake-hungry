# Quy tắc tra cứu dữ liệu — Lắc Đói

**Cập nhật lần cuối:** 2026-08-31 — đúc kết từ 10 mẻ tra cứu, 135 nguyên liệu ([ING-001](../05-tasks/tasks/ING-001.md), [ING-002](../05-tasks/tasks/ING-002.md))
**Áp dụng cho:** người biên soạn **và** mọi AI agent được giao việc tra cứu

> Tài liệu này trả lời: *tra ở đâu, tra thế nào, cái gì được ghi, cái gì phải bỏ.*
> Nó là quy trình vận hành. Phần **lý do** và ranh giới pháp lý nằm ở
> [DATA-PIPELINE §7](../04-data-sources/DATA-PIPELINE.md); phần **bẫy về loài và đơn vị** nằm ở
> [EDITORIAL-GUIDE §9](EDITORIAL-GUIDE.md).

Phạm vi: **nguyên liệu** (§1–§9) · **món ăn** (§10) · **công thức** (§11).

---

## 0. Chính sách nền — chỉ đạo của chủ dự án

Ngày 2026-08-31, chủ dự án chỉ đạo nguyên văn:

> *"Không cần phải xin phép viện dinh dưỡng bạn hãy tự tra cứu từng món ăn, từng công thức, từng
> nguyên liệu để lấy thông tin rồi bổ sung vào đi, tôi chấp nhận đánh đổi thời gian để lấy độ chính
> xác."*

Đây là **quyết định nền của dự án**, không phải một gợi ý. Nó thay thế hướng xin phép Viện Dinh dưỡng
([LEGAL-001](../05-tasks/tasks/LEGAL-001.md) — đã huỷ).

### Chỉ đạo này cho phép làm gì

| ✅ Được | Vì sao |
|---|---|
| Tra từng nguyên liệu, từng món, từng công thức từ **nhiều nguồn công khai** | Giá trị dinh dưỡng và thành phần món ăn là **dữ kiện**; dữ kiện không có bản quyền |
| Ghi lại **số liệu** kèm xuất xứ từng con số | Biên soạn độc lập, không phải sao chép |
| Đọc nhãn sản phẩm thương mại và ghi lại kèm thương hiệu | Thông tin công bố công khai |
| Tự cân, tự đo, ghi rõ phương pháp | Dữ liệu do dự án tạo ra |
| **Chấp nhận chậm** để đổi lấy độ chính xác và quyền sở hữu dữ liệu | Đây là đánh đổi chủ dự án đã chọn có ý thức |

### Chỉ đạo này KHÔNG cho phép làm gì

Đây là chỗ dễ hiểu sai nhất — "tự tra cứu" không có nghĩa là "lấy gì cũng được".

| 🚫 Không được | Vì sao |
|---|---|
| Số hoá hay chép **có hệ thống** bảng của Viện Dinh dưỡng, kể cả từ PDF trôi nổi | Cái được bảo hộ là **bảng biên soạn** — sự lựa chọn, sắp xếp, tập hợp. Chép nó vẫn là tái tạo tác phẩm của họ |
| Lấy số từ trang web tiếng Việt chép lại bảng đó | Nguồn trung gian không rửa sạch được vấn đề bản quyền — xem §6.1 |
| Chép **văn bản** công thức từ blog/sách nấu ăn | Danh sách nguyên liệu là dữ kiện, nhưng **cách diễn đạt** có bản quyền — xem §11.1 |
| Dùng nguồn trong danh sách đen QT-2 | Không đổi |
| Điền số từ trí nhớ hoặc suy đoán cho nhanh | Phá thẳng mục tiêu "đánh đổi thời gian lấy độ chính xác" |

> **Câu then chốt:** chỉ đạo là *"tự tra cứu"*, không phải *"tự điền"*. Khi không tra được, câu trả
> lời đúng là `null` — chứ không phải một con số hợp lý trông giống thật. Điền bừa là cách nhanh nhất
> để phản bội chính sự đánh đổi mà chủ dự án đã chấp nhận: mất thời gian mà vẫn không có độ chính xác.

---

## 1. Bốn quy tắc không thương lượng

1. **Không viết ra con số dinh dưỡng từ trí nhớ.** Áp dụng cho cả người lẫn AI. Mỗi giá trị phải đến
   từ một trang **đã thực sự truy cập** trong phiên làm việc đó.
2. **Mỗi con số phải có `external_id` và `source_url`.** Không có xuất xứ thì không được ghi vào file.
3. **`null` là kết quả hợp lệ.** Tra không ra thì để trống và ghi rõ đã tìm ở đâu. Nguyên liệu `null`
   làm món chứa nó hiển thị "chưa đủ dữ liệu" — đó là hành vi đúng theo QT-1, không phải thất bại.
4. **`null` khác `0`.** `null` = "không đo/không biết". `0` = "biết chắc bằng 0". Đừng bao giờ thay
   `null` bằng `0` cho gọn — nó biến "không biết" thành "biết chắc là không có".

---

## 2. Thứ tự nguồn

Dùng nguồn cao nhất tra được. Bảng đầy đủ kèm `fct_source` tương ứng:
[DATA-PIPELINE §7.2](../04-data-sources/DATA-PIPELINE.md).

| # | Nguồn | Khi nào dùng |
|---|---|---|
| 1 | **USDA FoodData Central** (SR Legacy, Foundation) | Mặc định |
| 2 | **ANSES-Ciqual** / **Canadian Nutrient File** / **UK CoFID** | USDA không có, hoặc để đối chiếu chéo |
| 3 | **USDA Branded** | Sản phẩm chế biến không có trong bảng thành phần |
| 4 | **Nhãn sản phẩm thương mại bán tại VN** | Nguyên liệu đặc thù Việt Nam |
| 5 | **Tự đo / tự cân** | Không nguồn nào có |
| 6 | `null` | Không tra được |

> Nguồn 3–5 phá nguyên tắc "một bảng duy nhất" của QT-4. Chỉ dùng cho nguyên liệu mà nguồn 1–2 không
> phủ, và phải hạ `confidence` của món dùng chúng.

---

## 3. Endpoint và cách truy cập

### 3.1 USDA FoodData Central

⚠️ **`DEMO_KEY` của `api.nal.usda.gov` gần như không dùng được:** bị chặn `429` sau **6–12 lượt gọi**,
`Retry-After` khoảng **14 giờ**, và giới hạn áp **theo key chứ không theo IP** — đổi máy không giúp gì.

Đường đi thực tế đã dùng — endpoint portal công khai, **không cần API key, không bị rate limit**:

```
Tìm kiếm:
POST https://fdc.nal.usda.gov/portal-data/external/search
body: {"generalSearchInput":"tên tiếng Anh",
       "includeDataTypes":{"SR Legacy":true,"Foundation":true},
       "pageNumber":1,"requireAllWords":false}

Chi tiết + quy đổi đơn vị:
GET https://fdc.nal.usda.gov/portal-data/external/{fdcId}
```

- Dinh dưỡng nằm ở `foodNutrients[].value`
- Quy đổi đơn vị nằm ở **`foodMeasures[]`** — *không phải* `foodPortions`
- Tên khoa học ở `scientificName`, số mẫu ở `dataPoints`, khoảng thực đo ở `min`/`max`

> ⚠️ **Endpoint này không có tài liệu chính thức.** Dùng được cho việc tra cứu thủ công, nhưng
> **KHÔNG đưa vào code sản xuất** — [DATA-006](../05-tasks/tasks/DATA-006.md) phải dùng API key
> riêng, hoặc tốt hơn là tải bộ CSV về `data/reference/` (đúng QT-3, chạy lại được offline).

> URL cho người đọc `https://fdc.nal.usda.gov/food-details/{fdcId}/nutrients` trả **HTTP 404 khi gọi
> bằng curl** (site là SPA Angular) nhưng mở bằng trình duyệt vẫn đúng. Lưu URL này vào
> `source_url` vì người sau cần click được; dùng endpoint `portal-data` để máy đọc.

### 3.2 ANSES-Ciqual

Endpoint Elasticsearch công khai, không cần key (phát hiện qua `https://ciqual.anses.fr/assets/env-specific.json`):

```
POST https://ciqual.anses.fr/esearch/aliments/_search
```

Truy vấn theo `code` hoặc `multi_match` trên `nomFr` / `nomEng`. Ciqual trả kèm **mã độ tin cậy A–D**
cho từng chỉ số — ánh xạ tốt vào trường `confidence`.

> ⚠️ Trường `milesime` trong config ghi `2017-01` — cần xác minh niên đại dữ liệu trước khi nhập chính thức.

### 3.3 Open Food Facts

```
GET https://world.openfoodfacts.org/api/v2/product/{barcode}
```

⚠️ **ODbL — share-alike.** Trước khi ghi giá trị OFF vào `data/`, đọc
[LICENSE-COMPLIANCE §5](../04-data-sources/LICENSE-COMPLIANCE.md) và ghi `license_note` vào file.
Đang chờ quyết định [LEGAL-002](../05-tasks/tasks/LEGAL-002.md).

---

## 4. Mười chín bẫy và kỹ thuật đã đúc kết từ thực tế

### 4.1 Đơn vị năng lượng — sai 4,184 lần

Trong JSON của FDC, "Energy" xuất hiện **hai lần**:

| nutrient id | Đơn vị |
|---|---|
| **1008** | **kcal** ← dùng cái này |
| 1062 | kJ |

Lọc theo **tên** thay vì **id** sẽ lấy nhầm kJ. Lỗi này **đã xảy ra thật** trong đợt tra cứu đầu và
phải trích lại toàn bộ.

Bản ghi **Foundation** lại không có 1008 mà dùng:

| nutrient id | Ý nghĩa |
|---|---|
| 2047 | Atwater tổng quát |
| 2048 | Atwater riêng |

Hai giá trị này **khác nhau** (ví dụ ba chỉ: 380 so với 385 kcal). Ghi rõ đã dùng cái nào.

### 4.2 `dataPoints = 0` nghĩa là giá trị được gán, không phải đo

Toàn bộ cột Energy và Carbohydrate của SR Legacy đều `n=0` vì tính by-difference — đó là bình thường.
Nhưng khi **protein** hay **lipid** cũng `n=0` (ví dụ "Rice noodles, dry" fdc 169742), giá trị đó chỉ
là ước lượng của USDA. Khi thấy `dataPoints = 0` ở macro, **hạ `mapping_confidence`** và ghi vào
`mapping_note`.

### 4.3 USDA và Ciqual đo carbohydrate khác nhau

USDA tính **by-difference** (lấy 100 trừ các thành phần khác), Ciqual **đo glucid trực tiếp**. Đây là
lý do carb khoai lang lệch 20,1 so với 17,1 và khoai tây lệch 17,5 so với 16,2. Lệch này **không phải
sai sót** — nó là khác biệt phương pháp, và là minh hoạ sống cho QT-4.

### 4.4 `foodMeasures` chứ không phải `foodPortions`

Quy đổi đơn vị nằm ở `foodMeasures[]`. Nhiều bản ghi có `foodPortions` rỗng.

Trên endpoint `portal-data/external/{id}`, mỗi phần tử dùng **`value` + `modifier` + `gramWeight`**,
không có `disseminationText`. Đọc đúng phải ghép: `9.0` + `"sprigs"` → 20 g.

### 4.5 Trường `amount` luôn null

Trên endpoint `portal-data/external/{id}`, `amount` **luôn** `null`. SR Legacy để số ở `value`;
Foundation để ở `value`/`median` kèm `min`/`max`. Code đọc `amount` sẽ nhận `null` trên **mọi** bản
ghi và tưởng là thiếu dữ liệu.

### 4.6 Bản ghi Foundation có thể KHÔNG có năng lượng

Quy tắc trực giác *"ưu tiên Foundation vì mới hơn"* **sai** trong một số trường hợp. Ví dụ thật:
`fdcId 2727585` (Green onion, Foundation, 2023) **không có 1008, không có 2047, không có 2048**, mục
Carbohydrates rỗng hoàn toàn, protein median 0,61 g so với 1,83 g ở SR Legacy.

⇒ **Luôn kiểm tra sự tồn tại của giá trị năng lượng trước khi chọn dòng.** Chọn Foundation mù quáng
sẽ tạo ra nguyên liệu không tính được calo.

### 4.7 Phân biệt `dataPoints = 0` bình thường và bất thường

| Trường hợp | Ý nghĩa |
|---|---|
| Chỉ **năng lượng** và **carbohydrate** có `dataPoints = 0` | ✅ Bình thường — chúng luôn là giá trị tính (Atwater / by-difference) |
| **Toàn bộ** proximate đều `dataPoints = 0` | 🚩 Cờ đỏ — không có mẫu đo nào, mọi giá trị đều gán. Hạ `confidence`, ghi vào `mapping_note` |

Ví dụ thật thuộc nhóm cờ đỏ: `170007` (Onions, welsh) và `170474` (mồng tơi).

### 4.8 Response lớn bị cắt — lỗi im lặng nguy hiểm nhất

Response tìm kiếm có thể lên tới **1,24 MB** cho từ khoá phổ thông (endpoint trả đủ mảng dinh dưỡng
cho 50 món), mất 60–150 giây. Timeout quá ngắn ⇒ nhận JSON **cụt** ⇒ lỗi parse ⇒ trông **giống hệt**
"nguyên liệu này không tra được".

**Cách phòng:** đặt timeout ≥150 s, hoặc bật `requireAllWords: true` cho từ khoá hiếm (giảm còn dưới
5 giây). Và **bắt buộc kiểm tra JSON parse thành công trước khi kết luận một nguyên liệu không tra
được** — nếu không, `lacdoi:import-usda` sẽ âm thầm bỏ sót nguyên liệu và báo là "không có dữ liệu".

### 4.9 Khoảng trắng trong từ khoá làm hỏng kết quả

Ví dụ thật: `"wax gourd"` (có dấu cách) trả 14 kết quả **nhưng không có bí đao**. `"waxgourd"` viết
liền trả 3 kết quả, có ngay; `"Benincasa"` trả đúng 1.

⇒ **Khi một nguyên liệu châu Á "không tìm thấy", trước khi kết luận `null` phải thử đủ ba dạng:**
tên có dấu cách · tên viết liền · **tên khoa học**.

Tương tự, mướp tra `"luffa"` mới ra — tên USDA của nó là *"dishcloth gourd / towelgourd"*, không ai
đoán được.

### 4.10 `derivation` quan trọng ngang `dataPoints`

`dataPoints` không kể hết câu chuyện. Trường `foodNutrientDerivation` mới cho biết giá trị **đến từ
đâu**:

| Mã / mô tả | Nghĩa |
|---|---|
| `A` — Analytical | ✅ Đo thật |
| `NC` — Calculated | Tính ra (năng lượng Atwater, carb by-difference) — bình thường |
| `Z` — Zero assigned | Gán bằng 0 |
| *"Based on another form of the food or similar food"* | 🚩 **Suy ra từ thực phẩm KHÁC** — `dataPoints` không tố cáo điều này |

Ví dụ thật: chất xơ của mướp (fdc 168414) mang mô tả cuối cùng — tức là mượn từ thực phẩm khác, dù
nhìn `dataPoints` không thấy vấn đề gì.

### 4.11 `kcal_min = kcal_max` là khoảng GIẢ — phải để `null`

**Quy tắc chốt 2026-08-31.** Khi không tra được biến thiên của một nguyên liệu, **để
`kcal_min` và `kcal_max` bằng `null`**, tuyệt đối không đặt cả hai bằng giá trị điểm.

| Cách ghi | Máy đọc ra | Có đúng không |
|---|---|---|
| `min: 43, max: 43` | "Đo được rất chính xác, biên độ bằng 0" | 🚫 **Sai ngược** |
| `min: null, max: null` | "Chưa tra được biến thiên" | ✅ Đúng |
| `min: 120, max: 167` | "Biến thiên thật, đã tra được" | ✅ Đúng |

Nghịch lý nếu làm sai: đu đủ (chỉ có **một** bản ghi USDA, nền mẫu mỏng) sẽ trông **chắc chắn hơn**
bơ (có ba bản ghi theo giống, dữ liệu dày) — chỉ vì bơ tra được biến thiên còn đu đủ thì không.
Đây là hiểu ngược hoàn toàn tinh thần QT-5.

Hiện tại **30/104 nguyên liệu có khoảng thật**; số còn lại để `null` đúng quy tắc.

### 4.12 Lượng tử hoá khẩu phần — dữ liệu Branded sai tới hàng chục lần

Giá trị per-100g của bản ghi **Branded** được **tính ngược** từ con số trên nhãn của một khẩu phần
nhỏ. Với khẩu phần 5–6 g, quy tắc làm tròn của FDA (21 CFR 101.9(c)(1): calo làm tròn theo bội số 5)
làm sai số per-100g **phình lên hàng chục lần**.

**Ví dụ thật — mắm ruốc Lee Kum Kee:** nhãn ghi *"5 kcal / 6 g"* → USDA hiển thị **83 kcal/100g**.
Nhưng giá trị thật của khẩu phần nằm trong 2,5–7,5 kcal → khoảng thật là **42–125 kcal/100g**.
Protein cũng vậy: *"1 g / 6 g"* → hiển thị 16,7 nhưng thật ra 8–25 g/100g.

**Cách phát hiện:** nhân giá trị per-100g với `servingSize/100`. Nếu ra đúng bội số 5 (hoặc protein
ra số nguyên 0/1 g), con số **đã bị lượng tử hoá** — ghi khoảng, không ghi số điểm.

### 4.13 "0 kcal" trên nhãn KHÔNG phải 0

Dấm gạo và mì chính đều khai **0 kcal**. Đó là ngưỡng làm tròn *dưới 5 kcal/khẩu phần*, **không phải
phép đo**. Toàn bộ 7.538 bản ghi Branded "rice vinegar" đều khai 0 theo cùng cách.

Nhập thẳng `0` vào cơ sở dữ liệu ⇒ engine calo **im lặng bỏ qua toàn bộ nhóm gia vị lỏng**.

**Cách đúng:** `kcal_min = 0`, `kcal_max = 5 × 100 / servingSize`. Hoặc tốt hơn, dùng một dòng đo
thật trong cùng bảng làm proxy và ghi rõ (dấm gạo đã làm vậy — dùng `Vinegar, distilled` 18 kcal).

### 4.14 Chất dinh dưỡng VẮNG MẶT khác với bằng 0

Sả (fdc 168573) **không có** nutrient 1079 (chất xơ) trong danh sách `foodNutrients` — khác hoàn toàn
với "xơ = 0". Mà carb 25,3 g lại tính by-difference.

Hệ quả: sả rất nhiều xơ không tiêu hoá được, nên 99 kcal là **cận trên mạnh**, calo hấp thu thực tế
thấp hơn nhiều. Code nhập liệu **phải phân biệt *vắng mặt* với *bằng 0***, nếu không sả bị tính là
không có xơ và calo bị thổi lên.

### 4.15 Tên trường của endpoint portal khác tài liệu API chính thức

| Tài liệu API nói | Endpoint portal thực tế dùng |
|---|---|
| `foodPortions` | **`foodMeasures`** |
| `dataType` | **`foodType`** |
| `publicationDate` | **`lastUpdated`** |
| `amount` | **`value`** |

Lọc theo tên bên trái trả rỗng, và trông hệt như "món này không có dữ liệu".

### 4.16 Open Food Facts chặn bằng 503, không phải 429

Khác USDA. OFF trả **503 sau khoảng 2 lượt liên tiếp**, và trả **HTML chứ không phải JSON** → lỗi
parse trông giống "không có dữ liệu". **Phải kiểm HTTP status trước khi parse.**

### 4.17 Cùng `ndbNumber` KHÔNG có nghĩa là hai bản ghi thay thế được cho nhau

Đây là bẫy nguy hiểm nhất trong nhóm "chọn dòng". SR Legacy và Foundation có thể mang **cùng
`ndbNumber`** mà vẫn lệch rất xa — không chỉ ở cách tính năng lượng, mà ở **macro gốc**:

| Nguyên liệu | ndbNumber | SR Legacy | Foundation | Lệch |
|---|---|---|---|---|
| Sữa chua nguyên kem | 1116 | 61 kcal, nước 87,9, béo 3,25 | 78 kcal, nước 85,3, béo 4,48 | **+28% kcal** |
| Lạc sống | 16087 | béo 49,2 · carb 16,1 | béo 43,3 · carb 26,5 | carb **+65%** |
| Bột gạo | 20061 | xơ **2,4 g** | xơ **0,5 g** | **4,8 lần** |

⇒ **Không được coi hai bản là thay thế cho nhau.** Chọn một, ghi rõ đã chọn cái nào và vì sao, và đưa
bản kia vào `alternatives` với giá trị cụ thể.

### 4.18 Kỹ thuật kiểm chéo: tỉ lệ khô/tươi phải khớp tỉ lệ chất khô

Đây là **phép kiểm dương tính** hiếm hoi — dùng nó cho mọi cặp khô/tươi.

Ví dụ thật (hạt sen): 332 kcal khô / 89 kcal tươi = **3,73**. Chất khô: 85,8% / 23,0% = **3,73**.
Trùng khít ⇒ hai bản ghi cùng gốc, số liệu tự nhất quán.

Nếu hai tỉ lệ **không khớp**, một trong hai bản ghi có vấn đề — điều tra trước khi dùng.

### 4.19 SR Legacy không công bố min/max

Nên `kcal_min` / `kcal_max` thường là `null` với nguyên liệu lấy từ SR Legacy. Khoảng bất định theo
QT-5 phải **sinh ở tầng engine** ([CALORIE-ENGINE §5](../06-nutrition/CALORIE-ENGINE.md)), hoặc lấy
từ việc đối chiếu nhiều nguồn — không lấy được từ USDA.

---

## 5. Quy trình tra một nguyên liệu — 8 bước

1. **Xác định trạng thái cần tra:** sống / chín / khô / ăn liền. Sai bước này thì mọi bước sau vô nghĩa
   (cơm chín và gạo sống chênh gần 3 lần).
2. **Tìm tên tiếng Anh đúng**, kể cả tên ít dùng. *(Rau muống nằm dưới "Water convolvulus", không phải
   "water spinach" — xem [EDITORIAL-GUIDE §9.1](EDITORIAL-GUIDE.md).)*
3. **Tra USDA**, lấy về **nhiều** ứng viên chứ không chỉ kết quả đầu.
4. **Kiểm `scientificName`** để xác nhận đúng loài. Không khớp ⇒ hạ `confidence` và ghi lý do.
5. **Chọn dòng, và ghi lại các dòng đã loại** vào `alternatives[]` kèm `why_not`. Đây là bước hay bị
   bỏ nhất và cũng có giá trị lâu dài nhất — nó ngăn người sau chọn lại đúng dòng đã bị loại có lý do.
6. **Đối chiếu chéo ít nhất một nguồn khác** (Ciqual / CNF / CoFID) với nguyên liệu quan trọng.
   Lệch >20% ⇒ ghi cả hai vào `mapping_note`, đặt `kcal_min`/`kcal_max`, hạ `confidence`.
7. **Lấy quy đổi đơn vị từ `foodMeasures`**, và **đánh dấu rõ đơn vị nào là cỡ phương Tây**
   (quả ớt 45 g, tép tỏi 3 g, bìa đậu phụ 324 g — đều không đúng cỡ Việt Nam).
8. **Ghi file** theo schema ở [DATA-REPOSITORY §3](../04-data-sources/DATA-REPOSITORY.md), đặt
   `needs_review = true`.

---

## 6. Nhận biết nguồn bẩn

### 6.1 Trang tiếng Việt chép bảng có bản quyền

Nhiều trang web Việt đăng số dinh dưỡng cho nguyên liệu Việt mà không ghi nguồn, hoặc dẫn lại "Viện
Dinh dưỡng" / "Viện Y tế". Gần như chắc chắn chép từ **Bảng Thành phần Thực phẩm Việt Nam có bản
quyền** — dùng là vi phạm QT-2 dù nguồn trung gian trông vô hại.

**Dấu hiệu:** nhiều trang cho **cùng một con số lẻ giống hệt nhau**, không trang nào dẫn được về phép
đo gốc.

**Ví dụ thật:** mắm tôm "73 kcal / 14,8 g protein" — xuất hiện khắp nơi, không truy nguyên được.
**Đã loại bỏ**, để `null`.

### 6.2 Trang do AI sinh, trích dẫn nghiên cứu không tồn tại

Khi tra về độ chính xác của app dinh dưỡng, nhiều kết quả đầu bảng tìm kiếm là site do AI sinh, trích
dẫn các "nghiên cứu" không tồn tại (kiểu *"benchmark 6 app DAI-VAL-2026-01"*). Các con số hay lan
truyền như *"37% bản ghi MyFitnessPal lệch >20% năng lượng"* chỉ truy được về những site đó.

**Dấu hiệu:** tên miền chung chung (`best-*.com`, `*-review.com`), không có tác giả, trích dẫn có mã
nghiên cứu trông chuyên nghiệp nhưng không tìm thấy ở đâu khác.

**Cách xử lý:** truy về nguồn gốc. Không truy được ⇒ **loại bỏ**, và **ghi lại là đã loại vì lý do gì**
— để người sau không mất công tra lại rồi tưởng là phát hiện mới.

### 6.3 Trang tổng hợp dinh dưỡng

`fatsecret`, `eatthismuch`, `nutritionvalue.org`… tổng hợp từ nhiều nguồn nhưng **không truy nguyên
được**. Không dùng làm nguồn chính. Dùng được để **kiểm tra tính hợp lý** ("con số này có vô lý
không?") nhưng không ghi vào file.

---

## 7. Thẩm định nguồn mới

Bất kỳ nguồn nào chưa có trong [LICENSE-COMPLIANCE §3](../04-data-sources/LICENSE-COMPLIANCE.md) đều
phải qua quy trình 6 bước ở §6 của tài liệu đó **trước khi** lấy số.

> ⚠️ **Đã có tiền lệ làm ngược.** Trong đợt ING-001, **UK CoFID** được dùng cho cá basa **trước khi**
> qua thẩm định. Thẩm định sau cho thấy license là OGL nên hợp lệ — nhưng đó là may, không phải quy
> trình. **Open Food Facts** cũng bị dùng khi đang ở trạng thái "⏸️ hoãn chờ quyết định". Cả hai đã
> được ghi vào nhật ký thẩm định §7 kèm ghi chú sai quy trình.
>
> Rút ra: khi tra cứu gặp một nguồn mới hấp dẫn, phản xạ tự nhiên là lấy số trước rồi hợp thức hoá
> sau. **Dừng lại và thẩm định trước.**

---

## 8. Mẫu prompt giao cho AI agent tra cứu

Đã dùng thật và cho kết quả tốt. Ba phần bắt buộc:

```
QUY TẮC TUYỆT ĐỐI:
- TUYỆT ĐỐI KHÔNG viết con số dinh dưỡng từ trí nhớ hay suy đoán.
- Mỗi con số PHẢI kèm fdc_id hoặc URL cụ thể bạn đã truy cập.
- Tra không ra thì ghi null và giải thích đã tìm ở đâu. KHÔNG đoán.
  null là kết quả hợp lệ, không phải thất bại.
- Không dùng [danh sách đen QT-2]. Không chép từ Bảng Thành phần
  Thực phẩm Việt Nam của Viện Dinh dưỡng.

NGUỒN THEO THỨ TỰ: [dán §2 và §3]

VỚI MỖI NGUYÊN LIỆU TRẢ VỀ: [dán schema ở DATA-REPOSITORY §3]

Ưu tiên độ chính xác và tính truy nguyên hơn là điền đủ ô.
Thà 5 nguyên liệu có nguồn chắc chắn còn hơn 13 nguyên liệu có số đẹp
mà không kiểm chứng được.
```

Câu cuối quan trọng hơn vẻ ngoài của nó: không có nó, agent có xu hướng điền đủ mọi ô bằng cách hạ
tiêu chuẩn nguồn.

**Kiểm lại kết quả của agent** — không nhận nguyên si:
- Có `external_id` và `source_url` cho **mọi** giá trị không?
- Có ô nào "tròn trịa đáng ngờ" mà không có mã nguồn không?
- Các `null` có kèm giải thích đã tìm ở đâu không?
- Có dùng nguồn nào ngoài danh sách trắng không?

---

## 10. Tra cứu MÓN ĂN

Khác nguyên liệu: món ăn hầu như **không có nguồn dữ liệu có cấu trúc**. Wikidata chỉ có ~58 món Việt.
Phần lớn phải tự xác định.

### 10.1 Các trường cần tra và tra ở đâu

| Trường | Nguồn | Ghi chú |
|---|---|---|
| Tên chuẩn + tên gọi khác | Wikidata (CC0), từ điển, báo ẩm thực | Quy tắc đặt tên: [EDITORIAL-GUIDE §1](EDITORIAL-GUIDE.md) |
| Tên tiếng Anh | Wikidata, Wikipedia | Đừng tự dịch nếu đã có tên phổ biến |
| Vùng miền | Bài viết văn hoá ẩm thực, báo địa phương | Gán vùng **gốc**, kể cả khi giờ phổ biến khắp nơi |
| Bữa phù hợp + trọng số | **Không có nguồn** — đây là phán đoán biên soạn | Tự đặt theo [EDITORIAL-GUIDE §5](EDITORIAL-GUIDE.md), ghi `source: curated` |
| Ăn ngoài / tự nấu | Phán đoán biên soạn | Một món có thể cả hai |
| Cách chế biến | Từ công thức | **Quyết định hệ số calo** — phải đúng |
| Khẩu phần điển hình | Tự cân, hoặc quan sát thực tế | Ghi rõ là ước lượng |

> **Không bịa vùng miền cho món không có gốc rõ ràng.** Cơm rang, trứng chiên phổ biến toàn quốc →
> để `region_id = null`. Ép gán vùng là tạo ra dữ liệu sai trông như dữ liệu thật.

### 10.2 Calo cho món ăn ngoài

Món ăn ngoài **không tính được từ nguyên liệu** — không ai biết quán cho bao nhiêu dầu. Quy trình:

1. Tìm món tương đương trong **USDA FNDDS** (có Food Code cho món chế biến sẵn) → dùng làm mốc.
2. Hoặc tính từ khẩu phần điển hình + nguyên liệu chính ước lượng.
3. Ghi **khoảng** `min`–`max`, `confidence: low`, và **`source_note` bắt buộc** nói rõ ước lượng dựa
   trên cái gì.
4. Không có căn cứ nào ⇒ **để trống**, món ở `confidence: unknown`.

Mẫu `source_note` tốt và tệ: [EDITORIAL-GUIDE §8](EDITORIAL-GUIDE.md).

---

## 11. Tra cứu CÔNG THỨC

### 11.1 Ranh giới bản quyền — đọc kỹ trước khi tra

Đây là chỗ khác biệt tinh tế nhất trong cả tài liệu:

| Thành phần của một công thức | Bản quyền? | Dự án được làm gì |
|---|---|---|
| **Danh sách nguyên liệu + định lượng** | ❌ Không — đây là dữ kiện và quy trình | ✅ Ghi lại, tổng hợp từ nhiều nguồn |
| **Các bước nấu, ở dạng chỉ dẫn thuần tuý** | ❌ Không, ở mức ý tưởng | ✅ **Viết lại bằng lời của mình** |
| **Cách diễn đạt**: lời dẫn, mô tả, câu văn, mẹo riêng | ✅ **Có** | 🚫 Không chép, kể cả một đoạn |
| **Ảnh món** | ✅ Có | 🚫 Không lấy nếu không có license rõ ràng |
| **Tuyển tập công thức** (cả cuốn sách, cả website) | ✅ Có, ở mức biên soạn | 🚫 Không chép hàng loạt |

> **Quy tắc vận hành:** đọc **ít nhất 3 nguồn** cho một món, tổng hợp thành định lượng của riêng dự
> án, rồi **viết các bước bằng lời của mình**. Nếu bản viết ra giống một nguồn tới mức nhận ra được
> từng câu, đó là chép — viết lại.
>
> Đây không phải chi tiết pháp lý vụn vặt: nó cũng cho ra **dữ liệu tốt hơn**. Ba nguồn mâu thuẫn
> nhau về định lượng chính là thông tin — nó cho biết khoảng dao động thật của món đó.

### 11.2 Quy trình tra một công thức — 6 bước

1. **Thu ít nhất 3 nguồn độc lập** cho cùng một món. Ưu tiên nguồn có định lượng cụ thể (gram, muỗng)
   hơn nguồn viết "một ít", "vừa đủ".
2. **Lập bảng so sánh định lượng** giữa các nguồn cho từng nguyên liệu.
3. **Chọn giá trị trung tâm**, ghi lại khoảng dao động giữa các nguồn.
4. **Quy về một khẩu phần** — phần lớn công thức trên mạng viết cho 4–6 người.
5. **Ánh xạ từng nguyên liệu** sang `data/ingredients/` (hoặc tạo mới theo §5).
6. **Viết các bước nấu bằng lời của mình**, ngắn gọn, không sao chép.

### 11.3 Ghi lại nguồn

Trong file món, mỗi công thức ghi:

```json
"recipe_sources": [
  { "url": "https://...", "accessed": "2026-09-05", "note": "định lượng cho 4 người, đã chia 4" },
  { "url": "https://...", "accessed": "2026-09-05", "note": "nguồn thứ 2, thịt nhiều hơn 20%" }
]
```

Ghi nguồn **không phải để xin phép** — mà để người sau kiểm chứng được, và để thấy định lượng đã
được tổng hợp từ đâu chứ không phải bịa ra.

> ⚠️ **Trường `recipe_sources` chưa có trong schema món ở
> [DATA-REPOSITORY §3](../04-data-sources/DATA-REPOSITORY.md).** Phải bổ sung trước khi bắt đầu
> [DISH-001](../05-tasks/tasks/DISH-001.md), nếu không sẽ lặp lại đúng lỗi QT-7 đã mắc với nguyên liệu.

### 11.4 Món không tìm được công thức đáng tin

Để món ở **Mức 1** (không có công thức, chỉ có khoảng calo ước lượng). Đó là kết quả hợp lệ —
[PRD §7](../01-prd/PRD.md) thiết kế ba mức chính vì tình huống này. Đừng cố lên Mức 2 bằng một công
thức đoán.

---

## 12. Ghi chép — nơi nào ghi gì

| Nội dung | Ghi ở đâu |
|---|---|
| Giá trị + xuất xứ của từng nguyên liệu | `data/ingredients/<slug>.json` |
| Vì sao chọn dòng này, loại dòng kia | `mapping_note` và `alternatives[]` trong chính file đó |
| Nguồn mới được duyệt hay bị từ chối | [LICENSE-COMPLIANCE §7](../04-data-sources/LICENSE-COMPLIANCE.md) — nhật ký thẩm định |
| Bẫy mới phát hiện (loài, đơn vị, phương pháp) | [EDITORIAL-GUIDE §9](EDITORIAL-GUIDE.md) |
| Kỹ thuật truy cập mới (endpoint, giới hạn) | **Tài liệu này**, §3 và §4 |
| Tổng kết một đợt tra cứu | File task tương ứng trong `docs/05-tasks/tasks/` |

> **Đừng để kiến thức vận hành nằm trong ghi chú của một task đã đóng.** Đó chính là chỗ người kế
> nhiệm không bao giờ tìm tới — và là lý do tài liệu này tồn tại.
