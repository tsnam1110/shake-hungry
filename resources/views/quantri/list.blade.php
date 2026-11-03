@extends('layouts.app')
@section('title', 'Quản trị — ' . $label)
@section('content')
<a href="{{ route('quantri.index') }}" class="ld-note">← Quản trị dữ liệu</a>
<h1 class="ld-title" style="margin-top:8px">{{ $label }}</h1>
@if (session('status'))<div class="ld-warn"><span>{{ session('status') }}</span></div>@endif
<form method="GET" style="margin-top:12px;display:flex;gap:8px">
    <input name="q" value="{{ $q }}" placeholder="Tìm theo tên hoặc slug…" class="ld-input" style="flex:1">
    <button class="ld-btn">Tìm</button>
</form>
<p class="ld-note" style="margin-top:10px">{{ count($rows) }} bản ghi.</p>
<ul class="ld-list" style="margin-top:8px">
    @foreach ($rows as $r)
    <li class="ld-row"><a href="{{ route('quantri.edit', ['module' => $module, 'slug' => $r['slug']]) }}" style="font-weight:600">{{ $r['title'] }}</a>
        <span class="ld-note">· {{ $r['slug'] }}</span>
        @if ($r['needs_review'])<span class="ld-badge">Chưa duyệt</span>@endif
        @if ($r['uncertain'])<span class="ld-badge">Chưa chắc chắn</span>@endif
    </li>
    @endforeach
</ul>
@endsection
