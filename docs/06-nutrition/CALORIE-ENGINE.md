# Engine tính calo — Lắc Đói

**Cập nhật lần cuối:** 2026-08-31 — hiện thực xong CALC-001 (`App\Nutrition\NutritionCalculator`, engine_version 1.0.0); thêm §5b
**Trạng thái:** ✅ **ĐÃ HIỆN THỰC** phần CALC-001 + logic chiên/xơ của CALC-002.
Phần còn thiếu là **dữ liệu** hệ số (DATA-004, DATA-008) — code đã sẵn chỗ.

Mọi công thức và hệ số trong tài liệu này đều có nguồn. Con số không nguồn không được đưa vào code.

---

## 1. Nguyên tắc

1. **Hàm thuần tuý.** Không gọi mạng, không gọi LLM, không phụ thuộc thời gian. Cùng đầu vào luôn cho
   cùng đầu ra → test được, tính lại được.
2. **Thiếu dữ liệu thì báo thiếu, không đoán.** Trả về `missing[]`.
3. **Luôn trả khoảng, không trả một số.** (QT-5)
4. **Một bảng thành phần cho một phép tính.** (QT-4)
5. **Có phiên bản.** `engine_version` lưu cùng kết quả, để biết món nào cần tính lại khi engine đổi.

---

## 2. Chữ ký hàm

```
tính_dinh_dưỡng(
    nguyên_liệu: [{ ingredient_id, grams, is_optional }],
    phương_pháp_chế_biến: string,
    fct_source: string
) → {
    kcal_min, kcal_max,
    protein_g, fat_g, carb_g, fiber_g,
    confidence: high|medium|low|unknown,
    missing: [ingredient_id],
    fct_source,
    engine_version
}
```

### Các bước

```
1. Với mỗi nguyên liệu:
     a. Quy đổi đơn vị → gram      (ingredient_units, hoặc density nếu là thể tích)
     b. Tra reference_foods theo fct_source  → dinh dưỡng /100g
        → không tra được ⇒ đưa vào missing[], BỎ QUA, không ước lượng
     c. Áp hệ số hao hụt khi nấu   (§3)
     d. Cộng dồn

2. Cộng dầu hấp thụ nếu chế biến là chiên/rán   (§4)

3. Tính khoảng min–max theo độ bất định tích luỹ  (§5)

4. Xác định confidence  (§6)
```

**Quan trọng ở bước 1b:** nguyên liệu không tra được thì **bỏ qua khỏi phép cộng** chứ không thay
bằng giá trị trung bình. Kết quả sẽ thấp hơn thực tế — và đó là lý do `missing[]` khác rỗng bắt buộc
kéo `confidence` xuống. Che giấu chỗ thiếu bằng số ước lượng là vi phạm QT-1.

---

## 3. Hệ số hao hụt khi nấu (yield factor)

Nấu làm thay đổi khối lượng: bay hơi nước, chảy mỡ, hấp thụ nước (gạo, bún). Nếu bỏ qua, mọi chất
dinh dưỡng đều sai theo cùng một tỷ lệ.

**Nguồn hệ số (public domain, được duyệt):**

- *USDA Table of Cooking Yields for Meat and Poultry, Release 2*
- *USDA Table of Nutrient Retention Factors, Release 6 (2007)* — 26 chất, ~290 thực phẩm
- *FAO/INFOODS Guidelines for Converting Units, Denominators and Expressions v1.0 (2012)* — hướng dẫn
  chọn hệ số hao hụt và hệ số giữ lại

**Cách áp dụng:**

```
gram_sau_nấu = gram_sống × yield_factor
```

Với năng lượng và ba chất sinh năng lượng (đạm, béo, bột đường), **khối lượng chất không đổi khi
mất nước** — chỉ nồng độ trên 100 g thay đổi. Nên thứ tự tính đúng là:

> Tính tổng dinh dưỡng **từ khối lượng nguyên liệu sống**, rồi mới dùng yield factor để suy ra khối
> lượng thành phẩm (phục vụ hiển thị "trên 100 g món").

Sai lầm thường gặp: nhân dinh dưỡng với yield factor. Điều đó làm mất chất dinh dưỡng một cách vô lý.

Hệ số giữ lại (retention factor) áp dụng cho **vi chất** (vitamin, khoáng), không áp cho năng lượng.
v1 chỉ tính macro nên tạm chưa cần, nhưng cột `micronutrients` đã dành sẵn chỗ.

---

## 4. Dầu hấp thụ khi chiên — nguồn sai số lớn nhất với món Việt

