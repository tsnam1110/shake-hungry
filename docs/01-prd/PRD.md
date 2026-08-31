# PRD — Lắc Đói

**Phiên bản:** 1.0
**Ngày tạo:** 2026-08-31
**Cập nhật lần cuối:** 2026-08-31 — bản đầu tiên
**Trạng thái:** Đã chốt phạm vi v1

---

## 1. Vấn đề

Câu hỏi "hôm nay ăn gì?" lặp lại 3–4 lần mỗi ngày và tốn năng lượng ra quyết định một cách vô ích.
Các ứng dụng hiện có giải quyết nửa vời:

- App công thức nấu ăn (Cookpad, Mealie…) yêu cầu người dùng **đã biết mình muốn nấu gì**.
- App đếm calo (MyFitnessPal…) chỉ ghi nhận **sau khi đã ăn**, không gợi ý.
- App đặt đồ ăn gợi ý theo **quảng cáo trả tiền**, không theo nhu cầu dinh dưỡng.
- Gần như không có gì phục vụ ngữ cảnh Việt Nam: món theo vùng miền, thói quen ăn ngoài phổ biến,
  cách phân loại món theo bữa của người Việt.

## 2. Giải pháp

Một nút. Bấm vào ra một món phù hợp với: thời điểm hiện tại, món đã ăn gần đây, hồ sơ thể trạng, và
các bộ lọc người dùng chọn (quốc gia, vùng miền, ăn ngoài / tự nấu).

Nếu là món tự nấu → có công thức và calo tính từ nguyên liệu.
Nếu là món ăn ngoài → có khoảng calo ước lượng kèm mức tin cậy.

## 3. Bản chất kỹ thuật của dự án — đọc kỹ

> **Đây là dự án biên soạn cơ sở dữ liệu, không phải dự án web app.**

Kết luận này rút ra từ khảo sát mã nguồn mở và dữ liệu mở ngày 2026-08-31:

1. **Engine gợi ý không phải phần khó.** Toàn bộ danh mục dự án mã nguồn mở về "meal recommender" là
   đồ án đã ngừng phát triển (`meal-planner` dừng 2016, `yumme` của Cornell dừng 2020). Bản triển
   khai đạt chuẩn sản phẩm duy nhất nằm trong Mealie và chỉ gồm vài chục dòng: các luật khớp theo
   `(thứ trong tuần, loại bữa)`, AND các filter lại, rồi query random có seed.
2. **Không tồn tại thư viện làm chuỗi `text nguyên liệu → tra bảng → tính kcal`.** Mọi dự án đều tự
   ghép từ các mảnh. Đây là phần phải tự xây.
3. **Không tồn tại dữ liệu món Việt có license mở.** Không có bảng thành phần thực phẩm Việt Nam mở,
   không có kho công thức món Việt mở. Wikidata chỉ có ~58–79 món Việt.

⇒ Lợi thế cạnh tranh duy nhất là **dữ liệu tự biên soạn** và **engine tính calo**. Mọi quyết định ưu
tiên phải phục vụ hai thứ đó.

## 4. Người dùng mục tiêu

| Nhóm | Nhu cầu chính | Ưu tiên |
|---|---|---|
| Người Việt đi làm, ăn ngoài là chính | Hết nghĩ "trưa nay ăn gì", muốn đổi món | **v1** |
| Người nấu ăn tại nhà | Gợi ý món + công thức theo bữa | **v1** |
| Người theo dõi calo | Biết món mình định ăn khoảng bao nhiêu calo | **v1** |
| Người ăn theo chế độ (chay, kiêng đạm, tiểu đường) | Lọc món phù hợp | v2 |
| Người quan tâm âm dương / ngũ hành | Chọn món theo tính vị, theo mùa | v2 (tuỳ chọn) |

**Người dùng nội bộ quan trọng nhất ở v1: chính đội biên soạn dữ liệu.** Công cụ nhập liệu phục vụ
họ là sản phẩm được ưu tiên cao hơn giao diện người dùng cuối. Xem [ROADMAP](../05-tasks/ROADMAP.md)
Giai đoạn 2.

