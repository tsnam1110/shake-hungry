@extends('layouts.app')

@section('title', 'Điều khoản sử dụng — Lắc Đói')

@section('content')
    <h1 class="ld-title" style="margin-top:14px">Điều khoản sử dụng</h1>
    <div class="ld-prose">
        <p class="updated">Cập nhật lần cuối: 31 tháng 10 năm 2026</p>
        <p>Lắc Đói là dự án dữ liệu món ăn Việt Nam do cộng đồng biên soạn, cung cấp tính năng gợi ý món ăn ("Lắc"), tra cứu dinh dưỡng, thông tin hoa quả theo mùa và nhật ký ăn uống cá nhân. Khi sử dụng Lắc Đói, bạn đồng ý với các điều khoản dưới đây.</p>

        <h2>1. Tài khoản</h2>
        <ul>
            <li>Bạn có thể dùng các tính năng tra cứu và Lắc cơ bản mà không cần tài khoản. Tài khoản giúp lưu hồ sơ khẩu vị, nhật ký, mục yêu thích và đóng góp.</li>
            <li>Bạn chịu trách nhiệm bảo mật mật khẩu và mọi hoạt động diễn ra dưới tài khoản của mình. Thông tin đăng ký phải chính xác; không mạo danh người khác.</li>
            <li>Chúng tôi có thể tạm khoá hoặc xoá tài khoản vi phạm điều khoản, sau khi đã cảnh báo trừ trường hợp vi phạm nghiêm trọng.</li>
        </ul>

        <h2>2. Nội dung người dùng đóng góp</h2>
        <ul>
            <li>Khi gửi đóng góp (món ăn, công thức, thông tin quán, nhận xét), bạn khẳng định mình có quyền chia sẻ nội dung đó và nội dung không sao chép trái phép từ nguồn có bản quyền.</li>
            <li>Bạn cấp cho Lắc Đói quyền sử dụng, biên tập và công bố đóng góp trong kho dữ liệu của dự án. Dữ liệu được dự án biên soạn và công bố theo giấy phép CC BY 4.0 (xem trang <a href="/nguon-du-lieu">Nguồn dữ liệu và giấy phép</a>); đóng góp có thể được ghi công theo tên hiển thị của bạn.</li>
            <li>Mọi đóng góp đều qua bước kiểm duyệt trước khi xuất hiện trong dữ liệu tính toán. Chúng tôi có quyền từ chối hoặc gỡ bỏ đóng góp không phù hợp mà không cần báo trước lý do chi tiết.</li>
        </ul>

        <h2>3. Hành vi bị cấm</h2>
        <ul>
            <li>Thu thập dữ liệu hàng loạt (cào/scrape) ngoài kênh xuất bản chính thức của dự án; tấn công, phá hoại hoặc làm quá tải hệ thống.</li>
            <li>Đăng nội dung sai sự thật về dinh dưỡng/an toàn thực phẩm, nội dung vi phạm pháp luật Việt Nam, hoặc nội dung xâm phạm quyền của người khác.</li>
            <li>Sử dụng Lắc Đói để đưa ra chẩn đoán, điều trị hoặc tư vấn y tế cho người khác.</li>
        </ul>

        <h2>4. Thông tin dinh dưỡng và sức khoẻ</h2>
        <p>Mọi con số dinh dưỡng trên Lắc Đói là ước tính phục vụ tham khảo, tuân theo trang <a href="/mien-tru-y-te">Miễn trừ y tế và dinh dưỡng</a> — trang đó là một phần của điều khoản này.</p>

        <h2>5. Tính khả dụng và thay đổi dịch vụ</h2>
        <p>Lắc Đói đang trong giai đoạn phát triển và hoàn thiện. Chúng tôi có thể thay đổi, bổ sung hoặc ngừng một tính năng; dữ liệu có thể được biên soạn lại khi có nguồn tốt hơn. Chúng tôi nỗ lực duy trì dịch vụ ổn định nhưng không cam kết dịch vụ không gián đoạn.</p>

        <h2>6. Trách nhiệm</h2>
        <p>Trong phạm vi pháp luật cho phép, Lắc Đói không chịu trách nhiệm về thiệt hại phát sinh từ việc dựa vào thông tin tham khảo trên hệ thống để ra quyết định về sức khoẻ. Trách nhiệm của người dùng đối với nội dung mình đóng góp vẫn giữ nguyên theo pháp luật.</p>

        <h2>7. Luật áp dụng và thay đổi điều khoản</h2>
        <p>Điều khoản này được điều chỉnh bởi pháp luật Việt Nam. Khi điều khoản thay đổi, chúng tôi cập nhật ngày ở đầu trang; thay đổi quan trọng sẽ được thông báo nổi bật trên hệ thống. Việc tiếp tục sử dụng sau khi thay đổi có hiệu lực đồng nghĩa bạn chấp nhận điều khoản mới.</p>
    </div>
@endsection