Đây là chỗ engine dễ sai nhất, và món Việt có rất nhiều món chiên rán.

**Số liệu đo được (nguồn: tổng quan trên PMC10660127 và IJFST):**

| Thực phẩm | Chất béo trước | Chất béo sau khi chiên |
|---|---|---|
| Khoai tây → khoai chiên | 0,2 % | ~14 % |
| Khoai tây → snack khoai | 0,2 % | tới 40 % |
| Phi lê cá tuyết | 1 % | 10,5 % |
| Đùi gà | 2 % | 18 % |

Dầu chỉ thấm khoảng **1 mm lớp vỏ ngoài**, nên lượng hấp thụ phụ thuộc: diện tích bề mặt, có bột áo
hay không, độ ẩm, nhiệt độ dầu, thời gian chiên — **không có thứ nào được ghi trong công thức nấu ăn**.

⇒ **Không thể tính chính xác. Chỉ tính được khoảng.** Đó là lý do món chiên luôn có khoảng min–max
rộng và `confidence` không bao giờ đạt `high`.

**Cách hiện thực:**

```
Nếu cooking_method ∈ {chien, ran, chien-ngap-dau}:
    dầu_hấp_thụ_g = Σ(gram_nguyên_liệu_được_chiên × oil_absorption_pct / 100)
    kcal += dầu_hấp_thụ_g × 9      (1 g chất béo = 9 kcal)
    Nới rộng khoảng: kcal_min = kcal × 0,80 ; kcal_max = kcal × 1,25
    confidence ≤ medium
```

`oil_absorption_pct` lưu ở từng nguyên liệu (`ingredients.oil_absorption_pct`), vì khoai tây và cá
hấp thụ rất khác nhau. Giá trị phải có nguồn; không có nguồn thì để null và món không tính được.

**Hiện thực hoá (CALC-004, chủ dự án duyệt 2026-10-31):** hệ số được gán **theo nhóm
thực phẩm**, trích từ chính hai nguồn trên (điểm đo fries/strips 12%, cheese samosa
10%, dải bánh áo bột 10–28%, phi lê cá 1%→10,5%, đùi gà 8,53% uptake): củ/quả chiên
miếng 12, phô mai trong vỏ bột 10, bột/vỏ chiên ngập 15, bột gạo/tinh bột áo 12,
hải sản 9, thịt/đạm 8. Nguyên liệu không phải chủ thể chiên trong món (gia vị, rau ăn
kèm sống, nhân kín, chính dầu ăn, thành phẩm đã chiên sẵn mà số liệu đã gồm dầu sản
xuất, tinh bột đã nấu chín rán lại với dầu đã kê trong công thức) nhận hệ số **0 theo
bản chất mô hình** — không phải số đo. Bảng đầy đủ và các đánh đổi đã biết:
[CALC-004](../05-tasks/tasks/CALC-004.md).

> **Đính chính 2026-10-31 — quy ước dữ liệu về dầu món chiên đã đổi (Phương án A, QA-002).**
> Từ 2026-10-31, công thức món chiên **không còn kê dầu của bước chiên chính**: dòng
> `dau-an` chỉ ghi dầu cho bước phụ (phi/xào nhân, xào sốt — xem
> [EDITORIAL-GUIDE §12](../09-standards/EDITORIAL-GUIDE.md)); dầu hấp thụ do chính mô hình
> ở mục này đảm nhiệm. Engine **không đổi** (vẫn 1.1.0). Ngoại lệ duy nhất còn kê dầu
> chiên trong công thức là món mà chủ thể chiên thuộc nhóm hệ số 0 "dầu đã kê" ở trên
> (xôi chiên, bánh chưng chiên) — với các món đó dòng dầu là cơ chế tính dầu duy nhất.
> Hệ quả đo lường: QA-002 lần 2 (sau Phương án A) vẫn **chưa đạt** — nhóm chiên +49,0%
> so với phía tham chiếu nguyên văn, vì tham chiếu nguyên văn không chứa dầu hấp thụ mà
> mô hình này cộng. Phân tích và lựa chọn tiếp theo (chờ chủ dự án):
> [accuracy-runs/2026-10-31-vn-dishes-lan-2.md](accuracy-runs/2026-10-31-vn-dishes-lan-2.md).

> **Cảnh báo cho người kế nhiệm:** nếu bỏ qua mục này, calo món chiên sẽ **thấp hơn thực tế 30–50%**.
> Đây là lỗi âm thầm — không có gì báo sai, chỉ là mọi con số đều thấp.

