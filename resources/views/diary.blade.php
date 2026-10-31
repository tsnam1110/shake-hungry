@extends('layouts.app')

@section('title', 'Nhật ký bữa ăn — Lắc Đói')

@section('content')
    <h1 class="ld-title" style="margin-top:14px">Nhật ký bữa ăn</h1>
    <p class="ld-subtitle">Trang nhật ký đầy đủ: xem từng ngày, đối chiếu với nhu cầu năng lượng và nhìn lại cả tuần. Mục nhật ký trong hồ sơ chỉ là bản dùng nhanh.</p>

    <div style="display:flex;align-items:center;gap:9px;margin-top:18px;flex-wrap:wrap">
        <button class="ld-btn ld-btn-gray" id="prev-day" style="padding:10px 16px;font-size:15px" type="button">‹ Hôm trước</button>
        <input id="log-date" type="date" class="ld-input" style="width:auto;padding:10px 12px">
        <button class="ld-btn ld-btn-gray" id="next-day" style="padding:10px 16px;font-size:15px" type="button">Hôm sau ›</button>
        <button class="ld-btn ld-btn-gray" id="today-btn" style="padding:10px 16px;font-size:15px" type="button">Hôm nay</button>
        <span id="day-label" style="font-weight:700;font-size:16.5px;margin-left:4px"></span>
    </div>

    <div class="ld-card" style="margin-top:16px">
        <p style="margin:0;color:var(--color-sub);font-size:14.5px;font-weight:600">Năng lượng ngày này</p>
        <div style="display:flex;align-items:baseline;gap:9px;margin-top:4px;flex-wrap:wrap">
            <span class="ld-kcal-so ld-tnum" id="sum-range" style="color:var(--color-ink)">—</span>
            <span style="color:var(--color-sub);font-size:14.5px">kcal đã ăn</span>
        </div>
        <div style="height:10px;border-radius:6px;background:var(--color-fill);margin-top:14px;overflow:hidden">
            <div id="sum-bar" style="height:100%;width:0%;background:var(--color-mint);border-radius:6px;transition:width .3s"></div>
        </div>
        <p class="ld-note" id="sum-note" style="margin-top:10px"></p>
    </div>

    <h2 class="ld-section-title">Các bữa trong ngày</h2>
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

    <h2 class="ld-section-title">Hoa quả đã ăn</h2>
    <div class="ld-group" id="fruit-logs"></div>
    <p class="ld-note" style="margin-top:10px">Ghi quả đã ăn ở trang chi tiết của từng quả (nút "Đã ăn quả này").</p>

    <h2 class="ld-section-title">Tuần này</h2>
    <div class="ld-card">
        <div id="week-bars" style="display:flex;align-items:flex-end;gap:10px;height:110px"></div>
        <div id="week-labels" style="display:flex;gap:10px;margin-top:8px"></div>
        <p class="ld-note" style="margin-top:10px">Chạm vào một cột để xem ngày đó. Cột xám nhạt là ngày ghi thiếu dữ liệu.</p>
    </div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
const slotName = { sang: 'Sáng', trua: 'Trưa', chieu: 'Chiều', toi: 'Tối', 'an-vat': 'Ăn vặt' };
const tileColors = ['blue', 'orange', 'green', 'purple', 'red'];
let myTdee = null;
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
function dstr(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }
function parseDay(s) { const [y, m, dd] = s.split('-').map(Number); return new Date(y, m - 1, dd); }
let day = dstr(new Date());
const dateInput = document.getElementById('log-date');
dateInput.value = day;

fetch('/api/me').then(r => r.json()).then(({ user }) => { if (!user) location.href = '/tai-khoan'; });
fetch('/api/profile').then(r => r.json()).then(({ tdee }) => { myTdee = tdee; loadDay(); }).catch(() => loadDay());

