@extends('layouts.app')

@section('title', 'Nguyên liệu — Lắc Đói')

@section('content')
    <a href="/nguyen-lieu" style="display:inline-flex;align-items:center;gap:5px;color:var(--color-link);text-decoration:none;font-weight:600;font-size:15px;margin-top:14px">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 5.5 8 12l6.5 6.5"/></svg>
        Nguyên liệu</a>
    <div id="content"><p style="color:var(--color-faint)">Đang tải…</p></div>
@endsection

@push('scripts')
<script>
const slug = location.pathname.split('/').filter(Boolean).pop();
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
const catLabel = { 'thit': 'Thịt', 'thuy-san': 'Thuỷ sản', 'rau-cu': 'Rau củ', 'rau-gia-vi': 'Rau gia vị', 'trai-cay': 'Trái cây', 'tinh-bot': 'Tinh bột', 'gia-vi': 'Gia vị', 'dau-mo': 'Dầu mỡ', 'khac': 'Khác' };
const facetLabel = { origin: 'Nguồn gốc', state: 'Trạng thái', allergen: 'Dị ứng', diet: 'Chế độ ăn', protein: 'Nhóm đạm' };
const fctLabel = { vn_fct_2007: 'Bảng TPTP Việt Nam', usda_sr_legacy: 'USDA SR Legacy', usda_foundation: 'USDA Foundation', usda_fndds: 'USDA FNDDS', mext_japan: 'Bảng TPTP Nhật Bản (MEXT)', cofid_uk: 'Bảng TPTP Anh (CoFID)', off: 'Open Food Facts', proxy: 'Proxy — chưa kiểm định', label_vn: 'Nhãn sản phẩm Việt Nam' };
const macroTile = (v, u, label) => v == null ? '' : `<div class="ld-macro"><div class="v ld-tnum">${(+v).toLocaleString('vi-VN', { maximumFractionDigits: 1 })}${u}</div><div class="k">${label}</div></div>`;

