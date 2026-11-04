@extends('layouts.app')

@section('title', 'Nguyên liệu — Lắc Đói')
@section('page_class', 'ld-page-wide')

@section('content')
    <div class="ld-seg" role="navigation" aria-label="Chuyển mục khám phá" style="max-width:440px;margin-top:14px">
        <a href="/thu-vien">Món ăn</a>
        <a href="/nguyen-lieu" class="on" aria-current="page">Nguyên liệu</a>
        <a href="/thu-vien/hoa-qua">Hoa quả</a>
        <a href="/thu-vien/do-uong">Đồ uống</a>
    </div>

    <h1 class="ld-title" style="margin-top:16px">Nguyên liệu</h1>
    <p class="ld-subtitle">Tra cứu toàn bộ nguyên liệu trong kho dữ liệu của Lắc Đói: số dinh dưỡng trên 100g, nguồn gốc con số, nhãn dị ứng và các món có dùng nguyên liệu đó. Tìm được cả theo tên gọi khác và tên vùng miền.</p>

    <div class="ld-card" style="margin-top:18px">
        <div class="ld-search" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
            <input id="q" type="search" placeholder="Tìm nguyên liệu (cả tên gọi khác)" aria-label="Tìm nguyên liệu">
        </div>
        <label class="ld-field-label" for="allergen-search" style="margin-top:12px">Dị ứng</label>
        {{-- Lọc dị ứng kiểu select2 (không dùng thư viện Select2 thật — JS thuần +
             CSS ld-*): ô chọn nhiều có tìm kiếm, mục đã chọn hiện thành tag có nút
             ×, danh sách thả xuống có số lượng từng nhóm. Logic lọc (tag API)
             giữ nguyên, chỉ đổi giao diện. --}}
        <div class="ld-multi" id="allergen-multi">
            <div class="ld-multi-box" id="allergen-box" role="combobox" aria-expanded="false" aria-haspopup="listbox" aria-controls="allergen-drop">
                <span class="ld-multi-tags" id="allergen-tags"></span>
                <input id="allergen-search" type="text" placeholder="Chọn dị ứng…" aria-label="Tìm và chọn dị ứng" autocomplete="off">
                <span class="ld-multi-caret" aria-hidden="true">▾</span>
            </div>
            <div class="ld-multi-drop" id="allergen-drop" role="listbox" aria-multiselectable="true" hidden></div>
        </div>
        <p class="ld-field-label" style="margin-top:12px">Dưỡng chất (trên 100g, chọn nhiều được)</p>
        <div class="ld-chips" id="nutrient-filters">
            <button type="button" class="ld-chip" data-nutrient="protein-high">Giàu đạm (≥ 10g)</button>
            <button type="button" class="ld-chip" data-nutrient="kcal-low">Ít calo (≤ 100 kcal)</button>
            <button type="button" class="ld-chip" data-nutrient="fiber-high">Nhiều chất xơ (≥ 3g)</button>
            <button type="button" class="ld-chip" data-nutrient="sodium-low">Ít natri (≤ 120mg)</button>
            <button type="button" class="ld-chip" data-nutrient="fat-low">Ít béo (≤ 3g)</button>
        </div>
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
const nutrients = new Set();
document.querySelectorAll('#nutrient-filters .ld-chip').forEach(b => b.addEventListener('click', () => {
    b.classList.toggle('active');
    b.classList.contains('active') ? nutrients.add(b.dataset.nutrient) : nutrients.delete(b.dataset.nutrient);
    reload();
}));

/* Lọc dị ứng kiểu select2: danh sách option lấy từ /api/tags như trước,
   chỉ khác cách chọn — tìm kiếm không dấu, chọn nhiều, tag đã chọn có ×. */
