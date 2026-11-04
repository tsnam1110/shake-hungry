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
                <button type="button" class="ld-chip active" data-tier="">Tất cả ({{ count($combinations) }})</button>
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
        @foreach ($combinations as $c)
        <article class="ld-card combo-card" data-tier="{{ $c['tier'] }}" data-cat="{{ $c['category'] }}" data-text="{{ mb_strtolower($c['side_a_label'] . ' ' . $c['side_b_label']) }}" style="margin-top:12px">
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
                <span class="ld-chip active" style="cursor:default">{{ $c['tier_label'] }}</span>
                <span class="ld-chip" style="cursor:default">{{ $c['category'] === 'drug_food' ? 'Thuốc × thực phẩm' : 'Thực phẩm × thực phẩm' }}</span>
                <span style="font-size:13.5px;color:var(--color-faint)">{{ $c['science_label'] }}</span>
            </div>
            <div style="display:flex;gap:8px;align-items:center;margin-top:12px">
                @foreach ([['label' => $c['side_a_label'], 'image' => $c['side_a_image']], ['label' => $c['side_b_label'], 'image' => $c['side_b_image']]] as $side)
                    @if (!empty($side['image']))
                        <img src="{{ $side['image']['thumb_url'] ?? $side['image']['url'] }}" srcset="{{ $side['image']['srcset'] ?? '' }}" sizes="84px" alt="{{ $side['label'] }}" loading="lazy" decoding="async" style="width:84px;height:84px;object-fit:cover;border-radius:12px;display:block;background:var(--color-fill)">
                    @else
                        <img src="/images/placeholder-ingredient.svg" alt="" loading="lazy" decoding="async" style="width:84px;height:84px;object-fit:cover;border-radius:12px;display:block;background:var(--color-fill)">
                    @endif
                    @if (!$loop->last)
                        <span aria-hidden="true" style="font-size:20px;font-weight:700;color:var(--color-faint)">×</span>
                    @endif
                @endforeach
            </div>
            @if (!empty($c['side_a_image']['representative']) || !empty($c['side_b_image']['representative']))
                <p class="ld-note" style="margin-top:6px">Ảnh đại diện:
                    @foreach (array_filter([$c['side_a_image']['representative'] ?? null, $c['side_b_image']['representative'] ?? null]) as $rep)
                        {{ $rep }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                    — ảnh của một thành viên trong nhóm, không phải toàn bộ nhóm.</p>
            @endif
            <h2 style="margin:10px 0 0;font-size:18px;font-weight:700">{{ $c['side_a_label'] }} × {{ $c['side_b_label'] }}</h2>
            <p style="margin:8px 0 0;font-size:15px;line-height:1.6">{{ $c['condition_vi'] }}</p>
            @if (!empty($c['mechanism_vi']))
            <p class="ld-note" style="margin-top:8px">Cơ chế: {{ $c['mechanism_vi'] }}</p>
            @endif
            @if (!empty($c['sources']))
            <p class="ld-note" style="margin-top:6px">Nguồn:
                @foreach ($c['sources'] as $s)
                    @if (!empty($s['url']))<a href="{{ $s['url'] }}" target="_blank" rel="noopener">{{ $s['note'] ?? $s['url'] }}</a>@else{{ $s['note'] ?? '' }}@endif{{ !$loop->last ? '; ' : '' }}
                @endforeach
            </p>
            @endif
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
    let shown = 0;
    document.querySelectorAll('.combo-card').forEach(el => {
        const ok = (!tier || el.dataset.tier === tier) && (!cat || el.dataset.cat === cat) && (!q || el.dataset.text.includes(q));
        el.hidden = !ok;
        if (ok) shown++;
    });
    document.getElementById('empty').hidden = shown > 0;
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
