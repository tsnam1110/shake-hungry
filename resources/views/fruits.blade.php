@extends('layouts.app')

@section('title', 'Hoa quả theo mùa — Lắc Đói')
@section('page_class', 'ld-page-wide')

@section('content')
    <h1 class="ld-title" style="margin-top:14px">Hoa quả theo mùa</h1>
    <p class="ld-subtitle">Tháng nào nên ăn quả gì — cân bằng cả dinh dưỡng lẫn giá. Giá là khoảng tham khảo theo mùa vụ, không phải giá hôm nay.</p>

    <div class="ld-card" style="margin-top:18px">
        <div class="ld-search" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
            <input id="q" type="search" placeholder="Tìm quả… (cả tên gọi khác, VD: khóm)" aria-label="Tìm quả">
        </div>
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
        <div style="margin-top:6px">
            <div class="ld-row" style="padding-left:2px;padding-right:2px">
                <span class="grow">Đang có hàng trong tháng đã chọn</span>
                <button class="ld-switch" type="button" role="switch" aria-checked="true" id="only-season" aria-label="Đang có hàng"></button>
            </div>
            <div class="ld-row" style="padding-left:2px;padding-right:2px">
                <span class="grow">Chỉ quả hời nhất tháng này</span>
                <button class="ld-switch" type="button" role="switch" aria-checked="false" id="only-value" aria-label="Hời nhất tháng này"></button>
            </div>
        </div>
    </div>

    <div id="list" class="ld-grid" style="margin-top:16px"></div>
    <p id="empty" class="ld-note" style="margin-top:20px" hidden>Không có quả nào khớp bộ lọc.</p>
@endsection

@push('scripts')
<script>
const now = new Date();
const monthSel = document.getElementById('month');
monthSel.innerHTML = '<option value="">Tất cả các tháng</option>' + Array.from({length: 12}, (_, i) =>
    `<option value="${i + 1}" ${i + 1 === now.getMonth() + 1 ? 'selected' : ''}>Tháng ${i + 1}</option>`).join('');
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
    if (month && isOn('only-value')) params.set('value_month', month);
    const group = document.getElementById('group').value;
    if (group) params.set('group', group);
    const origin = document.getElementById('origin').value;
    if (origin) params.set('origin', origin);
    const res = await fetch('/api/fruits?' + params, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    const m = month ? parseInt(month, 10) : null;
    document.getElementById('list').innerHTML = (data.data ?? []).map(f => `
        <a href="/hoa-qua/${f.slug}" class="ld-dcard">
            <img class="ld-dcard-img" src="${f.image?.url ?? '/images/placeholder-fruit.svg'}" alt="${esc(f.name_vi)}" loading="lazy">
            <span class="ld-heart" data-fav-fruit="${f.id}" role="button" aria-label="Yêu thích quả này">${heartSvg}</span>
            <span class="ld-dcard-body">
                <span class="ld-dcard-title">${esc(f.name_vi)}</span>
                <span class="ld-dcard-meta">${groupName[f.classification_group] ?? ''} · ${f.kcal_per_100g != null ? Math.round(f.kcal_per_100g) + ' kcal/100g' : 'chưa có số calo'}${f.aliases?.length ? ' · ' + f.aliases.map(a => esc(a.name)).join(', ') : ''}</span>
                <span class="ld-dcard-badges">${m && (f.main_season_months ?? []).includes(m) ? '<span class="ld-badge vu">Đang vụ chính</span>' : ''}${m && (f.best_value_months ?? []).includes(m) ? '<span class="ld-badge hoi">Hời nhất tháng này</span>' : ''}${m && (f.best_nutrition_months ?? []).includes(m) ? '<span class="ld-badge bo">Bổ nhất lúc này</span>' : ''}${f.unverified ? '<span class="ld-badge unv">Số chưa kiểm định</span>' : ''}${f.image?.is_ai ? '<span class="ld-badge">Ảnh minh hoạ</span>' : ''}</span>
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