async function loadDay() {
    dateInput.value = day;
    const d = parseDay(day);
    document.getElementById('day-label').textContent =
        d.toLocaleDateString('vi-VN', { weekday: 'long', day: 'numeric', month: 'numeric', year: 'numeric' }).replace(/^./, c => c.toUpperCase());
    const { logs, summary } = await (await fetch('/api/meal-logs?date=' + day)).json();
    document.getElementById('sum-range').textContent = summary.dishes_counted ? `${summary.kcal_min}–${summary.kcal_max}` : '0';
    let note = `Từ ${summary.dishes_counted} món có dữ liệu`;
    if (summary.dishes_unknown) note += ` · ${summary.dishes_unknown} món chưa đủ dữ liệu nên chưa tính vào tổng`;
    let pct = 0;
    if (myTdee) {
        const midT = (myTdee.tdee_min + myTdee.tdee_max) / 2;
        const midE = (summary.kcal_min + summary.kcal_max) / 2;
        pct = Math.min(100, Math.round(midE / midT * 100));
        const leftMin = Math.max(0, myTdee.tdee_min - summary.kcal_max);
        const leftMax = Math.max(0, myTdee.tdee_max - summary.kcal_min);
        note += ` · Nhu cầu ngày của bạn khoảng ${myTdee.tdee_min.toLocaleString('vi-VN')}–${myTdee.tdee_max.toLocaleString('vi-VN')} kcal` +
            (summary.dishes_counted ? `, còn dư khoảng ${leftMin.toLocaleString('vi-VN')}–${leftMax.toLocaleString('vi-VN')} kcal.` : '.');
    }
    document.getElementById('sum-note').textContent = note + (summary.dishes_counted ? '' : ' — chưa ghi bữa nào trong ngày này.');
    document.getElementById('sum-bar').style.width = pct + '%';
    document.getElementById('logs').innerHTML = logs.map((l, i) => {
        const n = l.dish?.nutrition;
        const name = l.dish?.names?.[0]?.name ?? '?';
        return `<div class="ld-row sep-icon">
            <span class="ld-tile ${tileColors[i % tileColors.length]}">${esc(name.trim().charAt(0).toUpperCase())}</span>
            <span class="grow"><span style="font-weight:600">${esc(name)}</span><span class="sub">${slotName[l.slot] ?? l.slot}</span></span>
            ${n ? `<span class="ld-price"><span class="so ld-tnum">${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)}</span><small>kcal</small></span>`
                 : `<span class="ld-price"><span class="so" style="color:var(--color-faint);font-weight:600">Chưa đủ dữ liệu</span></span>`}
            <button data-del="${l.id}" style="border:0;background:none;color:var(--color-danger);font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit">Xoá</button>
        </div>`;
    }).join('') || '<p style="color:var(--color-faint);padding:16px;margin:0">Chưa ghi bữa nào.</p>';
    document.querySelectorAll('[data-del]').forEach(b => b.onclick = async () => {
        await fetch('/api/meal-logs/' + b.dataset.del, { method: 'DELETE', headers });
        loadDay(); loadWeek();
    });
    loadFruits();
    loadWeek();
}

async function loadFruits() {
    const { logs } = await (await fetch('/api/fruit-logs?date=' + day)).json();
    document.getElementById('fruit-logs').innerHTML = logs.map((l, i) => `
        <div class="ld-row sep-icon">
            <span class="ld-tile green">${esc((l.name ?? '?').trim().charAt(0).toUpperCase())}</span>
            <span class="grow"><span style="font-weight:600">${esc(l.name ?? '')}</span><span class="sub">Hoa quả</span></span>
            <button data-fdel="${l.id}" style="border:0;background:none;color:var(--color-danger);font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit">Xoá</button>
        </div>`).join('') || '<p style="color:var(--color-faint);padding:16px;margin:0">Chưa ghi quả nào trong ngày này.</p>';
    document.querySelectorAll('[data-fdel]').forEach(b => b.onclick = async () => {
        await fetch('/api/fruit-logs/' + b.dataset.fdel, { method: 'DELETE', headers });
        loadFruits();
    });
}

async function loadWeek() {
    const d = parseDay(day);
    const dow = (d.getDay() + 6) % 7; // thứ Hai = 0
    const monday = new Date(d); monday.setDate(d.getDate() - dow);
    const sunday = new Date(monday); sunday.setDate(monday.getDate() + 6);
    const { days } = await (await fetch(`/api/meal-logs?from=${dstr(monday)}&to=${dstr(sunday)}`)).json();
    const names = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
    let bars = '', labels = '';
    for (let i = 0; i < 7; i++) {
        const cur = new Date(monday); cur.setDate(monday.getDate() + i);
        const key = dstr(cur);
        const s = days[key] ?? { kcal_min: 0, kcal_max: 0, dishes_counted: 0, dishes_unknown: 0 };
        const mid = (s.kcal_min + s.kcal_max) / 2;
        const target = myTdee ? (myTdee.tdee_min + myTdee.tdee_max) / 2 : 2000;
        const h = s.dishes_counted ? Math.max(6, Math.min(100, Math.round(mid / target * 100))) : (s.dishes_unknown ? 10 : 3);
        const isSel = key === day;
        bars += `<button data-day="${key}" title="${key}" style="flex:1;border:0;border-radius:7px 7px 0 0;cursor:pointer;height:${h}%;background:${s.dishes_counted ? (isSel ? 'var(--color-accent)' : 'var(--color-mint)') : 'var(--color-fill)'}"></button>`;
        labels += `<span style="flex:1;text-align:center;font-size:12.5px;${isSel ? 'font-weight:800;color:var(--color-ink)' : 'color:var(--color-faint)'}">${names[i]}</span>`;
    }
    document.getElementById('week-bars').innerHTML = bars;
    document.getElementById('week-labels').innerHTML = labels;
    document.querySelectorAll('[data-day]').forEach(b => b.onclick = () => { day = b.dataset.day; loadDay(); });
}

function shift(n) { const d = parseDay(day); d.setDate(d.getDate() + n); day = dstr(d); loadDay(); }
document.getElementById('prev-day').onclick = () => shift(-1);
document.getElementById('next-day').onclick = () => shift(1);
document.getElementById('today-btn').onclick = () => { day = dstr(new Date()); loadDay(); };
dateInput.onchange = () => { if (dateInput.value) { day = dateInput.value; loadDay(); } };

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
                eaten_at: day + 'T12:00:00',
            })});
            ldToast('Đã thêm món vào nhật ký');
            loadDay();
        });
    }, 350);
});
</script>
@endpush
