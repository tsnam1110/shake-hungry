<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hồ sơ của tôi — Lắc Đói</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-orange-50 min-h-screen text-neutral-800">
<main class="mx-auto max-w-2xl px-4 pt-8 pb-16">
    <header class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🎲 Hồ sơ của tôi</h1>
        <div class="text-sm">
            <a href="/" class="text-orange-700 underline mr-3">Lắc món</a>
            <button id="logout" class="text-neutral-500 underline">Đăng xuất</button>
        </div>
    </header>
    <p id="whoami" class="text-neutral-500 text-sm mt-1"></p>

    <section class="bg-white rounded-2xl shadow p-5 mt-4">
        <h2 class="font-semibold mb-3">Thể trạng & nhu cầu năng lượng</h2>
        <form id="profile-form" class="grid grid-cols-2 gap-3 text-sm">
            <label>Chiều cao (cm)<input name="height_cm" type="number" step="0.1" class="mt-1 w-full border rounded-xl px-3 py-2"></label>
            <label>Cân nặng (kg)<input name="weight_kg" type="number" step="0.1" class="mt-1 w-full border rounded-xl px-3 py-2"></label>
            <label>Năm sinh<input name="birth_year" type="number" class="mt-1 w-full border rounded-xl px-3 py-2"></label>
            <label>Giới tính
                <select name="sex" class="mt-1 w-full border rounded-xl px-3 py-2">
                    <option value="unspecified">Chưa rõ</option><option value="male">Nam</option><option value="female">Nữ</option>
                </select></label>
            <label>Mức vận động
                <select name="activity_level" class="mt-1 w-full border rounded-xl px-3 py-2">
                    <option value="nhe">Ít / nhẹ (PAL 1,40–1,69)</option>
                    <option value="vua">Vừa (PAL 1,70–1,99)</option>
                    <option value="nang">Mạnh (PAL 2,00–2,40)</option>
                </select></label>
            <label>Chế độ ăn
                <select name="diet_type" class="mt-1 w-full border rounded-xl px-3 py-2">
                    <option value="">Ăn thường</option><option value="chay">Ăn chay</option>
                    <option value="chay-truong">Chay trường (thuần chay)</option><option value="low-carb">Low-carb</option>
                </select></label>
            <label class="col-span-2">Không ăn được / dị ứng (chọn để lọc cứng khi lắc)
                <select id="excluded" multiple size="4" class="mt-1 w-full border rounded-xl px-3 py-2"></select>
            </label>
            <button class="col-span-2 bg-orange-600 text-white font-bold rounded-xl py-3">Lưu hồ sơ</button>
        </form>
        <div id="tdee" class="mt-4"></div>
    </section>

    <section class="bg-white rounded-2xl shadow p-5 mt-4">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold">Nhật ký bữa ăn</h2>
            <input id="log-date" type="date" class="border rounded-xl px-2 py-1.5 text-sm">
        </div>
        <div id="summary" class="mt-2 text-sm"></div>
        <ul id="logs" class="mt-3 space-y-2 text-sm"></ul>
        <div class="flex gap-2 mt-4">
            <input id="manual-q" placeholder="Thêm món đã ăn: gõ tên món…" class="flex-1 border rounded-xl px-3 py-2 text-sm">
            <select id="manual-slot" class="border rounded-xl px-2 py-2 text-sm">
                <option value="sang">Sáng</option><option value="trua">Trưa</option><option value="chieu">Chiều</option><option value="toi">Tối</option><option value="an-vat">Ăn vặt</option>
            </select>
        </div>
        <div id="manual-results" class="mt-2 space-y-1"></div>
    </section>

    <section class="bg-white rounded-2xl shadow p-5 mt-4">
        <h2 class="font-semibold mb-2">Đóng góp cho cộng đồng</h2>
        <p class="text-sm text-neutral-500 mb-3">Đề xuất món mới, báo sửa món, hoặc giới thiệu quán ăn — mọi đề xuất đều qua duyệt mới hiển thị.</p>
        <form id="contrib-form" class="space-y-3 text-sm">
            <select name="type" class="w-full border rounded-xl px-3 py-2">
                <option value="new_dish">Đề xuất món mới</option>
                <option value="fix_dish">Báo sửa thông tin món</option>
                <option value="new_place">Giới thiệu quán ăn</option>
            </select>
            <input name="title" required placeholder="Tên món / quán" class="w-full border rounded-xl px-3 py-2">
            <textarea name="detail" rows="3" placeholder="Chi tiết: nguyên liệu, cách làm, địa chỉ quán…" class="w-full border rounded-xl px-3 py-2"></textarea>
            <button class="bg-orange-600 text-white font-bold rounded-xl px-4 py-2.5">Gửi đề xuất</button>
            <span id="contrib-msg" class="text-sm"></span>
        </form>
    </section>
