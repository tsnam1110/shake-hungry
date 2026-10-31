<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chi tiết món — Lắc Đói</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-orange-50 min-h-screen text-neutral-800">
<main class="mx-auto max-w-2xl px-4 pt-8 pb-16" id="app">
    <a href="/" class="text-sm text-orange-700 underline">← Về trang Lắc món</a>
    <div id="content" class="mt-4 text-neutral-400">Đang tải…</div>
</main>
<script>
const slug = location.pathname.split('/').pop();
const slotName = { sang: 'Sáng', trua: 'Trưa', chieu: 'Chiều', toi: 'Tối', 'an-vat': 'Ăn vặt' };
const confLabel = { high: 'tin cậy cao', medium: 'tin cậy trung bình', low: 'tin cậy thấp — số tham khảo' };
fetch('/api/dishes/' + encodeURIComponent(slug), { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(({ dish: d, variants, provenance }) => {
        const n = d.nutrition;
        const name = d.names?.find(x => x.is_primary)?.name ?? d.slug;
        const aliases = (d.names ?? []).filter(x => !x.is_primary).map(x => x.name);
        document.title = name + ' — Lắc Đói';
        document.getElementById('content').innerHTML = `
        <div class="bg-white rounded-2xl shadow p-5">
            <h1 class="text-3xl font-bold">${name}</h1>
            ${aliases.length ? `<p class="text-neutral-400 text-sm">Còn gọi: ${aliases.join(', ')}</p>` : ''}
            <p class="text-neutral-500 mt-1">${d.region?.name ?? ''} ${d.cooking_method ? '· Cách làm: ' + d.cooking_method : ''} ${d.serving_desc ? '· ' + d.serving_desc : ''}</p>
            <p class="mt-1 text-sm">${(d.meal_slots ?? []).map(s => `<span class="inline-block bg-orange-100 text-orange-800 rounded-full px-2 py-0.5 mr-1">${slotName[s.slot] ?? s.slot}</span>`).join('')}</p>
            ${d.parent ? `<p class="text-sm mt-2">Biến thể của: <a class="text-orange-700 underline" href="/mon/${d.parent.slug}">${d.parent.names?.[0]?.name ?? ''}</a></p>` : ''}
            ${variants.length ? `<p class="text-sm mt-2">Biến thể: ${variants.map(v => `<a class="text-orange-700 underline" href="/mon/${v.slug}">${v.name}</a>`).join(', ')}</p>` : ''}
            <div class="mt-4 rounded-xl bg-orange-50 border border-orange-200 p-4">
                ${n ? `<div class="text-xl font-bold">🔥 ${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)} kcal/khẩu phần</div>
                <div class="text-sm text-neutral-600 mt-1">Đạm ${n.protein_g ?? '?'}g · Béo ${n.fat_g ?? '?'}g · Bột đường ${n.carb_g ?? '?'}g · Chất xơ ${n.fiber_g ?? '?'}g</div>
                <div class="text-sm text-neutral-500">Độ ${confLabel[n.confidence] ?? n.confidence}</div>
                ${(provenance?.unverified_ingredients ?? []).length ? `<div class="mt-2 rounded-lg bg-amber-100 border border-amber-300 text-amber-800 px-3 py-2">⚠️ <b>Số liệu tham khảo — chưa kiểm định:</b> món có nguyên liệu dùng nguồn thứ cấp (${provenance.unverified_ingredients.join(', ')}). Thông tin đã có nhưng chưa được kiểm chứng bằng nguồn chính thống.</div>` : ''}`
                : '<div class="text-neutral-400">Chưa đủ dữ liệu dinh dưỡng cho món này.</div>'}
            </div>
            ${provenance ? `<details class="mt-3 text-sm">
                <summary class="cursor-pointer text-orange-700 font-medium">Con số này từ đâu ra?</summary>
                <div class="mt-2 text-neutral-600 space-y-1">
                    <p>· Bảng thành phần: <b>${provenance.fct_source}</b> · Engine phiên bản ${provenance.engine_version} · Tính ngày ${new Date(provenance.computed_at).toLocaleDateString('vi-VN')}</p>
                    <p>· Nguyên liệu đã tham gia tính: ${provenance.contributing_ingredients.join(', ') || '—'}</p>
                    ${(provenance.missing_ingredients ?? []).length ? `<p>· Nguyên liệu chưa tra được (không tính vào số trên): ${provenance.missing_ingredients.join(', ')}</p>` : ''}
                    ${(provenance.unverified_ingredients ?? []).length ? `<p>· Nguyên liệu dùng nguồn thứ cấp (chưa kiểm định): ${provenance.unverified_ingredients.join(', ')}</p>` : ''}
                </div></details>` : ''}
        </div>
        <div class="bg-white rounded-2xl shadow p-5 mt-4">
            <h2 class="font-semibold mb-2">Nguyên liệu (một khẩu phần)</h2>
            <ul class="text-sm space-y-1">
                ${(d.dish_ingredients ?? []).map(di => `<li>• <b>${di.ingredient?.name_vi ?? ''}</b> — ${di.quantity ?? ''} ${di.unit_name ?? ''}${di.is_optional ? ' <span class="text-neutral-400">(tuỳ chọn)</span>' : ''}</li>`).join('') || '<li class="text-neutral-400">Chưa có định lượng.</li>'}
            </ul>
            ${d.recipe_steps?.length ? `<h2 class="font-semibold mt-4 mb-2">Cách làm</h2>
            <ol class="text-sm space-y-1 list-decimal list-inside">${d.recipe_steps.map(s => `<li>${s}</li>`).join('')}</ol>` : ''}
            ${d.needs_review ? '<p class="text-xs text-amber-600 mt-4">⚠️ Món đang trong giai đoạn biên soạn dữ liệu — thông tin có thể được cập nhật sau khi duyệt.</p>' : ''}
        </div>`;
    })
    .catch(() => { document.getElementById('content').textContent = 'Không tải được món này.'; });
</script>
</body>
</html>
