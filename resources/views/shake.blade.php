@extends('layouts.app')

@section('title', 'Lắc Đói — Hôm nay ăn gì?')

@section('content')
    <p class="ld-date" id="date-line"></p>
    <h1 class="ld-title" id="greeting">Hôm nay ăn gì?</h1>
    <p class="ld-subtitle">Lắc một món hợp bữa, đúng vùng miền bạn thích và tránh những gì bạn không ăn được.
        <span id="auth-area"></span></p>

    {{-- RET-001: Món của ngày --}}
    @if (!empty($dishOfDay))
        <section class="ld-card" style="margin-top:16px;padding:13px 16px" aria-label="Món của ngày">
            <a class="ld-dotd" href="/mon/{{ $dishOfDay['slug'] }}">
                <img src="{{ $dishOfDay['image'] }}" alt="" loading="lazy" decoding="async">
                <span style="min-width:0">
                    <span class="ld-dotd-tag">Món của ngày</span>
                    <span style="display:block;font-size:18.5px;font-weight:750;letter-spacing:-.01em;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $dishOfDay['name'] }}</span>
                    <span style="display:block;color:var(--color-sub);font-size:14px;margin-top:1px">{{ $dishOfDay['region'] ?? '' }}{{ $dishOfDay['kcal_label'] ?? '' }}</span>
                </span>
                <span style="margin-left:auto;color:var(--color-faint);font-size:20px;flex:none">›</span>
            </a>
        </section>
    @endif

    {{-- ===== Khối Lắc (UI-011): giờ hiện tại + gợi ý bữa, lựa chọn ở trên nút Lắc ===== --}}
    <section class="ld-card" style="margin-top:20px" aria-label="Lắc món">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
            <div>
                <div class="ld-clock ld-tnum" id="clock">--:--</div>
                <p id="slot-hint" style="color:var(--color-sub);font-size:14.5px;margin:5px 0 0">Đang xem giờ để gợi ý bữa phù hợp…</p>
            </div>
            <span id="shake-icon" class="ld-tile green" style="width:52px;height:52px;border-radius:14px">
                <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 12h16a8 8 0 0 1-16 0Z"/><path d="M9 8c1-1.6 4.5-1.8 6-.5"/><path d="M15.5 5.5c1.2-1 2.7-1.3 4-1"/></svg>
            </span>
        </div>

        <div class="ld-seg" id="slots" role="group" aria-label="Chọn bữa" style="margin-top:16px"></div>

            <div style="padding:16px 2px 0">
                <p class="ld-field-label" style="margin:0">Chế độ lắc</p>
                <div class="ld-seg" id="shake-mode" role="group" aria-label="Chọn chế độ lắc" style="margin-top:8px">
                    <button type="button" data-mode="eat_out" aria-pressed="true">Ăn ngoài · 1 món</button>
                    <button type="button" data-mode="home" aria-pressed="false">Tự nấu · Mâm cơm</button>
                </div>
                <div id="tray-count-row" hidden style="margin-top:12px">
                    <p class="ld-field-label" style="margin:0">Số món trong mâm <span style="font-weight:400;color:var(--color-faint)">— không tính cơm; mâm chuẩn là 3 món: món chính, canh và món rau</span></p>
                    <div class="ld-seg" id="tray-count" role="group" aria-label="Chọn số món trong mâm" style="margin-top:8px">
                        <button type="button" data-count="1" aria-pressed="false">1 món</button>
                        <button type="button" data-count="2" aria-pressed="false">2 món</button>
                        <button type="button" data-count="3" aria-pressed="true">3 món</button>
                        <button type="button" data-count="4" aria-pressed="false">4 món</button>
                    </div>
                    <div class="ld-row" style="padding:12px 2px 0">
                        <span class="grow">Bữa cơm bình dân <span style="display:block;font-weight:400;color:var(--color-faint);font-size:13px;margin-top:1px">Chỉ món dễ nấu, nguyên liệu phổ biến dễ kiếm — như bữa cơm nhà hằng ngày</span></span>
                        <button class="ld-switch" type="button" role="switch" aria-checked="false" id="binh-dan" aria-label="Bữa cơm bình dân"></button>
                    </div>
                </div>
            </div>

        <div style="margin-top:16px;border-top:1px solid var(--color-line);padding-top:12px">
            <p class="ld-field-label" style="margin:0">Tuỳ chọn chung <span style="font-weight:400;color:var(--color-faint)">— áp dụng cho cả Ăn ngoài và Tự nấu</span></p>
            <div class="ld-row" style="padding-left:2px;padding-right:2px">
                <span class="grow">Ăn chay</span>
                <button class="ld-switch" type="button" role="switch" aria-checked="false" id="vegetarian" aria-label="Ăn chay"></button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" style="padding:12px 2px 4px">
                <div>
                    <label class="ld-field-label" for="region">Vùng miền</label>
                    <select id="region" class="ld-select"><option value="">Mọi vùng miền</option></select>
                </div>
                <div>
                    <label class="ld-field-label" for="target_kcal">Kcal mục tiêu mỗi bữa (tuỳ chọn)</label>
                    <input id="target_kcal" type="number" min="100" max="2000" placeholder="Ví dụ: 600" class="ld-input">
                    <p class="ld-note" id="target-hint" style="margin-top:6px"></p>
                </div>
            </div>
            <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin-top:14px">
                <p class="ld-field-label" style="margin:0">Không ăn được</p>
                <a href="/ho-so" style="font-size:13.5px;color:var(--color-link);text-decoration:none;white-space:nowrap">Cấu hình trong hồ sơ</a>
            </div>
            <div class="ld-chips" id="allergens" style="margin-top:8px"></div>
            <p class="ld-note" style="margin-top:8px">Món chứa các thành phần này không bao giờ xuất hiện khi lắc — lọc cứng, không bị nới lỏng kể cả khi các lọc khác được nới.</p>
            <p class="ld-note" id="pref-note" hidden style="margin-top:12px">Các lựa chọn Chế độ lắc, Số món trong mâm, Bữa cơm bình dân, Ăn chay, Vùng miền, Kcal mục tiêu và Không ăn được được lấy từ hồ sơ của bạn và tự lưu lại khi bạn thay đổi. <span id="pref-saved" style="color:var(--color-mint-dark);font-weight:600"></span></p>
        </div>

        <button id="shake-btn" class="ld-btn ld-btn-primary" style="width:100%;margin-top:18px;padding:16px 24px;font-size:18px">
            Lắc một món
        </button>

        <div class="ld-row" style="padding:12px 2px 0">
            <span class="grow">Lắc máy để lắc món <span style="display:block;font-weight:400;color:var(--color-faint);font-size:13px;margin-top:1px">Bật lên rồi lắc nhẹ điện thoại — trang tự lắc một món cho bạn, khỏi bấm nút</span></span>
            <button class="ld-switch" type="button" role="switch" aria-checked="false" id="shake-gesture" aria-label="Lắc máy để lắc món"></button>
        </div>

        <div id="result" aria-live="polite">
            <p style="text-align:center;color:var(--color-faint);margin:18px 0 2px">Chọn bữa và khẩu vị ở trên, rồi bấm <b>Lắc một món</b>.</p>
        </div>
    </section>

    {{-- Popup đổi món trong mâm (REC-005): mặc định Ngẫu nhiên --}}
    <div class="ld-sheet-overlay" id="swap-overlay">
        <div class="ld-sheet" role="dialog" aria-modal="true" aria-label="Đổi món trong mâm">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
                <div>
                    <h2 style="margin:0;font-size:20px;font-weight:800;letter-spacing:-.01em">Đổi món</h2>
                    <p id="swap-context" style="margin:4px 0 0;color:var(--color-sub);font-size:14px"></p>
                </div>
                <button type="button" id="swap-close" aria-label="Đóng" style="border:0;background:var(--color-fill);width:32px;height:32px;border-radius:50%;font-size:15px;cursor:pointer;color:var(--color-sub)">✕</button>
            </div>
            <div class="ld-seg" id="swap-mode" role="group" aria-label="Cách đổi món" style="margin-top:16px">
                <button type="button" class="on" data-mode="random" aria-pressed="true">Ngẫu nhiên</button>
                <button type="button" data-mode="pick" aria-pressed="false">Chọn món</button>
            </div>
            <div id="swap-random-pane" style="margin-top:16px">
                <p class="ld-note" style="margin:0">Hệ thống chọn ngẫu nhiên một món khác cùng vị trí trong mâm, vẫn giữ nguyên bộ lọc của bạn (dị ứng là lọc cứng).</p>
                <div id="swap-preview" style="margin-top:12px"></div>
                <button type="button" class="ld-btn ld-btn-primary" id="swap-roll" style="width:100%;margin-top:14px;padding:12px">Đổi ngẫu nhiên</button>
            </div>
            <div id="swap-pick-pane" hidden style="margin-top:16px">
                <div class="ld-search" role="search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
                    <input id="swap-q" type="search" placeholder="Tìm món thay thế…" aria-label="Tìm món thay thế">
                </div>
                <div class="ld-group" id="swap-list" style="margin-top:12px;max-height:46vh;overflow:auto"></div>
            </div>
        </div>
    </div>

    {{-- ===== Hôm nay đã ăn (chỉ khi đăng nhập và có ghi bữa) ===== --}}
    <section id="today-section" hidden>
        <div class="ld-section-head"><h2 class="ld-section-title">Hôm nay bạn đã ăn</h2><a href="/nhat-ky">Nhật ký</a></div>
        <div class="ld-group" id="today-logs"></div>
        <p class="ld-note" id="today-summary" style="margin-top:10px"></p>
    </section>

    {{-- ===== Khám phá món (UI-006 + thẻ đồng nhất UI-012) ===== --}}
    <section id="kham-pha" style="scroll-margin-top:80px">
        <div class="ld-section-head"><h2 class="ld-section-title">Thư viện</h2><a href="/thu-vien">Vào Thư viện</a></div>
        <div class="ld-search" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
            <input id="catalog-q" type="search" placeholder="Tìm món ăn" aria-label="Tìm món ăn">
        </div>
        <div class="ld-chips-scroll" id="catalog-filters" style="margin-top:10px" role="group" aria-label="Lọc theo nhãn"></div>
        <div class="ld-grid" id="catalog"></div>
        <p class="ld-note" style="margin-top:12px">Mục này là bản dùng nhanh ngay tại trang chủ — cần lọc sâu và xem toàn bộ kho món thì mở trang Thư viện. Số calo luôn là khoảng kèm độ tin cậy; món chưa đủ dữ liệu không hiện số.</p>
    </section>

    {{-- ===== Hoa quả tháng này (UI-009 + thẻ đồng nhất UI-012) ===== --}}
    <section>
        <div class="ld-section-head">
            <h2 class="ld-section-title">Tháng này nên ăn quả gì</h2>
            <a href="/hoa-qua">Xem cả năm</a>
        </div>
        <p class="ld-subtitle" style="font-size:15px;margin-top:-4px">Quả đang vào vụ và giá hời nhất tháng này — vừa bổ vừa rẻ.</p>
        <div class="ld-scroll-cards" id="fruit-month" style="margin-top:14px"></div>
    </section>
