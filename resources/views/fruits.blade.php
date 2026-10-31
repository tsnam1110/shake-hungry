<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hoa quả theo mùa — Lắc Đói</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-orange-50 min-h-screen text-neutral-800">
<main class="mx-auto max-w-3xl px-4 pt-8 pb-16">
    <a href="/" class="text-sm text-orange-700 underline">← Về trang Lắc món</a>
    <h1 class="text-3xl font-bold mt-3">🍎 Hoa quả theo mùa</h1>
    <p class="text-neutral-500 mt-1">Tháng nào nên ăn quả gì — cân bằng cả dinh dưỡng lẫn giá. Giá là khoảng tham khảo theo mùa vụ, không phải giá hôm nay.</p>

    <div class="bg-white rounded-2xl shadow p-4 mt-4 flex flex-wrap gap-2 items-center">
        <input id="q" placeholder="Tìm quả… (cả tên gọi khác, VD: khóm)" class="border rounded-xl px-3 py-2 flex-1 min-w-44">
        <select id="month" class="border rounded-xl px-2 py-2"></select>
        <select id="group" class="border rounded-xl px-2 py-2">
            <option value="">Mọi nhóm quả</option>
            <option value="co-mui">Có múi</option>
            <option value="mong">Quả mọng</option>
            <option value="hach">Quả hạch</option>
            <option value="nhiet-doi">Nhiệt đới</option>
            <option value="dua">Họ dưa</option>
            <option value="khac">Khác</option>
        </select>
        <select id="origin" class="border rounded-xl px-2 py-2">
            <option value="">Mọi nguồn gốc</option>
            <option value="trong-nuoc">Trong nước</option>
            <option value="nhap-khau">Nhập khẩu</option>
            <option value="ca-hai">Cả hai</option>
        </select>
        <label class="flex items-center gap-1 text-sm"><input type="checkbox" id="only-season" class="accent-orange-600" checked> Đang có hàng</label>
        <label class="flex items-center gap-1 text-sm"><input type="checkbox" id="only-value" class="accent-orange-600"> Hời nhất tháng này</label>
    </div>

    <div id="list" class="grid grid-cols-2 gap-3 mt-4"></div>
    <p id="empty" class="text-neutral-400 mt-6 hidden">Không có quả nào khớp bộ lọc.</p>
</main>
<script>
const now = new Date();
const monthSel = document.getElementById('month');
monthSel.innerHTML = '<option value="">Tất cả các tháng</option>' + Array.from({length: 12}, (_, i) =>
    `<option value="${i + 1}" ${i + 1 === now.getMonth() + 1 ? 'selected' : ''}>Tháng ${i + 1}</option>`).join('');
const groupName = { 'co-mui': 'Có múi', mong: 'Quả mọng', hach: 'Quả hạch', 'nhiet-doi': 'Nhiệt đới', dua: 'Họ dưa', khac: 'Khác' };

function esc(s) { return (s ?? '').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

async function load() {
    const params = new URLSearchParams();
    const q = document.getElementById('q').value.trim();
    const month = monthSel.value;
    if (q) params.set('q', q);
    if (month && document.getElementById('only-season').checked) params.set('month', month);
    if (month && document.getElementById('only-value').checked) params.set('value_month', month);
    const group = document.getElementById('group').value;
    if (group) params.set('group', group);
    const origin = document.getElementById('origin').value;
    if (origin) params.set('origin', origin);
    const res = await fetch('/api/fruits?' + params, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    const m = month ? parseInt(month, 10) : null;
    document.getElementById('list').innerHTML = (data.data ?? []).map(f => `
        <a href="/hoa-qua/${f.slug}" class="bg-white rounded-2xl shadow p-4 block hover:ring-2 hover:ring-orange-300 transition">
            <div class="font-semibold">${esc(f.name_vi)}</div>
            ${f.aliases?.length ? `<div class="text-xs text-neutral-400">${f.aliases.map(a => esc(a.name)).join(' · ')}</div>` : ''}
            <div class="text-xs text-neutral-500 mt-1">${groupName[f.classification_group] ?? ''} · ${f.kcal_per_100g != null ? Math.round(f.kcal_per_100g) + ' kcal/100g' : 'chưa có số calo'}</div>
            <div class="flex flex-wrap gap-1 mt-2">
                ${m && (f.main_season_months ?? []).includes(m) ? '<span class="text-xs bg-green-100 text-green-800 rounded-full px-2 py-0.5">Đang vụ chính</span>' : ''}
                ${m && (f.best_value_months ?? []).includes(m) ? '<span class="text-xs bg-amber-100 text-amber-800 rounded-full px-2 py-0.5">💰 Hời nhất tháng này</span>' : ''}
                ${m && (f.best_nutrition_months ?? []).includes(m) ? '<span class="text-xs bg-sky-100 text-sky-800 rounded-full px-2 py-0.5">Bổ nhất lúc này</span>' : ''}
                ${f.unverified ? '<span class="text-xs bg-neutral-100 text-neutral-500 rounded-full px-2 py-0.5">Số chưa kiểm định</span>' : ''}
            </div>
            <div class="flex gap-0.5 mt-2">${Array.from({length: 12}, (_, i) =>
                `<span class="h-1.5 flex-1 rounded ${(f.availability_months ?? []).includes(i + 1) ? ((f.main_season_months ?? []).includes(i + 1) ? 'bg-green-500' : 'bg-green-200') : 'bg-neutral-200'}"></span>`).join('')}</div>
        </a>`).join('');
    document.getElementById('empty').classList.toggle('hidden', (data.data ?? []).length > 0);
}
['q', 'group', 'origin'].forEach(id => document.getElementById(id).addEventListener('input', load));
['month', 'only-season', 'only-value'].forEach(id => document.getElementById(id).addEventListener('change', load));
load();
</script>
</body>
</html>
