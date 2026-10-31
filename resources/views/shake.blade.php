@extends('layouts.app')

@section('title', 'Lắc Đói — Hôm nay ăn gì?')

@section('content')
    <p class="ld-date" id="date-line"></p>
    <h1 class="ld-title" id="greeting">Hôm nay ăn gì?</h1>
    <p class="ld-subtitle">Lắc một món hợp bữa, đúng vùng miền bạn thích và tránh những gì bạn không ăn được.
        <span id="auth-area"></span></p>

    {{-- ===== Sân khấu Lắc (UI-005) ===== --}}
    <section class="ld-card" style="margin-top:20px" aria-label="Lắc món">
        <div class="ld-seg" id="slots" role="group" aria-label="Chọn bữa"></div>

        <div id="result" aria-live="polite">
            <div style="text-align:center;color:var(--color-faint);padding:26px 10px 8px">
                <span id="shake-icon" class="ld-tile" style="width:52px;height:52px;border-radius:14px;margin-bottom:8px">
                    <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 12h16a8 8 0 0 1-16 0Z"/><path d="M9 8c1-1.6 4.5-1.8 6-.5"/><path d="M15.5 5.5c1.2-1 2.7-1.3 4-1"/></svg>
                </span>
                <p style="margin:6px 0 0">Bấm <b>Lắc một món</b> để nhận gợi ý cho bữa này.</p>
            </div>
        </div>

        <button id="shake-btn" class="ld-btn ld-btn-primary" style="width:100%;margin-top:14px;padding:16px 24px;font-size:18px">
            Lắc một món
        </button>

        <div style="margin-top:18px;border-top:1px solid var(--color-line);padding-top:6px">
            <div class="ld-row" style="padding-left:2px;padding-right:2px">
                <span class="grow">Ăn chay</span>
                <button class="ld-switch" type="button" role="switch" aria-checked="false" id="vegetarian" aria-label="Ăn chay"></button>
            </div>
            <div class="ld-row" style="padding-left:2px;padding-right:2px">
                <span class="grow">Ăn ngoài</span>
                <button class="ld-switch" type="button" role="switch" aria-checked="false" id="eat_out" aria-label="Ăn ngoài"></button>
            </div>
            <div class="ld-row" style="padding-left:2px;padding-right:2px">
                <span class="grow">Tự nấu</span>
                <button class="ld-switch" type="button" role="switch" aria-checked="false" id="home_cook" aria-label="Tự nấu"></button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" style="padding:12px 2px 4px">
                <div>
                    <label class="ld-field-label" for="region">Vùng miền</label>
                    <select id="region" class="ld-select"><option value="">Mọi vùng miền</option></select>
                </div>
                <div>
                    <label class="ld-field-label" for="target_kcal">Kcal mục tiêu mỗi bữa (tuỳ chọn)</label>
                    <input id="target_kcal" type="number" min="100" max="2000" placeholder="Ví dụ: 600" class="ld-input">
                </div>
            </div>
            <p class="ld-field-label" style="margin-top:12px">Không ăn được — lọc này không bao giờ bị nới lỏng</p>
            <div class="ld-chips" id="allergens"></div>
        </div>
    </section>

    {{-- ===== Hôm nay đã ăn (chỉ khi đăng nhập và có ghi bữa) ===== --}}
    <section id="today-section" hidden>
        <div class="ld-section-head"><h2 class="ld-section-title">Hôm nay bạn đã ăn</h2><a href="/ho-so#nhat-ky">Nhật ký</a></div>
        <div class="ld-group" id="today-logs"></div>
        <p class="ld-note" id="today-summary" style="margin-top:10px"></p>
    </section>

    {{-- ===== Khám phá món (UI-006) ===== --}}
    <section id="kham-pha" style="scroll-margin-top:80px">
        <div class="ld-section-head"><h2 class="ld-section-title">Khám phá món</h2></div>
        <div class="ld-search" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
            <input id="catalog-q" type="search" placeholder="Tìm món ăn" aria-label="Tìm món ăn">
        </div>
        <div class="ld-chips-scroll" id="catalog-filters" style="margin-top:10px" role="group" aria-label="Lọc theo nhãn"></div>
        <div class="ld-group" id="catalog"></div>
        <p class="ld-note" style="margin-top:10px">Số calo luôn là khoảng kèm độ tin cậy. Món chưa đủ dữ liệu không hiện số.</p>
    </section>

    {{-- ===== Hoa quả tháng này (UI-009, khối trang chủ) ===== --}}
    <section>
        <div class="ld-section-head">
            <h2 class="ld-section-title">Tháng này nên ăn quả gì</h2>
            <a href="/hoa-qua">Xem cả năm</a>
        </div>
        <p class="ld-subtitle" style="font-size:15px;margin-top:-4px">Quả đang vào vụ và giá hời nhất tháng này — vừa bổ vừa rẻ.</p>
        <div class="ld-fruit-scroll" id="fruit-month" style="margin-top:14px"></div>
    </section>