---

## 3b. Phần ăn được — áp TRƯỚC khi tra dinh dưỡng

**Phát hiện 2026-08-31 khi tra cứu sườn lợn.** Bảng USDA ghi giá trị trên **phần ăn được đã loại
xương**. Nhưng sườn, gà, cá, ghẹ, nghêu ở chợ Việt Nam **bán theo cân cả xương/cả vỏ**.

Công thức ghi *"300 g sườn"* là khối lượng **có xương**. Áp thẳng 277 kcal/100 g cho ra 831 kcal,
trong khi phần ăn được thực tế chỉ khoảng một nửa.

**Thứ tự đúng — ba hệ số nối tiếp, không được đảo:**

```
khối lượng mua ở chợ
   × edible_portion_pct     ← bỏ xương/vỏ (DATA-008)
   = khối lượng nguyên liệu sống ăn được
   → tra dinh dưỡng /100g
   × yield_factor            ← thay đổi khối lượng do NẤU (§3)
   = khối lượng thành phẩm
```

> `edible_portion_pct` và `yield_factor` là **hai thứ khác nhau**: cái đầu bỏ phần không ăn được
> *trước khi nấu*, cái sau là thay đổi khối lượng *do nấu*. Nhầm lẫn hai cái này là lỗi phổ biến.

Bảng hệ số: [DATA-008](../05-tasks/tasks/DATA-008.md). Ví dụ đã tra được: nghêu 15,0% (68 g thịt từ
1 lb cả vỏ); gà nguyên con 276 g phần ăn được từ 1 lb làm sẵn.

> **Đã hiện thực 2026-08-31:** `ingredients.edible_portion_pct` (+ `edible_portion_note` bắt
> buộc khi có số) — `DishNutritionService` nhân hệ số **trước** khi tra dinh dưỡng; dòng
> công thức có `is_edible_weight = true` thì bỏ qua (khối lượng đã là phần ăn được). Test:
> `tests/Feature/EdiblePortionTest.php` (300 g sườn × 50% ⇒ giảm đúng nửa). Ba hệ số đầu
> có nguồn foodMeasures: gà nguyên con 60,9%, nghêu 15,0%, chim cút 89,3%; 16 nguyên liệu
> xương/vỏ khác đang `null` kèm ghi chú cách tự cân.
>
> **Bảng hao hụt khi nấu đã nhập (DATA-004, 2026-08-31):** 163 dòng USDA Cooking Yields R2
> trong bảng `cooking_yields`, tra theo (NDB → fdc_id, phương pháp). 9 nguyên liệu thịt cốt
> lõi đã có `default_yield_factor` suy từ dòng cụ thể (ghi trong `default_yield_factor_note`);
> việc tra hệ số THEO CÁCH NẤU CỦA TỪNG MÓN (thay vì default) thuộc phần còn lại của CALC-002.

> ⚠️ **Đây là lỗi âm thầm.** Không có gì báo sai — chỉ là mọi món có xương đều bị vống calo. Nó ảnh
> hưởng đúng những món trung tâm của kho dữ liệu: sườn kho, gà luộc, cá kho, canh xương.

---

## 4a. Nguyên liệu tạo mùi rồi bị vớt bỏ

**Phát hiện 2026-08-31 khi tra cứu sả.** Nhiều món Việt dùng nguyên liệu **chỉ để lấy mùi rồi vớt
ra**: sả đập dập trong nồi phở/bún bò, lá chanh, hoa hồi, quế, thảo quả, gừng nướng.

Chúng **luôn có trong công thức** nhưng **không ai ăn**. Nếu engine cộng calo của chúng:

- Sả: **99 kcal/100g** — một nồi phở dùng 100–200 g sả, đủ làm sai đáng kể một tô.
- Quế, hoa hồi: 250–300 kcal/100g.

**Hiện thực:** cột `dish_ingredients.is_discarded`. Engine **tính khối lượng** (để ra khối lượng
thành phẩm đúng) nhưng **không cộng calo và macro**.

> Đây là khái niệm khác `is_optional`. Tuỳ chọn = có thể không có, khi có thì được ăn. Vớt bỏ = luôn
> có, nhưng không được ăn. Đánh nhầm sang `is_optional` là sai nghĩa và sẽ gây nhầm cho người biên
> soạn sau.