const norm = s => (s ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
let allergenOptions = [];
const allergenBox = document.getElementById('allergen-box');
const allergenDrop = document.getElementById('allergen-drop');
const allergenInput = document.getElementById('allergen-search');
const allergenTagsEl = document.getElementById('allergen-tags');
function renderAllergenTags() {
    allergenTagsEl.innerHTML = [...allergenTags].map(slug => {
        const t = allergenOptions.find(o => o.slug === slug);
        const name = t?.name ?? slug;
        return `<span class="ld-multi-tag">${esc(name)}<button type="button" data-remove="${esc(slug)}" aria-label="Bỏ dị ứng ${esc(name)}">×</button></span>`;
    }).join('');
    allergenTagsEl.querySelectorAll('[data-remove]').forEach(b => b.addEventListener('click', (e) => {
        e.stopPropagation();
        allergenTags.delete(b.dataset.remove);
        renderAllergenTags(); renderAllergenOptions(); reload();
    }));
    allergenInput.placeholder = allergenTags.size ? 'Tìm thêm dị ứng…' : 'Chọn dị ứng…';
}
function renderAllergenOptions() {
    const q = norm(allergenInput.value.trim());
    const list = allergenOptions.filter(o => !q || norm(o.name).includes(q) || norm(o.slug).includes(q));
    allergenDrop.innerHTML = list.length ? list.map(o => {
        const on = allergenTags.has(o.slug);
        return `<button type="button" class="ld-multi-opt${on ? ' selected' : ''}" role="option" aria-selected="${on}" data-slug="${esc(o.slug)}"><span class="ld-multi-check">${on ? '✓' : ''}</span>${esc(o.name)}<span class="ld-multi-count">(${o.ingredients})</span></button>`;
    }).join('') : '<p class="ld-multi-empty">Không có dị ứng nào khớp.</p>';
    allergenDrop.querySelectorAll('[data-slug]').forEach(b => b.addEventListener('click', () => {
        const slug = b.dataset.slug;
        allergenTags.has(slug) ? allergenTags.delete(slug) : allergenTags.add(slug);
        renderAllergenTags(); renderAllergenOptions(); allergenInput.focus(); reload();
    }));
}
function setAllergenDrop(open) {
    allergenDrop.hidden = !open;
    allergenBox.setAttribute('aria-expanded', open ? 'true' : 'false');
}
allergenInput.addEventListener('focus', () => { renderAllergenOptions(); setAllergenDrop(true); });
allergenInput.addEventListener('input', () => { renderAllergenOptions(); setAllergenDrop(true); });
allergenInput.addEventListener('keydown', (e) => { if (e.key === 'Escape') { setAllergenDrop(false); allergenInput.blur(); } });
allergenBox.addEventListener('click', (e) => {
    if (e.target === allergenInput || e.target.closest('[data-remove]')) return;
    setAllergenDrop(allergenDrop.hidden);
    if (!allergenDrop.hidden) allergenInput.focus();
});
document.addEventListener('click', (e) => {
    if (!document.getElementById('allergen-multi').contains(e.target)) setAllergenDrop(false);
});
fetch('/api/tags').then(r => r.json()).then(ts => {
    allergenOptions = ts.filter(t => t.facet === 'allergen' && t.ingredients > 0);
    renderAllergenTags(); renderAllergenOptions();
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
        <img class="ld-dcard-img" src="${i.image?.thumb_url ?? i.image?.url ?? '/images/placeholder-ingredient.svg'}" srcset="${i.image?.srcset ?? ''}" sizes="(max-width: 640px) 50vw, 320px" alt="${esc(i.name_vi)}" loading="lazy" decoding="async">
        <span class="ld-dcard-body">
            <span class="ld-dcard-title">${esc(i.name_vi)}</span>
            <span class="ld-dcard-meta">${catLabel[i.category] ?? esc(i.category ?? '')}${i.scientific_name ? ' · ' + esc(i.scientific_name) : ''}</span>
            <span class="ld-dcard-badges">${allergens.map(t => `<span class="ld-badge" style="background:var(--color-danger-tint);color:var(--color-danger)">Dị ứng: ${esc(t.name)}</span>`).join('')}${i.unverified ? '<span class="ld-badge unv">Chưa kiểm định</span>' : ''}</span>
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
    if (nutrients.size) params.set('nutrient', [...nutrients].join(','));
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
