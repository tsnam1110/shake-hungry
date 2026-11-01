# Tuân thủ license dữ liệu — Lắc Đói

**Cập nhật lần cuối:** 2026-08-31 — bản đầu tiên
**Áp dụng cho:** mọi dữ liệu, mọi thư viện, mọi ảnh đưa vào repo

> Tài liệu này là **bắt buộc**, không phải khuyến nghị. Một dự án mã nguồn mở dính dữ liệu bẩn thì
> không sửa được bằng cách xoá file — lịch sử git vẫn còn, và bản fork của người khác vẫn còn.

---

## 1. Nguyên tắc

1. **Không có license = không được dùng.** "Tải được" không phải "được phép". "Không thấy ai cấm"
   không phải "được cho phép". Mặc định của luật bản quyền là **cấm**, không phải cho.
2. **Người upload không thể cấp quyền mà họ không có.** Một dataset trên Kaggle ghi "CC0" nhưng nội
   dung là scrape từ site khác thì tuyên bố CC0 đó **vô hiệu**.
3. **Phi thương mại (NC) = không dùng.** Kể cả khi dự án hiện tại phi lợi nhuận. Ràng buộc NC làm
   dự án không thể đổi hướng, và với RecipeNLG thì nó còn ràng buộc cả tổ chức của người dùng.
4. **API cấm lưu trữ = không phải nguồn dữ liệu.** Dự án cần sở hữu cơ sở dữ liệu của mình (QT-3).
5. **Ghi lại quyết định.** Mỗi nguồn được duyệt hay bị từ chối đều để lại dấu vết ở §6.

---

## 2. Danh sách đen — cấm tuyệt đối

Đã xác minh 2026-08-31. **Không nhập, không chép, không "chỉ tham khảo nội bộ"** — vì một khi vào
repo là đã phân phối lại.

### 2.1 Dataset công thức

| Nguồn | Điều khoản thực tế | Ghi chú |
|---|---|---|
| **RecipeNLG** (2.231.142 công thức) | Thoả thuận tải ghi rõ: người dùng **chỉ được dùng cho mục đích nghiên cứu và giáo dục phi thương mại**; và nếu người dùng làm việc cho tổ chức vì lợi nhuận thì **tổ chức đó cũng bị ràng buộc** | Đây là cái hấp dẫn nhất và cũng là bẫy lớn nhất. Ngoài ra dữ liệu gốc là scrape từ các site công thức |
| **Recipe1M / Recipe1M+** | Tải phải đăng ký, điều khoản research-only | 13 triệu ảnh là ảnh của bên thứ ba |
| **Food.com — `shuyangli94`** (Kaggle) | Metadata: "Data files © Original Authors" → **không cấp quyền gì** | Scrape từ Food.com |
| **Food.com — `irkaal`** (Kaggle) | Metadata ghi "CC0: Public Domain" | ⚠️ **Tuyên bố vô hiệu** — người upload không sở hữu nội dung Food.com nên không thể đặt nó vào public domain |
| **Epicurious — `hugodarwood`** (Kaggle) | Metadata: "Unknown" | Scrape từ Condé Nast |
| **HUMMUS** | Không tìm thấy license | Dẫn xuất từ Food.com |
| **Web Data Commons — schema.org Recipe** | Framework trích xuất là Apache-2.0, **nội dung trích xuất không được cấp license** | Dùng được cho thống kê/xây ontology; **không** để tái xuất bản nội dung công thức |

### 2.2 Bảng thành phần / dữ liệu phi thương mại

| Nguồn | License | |
|---|---|---|
| **FAO/INFOODS** — các bảng thành phần khu vực, bảng đa dạng sinh học | CC BY-**NC**-SA 3.0 IGO | 🚫 NC |
| **30VNFoods / Vietnamese Foods** (Kaggle, 25.136 ảnh) | CC BY-**NC**-SA 4.0 | 🚫 NC |
| **EuroFIR / FoodEXplorer** | Hợp đồng thành viên, không phải license mở. Công bố phải xin duyệt trước 28 ngày | 🚫 |

> **Lưu ý:** *tài liệu hướng dẫn* của FAO/INFOODS (Converting Units, Food Matching, Data Checking)
> vẫn **đọc và áp dụng phương pháp được** — phương pháp không phải dữ liệu, và ý tưởng không có bản
> quyền. Cấm là cấm chép **bảng số liệu** của họ.

