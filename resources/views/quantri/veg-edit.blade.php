@extends('layouts.app')
@section('title', 'Sửa rau — ' . $slug)
@section('content')
<a href="{{ route('quantri.list', ['module' => $module]) }}" class="ld-note">← {{ $label }}</a>
<h1 class="ld-title" style="margin-top:8px">Sửa: {{ $data['name_vi'] }}</h1>
<p class="ld-note">Chỉ sửa được ghi chú và cờ. Tháng mùa vụ, nhóm, nguồn cung KHÔNG sửa ở đây (đổi mùa vụ = biên soạn lại qua file + duyệt).</p>
<form method="POST" action="{{ route('quantri.update', ['module' => $module, 'slug' => $slug]) }}" style="margin-top:14px;display:grid;gap:14px">
    @csrf
    @foreach (['selection_note' => 'Cách chọn', 'storage_note' => 'Cách bảo quản', 'cooking_note' => 'Cách chế biến', 'safety_note' => 'Lưu ý an toàn', 'uncertain_note' => 'Ghi chú chưa chắc chắn'] as $f => $l)
    <label class="ld-field">{{ $l }}
        <textarea name="{{ $f }}" rows="3" class="ld-input">{{ old($f, $data[$f]) }}</textarea>
    </label>
    @endforeach
    <label><input type="checkbox" name="uncertain" value="1" @checked(old('uncertain', $data['uncertain'] ?? false))> Chưa chắc chắn (hiện nhãn lưu ý)</label>
    <label><input type="checkbox" name="needs_review" value="1" @checked(old('needs_review', $data['needs_review'] ?? false))> Cần xem lại (chưa duyệt)</label>
    <button class="ld-btn ld-btn-primary">Lưu vào file data/</button>
</form>
@endsection
