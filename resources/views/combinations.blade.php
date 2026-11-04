@extends('layouts.app')

@section('title', 'Kết hợp cần lưu ý — Lắc Đói')
@section('page_class', 'ld-page-wide')

@section('content')
    <h1 class="ld-title" style="margin-top:14px">Kết hợp cần lưu ý</h1>
    <p class="ld-subtitle">Bảng tra cứu riêng: khi hai thứ ăn cùng nhau (hoặc thực phẩm gặp thuốc) thì cần lưu ý gì. Không giới hạn ở món ăn — có cả đồ uống như bưởi, bia, cà phê, trà xanh, rượu, sữa. Đây là thông tin tham khảo, <b>không phải danh sách cấm</b> và không làm món bị loại khỏi Lắc.</p>

    <div class="ld-card" style="margin-top:18px;border-left:4px solid var(--color-warn, #d97706)">
        <p style="margin:0;font-size:15px;line-height:1.6">{{ $disclaimer }}</p>
        <p class="ld-note" style="margin-top:10px">Tầng 1 “Tránh kết hợp” và tầng 2 “Không nên kết hợp” là lưu ý đã có cơ sở; tầng 3 là quan niệm dân gian / đông y chưa có bằng chứng — đọc kỹ cột “Bằng chứng” trước khi kiêng thứ gì.</p>
    </div>

    <div class="ld-card" style="margin-top:16px">
        <div class="ld-search" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
            <input id="q" type="search" placeholder="Tìm theo tên một phía… (VD: bưởi, bia, cà phê)" aria-label="Tìm cặp kết hợp">
        </div>
        <div style="margin-top:12px">
            <span class="ld-field-label">Tầng lưu ý</span>
            <div class="ld-chips" id="tier-filters">
                <button type="button" class="ld-chip active" data-tier="">Tất cả ({{ count($groups) }} nhóm · {{ $total_pairs }} cặp)</button>
                <button type="button" class="ld-chip" data-tier="1">Tầng 1 — Tránh kết hợp</button>
                <button type="button" class="ld-chip" data-tier="2">Tầng 2 — Không nên kết hợp</button>
                <button type="button" class="ld-chip" data-tier="3">Tầng 3 — Dân gian / đông y</button>
            </div>
        </div>
        <div style="margin-top:12px">
            <span class="ld-field-label">Loại cặp</span>
            <div class="ld-chips" id="cat-filters">
                <button type="button" class="ld-chip active" data-cat="">Tất cả</button>
                <button type="button" class="ld-chip" data-cat="food_food">Thực phẩm × thực phẩm</button>
                <button type="button" class="ld-chip" data-cat="drug_food">Thuốc × thực phẩm</button>
            </div>
        </div>
    </div>

    <div id="list" style="margin-top:16px">
        @foreach ($groups as $g)
        {{-- Một thẻ = một phía neo + toàn bộ cặp của nó. Gộp chỉ gộp phần đầu;
             từng cặp giữ nguyên lưu ý, cơ chế, nguồn riêng trong dòng của nó. --}}
        <article class="ld-card combo-card" data-text="{{ mb_strtolower($g['label'] . ' ' . collect($g['pairs'])->map(fn ($p) => $p['side_b_label'])->join(' ')) }}" style="margin-top:12px">
            <div style="display:flex;gap:12px;align-items:flex-start">
                @if (!empty($g['image']))
                    <img src="{{ $g['image']['thumb_url'] ?? $g['image']['url'] }}" srcset="{{ $g['image']['srcset'] ?? '' }}" sizes="84px" alt="{{ $g['label'] }}" loading="lazy" decoding="async" style="width:84px;height:84px;object-fit:cover;border-radius:12px;display:block;background:var(--color-fill);flex-shrink:0">
                @else
                    <img src="/images/placeholder-ingredient.svg" alt="" loading="lazy" decoding="async" style="width:84px;height:84px;object-fit:cover;border-radius:12px;display:block;background:var(--color-fill);flex-shrink:0">
                @endif
                <div style="min-width:0">
                    <h2 style="margin:0;font-size:19px;font-weight:700">{{ $g['label'] }}</h2>
                    <p style="margin:6px 0 0;font-size:14.5px;color:var(--color-sub)">{{ $g['count'] }} cặp cần lưu ý
                        · {{ collect($g['categories'])->map(fn ($c) => $c === 'drug_food' ? 'Thuốc × thực phẩm' : 'Thực phẩm × thực phẩm')->join(' · ') }}</p>
                    @if (!empty($g['member_labels']))
                        <p class="ld-note" style="margin-top:4px">Nhóm này gồm: {{ implode(', ', $g['member_labels']) }}</p>
                    @endif
                    @if (!empty($g['image']['representative']))
                        <p class="ld-note" style="margin-top:4px">Ảnh đại diện: {{ $g['image']['representative'] }} — ảnh của một thành viên trong nhóm, không phải toàn bộ nhóm.</p>
                    @endif
                </div>
            </div>

            @foreach ($g['pairs'] as $c)
            <div class="combo-row" data-tier="{{ $c['tier'] }}" data-cat="{{ $c['category'] }}" data-text="{{ mb_strtolower($c['side_b_label'] . ' ' . ($c['condition_vi'] ?? '') . ' ' . ($c['mechanism_vi'] ?? '')) }}" style="margin-top:14px;border-top:1px solid var(--color-line);padding-top:14px">
                <div style="display:flex;gap:10px;align-items:flex-start">
                    @if (!empty($c['side_b_image']))
                        <img src="{{ $c['side_b_image']['thumb_url'] ?? $c['side_b_image']['url'] }}" srcset="{{ $c['side_b_image']['srcset'] ?? '' }}" sizes="56px" alt="{{ $c['side_b_label'] }}" loading="lazy" decoding="async" style="width:56px;height:56px;object-fit:cover;border-radius:10px;display:block;background:var(--color-fill);flex-shrink:0">
                    @endif
                    <div style="min-width:0">
                        <p style="margin:0;font-size:16px;line-height:1.5"><b>{{ $c['side_b_label'] }}</b>
                            <span class="ld-badge bo">{{ $c['tier_label'] }}</span>
                            <span style="font-size:13.5px;color:var(--color-faint)">{{ $c['category'] === 'drug_food' ? 'Thuốc × thực phẩm' : 'Thực phẩm × thực phẩm' }} · {{ $c['science_label'] }}</span>
                        </p>
                        <p style="margin:8px 0 0;font-size:15px;line-height:1.6">{{ $c['condition_vi'] }}</p>
                        @if (!empty($c['counterpart_member_labels']))
                            <p class="ld-note" style="margin-top:6px">Phía này gồm: {{ implode(', ', $c['counterpart_member_labels']) }}</p>
                        @endif
                        @if (!empty($c['mechanism_vi']))
                            <p class="ld-note" style="margin-top:6px">Cơ chế: {{ $c['mechanism_vi'] }}</p>
                        @endif
                        @if (!empty($c['side_b_image']['representative']))
                            <p class="ld-note" style="margin-top:4px">Ảnh đại diện: {{ $c['side_b_image']['representative'] }} — ảnh của một thành viên trong nhóm, không phải toàn bộ nhóm.</p>
                        @endif
                        @if (!empty($c['sources']))
                            <p class="ld-note" style="margin-top:6px">Nguồn:
                                @foreach ($c['sources'] as $s)
                                    @if (!empty($s['url']))<a href="{{ $s['url'] }}" target="_blank" rel="noopener">{{ $s['note'] ?? $s['url'] }}</a>@else{{ $s['note'] ?? '' }}@endif{{ !$loop->last ? '; ' : '' }}
                                @endforeach
                            </p>
                        @endif
                        <p style="margin:8px 0 0"><a href="/ket-hop-can-luu-y/{{ $g['key'] }}#pair-{{ $c['slug'] }}" style="color:var(--color-link);text-decoration:none;font-size:14.5px;font-weight:600">Chi tiết cặp này ›</a></p>
                    </div>
                </div>
            </div>
            @endforeach

            <p style="margin:16px 0 0;border-top:1px solid var(--color-line);padding-top:12px"><a href="/ket-hop-can-luu-y/{{ $g['key'] }}" style="color:var(--color-link);text-decoration:none;font-size:15px;font-weight:600">Xem chi tiết nhóm {{ $g['label'] }} →</a></p>
        </article>
        @endforeach
    </div>
    <p id="empty" class="ld-note" style="margin-top:20px" hidden>Không có cặp nào khớp bộ lọc.</p>