@endsection

@push('scripts')
<script>
const SLOTS = [['sang','Sáng'],['trua','Trưa'],['chieu','Chiều'],['toi','Tối'],['an-vat','Ăn vặt']];
const ALLERGENS = [
    ['Đậu phộng', ['lac-rang','lac-song']],
    ['Tôm/tép', ['tom-su','tom-kho','tep']],
    ['Cua', ['cua-bien','cua-dong']],
    ['Trứng', ['trung-ga','trung-vit','trung-cut','trung-vit-lon']],
    ['Sữa', ['sua-tuoi','sua-dac','sua-chua']],
    ['Đậu nành', ['dau-nanh-hat','sua-dau-nanh','dau-phu','dau-phu-chien']],
    ['Lúa mì', ['bot-mi','banh-mi','mi-trung-kho','mi-an-lien']],
    ['Mắm tôm', ['mam-tom']],
];
let slot = (() => { const h = new Date().getHours();
    if (h < 10) return 'sang'; if (h < 14) return 'trua'; if (h < 17) return 'chieu'; if (h < 21) return 'toi'; return 'an-vat'; })();
let allergenSlugs = new Set();
let shakeIndex = 0;
const recent = JSON.parse(localStorage.getItem('lacdoi_recent') || '[]');
const csrf = document.querySelector('meta[name=csrf-token]').content;
let currentUser = null;
const confLabel = { high: 'Tin cậy cao', medium: 'Tin cậy trung bình', low: 'Tin cậy thấp · số tham khảo' };
const confClass = { high: '', medium: 'medium', low: 'low' };
const slotName = { sang: 'Sáng', trua: 'Trưa', chieu: 'Chiều', toi: 'Tối', 'an-vat': 'Ăn vặt' };
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
function swOn(id) { return document.getElementById(id).getAttribute('aria-checked') === 'true'; }

/* Lời chào + ngày theo giờ thật */
(() => {
    const now = new Date();
    const h = now.getHours();
    const greet = h < 10 ? 'Chào buổi sáng' : h < 14 ? 'Chào buổi trưa' : h < 17 ? 'Chào buổi chiều' : h < 21 ? 'Chào buổi tối' : 'Chào bạn';
    document.getElementById('greeting').textContent = greet;
    document.getElementById('date-line').textContent =
        now.toLocaleDateString('vi-VN', { weekday: 'long', day: 'numeric', month: 'long' }).replace(/^./, c => c.toUpperCase());
})();

fetch('/api/me').then(r => r.json()).then(({ user }) => {
    currentUser = user;
    document.getElementById('auth-area').innerHTML = user
        ? `Xin chào <b>${esc(user.name)}</b> — <a href="/ho-so" style="color:var(--color-link)">hồ sơ và nhật ký</a> của bạn đã sẵn sàng.`
        : `<a href="/tai-khoan" style="color:var(--color-link)">Đăng nhập</a> để lưu bữa ăn và lắc theo khẩu vị của bạn — khách vẫn lắc thoải mái.`;
    if (user) loadToday();
});

const slotsEl = document.getElementById('slots');
function renderSlots() {
    slotsEl.innerHTML = SLOTS.map(([v, l]) =>
        `<button type="button" aria-pressed="${v === slot}" data-slot="${v}">${l}</button>`).join('');
    slotsEl.querySelectorAll('button').forEach(b => b.onclick = () => { slot = b.dataset.slot; renderSlots(); });
}
renderSlots();

