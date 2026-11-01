@extends('layouts.app')

@section('title', 'Chi tiết quả — Lắc Đói')

@section('content')
    <a href="/hoa-qua" style="display:inline-flex;align-items:center;gap:3px;color:var(--color-link);font-size:16.5px;font-weight:500;text-decoration:none;padding-top:10px">
        <svg width="11" height="18" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="transform:rotate(180deg)"><path d="M1 1l6 6-6 6"/></svg>
        Hoa quả theo mùa
    </a>
    <div id="content" style="color:var(--color-faint);margin-top:14px">Đang tải…</div>
@endsection

@push('scripts')
<script>
const slug = location.pathname.split('/').pop();
const regionName = { bac: 'Miền Bắc', trung: 'Miền Trung', nam: 'Miền Nam' };
const groupName = { 'co-mui': 'Có múi', mong: 'Quả mọng', hach: 'Quả hạch', 'nhiet-doi': 'Nhiệt đới', dua: 'Họ dưa', khac: 'Khác' };
const originName = { 'trong-nuoc': 'Trồng trong nước', 'nhap-khau': 'Nhập khẩu', 'ca-hai': 'Cả trồng trong nước và nhập khẩu' };
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
const heartSvg = '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"><path d="M12 20.5C7 16.5 3.8 13.3 3.8 9.6 3.8 7 5.7 5 8.1 5c1.6 0 3.1.9 3.9 2.3C12.8 5.9 14.3 5 15.9 5c2.4 0 4.3 2 4.3 4.6 0 3.7-3.2 6.9-8.2 10.9Z"/></svg>';
function fmt(v) { return v == null ? '—' : Math.round(v).toLocaleString('vi-VN'); }
function seasonRow(label, months, bg, fg) {
    return `<div style="display:flex;align-items:center;gap:3px;margin-top:5px">
        <span style="width:104px;font-size:12.5px;color:var(--color-sub);flex:none">${label}</span>
        ${Array.from({length: 12}, (_, i) => `<span style="flex:1;text-align:center;font-size:10.5px;border-radius:5px;padding:2.5px 0;${(months ?? []).includes(i + 1) ? `background:${bg};color:${fg};font-weight:700` : 'background:var(--color-fill);color:var(--color-faint)'}">${i + 1}</span>`).join('')}
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
        const allergenTags = (ing.tags ?? []).filter(t => t.slug.startsWith('allergen:'));
        document.getElementById('content').innerHTML = `
        <h1 class="ld-title" style="margin-top:6px">${esc(f.name_vi)}</h1>
        ${ing.aliases?.length ? `<p style="color:var(--color-faint);margin:0">Tên gọi khác: ${ing.aliases.map(a => a.note ? `${esc(a.name)} (${esc(a.note)})` : esc(a.name)).join(', ')}</p>` : ''}
        <p class="ld-subtitle" style="margin-top:4px">${groupName[f.classification_group] ?? ''}${f.family ? ' · ' + esc(f.family) : ''} · ${originName[f.origin] ?? ''}</p>
        ${allergenTags.length ? `<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px">${allergenTags.map(t => `<span class="ld-badge" style="background:var(--color-danger-tint);color:var(--color-danger)">Dị ứng: ${esc(t.name)}</span>`).join('')}</div>` : ''}
        ${f.image ? `<figure style="margin:18px 0 0">
            <img src="${esc(f.image.url)}" alt="${esc(f.name_vi)}" style="width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:18px;display:block" fetchpriority="high" decoding="async">
            <figcaption style="color:var(--color-faint);font-size:12.5px;margin-top:6px">${f.image.is_ai ? 'Ảnh minh họa' : 'Ảnh: ' + esc(f.image.attribution ?? '')}</figcaption>
        </figure>` : ''}

        <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap">
            <button type="button" class="ld-btn ld-btn-gray ld-fav-btn" data-fav-fruit="${f.id}" style="padding:11px 20px;font-size:15px"><span class="heart-ic">${heartSvg}</span>Yêu thích</button>
            <button type="button" class="ld-btn ld-btn-primary" id="fruit-eat-btn" style="padding:11px 20px;font-size:15px">Đã ăn quả này</button>
        </div>

        <div class="ld-card" style="margin-top:18px">
            <p style="margin:0;color:var(--color-sub);font-size:14.5px;font-weight:600">Dinh dưỡng mỗi 100g phần ăn được</p>
            ${n.kcal_per_100g != null ? `
            <div style="display:flex;align-items:baseline;gap:9px;margin-top:4px">
                <span class="ld-kcal-so ld-tnum">${fmt(n.kcal_per_100g)}</span>
                <span style="color:var(--color-sub);font-size:14.5px">kcal/100g</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5" style="margin-top:14px">
                <div class="ld-macro"><div class="gt ld-tnum">${fmt(n.protein_g)} g</div><div class="nhan">Đạm</div></div>
                <div class="ld-macro"><div class="gt ld-tnum">${fmt(n.fat_g)} g</div><div class="nhan">Béo</div></div>
                <div class="ld-macro"><div class="gt ld-tnum">${fmt(n.carb_g)} g</div><div class="nhan">Bột đường</div></div>
                <div class="ld-macro"><div class="gt ld-tnum">${fmt(n.fiber_g)} g</div><div class="nhan">Chất xơ</div></div>
            </div>
            <p class="ld-note" style="margin-top:12px">Natri ${fmt(n.sodium_mg)}mg · Nguồn số liệu: ${esc(n.fct_source ?? '—')}${n.scientific_name ? ' · ' + esc(n.scientific_name) : ''}</p>
            ${n.unverified ? '<div class="ld-warn" style="margin-top:12px"><span>Số dinh dưỡng từ nguồn thứ cấp — chưa kiểm định chéo, chỉ nên tham khảo tương đối.</span></div>' : ''}`
            : '<p style="color:var(--color-faint);margin:10px 0 0">Chưa có số dinh dưỡng đạt chuẩn cho quả này — hệ thống không đoán số.</p>'}
        </div>

        <h2 class="ld-section-title">Mùa vụ trong năm</h2>
        <div class="ld-card" style="padding:16px 18px">
            ${seasonRow('Có hàng', f.availability_months, '#bfe8cb', 'var(--color-mint-dark)')}
            ${seasonRow('Vụ chính', f.main_season_months, 'var(--color-mint)', '#fff')}
            ${seasonRow('Bổ nhất', f.best_nutrition.months, '#4a90d9', '#fff')}
            ${seasonRow('Giá hời nhất', f.best_value.months, 'var(--color-sun)', '#fff')}
            ${f.best_nutrition.note ? `<p style="font-size:14.5px;margin:14px 0 0"><b>Bổ nhất:</b> ${esc(f.best_nutrition.note)}</p>` : ''}
            ${f.best_value.note ? `<p style="font-size:14.5px;margin:6px 0 0"><b>Giá hời:</b> ${esc(f.best_value.note)}</p>` : ''}
        </div>

        ${(f.grown_in ?? []).length ? `
        <h2 class="ld-section-title">Vùng trồng tiêu biểu</h2>
        <div class="ld-group">
            ${f.grown_in.map(g => `<div class="ld-row"><span class="grow"><b>${esc(g.region)}</b>${g.note ? `<span class="sub">${esc(g.note)}</span>` : ''}</span></div>`).join('')}
        </div>` : ''}

        ${(f.pairing_cautions ?? []).length ? `
        <h2 class="ld-section-title">Lưu ý khi kết hợp</h2>
        <div class="ld-group">
            ${f.pairing_cautions.map(c => `<div class="ld-row" style="align-items:flex-start"><span class="grow"><b>${esc(c.with)}</b><span class="sub" style="color:var(--color-sub)">${esc(c.note)}</span></span><span class="ld-badge ${c.level === 'co-so' ? '' : 'unv'}" style="${c.level === 'co-so' ? 'background:var(--color-danger-tint);color:var(--color-danger)' : ''}">${c.level === 'co-so' ? 'Có cơ sở' : 'Dân gian — chưa kiểm chứng'}</span></div>`).join('')}
        </div>` : ''}

        ${f.selection_note ? `<h2 class="ld-section-title">Cách chọn quả ngon</h2><p style="font-size:15.5px;margin:0">${esc(f.selection_note)}</p>` : ''}
        ${f.storage_note ? `<h2 class="ld-section-title">Bảo quản</h2><p style="font-size:15.5px;margin:0">${esc(f.storage_note)}</p>` : ''}

        <h2 class="ld-section-title">Giá tham khảo theo mùa vụ <span style="font-size:14.5px;color:var(--color-faint);font-weight:500">(VNĐ/kg)</span></h2>
        ${(prices ?? []).length ? `
        <div class="ld-card" style="padding:8px 0;overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:14px;min-width:520px">
                <tr><th style="text-align:left;padding:9px 16px;color:var(--color-sub);font-weight:600">Miền</th>${monthsWithPrices.map(m => `<th style="padding:9px 8px;color:var(--color-sub);font-weight:600">Th ${m}</th>`).join('')}</tr>
                ${Object.keys(byRegion).map(r => `<tr style="border-top:1px solid var(--color-line)"><td style="padding:9px 16px;font-weight:600">${regionName[r]}</td>${monthsWithPrices.map(m => {
                    const p = byRegion[r][m];
                    return `<td style="padding:9px 8px;text-align:center" class="ld-tnum">${p ? fmt(p.price_min) + '–' + fmt(p.price_max) : '—'}</td>`;
                }).join('')}</tr>`).join('')}
            </table>
        </div>
        <p class="ld-note" style="margin-top:10px">Giá tham khảo mùa vụ thu thập ngày ${esc(sources[0]?.collected_at ?? '')} — nguồn: ${sources.map(s => s.source_url ? `<a href="${esc(s.source_url)}" target="_blank" rel="noopener" style="color:var(--color-link)">${esc(s.source_name.split(' — ')[0].split(':')[0])}</a>` : esc(s.source_name.split(' — ')[0].split(':')[0])).join('; ')}. Giá thực tế thay đổi theo chợ và ngày mua.</p>`
        : '<p style="color:var(--color-faint)">Chưa có dữ liệu giá đủ tin cậy cho quả này.</p>'}`;
        if (window.ldRefreshHearts) window.ldRefreshHearts();
        const eatBtn = document.getElementById('fruit-eat-btn');
        if (eatBtn) eatBtn.addEventListener('click', async () => {
            const resp = await fetch('/api/fruit-logs', { method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ fruit_id: f.id }) });
            if (resp.status === 401) { ldToast('Đăng nhập để ghi lại quả đã ăn'); return; }
            if (resp.ok) { eatBtn.textContent = 'Đã ghi vào thống kê ✓'; eatBtn.disabled = true; ldToast('Đã ghi ' + f.name_vi + ' vào thống kê quả đã ăn'); }
            else ldToast('Chưa ghi được — thử lại sau');
        });
    })
    .catch(() => { document.getElementById('content').textContent = 'Không tải được hồ sơ quả.'; });
</script>
@endpush