@endsection

@push('scripts')
<script>
const SLOTS = [['sang','Sáng'],['trua','Trưa'],['chieu','Chiều'],['toi','Tối'],['an-vat','Ăn vặt']];
{{-- FIX-A1 (AUDIT-ALLERGEN): danh sách loại trừ SINH TỪ TaggingRules (AllergenPreset) — không viết tay, đủ 11 nhóm. --}}
const ALLERGENS = @json(\App\Taxonomy\AllergenPreset::chips());
function suggestedSlot(h) { if (h < 10) return 'sang'; if (h < 14) return 'trua'; if (h < 17) return 'chieu'; if (h < 21) return 'toi'; return 'an-vat'; }
let slot = suggestedSlot(new Date().getHours());
let allergenSlugs = new Set();
let shakeIndex = 0;
let shakeMode = 'eat_out'; // eat_out: lắc 1 món quán | home: lắc mâm cơm tự nấu (REC-003)
let trayCount = 3; // số món trong mâm, không tính cơm — mặc định mâm chuẩn 3 món
const recent = JSON.parse(localStorage.getItem('lacdoi_recent') || '[]');
const csrf = document.querySelector('meta[name=csrf-token]').content;
let currentUser = null;
let myProfile = null;
let myTdee = null;
let targetMode = 'guest'; // guest | profile (đã cấu hình) | suggest (gợi ý TDEE) | session (sửa tay lần này)
const heartSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"><path d="M12 20.5C7 16.5 3.8 13.3 3.8 9.6 3.8 7 5.7 5 8.1 5c1.6 0 3.1.9 3.9 2.3C12.8 5.9 14.3 5 15.9 5c2.4 0 4.3 2 4.3 4.6 0 3.7-3.2 6.9-8.2 10.9Z"/></svg>';
const confLabel = { high: 'Tin cậy cao', medium: 'Tin cậy trung bình', low: 'Tin cậy thấp · số tham khảo' };
const confShort = { high: 'Tin cậy cao', medium: 'Tin cậy TB', low: 'Tin cậy thấp' };
const confClass = { high: '', medium: 'medium', low: 'low' };
const slotName = { sang: 'Sáng', trua: 'Trưa', chieu: 'Chiều', toi: 'Tối', 'an-vat': 'Ăn vặt' };
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
function swOn(id) { return document.getElementById(id).getAttribute('aria-checked') === 'true'; }
function setSw(id, on) { document.getElementById(id).setAttribute('aria-checked', on ? 'true' : 'false'); }

