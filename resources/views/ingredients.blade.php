@extends('layouts.app')

@section('title', 'Nguyên liệu — Lắc Đói')
@section('page_class', 'ld-page-wide')

@section('content')
    <div class="ld-seg" role="navigation" aria-label="Chuyển mục khám phá" style="max-width:340px;margin-top:14px">
        <a href="/kham-pha">Món ăn</a>
        <a href="/nguyen-lieu" class="on" aria-current="page">Nguyên liệu</a>
    </div>

    <h1 class="ld-title" style="margin-top:16px">Nguyên liệu</h1>
    <p class="ld-subtitle">Tra cứu toàn bộ nguyên liệu trong kho dữ liệu của Lắc Đói: số dinh dưỡng trên 100g, nguồn gốc con số, nhãn dị ứng và các món có dùng nguyên liệu đó. Tìm được cả theo tên gọi khác và tên vùng miền.</p>

    <div class="ld-card" style="margin-top:18px">
        <div class="ld-search" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
            <input id="q" type="search" placeholder="Tìm nguyên liệu (cả tên gọi khác)" aria-label="Tìm nguyên liệu">
        </div>
        <div class="ld-chips-scroll" id="allergen-filters" style="margin-top:12px" role="group" aria-label="Lọc theo chất gây dị ứng"></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" style="margin-top:4px">
            <div><label class="ld-field-label" for="category">Nhóm nguyên liệu</label>
                <select id="category" class="ld-select">
                    <option value="">Mọi nhóm</option>
                    <option value="thit">Thịt</option>
                    <option value="thuy-san">Thuỷ sản</option>
                    <option value="rau-cu">Rau củ</option>
                    <option value="rau-gia-vi">Rau gia vị</option>
                    <option value="trai-cay">Trái cây</option>
                    <option value="tinh-bot">Tinh bột</option>
                    <option value="gia-vi">Gia vị</option>
                    <option value="dau-mo">Dầu mỡ</option>
                    <option value="khac">Khác</option>
                </select></div>
            <div><label class="ld-field-label" for="sort">Sắp xếp</label>
                <select id="sort" class="ld-select">
                    <option value="">Tên A–Z</option>
                    <option value="kcal_asc">Kcal thấp → cao</option>
                    <option value="kcal_desc">Kcal cao → thấp</option>
                </select></div>
        </div>
        <div class="ld-row" style="padding-left:2px;padding-right:2px">
            <span class="grow">Chỉ nguyên liệu đã có số dinh dưỡng</span>
            <button class="ld-switch" type="button" role="switch" aria-checked="false" id="with-nutrition" aria-label="Chỉ nguyên liệu đã có số dinh dưỡng"></button>
        </div>
    </div>

    <p class="ld-note" id="count-line" style="margin:18px 2px 10px"></p>
    <div class="ld-grid" id="catalog"></div>
    <div style="text-align:center;margin-top:20px">
        <button id="more-btn" class="ld-btn ld-btn-gray" style="display:none">Tải thêm nguyên liệu</button>
    </div>
@endsection

@push('scripts')
<script>
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
const catLabel = { 'thit': 'Thịt', 'thuy-san': 'Thuỷ sản', 'rau-cu': 'Rau củ', 'rau-gia-vi': 'Rau gia vị', 'trai-cay': 'Trái cây', 'tinh-bot': 'Tinh bột', 'gia-vi': 'Gia vị', 'dau-mo': 'Dầu mỡ', 'khac': 'Khác' };
let page = 1, lastPage = 1, total = 0;
const allergenTags = new Set();

fetch('/api/tags').then(r => r.json()).then(ts => {
    const usable = ts.filter(t => t.facet === 'allergen' && t.ingredients > 0);
    document.getElementById('allergen-filters').innerHTML = usable.map(t =>
        `<button type="button" class="ld-chip" data-slug="${t.slug}">Dị ứng: ${esc(t.name)} (${t.ingredients})</button>`).join('');
    document.querySelectorAll('#allergen-filters .ld-chip').forEach(b => b.addEventListener('click', () => {
        b.classList.toggle('active');
        b.classList.contains('active') ? allergenTags.add(b.dataset.slug) : allergenTags.delete(b.dataset.slug);
        reload();
    }));
});
document.getElementById('with-nutrition').addEventListener('click', (e) => {
    const el = e.currentTarget;
    el.setAttribute('aria-checked', el.getAttribute('aria-checked') === 'true' ? 'false' : 'true');
    reload();
});

function cardHtml(i) {
    const snap = i.nutrition_snapshot ?? {};
    const allergens = (i.tags ?? []).filter(t => t.slug.startsWith('allergen:'));
    return `<a href="/nguyen-lieu/${i.slug}" class="ld-dcard">
        <img class="ld-dcard-img" src="${i.image?.url ?? '/images/placeholder-ingredient.svg'}" alt="${esc(i.name_vi)}" loading="lazy">
        <span class="ld-dcard-body">
            <span class="ld-dcard-title">${esc(i.name_vi)}</span>
            <span class="ld-dcard-meta">${catLabel[i.category] ?? esc(i.category ?? '')}${i.scientific_name ? ' · ' + esc(i.scientific_name) : ''}</span>
            <span class="ld-dcard-badges">${allergens.map(t => `<span class="ld-badge" style="background:var(--color-danger-tint);color:var(--color-danger)">Dị ứng: ${esc(t.name)}</span>`).join('')}${i.unverified ? '<span class="ld-badge unv">Chưa kiểm định</span>' : ''}${i.image?.is_ai ? '<span class="ld-badge">Ảnh minh họa</span>' : ''}</span>
            <span class="ld-dcard-foot">${snap.kcal_per_100g != null
                ? `<span class="ld-dcard-kcal ld-tnum">${Math.round(snap.kcal_per_100g)} kcal<span style="font-weight:500;color:var(--color-faint)">/100g</span></span>`
                : `<span class="ld-dcard-kcal none">Chưa đủ dữ liệu</span><span class="ld-dcard-conf">không hiện số</span>`}</span>
        </span>
    </a>`;
}

async function loadPage(append) {
    const params = new URLSearchParams({ per_page: 24, page });
    const q = document.getElementById('q').value.trim();
    if (q) params.set('q', q);
    allergenTags.forEach(t => params.append('tag', t));
    const cat = document.getElementById('category').value;
    if (cat) params.set('category', cat);
    const sort = document.getElementById('sort').value;
    if (sort) params.set('sort', sort);
    if (document.getElementById('with-nutrition').getAttribute('aria-checked') === 'true') params.set('with_nutrition', 1);
    const data = await (await fetch('/api/ingredients?' + params)).json();
    total = data.total ?? 0; lastPage = data.last_page ?? 1;
    const html = (data.data ?? []).map(cardHtml).join('');
    const box = document.getElementById('catalog');
    box.innerHTML = append ? box.innerHTML + html : (html || '<p style="color:var(--color-faint);margin:0">Không thấy nguyên liệu nào khớp bộ lọc.</p>');
    const shown = box.querySelectorAll('.ld-dcard').length;
    document.getElementById('count-line').textContent = total ? `Đang hiện ${shown} trong ${total} nguyên liệu` : '';
    document.getElementById('more-btn').style.display = page < lastPage ? '' : 'none';
}
function reload() { page = 1; loadPage(false); }
document.getElementById('more-btn').addEventListener('click', () => { page++; loadPage(true); });
['category', 'sort'].forEach(id => document.getElementById(id).addEventListener('change', reload));
let qt; document.getElementById('q').addEventListener('input', () => { clearTimeout(qt); qt = setTimeout(reload, 350); });
reload();
</script>
@endpush
