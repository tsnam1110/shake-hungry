@extends('layouts.app')

@section('title', 'Rau theo mùa — Lắc Đói')

@section('content')
    <a href="/rau-mua" style="display:inline-flex;align-items:center;gap:3px;color:var(--color-link);font-size:16.5px;font-weight:500;text-decoration:none;padding-top:10px">
        <svg width="11" height="18" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="transform:rotate(180deg)"><path d="M1 1l6 6-6 6"/></svg>
        Rau theo mùa
    </a>
    <div id="content" style="color:var(--color-faint);margin-top:14px">Đang tải…</div>
@endsection

@push('scripts')
<script>
const slug = location.pathname.split('/').pop();
const groupName = { 'rau-la': 'Rau lá', 'rau-cu': 'Rau củ', 'rau-qua': 'Rau quả', 'dau-do': 'Đậu–đỗ rau, ngô, giá', 'rau-thom-an-kem': 'Rau thơm ăn kèm', 'dang-che-bien': 'Dạng chế biến' };
const supplyName = { 'trong-nuoc': 'Trồng trong nước', 'da-lat': 'Đà Lạt', 'nhap-khau': 'Nhập khẩu', 'ca-hai': 'Cả hai (tươi trong nước + đông lạnh nhập)' };
const stateName = { tuoi: 'Tươi', muoi: 'Muối', kho: 'Khô', chua: 'Chua' };
const regionLabel = { bac: 'Miền Bắc', trung: 'Miền Trung', nam: 'Miền Nam' };
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
function fmt(v) { return v == null ? '—' : Math.round(v).toLocaleString('vi-VN'); }
function seasonRow(label, months, bg, fg) {
    return `<div style="display:flex;align-items:center;gap:3px;margin-top:5px">
        <span style="width:104px;font-size:12.5px;color:var(--color-sub);flex:none">${label}</span>
        ${Array.from({length: 12}, (_, i) => `<span style="flex:1;text-align:center;font-size:10.5px;border-radius:5px;padding:2.5px 0;${(months ?? []).includes(i + 1) ? `background:${bg};color:${fg};font-weight:700` : 'background:var(--color-fill);color:var(--color-faint)'}">${i + 1}</span>`).join('')}
    </div>`;
}