## 5. Phạm vi v1

### Có

- Danh mục món ăn Việt Nam, phân theo vùng miền (Bắc / Trung / Nam, chi tiết tới tỉnh nếu có).
- Phân loại món theo bữa: sáng / trưa / chiều / tối / ăn vặt.
- Phân loại hình thức: **ăn ngoài** hoặc **tự nấu** (một món có thể thuộc cả hai).
- Món tự nấu: công thức, nguyên liệu có định lượng, calo và macro tính từ bảng thành phần.
- Món ăn ngoài: khoảng calo ước lượng + mức tin cậy + nguồn ước lượng.
- Nút Lắc: gợi ý ngẫu nhiên có điều kiện, loại trừ N món vừa ăn.
- Hồ sơ người dùng: chiều cao, cân nặng, tuổi, giới tính, mức vận động → khoảng TDEE.
- Lịch sử bữa ăn (người dùng tự đánh dấu "đã ăn món này").
- Khu vực quản trị: công cụ nhập liệu tối ưu tốc độ, có trợ lý LLM.

### Không có ở v1 — và lý do

| Không làm | Lý do |
|---|---|
| **Bản đồ / tìm quán gần đây** | Đã đo độ phủ OpenStreetMap tại VN ngày 2026-08-31: lõi TP.HCM chỉ có 1.845 nhà hàng, lõi Hà Nội 1.324 — trong khi thực tế có hàng chục nghìn. Quán vỉa hè gần như không được map. Dữ liệu quá mỏng để làm tính năng chính. Xem [DATA-SOURCES §5](../04-data-sources/DATA-SOURCES.md) |
| **Món ăn quốc tế** | Schema thiết kế đa quốc gia sẵn, nhưng dữ liệu v1 chỉ làm món Việt để đảm bảo chất lượng. Mở rộng sau khi Việt Nam xong |
| **Ứng dụng di động** | v1 là web responsive / PWA |
| **Đăng nhập mạng xã hội, chia sẻ, mạng xã hội món ăn** | Không phục vụ mục tiêu chính |
| **Đóng góp từ người dùng ở quy mô lớn** | Đóng góp là **kênh phụ** (v1 chỉ có form đơn giản + hàng đợi duyệt ở GĐ6). Dữ liệu chính do dự án tự biên soạn |
| **Lớp âm dương / ngũ hành** | Tuỳ chọn, làm ở GĐ6 sau khi dữ liệu lõi ổn định |
| **API công khai** | v2 |

## 6. Mục tiêu đo được

| Mục tiêu | Chỉ số | Ngưỡng đạt |
|---|---|---|
| Độ chính xác calo | ≥80% số món nằm trong ±20% so với giá trị tham chiếu | Xem [ACCURACY-SPEC](../06-nutrition/ACCURACY-SPEC.md) |
| Tốc độ nhập liệu | Thời gian trung bình nhập trọn một món ở Mức 2 | ≤ 5 phút |
| Quy mô dữ liệu — mốc dùng được | Số món đạt Mức 1 | ≥ 150 món |
| Quy mô dữ liệu — mốc đủ dùng | Số món đạt Mức 1 | ≥ 500 món |
| Nền nguyên liệu | Số nguyên liệu đã ánh xạ dinh dưỡng đầy đủ | ≥ 250 nguyên liệu |
| Tính tái lập | Clone repo trắng + seed ra đúng dữ liệu gốc | 100%, không sai lệch |

> **Về mục tiêu ">80% chính xác":** đây là con số ban đầu do chủ dự án đặt ra. Nó **chỉ có nghĩa khi
> kèm định nghĩa**. Định nghĩa đã chốt: *"≥80% số món có sai lệch ≤20% so với giá trị tham chiếu do
> chuyên gia tính theo hướng dẫn FAO/INFOODS"*. Định nghĩa "±10% so với phân tích hoá học" là
> **không khả thi** — ngay cả nhãn calo bắt buộc trên menu nhà hàng Mỹ cũng không đạt (đo thực tế cao
> hơn công bố trung bình ~18%; ~19% món lệch ≥100 kcal). Chi tiết:
> [ACCURACY-SPEC](../06-nutrition/ACCURACY-SPEC.md).