@endsection

@push('scripts')
<script>
let tier = '', cat = '';
function applyComboFilter() {
    const q = (document.getElementById('q').value || '').trim().toLowerCase();
    let shownGroups = 0;
    document.querySelectorAll('.combo-card').forEach(card => {
        let shownRows = 0;
        card.querySelectorAll('.combo-row').forEach(row => {
            const ok = (!tier || row.dataset.tier === tier) && (!cat || row.dataset.cat === cat)
                && (!q || card.dataset.text.includes(q) || row.dataset.text.includes(q));
            row.hidden = !ok;
            if (ok) shownRows++;
        });
        card.hidden = shownRows === 0;
        if (shownRows > 0) shownGroups++;
    });
    document.getElementById('empty').hidden = shownGroups > 0;
}
document.querySelectorAll('#tier-filters .ld-chip').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('#tier-filters .ld-chip').forEach(x => x.classList.remove('active'));
    b.classList.add('active'); tier = b.dataset.tier; applyComboFilter();
}));
document.querySelectorAll('#cat-filters .ld-chip').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('#cat-filters .ld-chip').forEach(x => x.classList.remove('active'));
    b.classList.add('active'); cat = b.dataset.cat; applyComboFilter();
}));
document.getElementById('q').addEventListener('input', applyComboFilter);
</script>
@endpush
