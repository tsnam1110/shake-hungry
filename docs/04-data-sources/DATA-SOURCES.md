# Nguồn dữ liệu — Lắc Đói

**Cập nhật lần cuối:** 2026-10-31
**Trạng thái:** ✅ Đang vận hành — các nguồn chính đã nhập/tra cứu xong (xem §6). Tài liệu gốc là
kế hoạch thẩm định license ngày 2026-08-31; các đính chính theo thời gian nằm ở §1.1 và §6.

> **Trước khi thêm bất kỳ nguồn mới nào**, đọc [LICENSE-COMPLIANCE](LICENSE-COMPLIANCE.md) và làm
> theo quy trình duyệt nguồn ở §5 của tài liệu đó. Không có ngoại lệ, kể cả cho "chỉ tham khảo".

---

## 1. Phát hiện nền tảng: lỗ hổng dữ liệu Việt Nam

Khảo sát ngày 2026-08-31 kết luận:

> **Không tồn tại bảng thành phần thực phẩm Việt Nam có license mở, và không tồn tại kho công thức
> món Việt có license mở.**

Đây không phải chi tiết nhỏ — nó định hình toàn bộ chiến lược dự án. Cụ thể:

| Nguồn Việt Nam | Thực trạng license | Kết luận |
|---|---|---|
| **Bảng Thành phần Thực phẩm Việt Nam** (Viện Dinh dưỡng – Bộ Y tế). Bản 2007: 501 thực phẩm × 15 thành phần. Bản 2017: ~600 thực phẩm, tới 86 thành phần/100g | Sách có bản quyền, xuất bản qua NXB Y học. **Không có license mở, không có tuyên bố dữ liệu mở nào.** PDF trôi nổi trên mạng (kể cả bản FAO mirror) **không đồng nghĩa được phép dùng** | 🚫 **Không sao chép/nhập hàng loạt.** Quyết định 2026-08-31: không xin phép, chuyển sang **tự tra cứu từng nguyên liệu** — xem đính chính §1.1. **Đính chính 2026-10-31:** được dùng ở **tầng 6** (tra từng mục kèm trích dẫn, gắn cờ "chưa kiểm định") khi nguồn chính thống không có — xem [DATA-PIPELINE §7.2](DATA-PIPELINE.md) |
| **VNeNUTRITION** (web của Viện Dinh dưỡng, có sẵn tra cứu thực phẩm và tra cứu món ăn) | Site **không công bố điều khoản sử dụng, license, hay chính sách API nào** | 🚫 Không có điều khoản = không được phép. Không dùng |
| **ASEAN Food Composition Database e-v1 (2014)**, Mahidol INMU — 616 thực phẩm | © INMU, tải miễn phí nhưng **không nêu license mở**. Chỉ có PDF | ⚠️ Đọc được, không phân phối lại được. Và PDF thì phải bóc tay |
| **Wikidata — món Việt** | CC0 | ✅ Dùng được, nhưng **chỉ ~58 món** có `cuisine = ẩm thực Việt Nam` (79 nếu tính cả ẩm thực vùng) |
| **Open Food Facts — Việt Nam** | ODbL | ✅ Dùng được. **1.583 sản phẩm đóng gói** bán tại VN. Là đồ đóng gói, không phải món ăn |
| **30VNFoods / Vietnamese Foods (Kaggle)** — 25.136 ảnh, 30 loại món | CC BY-**NC**-SA 4.0 | 🚫 Phi thương mại — không dùng |
| Kho công thức tiếng Việt (Cooky.vn, Món Ngon Mỗi Ngày…) | Không có kho nào có license mở | 🚫 Phải tự xây, hoặc xin phép từng bên |

**Hệ quả chiến lược:** nguồn mở cung cấp **rất nhiều dữ liệu dinh dưỡng nhưng rất ít món Việt**. Phần
món Việt buộc phải tự biên soạn. Đó là lý do dự án được tổ chức như một dự án biên soạn dữ liệu.

### 1.1 Đính chính 2026-08-31 — hướng xử lý lỗ hổng này đã đổi

