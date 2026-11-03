# Mười nguyên tắc thực thi — Lắc Đói

> **Nguồn:** chủ dự án (Benjamin), 2026-11-03.
> **Vị trí:** file này là nơi duy nhất ghi đầy đủ 10 nguyên tắc. `CLAUDE.md` (§5, §6) và
> `AGENTS.md` chỉ **link tới file này**, không chép lại nội dung — sửa nguyên tắc thì sửa tại
> đây, không sửa thẳng vào file chính.
> **Quan hệ với quy tắc khác:** các nguyên tắc dưới đây bổ sung cho QT-8 (phân tích trước khi
> thực thi) và QT-9 (chính xác trên tốc độ); khi có xung đột, áp dụng quy tắc chặt hơn.
> Vi phạm một nguyên tắc là lý do đủ để chủ dự án yêu cầu làm lại, dù kết quả nhìn bề ngoài
> có vẻ chạy được.

## 1. Understand Before Execution – Hiểu trước khi thực hiện

Không được bỏ qua. AI không được bắt đầu viết code ngay khi nhận yêu cầu.

Bắt buộc phải:

- Đọc và hiểu yêu cầu.
- Kiểm tra cấu trúc dự án hiện tại.
- Đọc các tài liệu liên quan.
- Xác định những chức năng và Module bị ảnh hưởng.
- Kiểm tra các logic hiện có trước khi đề xuất thay đổi.
- Xác định những thông tin chưa rõ.

Quy tắc: Không tự suy diễn nghiệp vụ khi chưa có đủ căn cứ. Nếu có điểm chưa rõ hoặc nhiều
cách hiểu, phải hỏi lại người dùng.

## 2. Plan Before Coding – Lập kế hoạch trước khi lập trình

AI phải trình bày kế hoạch thực hiện trước khi bắt đầu thay đổi code.

Kế hoạch cần có:

- Mục tiêu cần đạt.
- Các file dự kiến cần tạo hoặc chỉnh sửa.
- Logic xử lý dự kiến.
- Các Module/API/Database liên quan.
- Các công việc cần thực hiện theo thứ tự.
- Rủi ro và các trường hợp ngoại lệ.
- Phương án kiểm thử.

Quy tắc: Không triển khai những thay đổi lớn khi chưa có kế hoạch rõ ràng.

## 3. No Blind Modification – Không chỉnh sửa mù

Đây là một trong những nguyên tắc quan trọng nhất.

AI không được:

- Tự ý sửa file khi chưa đọc nội dung.
- Xóa code chỉ vì cho rằng không cần thiết.
- Thay thế toàn bộ file khi chỉ cần sửa một phần nhỏ.
- Tự ý thay đổi cấu trúc Database.
- Tự ý thay đổi kiến trúc hệ thống.
- Tự ý chỉnh sửa các chức năng không thuộc phạm vi yêu cầu.

Quy tắc: Chỉ thay đổi những gì thực sự cần thiết để giải quyết yêu cầu.

## 4. No Batch Modification – Không chỉnh sửa hàng loạt thiếu kiểm soát

Không được thực hiện các thao tác hàng loạt trên nhiều file khi chưa đánh giá tác động.

Ví dụ:

- Thay thế hàng loạt tên biến.
- Xóa hoặc ghi đè nhiều file.
- Chạy Script tự động cập nhật Database.
- Refactor hàng loạt các Module.
- Thay đổi cấu trúc thư mục trên diện rộng.

Nếu cần thay đổi hàng loạt, AI phải:

1. Xác định phạm vi file bị ảnh hưởng.
2. Giải thích lý do cần thay đổi.
3. Đánh giá rủi ro.
4. Thực hiện theo từng nhóm nhỏ.
5. Kiểm tra kết quả sau mỗi nhóm.

Quy tắc: Ưu tiên thay đổi nhỏ, có kiểm soát và có thể kiểm chứng.

