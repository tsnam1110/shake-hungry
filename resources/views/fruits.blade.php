@extends('layouts.app')

@section('title', !empty($library) ? 'Thư viện · Hoa quả — Lắc Đói' : 'Hoa quả theo mùa — Lắc Đói')
@section('page_class', 'ld-page-wide')

@section('content')
    @if(!empty($library))
    {{-- Tab Hoa quả trong Thư viện (UI-023): dùng chung trang hoa quả, thêm thanh chuyển mục --}}
    <div class="ld-seg" role="navigation" aria-label="Chuyển mục khám phá" style="max-width:440px;margin-top:14px">
        <a href="/thu-vien">Món ăn</a>
        <a href="/nguyen-lieu">Nguyên liệu</a>
        <a href="/thu-vien/hoa-qua" class="on" aria-current="page">Hoa quả</a>
        <a href="/thu-vien/do-uong">Đồ uống</a>
    </div>
    <h1 class="ld-title" style="margin-top:16px">Thư viện</h1>
    <h2 class="ld-section-title" style="margin-top:10px">Hoa quả theo mùa</h2>
    @else
    <h1 class="ld-title" style="margin-top:14px">Hoa quả theo mùa</h1>
    @endif
    <p class="ld-subtitle">@if(!empty($library))Toàn bộ hoa quả trong thư viện — mở sẵn toàn danh sách, dùng Bộ lọc mở rộng để lọc theo dưỡng chất, nhóm quả và mùa vụ.@else Tháng nào nên ăn quả gì — cân bằng cả dinh dưỡng lẫn giá. Giá là khoảng tham khảo theo mùa vụ, không phải giá hôm nay.@endif</p>

    <div class="ld-card" style="margin-top:18px">
        <div class="ld-search" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
            <input id="q" type="search" placeholder="Tìm quả… (cả tên gọi khác, VD: khóm)" aria-label="Tìm quả">
        </div>
        @if(!empty($library))
        <button type="button" id="adv-toggle" class="ld-btn ld-btn-gray" style="width:100%;margin-top:14px;padding:11px;font-size:15px">Bộ lọc mở rộng <span id="adv-count"></span> ▾</button>
        <div id="adv-panel" hidden>
        @endif
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" style="margin-top:12px">
            <div><label class="ld-field-label" for="month">Tháng</label><select id="month" class="ld-select"></select></div>
            <div><label class="ld-field-label" for="group">Nhóm quả</label>
                <select id="group" class="ld-select">
                    <option value="">Mọi nhóm quả</option>
                    <option value="co-mui">Có múi</option>
                    <option value="mong">Quả mọng</option>
                    <option value="hach">Quả hạch</option>
                    <option value="nhiet-doi">Nhiệt đới</option>
                    <option value="dua">Họ dưa</option>
                    <option value="khac">Khác</option>
                </select></div>
            <div><label class="ld-field-label" for="origin">Nguồn gốc</label>
                <select id="origin" class="ld-select">
                    <option value="">Mọi nguồn gốc</option>
                    <option value="trong-nuoc">Trong nước</option>
                    <option value="nhap-khau">Nhập khẩu</option>
                    <option value="ca-hai">Cả hai</option>
                </select></div>
        </div>
        @if(!empty($library))
        <p class="ld-field-label" style="margin-top:12px">Dưỡng chất (trên 100g, chọn nhiều được)</p>
        <div class="ld-chips" id="nutrient-filters">
            <button type="button" class="ld-chip" data-nutrient="kcal-low">Ít calo (≤ 60 kcal)</button>
            <button type="button" class="ld-chip" data-nutrient="fiber-high">Nhiều chất xơ (≥ 3g)</button>
            <button type="button" class="ld-chip" data-nutrient="carb-low">Ít bột đường (≤ 10g)</button>
            <button type="button" class="ld-chip" data-nutrient="water-high">Nhiều nước (≥ 85g)</button>
        </div>
        <div style="margin-top:12px"><label class="ld-field-label" for="sort">Sắp xếp</label>
            <select id="sort" class="ld-select">
                <option value="">Mặc định</option><option value="name">Tên A–Z</option>
                <option value="kcal_asc">Kcal thấp → cao</option><option value="kcal_desc">Kcal cao → thấp</option>
                <option value="fiber_desc">Nhiều chất xơ nhất</option>
            </select></div>
        @endif
        <div style="margin-top:6px">
            <div class="ld-row" style="padding-left:2px;padding-right:2px">
                <span class="grow">Đang có hàng trong tháng đã chọn</span>
                <button class="ld-switch" type="button" role="switch" aria-checked="{{ !empty($library) ? 'false' : 'true' }}" id="only-season" aria-label="Đang có hàng"></button>
            </div>
            <div class="ld-row" style="padding-left:2px;padding-right:2px">
                <span class="grow">Chỉ quả hời nhất tháng này</span>
                <button class="ld-switch" type="button" role="switch" aria-checked="false" id="only-value" aria-label="Hời nhất tháng này"></button>
            </div>
        </div>
        @if(!empty($library))
        </div>
        @endif
    </div>

    <div id="list" class="ld-grid" style="margin-top:16px"></div>
    <p id="empty" class="ld-note" style="margin-top:20px" hidden>Không có quả nào khớp bộ lọc.</p>
@endsection

@push('scripts')
<script>
const LIBRARY = {{ !empty($library) ? 'true' : 'false' }};
const now = new Date();
const monthSel = document.getElementById('month');
// Trang theo mùa (menu): ưu tiên tháng hiện tại. Tab Thư viện: mở toàn danh sách.
monthSel.innerHTML = '<option value="">Tất cả các tháng</option>' + Array.from({length: 12}, (_, i) =>
    `<option value="${i + 1}" ${!LIBRARY && i + 1 === now.getMonth() + 1 ? 'selected' : ''}>Tháng ${i + 1}</option>`).join('');