> Bản đầu của tài liệu này kết luận rằng cách lấp lỗ hổng dữ liệu Việt Nam là **xin phép Viện Dinh
> dưỡng** ([LEGAL-001](../05-tasks/tasks/LEGAL-001.md)), và gọi đó là "rủi ro license lớn nhất của
> dự án". **Hướng đó đã bị huỷ.**
>
> Chủ dự án quyết định **tự tra cứu từng nguyên liệu, từng công thức từ các nguồn công khai**, chấp
> nhận tốn thời gian để đổi lấy độ chính xác và **quyền sở hữu dữ liệu**.
>
> **Vì sao hợp lệ:** giá trị dinh dưỡng là **dữ kiện**, không phải tác phẩm. Cái được bảo hộ là
> **bảng biên soạn** của Viện Dinh dưỡng — sự lựa chọn, sắp xếp, tập hợp. Tra từng nguyên liệu từ
> nhiều nguồn công khai, mỗi con số ghi rõ xuất xứ riêng, là **biên soạn độc lập**, khác hẳn với
> việc tái tạo bảng của họ.
>
> **Ranh giới vẫn giữ:** không số hoá hay chép có hệ thống bảng của Viện Dinh dưỡng, kể cả từ bản
> PDF trôi nổi.
>
> Quy trình tra cứu bắt buộc — thứ tự nguồn, quy tắc ghi xuất xứ, cách xử lý nguyên liệu không tra
> được: [DATA-PIPELINE §7](DATA-PIPELINE.md).

---

## 2. Danh sách trắng — nguồn được duyệt dùng

Đã xác minh license ngày 2026-08-31. Cột "Ưu tiên" cho biết dùng ở giai đoạn nào.

| Nguồn | Nội dung dùng | Quy mô | License | Cho phép thương mại | Cho phép phân phối lại | Ưu tiên |
|---|---|---|---|---|---|---|
| **USDA FoodData Central** — Foundation + SR Legacy | Thành phần dinh dưỡng /100g cho nguyên liệu thô | SR Legacy 54 MB (bản cuối 2018); Foundation 29 MB | **Public domain / CC0 1.0** | ✅ | ✅ (khuyến khích ghi nguồn, không bắt buộc) | **GĐ1** |
| **USDA FNDDS + SR Links** | ⭐ **Đồ thị món → nguyên liệu → gram trên 100 g món** | FNDDS 2021–2023, ~1,6 GB CSV | **Public domain / CC0** | ✅ | ✅ | **GĐ1** |
| **Wikidata** | Danh mục món, ẩm thực, tên đa ngôn ngữ, ảnh | 9.365 món tổng; 58–79 món Việt | **CC0 1.0** | ✅ | ✅ | **GĐ1** |
| **USDA Table of Cooking Yields** (Release 2) | Hệ số hao hụt khi nấu thịt/gia cầm | PDF | Public domain | ✅ | ✅ | **GĐ1** |
| **USDA Nutrient Retention Factors** (Release 6, 2007) | Hệ số giữ lại chất dinh dưỡng, 26 chất × ~290 thực phẩm | PDF + CSV | Public domain | ✅ | ✅ | GĐ1 |
| **FAO/INFOODS Guidelines** (Converting Units v1.0; Food Matching v1.2; Data Checking) | **Phương pháp**, không phải dữ liệu — cách chọn hệ số, cách ánh xạ nguyên liệu | PDF | Tài liệu hướng dẫn | Đọc và áp dụng phương pháp | Không chép nội dung | GĐ1 |
| **Nutrition5k** (Google Research) | ~5.000 món thật, mỗi món có nguyên liệu + khối lượng + calo đo bằng thiết bị, kèm ảnh | ~5.000 món | **CC BY 4.0** | ✅ | ✅ (ghi công) | **GĐ1 — dùng để hiệu chuẩn engine** |
| **Open Food Facts** | Sản phẩm đóng gói, gia vị công nghiệp bán tại VN | 4.718.563 sản phẩm; VN: 1.583 | **ODbL 1.0** (dữ liệu) + DbCL (nội dung) + CC BY-SA (ảnh) | ✅ | ⚠️ **Share-alike** — xem cảnh báo §4 | GĐ2 |
| **Wikibooks Cookbook** | Công thức tham khảo (chủ yếu món quốc tế) | 3.825 trang công thức | **CC BY-SA 4.0** | ✅ | ⚠️ Share-alike + ghi công | GĐ5+ (khi mở rộng quốc tế) |
| **ANSES-Ciqual (Pháp)** | Bảng thành phần bổ sung khi USDA thiếu | ~3.000 thực phẩm | Licence Ouverte / Etalab | ✅ | ✅ (ghi công) | Dự phòng |
| **Canadian Nutrient File** | Bảng thành phần + tỷ lệ công thức | ~5.700 thực phẩm | OGL-Canada | ✅ | ✅ (ghi công) | Dự phòng |
| **Danh mục "dược thực đồng nguyên" (药食同源)** — Bộ Y tế Trung Quốc | 106 chất vừa là thực phẩm vừa là dược liệu, **đã có tính + vị chính thức** | 106 mục | Văn bản nhà nước công bố | Dùng làm mồi cho lớp ngũ hành | Ghi rõ nguồn | GĐ6 |

