@extends('layouts.app')

@section('title', 'Chính sách bảo mật — Lắc Đói')

@section('content')
    <h1 class="ld-title" style="margin-top:14px">Chính sách bảo mật</h1>
    <div class="ld-prose">
        <p class="updated">Cập nhật lần cuối: 31 tháng 10 năm 2026</p>
        <p>Chính sách này mô tả cách Lắc Đói thu thập, sử dụng và bảo vệ dữ liệu cá nhân của bạn, theo Luật Bảo vệ dữ liệu cá nhân và Nghị định 13/2023/NĐ-CP của Việt Nam. Bên kiểm soát dữ liệu là chủ dự án Lắc Đói; kênh liên hệ về dữ liệu cá nhân được nêu ở mục 8.</p>

        <h2>1. Dữ liệu chúng tôi thu thập</h2>
        <ul>
            <li><strong>Dữ liệu tài khoản:</strong> tên hiển thị, địa chỉ email, mật khẩu (lưu dưới dạng băm một chiều, không lưu dạng rõ).</li>
            <li><strong>Dữ liệu hồ sơ (bạn tự nguyện cung cấp):</strong> năm sinh, giới tính, chiều cao, cân nặng, mức vận động, chế độ ăn, thành phần không ăn được/dị ứng, vùng miền và mức kcal mục tiêu. Trong đó, thông tin thể trạng và dị ứng là <strong>dữ liệu cá nhân nhạy cảm</strong> theo pháp luật; chúng tôi chỉ xử lý khi bạn đồng ý bằng việc chủ động nhập vào hồ sơ.</li>
            <li><strong>Dữ liệu sử dụng:</strong> nhật ký bữa ăn và hoa quả bạn tự ghi, mục yêu thích, đóng góp bạn gửi.</li>
            <li><strong>Dữ liệu kỹ thuật:</strong> cookie phiên đăng nhập và thông tin trình duyệt cơ bản phục vụ vận hành và bảo mật.</li>
        </ul>

        <h2>2. Mục đích sử dụng</h2>
        <ul>
            <li>Cá nhân hoá gợi ý món: tính nhu cầu năng lượng (TDEE), áp dụng lọc dị ứng/chế độ ăn, điền sẵn tuỳ chọn khi lắc món.</li>
            <li>Hiển thị nhật ký, thống kê ăn uống và mục yêu thích của chính bạn.</li>
            <li>Kiểm duyệt đóng góp và vận hành, bảo mật hệ thống.</li>
        </ul>
        <p>Chúng tôi <strong>không</strong> bán dữ liệu cá nhân, không dùng dữ liệu hồ sơ của bạn cho quảng cáo, và không chia sẻ cho bên thứ ba ngoài các nhà cung cấp hạ tầng kỹ thuật cần thiết để vận hành hệ thống (máy chủ, gửi email) trong phạm vi tối thiểu.</p>

        <h2>3. Lưu trữ và bảo mật</h2>
        <ul>
            <li>Dữ liệu được lưu trên máy chủ của dự án, truyền qua kết nối mã hoá (HTTPS) khi hệ thống vận hành chính thức.</li>
            <li>Mật khẩu được băm bằng thuật toán mạnh một chiều; quyền truy cập dữ liệu bị giới hạn ở quản trị viên của dự án.</li>
            <li>Dữ liệu tài khoản được giữ trong thời gian tài khoản tồn tại. Khi bạn xoá tài khoản, dữ liệu hồ sơ, nhật ký và yêu thích gắn với tài khoản sẽ bị xoá; đóng góp đã được biên soạn vào kho dữ liệu chung sẽ được ẩn danh.</li>
        </ul>

        <h2>4. Quyền của bạn</h2>
        <p>Theo pháp luật về bảo vệ dữ liệu cá nhân, bạn có quyền: được biết và đồng ý việc xử lý dữ liệu; truy cập, xem dữ liệu của mình; yêu cầu chỉnh sửa; yêu cầu xoá dữ liệu và rút lại sự đồng ý; khiếu nại về việc xử lý dữ liệu. Phần lớn thao tác xem và sửa thực hiện ngay trong trang Hồ sơ; các yêu cầu còn lại gửi qua kênh liên hệ ở mục 8, chúng tôi phản hồi trong thời hạn pháp luật quy định.</p>

        <h2>5. Cookie</h2>
        <p>Lắc Đói chỉ dùng cookie cần thiết cho đăng nhập và bảo mật phiên (ví dụ cookie phiên, token chống giả mạo yêu cầu). Chúng tôi không dùng cookie theo dõi quảng cáo của bên thứ ba.</p>

        <h2>6. Trẻ em</h2>
        <p>Lắc Đói không hướng tới trẻ em dưới 16 tuổi và không chủ đích thu thập dữ liệu của trẻ em. Việc xử lý dữ liệu của trẻ em (nếu phát sinh qua tài khoản gia đình) phải có sự đồng ý của cha mẹ hoặc người giám hộ theo quy định pháp luật.</p>

        <h2>7. Thay đổi chính sách</h2>
        <p>Khi chính sách thay đổi, ngày cập nhật ở đầu trang sẽ được sửa; thay đổi quan trọng về mục đích xử lý dữ liệu sẽ được thông báo và xin đồng ý lại khi pháp luật yêu cầu.</p>

        <h2>8. Liên hệ về dữ liệu cá nhân</h2>
        <p>Trong giai đoạn phát triển, bạn liên hệ qua tính năng Đóng góp trong ứng dụng (sau khi đăng nhập) hoặc qua trang hồ sơ. Thông tin liên hệ chính thức của bên kiểm soát dữ liệu sẽ được công bố tại trang này khi Lắc Đói ra mắt công khai.</p>
    </div>
@endsection