> ⚠️ Một phần chất tan **có** ngấm vào nước dùng (đó là lý do người ta cho sả vào). Bỏ 100% calo là
> xấp xỉ, hơi thấp so với thực tế. Nhưng sai số đó **nhỏ hơn nhiều** so với cộng đủ 100% calo của thứ
> bị vứt đi. Nếu sau này cần chính xác hơn, thêm hệ số chiết xuất — nhưng phải có nguồn, không đoán.

---

## 4b. Thực phẩm giàu xơ — năng lượng bị thổi phồng

**Phát hiện 2026-08-31 khi tra cứu nguyên liệu.** Mộc nhĩ khô (`moc-nhi-kho`) có carbohydrate
**73,0 g** nhưng chất xơ **70,1 g** — gần như toàn bộ glucid là xơ.

USDA áp hệ số Atwater **4 kcal/g cho toàn bộ carbohydrate**, kể cả phần xơ. Kết quả là 284 kcal/100g,
**không phản ánh năng lượng thực hấp thu** — chất xơ không tiêu hoá được cho ra khoảng 2 kcal/g
(hệ số EU) hoặc 0 (một số hệ khác).

**Hệ quả:** mọi món có mộc nhĩ (miến xào, nem rán, canh) sẽ bị thổi phồng năng lượng nếu dùng thẳng
con số USDA.

**Hiện thực đề nghị:**

```
Nếu fiber_g / carb_g > 0,5:
    kcal_hieu_chinh = kcal - (fiber_g × 2)      # trừ bớt phần xơ tính thừa
    ghi rõ đã hiệu chỉnh, và giữ cả giá trị gốc để truy nguyên
```

> ⚠️ Hệ số 2 kcal/g là **quy ước của EU**, cần ghi nguồn khi hiện thực. Ngưỡng 0,5 là do dự án đặt,
> chưa hiệu chuẩn. Đây là thứ phải kiểm chứng ở [TEST-002](../05-tasks/tasks/TEST-002.md), không phải
> hằng số thiêng liêng.

Nguyên liệu đã biết thuộc nhóm này: mộc nhĩ khô (xơ/carb = 0,96), nấm hương khô (0,15 — dưới ngưỡng).

---

## 5. Khoảng min–max

Mỗi nguồn bất định nới khoảng thêm. Nhân dồn:

| Nguồn bất định | Hệ số nới |
|---|---|
| **Bất định nền của mọi phép tính** (bảng thành phần + làm tròn + xấp xỉ của chính engine) | **×0,96 – ×1,04 — LUÔN áp dụng**, kể cả khi mọi nguyên liệu ánh xạ `high` |
| Ánh xạ nguyên liệu `mapping_confidence = medium` | ×0,95 – ×1,05 mỗi nguyên liệu (dồn theo tỷ trọng khối lượng) |
| Ánh xạ `mapping_confidence = low` | ×0,85 – ×1,15 |
| Chế biến chiên/rán | ×0,80 – ×1,25 (§4) |
| Khẩu phần ước lượng, không cân | ×0,85 – ×1,15 |
| Có `missing[]` | Chỉ nới cận **trên**: `kcal_max × (1 + tỷ_lệ_khối_lượng_thiếu)` |

> ⚠️ Các hệ số ở bảng này là **giá trị khởi đầu do dự án đặt**, chưa hiệu chuẩn — **trừ dòng đầu**,
> đã hiệu chuẩn 2026-09-01 (xem khối dưới). Chúng phải được điều chỉnh sau khi chạy kiểm chứng trên
> Nutrition5k và FNDDS ([ACCURACY-SPEC §4](ACCURACY-SPEC.md)). Đừng coi chúng là hằng số thiêng
> liêng — nhưng cũng đừng đổi mà không chạy lại kiểm chứng.

