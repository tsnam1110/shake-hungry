# DATA-009 — Chuẩn hoá tên món: tên chính tiếng Việt có dấu + tên tiếng Anh đúng nghĩa

- **Trạng thái:** ✅ (2026-10-31 — theo yêu cầu của chủ dự án)
- **Phụ thuộc:** ALIAS-001 (hạ tầng tên món), DISH-001…005 (kho món)
- **Nhánh:** Dữ liệu

## Bối cảnh & quy ước (chủ dự án chốt 2026-10-31)

Người dùng chính của ứng dụng là người Việt. Một số tên món hiển thị không dấu
(thực chất là tên tiếng Anh dạng phiên âm kiểu "Bun bo Hue (…)", "banh xeo (…)"
đứng ở vị trí tên đầu tiên) gây hiểu nhầm nghiêm trọng. Quy ước từ nay:

1. **Tên chính của món là tên tiếng Việt có dấu đầy đủ**, luôn đứng đầu trong
   mảng `names` của file món và là tên hiển thị mặc định.
2. **Mỗi món có thêm một tên tiếng Anh đúng nghĩa** (dịch thật, không phiên âm
   không dấu). Các tên Anh cũ dạng phiên âm + ngoặc đơn được thay bằng chính
   phần dịch trong ngoặc.
3. **Món có tên gốc là tiếng Anh** (món ngoại nhập mà tên gốc là tên Anh) thì
   tên Anh là tên chính, tên Việt là tên phụ. Hiện tại kho 496 món chưa có món
   nào thuộc diện này — quy ước ghi nhận cho các đợt biên soạn sau.

## Đã làm (trên 496 file món đã commit; 60 file món mới đang biên soạn của đợt
DISH-005 không thuộc phạm vi, tên của chúng đã đúng quy ước)

- Sửa tên chính tiếng Việt viết thường/không dấu ở 5 món (Canh chua, Cua rang
  me, Heo quay, Don, Nem chua Lai Vung — các tên này đúng chính tả là không dấu
  thanh nhưng trước đây viết thường lẫn với các món viết hoa).
- Thay **212 tên tiếng Anh phiên âm** bằng bản dịch đúng nghĩa (86 dạng viết
  hoa + 126 dạng viết thường), cộng 2 tên sửa tay (phở gà, nem chua Lai Vung).
- **Bổ sung tên tiếng Anh còn thiếu cho 46 món** và nâng 2 dòng tên Anh sẵn có
  thành tên chính (canh khoai sọ, cốm làng Vòng); phân biệt lại tên Anh của
  bánh trôi tàu (Tangyuan-style…) để không trùng chè trôi nước.
- Đưa tên chính tiếng Việt lên đầu mảng `names` ở 181 file (trước đây tên Anh
  đứng trước); viết hoa chữ cái đầu mọi tên Anh cho đồng nhất.
- Mã nguồn: thêm `Dish::displayName()` — luôn ưu tiên tên chính tiếng Việt —
  và dùng ở các presenter công khai (gợi ý Lắc, danh mục, yêu thích, lịch sử)
  thay cho `names->first()` vốn phụ thuộc thứ tự dòng.

## Về "cách nấu không có dấu"

Đã kiểm cả file dữ liệu lẫn CSDL đang chạy: **toàn bộ `recipe_steps` và mô tả
món đều đã có dấu đầy đủ** (0 trường hợp không dấu). Phần không dấu người dùng
nhìn thấy là các tên tiếng Anh phiên âm ở vị trí tên hiển thị — đã xử lý ở trên.

## Kiểm chứng

- Quét lại toàn kho sau sửa: 0 món thiếu tên Anh, 0 tên Anh còn dạng phiên âm,
  0 món có tên đầu tiên không phải tiếng Việt.
- `lacdoi:validate-data`: 1.035 bản ghi, **0 🔴** (5 🟡 là các cặp gần trùng đã
  biết từ trước, không phát sinh từ đợt này).
- Pest **106/106**. Đã đồng bộ `dish_names` vào CSDL đang chạy theo đúng logic
  của DishSeeder cho mọi file thay đổi.