const algEl = document.getElementById('allergens');
algEl.innerHTML = ALLERGENS.map(([label], i) => `<button type="button" class="ld-chip allergen" data-i="${i}">${label}</button>`).join('');
algEl.querySelectorAll('button').forEach(b => b.onclick = () => {
    const slugs = ALLERGENS[+b.dataset.i][1];
    const on = b.classList.toggle('active');
    slugs.forEach(s => on ? allergenSlugs.add(s) : allergenSlugs.delete(s));
});

['vegetarian', 'eat_out', 'home_cook'].forEach(id => {
    const el = document.getElementById(id);
    el.addEventListener('click', () => el.setAttribute('aria-checked', el.getAttribute('aria-checked') === 'true' ? 'false' : 'true'));
});

fetch('/api/regions').then(r => r.json()).then(rs => {
    document.getElementById('region').innerHTML = '<option value="">Mọi vùng miền</option>' +
        rs.map(r => `<option value="${r.id}">${esc(r.name)}</option>`).join('');
});

/* ===== Khám phá: lọc theo nhãn (TAXONOMY) — 1 lựa chọn mỗi facet ===== */
const catalogTags = { loai: null, diet: null };
fetch('/api/tags').then(r => r.json()).then(ts => {
    const usable = ts.filter(t => (t.facet === 'loai' || t.facet === 'diet') && t.dishes > 0);
    document.getElementById('catalog-filters').innerHTML = usable.map(t =>
        `<button type="button" class="ld-chip" data-facet="${t.facet}" data-slug="${t.slug}">${esc(t.name)} (${t.dishes})</button>`).join('');
    document.querySelectorAll('#catalog-filters .ld-chip').forEach(b => b.addEventListener('click', () => {
        const f = b.dataset.facet;
        catalogTags[f] = catalogTags[f] === b.dataset.slug ? null : b.dataset.slug;
        document.querySelectorAll(`#catalog-filters .ld-chip[data-facet="${f}"]`).forEach(x =>
            x.classList.toggle('active', x.dataset.slug === catalogTags[f]));
        loadCatalog();
    }));
});

const tileColors = ['blue', 'orange', 'green', 'purple', 'red'];
async function loadCatalog() {
    const q = document.getElementById('catalog-q').value;
    const params = new URLSearchParams({ per_page: 24 });
    if (q) params.set('q', q);
    Object.values(catalogTags).forEach(t => { if (t) params.append('tag', t); });
    const data = await (await fetch('/api/dishes?' + params)).json();
    document.getElementById('catalog').innerHTML = (data.data ?? []).map((d, i) => {
        const n = d.nutrition;
        const name = d.names?.[0]?.name ?? d.slug;
        const tags = (d.tags ?? []).filter(t => t.slug.startsWith('loai:') || t.slug === 'diet:chay');
        const unv = (n?.unverified_ingredients ?? []).length > 0;
        return `<a href="/mon/${d.slug}" class="ld-row sep-icon">
            <span class="ld-tile ${tileColors[i % tileColors.length]}">${esc(name.trim().charAt(0).toUpperCase())}</span>
            <span class="grow"><span style="font-weight:600">${esc(name)}</span>
                <span class="sub">${esc(d.region?.name ?? '')}${tags.map(t => ' · ' + esc(t.name)).join('')}${unv ? ' <span class="ld-badge unv">Chưa kiểm định</span>' : ''}</span></span>
            ${n ? `<span class="ld-price"><span class="so ld-tnum">${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)}</span><small>kcal · ${(confLabel[n.confidence] ?? '').replace('Tin cậy ', '')}</small></span>`
                 : `<span class="ld-price"><span class="so" style="color:var(--color-faint);font-weight:600">Chưa đủ dữ liệu</span><small>không hiện số</small></span>`}
            <svg class="ld-chev" width="8" height="14" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M1 1l6 6-6 6"/></svg>
        </a>`;
    }).join('') || '<p style="color:var(--color-faint);padding:18px 16px;margin:0">Không thấy món nào khớp bộ lọc.</p>';
}
loadCatalog();
let qTimer;
document.getElementById('catalog-q').addEventListener('input', () => {
    clearTimeout(qTimer); qTimer = setTimeout(loadCatalog, 350);
});