## 7. Ba mức đầy đủ dữ liệu

Không cố điền đủ mọi trường cho mọi món — sẽ tắc ngay ở món thứ 30. Mỗi món có `completeness_level`:

| Mức | Trường bắt buộc | Dùng để làm gì |
|---|---|---|
| **Mức 1** | Tên món, quốc gia, vùng miền, bữa phù hợp, ăn ngoài/tự nấu, khoảng calo ước lượng + độ tin cậy, ảnh | **Nút Lắc chạy được chỉ với mức này** |
| **Mức 2** | + công thức, nguyên liệu có định lượng, calo tính toán, macro | Món tự nấu hoàn chỉnh |
| **Mức 3** | + tính vị (âm dương, ngũ hành), ghi chú văn hoá | Lớp tuỳ chọn |

Phần lớn **món ăn ngoài sẽ dừng ở Mức 1 vĩnh viễn** — điều đó hoàn toàn chấp nhận được, vì không ai
biết quán cho bao nhiêu dầu.

## 8. Rủi ro đã nhận diện

| Rủi ro | Mức | Cách xử lý |
|---|---|---|
| Không có dữ liệu thành phần thực phẩm Việt Nam hợp pháp | **Cao** | Xin phép Viện Dinh dưỡng ngay GĐ0 ([LEGAL-001](../05-tasks/tasks/LEGAL-001.md)). Phương án dự phòng: dùng USDA + tự đo, chấp nhận sai số cao hơn cho nguyên liệu đặc thù Việt |
| Bỏ cuộc giữa chừng vì nhập liệu quá mệt | **Cao** | GĐ2 bắt buộc đạt ≤5 phút/món trước khi sang GĐ3. Có mốc dùng được sớm ở 150 món |
| Dùng nhầm dữ liệu license bẩn | Trung bình | [LICENSE-COMPLIANCE](../04-data-sources/LICENSE-COMPLIANCE.md) + quy trình duyệt nguồn mới |
| Calo sai nhiều ở món chiên rán | Trung bình | Hệ số hấp thụ dầu riêng — xem [CALORIE-ENGINE §4](../06-nutrition/CALORIE-ENGINE.md) |
| Lớp ngũ hành bị hiểu là tư vấn y tế | Trung bình | Khung diễn đạt văn hoá, không cá nhân hoá theo tình trạng sức khoẻ ([NGU-HANH-AM-DUONG §5](../08-cultural/NGU-HANH-AM-DUONG.md)) |
| Trùng lặp / mâu thuẫn dữ liệu khi nhiều người cùng biên soạn | Thấp ở v1 | [EDITORIAL-GUIDE](../09-standards/EDITORIAL-GUIDE.md) + `pg_trgm` cảnh báo trùng lúc nhập |

## 9. Câu hỏi còn mở

| # | Câu hỏi | Ai quyết | Hạn |
|---|---|---|---|
| Q1 | Tên chính thức là "Lắc Đói" hay "Lắc Đối"? Tài liệu đang dùng **Lắc Đói** (khớp với tên thư mục `shake-hungry`) | Chủ dự án | Trước GĐ1 |
| Q2 | Giấy phép mã nguồn của dự án? Ảnh hưởng tới việc dùng Open Food Facts (ODbL) và Wikibooks (CC BY-SA) | Chủ dự án | Trước khi nhập 2 nguồn đó |
| Q3 | Có xin được dữ liệu Viện Dinh dưỡng không? | Viện Dinh dưỡng | Theo dõi ở [LEGAL-001](../05-tasks/tasks/LEGAL-001.md) |
| Q4 | Ảnh món ăn lấy từ đâu? Tự chụp / mua / CC BY? Ảnh hưởng tới kích thước repo | Chủ dự án | Trước GĐ3 |