/* Lời chào + ngày + đồng hồ + gợi ý bữa theo giờ hiện tại */
(() => {
    const now = new Date();
    const h = now.getHours();
    const greet = h < 10 ? 'Chào buổi sáng' : h < 14 ? 'Chào buổi trưa' : h < 17 ? 'Chào buổi chiều' : h < 21 ? 'Chào buổi tối' : 'Chào bạn';
    document.getElementById('greeting').textContent = greet;
    document.getElementById('date-line').textContent =
        now.toLocaleDateString('vi-VN', { weekday: 'long', day: 'numeric', month: 'long' }).replace(/^./, c => c.toUpperCase());
    function tick() {
        const t = new Date();
        document.getElementById('clock').textContent =
            String(t.getHours()).padStart(2, '0') + ':' + String(t.getMinutes()).padStart(2, '0');
        document.getElementById('slot-hint').textContent =
            `Theo giờ hiện tại, gợi ý bữa ${slotName[suggestedSlot(t.getHours())]} — bạn vẫn đổi sang bữa khác được.`;
    }
    tick(); setInterval(tick, 15000);
})();

/* ===== Hồ sơ: điền sẵn tuỳ chọn lắc + lưu ngược khi đổi (UI-011) ===== */
function flashSaved() {
    const el = document.getElementById('pref-saved');
    el.textContent = 'Đã lưu vào hồ sơ ✓';
    clearTimeout(el._h); el._h = setTimeout(() => el.textContent = '', 2200);
}
async function savePref(partial) {
    if (!currentUser) return;
    try {
        const resp = await fetch('/api/profile', { method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(partial) });
        if (resp.ok) { const data = await resp.json(); myProfile = data.profile; flashSaved(); }
    } catch { /* lưu tuỳ chọn là phụ — lỗi thì bỏ qua */ }
}
const slotRatio = { sang: 0.25, trua: 0.35, chieu: 0.15, toi: 0.25, 'an-vat': 0.10 };
function applySuggestion() {
    if (!myTdee) return;
    const mid = (myTdee.tdee_min + myTdee.tdee_max) / 2;
    document.getElementById('target_kcal').value = Math.round(mid * (slotRatio[slot] ?? 0.3) / 10) * 10;
    document.getElementById('target-hint').textContent =
        `Gợi ý theo nhu cầu ngày ${myTdee.tdee_min.toLocaleString('vi-VN')}–${myTdee.tdee_max.toLocaleString('vi-VN')} kcal của bạn — chưa lưu vào hồ sơ, bạn sửa thoải mái.`;
}
function applyPrefs() {
    if (!myProfile) return;
    if (['chay', 'chay-truong'].includes(myProfile.diet_type)) setSw('vegetarian', true);
    if (myProfile.shake_mode) shakeMode = myProfile.shake_mode;
    if (myProfile.tray_dish_count) trayCount = myProfile.tray_dish_count;
    if (myProfile.tray_binh_dan) setSw('binh-dan', true);
    renderShakeMode();
    if (myProfile.preferred_region_id) {
        const sel = document.getElementById('region');
        if ([...sel.options].some(o => +o.value === myProfile.preferred_region_id)) sel.value = myProfile.preferred_region_id;
    }
    if (myProfile.target_kcal_per_meal) {
        targetMode = 'profile';
        document.getElementById('target_kcal').value = myProfile.target_kcal_per_meal;
        document.getElementById('target-hint').textContent = 'Đang dùng mức kcal bạn đã đặt trong hồ sơ.';
    } else if (myTdee) {
        targetMode = 'suggest';
        applySuggestion();
    }
    const ex = new Set(myProfile.excluded_ingredients || []);
    document.querySelectorAll('#allergens .ld-chip').forEach(b => {
        const slugs = ALLERGENS[+b.dataset.i][1];
        if (slugs.some(s => ex.has(s))) { b.classList.add('active'); slugs.forEach(s => allergenSlugs.add(s)); }
    });
}

