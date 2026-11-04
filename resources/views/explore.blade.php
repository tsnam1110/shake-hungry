@extends('layouts.app')

@section('title', 'Thư viện — Lắc Đói')
@section('page_class', 'ld-page-wide')

@section('content')
    <div class="ld-seg" role="navigation" aria-label="Chuyển mục khám phá" style="max-width:440px;margin-top:14px">
        <a href="/thu-vien" class="on" aria-current="page">Món ăn</a>
        <a href="/nguyen-lieu">Nguyên liệu</a>
        <a href="/thu-vien/hoa-qua">Hoa quả</a>
        <a href="/thu-vien/do-uong">Đồ uống</a>
    </div>

    <h1 class="ld-title" style="margin-top:16px">Thư viện</h1>
    <p class="ld-subtitle">Thư viện là nơi tra cứu toàn bộ kho của Lắc Đói — chuyển giữa hai mục Món ăn và Nguyên liệu ở thanh trên. Tại đây bạn tìm kiếm, lọc theo loại món, chế độ ăn, vùng miền và sắp xếp đầy đủ; trang chủ chỉ hiện một phần để dùng nhanh.</p>

    <div class="ld-card" style="margin-top:18px">
        <div class="ld-search" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
            <input id="q" type="search" placeholder="Tìm món ăn (cả tên gọi khác)" aria-label="Tìm món ăn">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" style="margin-top:12px">
            <div><label class="ld-field-label" for="loai">Loại món</label>
                <select id="loai" class="ld-select"><option value="">Mọi loại món</option></select></div>
            <div><label class="ld-field-label" for="sort">Sắp xếp</label>
                <select id="sort" class="ld-select">
                    <option value="">Mới nhất</option>
                    <option value="confidence">Độ uy tín cao nhất</option>
                    <option value="name">Tên A–Z</option>
                    <option value="kcal_asc">Kcal thấp → cao</option><option value="kcal_desc">Kcal cao → thấp</option>
                    <option value="protein_desc">Nhiều đạm nhất</option>
                </select></div>
        </div>
        <p class="ld-field-label" style="margin-top:12px">Chế độ ăn</p>
        <div class="ld-chips-scroll" id="filters" role="group" aria-label="Lọc theo chế độ ăn"></div>
        <button type="button" id="adv-toggle" class="ld-btn ld-btn-gray" style="width:100%;margin-top:14px;padding:11px;font-size:15px">Bộ lọc mở rộng <span id="adv-count"></span> ▾</button>
        <div id="adv-panel" hidden style="margin-top:14px">
            <p class="ld-field-label">Dưỡng chất (trên mỗi khẩu phần, chọn nhiều được)</p>
            <div class="ld-chips" id="nutrient-filters">
                <button type="button" class="ld-chip" data-nutrient="protein-high">Giàu đạm (≥ 20g)</button>
                <button type="button" class="ld-chip" data-nutrient="kcal-low">Ít calo (≤ 400 kcal)</button>
                <button type="button" class="ld-chip" data-nutrient="fiber-high">Nhiều chất xơ (≥ 5g)</button>
                <button type="button" class="ld-chip" data-nutrient="carb-low">Ít bột đường (≤ 30g)</button>
                <button type="button" class="ld-chip" data-nutrient="fat-low">Ít béo (≤ 10g)</button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" style="margin-top:6px">
                <div><label class="ld-field-label" for="region">Vùng miền</label>
                    <select id="region" class="ld-select"><option value="">Mọi vùng miền</option></select></div>
                <div><label class="ld-field-label" for="slot">Bữa phù hợp</label>
                    <select id="slot" class="ld-select">
                        <option value="">Mọi bữa</option><option value="sang">Sáng</option><option value="trua">Trưa</option>
                        <option value="chieu">Chiều</option><option value="toi">Tối</option><option value="an-vat">Ăn vặt</option>
                    </select></div>
            </div>
            <div class="ld-row" style="padding-left:2px;padding-right:2px">
                <span class="grow">Chỉ món đã có số dinh dưỡng</span>
                <button class="ld-switch" type="button" role="switch" aria-checked="false" id="with-nutrition" aria-label="Chỉ món đã có số dinh dưỡng"></button>
            </div>
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
const tags = { diet: null };
const nutrients = new Set();
let page = 1, lastPage = 1, total = 0;