## 5. Preserve Existing Logic – Bảo toàn logic hiện tại

AI phải ưu tiên bảo toàn hệ thống hiện có.

Bắt buộc:

- Không phá vỡ chức năng đang hoạt động.
- Không tự ý thay đổi Business Logic.
- Không tạo ra các logic trùng lặp.
- Tái sử dụng Component, Function và Service hiện có khi phù hợp.
- Kiểm tra khả năng tương thích với các Module liên quan.

Quy tắc: Một tính năng mới không được làm hỏng tính năng cũ.

## 6. Quality Over Speed – Chất lượng quan trọng hơn tốc độ

Không được tối ưu thời gian bằng cách bỏ qua các bước quan trọng.

AI không được:

- Bỏ qua việc đọc tài liệu.
- Bỏ qua kiểm tra logic.
- Bỏ qua xử lý lỗi.
- Bỏ qua kiểm thử.
- Sử dụng giải pháp tạm thời để thay thế giải pháp hoàn chỉnh mà không thông báo.

Quy tắc: Ưu tiên giải pháp đúng, ổn định, dễ bảo trì thay vì giải pháp nhanh nhưng thiếu
hoàn thiện.

## 7. Follow Existing Standards – Tuân thủ tiêu chuẩn dự án

AI phải tuân thủ:

- Coding Convention.
- Naming Convention.
- Folder Structure.
- Design Pattern hiện tại.
- Architecture hiện tại.
- Quy tắc bảo mật.
- Quy chuẩn UI/UX.
- Quy chuẩn API và Database.

Quy tắc: Không tự tạo ra một cách triển khai mới nếu dự án đã có quy chuẩn hoặc phương pháp
tương ứng.

## 8. Verify Every Change – Kiểm tra mọi thay đổi

Sau khi chỉnh sửa code, AI bắt buộc phải kiểm tra kết quả.

Các bước:

- Kiểm tra lỗi cú pháp.
- Kiểm tra Type / Lint.
- Chạy Unit Test.
- Chạy Integration Test nếu có.
- Kiểm tra các chức năng liên quan.
- Kiểm tra các trường hợp ngoại lệ.
- Kiểm tra Regression.

Quy tắc: Không được tuyên bố hoàn thành nếu chưa có bằng chứng kiểm chứng tương ứng.

Nếu không thể chạy kiểm thử, phải nói rõ lý do và những gì chưa được xác minh.

## 9. Checkpoint & Progress Control – Kiểm soát tiến độ theo từng giai đoạn

Không được thực hiện một chuỗi thay đổi lớn mà không có điểm kiểm tra.

Chia công việc thành các Phase:

| Phase | Nội dung | Điều kiện hoàn thành |
|---|---|---|
| 1 | Phân tích | Hiểu rõ yêu cầu |
| 2 | Planning | Kế hoạch được xác định |
| 3 | Implementation | Hoàn thành từng nhóm thay đổi |
| 4 | Verification | Kiểm tra đạt yêu cầu |
| 5 | Final Review | Đánh giá toàn bộ |
| 6 | Handover | Báo cáo và bàn giao |

Sau mỗi Phase, AI phải:

- Báo cáo những gì đã thực hiện.
- Xác nhận kết quả.
- Liệt kê vấn đề còn tồn tại.
- Chỉ tiếp tục khi đáp ứng điều kiện chuyển giai đoạn.

## 10. Transparent Reporting – Báo cáo minh bạch

Khi hoàn thành, AI phải báo cáo:

1. Những gì đã thực hiện.
2. Những file đã tạo hoặc chỉnh sửa.
3. Những logic đã thay đổi.
4. Các kiểm thử đã thực hiện.
5. Kết quả kiểm thử.
6. Những vấn đề chưa giải quyết.
7. Những rủi ro hoặc tác động cần lưu ý.

Quy tắc: Không được che giấu lỗi, không được báo cáo thành công khi chưa thực sự hoàn thành.
