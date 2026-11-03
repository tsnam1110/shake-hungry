@extends('layouts.app')
@section('title', 'Sửa kết hợp — ' . $slug)
@section('content')
<a href="{{ route('quantri.list', ['module' => $module]) }}" class="ld-note">← {{ $label }}</a>
<h1 class="ld-title" style="margin-top:8px">Sửa: {{ $data['side_a']['ref'] ?? '' }} + {{ $data['side_b']['ref'] ?? '' }}</h1>
<p class="ld-note">Chỉ sửa được ghi chú và trạng thái khoa học. Hai phía của cặp, loại cặp và bằng chứng gốc KHÔNG sửa ở đây (đổi cặp = tạo bản ghi mới qua file + duyệt).</p>
<form method="POST" action="{{ route('quantri.update', ['module' => $module, 'slug' => $slug]) }}" style="margin-top:14px;display:grid;gap:14px">
    @csrf
    <label class="ld-field">Điều kiện áp dụng (tiếng Việt)
        <textarea name="condition_vi" rows="4" class="ld-input">{{ old('condition_vi', $data['condition_vi']) }}</textarea>
    </label>
    <label class="ld-field">Cơ chế (tiếng Việt)
        <textarea name="mechanism_vi" rows="4" class="ld-input">{{ old('mechanism_vi', $data['mechanism_vi']) }}</textarea>
    </label>
    <label class="ld-field">Trạng thái khoa học
        <select name="science_status" class="ld-input">
            @foreach (['supported' => 'Có bằng chứng', 'conditional' => 'Có điều kiện', 'unproven' => 'Chưa chứng minh', 'refuted' => 'Đã bác bỏ'] as $v => $l)
            <option value="{{ $v }}" @selected(old('science_status', $data['science_status']) === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </label>
    <label><input type="checkbox" name="needs_review" value="1" @checked(old('needs_review', $data['needs_review'] ?? false))> Cần xem lại (chưa duyệt)</label>
    <button class="ld-btn ld-btn-primary">Lưu vào file data/</button>
</form>
@endsection
