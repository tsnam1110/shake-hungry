@extends('layouts.app')

@section('title', 'Hồ sơ của tôi — Lắc Đói')

@section('content')
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px">
        <div>
            <h1 class="ld-title" style="margin-top:14px">Hồ sơ</h1>
            <p id="whoami" class="ld-subtitle" style="font-size:15px"></p>
        </div>
        <button id="logout" style="border:0;background:none;color:var(--color-danger);font-weight:600;font-size:15px;cursor:pointer;padding-top:22px;font-family:inherit">Đăng xuất</button>
    </div>

    {{-- Năng lượng (TDEE) — render bằng JS vào #tdee --}}
    <p class="ld-group-label">Năng lượng</p>
    <div id="tdee"></div>

    {{-- Nhật ký bữa ăn --}}
    <section id="nhat-ky" style="scroll-margin-top:80px">
        <div class="ld-section-head">
            <h2 class="ld-section-title">Nhật ký bữa ăn</h2>
            <input id="log-date" type="date" class="ld-input" style="width:auto;padding:8px 11px;font-size:14.5px">
        </div>
        <div id="summary" style="font-size:15px;margin-bottom:12px"></div>
        <div class="ld-group" id="logs"></div>
        <div style="display:flex;gap:9px;margin-top:14px">
            <div class="ld-search" style="flex:1">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
                <input id="manual-q" type="search" placeholder="Thêm món đã ăn: gõ tên món…" aria-label="Thêm món đã ăn">
            </div>
            <select id="manual-slot" class="ld-select" style="width:auto">
                <option value="sang">Sáng</option><option value="trua">Trưa</option><option value="chieu">Chiều</option><option value="toi">Tối</option><option value="an-vat">Ăn vặt</option>
            </select>
        </div>
        <div id="manual-results" class="ld-group" style="margin-top:10px"></div>
    </section>

    {{-- Thể trạng & ăn uống --}}
    <section>
        <h2 class="ld-section-title">Thể trạng và ăn uống</h2>
        <form id="profile-form" class="ld-card">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="ld-field-label" for="f-height">Chiều cao (cm)</label>
                    <input id="f-height" name="height_cm" type="number" step="0.1" class="ld-input"></div>
                <div><label class="ld-field-label" for="f-weight">Cân nặng (kg)</label>
                    <input id="f-weight" name="weight_kg" type="number" step="0.1" class="ld-input"></div>
                <div><label class="ld-field-label" for="f-birth">Năm sinh</label>
                    <input id="f-birth" name="birth_year" type="number" class="ld-input"></div>
                <div><label class="ld-field-label" for="f-sex">Giới tính</label>
                    <select id="f-sex" name="sex" class="ld-select">
                        <option value="unspecified">Chưa rõ</option><option value="male">Nam</option><option value="female">Nữ</option>
                    </select></div>
                <div><label class="ld-field-label" for="f-act">Mức vận động</label>
                    <select id="f-act" name="activity_level" class="ld-select">
                        <option value="nhe">Ít / nhẹ (PAL 1,40–1,69)</option>
                        <option value="vua">Vừa (PAL 1,70–1,99)</option>
                        <option value="nang">Mạnh (PAL 2,00–2,40)</option>
                    </select></div>
                <div><label class="ld-field-label" for="f-diet">Chế độ ăn</label>
                    <select id="f-diet" name="diet_type" class="ld-select">
                        <option value="">Ăn thường</option><option value="chay">Ăn chay</option>
                        <option value="chay-truong">Chay trường (thuần chay)</option><option value="low-carb">Low-carb</option>
                    </select></div>
                <div><label class="ld-field-label" for="f-region">Vùng miền yêu thích (điền sẵn khi lắc)</label>
                    <select id="f-region" name="preferred_region_id" class="ld-select">
                        <option value="">Mọi vùng miền</option>
                    </select></div>
                <div><label class="ld-field-label" for="f-target">Kcal mục tiêu mỗi bữa (điền sẵn khi lắc)</label>
                    <input id="f-target" name="target_kcal_per_meal" type="number" min="100" max="2000" placeholder="Ví dụ: 600" class="ld-input"></div>
                <div class="sm:col-span-2"><label class="ld-field-label" for="excluded">Không ăn được / dị ứng — chọn để lọc cứng khi lắc</label>
                    <select id="excluded" multiple size="4" class="ld-select"></select>
                    <p class="ld-note" style="margin-top:6px">Giữ Ctrl (hoặc Cmd) để chọn nhiều mục. Lọc dị ứng không bao giờ bị hệ thống tự nới lỏng.</p></div>
            </div>
            <button class="ld-btn ld-btn-primary" style="width:100%;margin-top:18px">Lưu hồ sơ</button>
        </form>
    </section>

    {{-- Đóng góp cộng đồng --}}
    <section id="dong-gop" style="scroll-margin-top:80px">
        <h2 class="ld-section-title">Đóng góp cho cộng đồng</h2>
        <div class="ld-card">
            <p style="color:var(--color-sub);font-size:15px;margin:0 0 14px">Đề xuất món mới, báo sửa món, hoặc giới thiệu quán ăn — mọi đề xuất đều qua duyệt mới hiển thị.</p>
            <form id="contrib-form" style="display:flex;flex-direction:column;gap:11px">
                <select name="type" class="ld-select">
                    <option value="new_dish">Đề xuất món mới</option>
                    <option value="fix_dish">Báo sửa thông tin món</option>
                    <option value="new_place">Giới thiệu quán ăn</option>
                </select>
                <input name="title" required placeholder="Tên món / quán" class="ld-input">
                <textarea name="detail" rows="3" placeholder="Chi tiết: nguyên liệu, cách làm, địa chỉ quán…" class="ld-input"></textarea>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                    <button class="ld-btn ld-btn-primary" style="padding:11px 22px;font-size:15.5px">Gửi đề xuất</button>
                    <span id="contrib-msg" style="font-size:14.5px"></span>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
const ALLERGEN_PRESETS = [
    ['Đậu phộng', ['lac-rang','lac-song']], ['Tôm/tép', ['tom-su','tom-kho','tep']],
    ['Cua', ['cua-bien','cua-dong']], ['Trứng', ['trung-ga','trung-vit','trung-cut','trung-vit-lon']],
    ['Sữa', ['sua-tuoi','sua-dac','sua-chua']], ['Đậu nành', ['dau-nanh-hat','sua-dau-nanh','dau-phu','dau-phu-chien']],
    ['Lúa mì', ['bot-mi','banh-mi','mi-trung-kho','mi-an-lien']], ['Mắm tôm', ['mam-tom']],
];
const excludedEl = document.getElementById('excluded');
excludedEl.innerHTML = ALLERGEN_PRESETS.map(([l], i) => `<option value="${i}">${l}</option>`).join('');

async function init() {
    const me = await (await fetch('/api/me')).json();
    if (!me.user) { location.href = '/tai-khoan'; return; }
    document.getElementById('whoami').textContent = `${me.user.name} · ${me.user.email}`;
    const regions = await (await fetch('/api/regions')).json().catch(() => []);
    const f0 = document.getElementById('profile-form');
    f0.elements['preferred_region_id'].innerHTML = '<option value="">Mọi vùng miền</option>' +
        regions.map(r => `<option value="${r.id}">${esc(r.name)}</option>`).join('');
    const { profile, tdee } = await (await fetch('/api/profile')).json();
    if (profile) {
        const f = document.getElementById('profile-form');
        ['height_cm','weight_kg','birth_year','sex','activity_level','diet_type','preferred_region_id','target_kcal_per_meal'].forEach(k => { if (profile[k] != null && f.elements[k]) f.elements[k].value = profile[k]; });
        const ex = new Set(profile.excluded_ingredients || []);
        [...excludedEl.options].forEach(o => { o.selected = ALLERGEN_PRESETS[+o.value][1].some(s => ex.has(s)); });
    }
    renderTdee(tdee);
    document.getElementById('log-date').value = new Date().toISOString().slice(0, 10);
    loadLogs();
}
function renderTdee(tdee) {
    document.getElementById('tdee').innerHTML = tdee
        ? `<div class="ld-card" style="padding:18px 20px">
             <div style="display:flex;align-items:baseline;gap:9px;flex-wrap:wrap">
               <span class="ld-tnum" style="font-size:27px;font-weight:800;letter-spacing:-.02em">${tdee.tdee_min.toLocaleString('vi-VN')} – ${tdee.tdee_max.toLocaleString('vi-VN')}</span>
               <span style="color:var(--color-sub)">kcal mỗi ngày, theo hồ sơ thể trạng của bạn</span>
             </div>
             <p class="ld-note" style="margin-top:8px">BMR ≈ ${tdee.bmr} kcal · ${esc(tdee.formula)}. ${esc(tdee.note)}</p>
           </div>`
        : '<div class="ld-card" style="padding:18px 20px"><p style="color:var(--color-faint);margin:0">Điền cân nặng và năm sinh ở mục Thể trạng để xem nhu cầu năng lượng.</p></div>';
}
document.getElementById('profile-form').onsubmit = async (e) => {
    e.preventDefault();
    const raw = Object.fromEntries(new FormData(e.target).entries());
    const body = {};
    ['height_cm','weight_kg','birth_year','preferred_region_id','target_kcal_per_meal'].forEach(k => body[k] = raw[k] === '' || raw[k] == null ? null : +raw[k]);
    ['sex','activity_level','diet_type'].forEach(k => body[k] = raw[k] === '' ? null : raw[k]);
    body.excluded_ingredients = [...excludedEl.selectedOptions].flatMap(o => ALLERGEN_PRESETS[+o.value][1]);
    const resp = await fetch('/api/profile', { method: 'PUT', headers, body: JSON.stringify(body) });
    const data = await resp.json();
    if (resp.ok) { renderTdee(data.tdee); ldToast('Đã lưu hồ sơ'); } else alert(data.message || 'Lưu thất bại');
};
const slotName = { sang: 'Sáng', trua: 'Trưa', chieu: 'Chiều', toi: 'Tối', 'an-vat': 'Ăn vặt' };
async function loadLogs() {
    const date = document.getElementById('log-date').value;
    const { logs, summary } = await (await fetch('/api/meal-logs?date=' + date)).json();
    document.getElementById('summary').innerHTML =
        `Tổng hôm nay: <b class="ld-tnum">${summary.kcal_min} – ${summary.kcal_max} kcal</b> từ ${summary.dishes_counted} món có dữ liệu` +
        (summary.dishes_unknown ? ` · <span style="color:var(--color-warn-ink)">${summary.dishes_unknown} món chưa đủ dữ liệu nên chưa tính vào tổng</span>` : '');
    document.getElementById('logs').innerHTML = logs.map(l => {
        const n = l.dish?.nutrition;
        const name = l.dish?.names?.[0]?.name ?? '?';
        return `<div class="ld-row sep-icon">
            <span class="ld-tile blue">${esc(name.trim().charAt(0).toUpperCase())}</span>
            <span class="grow"><span style="font-weight:600">${esc(name)}</span><span class="sub">${slotName[l.slot] ?? l.slot}</span></span>
            ${n ? `<span class="ld-price"><span class="so ld-tnum">${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)}</span><small>kcal</small></span>`
                 : `<span class="ld-price"><span class="so" style="color:var(--color-faint);font-weight:600">Chưa đủ dữ liệu</span></span>`}
            <button data-del="${l.id}" style="border:0;background:none;color:var(--color-danger);font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit">Xoá</button>
        </div>`;
    }).join('') || '<p style="color:var(--color-faint);padding:16px;margin:0">Chưa ghi bữa nào.</p>';
    document.querySelectorAll('[data-del]').forEach(b => b.onclick = async () => {
        await fetch('/api/meal-logs/' + b.dataset.del, { method: 'DELETE', headers });
        loadLogs();
    });
}
document.getElementById('log-date').onchange = loadLogs;
let mq;
document.getElementById('manual-q').addEventListener('input', (e) => {
    clearTimeout(mq);
    mq = setTimeout(async () => {
        const q = e.target.value.trim(); if (!q) { document.getElementById('manual-results').innerHTML = ''; return; }
        const data = await (await fetch('/api/dishes?q=' + encodeURIComponent(q))).json();
        document.getElementById('manual-results').innerHTML = data.data.slice(0, 6).map(d =>
            `<button data-add="${d.id}" class="ld-row" style="cursor:pointer"><span class="grow">+ ${esc(d.names?.[0]?.name ?? d.slug)}</span></button>`).join('');
        document.querySelectorAll('[data-add]').forEach(b => b.onclick = async () => {
            await fetch('/api/meal-logs', { method: 'POST', headers, body: JSON.stringify({
                dish_id: +b.dataset.add, slot: document.getElementById('manual-slot').value, source: 'manual',
                eaten_at: document.getElementById('log-date').value + 'T12:00:00',
            })});
            ldToast('Đã thêm món vào nhật ký');
            loadLogs();
        });
    }, 350);
});
document.getElementById('contrib-form').onsubmit = async (e) => {
    e.preventDefault();
    const body = Object.fromEntries(new FormData(e.target).entries());
    const resp = await fetch('/api/contributions', { method: 'POST', headers, body: JSON.stringify(body) });
    document.getElementById('contrib-msg').textContent = resp.ok ? 'Đã gửi — cảm ơn bạn! Đề xuất đang chờ duyệt.' : 'Gửi thất bại, thử lại sau.';
    if (resp.ok) e.target.reset();
};
document.getElementById('logout').onclick = async () => { await fetch('/api/logout', { method: 'POST', headers }); location.href = '/'; };
init();
</script>
@endpush
