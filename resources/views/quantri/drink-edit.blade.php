@extends('layouts.app')
@section('title', 'Sửa nước uống — ' . $slug)
@section('content')
<a href="{{ route('quantri.list', ['module' => $module]) }}" class="ld-note">← {{ $label }}</a>
<h1 class="ld-title" style="margin-top:8px">Sửa: {{ $data['name_vi'] }}</h1>
<p class="ld-note">Chỉ sửa được ghi chú và cờ. Công thức, nguyên liệu, lượng đường/cồn KHÔNG sửa ở đây (đổi công thức = biên soạn lại qua file + duyệt). Đồ đóng gói không có recipe_steps — không thêm công thức giả.</p>
<form method="POST" action="{{ route('quantri.update', ['module' => $module, 'slug' => $slug]) }}" style="margin-top:14px;display:grid;gap:14px">
    @csrf
    @foreach (['cultural_note' => 'Ghi chú văn hóa', 'sugar_note' => 'Ghi chú đường', 'alcohol_note' => 'Ghi chú cồn', 'label_note' => 'Thông tin nhãn (đồ đóng gói)', 'uncertain_note' => 'Ghi chú chưa chắc chắn'] as $f => $l)
    <label class="ld-field">{{ $l }}
        <textarea name="{{ $f }}" rows="3" class="ld-input">{{ old($f, $data[$f]) }}</textarea>
    </label>
    @endforeach
    <label class="ld-field">Mức caffeine (định tính, không ghi mg khi chưa có nguồn đo)
        <select name="caffeine_level" class="ld-input">
            @foreach (['khong' => 'Không', 'thap' => 'Thấp', 'trung-binh' => 'Trung bình', 'cao' => 'Cao'] as $v => $l)
            <option value="{{ $v }}" @selected(old('caffeine_level', $data['caffeine_level']) === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </label>
    <label><input type="checkbox" name="uncertain" value="1" @checked(old('uncertain', $data['uncertain'] ?? false))> Chưa chắc chắn (hiện nhãn lưu ý)</label>
    <label><input type="checkbox" name="needs_review" value="1" @checked(old('needs_review', $data['needs_review'] ?? false))> Cần xem lại (chưa duyệt)</label>
    <button class="ld-btn ld-btn-primary">Lưu vào file data/</button>
</form>
@endsection
