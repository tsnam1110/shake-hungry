@extends('layouts.app')

@section('title', 'Nguồn dữ liệu và giấy phép — Lắc Đói')

@section('content')
    <h1 class="ld-title" style="margin-top:14px">Nguồn dữ liệu và giấy phép</h1>
    <div class="ld-prose">
        <p class="updated">Cập nhật lần cuối: 31 tháng 10 năm 2026</p>
        <p>Lắc Đói xây dựng kho dữ liệu món ăn và dinh dưỡng theo nguyên tắc: con số chỉ đến từ các bảng thành phần thực phẩm có kiểm định và công thức định lượng được biên soạn có nguồn; mọi con số trên giao diện đều truy vết được về nguồn.</p>

        <h2>1. Nguồn dữ liệu</h2>
        <ul>
            <li><strong>Bảng thành phần thực phẩm Việt Nam</strong> (Viện Dinh dưỡng Quốc gia) — nguồn chính cho nguyên liệu Việt Nam.</li>
            <li><strong>USDA FoodData Central</strong> (Bộ Nông nghiệp Hoa Kỳ, dữ liệu công cộng) — cho nguyên liệu không có trong bảng trong nước.</li>
            <li>Các bảng thành phần quốc gia khác (Nhật Bản — MEXT, Anh — CoFID) và bao bì sản phẩm trong nước cho trường hợp đặc thù.</li>
            <li><strong>Open Food Facts</strong> — chỉ một số ít dòng dữ liệu nhãn sản phẩm, được ghi công theo mục 3.</li>
            <li>Đóng góp của cộng đồng và biên soạn của dự án: định lượng công thức, phân loại món, thông tin mùa vụ và giá hoa quả (mỗi dòng giá đều ghi nguồn và ngày thu thập).</li>
            <li>Nguồn thứ cấp (trang ẩm thực, nhãn thương hiệu) chỉ dùng bổ sung khi nguồn có kiểm định không có dữ liệu, và luôn bị gắn nhãn "Chưa kiểm định" trên giao diện.</li>
        </ul>

        <h2>2. Giấy phép</h2>
        <ul>
            <li><strong>Mã nguồn</strong> của Lắc Đói: giấy phép <strong>MIT</strong>.</li>
            <li><strong>Dữ liệu do dự án tự biên soạn</strong> (định lượng món, phân loại, mùa vụ, tổng hợp): giấy phép <strong>Creative Commons Attribution 4.0 (CC BY 4.0)</strong> — bạn được sử dụng lại khi ghi công "Lắc Đói" kèm liên kết về trang này.</li>
            <li><strong>Một số ít dòng dữ liệu có nguồn Open Food Facts</strong>: giữ giấy phép <strong>ODbL 1.0</strong> của nguồn gốc kèm ghi công "© Open Food Facts contributors". Khi trích xuất dữ liệu, các dòng này được đánh dấu riêng trong file dữ liệu của dự án.</li>
            <li>Dữ liệu từ các bảng thành phần quốc gia thuộc về cơ quan ban hành tương ứng; dự án chỉ dẫn chiếu phục vụ tính toán và luôn ghi rõ nguồn ở khối "Nguồn gốc con số" trên từng món.</li>
        </ul>

        <h2>3. Ghi công Open Food Facts</h2>
        <p>Một phần nhỏ dữ liệu nhãn sản phẩm trong Lắc Đói có nguồn từ Open Food Facts (https://world.openfoodfacts.org), cơ sở dữ liệu mở theo giấy phép Open Database License (ODbL 1.0). Nội dung từng sản phẩm thuộc về các cộng tác viên của Open Food Facts theo giấy phép CC BY-SA 3.0.</p>

        <h2>4. Khiếu nại bản quyền và đính chính dữ liệu</h2>
        <p>Nếu bạn là chủ sở hữu quyền đối với nội dung xuất hiện trên Lắc Đói và muốn yêu cầu ghi công, sửa đổi hoặc gỡ bỏ, hoặc nếu bạn phát hiện số liệu sai, hãy gửi qua tính năng Đóng góp trong ứng dụng. Chúng tôi sẽ rà soát và xử lý: đính chính có ghi rõ trong tài liệu dự án, gỡ bỏ khi yêu cầu hợp lệ.</p>
    </div>
@endsection
