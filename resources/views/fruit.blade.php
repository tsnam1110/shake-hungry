<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chi tiết quả — Lắc Đói</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-orange-50 min-h-screen text-neutral-800">
<main class="mx-auto max-w-2xl px-4 pt-8 pb-16">
    <a href="/hoa-qua" class="text-sm text-orange-700 underline">← Danh sách hoa quả</a>
    <div id="content" class="mt-4 text-neutral-400">Đang tải…</div>
</main>
<script>
const slug = location.pathname.split('/').pop();
const regionName = { bac: 'Miền Bắc', trung: 'Miền Trung', nam: 'Miền Nam' };
const groupName = { 'co-mui': 'Có múi', mong: 'Quả mọng', hach: 'Quả hạch', 'nhiet-doi': 'Nhiệt đới', dua: 'Họ dưa', khac: 'Khác' };
const originName = { 'trong-nuoc': 'Trồng trong nước', 'nhap-khau': 'Nhập khẩu', 'ca-hai': 'Cả trồng trong nước và nhập khẩu' };
function esc(s) { return (s ?? '').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
function fmt(v) { return v == null ? '—' : Math.round(v).toLocaleString('vi-VN'); }
function seasonRow(label, months, cls) {
    return `<div class="flex items-center gap-1 mt-1">
        <span class="w-32 text-xs text-neutral-500 shrink-0">${label}</span>
        ${Array.from({length: 12}, (_, i) => `<span class="flex-1 text-center text-[10px] rounded py-0.5 ${(months ?? []).includes(i + 1) ? cls : 'bg-neutral-100 text-neutral-300'}">${i + 1}</span>`).join('')}
    </div>`;
}

fetch('/api/fruits/' + encodeURIComponent(slug), { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(({ fruit: f, ingredient: ing, prices }) => {
        const n = ing.nutrition;
        document.title = f.name_vi + ' — Lắc Đói';
        const byRegion = {};
        (prices ?? []).forEach(p => { (byRegion[p.region_code] ??= {})[p.month] = p; });
        const monthsWithPrices = [...new Set((prices ?? []).map(p => p.month))].sort((a, b) => a - b);
        const sources = [...new Map((prices ?? []).map(p => [p.source_name + p.collected_at, p])).values()];
        document.getElementById('content').innerHTML = `
        <div class="bg-white rounded-2xl shadow p-5">
            <h1 class="text-3xl font-bold">${esc(f.name_vi)}</h1>
            ${ing.aliases?.length ? `<p class="text-neutral-400 text-sm">Tên gọi khác: ${ing.aliases.map(a => a.note ? `${esc(a.name)} (${esc(a.note)})` : esc(a.name)).join(', ')}</p>` : ''}
            <p class="text-neutral-500 mt-1">${groupName[f.classification_group] ?? ''}${f.family ? ' · ' + esc(f.family) : ''} · ${originName[f.origin] ?? ''}</p>
            ${(ing.tags ?? []).filter(t => t.slug.startsWith('allergen:')).length ? `<div class="flex flex-wrap gap-1.5 mt-2">${ing.tags.filter(t => t.slug.startsWith('allergen:')).map(t => `<span class="text-xs border border-red-300 text-red-700 bg-red-50 rounded-full px-2.5 py-0.5">Dị ứng: ${esc(t.name)}</span>`).join('')}</div>` : ''}

            <div class="mt-4 rounded-xl bg-orange-50 border border-orange-200 p-4">
                ${n.kcal_per_100g != null ? `<div class="text-xl font-bold">🔥 ${fmt(n.kcal_per_100g)} kcal/100g</div>
                <div class="text-sm text-neutral-600 mt-1">Đạm ${fmt(n.protein_g)}g · Béo ${fmt(n.fat_g)}g · Bột đường ${fmt(n.carb_g)}g · Chất xơ ${fmt(n.fiber_g)}g · Natri ${fmt(n.sodium_mg)}mg</div>
                <div class="text-xs text-neutral-400 mt-1">Nguồn số liệu: ${esc(n.fct_source ?? '—')}${n.scientific_name ? ' · ' + esc(n.scientific_name) : ''}</div>
                ${n.unverified ? '<div class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-2 py-1 mt-2">⚠️ Số dinh dưỡng từ nguồn thứ cấp — chưa kiểm định chéo, chỉ nên tham khảo tương đối.</div>' : ''}`
                : '<div class="text-neutral-500">Chưa có số dinh dưỡng đạt chuẩn cho quả này — hệ thống không đoán số.</div>'}
            </div>

            <h2 class="font-semibold mt-6 mb-1">📅 Mùa vụ trong năm</h2>
            ${seasonRow('Có hàng', f.availability_months, 'bg-green-200 text-green-900')}
            ${seasonRow('Vụ chính', f.main_season_months, 'bg-green-500 text-white')}
            ${seasonRow('Bổ nhất', f.best_nutrition.months, 'bg-sky-400 text-white')}
            ${seasonRow('Giá hời nhất', f.best_value.months, 'bg-amber-400 text-white')}
            ${f.best_nutrition.note ? `<p class="text-sm text-neutral-600 mt-2"><b>Bổ nhất:</b> ${esc(f.best_nutrition.note)}</p>` : ''}
            ${f.best_value.note ? `<p class="text-sm text-neutral-600 mt-1"><b>Giá hời:</b> ${esc(f.best_value.note)}</p>` : ''}

            ${(f.grown_in ?? []).length ? `<h2 class="font-semibold mt-6">📍 Vùng trồng tiêu biểu</h2>
            <ul class="text-sm text-neutral-700 mt-1">${f.grown_in.map(g => `<li>• <b>${esc(g.region)}</b>${g.note ? ' — ' + esc(g.note) : ''}</li>`).join('')}</ul>` : ''}

            ${(f.pairing_cautions ?? []).length ? `<h2 class="font-semibold mt-6">⚠️ Lưu ý khi kết hợp</h2>
            <ul class="text-sm mt-1 space-y-1">${f.pairing_cautions.map(c => `<li>• <b>${esc(c.with)}</b> — ${esc(c.note)} <span class="text-xs rounded-full px-2 py-0.5 ${c.level === 'co-so' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-neutral-100 text-neutral-500'}">${c.level === 'co-so' ? 'Có cơ sở' : 'Dân gian — chưa kiểm chứng'}</span></li>`).join('')}</ul>` : ''}

            ${f.selection_note ? `<h2 class="font-semibold mt-6">🛒 Cách chọn</h2><p class="text-sm text-neutral-700 mt-1">${esc(f.selection_note)}</p>` : ''}
            ${f.storage_note ? `<h2 class="font-semibold mt-4">🧊 Bảo quản</h2><p class="text-sm text-neutral-700 mt-1">${esc(f.storage_note)}</p>` : ''}

            <h2 class="font-semibold mt-6">💰 Giá tham khảo theo mùa vụ (VNĐ/kg)</h2>
            ${(prices ?? []).length ? `
            <div class="overflow-x-auto mt-2"><table class="text-sm border-collapse">
                <tr><th class="border px-2 py-1 bg-neutral-50 text-left">Miền</th>${monthsWithPrices.map(m => `<th class="border px-2 py-1 bg-neutral-50">Th ${m}</th>`).join('')}</tr>
                ${Object.keys(byRegion).map(r => `<tr><td class="border px-2 py-1 font-medium">${regionName[r]}</td>${monthsWithPrices.map(m => {
                    const p = byRegion[r][m];
                    return `<td class="border px-2 py-1 text-center">${p ? fmt(p.price_min) + '–' + fmt(p.price_max) : '—'}</td>`;
                }).join('')}</tr>`).join('')}
            </table></div>
            <p class="text-xs text-neutral-400 mt-2">Giá tham khảo mùa vụ thu thập ngày ${esc(sources[0]?.collected_at ?? '')} — nguồn: ${sources.map(s => s.source_url ? `<a class="underline" href="${esc(s.source_url)}" target="_blank" rel="noopener">${esc(s.source_name.split(' — ')[0].split(':')[0])}</a>` : esc(s.source_name.split(' — ')[0].split(':')[0])).join('; ')}. Giá thực tế thay đổi theo chợ và ngày mua.</p>`
            : '<p class="text-sm text-neutral-500 mt-1">Chưa có dữ liệu giá đủ tin cậy cho quả này.</p>'}
        </div>`;
    })
    .catch(() => { document.getElementById('content').textContent = 'Không tải được hồ sơ quả.'; });
</script>
</body>
</html>