fetch('/api/vegetables/' + encodeURIComponent(slug), { headers: { 'Accept': 'application/json' } })
    .then(r => { if (!r.ok) throw new Error('not-found'); return r.json(); })
    .then(({ vegetable: v, ingredient: ing, combinations, combinations_disclaimer }) => {
        const n = ing?.nutrition ?? {};
        document.title = v.name_vi + ' — Lắc Đói';
        // Tên gọi khác: ưu tiên tên khác của hồ sơ rau, thiếu thì dùng tên
        // gọi khác của nguyên liệu liên kết (cùng mẫu dòng tên khác của quả).
        const aliasNames = (v.aliases ?? []).length ? v.aliases : (ing?.aliases ?? []).map(a => a.name);
        const allergenTags = (ing?.tags ?? []).filter(t => t.slug.startsWith('allergen:'));
        // Dạng chế biến bán quanh năm: lịch vùng trong dữ liệu để null, khi
        // vẽ phải hiểu là đủ 12 tháng, không phải không có tháng nào.
        const allYear = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        const monthsFor = (r) => v.state !== 'tuoi' ? allYear : (v['months_' + r] ?? null);
        const regionNotes = ['bac', 'trung', 'nam'].map(r => {
            if (v.state !== 'tuoi') return '';
            if (v['months_' + r] === null || v['months_' + r] === undefined) return `<p style="font-size:14.5px;margin:6px 0 0"><b>${regionLabel[r]}:</b> Chưa có lịch chợ riêng — không gán tháng.</p>`;
            const note = (v.region_notes && v.region_notes[r]) || '';
            return note ? `<p style="font-size:14.5px;margin:6px 0 0"><b>${regionLabel[r]}:</b> ${esc(note)}</p>` : '';
        }).join('');
        const nutNoteParts = [];
        if (n.sodium_mg != null) nutNoteParts.push(`Natri ${fmt(n.sodium_mg)}mg`);
        if (n.fct_source) nutNoteParts.push(`Nguồn số liệu: ${esc(n.fct_source)}`);
        if (n.scientific_name) nutNoteParts.push(esc(n.scientific_name));
        if (v.kcal_confidence) nutNoteParts.push(`Độ tin cậy kcal: ${esc(v.kcal_confidence)}`);
        document.getElementById('content').innerHTML = `
        <h1 class="ld-title" style="margin-top:6px">${esc(v.name_vi)}</h1>
        ${aliasNames.length ? `<p style="color:var(--color-faint);margin:0">Tên gọi khác: ${aliasNames.map(a => esc(a)).join(', ')}</p>` : ''}
        <p class="ld-subtitle" style="margin-top:4px">${esc(groupName[v.group] || v.group)} · ${esc(supplyName[v.supply] || v.supply)} · ${esc(stateName[v.state] || v.state)}</p>
        ${v.image ? `<figure style="margin:18px 0 0">
            <img src="${esc(v.image.url)}" alt="${esc(v.name_vi)}" style="width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:18px;display:block" fetchpriority="high" decoding="async">
            <figcaption style="color:var(--color-faint);font-size:12.5px;margin-top:6px">${v.image.is_ai ? 'Ảnh minh họa' : 'Ảnh: ' + esc(v.image.attribution ?? '')}</figcaption>
        </figure>` : ''}

        <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap">
            <button type="button" class="ld-btn ld-btn-gray" data-feedback ${ing ? `data-ingredient="${ing.id}"` : ''} data-context="${esc(v.name_vi)}" style="padding:11px 20px;font-size:15px">Gửi phản hồi</button>
        </div>
        <h2 class="ld-section-title">Thông tin dị ứng</h2>
        <div class="ld-card">
            ${allergenTags.length ? `<div style="display:flex;flex-wrap:wrap;gap:6px">${allergenTags.map(t => `<span class="ld-badge" style="background:var(--color-danger-tint);color:var(--color-danger)">Dị ứng: ${esc(t.name)}</span>`).join('')}</div>
                <p class="ld-note" style="margin-top:10px">Nhãn dị ứng theo dữ liệu dự án biên soạn — vẫn có thể có phản ứng chéo hoặc dị ứng cá nhân ngoài danh sách này. Nếu bạn dị ứng nặng, hãy thận trọng và hỏi ý kiến bác sĩ. <a href="/mien-tru-y-te">Đọc Miễn trừ y tế</a>.</p>`
            : `<p class="ld-note" style="margin:0">Dữ liệu hiện tại chưa ghi nhận chất gây dị ứng phổ biến ở loại rau này. Điều này không phải cam kết an toàn tuyệt đối — dị ứng cá nhân vẫn có thể xảy ra. <a href="/mien-tru-y-te">Đọc Miễn trừ y tế</a>.</p>`}
        </div>

        <div class="ld-card" style="margin-top:18px">
            <p style="margin:0;color:var(--color-sub);font-size:14.5px;font-weight:600">Dinh dưỡng mỗi 100g phần ăn được</p>
            ${v.kcal_per_100g != null ? `
            <div style="display:flex;align-items:baseline;gap:9px;margin-top:4px">
                <span class="ld-kcal-so ld-tnum">${fmt(v.kcal_per_100g)}</span>
                <span style="color:var(--color-sub);font-size:14.5px">kcal/100g</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5" style="margin-top:14px">
                <div class="ld-macro"><div class="gt ld-tnum">${fmt(n.protein_g)} g</div><div class="nhan">Đạm</div></div>
                <div class="ld-macro"><div class="gt ld-tnum">${fmt(n.fat_g)} g</div><div class="nhan">Béo</div></div>
                <div class="ld-macro"><div class="gt ld-tnum">${fmt(n.carb_g)} g</div><div class="nhan">Bột đường</div></div>
                <div class="ld-macro"><div class="gt ld-tnum">${fmt(n.fiber_g)} g</div><div class="nhan">Chất xơ</div></div>
            </div>
            ${nutNoteParts.length ? `<p class="ld-note" style="margin-top:12px">${nutNoteParts.join(' · ')}</p>` : ''}
            ${n.unverified ? '<div class="ld-warn" style="margin-top:12px"><span>Số dinh dưỡng từ nguồn thứ cấp — chưa kiểm định chéo, chỉ nên tham khảo tương đối.</span></div>' : ''}`
            : '<p style="color:var(--color-faint);margin:10px 0 0">Chưa có số dinh dưỡng đạt chuẩn cho rau này — hệ thống không đoán số.</p>'}
            ${v.uncertain && v.uncertain_note ? `<p class="ld-note" style="margin-top:10px">Tháng chưa chắc chắn: ${esc(v.uncertain_note)}</p>` : ''}
        </div>

        ${window.ldShowTcm && ing?.tcm?.traits?.length ? `
        <div class="ld-card" style="margin-top:30px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div class="ld-tcm-lines">
                    ${ldTcm.line('nature', ing.tcm.primary.nature, 'Tính:', esc(ing.tcm.primary.nature_label))}
                    ${ldTcm.line('flavor', ing.tcm.primary.flavor, 'Vị:', esc(ing.tcm.primary.flavor_label))}
                    ${ldTcm.line('element', ing.tcm.primary.element, 'Ngũ hành:', esc(ing.tcm.primary.element_label))}
                    ${ing.tcm.primary.derived_from ? '<div><span class="ld-badge">suy diễn</span></div>' : ''}
                </div>
                <button type="button" class="ld-btn ld-btn-gray" data-tcm-open data-tcm-title="Tính vị: ${esc(v.name_vi)}" style="padding:9px 16px;font-size:14px;flex:none">Nguồn &amp; chi tiết</button>
            </div>
        </div>
        <template id="tcm-detail">
            ${ing.tcm.traits.map(t => `<div class="ld-row" style="align-items:flex-start;padding-left:0;padding-right:0"><span class="grow">
                ${ldTcm.segments(t)}
                <span class="sub" style="overflow-wrap:anywhere">Nguồn: ${esc(t.source_ref)} · Độ tin cậy: ${esc(t.confidence)}</span>
                ${t.derived_from ? `<span class="sub">Nhãn suy diễn từ ${esc(t.derived_from_name ?? t.derived_from)} — đây là dạng chế biến đơn thành phần của nguyên liệu gốc, dự án suy nhãn theo chính sách riêng và luôn để độ tin cậy thấp.</span>` : ''}
                ${t.usage_restricted ? `<span class="sub" style="color:var(--color-danger)">Không được dùng — ${esc(t.usage_note ?? '')}</span>` : ''}
            </span></div>`).join('')}
            ${ing.tcm.has_conflicts ? '<p class="ld-note" style="margin-top:10px">Các nguồn chưa thống nhất về tính của loại rau này — giá trị chính ngoài trang là nhãn có độ tin cậy cao nhất; mọi nhãn đều được giữ và hiển thị đầy đủ trong mục này.</p>' : ''}
            <p class="ld-note" style="margin-top:10px">Phân loại theo quan niệm ẩm thực truyền thống — thông tin văn hoá, không phải tư vấn sức khoẻ. <a href="/mien-tru-y-te">Đọc Miễn trừ y tế</a>.</p>
        </template>` : ''}

        ${(combinations ?? []).length ? `<h2 class="ld-section-title">Kết hợp cần lưu ý</h2>
        <div class="ld-card">
            ${combinations.map(c => `<div style="padding:10px 0;border-bottom:1px solid var(--color-line)">
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                    <span class="ld-badge" style="background:#eef4fb;color:#0058b3">${esc(c.tier_label)}</span>
                    <b>${c.other?.type === 'drug_group' ? 'Nếu bạn đang dùng ' : 'Ăn cùng '}${esc(c.other?.label ?? '')}</b>
                </div>
                <p class="ld-note" style="margin:6px 0 0"><b>${esc(c.science_label)}.</b>${c.condition_vi ? ' ' + esc(c.condition_vi) : ''}</p>
                ${c.mechanism_vi ? `<p class="ld-note" style="margin:4px 0 0">${esc(c.mechanism_vi)}</p>` : ''}
                ${c.traditional_source ? `<p class="ld-note" style="margin:4px 0 0">Nguồn truyền thống: ${esc(c.traditional_source.text)}${c.traditional_source.quote ? ` — “${esc(c.traditional_source.quote)}”` : ''}</p>` : ''}
                ${(c.sources ?? []).length ? `<p class="ld-note" style="margin:4px 0 0">Nguồn: ${c.sources.map(s => `<a href="${esc(s.url)}" target="_blank" rel="noopener" style="color:var(--color-link)">${esc(s.note)}</a>`).join(' · ')}</p>` : ''}
            </div>`).join('')}
            <p class="ld-note" style="margin-top:10px">${esc(combinations_disclaimer ?? '')}</p>
        </div>` : ''}

        <h2 class="ld-section-title">Mùa vụ trong năm</h2>
        <div class="ld-card" style="padding:16px 18px">
            ${seasonRow('Bắc — có bán', monthsFor('bac'), '#bfe8cb', 'var(--color-mint-dark)')}
            ${seasonRow('Trung — có bán', monthsFor('trung'), '#bfe8cb', 'var(--color-mint-dark)')}
            ${seasonRow('Nam — có bán', monthsFor('nam'), '#bfe8cb', 'var(--color-mint-dark)')}
            ${seasonRow('Chính vụ', v.main_season_months, 'var(--color-mint)', '#fff')}
            ${seasonRow('Tốt nhất', v.best_months, '#4a90d9', '#fff')}
            ${v.state !== 'tuoi' ? '<p style="font-size:14.5px;margin:14px 0 0">Dạng chế biến bán quanh năm.</p>' : ''}
            ${regionNotes}
            <p style="font-size:14.5px;margin:14px 0 0">Tháng là tháng có bán trên thị trường, không đồng nghĩa thu hoạch tại chỗ ở miền đó. Rau miền Nam mùa đông chủ yếu là hàng Đà Lạt.</p>
        </div>

        ${v.selection_note ? `<h2 class="ld-section-title">Cách chọn rau ngon</h2><p style="font-size:15.5px;margin:0">${esc(v.selection_note)}</p>` : ''}
        ${v.storage_note ? `<h2 class="ld-section-title">Bảo quản</h2><p style="font-size:15.5px;margin:0">${esc(v.storage_note)}</p>` : ''}
        ${v.cooking_note ? `<h2 class="ld-section-title">Cách nấu</h2><p style="font-size:15.5px;margin:0">${esc(v.cooking_note)}</p>` : ''}
        ${v.safety_note ? `<h2 class="ld-section-title">Lưu ý an toàn</h2><p style="font-size:15.5px;margin:0">${esc(v.safety_note)}</p>` : ''}
        ${(v.cooking_note || v.safety_note) ? `<p class="ld-note" style="margin-top:10px">Các lưu ý trên là an toàn sơ chế/nấu chín và vệ sinh, không phải cặp kiêng kỵ — chỉ cặp trong khối “Kết hợp cần lưu ý” mới là cặp đã thẩm định.</p>` : ''}

        <h2 class="ld-section-title">Nguồn &amp; độ tin cậy</h2>
        <div class="ld-card">
            <p class="ld-note" style="margin:0">${esc(v.source_note || '')}</p>
            ${ing?.nutrition?.unverified ? '<p class="ld-note" style="margin-top:8px">Số dinh dưỡng từ nguồn thứ cấp — chưa kiểm định.</p>' : ''}
            ${v.fresh_counterpart_slug ? `<p style="margin:8px 0 0">Đối tác tươi: <a href="/rau-mua/${esc(v.fresh_counterpart_slug)}" style="color:var(--color-link)">${esc(v.fresh_counterpart_slug)}</a></p>` : ''}
        </div>`;
    })
    .catch(() => { document.getElementById('content').textContent = 'Không tìm thấy rau này.'; });
</script>
@endpush