fetch('/api/me').then(r => r.json()).then(async ({ user }) => {
    currentUser = user;
    document.getElementById('auth-area').innerHTML = user
        ? `Xin chào <b>${esc(user.name)}</b> — <a href="/ho-so" style="color:var(--color-link)">hồ sơ và nhật ký</a> của bạn đã sẵn sàng.`
        : `<a href="/tai-khoan" style="color:var(--color-link)">Đăng nhập</a> để lưu bữa ăn và lắc theo khẩu vị của bạn — khách vẫn lắc thoải mái.`;
    if (user) {
        document.getElementById('pref-note').hidden = false;
        try { const data = await (await fetch('/api/profile')).json(); myProfile = data.profile; myTdee = data.tdee; applyPrefs(); } catch { /* bỏ qua */ }
        loadToday();
    }
});

const slotsEl = document.getElementById('slots');
function renderSlots() {
    slotsEl.innerHTML = SLOTS.map(([v, l]) =>
        `<button type="button" aria-pressed="${v === slot}" data-slot="${v}">${l}</button>`).join('');
    slotsEl.querySelectorAll('button').forEach(b => b.onclick = () => { slot = b.dataset.slot; renderSlots(); if (targetMode === 'suggest') applySuggestion(); });
}
renderSlots();

const algEl = document.getElementById('allergens');
algEl.innerHTML = ALLERGENS.map(([label], i) => `<button type="button" class="ld-chip allergen" data-i="${i}">${label}</button>`).join('');
algEl.querySelectorAll('button').forEach(b => b.onclick = () => {
    const slugs = ALLERGENS[+b.dataset.i][1];
    const on = b.classList.toggle('active');
    slugs.forEach(s => on ? allergenSlugs.add(s) : allergenSlugs.delete(s));
    savePref({ excluded_ingredients: [...allergenSlugs] });
});

const vegEl = document.getElementById('vegetarian');
vegEl.addEventListener('click', () => {
    const turningOn = vegEl.getAttribute('aria-checked') !== 'true';
    vegEl.setAttribute('aria-checked', turningOn ? 'true' : 'false');
    if (myProfile?.diet_type !== 'low-carb') {
        savePref({ diet_type: swOn('vegetarian') ? 'chay' : null });
    }
});

/* REC-004: công tắc "Bữa cơm bình dân" — lưu ngược vào hồ sơ như các tuỳ chọn mâm. */
const bdEl = document.getElementById('binh-dan');
bdEl.addEventListener('click', () => {
    const turningOn = bdEl.getAttribute('aria-checked') !== 'true';
    bdEl.setAttribute('aria-checked', turningOn ? 'true' : 'false');
    savePref({ tray_binh_dan: turningOn });
});

/* ===== Chế độ lắc hai loại (REC-003): ăn ngoài 1 món / mâm cơm tự nấu 1–4 món ===== */
function renderShakeMode() {
    document.querySelectorAll('#shake-mode button').forEach(b =>
        b.setAttribute('aria-pressed', b.dataset.mode === shakeMode ? 'true' : 'false'));
    document.getElementById('tray-count-row').hidden = shakeMode !== 'home';
    document.querySelectorAll('#tray-count button').forEach(b =>
        b.setAttribute('aria-pressed', +b.dataset.count === trayCount ? 'true' : 'false'));
    document.getElementById('shake-btn').textContent = shakeMode === 'home' ? 'Lắc mâm cơm' : 'Lắc một món';
}
document.querySelectorAll('#shake-mode button').forEach(b => b.addEventListener('click', () => {
    shakeMode = b.dataset.mode; renderShakeMode(); savePref({ shake_mode: shakeMode });
}));
document.querySelectorAll('#tray-count button').forEach(b => b.addEventListener('click', () => {
    trayCount = +b.dataset.count; renderShakeMode(); savePref({ tray_dish_count: trayCount });
}));
renderShakeMode();