fetch('/api/regions').then(r => r.json()).then(rs => {
    document.getElementById('region').innerHTML = '<option value="">Mọi vùng miền</option>' +
        rs.map(r => `<option value="${r.id}">${esc(r.name)}</option>`).join('');
});
fetch('/api/tags').then(r => r.json()).then(ts => {
    // Loại món chuyển thành dropdown cho gọn (UI-019) — trước đây là hàng
    // chip dài (cơm, phở, bánh, chè…) chiếm nhiều diện tích.
    const loai = ts.filter(t => t.facet === 'loai' && t.dishes > 0);
    document.getElementById('loai').innerHTML = '<option value="">Mọi loại món</option>' +
        loai.map(t => `<option value="${t.slug}">${esc(t.name)} (${t.dishes})</option>`).join('');
    const diets = ts.filter(t => t.facet === 'diet' && t.dishes > 0);
    document.getElementById('filters').innerHTML = diets.map(t =>
        `<button type="button" class="ld-chip" data-slug="${t.slug}">${esc(t.name)} (${t.dishes})</button>`).join('');
    document.querySelectorAll('#filters .ld-chip').forEach(b => b.addEventListener('click', () => {
        tags.diet = tags.diet === b.dataset.slug ? null : b.dataset.slug;
        document.querySelectorAll('#filters .ld-chip').forEach(x =>
            x.classList.toggle('active', x.dataset.slug === tags.diet));
        reload();
    }));
});
document.querySelectorAll('#nutrient-filters .ld-chip').forEach(b => b.addEventListener('click', () => {
    b.classList.toggle('active');
    b.classList.contains('active') ? nutrients.add(b.dataset.nutrient) : nutrients.delete(b.dataset.nutrient);
    updateAdvCount(); reload();
}));
function updateAdvCount() {
    let n = nutrients.size;
    if (document.getElementById('region').value) n++;
    if (document.getElementById('slot').value) n++;
    if (document.getElementById('with-nutrition').getAttribute('aria-checked') === 'true') n++;
    document.getElementById('adv-count').textContent = n ? `(${n} đang bật)` : '';
}
document.getElementById('adv-toggle').addEventListener('click', () => {
    const p = document.getElementById('adv-panel');
    p.hidden = !p.hidden;
    document.getElementById('adv-toggle').lastChild.textContent = p.hidden ? ' ▾' : ' ▴';
});
document.getElementById('with-nutrition').addEventListener('click', (e) => {
    const el = e.currentTarget;
    el.setAttribute('aria-checked', el.getAttribute('aria-checked') === 'true' ? 'false' : 'true');
    updateAdvCount(); reload();
});

function cardHtml(d) {
    const n = d.nutrition;
    const name = d.names?.[0]?.name ?? d.slug;
    const tagList = (d.tags ?? []).filter(t => t.slug.startsWith('loai:') || t.slug === 'diet:chay');
    const unv = (n?.unverified_ingredients ?? []).length > 0;
    const chay = (d.tags ?? []).some(t => t.slug === 'diet:chay');
    return `<a href="/mon/${d.slug}" class="ld-dcard">
        <img class="ld-dcard-img" src="${d.image?.thumb_url ?? d.image?.url ?? '/images/placeholder-dish.svg'}" srcset="${d.image?.srcset ?? ''}" sizes="(max-width: 640px) 50vw, 320px" alt="${esc(name)}" loading="lazy" decoding="async">
        <span class="ld-heart" data-fav-dish="${d.id}" role="button" aria-label="Yêu thích món này">${heartSvg}</span>
        <span class="ld-dcard-body">
            <span class="ld-dcard-title">${esc(name)}</span>
            <span class="ld-dcard-meta">${esc(d.region?.name ?? '')}${tagList.filter(t => t.slug.startsWith('loai:')).map(t => ' · ' + esc(t.name)).join('')}</span>
            <span class="ld-dcard-badges">${chay ? '<span class="ld-badge chay">Chay</span>' : ''}${unv ? '<span class="ld-badge unv">Chưa kiểm định</span>' : ''}</span>
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
    const loai = document.getElementById('loai').value;
    if (loai) params.append('tag', loai);
    if (tags.diet) params.append('tag', tags.diet);
    if (nutrients.size) params.set('nutrient', [...nutrients].join(','));
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
['loai', 'sort'].forEach(id => document.getElementById(id).addEventListener('change', reload));
['region', 'slot'].forEach(id => document.getElementById(id).addEventListener('change', () => { updateAdvCount(); reload(); }));
let qt; document.getElementById('q').addEventListener('input', () => { clearTimeout(qt); qt = setTimeout(reload, 350); });
const q0 = new URLSearchParams(location.search).get('q');
if (q0) document.getElementById('q').value = q0;
reload();
</script>
@endpush