fetch('/api/ingredients/' + slug).then(r => { if (!r.ok) throw new Error('404'); return r.json(); }).then(({ ingredient: i, fruit_slug, dishes }) => {
    document.title = i.name_vi + ' — Nguyên liệu Lắc Đói';
    const snap = i.nutrition_snapshot ?? {};
    const hasKcal = snap.kcal_per_100g != null;
    const macros = [
        ['protein_g', 'g', 'Đạm'], ['fat_g', 'g', 'Béo'], ['carb_g', 'g', 'Tinh bột'],
        ['fiber_g', 'g', 'Chất xơ'], ['sodium_mg', 'mg', 'Natri'], ['water_g', 'g', 'Nước'],
    ].filter(([k]) => snap[k] != null)
     .map(([k, u, label]) => macroTile(snap[k], u, label)).join('');
    const allergenTags = (i.tags ?? []).filter(t => t.slug.startsWith('allergen:'));
    const facets = {};
    (i.tags ?? []).filter(t => !t.slug.startsWith('allergen:')).forEach(t => { const f = t.slug.split(':')[0]; (facets[f] ??= []).push(t); });
    const img = i.image;
    document.getElementById('content').innerHTML = `
        <div class="ld-card" style="margin-top:14px;padding:0;overflow:hidden">
            <img src="${img?.url ?? '/images/placeholder-ingredient.svg'}" alt="${esc(i.name_vi)}" style="width:100%;aspect-ratio:16/8;object-fit:cover;display:block" fetchpriority="high" decoding="async">
            ${img ? `<p style="margin:0;padding:9px 16px;font-size:12.5px;color:var(--color-faint)">${img.is_ai ? 'Ảnh minh họa' : esc(img.attribution ?? '')}</p>` : ''}
        </div>
        <h1 class="ld-title" style="margin-top:16px">${esc(i.name_vi)}</h1>
        <p class="ld-subtitle">${catLabel[i.category] ?? esc(i.category ?? '')}${i.name_en ? ' · ' + esc(i.name_en) : ''}${i.scientific_name ? ' · ' : ''}${i.scientific_name ? '<i>' + esc(i.scientific_name) + '</i>' : ''}</p>
        ${(i.aliases ?? []).length ? `<p class="ld-note" style="margin-top:8px">Tên gọi khác: ${i.aliases.map(a => esc(a.name)).join(' · ')}</p>` : ''}
        <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap">
            <button type="button" class="ld-btn ld-btn-gray" data-feedback data-ingredient="${i.id}" data-context="${esc(i.name_vi)}" style="padding:11px 20px;font-size:15px">Gửi phản hồi về nguyên liệu này</button>
        </div>
        ${fruit_slug ? `<a href="/hoa-qua/${fruit_slug}" class="ld-row" style="margin-top:14px;text-decoration:none"><span class="grow"><span style="font-weight:600">Hồ sơ hoa quả đầy đủ</span><span class="sub">Mùa vụ, giá theo vùng, cách chọn và bảo quản</span></span><span style="color:var(--color-faint);font-size:19px">›</span></a>` : ''}

        ${i.unverified ? '<div class="ld-warn" style="margin-top:16px"><span>Số liệu của nguyên liệu này lấy từ nguồn thứ cấp, <strong>chưa qua kiểm định</strong> — hãy cân nhắc khi dùng để tính toán chính xác.</span></div>' : ''}

        <div class="ld-card" style="margin-top:16px">
            <p style="margin:0;color:var(--color-sub);font-size:14.5px;font-weight:600">Dinh dưỡng trong 100g phần ăn được</p>
            ${hasKcal ? `
            <div style="display:flex;align-items:baseline;gap:9px;margin-top:4px">
                <span class="ld-kcal-so ld-tnum">${Math.round(snap.kcal_min ?? snap.kcal_per_100g)}${snap.kcal_max != null && snap.kcal_max !== snap.kcal_min ? '–' + Math.round(snap.kcal_max) : ''}</span>
                <span style="color:var(--color-sub);font-size:14.5px">kcal</span>
            </div>
            ${macros ? `<div class="ld-macros">${macros}</div>` : ''}
            <div style="margin-top:14px">
                <div class="ld-row"><span class="grow" style="color:var(--color-sub)">Nguồn con số</span><span class="value">${esc(fctLabel[snap.fct_source] ?? snap.fct_source ?? '—')}</span></div>
                <div class="ld-row"><span class="grow" style="color:var(--color-sub)">Độ tin cậy ánh xạ</span><span class="value">${esc(i.mapping_confidence ?? '—')}</span></div>
                ${snap.source_url ? `<div class="ld-row"><span class="grow" style="color:var(--color-sub)">Tra cứu nguồn</span><a class="value" href="${esc(snap.source_url)}" target="_blank" rel="noopener" style="color:var(--color-link);text-decoration:none">Mở trang nguồn ›</a></div>` : ''}
            </div>`
            : '<p style="color:var(--color-faint);margin:10px 0 0">Chưa đủ dữ liệu dinh dưỡng cho nguyên liệu này — hệ thống không hiện số thay vì đoán.</p>'}
        </div>

        <h2 class="ld-section-title">Thông tin dị ứng</h2>
        <div class="ld-card">
            ${allergenTags.length ? `<div style="display:flex;flex-wrap:wrap;gap:6px">${allergenTags.map(t => `<span class="ld-badge" style="background:var(--color-danger-tint);color:var(--color-danger)">Dị ứng: ${esc(t.name)}</span>`).join('')}</div>
                <p class="ld-note" style="margin-top:10px">Nguyên liệu này mang nhãn chất gây dị ứng theo dữ liệu dự án biên soạn. Khi lắc món, dị ứng trong hồ sơ của bạn là điều kiện lọc cứng — món chứa nguyên liệu này sẽ bị loại. <a href="/mien-tru-y-te">Đọc Miễn trừ y tế</a>.</p>`
            : `<p class="ld-note" style="margin:0">Dữ liệu hiện tại chưa ghi nhận chất gây dị ứng phổ biến ở nguyên liệu này. Điều này không phải cam kết an toàn tuyệt đối — nếu bạn bị dị ứng, hãy kiểm tra kỹ trước khi dùng. <a href="/mien-tru-y-te">Đọc Miễn trừ y tế</a>.</p>`}
        </div>
        ${window.ldShowTcm && i.tcm?.traits?.length ? `<div class="ld-card" style="margin-top:30px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div class="ld-tcm-lines">
                    ${ldTcm.line('nature', i.tcm.primary.nature, 'Tính:', esc(i.tcm.primary.nature_label))}
                    ${ldTcm.line('flavor', i.tcm.primary.flavor, 'Vị:', esc(i.tcm.primary.flavor_label))}
                    ${ldTcm.line('element', i.tcm.primary.element, 'Ngũ hành:', esc(i.tcm.primary.element_label))}
                    ${i.tcm.primary.derived_from ? '<div><span class="ld-badge">suy diễn</span></div>' : ''}
                </div>
                <button type="button" class="ld-btn ld-btn-gray" data-tcm-open data-tcm-title="Tính vị: ${esc(i.name_vi)}" style="padding:9px 16px;font-size:14px;flex:none">Nguồn &amp; chi tiết</button>
            </div>
        </div>
        <template id="tcm-detail">
            ${i.tcm.traits.map(t => `<div class="ld-row" style="align-items:flex-start;padding-left:0;padding-right:0"><span class="grow">
                ${ldTcm.segments(t)}
                <span class="sub" style="overflow-wrap:anywhere">Nguồn: ${esc(t.source_ref)} · Độ tin cậy: ${esc(t.confidence)}</span>
                ${t.derived_from ? `<span class="sub">Nhãn suy diễn từ ${esc(t.derived_from_name ?? t.derived_from)} — đây là dạng chế biến đơn thành phần của nguyên liệu gốc, dự án suy nhãn theo chính sách riêng và luôn để độ tin cậy thấp.</span>` : ''}
                ${t.usage_restricted ? `<span class="sub" style="color:var(--color-danger)">Không được dùng — ${esc(t.usage_note ?? '')}</span>` : ''}
            </span></div>`).join('')}
            ${i.tcm.has_conflicts ? '<p class="ld-note" style="margin-top:10px">Các nguồn chưa thống nhất về tính của nguyên liệu này — giá trị chính ngoài trang là nhãn có độ tin cậy cao nhất; mọi nhãn đều được giữ và hiển thị đầy đủ trong mục này, dự án không giấu nhãn nào.</p>' : ''}
            <p class="ld-note" style="margin-top:10px">Phân loại theo quan niệm ẩm thực truyền thống, gán ở cấp độ nguyên liệu — đây là thông tin văn hoá, không phải tư vấn sức khoẻ. <a href="/mien-tru-y-te">Đọc Miễn trừ y tế</a>.</p>
        </template>` : ''}

        ${(i.combinations ?? []).length ? `<h2 class="ld-section-title">Kết hợp cần lưu ý</h2>
        <div class="ld-card">
            ${i.combinations.map(c => `<div style="padding:10px 0;border-bottom:1px solid var(--color-line)">
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                    <span class="ld-badge" style="background:#eef4fb;color:#0058b3">${esc(c.tier_label)}</span>
                    <b>${c.other?.type === 'drug_group' ? 'Nếu bạn đang dùng ' : 'Ăn cùng '}${esc(c.other?.label ?? '')}</b>
                </div>
                <p class="ld-note" style="margin:6px 0 0"><b>${esc(c.science_label)}.</b>${c.condition_vi ? ' ' + esc(c.condition_vi) : ''}</p>
                ${c.mechanism_vi ? `<p class="ld-note" style="margin:4px 0 0">${esc(c.mechanism_vi)}</p>` : ''}
                ${c.traditional_source ? `<p class="ld-note" style="margin:4px 0 0">Nguồn truyền thống: ${esc(c.traditional_source.text)}${c.traditional_source.quote ? ` — “${esc(c.traditional_source.quote)}”` : ''}</p>` : ''}
                ${(c.sources ?? []).length ? `<p class="ld-note" style="margin:4px 0 0">Nguồn: ${c.sources.map(s => `<a href="${esc(s.url)}" target="_blank" rel="noopener" style="color:var(--color-link)">${esc(s.note)}</a>`).join(' · ')}</p>` : ''}
            </div>`).join('')}
            <p class="ld-note" style="margin-top:10px">Thông tin tham khảo về an toàn thực phẩm và văn hoá ẩm thực; không thay thế tư vấn y tế. Nếu đang dùng thuốc, hãy hỏi bác sĩ hoặc dược sĩ của bạn.</p>
        </div>` : ''}

        ${(i.component_links ?? []).length ? `<h2 class="ld-section-title">Thành phần cấu thành</h2>
        <div class="ld-group">
            ${i.component_links.map(c => `<a class="ld-row" href="/nguyen-lieu/${c.slug}" style="text-decoration:none"><span class="grow">${esc(c.name_vi)}</span><span style="color:var(--color-faint);font-size:19px">›</span></a>`).join('')}
        </div>
        <p class="ld-note" style="margin-top:10px">Đây là nguyên liệu hỗn hợp — bấm vào từng thành phần để xem chi tiết dinh dưỡng và tính vị của thành phần đó.</p>` : ''}

        ${Object.keys(facets).length ? `<h2 class="ld-section-title">Phân loại</h2>
        <div style="display:flex;flex-direction:column;gap:10px">${Object.entries(facets).map(([f, ts]) => `
            <div><p class="ld-group-label" style="margin:0 0 6px">${facetLabel[f] ?? esc(f)}</p>
            <div style="display:flex;flex-wrap:wrap;gap:6px">${ts.map(t => `<span class="ld-badge vu">${esc(t.name)}</span>`).join('')}</div></div>`).join('')}
        </div>` : ''}

        ${dishes.length ? `<h2 class="ld-section-title">Món ăn có ${esc(i.name_vi)}</h2>
        <div class="ld-grid">${dishes.map(d => `
            <a href="/mon/${d.slug}" class="ld-dcard">
                <img class="ld-dcard-img" src="${d.image?.thumb_url ?? d.image?.url ?? '/images/placeholder-dish.svg'}" srcset="${d.image?.srcset ?? ''}" sizes="(max-width: 640px) 50vw, 320px" alt="" loading="lazy" decoding="async">
                <span class="ld-dcard-body">
                    <span class="ld-dcard-title">${esc(d.name ?? d.slug)}</span>
                    <span class="ld-dcard-meta"></span>
                    <span class="ld-dcard-badges"></span>
                    <span class="ld-dcard-foot">${d.nutrition
                        ? `<span class="ld-dcard-kcal ld-tnum">${Math.round(d.nutrition.kcal_min)}–${Math.round(d.nutrition.kcal_max)} kcal</span>`
                        : `<span class="ld-dcard-kcal none">Chưa đủ dữ liệu</span>`}</span>
                </span>
            </a>`).join('')}</div>` : ''}`;
}).catch(() => {
    document.getElementById('content').innerHTML = '<h1 class="ld-title" style="margin-top:16px">Không tìm thấy nguyên liệu</h1><p class="ld-subtitle">Nguyên liệu này chưa có trong kho dữ liệu.</p>';
});
</script>
@endpush