/* ===== Hôm nay đã ăn ===== */
async function loadToday() {
    try {
        const date = new Date().toISOString().slice(0, 10);
        const { logs, summary } = await (await fetch('/api/meal-logs?date=' + date)).json();
        if (!logs?.length) return;
        document.getElementById('today-section').hidden = false;
        document.getElementById('today-logs').innerHTML = logs.slice(0, 4).map((l, i) => {
            const n = l.dish?.nutrition;
            const name = l.dish?.names?.[0]?.name ?? '?';
            return `<div class="ld-row sep-icon">
                <span class="ld-tile ${tileColors[i % tileColors.length]}">${esc(name.trim().charAt(0).toUpperCase())}</span>
                <span class="grow"><span style="font-weight:600">${esc(name)}</span><span class="sub">${slotName[l.slot] ?? l.slot}</span></span>
                ${n ? `<span class="ld-price"><span class="so ld-tnum">${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)}</span><small>kcal</small></span>`
                     : `<span class="ld-price"><span class="so" style="color:var(--color-faint);font-weight:600">Chưa đủ dữ liệu</span></span>`}
            </div>`;
        }).join('');
        document.getElementById('today-summary').textContent =
            `Tổng hôm nay: ${summary.kcal_min}–${summary.kcal_max} kcal từ ${summary.dishes_counted} món có dữ liệu` +
            (summary.dishes_unknown ? ` · ${summary.dishes_unknown} món chưa đủ dữ liệu nên chưa tính vào tổng.` : '.');
    } catch { /* khối phụ — lỗi thì ẩn */ }
}

/* ===== Lắc món ===== */
document.getElementById('shake-btn').addEventListener('click', async () => {
    const icon = document.getElementById('shake-icon');
    if (icon) { icon.classList.remove('ld-shaking'); void icon.offsetWidth; icon.classList.add('ld-shaking'); }
    const btn = document.getElementById('shake-btn'); btn.disabled = true; btn.textContent = 'Đang lắc…';
    try {
        const resp = await fetch('/api/shake', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                slot,
                shake_index: shakeIndex++,
                recent_dish_ids: recent.slice(-10),
                vegetarian: swOn('vegetarian'),
                eat_out: swOn('eat_out'),
                home_cook: swOn('home_cook'),
                region_id: document.getElementById('region').value || null,
                target_kcal: document.getElementById('target_kcal').value || null,
                allergens: [...allergenSlugs],
            }),
        });
        const data = await resp.json();
        const box = document.getElementById('result');
        if (!data.dish) {
            box.innerHTML = `<div class="ld-warn" style="margin-top:16px"><span>Chưa tìm được món phù hợp. ${esc(data.suggestion ?? 'Hãy nới lỏng bớt bộ lọc (trừ dị ứng) rồi lắc lại.')}</span></div>`;
            return;
        }
        const d = data.dish;
        recent.push(d.id); localStorage.setItem('lacdoi_recent', JSON.stringify(recent.slice(-15)));
        const n = d.nutrition;
        const unv = (n?.unverified_ingredients ?? []);
        box.innerHTML = `<div style="padding-top:8px">
            <h2 style="font-size:29px;font-weight:800;letter-spacing:-.02em;line-height:1.2;margin:12px 0 2px">
                <a href="/mon/${d.slug}" style="color:inherit;text-decoration:none">${esc(d.name)}</a></h2>
            <p style="color:var(--color-sub);font-size:15px;margin:0">${esc(d.region ?? '')}${d.serving_desc ? ' · ' + esc(d.serving_desc) : ''}</p>
            ${n ? `<div style="display:flex;align-items:baseline;gap:9px;margin-top:14px;flex-wrap:wrap">
                    <span class="ld-kcal-so ld-tnum">${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)}</span>
                    <span style="color:var(--color-sub);font-size:14.5px">kcal mỗi khẩu phần</span>
                    <span class="ld-capsule ${confClass[n.confidence] ?? ''}">${confLabel[n.confidence] ?? esc(n.confidence)}</span></div>
                <div class="grid grid-cols-3 gap-2.5" style="margin-top:14px">
                    <div class="ld-macro"><div class="gt ld-tnum">${n.protein_g ?? '?'} g</div><div class="nhan">Đạm</div></div>
                    <div class="ld-macro"><div class="gt ld-tnum">${n.fat_g ?? '?'} g</div><div class="nhan">Béo</div></div>
                    <div class="ld-macro"><div class="gt ld-tnum">${n.carb_g ?? '?'} g</div><div class="nhan">Bột đường</div></div>
                </div>
                ${unv.length ? `<div class="ld-warn" style="margin-top:14px"><span>Số liệu tham khảo — chưa kiểm định (nguồn thứ cấp): ${esc(unv.join(', '))}.</span></div>` : ''}`
            : '<p style="color:var(--color-faint);margin-top:14px">Món này chưa đủ dữ liệu dinh dưỡng nên không hiện số.</p>'}
            ${data.relaxed_level > 0 ? '<p class="ld-note" style="margin-top:12px">Đã nới lỏng bộ lọc để tìm thêm lựa chọn — lọc dị ứng vẫn giữ nguyên.</p>' : ''}
            <div style="margin-top:16px">${currentUser
                ? `<button id="eat-btn" class="ld-btn ld-btn-primary" style="padding:11px 22px;font-size:15.5px;width:100%">Ăn món này</button>`
                : `<a href="/tai-khoan" style="color:var(--color-link);font-size:15px">Đăng nhập để ghi lại bữa ăn này</a>`}</div>
        </div>`;
        const eatBtn = document.getElementById('eat-btn');
        if (eatBtn) eatBtn.onclick = async () => {
            const resp = await fetch('/api/meal-logs', { method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ dish_id: d.id, slot, source: 'shake_accepted' }) });
            if (resp.ok) { eatBtn.textContent = 'Đã ghi vào nhật ký'; eatBtn.disabled = true; ldToast('Đã ghi ' + d.name + ' vào nhật ký'); loadToday(); }
            else { eatBtn.textContent = 'Ghi thất bại — thử lại'; }
        };
    } finally {
        btn.disabled = false; btn.textContent = 'Lắc món khác';
        if (icon) setTimeout(() => icon.classList.remove('ld-shaking'), 600);
    }
});