</main>
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
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
    const { profile, tdee } = await (await fetch('/api/profile')).json();
    if (profile) {
        const f = document.getElementById('profile-form');
        ['height_cm','weight_kg','birth_year','sex','activity_level','diet_type'].forEach(k => { if (profile[k] != null && f.elements[k]) f.elements[k].value = profile[k]; });
        const ex = new Set(profile.excluded_ingredients || []);
        [...excludedEl.options].forEach(o => { o.selected = ALLERGEN_PRESETS[+o.value][1].some(s => ex.has(s)); });
    }
    renderTdee(tdee);
    document.getElementById('log-date').value = new Date().toISOString().slice(0, 10);
    loadLogs();
}
function renderTdee(tdee) {
    document.getElementById('tdee').innerHTML = tdee
        ? `<div class="rounded-xl bg-orange-50 border border-orange-200 p-4">
             <div class="text-sm text-neutral-500">Nhu cầu năng lượng ước tính</div>
             <div class="text-2xl font-bold">${tdee.tdee_min.toLocaleString('vi-VN')} – ${tdee.tdee_max.toLocaleString('vi-VN')} kcal/ngày</div>
             <div class="text-xs text-neutral-500 mt-1">BMR ≈ ${tdee.bmr} kcal · ${tdee.formula}. ${tdee.note}</div>
           </div>`
        : '<p class="text-sm text-neutral-400">Điền cân nặng và năm sinh để xem nhu cầu năng lượng.</p>';
}
document.getElementById('profile-form').onsubmit = async (e) => {
    e.preventDefault();
    const raw = Object.fromEntries(new FormData(e.target).entries());
    const body = {};
    ['height_cm','weight_kg','birth_year'].forEach(k => body[k] = raw[k] === '' ? null : +raw[k]);
    ['sex','activity_level','diet_type'].forEach(k => body[k] = raw[k] === '' ? null : raw[k]);
    body.excluded_ingredients = [...excludedEl.selectedOptions].flatMap(o => ALLERGEN_PRESETS[+o.value][1]);
    const resp = await fetch('/api/profile', { method: 'PUT', headers, body: JSON.stringify(body) });
    const data = await resp.json();
    if (resp.ok) renderTdee(data.tdee); else alert(data.message || 'Lưu thất bại');
};
const slotName = { sang: 'Sáng', trua: 'Trưa', chieu: 'Chiều', toi: 'Tối', 'an-vat': 'Ăn vặt' };
async function loadLogs() {
    const date = document.getElementById('log-date').value;
    const { logs, summary } = await (await fetch('/api/meal-logs?date=' + date)).json();
    document.getElementById('summary').innerHTML =
        `Tổng hôm nay: <b>${summary.kcal_min} – ${summary.kcal_max} kcal</b> từ ${summary.dishes_counted} món có dữ liệu` +
        (summary.dishes_unknown ? ` · <span class="text-amber-600">${summary.dishes_unknown} món chưa đủ dữ liệu nên chưa tính vào tổng</span>` : '');
    document.getElementById('logs').innerHTML = logs.map(l => {
        const n = l.dish?.nutrition;
        return `<li class="flex items-center justify-between border rounded-xl px-3 py-2">
            <span>${slotName[l.slot] ?? l.slot} · <b>${l.dish?.names?.[0]?.name ?? '?'}</b>
            <span class="text-neutral-400">${n ? `${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)} kcal` : 'chưa đủ dữ liệu'}</span></span>
            <button data-del="${l.id}" class="text-red-500 text-xs underline">Xoá</button></li>`;
    }).join('') || '<li class="text-neutral-400">Chưa ghi bữa nào.</li>';
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
        const q = e.target.value.trim(); if (!q) return;
        const data = await (await fetch('/api/dishes?q=' + encodeURIComponent(q))).json();
        document.getElementById('manual-results').innerHTML = data.data.slice(0, 6).map(d =>
            `<button data-add="${d.id}" class="block w-full text-left border rounded-xl px-3 py-2 text-sm bg-orange-50 hover:bg-orange-100">+ ${d.names?.[0]?.name ?? d.slug}</button>`).join('');
        document.querySelectorAll('[data-add]').forEach(b => b.onclick = async () => {
            await fetch('/api/meal-logs', { method: 'POST', headers, body: JSON.stringify({
                dish_id: +b.dataset.add, slot: document.getElementById('manual-slot').value, source: 'manual',
                eaten_at: document.getElementById('log-date').value + 'T12:00:00',
            })});
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
</body>
</html>