### Vì sao FNDDS + SR Links quan trọng đến vậy

FNDDS có tệp `SR Links` cho biết, với mỗi **mã món ăn** (một món đã chế biến, ví dụ phở bò), danh
sách **mã nguyên liệu** kèm **số gram trên 100 g món**. Đây là một đồ thị món→nguyên liệu→dinh dưỡng
do chính phủ Mỹ duy trì, ở public domain.

Nó **không có món Việt**, nhưng nó cho hai thứ:
1. Một **mẫu tham chiếu về cấu trúc** — biết một món chế biến nên được mô tả bằng bao nhiêu nguyên
   liệu, tỷ lệ điển hình ra sao.
2. Một **bộ dữ liệu để kiểm thử engine tính calo**: chạy engine trên công thức FNDDS rồi so với giá
   trị dinh dưỡng mà USDA công bố cho chính món đó. Nếu engine lệch nhiều thì engine sai, không phải
   dữ liệu sai. Xem [ACCURACY-SPEC §4](../06-nutrition/ACCURACY-SPEC.md).

---

## 3. Danh sách đen — tuyệt đối không nhập

Chi tiết lý do và trích dẫn điều khoản: [LICENSE-COMPLIANCE §2](LICENSE-COMPLIANCE.md).

| Nguồn | Vì sao hấp dẫn | Vì sao cấm |
|---|---|---|
| **RecipeNLG** | 2.231.142 công thức có gán nhãn nguyên liệu | Điều khoản **chỉ phi thương mại/nghiên cứu**, và **ràng buộc cả tổ chức của người dùng** |
| **Recipe1M / Recipe1M+** | 1 triệu công thức + 13 triệu ảnh | Research-only, tải phải đăng ký, ảnh là ảnh scrape của bên thứ ba |
| **Food.com trên Kaggle** (mọi bản) | 180k–500k công thức **có sẵn cột dinh dưỡng** | Scrape từ Food.com, metadata ghi "© Original Authors" = không cấp quyền. Có bản ghi "CC0" nhưng **người upload không có quyền cấp CC0 cho nội dung người khác** |
| **Epicurious (Kaggle)** | 20k công thức có calo | Scrape từ Condé Nast, license "Unknown" |
| **HUMMUS** | 500k công thức + điểm sức khoẻ + liên kết FoodOn | Dẫn xuất từ Food.com đã scrape, không có license |
| **Web Data Commons — schema.org Recipe** | Công thức + dinh dưỡng trích từ Common Crawl | Framework Apache-2.0 nhưng **nội dung trích xuất không được cấp license**; mỗi công thức vẫn thuộc bản quyền nhà xuất bản |
| **Các bảng FAO/INFOODS regional** | Bảng thành phần khu vực châu Á | **CC BY-NC-SA 3.0 IGO** — phi thương mại |
| **30VNFoods** | 25k ảnh món Việt | CC BY-NC-SA 4.0 — phi thương mại |
| **TheMealDB** | API món ăn có sẵn, dễ dùng | Không phải open data. Key miễn phí **không được đưa lên app store**; bán lại bị cấm; điều khoản có thể thay đổi |
| **Spoonacular** | API công thức + dinh dưỡng rất đầy đủ | Điều khoản **cấm lưu trữ dữ liệu**, cache tối đa 1 giờ, ngừng trả tiền là phải xoá sạch |
| **Edamam** | API phân tích dinh dưỡng | Bậc miễn phí chỉ cho cá nhân/phi lợi nhuận; bậc trả phí **chỉ được cache 4 trường** |
| **Nutritionix** | 1,9 triệu mục | Hợp đồng license thương mại, không phải open data |