> ## ⚠️ Đính chính 2026-09-01 — engine từng trả "khoảng" rộng bằng 0, vi phạm thẳng QT-5
>
> Bảng trên bản đầu **không có dòng bất định nền**, và `MAPPING_SPREAD['high']` bằng 0. Hệ quả: món
> có toàn nguyên liệu ánh xạ `high`, không chiên, khẩu phần có cân thì `kcal_min === kcal_max` —
> một con số điểm đội lốt khoảng. Đó **đúng là thứ QT-5 cấm** ("không hiển thị con số chính xác giả"),
> và nó nằm ngay trong engine suốt từ CALC-001.
>
> Phép đo FNDDS lần 1 đã in ra bằng chứng mà không ai đọc: chỉ **1,0%** giá trị tham chiếu rơi vào
> khoảng engine trả về. Với khoảng rộng 0 thì 1,0% đó chỉ là số lần hai số tình cờ bằng nhau sau
> làm tròn.
>
> **Cách chọn 0,04.** Không đặt tay. Lấy phân vị 90 của sai số tuyệt đối trên nhóm món mà giả định
> của engine thực sự đúng — nhóm **bảo toàn khối lượng** (`song` / `luoc` / `nau`, n = 124 của bộ
> FNDDS): p50 0,40% · p80 0,94% · **p90 3,61%** · p95 9,91%. Làm tròn lên 4% ⇒ phủ 91,1% nhóm đó.
>
> **Cố ý KHÔNG nới rộng hơn.** Muốn phủ luôn nhóm `hap`/`kho` thì phải nới tới ±20%. Sai lệch của
> hai nhóm đó là **thiên lệch có hệ thống** do thiếu hệ số hao hụt, phải sửa bằng dữ liệu. Nới
> khoảng cho tới khi nó nuốt trọn thiên lệch là biến QT-5 từ công cụ trung thực thành công cụ che
> lỗi — báo cáo đẹp lên mà engine không tốt hơn một chút nào.
>
> Kết quả sau khi sửa (engine 1.1.0, cùng bộ mẫu): % tham chiếu nằm trong khoảng **1,0% → 80,4%**,
> trong khi **% trong ±20% giữ nguyên 89,7%**, MAPE 3,9% và bias −3,8% **không đổi** — vì điểm giữa
> khoảng không đổi. Đó là bằng chứng phép sửa không đụng vào con số tiêu chí chính.

---

## 5b. Hai quyết định hiện thực — bổ sung 2026-08-31 (CALC-001)

Đặc tả gốc không định nghĩa hai trường hợp; cách hiện thực đã chọn:

1. **Nguyên liệu tuỳ chọn (`is_optional`)**: không tính vào `kcal_min`, tính đủ vào
   `kcal_max` — khoảng phản ánh đúng nghĩa "có thể có hoặc không". Macro (cột đơn, không có
   khoảng trong `dish_nutrition`) là ước lượng của công thức đầy đủ gồm cả phần tuỳ chọn.
2. **`fct_source = 'mixed'`** (ngoại lệ có kiểm soát, [DATA-PIPELINE §7.2](../04-data-sources/DATA-PIPELINE.md)):
   engine nhận dữ liệu từ mọi nguồn của từng nguyên liệu, nhưng ép `confidence ≤ medium` —
   đúng cam kết "món dùng nguồn 4–5 phải hạ confidence".

Món chiên: nguyên liệu chưa có `oil_absorption_pct` có nguồn ⇒ vào `missing[]`
(CALC-002 mục 3), không bịa hệ số. Hệ quả thực tế: món chiên chỉ tính được khi nguyên liệu
chính đã tra được hệ số dầu — hiện mới có nhóm khoai/cá/gà điển hình trong tài liệu tham khảo.

## 6. Xác định `confidence`

| Điều kiện | confidence |
|---|---|
| Mọi nguyên liệu ánh xạ `high`, cùng một `fct_source`, chế biến không phải chiên, khẩu phần có cân | `high` |
| Có nguyên liệu ánh xạ `medium`, hoặc chế biến chiên, hoặc khẩu phần ước lượng | `medium` |
| Có nguyên liệu ánh xạ `low`, hoặc `missing[]` chiếm <30% khối lượng | `low` |
| `missing[]` chiếm ≥30% khối lượng, hoặc không có nguyên liệu nào | `unknown` — **không hiển thị số** |

Món ăn ngoài chỉ có `estimated_kcal` (không có nguyên liệu) **luôn** là `low`.

---

## 7. Hiển thị ra giao diện

| confidence | Hiển thị |
|---|---|
| `high` | `≈ 480 kcal` (khoảng hẹp, có thể ẩn) · nhãn "tính từ công thức" |
| `medium` | `≈ 420 – 520 kcal` · nhãn "tính từ công thức, có ước lượng" |
| `low` | `≈ 400 – 600 kcal` · nhãn "ước lượng" · có thể xem nguồn ước lượng |
| `unknown` | "Chưa đủ dữ liệu dinh dưỡng" — **không có số nào** |

**Cấm tuyệt đối:** hiển thị `437 kcal` cho một món ăn ngoài. Một con số lẻ trông chính xác nhưng sai
phá huỷ niềm tin nhanh hơn là một khoảng trung thực (QT-5).

Mọi màn hình hiển thị dinh dưỡng phải có lối vào xem: nguồn bảng thành phần, danh sách nguyên liệu đã
dùng để tính, và các nguyên liệu còn thiếu.

