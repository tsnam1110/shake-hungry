<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lắc Đói — Hôm nay ăn gì?</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .chip { border: 1px solid #d9d9d9; border-radius: 999px; padding: 6px 14px; cursor: pointer; background: #fff; font-size: 14px; }
        .chip.active { background: #e8590c; border-color: #e8590c; color: #fff; font-weight: 600; }
        .chip.allergen.active { background: #c92a2a; border-color: #c92a2a; }
        @keyframes shake-anim { 0%,100%{transform:rotate(0)} 20%{transform:rotate(-6deg)} 40%{transform:rotate(5deg)} 60%{transform:rotate(-4deg)} 80%{transform:rotate(3deg)} }
        .shaking { animation: shake-anim .5s ease-in-out; }
    </style>
</head>
<body class="bg-orange-50 min-h-screen text-neutral-800">
    <main class="mx-auto max-w-2xl px-4 pb-16">
        <header class="pt-8 pb-2 text-center">
            <div id="dice" class="text-6xl">🎲</div>
            <h1 class="text-3xl font-bold mt-2">Lắc Đói</h1>
            <p class="text-neutral-500">Không biết ăn gì? Lắc một cái là xong.</p>
            <p id="auth-area" class="text-sm mt-2 text-neutral-500"></p>
        </header>

        <section class="bg-white rounded-2xl shadow p-4 mt-4">
            <div class="flex flex-wrap gap-2 justify-center" id="slots"></div>
            <div class="flex flex-wrap items-center gap-3 mt-4 justify-center text-sm">
                <label class="flex items-center gap-1"><input type="checkbox" id="vegetarian" class="accent-orange-600"> Ăn chay</label>
                <select id="region" class="border rounded-lg px-2 py-1.5">
                    <option value="">Mọi vùng miền</option>
                </select>
                <input id="target_kcal" type="number" min="100" max="2000" placeholder="Kcal mục tiêu/bữa" class="border rounded-lg px-2 py-1.5 w-44">
            </div>
            <p class="text-center text-xs text-neutral-400 mt-3">Dị ứng với (chạm để chọn — bộ lọc này không bao giờ bị nới lỏng):</p>
            <div class="flex flex-wrap gap-2 justify-center mt-1" id="allergens"></div>
            <button id="shake-btn" class="mt-5 w-full bg-orange-600 hover:bg-orange-700 text-white text-xl font-bold rounded-2xl py-4 transition">
                🎲 LẮC MÓN
            </button>
            <div id="result" class="mt-4"></div>
        </section>

        <section class="mt-8">
            <h2 class="font-semibold text-lg mb-2">Khám phá món</h2>
            <input id="catalog-q" placeholder="Tìm món…" class="w-full border rounded-xl px-3 py-2 mb-3 bg-white">
            <div id="catalog" class="grid grid-cols-2 gap-3"></div>
        </section>

        <footer class="text-center text-xs text-neutral-400 mt-10">
            Số calo là khoảng ước tính từ dữ liệu USDA qua engine của Lắc Đói, kèm độ tin cậy — món chưa đủ dữ liệu sẽ không hiện số.
        </footer>
    </main>

<script>
const SLOTS = [['sang','🌅 Sáng'],['trua','☀️ Trưa'],['chieu','🌤️ Chiều'],['toi','🌙 Tối'],['an-vat','🍡 Ăn vặt']];
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
fetch('/api/me').then(r => r.json()).then(({ user }) => {
    currentUser = user;
    document.getElementById('auth-area').innerHTML = user
        ? `Xin chào <b>${user.name}</b> · <a class="text-orange-700 underline" href="/ho-so">Hồ sơ & nhật ký bữa ăn</a>`
        : `<a class="text-orange-700 underline" href="/tai-khoan">Đăng nhập</a> để lưu bữa ăn và lắc theo khẩu vị của bạn — khách vẫn lắc thoải mái.`;
});
const confLabel = { high: 'tin cậy cao', medium: 'tin cậy trung bình', low: 'tin cậy thấp — số tham khảo' };

const slotsEl = document.getElementById('slots');
function renderSlots() {
    slotsEl.innerHTML = SLOTS.map(([v, l]) =>
        `<button class="chip ${v === slot ? 'active' : ''}" data-slot="${v}">${l}</button>`).join('');
    slotsEl.querySelectorAll('button').forEach(b => b.onclick = () => { slot = b.dataset.slot; renderSlots(); });
}
renderSlots();

const algEl = document.getElementById('allergens');
algEl.innerHTML = ALLERGENS.map(([label], i) => `<button class="chip allergen" data-i="${i}">⚠️ ${label}</button>`).join('');
algEl.querySelectorAll('button').forEach(b => b.onclick = () => {
    const slugs = ALLERGENS[+b.dataset.i][1];
    const on = b.classList.toggle('active');
    slugs.forEach(s => on ? allergenSlugs.add(s) : allergenSlugs.delete(s));
});

fetch('/api/regions').then(r => r.json()).then(rs => {
    document.getElementById('region').innerHTML = '<option value="">Mọi vùng miền</option>' +
        rs.map(r => `<option value="${r.id}">${r.name}</option>`).join('');
});

async function loadCatalog(q = '') {
    const data = await (await fetch('/api/dishes?per_page=24' + (q ? '&q=' + encodeURIComponent(q) : ''))).json();
    document.getElementById('catalog').innerHTML = data.data.map(d => {
        const n = d.nutrition;
        const kcal = n ? `${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)} kcal` : 'Chưa đủ dữ liệu';
        return `<div class="bg-white rounded-xl shadow p-3">
            <div class="font-semibold">${d.names?.[0]?.name ?? d.slug}</div>
            <div class="text-sm text-neutral-500">${d.region?.name ?? ''}</div>
            <div class="text-sm ${n ? 'text-orange-700 font-medium' : 'text-neutral-400'}">${kcal}</div>
        </div>`;
    }).join('') || '<p class="text-neutral-400 col-span-2">Không thấy món nào.</p>';
}
loadCatalog();
let qTimer;
document.getElementById('catalog-q').addEventListener('input', e => {
    clearTimeout(qTimer); qTimer = setTimeout(() => loadCatalog(e.target.value), 350);
});

document.getElementById('shake-btn').addEventListener('click', async () => {
    const dice = document.getElementById('dice');
    dice.classList.remove('shaking'); void dice.offsetWidth; dice.classList.add('shaking');
    const btn = document.getElementById('shake-btn'); btn.disabled = true; btn.textContent = 'Đang lắc…';
    try {
        const resp = await fetch('/api/shake', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                slot,
                shake_index: shakeIndex++,
                recent_dish_ids: recent.slice(-10),
                vegetarian: document.getElementById('vegetarian').checked,
                region_id: document.getElementById('region').value || null,
                target_kcal: document.getElementById('target_kcal').value || null,
                allergens: [...allergenSlugs],
            }),
        });
        const data = await resp.json();
        const box = document.getElementById('result');
        if (!data.dish) {
            box.innerHTML = `<div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-center">${data.suggestion ?? 'Chưa tìm được món phù hợp.'}</div>`;
            return;
        }
        const d = data.dish;
        recent.push(d.id); localStorage.setItem('lacdoi_recent', JSON.stringify(recent.slice(-15)));
        const n = d.nutrition;
        box.innerHTML = `<div class="rounded-2xl border-2 border-orange-200 bg-orange-50 p-5 text-center">
            <div class="text-sm text-neutral-500">${d.region ?? ''} ${d.serving_desc ? '· ' + d.serving_desc : ''}</div>
            <div class="text-2xl font-bold mt-1">${d.name}</div>
            ${n ? `<div class="mt-2 text-lg">🔥 ${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)} kcal <span class="text-sm text-neutral-500">(${confLabel[n.confidence] ?? n.confidence})</span></div>
            <div class="text-sm text-neutral-600">Đạm ${n.protein_g ?? '?'}g · Béo ${n.fat_g ?? '?'}g · Bột đường ${n.carb_g ?? '?'}g</div>`
            : '<div class="mt-2 text-neutral-400">Chưa đủ dữ liệu dinh dưỡng</div>'}
            ${data.relaxed_level > 0 ? '<div class="text-xs text-amber-600 mt-2">Đã nới lỏng bộ lọc để tìm thêm lựa chọn (lọc dị ứng vẫn giữ nguyên).</div>' : ''}
            <div class="mt-3">${currentUser
                ? `<button id="eat-btn" class="bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl px-4 py-2">✓ Ăn món này</button>`
                : `<a href="/tai-khoan" class="text-sm text-orange-700 underline">Đăng nhập để ghi lại bữa ăn này</a>`}</div>
        </div>`;
        const eatBtn = document.getElementById('eat-btn');
        if (eatBtn) eatBtn.onclick = async () => {
            const resp = await fetch('/api/meal-logs', { method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ dish_id: d.id, slot, source: 'shake_accepted' }) });
            eatBtn.textContent = resp.ok ? 'Đã ghi vào nhật ký ✓' : 'Ghi thất bại';
            eatBtn.disabled = true;
        };
    } finally {
        btn.disabled = false; btn.textContent = '🎲 LẮC MÓN KHÁC';
    }
});
</script>
</body>
</html>