**Nguyên tắc rút ra:** *API thương mại không phải nguồn dữ liệu.* Dự án này cần **sở hữu** cơ sở dữ
liệu của mình (QT-3); mọi nguồn cấm lưu trữ đều vô dụng với ta, dù dữ liệu có tốt tới đâu.

---

## 4. Cảnh báo share-alike — phải quyết định trước khi nhập

Hai nguồn trong danh sách trắng có ràng buộc **share-alike**:

- **Open Food Facts (ODbL 1.0):** nếu bạn tạo ra một *Derivative Database* (nhập dữ liệu OFF vào
  bảng của mình rồi làm giàu thêm) và **công khai sử dụng** nó, bạn phải phát hành cơ sở dữ liệu dẫn
  xuất đó dưới ODbL. Chỉ *hiển thị* dữ liệu OFF trên giao diện (một "Produced Work") thì chỉ cần ghi
  nguồn.
- **Wikibooks Cookbook (CC BY-SA 4.0):** văn bản dẫn xuất phải giữ CC BY-SA.

⇒ **Câu hỏi Q2 ở [PRD §9](../01-prd/PRD.md) phải được trả lời trước khi nhập hai nguồn này.** Nếu dự
án muốn giữ cơ sở dữ liệu ở license khác, hãy hoặc (a) chấp nhận ODbL cho phần dữ liệu, hoặc (b)
không nhập OFF vào bảng của mình mà chỉ tra cứu lúc hiển thị, hoặc (c) bỏ OFF.

Nhập rồi mới nghĩ tới chuyện này là quá muộn — gỡ dữ liệu ra khỏi một cơ sở dữ liệu đã trộn là gần
như bất khả thi.

---

## 5. Dữ liệu địa điểm quán ăn — hoãn sang v2, và lý do

v1 **không** dùng dữ liệu bản đồ. Món ăn ngoài chỉ là bản ghi món, không gắn địa điểm.

Đo thực tế độ phủ OpenStreetMap tại Việt Nam (Overpass, ảnh chụp 2026-08-31):

| Khu vực | `amenity=restaurant` | `amenity=cafe` | `amenity=fast_food` |
|---|---|---|---|
| Lõi TP.HCM (10,70–10,85 N; 106,60–106,75 E) | 1.845 | 1.293 | 256 |
| Lõi Hà Nội (20,98–21,07 N; 105,78–105,88 E) | 1.324 | — | — |

Thực tế TP.HCM có hàng chục nghìn hàng quán. Phần được map chủ yếu là chuỗi, trung tâm thương mại và
khu du lịch; **quán vỉa hè và quán gia đình — thứ người Việt ăn hằng ngày — gần như không có**. Tag
`cuisine` còn thưa hơn.

**Khi nào cần địa điểm thật (v2+): dùng Overture Maps hoặc Foursquare OS Places, KHÔNG ingest OSM thô.**

| Nguồn | License | Share-alike | Ghi chú |
|---|---|---|---|
| OpenStreetMap / Overpass | ODbL 1.0 | ⚠️ **Có** | Nhập POI của OSM vào bảng của mình rồi làm giàu sẽ kích hoạt nghĩa vụ mở cả cơ sở dữ liệu |
| **Overture Maps — places** | CDLA-Permissive-2.0 (phần lớn), Apache-2.0 (Foursquare), CC0 (AllThePlaces) | ✅ Không | ~74 triệu địa điểm. **Chủ đích loại OSM khỏi lớp places để tránh chính cái bẫy ODbL này** |
| **Foursquare OS Places** | Apache-2.0 | ✅ Không | 100 triệu+ POI. Bộ trên HuggingFace bị gate, phải đăng ký |

