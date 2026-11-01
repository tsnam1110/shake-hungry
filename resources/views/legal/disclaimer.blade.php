@extends('layouts.app')

@section('title', 'Miễn trừ y tế và dinh dưỡng — Lắc Đói')

@section('content')
    <h1 class="ld-title" style="margin-top:14px">Miễn trừ y tế và dinh dưỡng</h1>
    <div class="ld-prose">
        <p class="updated">Cập nhật lần cuối: 31 tháng 10 năm 2026</p>
        <p>Lắc Đói là công cụ gợi ý món ăn và tra cứu thông tin dinh dưỡng phục vụ đời sống hằng ngày. Toàn bộ nội dung trên hệ thống <strong>chỉ mang tính tham khảo và giáo dục, không phải tư vấn y tế</strong>, không thay thế chẩn đoán, điều trị hay hướng dẫn của bác sĩ và chuyên gia dinh dưỡng.</p>

        <h2>1. Về các con số calo và dinh dưỡng</h2>
        <ul>
            <li>Mọi con số đều là <strong>khoảng ước tính kèm độ tin cậy</strong>, được tính từ định lượng công thức và các bảng thành phần thực phẩm tham chiếu. Món ăn thực tế biến thiên theo nguyên liệu, mùa vụ, cách chế biến và khẩu phần, nên sai lệch so với khoảng hiển thị là điều có thể xảy ra.</li>
            <li>Món chưa đủ dữ liệu tin cậy sẽ hiển thị "Chưa đủ dữ liệu" thay vì một con số đoán. Dữ liệu từ nguồn thứ cấp chưa qua kiểm định được gắn nhãn "Chưa kiểm định" ngay trên giao diện.</li>
            <li>Nhu cầu năng lượng cá nhân (TDEE) được ước tính bằng công thức dân số học kết hợp hệ số vận động — đây là điểm tham khảo ban đầu, không phải phép đo y khoa.</li>
        </ul>

        <h2>2. Về bộ lọc dị ứng "Không ăn được"</h2>
        <ul>
            <li>Bộ lọc loại trừ món chứa thành phần bạn khai báo dựa trên dữ liệu thành phần mà dự án biên soạn được. Dữ liệu có thể chưa đầy đủ (ví dụ thành phần phụ trong nước chấm, gia vị tổng hợp, hoặc chế biến tại quán).</li>
            <li>Do đó bộ lọc <strong>không phải là cam kết an toàn tuyệt đối</strong>. Nếu bạn có dị ứng nghiêm trọng hoặc bệnh lý cần kiêng khem, hãy luôn tự kiểm tra thành phần món ăn và hỏi ý kiến bác sĩ trước khi ăn.</li>
        </ul>

        <h2>3. Về nội dung văn hoá (âm dương, ngũ hành)</h2>
        <p>Thông tin về tính hàn/nhiệt, âm dương, ngũ hành (kèm các kiêng kỵ kết hợp thực phẩm của hoa quả) là lớp thông tin <strong>văn hoá — ẩm thực truyền thống</strong>, được trình bày ở cấp độ nguyên liệu để tham khảo. Đây không phải khẳng định y học và không nên dùng làm căn cứ điều trị.</p>
        <p>Nhãn tính vị được gán cho từng nguyên liệu kèm nguồn; tính vị của một món là kết quả <strong>tổng hợp tự động</strong> từ các nguyên liệu của món theo trọng số khối lượng, kèm hệ số điều chỉnh theo cách chế biến vốn là <strong>quy ước của dự án</strong> (y văn không định lượng con số này) — và chỉ hiển thị khi phần nguyên liệu đã có nhãn đủ lớn. Phân loại này không được chuẩn hoá giữa các trường phái: khi các nguồn ghi khác nhau, hệ thống hiển thị cả mâu thuẫn thay vì tự chọn một.</p>

        <h2>4. Khi nào cần gặp chuyên gia</h2>
        <p>Nếu bạn đang mang thai, cho con bú, có bệnh nền (đái tháo đường, thận, tim mạch…), đang dùng thuốc, hoặc có mục tiêu thay đổi cân nặng vì lý do sức khoẻ, hãy làm việc trực tiếp với bác sĩ hoặc chuyên gia dinh dưỡng thay vì dựa vào gợi ý của hệ thống.</p>
    </div>
@endsection
