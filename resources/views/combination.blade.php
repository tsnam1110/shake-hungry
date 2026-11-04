@extends('layouts.app')

@section('title', $group['label'] . ' — Kết hợp cần lưu ý — Lắc Đói')
@section('page_class', 'ld-page-wide')

@section('content')
    <a href="/ket-hop-can-luu-y" style="display:inline-flex;align-items:center;gap:3px;color:var(--color-link);font-size:16.5px;font-weight:500;text-decoration:none;padding-top:10px">
        <svg width="11" height="18" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="transform:rotate(180deg)"><path d="M1 1l6 6-6 6"/></svg>
        Kết hợp cần lưu ý</a>

    <h1 class="ld-title" style="margin-top:16px">{{ $group['label'] }}</h1>
    <p class="ld-subtitle">{{ $group['count'] }} cặp cần lưu ý
        · {{ collect($group['categories'])->map(fn ($c) => $c === 'drug_food' ? 'Thuốc × thực phẩm' : 'Thực phẩm × thực phẩm')->join(' · ') }}</p>
    @if (!empty($group['member_labels']))
        <p class="ld-note" style="margin-top:8px">Nhóm này gồm: {{ implode(', ', $group['member_labels']) }} — lưu ý dưới đây áp dụng cho cả nhóm, nhưng mỗi cặp vẫn có mức độ và cơ chế riêng.</p>
    @endif

    @if (!empty($group['image']))
        <figure style="margin:18px 0 0">
            <img src="{{ $group['image']['url'] }}" srcset="{{ $group['image']['srcset'] ?? '' }}" alt="{{ $group['label'] }}" style="width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:18px;display:block" fetchpriority="high" decoding="async">
            <figcaption style="color:var(--color-faint);font-size:12.5px;margin-top:6px">
                @if (!empty($group['image']['representative']))
                    Ảnh đại diện: {{ $group['image']['representative'] }} — ảnh của một thành viên trong nhóm, không phải toàn bộ nhóm.
                @elseif (!empty($group['image']['is_ai']))
                    Ảnh minh họa
                @elseif (!empty($group['image']['attribution']))
                    Ảnh: {{ $group['image']['attribution'] }}
                @endif
            </figcaption>
        </figure>
    @endif

    @if (!empty($group['url']))
        <a href="{{ $group['url'] }}" class="ld-row" style="margin-top:16px;text-decoration:none"><span class="grow"><span style="font-weight:600">Hồ sơ {{ $group['label'] }}</span><span class="sub">Dinh dưỡng, dị ứng và các thông tin khác của phía này</span></span><span style="color:var(--color-faint);font-size:19px">›</span></a>
    @endif

    <div class="ld-card" style="margin-top:18px;border-left:4px solid var(--color-warn, #d97706)">
        <p style="margin:0;font-size:15px;line-height:1.6">{{ $disclaimer }}</p>
        <p class="ld-note" style="margin-top:10px">Đây là thông tin tham khảo, <b>không phải danh sách cấm</b> và không làm món bị loại khỏi Lắc. Tầng 1 “Tránh kết hợp” và tầng 2 “Không nên kết hợp” là lưu ý đã có cơ sở; tầng 3 là quan niệm dân gian / đông y chưa có bằng chứng.</p>
    </div>

    @foreach ($group['pairs'] as $c)
    <section class="ld-card" id="pair-{{ $c['slug'] }}" style="margin-top:16px;scroll-margin-top:16px">
        <h2 style="margin:0;font-size:18px;font-weight:700">{{ $group['label'] }} × {{ $c['side_b_label'] }}</h2>
        <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:10px">
            <span class="ld-badge bo">{{ $c['tier_label'] }}</span>
            <span class="ld-chip" style="cursor:default">{{ $c['category'] === 'drug_food' ? 'Thuốc × thực phẩm' : 'Thực phẩm × thực phẩm' }}</span>
        </div>
        <p style="margin:12px 0 0;font-size:15px;line-height:1.6"><b>Bằng chứng:</b> {{ $c['science_label'] }}</p>
        <p style="margin:10px 0 0;font-size:15px;line-height:1.6">{{ $c['condition_vi'] }}</p>
        @if (!empty($c['counterpart_member_labels']))
            <p class="ld-note" style="margin-top:8px">Phía này gồm: {{ implode(', ', $c['counterpart_member_labels']) }}</p>
        @endif
        @if (!empty($c['mechanism_vi']))
            <p style="margin:10px 0 0;font-size:15px;line-height:1.6"><b>Cơ chế:</b> {{ $c['mechanism_vi'] }}</p>
        @endif
        @if (!empty($c['traditional_source']))
            <p style="margin:10px 0 0;font-size:15px;line-height:1.6"><b>Nguồn dân gian / đông y:</b> {{ $c['traditional_source']['text'] ?? '' }}
                @if (!empty($c['traditional_source']['quote']))<br><i>“{{ $c['traditional_source']['quote'] }}”</i>@endif
            </p>
        @endif
        @if (!empty($c['sources']))
            <p class="ld-note" style="margin-top:10px">Nguồn tham khảo:
                @foreach ($c['sources'] as $s)
                    @if (!empty($s['url']))<a href="{{ $s['url'] }}" target="_blank" rel="noopener">{{ $s['note'] ?? $s['url'] }}</a>@else{{ $s['note'] ?? '' }}@endif{{ !$loop->last ? '; ' : '' }}
                @endforeach
            </p>
        @endif
        @if ($c['category'] === 'drug_food')
            <p class="ld-note" style="margin-top:10px">Nếu đang dùng thuốc này, hãy hỏi bác sĩ hoặc dược sĩ trước khi thay đổi cách ăn uống.</p>
        @endif
    </section>
    @endforeach

    <p style="margin-top:20px"><a href="/ket-hop-can-luu-y" style="color:var(--color-link);text-decoration:none;font-size:15px;font-weight:600">← Quay lại danh sách kết hợp cần lưu ý</a></p>
@endsection
