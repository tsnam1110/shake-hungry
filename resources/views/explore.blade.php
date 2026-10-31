@extends('layouts.app')

@section('title', 'Khám phá món — Lắc Đói')
@section('page_class', 'ld-page-wide')

@section('content')
    <h1 class="ld-title" style="margin-top:14px">Khám phá món</h1>
    <p class="ld-subtitle">Toàn bộ kho món của Lắc Đói — tìm kiếm, lọc theo loại món, chế độ ăn, vùng miền và sắp xếp. Trang chủ chỉ hiện một phần để dùng nhanh; đây là trang khám phá đầy đủ.</p>

    <div class="ld-card" style="margin-top:18px">
        <div class="ld-search" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
            <input id="q" type="search" placeholder="Tìm món ăn (cả tên gọi khác)" aria-label="Tìm món ăn">
        </div>
        <div class="ld-chips-scroll" id="filters" style="margin-top:12px" role="group" aria-label="Lọc theo nhãn"></div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" style="margin-top:4px">
            <div><label class="ld-field-label" for="region">Vùng miền</label>
                <select id="region" class="ld-select"><option value="">Mọi vùng miền</option></select></div>
            <div><label class="ld-field-label" for="slot">Bữa phù hợp</label>
                <select id="slot" class="ld-select">
                    <option value="">Mọi bữa</option><option value="sang">Sáng</option><option value="trua">Trưa</option>
                    <option value="chieu">Chiều</option><option value="toi">Tối</option><option value="an-vat">Ăn vặt</option>
                </select></div>
            <div><label class="ld-field-label" for="sort">Sắp xếp</label>
                <select id="sort" class="ld-select">
                    <option value="">Mới nhất</option><option value="name">Tên A–Z</option>
                    <option value="kcal_asc">Kcal thấp → cao</option><option value="kcal_desc">Kcal cao → thấp</option>
                </select></div>
        </div>
        <div class="ld-row" style="padding-left:2px;padding-right:2px">
            <span class="grow">Chỉ món đã có số dinh dưỡng</span>
            <button class="ld-switch" type="button" role="switch" aria-checked="false" id="with-nutrition" aria-label="Chỉ món đã có số dinh dưỡng"></button>
        </div>
    </div>

    <p class="ld-note" id="count-line" style="margin:18px 2px 10px"></p>
    <div class="ld-grid" id="catalog"></div>
    <div style="text-align:center;margin-top:20px">
        <button id="more-btn" class="ld-btn ld-btn-gray" style="display:none">Tải thêm món</button>
    </div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
const confShort = { high: 'Tin cậy cao', medium: 'Tin cậy TB', low: 'Tin cậy thấp' };
const heartSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"><path d="M12 20.5C7 16.5 3.8 13.3 3.8 9.6 3.8 7 5.7 5 8.1 5c1.6 0 3.1.9 3.9 2.3C12.8 5.9 14.3 5 15.9 5c2.4 0 4.3 2 4.3 4.6 0 3.7-3.2 6.9-8.2 10.9Z"/></svg>';
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
const tags = { loai: null, diet: null };
let page = 1, lastPage = 1, total = 0;

fetch('/api/regions').then(r => r.json()).then(rs => {
    document.getElementById('region').innerHTML = '<option value="">Mọi vùng miền</option>' +
        rs.map(r => `<option value="${r.id}">${esc(r.name)}</option>`).join('');
});
fetch('/api/tags').then(r => r.json()).then(ts => {
    const usable = ts.filter(t => (t.facet === 'loai' || t.facet === 'diet') && t.dishes > 0);
    document.getElementById('filters').innerHTML = usable.map(t =>
        `<button type="button" class="ld-chip" data-facet="${t.facet}" data-slug="${t.slug}">${esc(t.name)} (${t.dishes})</button>`).join('');
    document.querySelectorAll('#filters .ld-chip').forEach(b => b.addEventListener('click', () => {
        const f = b.dataset.facet;
        tags[f] = tags[f] === b.dataset.slug ? null : b.dataset.slug;
        document.querySelectorAll(`#filters .ld-chip[data-facet="${f}"]`).forEach(x =>
            x.classList.toggle('active', x.dataset.slug === tags[f]));
        reload();
    }));
});
document.getElementById('with-nutrition').addEventListener('click', (e) => {
    const el = e.currentTarget;
    el.setAttribute('aria-checked', el.getAttribute('aria-checked') === 'true' ? 'false' : 'true');
    reload();
});

