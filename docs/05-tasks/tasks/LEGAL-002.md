# [LEGAL-002] — Chốt giấy phép mã nguồn và dữ liệu của dự án

**Mã task:** LEGAL-002
**Giai đoạn:** GĐ0
**Loại:** Quyết định
**Status:** ✅ DONE
**Ngày tạo:** 2026-08-31
**Ngày hoàn thành:** 2026-10-31
**Phụ thuộc:** —

---

## Mục tiêu

Quyết định license cho **mã nguồn** và cho **cơ sở dữ liệu** của dự án. Đây là câu hỏi Q2 ở
[PRD §9](../../01-prd/PRD.md).

**Phải quyết trước khi nhập Open Food Facts hoặc Wikibooks Cookbook** — cả hai đều có ràng buộc
share-alike. Nhập rồi mới nghĩ là quá muộn: gỡ dữ liệu ra khỏi một cơ sở dữ liệu đã trộn gần như bất
khả thi.

---

## Bối cảnh

Hai nguồn trong danh sách trắng có share-alike:

- **Open Food Facts — ODbL 1.0.** Nếu tạo *Derivative Database* (nhập dữ liệu OFF vào bảng của mình
  rồi làm giàu) và công khai sử dụng, **phải phát hành cơ sở dữ liệu dẫn xuất đó dưới ODbL**. Chỉ
  hiển thị dữ liệu OFF lên giao diện ("Produced Work") thì chỉ cần ghi nguồn.
- **Wikibooks Cookbook — CC BY-SA 4.0.** Văn bản dẫn xuất phải giữ CC BY-SA.

Chi tiết: [LICENSE-COMPLIANCE §5](../../04-data-sources/LICENSE-COMPLIANCE.md).

---

## Việc cần làm

Quyết định hai thứ **riêng biệt** (mã nguồn và dữ liệu có thể có license khác nhau — đây là chuyện
bình thường với dự án dữ liệu):

### 1. License mã nguồn

| Phương án | Hệ quả |
|---|---|
| MIT / Apache-2.0 | Ai cũng dùng lại được, kể cả trong sản phẩm đóng. Đơn giản nhất |
| AGPL-3.0 | Ai chạy bản sửa đổi trên web phải mở mã. Bảo vệ dự án khỏi bị đóng lại |

### 2. License cơ sở dữ liệu

| Phương án | Hệ quả |
|---|---|
| **(a)** ODbL cho phần dữ liệu | Dùng được Open Food Facts thoải mái. Dữ liệu mở, ai dùng lại cũng phải mở |
| **(b)** CC0 / CC BY cho dữ liệu tự biên soạn, **không nhập OFF vào DB** | Giữ tự do tối đa. Mất 1.583 sản phẩm đóng gói VN của OFF |
| **(c)** Bỏ hẳn OFF | Như (b), đơn giản hơn |

**Khuyến nghị cho v1:** phương án **(b)** hoặc **(c)**. Lý do: v1 tập trung món nấu, không phải sản
phẩm đóng gói, nên mất mát từ việc bỏ OFF là nhỏ; đổi lại giữ được toàn quyền với dữ liệu do chính
dự án tốn công biên soạn.

---

## Definition of Done

- [x] Đã chốt license mã nguồn, có file `LICENSE` ở gốc repo
- [x] Đã chốt license dữ liệu, ghi rõ trong `README.md` và `data/README.md`
- [x] Quyết định + lý do + các phương án bị loại đã ghi vào "Nhật ký quyết định" ở [ROADMAP](../ROADMAP.md)
- [x] Cập nhật [LICENSE-COMPLIANCE §5](../../04-data-sources/LICENSE-COMPLIANCE.md) với kết luận
- [x] Cập nhật trạng thái Open Food Facts và Wikibooks trong nhật ký thẩm định §7 (từ ⏸️ sang ✅ hoặc ❌)

---

## Cạm bẫy đã biết

- **Đừng để trống rồi tính sau.** Repo không có LICENSE mặc định là "mọi quyền được bảo lưu" — tức là
  không ai được dùng, ngược hẳn với ý định của một dự án mở.
- **License mã nguồn không tự động áp cho dữ liệu.** Phải nói rõ hai thứ riêng biệt, nếu không người
  dùng lại sẽ không biết dữ liệu chịu điều kiện gì.

---

## Ghi chú

**Quyết định (chủ dự án duyệt 2026-10-31):**

- **Mã nguồn: MIT** (file `LICENSE` ở gốc repo). Lý do: đơn giản nhất, không tốn chi phí
  cho dự án cá nhân chuẩn bị công khai; AGPL-3.0 bị loại vì dự án không đặt mục tiêu
  chống đóng mã ở giai đoạn này.
- **Dữ liệu: mô hình cơ sở dữ liệu tập hợp (collective database).** Phần tự biên soạn
  theo **CC BY 4.0**; các dòng có nguồn Open Food Facts giữ **ODbL 1.0** + ghi công
  (nhận diện qua `license_note` trong từng file). Wikibooks: **không dùng**, chưa từng
  nhập dòng nào.

**Đính chính tiền đề của phụ lục kiểm kê (2026-10-30):** phụ lục viết "Open Food Facts /
Wikibooks — chưa nhập dòng nào". Điều đó đúng với nghĩa *nhập hàng loạt*, nhưng nhật ký
thẩm định (LICENSE-COMPLIANCE §7) đã ghi nhận từ trước: một số ít nguyên liệu
(`mam-tom`, `sua-dac`, `tuong-ot`…) dùng số **chép từ nhãn sản phẩm tra qua OFF** ở đợt
ING-001. Vì vậy phương án (b)/(c) thuần tuý ("không có dòng OFF nào") không còn đúng
thực tế; quyết định cuối xử lý đúng thực tế đó bằng mô hình collective database thay vì
xoá dữ liệu (nước mắm/mắm tôm là nguyên liệu trụ cột) hoặc ép toàn bộ dataset sang ODbL.
Phương án dự phòng nếu sau này cần bề mặt ODbL bằng 0: thay các dòng đó bằng ảnh nhãn
tự chụp — các file liên quan đã được cập nhật `license_note` trỏ về quyết định này.

---

## Phụ lục kiểm kê nguồn dữ liệu hiện có (2026-10-30) — sẵn sàng để chốt

Phần phân tích và khuyến nghị ở trên vẫn nguyên giá trị. Kiểm kê thực tế kho
dữ liệu **hôm nay** để chủ dự án chốt nhanh:

| Nguồn đang có trong DB | Giấy phép | Hệ quả |
|---|---|---|
| USDA SR Legacy (7.793 dòng) | Public domain (CC0) | Không ràng buộc |
| Wikidata (61 món vỏ) | CC0 | Không ràng buộc |
| Dữ liệu tự biên soạn (255 nguyên liệu, định lượng 57 món) | Của dự án | Toàn quyền quyết định |
| Open Food Facts / Wikibooks | **Chưa nhập dòng nào** | Chọn (b)/(c) lúc này **không mất gì** |

Tức là: chốt phương án (b) hoặc (c) cho dữ liệu + MIT hoặc AGPL-3.0 cho mã
nguồn là quyết định không tốn chi phí ở thời điểm này, và mở đường cho việc
công khai repo an toàn. Việc còn lại thuần tuý là **chủ dự án chọn** — trợ lý
không tự quyết thay.