fetch('/api/regions').then(r => r.json()).then(rs => {
    document.getElementById('region').innerHTML = '<option value="">Mọi vùng miền</option>' +
        rs.map(r => `<option value="${r.id}">${esc(r.name)}</option>`).join('');
    applyPrefs();
});
document.getElementById('region').addEventListener('change', (e) => {
    savePref({ preferred_region_id: e.target.value ? +e.target.value : null });
});
document.getElementById('target_kcal').addEventListener('change', (e) => {
    const v = e.target.value;
    if (targetMode === 'profile') {
        if (v === '') { savePref({ target_kcal_per_meal: null }); return; }
        const n = +v;
        if (n >= 100 && n <= 2000) savePref({ target_kcal_per_meal: n });
    } else if (targetMode === 'suggest' || targetMode === 'session') {
        // Chưa cấu hình trong hồ sơ: giá trị sửa tay chỉ dùng cho lần lắc này.
        targetMode = 'session';
        document.getElementById('target-hint').textContent = 'Mức này chỉ dùng cho lần lắc này. Muốn cố định cho mọi lần, hãy đặt trong trang hồ sơ.';
    }
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

/* Thẻ món đồng nhất (UI-012): mọi dòng có chiều cao cố định, render cả khi rỗng */
async function loadCatalog() {
    const q = document.getElementById('catalog-q').value;
    const params = new URLSearchParams({ per_page: 24 });
    if (q) params.set('q', q);
    Object.values(catalogTags).forEach(t => { if (t) params.append('tag', t); });
    const data = await (await fetch('/api/dishes?' + params)).json();
    document.getElementById('catalog').innerHTML = (data.data ?? []).map(d => {
        const n = d.nutrition;
        const name = d.names?.[0]?.name ?? d.slug;
        const tags = (d.tags ?? []).filter(t => t.slug.startsWith('loai:') || t.slug === 'diet:chay');
        const unv = (n?.unverified_ingredients ?? []).length > 0;
        const chay = (d.tags ?? []).some(t => t.slug === 'diet:chay');
        return `<a href="/mon/${d.slug}" class="ld-dcard">
            <img class="ld-dcard-img" src="${d.image?.url ?? '/images/placeholder-dish.svg'}" alt="${esc(name)}" loading="lazy" decoding="async">
            <span class="ld-heart" data-fav-dish="${d.id}" role="button" aria-label="Yêu thích món này">${heartSvg}</span>
            <span class="ld-dcard-body">
                <span class="ld-dcard-title">${esc(name)}</span>
                <span class="ld-dcard-meta">${esc(d.region?.name ?? '')}${tags.filter(t => t.slug.startsWith('loai:')).map(t => ' · ' + esc(t.name)).join('')}</span>
                <span class="ld-dcard-badges">${chay ? '<span class="ld-badge chay">Chay</span>' : ''}${unv ? '<span class="ld-badge unv">Chưa kiểm định</span>' : ''}${d.image?.is_ai ? '<span class="ld-badge">Ảnh minh họa</span>' : ''}</span>
                <span class="ld-dcard-foot">${n
                    ? `<span class="ld-dcard-kcal ld-tnum">${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)} kcal</span><span class="ld-dcard-conf">${confShort[n.confidence] ?? ''}</span>`
                    : `<span class="ld-dcard-kcal none">Chưa đủ dữ liệu</span><span class="ld-dcard-conf">không hiện số</span>`}</span>
            </span>
        </a>`;
    }).join('') || '<p style="color:var(--color-faint);margin:0">Không thấy món nào khớp bộ lọc.</p>';
    if (window.ldRefreshHearts) window.ldRefreshHearts();
}
loadCatalog();
let qTimer;
document.getElementById('catalog-q').addEventListener('input', () => {
    clearTimeout(qTimer); qTimer = setTimeout(loadCatalog, 350);
});

/* ===== Hôm nay đã ăn ===== */
const tileColors = ['blue', 'orange', 'green', 'purple', 'red'];
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

/* ===== RET-001: quay số + ăn mừng + lắc máy để lắc món ===== */
const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
let gestureTriggered = false;
let reelPool = [];
fetch('/api/dishes?per_page=60').then(r => r.json()).then(data => {
    reelPool = (data.data ?? []).map(d => ({ name: d.names?.[0]?.name ?? d.slug, img: d.image?.url ?? '' })).filter(p => p.name);
}).catch(() => {});

function startReel(box) {
    if (REDUCED_MOTION || reelPool.length < 3) return Promise.resolve();
    box.innerHTML = `<div class="ld-reel rolling"><img alt=""><div style="min-width:0"><div class="ld-reel-name"></div><div class="ld-reel-sub">Đang lắc…</div></div></div>`;
    const img = box.querySelector('.ld-reel img');
    const nm = box.querySelector('.ld-reel-name');
    return new Promise(resolve => {
        let delay = 55;
        const t0 = performance.now();
        (function step() {
            const p = reelPool[Math.floor(Math.random() * reelPool.length)];
            nm.textContent = p.name;
            if (p.img) img.src = p.img;
            if (performance.now() - t0 < 880) { delay = Math.min(delay * 1.24, 210); setTimeout(step, delay); }
            else resolve();
        })();
    });
}

function celebrate(box) {
    if (gestureTriggered) { gestureTriggered = false; if (navigator.vibrate) navigator.vibrate(8); }
    const el = box.firstElementChild;
    if (!el || REDUCED_MOTION) return;
    el.classList.add('ld-pop');
    const colors = ['#0071e3', '#34c759', '#ff9f0a', '#ffd60a', '#bf5af2'];
    const cx = box.clientWidth / 2, cy = 30;
    for (let i = 0; i < 12; i++) {
        const s = document.createElement('span');
        s.className = 'ld-burst-p';
        s.style.background = colors[i % colors.length];
        s.style.left = cx + 'px'; s.style.top = cy + 'px';
        box.appendChild(s);
        const ang = (Math.PI * 2 * i) / 12 + Math.random() * 0.5;
        const dist = 46 + Math.random() * 52;
        s.animate([
            { transform: 'translate(-50%,-50%) scale(1)', opacity: 1 },
            { transform: `translate(${Math.cos(ang) * dist - 4}px, ${Math.sin(ang) * dist * 0.7 + 16}px) scale(.3)`, opacity: 0 },
        ], { duration: 620 + Math.random() * 220, easing: 'cubic-bezier(.17,.67,.35,1)' }).onfinish = () => s.remove();
    }
}

function staggerTray(box) {
    if (REDUCED_MOTION) return;
    const rows = box.querySelectorAll('.ld-group .ld-row');
    rows.forEach((r, i) => { r.classList.add('ld-rise'); r.style.transitionDelay = (i * 90) + 'ms'; });
    requestAnimationFrame(() => requestAnimationFrame(() => rows.forEach(r => r.classList.add('in'))));
}

let gestureOn = localStorage.getItem('lacdoi_shake_gesture') === '1';
if (gestureOn) setSw('shake-gesture', true);
document.getElementById('shake-gesture').addEventListener('click', async () => {
    if (swOn('shake-gesture')) {
        setSw('shake-gesture', false); gestureOn = false;
        localStorage.setItem('lacdoi_shake_gesture', '0');
        return;
    }
    let ok = true;
    if (typeof DeviceMotionEvent !== 'undefined' && typeof DeviceMotionEvent.requestPermission === 'function') {
        try { ok = (await DeviceMotionEvent.requestPermission()) === 'granted'; } catch { ok = false; }
    }
    if (!ok) { ldToast('Trình duyệt chưa cấp quyền cảm biến chuyển động'); return; }
    setSw('shake-gesture', true); gestureOn = true;
    localStorage.setItem('lacdoi_shake_gesture', '1');
    ldToast('Đã bật: lắc nhẹ máy để lắc món');
});
let lastAccMag = null, lastGestureAt = 0;
window.addEventListener('devicemotion', (e) => {
    if (!gestureOn || document.hidden) return;
    const a = e.accelerationIncludingGravity;
    if (!a) return;
    const mag = Math.abs(a.x ?? 0) + Math.abs(a.y ?? 0) + Math.abs(a.z ?? 0);
    if (lastAccMag !== null && Math.abs(mag - lastAccMag) > 27 && Date.now() - lastGestureAt > 1500) {
        const btn = document.getElementById('shake-btn');
        if (btn && !btn.disabled && !document.getElementById('swap-overlay').classList.contains('open')) {
            lastGestureAt = Date.now();
            gestureTriggered = true;
            btn.click();
        }
    }
    lastAccMag = mag;
});

/* ===== Lắc món ===== */
document.getElementById('shake-btn').addEventListener('click', async () => {
    const icon = document.getElementById('shake-icon');
    icon.classList.remove('ld-shaking'); void icon.offsetWidth; icon.classList.add('ld-shaking');
    const btn = document.getElementById('shake-btn'); btn.disabled = true; btn.textContent = 'Đang suy nghĩ…';
    lastShakePayload = {
        slot,
        recent_dish_ids: recent.slice(-10),
        vegetarian: swOn('vegetarian'),
        mode: shakeMode,
        dish_count: shakeMode === 'home' ? trayCount : 1,
        binh_dan: shakeMode === 'home' ? swOn('binh-dan') : false,
        region_id: document.getElementById('region').value || null,
        target_kcal: document.getElementById('target_kcal').value || null,
        allergens: [...allergenSlugs],
    };
    try {
        const box = document.getElementById('result');
        const reelPromise = startReel(box);
        const resp = await fetch('/api/shake', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ ...lastShakePayload, shake_index: shakeIndex++ }),
        });
        const data = await resp.json();
        await reelPromise;
        if (data.mode === 'home') { renderTrayResult(data, box); staggerTray(box); celebrate(box); return; }
        if (!data.dish) {
            box.innerHTML = `<div class="ld-warn" style="margin-top:16px"><span>Chưa tìm được món phù hợp. ${esc(data.suggestion ?? 'Hãy nới lỏng bớt bộ lọc (trừ dị ứng) rồi lắc lại.')}</span></div>`;
            return;
        }
        const d = data.dish;
        recent.push(d.id); localStorage.setItem('lacdoi_recent', JSON.stringify(recent.slice(-15)));
        const n = d.nutrition;
        const unv = (n?.unverified_ingredients ?? []);
        box.innerHTML = `<div style="padding-top:8px;border-top:1px solid var(--color-line);margin-top:18px">
            <div style="display:flex;gap:14px;align-items:center;margin-top:12px">
                ${d.image ? `<img class="ld-thumb" src="${esc(d.image.url)}" alt="" fetchpriority="high" decoding="async" style="width:48px;height:48px">` : ''}
                <div style="min-width:0">
                    <h2 style="font-size:29px;font-weight:800;letter-spacing:-.02em;line-height:1.2;margin:0">
                        <a href="/mon/${d.slug}" style="color:inherit;text-decoration:none">${esc(d.name)}</a></h2>
                    <p style="color:var(--color-sub);font-size:15px;margin:2px 0 0">${esc(d.region ?? '')}${d.serving_desc ? ' · ' + esc(d.serving_desc) : ''}</p>
                </div>
            </div>
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
        celebrate(box);
    } finally {
        btn.disabled = false; btn.textContent = shakeMode === 'home' ? 'Lắc mâm khác' : 'Lắc món khác';
        setTimeout(() => icon.classList.remove('ld-shaking'), 600);
    }
});

/* ===== Hiển thị kết quả mâm cơm (REC-003) + đổi từng món (REC-005) ===== */
let lastShakePayload = null;
let currentTray = null; // { dishes: [...], binh_dan: bool }

function trayKcalOf(dishes) {
    const withN = dishes.filter(d => d.nutrition);
    if (!withN.length) return null;
    return {
        kcal_min: withN.reduce((s, d) => s + d.nutrition.kcal_min, 0),
        kcal_max: withN.reduce((s, d) => s + d.nutrition.kcal_max, 0),
        dishes_counted: withN.length,
    };
}

function renderTrayResult(data, box, fromSwap = false) {
    const dishes = data.dishes ?? [];
    if (!dishes.length) {
        box.innerHTML = `<div class="ld-warn" style="margin-top:16px"><span>Chưa gom được mâm cơm phù hợp. ${esc(data.suggestion ?? 'Hãy nới lỏng bớt bộ lọc (trừ dị ứng) rồi lắc lại.')}</span></div>`;
        currentTray = null;
        return;
    }
    if (!fromSwap) {
        dishes.forEach(d => recent.push(d.id));
        localStorage.setItem('lacdoi_recent', JSON.stringify(recent.slice(-15)));
    }
    currentTray = { dishes: [...dishes], binh_dan: !!data.binh_dan };
    const tk = fromSwap ? trayKcalOf(currentTray.dishes) : data.tray_kcal;
    const unvNames = [...new Set(currentTray.dishes.flatMap(d => d.nutrition?.unverified_ingredients ?? []))];
    box.innerHTML = `<div style="padding-top:8px;border-top:1px solid var(--color-line);margin-top:18px">
        <h2 style="font-size:26px;font-weight:800;letter-spacing:-.02em;line-height:1.2;margin:12px 0 2px">${data.binh_dan ? 'Mâm cơm bình dân' : 'Mâm cơm gợi ý'} · ${currentTray.dishes.length} món</h2>
        <p style="color:var(--color-sub);font-size:15px;margin:0">${data.binh_dan ? 'Món dễ nấu, nguyên liệu phổ biến dễ kiếm. ' : ''}Chưa tính cơm — cơm nhà mình tự đong theo khẩu phần quen thuộc.</p>
        ${tk ? `<div style="display:flex;align-items:baseline;gap:9px;margin-top:12px;flex-wrap:wrap">
                <span class="ld-kcal-so ld-tnum">${Math.round(tk.kcal_min)}–${Math.round(tk.kcal_max)}</span>
                <span style="color:var(--color-sub);font-size:14.5px">kcal cả mâm (từ ${tk.dishes_counted} món có dữ liệu, chưa gồm cơm)</span></div>` : ''}
        <div class="ld-group" style="margin-top:14px">
            ${currentTray.dishes.map((d, i) => { const n = d.nutrition; return `<div class="ld-row ld-row-compact">
                <img class="ld-thumb" src="${d.image?.url ?? '/images/placeholder-dish.svg'}" alt="" loading="lazy" decoding="async">
                <a class="grow" href="/mon/${d.slug}" style="text-decoration:none;color:inherit;min-width:0">
                    <span style="font-weight:600">${esc(d.name)}</span>
                    <span class="sub">${esc(d.role_label ?? '')}${d.cooking_method_label ? ' · ' + esc(d.cooking_method_label) : ''}${d.region ? ' · ' + esc(d.region) : ''}${n ? ' · ' + Math.round(n.kcal_min) + '–' + Math.round(n.kcal_max) + ' kcal' : ' · Chưa đủ dữ liệu'}</span>
                </a>
                <button type="button" class="ld-btn ld-btn-gray" data-swap="${i}" style="padding:8px 16px;font-size:14px;flex:none">Đổi</button>
            </div>`; }).join('')}
        </div>
        ${unvNames.length ? `<div class="ld-warn" style="margin-top:14px"><span>Số liệu tham khảo — chưa kiểm định (nguồn thứ cấp): ${esc(unvNames.join(', '))}.</span></div>` : ''}
        ${!fromSwap && data.suggestion ? `<p class="ld-note" style="margin-top:12px">${esc(data.suggestion)}</p>` : ''}
        ${!fromSwap && data.relaxed_level > 0 ? '<p class="ld-note" style="margin-top:12px">Đã nới lỏng bộ lọc để gom đủ mâm — lọc dị ứng vẫn giữ nguyên.</p>' : ''}
        <div style="margin-top:16px">${currentUser
            ? `<button id="eat-tray-btn" class="ld-btn ld-btn-primary" style="padding:11px 22px;font-size:15.5px;width:100%">Ăn mâm này</button>`
            : `<a href="/tai-khoan" style="color:var(--color-link);font-size:15px">Đăng nhập để ghi lại mâm cơm này</a>`}</div>
    </div>`;
    box.querySelectorAll('[data-swap]').forEach(b => b.addEventListener('click', () => openSwap(+b.dataset.swap)));
    const eatBtn = document.getElementById('eat-tray-btn');
    if (eatBtn) eatBtn.onclick = async () => {
        eatBtn.disabled = true; eatBtn.textContent = 'Đang ghi…';
        let okCount = 0;
        for (const d of currentTray.dishes) {
            const r = await fetch('/api/meal-logs', { method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ dish_id: d.id, slot, source: 'shake_accepted' }) });
            if (r.ok) okCount++;
        }
        if (okCount === currentTray.dishes.length) { eatBtn.textContent = 'Đã ghi cả mâm vào nhật ký'; ldToast('Đã ghi mâm cơm vào nhật ký'); loadToday(); }
        else { eatBtn.textContent = `Đã ghi ${okCount}/${currentTray.dishes.length} món — bấm để ghi tiếp`; eatBtn.disabled = false; }
    };
}

/* ===== Đổi món trong mâm (REC-005) ===== */
let swapIdx = null;
let swapRollIndex = 1000;
let swapCandidates = [];

function swapContextBody(extra = {}) {
    return {
        slot: lastShakePayload.slot,
        region_id: lastShakePayload.region_id,
        vegetarian: lastShakePayload.vegetarian,
        binh_dan: lastShakePayload.binh_dan,
        target_kcal: lastShakePayload.target_kcal,
        allergens: lastShakePayload.allergens,
        recent_dish_ids: recent.slice(-10),
        ...extra,
    };
}

function openSwap(idx) {
    if (!currentTray || !lastShakePayload) return;
    swapIdx = idx;
    const d = currentTray.dishes[idx];
    document.getElementById('swap-context').textContent = `${d.role_label ?? 'Món'} hiện tại: ${d.name}`;
    setSwapMode('random'); // mặc định Ngẫu nhiên
    document.getElementById('swap-preview').innerHTML = '';
    document.getElementById('swap-roll').textContent = 'Đổi ngẫu nhiên';
    document.getElementById('swap-overlay').classList.add('open');
}
function closeSwap() { document.getElementById('swap-overlay').classList.remove('open'); }
document.getElementById('swap-close').addEventListener('click', closeSwap);
document.getElementById('swap-overlay').addEventListener('click', (e) => { if (e.target.id === 'swap-overlay') closeSwap(); });

function setSwapMode(mode) {
    document.querySelectorAll('#swap-mode button').forEach(b => {
        const on = b.dataset.mode === mode;
        b.setAttribute('aria-pressed', on ? 'true' : 'false');
        b.classList.toggle('active', on);
    });
    document.getElementById('swap-random-pane').hidden = mode !== 'random';
    document.getElementById('swap-pick-pane').hidden = mode !== 'pick';
    if (mode === 'pick') loadSwapCandidates();
}
document.querySelectorAll('#swap-mode button').forEach(b => b.addEventListener('click', () => setSwapMode(b.dataset.mode)));

function applySwap(newDish) {
    if (swapIdx === null || !currentTray) return;
    currentTray.dishes[swapIdx] = newDish;
    recent.push(newDish.id);
    localStorage.setItem('lacdoi_recent', JSON.stringify(recent.slice(-15)));
    closeSwap();
    renderTrayResult({ dishes: currentTray.dishes, binh_dan: currentTray.binh_dan }, document.getElementById('result'), true);
    if (window.ldToast) ldToast('Đã đổi thành ' + newDish.name);
}

document.getElementById('swap-roll').addEventListener('click', async () => {
    const btn = document.getElementById('swap-roll');
    btn.disabled = true; btn.textContent = 'Đang suy nghĩ…';
    try {
        const resp = await fetch('/api/shake/tray-swap', { method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(swapContextBody({
                role: currentTray.dishes[swapIdx].role,
                exclude_dish_ids: currentTray.dishes.map(d => d.id),
                shake_index: swapRollIndex++,
            })) });
        const data = await resp.json();
        const pv = document.getElementById('swap-preview');
        if (!data.dish) {
            pv.innerHTML = `<div class="ld-warn"><span>${esc(data.suggestion ?? 'Không còn món khác phù hợp cho vị trí này.')}</span></div>`;
            btn.textContent = 'Đổi ngẫu nhiên';
            return;
        }
        const d = data.dish; const n = d.nutrition;
        pv.innerHTML = `<div class="ld-group"><div class="ld-row ld-row-compact">
                <img class="ld-thumb" src="${d.image?.url ?? '/images/placeholder-dish.svg'}" alt="" loading="lazy" decoding="async">
                <span class="grow"><span style="font-weight:600">${esc(d.name)}</span>
                <span class="sub">${esc(d.role_label ?? '')}${n ? ' · ' + Math.round(n.kcal_min) + '–' + Math.round(n.kcal_max) + ' kcal' : ''}</span></span>
            </div></div>
            <button type="button" class="ld-btn ld-btn-primary" id="swap-apply" style="width:100%;margin-top:12px;padding:12px">Dùng món này</button>`;
        document.getElementById('swap-apply').addEventListener('click', () => applySwap(d));
        btn.textContent = 'Ngẫu nhiên lại';
    } finally { btn.disabled = false; }
});

async function loadSwapCandidates() {
    const list = document.getElementById('swap-list');
    list.innerHTML = '<p style="color:var(--color-faint);padding:14px;margin:0">Đang tải món thay thế…</p>';
    const params = new URLSearchParams();
    const body = swapContextBody({ role: currentTray.dishes[swapIdx].role, exclude_dish_ids: currentTray.dishes.map(d => d.id) });
    Object.entries(body).forEach(([k, v]) => {
        if (Array.isArray(v)) v.forEach(x => params.append(k + '[]', x));
        else if (v !== null && v !== undefined && v !== '') params.set(k, v);
    });
    const resp = await fetch('/api/shake/tray-candidates?' + params, { headers: { 'Accept': 'application/json' } });
    const data = await resp.json();
    swapCandidates = data.dishes ?? [];
    renderSwapCandidates('');
}
function renderSwapCandidates(q) {
    const list = document.getElementById('swap-list');
    const rows = swapCandidates.filter(d => d.name.toLowerCase().includes(q.toLowerCase()));
    list.innerHTML = rows.map(d => { const n = d.nutrition; return `<button type="button" class="ld-row ld-row-compact" data-cand="${d.id}" style="width:100%">
            <img class="ld-thumb" src="${d.image?.url ?? '/images/placeholder-dish.svg'}" alt="" loading="lazy" decoding="async">
            <span class="grow" style="text-align:left"><span style="font-weight:600">${esc(d.name)}</span>
            <span class="sub">${d.cooking_method_label ? esc(d.cooking_method_label) + ' · ' : ''}${n ? Math.round(n.kcal_min) + '–' + Math.round(n.kcal_max) + ' kcal' : 'Chưa đủ dữ liệu'}</span></span>
        </button>`; }).join('')
        || '<p style="color:var(--color-faint);padding:14px;margin:0">Không có món thay thế phù hợp.</p>';
    list.querySelectorAll('[data-cand]').forEach(b => b.addEventListener('click', () => {
        const d = swapCandidates.find(x => x.id === +b.dataset.cand);
        if (d) applySwap(d);
    }));
}
document.getElementById('swap-q').addEventListener('input', (e) => renderSwapCandidates(e.target.value.trim()));

/* ===== Khối hoa quả tháng này — thẻ quả đồng nhất (UI-012) ===== */
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
        box.innerHTML = list.slice(0, 8).map(f => `
            <a href="/hoa-qua/${f.slug}" class="ld-dcard">
                <img class="ld-dcard-img" src="${f.image?.url ?? '/images/placeholder-fruit.svg'}" alt="${esc(f.name_vi)}" loading="lazy" decoding="async">
                <span class="ld-heart" data-fav-fruit="${f.id}" role="button" aria-label="Yêu thích quả này">${heartSvg}</span>
                <span class="ld-dcard-body">
                    <span class="ld-dcard-title">${esc(f.name_vi)}</span>
                    <span class="ld-dcard-meta">${f.kcal_per_100g != null ? Math.round(f.kcal_per_100g) + ' kcal/100g' : 'Chưa có số calo'}</span>
                    <span class="ld-dcard-badges">${(f.main_season_months ?? []).includes(m) ? '<span class="ld-badge vu">Đang vụ chính</span>' : ''}${(f.best_value_months ?? []).includes(m) ? '<span class="ld-badge hoi">Giá hời</span>' : ''}${f.image?.is_ai ? '<span class="ld-badge">Ảnh minh họa</span>' : ''}</span>
                    <span class="ld-dcard-foot"><span class="ld-seasonbar" aria-hidden="true">${Array.from({length: 12}, (_, i) =>
                        `<i class="${(f.main_season_months ?? []).includes(i + 1) ? 'main' : (f.availability_months ?? []).includes(i + 1) ? 'on' : ''}"></i>`).join('')}</span></span>
                </span>
            </a>`).join('') || '<p class="ld-note">Chưa có dữ liệu quả cho tháng này.</p>';
        if (window.ldRefreshHearts) window.ldRefreshHearts();
    } catch { /* khối phụ — lỗi thì bỏ qua, không ảnh hưởng phần Lắc */ }
})();
</script>
@endpush
