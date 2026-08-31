# [LEGAL-001] — Xin phép sử dụng dữ liệu Viện Dinh dưỡng

**Mã task:** LEGAL-001
**Giai đoạn:** GĐ0
**Loại:** Thủ tục
**Status:** ❌ CANCELLED
**Ngày tạo:** 2026-08-31
**Ngày huỷ:** 2026-08-31
**Phụ thuộc:** —

---

> ## ❌ Huỷ 2026-08-31 — chọn hướng tự tra cứu thay vì xin phép
>
> Chủ dự án quyết định **không xin phép Viện Dinh dưỡng**, mà **tự tra cứu từng nguyên liệu, từng
> món, từng công thức từ các nguồn công khai**, chấp nhận đánh đổi thời gian để lấy độ chính xác.
>
> **Vì sao hướng này hợp lệ về pháp lý:** giá trị dinh dưỡng là **dữ kiện**, không phải tác phẩm.
> Cái được bảo hộ bản quyền là **bảng biên soạn** của Viện Dinh dưỡng — cách chọn mục, cách sắp xếp,
> toàn bộ tập hợp. Tra từng nguyên liệu từ nhiều nguồn công khai rồi tự biên soạn, mỗi con số ghi rõ
> nguồn, là **hoạt động biên soạn độc lập**, khác hẳn với việc chép lại bảng của họ.
>
> **Ranh giới vẫn giữ nguyên:** không được số hoá hay chép có hệ thống bảng của Viện Dinh dưỡng,
> kể cả từ bản PDF trôi nổi. Đó vẫn là tái tạo tác phẩm biên soạn của họ.
>
> Task này **không được mở lại** trừ khi chủ dự án đổi ý. Công việc thay thế:
> [ING-001](./ING-001.md) → [ING-002](./ING-002.md) → [ING-003](./ING-003.md), với quy trình tra cứu
> mô tả ở [DATA-PIPELINE §7](../../04-data-sources/DATA-PIPELINE.md).
>
> Nội dung gốc bên dưới **giữ lại làm lịch sử** — để người kế nhiệm biết phương án này đã được cân
> nhắc và vì sao không chọn, thay vì đề xuất lại từ đầu.

---

## Mục tiêu (nội dung gốc — đã huỷ)

Xin phép Viện Dinh dưỡng Quốc gia (Bộ Y tế) sử dụng *Bảng Thành phần Thực phẩm Việt Nam* và/hoặc dữ
liệu tra cứu của VNeNUTRITION cho một dự án mã nguồn mở phi lợi nhuận, có ghi nguồn.

**Đây là task có thời gian chờ dài nhất của dự án. Gửi ngay tuần đầu, không chờ code.** Thủ tục hành
chính có thể mất nhiều tháng và chạy song song được với mọi việc khác.

---

## Bối cảnh

Khảo sát 2026-08-31 kết luận: **không tồn tại bảng thành phần thực phẩm Việt Nam có license mở.**

| Nguồn | Thực trạng |
|---|---|
| *Bảng Thành phần Thực phẩm Việt Nam* — bản 2007: 501 thực phẩm × 15 thành phần; bản 2017: ~600 thực phẩm, tới 86 thành phần/100 g | Sách có bản quyền, xuất bản qua NXB Y học. **Không có license mở nào.** Bản PDF trôi nổi trên mạng (kể cả bản trên mirror của FAO) **không đồng nghĩa được phép sử dụng** |
| VNeNUTRITION (viendinhduong.vn) — có sẵn "Tra cứu thực phẩm" và "Tra cứu món ăn" | Site **không công bố điều khoản sử dụng, license hay chính sách API nào**. Không có điều khoản = không được phép |

Nếu **được cấp phép**: chất lượng dữ liệu món Việt của dự án sẽ vượt xa mọi phương án thay thế, vì
thịt lợn, gạo, rau Việt Nam khác Mỹ về giống và điều kiện nuôi trồng.

Nếu **không được cấp phép**: dự án vẫn đi tiếp bằng USDA + tự đo, nhưng nguyên liệu đặc thù Việt
(nước mắm, bánh phở, giò lụa, rau muống) sẽ thiếu và độ chính xác thấp hơn. Xem phương án dự phòng ở
[ING-003](./ING-003.md).

---

## Việc cần làm

1. Soạn công văn/thư đề nghị, nêu rõ:
   - Dự án là gì, mã nguồn mở, phi lợi nhuận, không quảng cáo.
   - Xin dùng dữ liệu nào, dùng vào việc gì (tính calo món ăn cho người dùng cuối).
   - Cam kết **ghi nguồn rõ ràng** ở mọi nơi hiển thị dữ liệu.
   - Đề nghị nêu cả phương án hợp tác: dự án có thể chia sẻ ngược dữ liệu món ăn đã biên soạn.
2. Gửi tới Viện Dinh dưỡng Quốc gia — qua đường công văn và qua email liên hệ công bố trên
   viendinhduong.vn.
3. **Ghi lại:** ngày gửi, người/bộ phận nhận, kênh gửi. Lưu bản sao nội dung tại
   `data/reference/_licenses/vn-nin-request.md`.
4. Đặt lịch nhắc theo dõi sau 30 ngày.
5. Khi có phản hồi: cập nhật task này, cập nhật nhật ký thẩm định ở
   [LICENSE-COMPLIANCE §7](../../04-data-sources/LICENSE-COMPLIANCE.md), và cập nhật câu hỏi Q3 ở
   [ROADMAP](../ROADMAP.md).

---

## Definition of Done

- [ ] Nội dung đề nghị đã soạn và lưu vào `data/reference/_licenses/vn-nin-request.md`
- [ ] Đã gửi, có ghi lại ngày gửi và kênh gửi
- [ ] Đã đặt lịch theo dõi 30 ngày
- [ ] Kết quả (dù là không phản hồi) đã ghi vào nhật ký thẩm định LICENSE-COMPLIANCE §7

> Task này DONE khi **đã gửi và đã ghi lại**, không phải khi được chấp thuận. Việc chờ phản hồi theo
> dõi ở câu hỏi Q3 trong ROADMAP.

---

## Cạm bẫy đã biết

- **Đừng nhập dữ liệu trước rồi xin phép sau.** Một khi vào repo là đã phân phối lại, và lịch sử git
  vẫn còn kể cả khi xoá file.
- **Đừng coi PDF tải được là được phép.** Đây là hiểu lầm phổ biến nhất về dữ liệu này.
- **Đừng gọi API nội bộ chưa công bố tài liệu của VNeNUTRITION.** Không có điều khoản không có nghĩa
  là được phép — nó có nghĩa là không được phép.

---

## Ghi chú

*(Ghi diễn biến trao đổi ở đây.)*