/* ===== Khối hoa quả tháng này ===== */
(async () => {
    const m = new Date().getMonth() + 1;
    const box = document.getElementById('fruit-month');
    if (!box) return;
    try {
        let res = await fetch(`/api/fruits?month=${m}&value_month=${m}&per_page=60`, { headers: { 'Accept': 'application/json' } });
        let data = await res.json();
        let list = data.data ?? [];
        if (list.length < 4) {
            res = await fetch(`/api/fruits?month=${m}&per_page=60`, { headers: { 'Accept': 'application/json' } });
            data = await res.json();
            const extra = (data.data ?? []).filter(f => (f.main_season_months ?? []).includes(m) && !list.some(x => x.slug === f.slug));
            list = [...list, ...extra];
        }
        box.innerHTML = list.slice(0, 8).map((f, i) => `
            <a href="/hoa-qua/${f.slug}" class="ld-fruit-card">
                <span class="ld-tile ${tileColors[i % tileColors.length]}" style="width:44px;height:44px;border-radius:12px;font-size:19px;margin-bottom:11px">${esc(f.name_vi.trim().charAt(0).toUpperCase())}</span>
                <span style="display:block;font-size:16.5px;font-weight:700">${esc(f.name_vi)}</span>
                <span style="display:block;margin-top:6px">
                    ${(f.main_season_months ?? []).includes(m) ? '<span class="ld-badge vu">Đang vụ chính</span>' : ''}
                    ${(f.best_value_months ?? []).includes(m) ? '<span class="ld-badge hoi">Giá hời</span>' : ''}
                </span>
                ${f.kcal_per_100g != null ? `<span class="ld-note" style="display:block;margin-top:6px">${Math.round(f.kcal_per_100g)} kcal/100g</span>` : ''}
            </a>`).join('') || '<p class="ld-note">Chưa có dữ liệu quả cho tháng này.</p>';
    } catch { /* khối phụ — lỗi thì bỏ qua, không ảnh hưởng phần Lắc */ }
})();
</script>
@endpush