---

## 8. Nhu cầu năng lượng của người dùng (TDEE)

### Công thức BMR

**Mifflin–St Jeor (1990)** — mặc định của dự án. W = kg, H = cm, A = tuổi.

```
Nam:  BMR = 10·W + 6,25·H − 5·A + 5
Nữ:   BMR = 10·W + 6,25·H − 5·A − 161
```

**Harris–Benedict (bản hiệu chỉnh Roza & Shizgal 1984)** — dùng để đối chiếu:

```
Nam:  BMR = 88,362 + 13,397·W + 4,799·H − 5,677·A
Nữ:   BMR = 447,593 + 9,247·W + 3,098·H − 4,330·A
```

**Katch–McArdle** — dùng khi người dùng có % mỡ cơ thể (không phân biệt giới tính):

```
LBM = W × (1 − %mỡ)
RMR = 370 + 21,6 × LBM
```

**Schofield / FAO-WHO-UNU** — chỉ cần cân nặng, có dải cho trẻ em. Dùng khi thiếu chiều cao:

| Tuổi | Nam | Nữ |
|---|---|---|
| <3 | 59,512·W − 30,4 | 58,317·W − 31,1 |
| 3–10 | 22,706·W + 504,3 | 20,315·W + 485,9 |
| 10–18 | 17,686·W + 658,2 | 13,384·W + 692,6 |
| 18–30 | 15,057·W + 692,2 | 14,818·W + 486,6 |
| 30–60 | 11,472·W + 873,1 | 8,126·W + 845,6 |
| >60 | 11,711·W + 587,7 | 9,082·W + 658,5 |

### TDEE = BMR × PAL

> ⚠️ **Đính chính một hiểu lầm phổ biến:** thang hệ số hoạt động 1,2 / 1,375 / 1,55 / 1,725 / 1,9 mà
> mọi app thể hình dùng **không phải bảng của WHO**. Đó là cách đơn giản hoá của ngành fitness.
> FAO/WHO/UNU 2004 chỉ định nghĩa **ba nhóm lối sống với PAL dạng khoảng**:
>
> | Nhóm | PAL |
> |---|---|
> | Ít vận động / vận động nhẹ | 1,40 – 1,69 |
> | Vận động vừa | 1,70 – 1,99 |
> | Vận động mạnh | 2,00 – 2,40 |
>
> **Dự án dùng khoảng của FAO**, không dùng thang 5 mức. Khi trích dẫn, ghi nguồn là FAO chứ không
> phải WHO. Đây chính là lý do TDEE hiển thị dạng khoảng.

### Độ chính xác của chính công thức

Frankenfield và cộng sự (2005, *J Am Diet Assoc*) so với đo thực tế:

| Công thức | Trong ±10% ở người không béo phì | Ở người béo phì |
|---|---|---|
| Mifflin–St Jeor | **82%** | ~70% |
| Harris–Benedict | 69% | 64% |

⇒ **Nhu cầu năng lượng của người dùng đã sai sẵn khoảng 10–15% trước khi tính tới món ăn.** Vì vậy
hiển thị "bạn cần 2.147 kcal/ngày" là con số chính xác giả. Hiển thị đúng:

```
Nhu cầu ước tính: 1.900 – 2.300 kcal/ngày
Đây là ước lượng theo công thức Mifflin–St Jeor, sai số điển hình ±10%.
```

---

## 9. Nguồn tham khảo

| Nội dung | Nguồn |
|---|---|
| Hệ số hao hụt thịt/gia cầm | USDA Table of Cooking Yields for Meat and Poultry, Release 2 |
| Hệ số giữ lại chất dinh dưỡng | USDA Table of Nutrient Retention Factors, Release 6 (2007), DOI 10.15482/USDA.ADC/1409034 |
| Phương pháp tính công thức, chọn hệ số | FAO/INFOODS Guidelines for Converting Units, Denominators and Expressions v1.0 (2012) |
| Phương pháp ánh xạ nguyên liệu | FAO/INFOODS Guidelines for Food Matching v1.2 |
| Dầu hấp thụ khi chiên | PMC10660127; International Journal of Food Science & Technology 43(8):1410 |
| Độ chính xác công thức BMR | Frankenfield D, et al. *J Am Diet Assoc* 2005 |
| Nhóm PAL | FAO, *Human Energy Requirements* (2004), chương 5 |
| Công thức Schofield | FAO/WHO/UNU Expert Committee |