### 2.3 API thương mại — không phải nguồn dữ liệu

| Nguồn | Điều khoản chặn ta |
|---|---|
| **Spoonacular** | Cấm scrape/sao chép/lưu trữ, kể cả dữ liệu đã biến đổi hoặc hash. Cache tối đa **1 giờ** và phải xin phép trước. Ngừng trả tiền là phải xoá toàn bộ |
| **Edamam** | Bậc miễn phí chỉ cho cá nhân/phi lợi nhuận. Bậc trả phí chỉ được cache **4 trường** (đạm, béo, net carb, calo), lưu theo từng người dùng cuối, sau xác thực. Cấm thu thập |
| **Nutritionix** | Hợp đồng license cơ sở dữ liệu riêng |
| **TheMealDB** | Không phải open data. Key miễn phí **không được dùng cho app phát hành trên store**; cấm bán lại; bản quyền ảnh do họ giữ |

### 2.4 Mã nguồn không được fork

| Project | License thực tế | Vì sao cấm |
|---|---|---|
| **Tandoor Recipes** (8,6k sao) | **AGPL-3.0 + Commons Clause** | ⚠️ **Không phải mã nguồn mở.** Commons Clause loại bỏ quyền "bán phần mềm", trong đó **bao gồm cả thu phí hosting hay tư vấn/hỗ trợ**. Không thể fork vào dự án có license khác |
| **Mealie** (13,1k sao) | AGPL-3.0 | Copyleft mạng: phục vụ người dùng qua web kích hoạt nghĩa vụ công bố mã của **toàn bộ** tác phẩm dẫn xuất |
| **KitchenOwl** | AGPL-3.0 | như trên |
| **Nextcloud Cookbook** | AGPL-3.0 | như trên, lại còn gắn chặt với Nextcloud |
| **RecipeSage** | AGPL phi thương mại / trả phí cho thương mại + **CLA bắt buộc chuyển nhượng đóng góp** | Xấu nhất về pháp lý |