> ⚠️ **Chưa xác minh:** độ phủ Việt Nam của Overture và Foursquare **chưa được đo**. Giả thuyết là
> Overture (có nguồn từ Meta) phủ VN tốt hơn OSM vì hàng quán Việt Nam dùng Facebook gần như phổ
> cập — nhưng đây là **giả thuyết cần kiểm chứng**, không phải kết luận. Cách kiểm chứng: chạy DuckDB
> query trên parquet của Overture với bbox Việt Nam. Ghi vào task khi mở v2.

---

## 6. Trạng thái nhập liệu

Cập nhật bảng này mỗi lần nhập một nguồn.

| Nguồn | Trạng thái | Ngày nhập | Phiên bản/ảnh chụp | File trong `data/reference/` | Task |
|---|---|---|---|---|---|
| USDA SR Legacy | ✅ Đã nhập **7.793 dòng** | 2026-08-31 | `sr_legacy_food_csv_2018-04` | `reference/MANIFEST.json` | [DATA-002](../05-tasks/tasks/DATA-002.md) |
| USDA Foundation Foods | ⬜ Chưa nhập (chưa cần — SR Legacy phủ đủ) | — | — | — | [DATA-002](../05-tasks/tasks/DATA-002.md) |
| USDA FNDDS + SR Links | ✅ Đã nhập **333 Food Code / 1.810 dòng SR Links** (chỉ để kiểm chứng engine) | 2026-08-31 | `survey_food_csv_2024-10-31` | `reference/MANIFEST.json` | [DATA-003](../05-tasks/tasks/DATA-003.md) |
| USDA Cooking Yields | ✅ Đã nhập **163 dòng** | 2026-08-31 | — | `reference/` | [DATA-004](../05-tasks/tasks/DATA-004.md) |
| Wikidata — món Việt | ✅ Đã nhập **61 món** (làm vỏ khởi đầu cho DISH-001) | 2026-08-31 | — | `reference/wikidata-vn-dishes.json` | [DATA-005](../05-tasks/tasks/DATA-005.md) |
| Nutrition5k (hiệu chuẩn) | 🔵 Đã dùng **metadata** cho TEST-002 (không lưu vào repo) | 2026-08-31 | — | — (storage/imports) | [TEST-002](../05-tasks/tasks/TEST-002.md) |
| Viện Dinh dưỡng (Bảng TPTP VN) | 🔶 Không nhập hàng loạt; **tra cứu từng mục ở tầng 6** kèm trích dẫn + cờ chưa kiểm định (quyết định 2026-10-31) | 2026-10-31 | — | — | [LEGAL-001](../05-tasks/tasks/LEGAL-001.md) — huỷ; [DATA-PIPELINE §7.2](DATA-PIPELINE.md) |
| ANSES-Ciqual | 🔵 **Chỉ dùng đối chiếu chéo** — không có nguyên liệu nào lấy Ciqual làm nguồn chính | 2026-08-31 | — | — | [ING-001](../05-tasks/tasks/ING-001.md) |
| USDA Branded (nhãn nhà sản xuất) | ✅ Đã dùng cho **3** nguyên liệu | 2026-08-31 | — | — | giò lụa, lạp xưởng, bánh tráng |
| Open Food Facts | ✅ Đã dùng tra cứu nhãn từng sản phẩm **trước khi chốt Q2**; LEGAL-002 đã chốt 2026-10-31: các dòng có nguồn OFF giữ ODbL 1.0 + ghi công trong mô hình collective database | 2026-08-31 | — | — | [LEGAL-002](../05-tasks/tasks/LEGAL-002.md) ✅ |
| UK CoFID | ✅ Đã dùng cho **1** nguyên liệu (cá basa), OGL, duyệt hồi tố | 2026-08-31 | — | — | Chưa đối chiếu file gốc |

> **Lưu ý về bảng này:** các nguồn nhập hàng loạt (USDA, Wikidata) có file + checksum trong
> `data/reference/MANIFEST.json`. Phần còn lại của kho lấy bằng **tra cứu từng nguyên liệu**, giá trị
> lưu thẳng vào `data/ingredients/*.json` dạng bản chụp kèm `external_id` + `source_url`. Xem
> [DATA-MODEL §3.1](../03-data-model/DATA-MODEL.md).
