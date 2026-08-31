# [LEGAL-002] — Chốt giấy phép mã nguồn và dữ liệu của dự án

**Mã task:** LEGAL-002
**Giai đoạn:** GĐ0
**Loại:** Quyết định
**Status:** ⬜ TODO
**Ngày tạo:** 2026-08-31
**Ngày hoàn thành:** —
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

- [ ] Đã chốt license mã nguồn, có file `LICENSE` ở gốc repo
- [ ] Đã chốt license dữ liệu, ghi rõ trong `README.md` và `data/README.md`
- [ ] Quyết định + lý do + các phương án bị loại đã ghi vào "Nhật ký quyết định" ở [ROADMAP](../ROADMAP.md)
- [ ] Cập nhật [LICENSE-COMPLIANCE §5](../../04-data-sources/LICENSE-COMPLIANCE.md) với kết luận
- [ ] Cập nhật trạng thái Open Food Facts và Wikibooks trong nhật ký thẩm định §7 (từ ⏸️ sang ✅ hoặc ❌)

---

## Cạm bẫy đã biết

- **Đừng để trống rồi tính sau.** Repo không có LICENSE mặc định là "mọi quyền được bảo lưu" — tức là
  không ai được dùng, ngược hẳn với ý định của một dự án mở.
- **License mã nguồn không tự động áp cho dữ liệu.** Phải nói rõ hai thứ riêng biệt, nếu không người
  dùng lại sẽ không biết dữ liệu chịu điều kiện gì.

---

## Ghi chú

*(Ghi quyết định và lý do ở đây khi chốt.)*