const nutrients = new Set();
if (LIBRARY) {
    document.querySelectorAll('#nutrient-filters .ld-chip').forEach(b => b.addEventListener('click', () => {
        b.classList.toggle('active');
        b.classList.contains('active') ? nutrients.add(b.dataset.nutrient) : nutrients.delete(b.dataset.nutrient);
        load();
    }));
    document.getElementById('sort').addEventListener('change', load);
    document.getElementById('adv-toggle').addEventListener('click', () => {
        const p = document.getElementById('adv-panel');
        p.hidden = !p.hidden;
        document.getElementById('adv-toggle').lastChild.textContent = p.hidden ? ' ▾' : ' ▴';
    });
}
function updateAdvCount() {
    if (!LIBRARY) return;
    let n = nutrients.size;
    if (monthSel.value) n++;
    if (document.getElementById('group').value) n++;
    if (document.getElementById('origin').value) n++;
    if (document.getElementById('sort').value) n++;
    if (isOn('only-season')) n++;
    if (isOn('only-value')) n++;
    document.getElementById('adv-count').textContent = n ? `(${n} đang bật)` : '';
}
const groupName = { 'co-mui': 'Có múi', mong: 'Quả mọng', hach: 'Quả hạch', 'nhiet-doi': 'Nhiệt đới', dua: 'Họ dưa', khac: 'Khác' };
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
const heartSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"><path d="M12 20.5C7 16.5 3.8 13.3 3.8 9.6 3.8 7 5.7 5 8.1 5c1.6 0 3.1.9 3.9 2.3C12.8 5.9 14.3 5 15.9 5c2.4 0 4.3 2 4.3 4.6 0 3.7-3.2 6.9-8.2 10.9Z"/></svg>';
function isOn(id) { return document.getElementById(id).getAttribute('aria-checked') === 'true'; }
['only-season', 'only-value'].forEach(id => {
    const el = document.getElementById(id);
    el.addEventListener('click', () => { el.setAttribute('aria-checked', el.getAttribute('aria-checked') === 'true' ? 'false' : 'true'); load(); });
});

async function load() {
    const params = new URLSearchParams();
    const q = document.getElementById('q').value.trim();
    const month = monthSel.value;
    if (q) params.set('q', q);
    if (month && isOn('only-season')) params.set('month', month);
    // Nút "hời nhất tháng này": chưa chọn tháng thì mặc định tháng hiện tại.
    if (isOn('only-value')) params.set('value_month', month || String(now.getMonth() + 1));
    const group = document.getElementById('group').value;
    if (group) params.set('group', group);
    const origin = document.getElementById('origin').value;
    if (origin) params.set('origin', origin);
    if (LIBRARY) {
        params.set('per_page', '100'); // Thư viện mở toàn danh sách
        if (nutrients.size) params.set('nutrient', [...nutrients].join(','));
        const sort = document.getElementById('sort').value;
        if (sort) params.set('sort', sort);
        updateAdvCount();
    }
    const res = await fetch('/api/fruits?' + params, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    // Huy hiệu theo tháng cũng bám tháng hiệu lực: hời nhất bật mà chưa chọn
    // tháng thì hiểu là tháng hiện tại.
    const m = month ? parseInt(month, 10) : (isOn('only-value') ? now.getMonth() + 1 : null);
    document.getElementById('list').innerHTML = (data.data ?? []).map(f => `
        <a href="/hoa-qua/${f.slug}" class="ld-dcard">
            <img class="ld-dcard-img" src="${f.image?.thumb_url ?? f.image?.url ?? '/images/placeholder-fruit.svg'}" srcset="${f.image?.srcset ?? ''}" sizes="(max-width: 640px) 50vw, 320px" alt="${esc(f.name_vi)}" loading="lazy" decoding="async">
            <span class="ld-heart" data-fav-fruit="${f.id}" role="button" aria-label="Yêu thích quả này">${heartSvg}</span>
            <span class="ld-dcard-body">
                <span class="ld-dcard-title">${esc(f.name_vi)}</span>
                <span class="ld-dcard-meta">${groupName[f.classification_group] ?? ''} · ${f.kcal_per_100g != null ? Math.round(f.kcal_per_100g) + ' kcal/100g' : 'chưa có số calo'}${f.aliases?.length ? ' · ' + f.aliases.map(a => esc(a.name)).join(', ') : ''}</span>
                <span class="ld-dcard-badges">${m && (f.main_season_months ?? []).includes(m) ? '<span class="ld-badge vu">Đang vụ chính</span>' : ''}${m && (f.best_value_months ?? []).includes(m) ? '<span class="ld-badge hoi">Hời nhất tháng này</span>' : ''}${m && (f.best_nutrition_months ?? []).includes(m) ? '<span class="ld-badge bo">Bổ nhất lúc này</span>' : ''}${f.unverified ? '<span class="ld-badge unv">Số chưa kiểm định</span>' : ''}</span>
                <span class="ld-dcard-foot"><span class="ld-seasonbar" aria-hidden="true">${Array.from({length: 12}, (_, i) =>
                    `<i class="${(f.main_season_months ?? []).includes(i + 1) ? 'main' : (f.availability_months ?? []).includes(i + 1) ? 'on' : ''}"></i>`).join('')}</span></span>
            </span>
        </a>`).join('');
    if (window.ldRefreshHearts) window.ldRefreshHearts();
    document.getElementById('empty').hidden = (data.data ?? []).length > 0;
}
['q', 'group', 'origin'].forEach(id => document.getElementById(id).addEventListener('input', load));
monthSel.addEventListener('change', load);
load();
</script>
@endpush