**Được phép:** đọc mã nguồn của chúng để **học thiết kế**, rồi tự viết lại. Ý tưởng và kiến trúc
không có bản quyền; mã nguồn thì có. Cụ thể xem [§4](#4-tái-sử-dụng-mã-nguồn-cách-làm-đúng).

---

## 3. Danh sách trắng — được duyệt

| Nguồn | License | Nghĩa vụ khi dùng |
|---|---|---|
| USDA FoodData Central (Foundation, SR Legacy, FNDDS, SR Links) | **CC0 / public domain** | Không bắt buộc gì. Vẫn nên ghi nguồn |
| USDA Cooking Yields, Nutrient Retention Factors | Public domain | như trên |
| Wikidata | **CC0 1.0** | Không bắt buộc |
| Nutrition5k (Google Research) | **CC BY 4.0** | **Bắt buộc ghi công** |
| ANSES-Ciqual | Licence Ouverte / Etalab | Ghi công |
| Canadian Nutrient File | OGL-Canada | Ghi công |
| Open Food Facts | **ODbL 1.0** + DbCL + ảnh CC BY-SA | ⚠️ **Share-alike** — xem §5. **ĐÃ DÙNG cho 5 nguyên liệu** (2026-08-31), chờ quyết định [LEGAL-002](../05-tasks/tasks/LEGAL-002.md) |
| **UK CoFID** (McCance & Widdowson, bảng thành phần quốc gia Anh) | **Open Government Licence (OGL)** | Ghi công. Bổ sung vào danh sách trắng 2026-08-31 — xem §7 |
| Wikibooks Cookbook | **CC BY-SA 4.0** | ⚠️ Share-alike + ghi công |
| Overture Maps places (v2) | CDLA-Permissive-2.0 / Apache-2.0 / CC0 | Ghi công theo `docs.overturemaps.org/attribution` |
| Foursquare OS Places (v2) | Apache-2.0 | Ghi công |

### Thư viện mã nguồn được phép dùng

| Thư viện | License | Dùng làm gì |
|---|---|---|
| `recipe-scrapers` | MIT | ⚠️ Không có site Việt Nam — chỉ hữu ích nếu mở rộng quốc tế |
| `ingredient-parser-nlp` | MIT | ⚠️ Chỉ tiếng Anh — xem [ARCHITECTURE §3](../02-architecture/ARCHITECTURE.md) |
| `extruct` | BSD-3-Clause | Trích JSON-LD/Microdata, chỉ cần khi crawl |
| Cooklang / `cooklang-rs` | MIT | Định dạng công thức dạng text thuần, nếu cần |
| Grocy | MIT | Tham khảo mô hình tồn kho "nấu được gì từ đồ đang có" |

---

## 4. Tái sử dụng mã nguồn: cách làm đúng

Trả lời câu hỏi gốc của dự án — *"tận dụng open source được không?"*:

> **Được, nhưng ở tầng thư viện và tầng thiết kế, không phải tầng fork ứng dụng.**

| Cần gì | Lấy từ đâu | Cách lấy |
|---|---|---|
| Engine tính dinh dưỡng cho công thức | **Thiết kế** của Tandoor `FoodPropertyHelper` — duyệt công thức → nguyên liệu → thuộc tính/100g → quy đổi đơn vị → báo cả `total_value` lẫn `missing_value` | **Đọc, hiểu, tự viết lại bằng PHP.** Không chép mã |
| Mô hình dữ liệu thực phẩm | **Thiết kế** của Tandoor: `Food` (cây, có `fdc_id`), `PropertyType`, `UnitConversion` | Như trên |
| Bộ chọn món ngẫu nhiên có luật | **Thiết kế** của Mealie: các luật khớp `(thứ, loại bữa)`, AND filter, query `order_by=random` có seed | ~60 dòng. **Tự viết** — không fork AGPL vì 60 dòng |
| Kiến trúc parser nguyên liệu nhiều tầng | **Thiết kế** của Mealie: một interface, nhiều bản cài (brute-force / NLP / LLM) | Tự viết |
| Danh mục các định dạng công thức tồn tại trên đời | Danh sách ~26 importer của Tandoor | Dùng như một bản khảo sát, không chép mã |

> **Cảnh báo về Mealie:** Mealie **không hề tính dinh dưỡng**. Bảng `recipe_nutrition` của nó lưu
> calo/đạm/béo dưới dạng **chuỗi ký tự chép từ markup schema.org**, không đơn vị, không tính toán,
> không truy nguyên được về nguyên liệu. **Không bắt chước mô hình này** — nó chính là thứ dự án ta
> tồn tại để làm tốt hơn.

---

## 5. Bẫy share-alike — phải quyết trước, không sửa được sau

**ODbL (Open Food Facts):**
- Hiển thị dữ liệu OFF lên giao diện = "Produced Work" → chỉ cần ghi nguồn. ✅
- Nhập dữ liệu OFF vào bảng của mình, làm giàu thêm, rồi công khai sử dụng = "Derivative Database"
  → **phải phát hành cơ sở dữ liệu dẫn xuất đó dưới ODbL**. ⚠️

**CC BY-SA (Wikibooks, DBpedia):** văn bản dẫn xuất phải giữ CC BY-SA.

**Việc phải làm trước khi nhập hai nguồn này:** trả lời câu hỏi Q2 ở [PRD §9](../01-prd/PRD.md) —
license của chính dự án là gì. Ba lựa chọn:

| Lựa chọn | Hệ quả |
|---|---|
| (a) Chấp nhận ODbL cho phần cơ sở dữ liệu | Đơn giản nhất. Dữ liệu mở, ai cũng dùng lại được với cùng điều kiện |
| (b) Không nhập OFF vào bảng của mình, chỉ tra cứu lúc hiển thị | Giữ tự do cho cơ sở dữ liệu, nhưng phụ thuộc mạng và mâu thuẫn một phần với QT-3 |
| (c) Bỏ hẳn OFF | Mất 1.583 sản phẩm đóng gói ở VN. Với v1 tập trung món nấu, mất mát không lớn |

Khuyến nghị cho v1: **(c) hoặc (a)**. Đừng nhập rồi mới nghĩ.

**KẾT LUẬN — đã chốt tại [LEGAL-002](../05-tasks/tasks/LEGAL-002.md) ngày 2026-10-31:**
mã nguồn theo **MIT**; cơ sở dữ liệu phát hành dạng **collective database**: phần tự biên
soạn theo **CC BY 4.0**, các dòng có nguồn Open Food Facts (đã tra cứu từng nhãn từ trước,
nhận diện qua `license_note` trong từng file nguyên liệu) giữ **ODbL 1.0** kèm ghi công.
Dự án **chưa bao giờ nhập hàng loạt** OFF hay Wikibooks; Wikibooks không dùng cho v1.
Phương án (b) thuần tuý (không dòng OFF nào) bị loại vì thực tế đã có vài dòng nhãn tra
qua OFF từ đợt ING-001 — thay thế chúng bằng nhãn tự chụp được giữ làm phương án dự phòng
nếu sau này cần loại bỏ hoàn toàn bề mặt ODbL.

---

## 5b. Chính sách lưu trữ nguồn hạn chế cho lớp tính vị (chốt 2026-11-01, TCM-000)

Chủ dự án chốt: dữ liệu của dự án tuy nằm trong kho mã nguồn mở nhưng **chủ dự án chỉ dùng
cho lưu trữ thông tin và lưu hành nội bộ**. Với lớp tính vị (Mức 3), khi một nguồn **uy tín**
(Dược điển Trung Quốc, CMRO Hồng Kông, bộ dữ liệu học thuật…) mà điều khoản ghi hạn chế sử
dụng hoặc tái phân phối, vẫn được **lưu vào thư viện** — vì việc lưu trữ nội bộ để tra cứu
không phải là hành vi phân phối — với kỷ luật chặt:

1. Dòng nhãn lấy từ nguồn đó **bắt buộc** mang `usage_restricted: true` + `usage_note` ghi rõ
   **"không được dùng"** và lý do điều khoản (validator V20 + CHECK ở DB cưỡng chế).
2. Ghi chú phải đi kèm dữ liệu **ở mọi bề mặt** dữ liệu xuất hiện (file, API, trang hiển thị);
   không có ngoại lệ "chỉ hiển thị nội bộ nên bỏ qua".
3. **Chính sách hiển thị công khai** cho dữ liệu mang cờ này chốt riêng **trước go-live** —
   lựa chọn mặc định an toàn khi đó là ẩn mọi dòng mang cờ khỏi bản công khai và chỉ giữ
   trong thư viện nội bộ, trừ khi thẩm định lại điều khoản cho phép khác.
4. Chính sách này **không** áp dụng cho dữ liệu dinh dưỡng hay công thức: các bảng cấm ở
   §2 vẫn cấm tuyệt đối việc nhập dữ liệu của chúng vào kho.

## 6. Quy trình duyệt nguồn mới

Bất kỳ ai (người hay AI agent) muốn thêm một nguồn dữ liệu:

1. **Tìm license thật.** Không chấp nhận metadata của Kaggle làm bằng chứng — mở LICENSE gốc, điều
   khoản gốc, hoặc trang chính thức của nhà cung cấp.
2. **Trả lời 5 câu hỏi:**
   - Có cho dùng thương mại không?
   - Có cho phân phối lại không?
   - Có ràng buộc share-alike không?
   - Có bắt buộc ghi công không? Ghi ở đâu?
   - Người cấp license có thực sự sở hữu nội dung không?
3. **Bất kỳ câu nào không rõ ⇒ coi như KHÔNG.** Ghi "unclear", không ghi "chắc là được".
4. **Ghi vào bảng §7 bên dưới**, kể cả khi bị từ chối — để người sau không tra lại từ đầu.
5. **Cập nhật [DATA-SOURCES](DATA-SOURCES.md)** nếu được duyệt.
6. Lưu bản sao điều khoản tại `data/reference/_licenses/<tên-nguồn>.txt` kèm ngày tải — điều khoản
   online có thể thay đổi.

---

## 7. Nhật ký thẩm định

| Ngày | Nguồn | Kết luận | Người/agent thẩm định | Ghi chú |
|---|---|---|---|---|
| 2026-08-31 | USDA FDC (Foundation, SR Legacy, FNDDS, SR Links) | ✅ Duyệt | Khảo sát khởi tạo dự án | CC0, không ràng buộc |
| 2026-08-31 | USDA FDC **SR Legacy — ĐÃ NHẬP** ([DATA-002](../05-tasks/tasks/DATA-002.md)) | ✅ Nhập 7.793 dòng | `lacdoi:import-usda` | Bản `sr_legacy_food_csv_2018-04.zip`, SHA-256 trong `data/reference/MANIFEST.json`; bản sao điều khoản tại `data/reference/_licenses/usda-fdc.txt` |
| 2026-08-31 | Wikidata | ✅ Duyệt | — | CC0. Chỉ 58–79 món Việt |
| 2026-08-31 | Nutrition5k | ✅ Duyệt | — | CC BY 4.0, phải ghi công |
| 2026-08-31 | Nutrition5k — **ĐÃ DÙNG** (metadata) | ✅ Ghi công xong | TEST-002 | Chỉ tải 3 file metadata (~2,3 MB) vào `storage/imports/` (không commit — không cần cho việc dựng lại). Ghi công đã thêm vào README §Ghi công nguồn dữ liệu kèm trích dẫn bài báo CVPR 2021. ⚠️ Phát hiện: calo của bộ này **không đo độc lập** mà tính từ chính bảng nguyên liệu của nó — xem đính chính [ACCURACY-SPEC §4](../06-nutrition/ACCURACY-SPEC.md) |
| 2026-08-31 | USDA FDC **Survey (FNDDS) + SR Links — ĐÃ NHẬP** ([DATA-003](../05-tasks/tasks/DATA-003.md)) | ✅ Nhập 333 Food Code / 1.810 dòng | `lacdoi:import-usda --source=fndds` | Bản `survey_food_csv_2024-10-31`, CC0. Dùng **chỉ để kiểm chứng engine**, không phải món hiển thị |
| 2026-08-31 | **Wikidata — ĐÃ NHẬP** ([DATA-005](../05-tasks/tasks/DATA-005.md)) | ✅ 61 món, status=draft | `lacdoi:import-wikidata` | CC0. Kết quả thô + câu SPARQL + ngày truy vấn lưu ở `data/reference/wikidata-vn-dishes.json`. **Ảnh chưa tải về** — license từng ảnh Commons khác nhau, chờ Q4 |
| 2026-08-31 | Open Food Facts | ✅ **Đã chốt 2026-10-31 (LEGAL-002)**: giữ ODbL 1.0 cho các dòng có nguồn OFF trong mô hình collective database, ghi công OFF; trạng thái cũ: ⚠️ **Đã dùng trước khi chốt Q2** | Đợt tra cứu ING-001 | ⚠️ **Ghi nhận sai quy trình:** OFF đang ở trạng thái "⏸️ hoãn chờ Q2" nhưng đã được dùng cho 4 nguyên liệu (`nuoc-mam-truyen-thong`, `nuoc-mam-cong-nghiep`, `tuong-ot`, `sua-dac`, `nem-chua`) — vì đây là nguyên liệu USDA không phủ được và nhãn sản phẩm Việt là nguồn duy nhất tra được. Mỗi file đã ghi `license_note` cảnh báo. **Phải chốt [LEGAL-002](../05-tasks/tasks/LEGAL-002.md) trước khi phát hành**, nếu không chấp nhận ODbL thì thay bằng ảnh nhãn tự chụp |
| 2026-08-31 | **ANSES-Ciqual** | ✅ Đã dùng (đã có trong danh sách trắng §3) | Đợt tra cứu ING-002 | Dùng làm nguồn CHÍNH cho 2 nguyên liệu mà USDA không có: `tiet-lon` (tiết bò — sai loài, độ tin cậy Ciqual D) và `pate-gan` (pâté de foie de porc — đúng loài, độ tin cậy A cho đạm/béo/natri). Licence Ouverte/Etalab, cho phép thương mại + phân phối lại, có ghi công. ⚠️ **Phát hiện quan trọng:** Ciqual đôi khi CHÉP LẠI USDA — vịt không da: Ciqual 36201 và USDA 172410 có đạm/béo/nước GIỐNG HỆT từng chữ số. Không coi là hai xác nhận độc lập |
| 2026-08-31 | **MEXT Japan** — Standard Tables of Food Composition in Japan, 8th rev. (`fooddb.mext.go.jp`) | ⏸️ **Chờ thẩm định — CHƯA dùng** | Đợt tra cứu ING-002 | Agent tra cứu tìm được **5 nguyên liệu** chỉ có ở nguồn này: cá nục (*Decapterus muroadsi*, đúng chi), ghẹ (*Portunus trituberculatus*, đúng chi), mực khô, cá cơm khô (niboshi), sò huyết (*Anadara broughtonii*, đúng chi). Chất lượng ánh xạ loài **tốt hơn hẳn** mọi ứng viên USDA. **Số liệu đang GIỮ NGOÀI repo** cho tới khi đối chiếu 政府標準利用規約 (Điều khoản sử dụng chuẩn của Chính phủ Nhật). Đây là lần đầu quy trình §6 được tuân thủ đúng thứ tự — thẩm định **trước**, không phải sau |
| 2026-08-31 | **UK CoFID** (McCance & Widdowson 2021) | ✅ Duyệt **hồi tố** | Đợt tra cứu ING-001 | ⚠️ **Ghi nhận sai quy trình:** đã dùng cho `ca-basa` **trước khi** qua quy trình duyệt nguồn ở §6. Thẩm định sau cho thấy license là **Open Government Licence (OGL)** — cho phép thương mại và phân phối lại, có ghi công → hợp lệ. Nhưng thứ tự làm ngược. Lưu ý thêm: giá trị đọc qua nutridex.org.uk (nguồn thứ cấp), **chưa đối chiếu file CoFID gốc** |
| 2026-08-31 | Wikibooks Cookbook | ❌ **Không dùng cho v1** (chốt 2026-10-31, LEGAL-002) | — | Chưa từng nhập dòng nào. Chỉ xét lại khi mở rộng quốc tế |
| 2026-08-31 | Bảng TPTP Việt Nam (Viện Dinh dưỡng) | ⏸️ Chờ giấy phép *(trạng thái tại thời điểm đó — đã được thay bằng quyết định tầng 6 ngày 2026-10-31 ở cuối bảng này)* | — | Sách có bản quyền. Xem [LEGAL-001](../05-tasks/tasks/LEGAL-001.md) |
| 2026-08-31 | VNeNUTRITION | ⏸️ Chờ giấy phép | — | Site không công bố điều khoản nào |
| 2026-08-31 | ASEAN FCD 2014 (INMU) | ❌ Từ chối | — | © INMU, không có license mở, chỉ có PDF |
| 2026-08-31 | RecipeNLG | ❌ Từ chối | — | NC + ràng buộc cả tổ chức |
| 2026-08-31 | Recipe1M/1M+ | ❌ Từ chối | — | Research-only |
| 2026-08-31 | Food.com (mọi bản Kaggle) | ❌ Từ chối | — | Scrape, không có quyền |
| 2026-08-31 | Epicurious (Kaggle) | ❌ Từ chối | — | License "Unknown" |
| 2026-08-31 | HUMMUS | ❌ Từ chối | — | Dẫn xuất Food.com |
| 2026-08-31 | WDC schema.org Recipe | ❌ Từ chối | — | Nội dung không được cấp license |
| 2026-08-31 | FAO/INFOODS (bảng số liệu) | ❌ Từ chối | — | CC BY-NC-SA 3.0 IGO |
| 2026-08-31 | 30VNFoods | ❌ Từ chối | — | CC BY-NC-SA 4.0 |
| 2026-08-31 | TheMealDB / Spoonacular / Edamam / Nutritionix | ❌ Từ chối | — | API thương mại, cấm lưu trữ |
| 2026-08-31 | Tandoor (fork) | ❌ Từ chối | — | AGPL + Commons Clause |
| 2026-08-31 | Mealie / KitchenOwl / Nextcloud Cookbook (fork) | ❌ Từ chối | — | AGPL-3.0 |
| 2026-08-31 | RecipeSage (fork) | ❌ Từ chối | — | Phi thương mại + CLA |
| 2026-10-31 | Bảng TPTP Việt Nam (Viện Dinh dưỡng), MEXT (Nhật), IFCT (Ấn Độ), Thai FCT, FNRI (Philippines), văn liệu khoa học | 🔶 **Duyệt có điều kiện — tầng thứ cấp** theo quyết định chủ dự án | Chủ dự án + đợt thu thập 2026-10-31 | Chỉ tra TỪNG nguyên liệu khi nguồn chính thống không có; ghi trích dẫn từng số; đánh dấu "chưa kiểm định" và hiển thị cảnh báo trên giao diện. Vẫn CẤM nhập/chép hàng loạt toàn bộ bảng (mục §2 giữ nguyên cho hành vi sao chép có hệ thống). Số liệu dinh dưỡng là dữ kiện thực tế; việc biên soạn độc lập từng mục có xuất xứ riêng là cơ sở pháp lý của việc dùng này (xem §7.1 DATA-PIPELINE) |
| 2026-11-01 | **Chính sách lưu trữ nguồn hạn chế — lớp tính vị (Mức 3)** | 🔶 **Duyệt theo chính sách §5b** — lưu trữ nội bộ, không phân phối | Chủ dự án, qua TCM-000 | Nguồn tính vị uy tín (Dược điển TQ, CMRO, ETCM/TCMSP…) có thể mang điều khoản hạn chế; theo chính sách §5b: vẫn lưu vào thư viện kèm cờ `usage_restricted` + ghi chú "không được dùng" gắn tại từng dòng nhãn; chính sách hiển thị công khai chốt trước go-live. Thẩm định điều khoản từng nguồn tính vị cụ thể ghi ở các dòng nhật ký tiếp theo khi TCM-001a tra cứu |
| 2026-11-01 | CMRO Hồng Kông — ấn phẩm 《中藥趣談》 tập 1–2 (Phòng Quản lý Trung y dược, Sở Y tế HK) | 🔶 **Lưu kèm cờ `usage_restricted`** theo §5b — không được dùng hiển thị công khai | TCM-001a tra cứu trực tiếp ấn phẩm | Trang cuối tập 2 ghi nội dung/hình ảnh thuộc bản quyền CMRO, cấm sao chép khi chưa có đồng ý bằng văn bản. 4 dòng nhãn gieo từ nguồn này (hoa-hoi, rau-diep-ca, tao-tau, tao-tau-kho) đều mang cờ + `usage_note` "không được dùng"; giá trị tính/vị được Dược điển TQ corroborate độc lập ở mọi mục |
| 2026-11-01 | Sổ tay 《癌症病人照顧者全面手冊》 (Hồng Kông), chương 中醫食療——食物種類分析 — nội dung TS. Lâm Đạo Nghi (thầy thuốc Trung y đăng ký), chốt bản 2022-04 | ✅ Dùng làm nguồn tầng 3 cho TCM-001b — ghi trích dẫn từng dòng nhãn | TCM-001b tra cứu + census mở lại toàn văn | Tài liệu giáo dục sức khoẻ công cộng của tổ chức hỗ trợ bệnh nhân ung thư HK; dự án chỉ lưu giá trị 性味 + trích dẫn mục + URL, không sao chép văn bản. 32 dòng nhãn / 27 nguyên liệu |
| 2026-11-01 | A+醫學百科 (a-hospital.com) — wiki y học biên soạn theo 《中藥大辭典》/《全國中草藥彙編》/《中華本草》 | ✅ Dùng làm nguồn tầng 3 cho TCM-001b — bắt buộc đọc toàn trang (trang thường chứa 2 giá trị lệch nhau, gieo cả hai) | TCM-001b tra cứu + census | Nội dung wiki tổng hợp từ từ điển đã xuất bản có ghi nguồn từng mục; dự án lưu giá trị + trích dẫn + URL trang. 14 dòng / 8 nguyên liệu. Không thấy điều khoản hạn chế phân phối giá trị tham chiếu; nếu trang thay đổi điều khoản, áp chính sách §5b |
| 2026-11-01 | 《本草綱目》 (Lý Thời Trân, đời Minh) — bản toàn văn số hoá arteducation.com.tw | ✅ Văn bản cổ đã hết hạn bản quyền — dùng trực tiếp | TCM-001b | 1 dòng (trung-ga). Chỉ lưu giá trị + trích dẫn mục |
| 2026-11-01 | Y văn Việt Nam: Đỗ Tất Lợi — *Những cây thuốc và vị thuốc Việt Nam* (bản trực tuyến thaythuoccuaban.com); Đỗ Huy Bích và cộng sự — *Cây thuốc và động vật làm thuốc ở Việt Nam* (NXB KH&KT, trích qua eva.vn); bản số hoá sách/bài tại thuvienso.com.vn + lib.vn; báo Sức khỏe & Đời sống; Nhà thuốc Long Châu | ✅ Dùng làm nguồn tầng 3–4 cho TCM-001b — confidence low–medium vì đọc qua bản tái bản/trích dẫn trực tuyến | TCM-001b tra cứu + census | Dự án chỉ lưu giá trị tính vị + câu trích nguyên văn + URL kiểm chứng, không sao chép nội dung sách/bài. 9 dòng / 6 nguyên liệu |
