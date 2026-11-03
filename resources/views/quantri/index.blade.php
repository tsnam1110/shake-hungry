@extends('layouts.app')
@section('title', 'Quản trị dữ liệu — Lắc Đói')
@section('content')
<h1 class="ld-title">Quản trị dữ liệu (tối thiểu)</h1>
<p class="ld-note">Trang này sửa trực tiếp file JSON trong <code>data/</code> — nguồn sự thật của dự án (QT-3). Sau khi lưu, người vận hành chạy <code>php artisan migrate:fresh --seed</code> rồi <code>lacdoi:validate-data</code> để áp dụng lên trang công khai. Không tự động seed từ web vì <code>migrate:fresh</code> xóa cả bảng người dùng. Chỉ sửa ghi chú/cờ an toàn; đổi định danh (cặp, slug, công thức, mùa vụ) phải làm qua file + duyệt như quy trình biên soạn thường.</p>
@if (session('status'))<div class="ld-warn"><span>{{ session('status') }}</span></div>@endif
<div class="ld-grid" style="margin-top:16px">
    @foreach ($modules as $key => $m)
    <a class="ld-card" href="{{ route('quantri.list', ['module' => $key]) }}" style="text-decoration:none;color:inherit">
        <h2 style="margin:0 0 6px">{{ $m['label'] }}</h2>
        <p class="ld-note" style="margin:0">{{ $counts[$key] }} bản ghi · xem / sửa ghi chú / lật cờ duyệt</p>
    </a>
    @endforeach
</div>
<p class="ld-note" style="margin-top:16px">Lưu ý: admin React tại <a href="/admin">/admin</a> vẫn quản lý món/ nguyên liệu/ quả (sửa DB, cuối phiên chạy <code>lacdoi:export-data</code>). Ba module ở đây chưa có trong React và chưa được <code>export-data</code> phủ, nên sửa file trực tiếp là cách duy nhất không mất dữ liệu khi seed lại.</p>
@endsection