function cardHtml(d) {
    const n = d.nutrition;
    const name = d.names?.[0]?.name ?? d.slug;
    const tagList = (d.tags ?? []).filter(t => t.slug.startsWith('loai:') || t.slug === 'diet:chay');
    const unv = (n?.unverified_ingredients ?? []).length > 0;
    const chay = (d.tags ?? []).some(t => t.slug === 'diet:chay');
    return `<a href="/mon/${d.slug}" class="ld-dcard">
        <img class="ld-dcard-img" src="${d.image?.url ?? '/images/placeholder-dish.svg'}" alt="${esc(name)}" loading="lazy">
        <span class="ld-heart" data-fav-dish="${d.id}" role="button" aria-label="Yêu thích món này">${heartSvg}</span>
        <span class="ld-dcard-body">
            <span class="ld-dcard-title">${esc(name)}</span>
            <span class="ld-dcard-meta">${esc(d.region?.name ?? '')}${tagList.filter(t => t.slug.startsWith('loai:')).map(t => ' · ' + esc(t.name)).join('')}</span>
            <span class="ld-dcard-badges">${chay ? '<span class="ld-badge chay">Chay</span>' : ''}${unv ? '<span class="ld-badge unv">Chưa kiểm định</span>' : ''}${d.image?.is_ai ? '<span class="ld-badge">Ảnh minh hoạ</span>' : ''}</span>
            <span class="ld-dcard-foot">${n
                ? `<span class="ld-dcard-kcal ld-tnum">${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)} kcal</span><span class="ld-dcard-conf">${confShort[n.confidence] ?? ''}</span>`
                : `<span class="ld-dcard-kcal none">Chưa đủ dữ liệu</span><span class="ld-dcard-conf">không hiện số</span>`}</span>
        </span>
    </a>`;
}

async function loadPage(append) {
    const params = new URLSearchParams({ per_page: 24, page });
    const q = document.getElementById('q').value.trim();
    if (q) params.set('q', q);
    Object.values(tags).forEach(t => { if (t) params.append('tag', t); });
    const region = document.getElementById('region').value;
    if (region) params.set('region_id', region);
    const slot = document.getElementById('slot').value;
    if (slot) params.set('slot', slot);
    const sort = document.getElementById('sort').value;
    if (sort) params.set('sort', sort);
    if (document.getElementById('with-nutrition').getAttribute('aria-checked') === 'true') params.set('with_nutrition', 1);
    const data = await (await fetch('/api/dishes?' + params)).json();
    total = data.total ?? 0; lastPage = data.last_page ?? 1;
    const html = (data.data ?? []).map(cardHtml).join('');
    const box = document.getElementById('catalog');
    box.innerHTML = append ? box.innerHTML + html : (html || '<p style="color:var(--color-faint);margin:0">Không thấy món nào khớp bộ lọc.</p>');
    const shown = box.querySelectorAll('.ld-dcard').length;
    document.getElementById('count-line').textContent = total ? `Đang hiện ${shown} trong ${total} món` : '';
    document.getElementById('more-btn').style.display = page < lastPage ? '' : 'none';
    if (window.ldRefreshHearts) window.ldRefreshHearts();
}
function reload() { page = 1; loadPage(false); }
document.getElementById('more-btn').addEventListener('click', () => { page++; loadPage(true); });
['region', 'slot', 'sort'].forEach(id => document.getElementById(id).addEventListener('change', reload));
let qt; document.getElementById('q').addEventListener('input', () => { clearTimeout(qt); qt = setTimeout(reload, 350); });
const q0 = new URLSearchParams(location.search).get('q');
if (q0) document.getElementById('q').value = q0;
reload();
</script>
@endpush
