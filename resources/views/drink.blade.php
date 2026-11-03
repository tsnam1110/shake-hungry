@extends('layouts.app')

@section('title', 'Nước uống — Lắc Đói')

@section('content')
    {{-- Đầu trang theo đúng mẫu trang món (/mon/{slug}): link quay lại kiểu chevron,
         tiêu đề ld-title, phụ đề, hàng badge ld-badge, hộp cảnh báo ld-warn. --}}
    <a href="/do-uong" style="display:inline-flex;align-items:center;gap:3px;color:var(--color-link);font-size:16.5px;font-weight:500;text-decoration:none;padding-top:10px">
        <svg width="11" height="18" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="transform:rotate(180deg)"><path d="M1 1l6 6-6 6"/></svg>
        Thư viện nước uống
    </a>
    <div id="body" style="margin-top:14px"></div>
@endsection

@push('scripts')
<script>
const slug = location.pathname.split('/').pop();
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
function li(x) { return `<li>${esc(x)}</li>`; }
function ing(x) { return `<li>${esc(x.name)}${x.quantity !== null && x.quantity !== undefined ? ` — ${x.quantity}${x.unit ? ' ' + x.unit : ''}` : ''}</li>`; }
const chev = '<svg class="ld-chev" width="8" height="14" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M1 1l6 6-6 6"/></svg>';
fetch('/api/drinks/' + slug).then(r => r.json()).then(({drink: d, combinations, combinations_disclaimer}) => {
    const groupName = { tra: 'Trà', 'ca-phe': 'Cà phê', 'sinh-to': 'Sinh tố', 'nuoc-ep': 'Nước ép', 'giai-khat': 'Giải khát', 'cocktail-co-con': 'Cocktail có cồn', mocktail: 'Mocktail', 'nuoc-ngot-co-ga': 'Nước ngọt có ga', 'tra-dong-chai': 'Trà đóng chai', 'ca-phe-dong-goi': 'Cà phê đóng gói', 'nuoc-ep-dong-hop': 'Nước ép đóng hộp', 'sua-dong-hop': 'Sữa đóng hộp', 'tang-luc-dien-giai': 'Tăng lực / điện giải', 'nuoc-dong-goi-khac': 'Nước đóng gói khác', bia: 'Bia', vang: 'Vang', ruou: 'Rượu', 'nuoc-giai-khat-dong-goi': 'Nước giải khát đóng gói' };
    const cafName = { khong: 'Không caffeine', thap: 'Caffeine thấp', 'trung-binh': 'Caffeine trung bình', cao: 'Caffeine cao' };
    const tempName = { nong: 'Nóng', lanh: 'Lạnh', 'ca-hai': 'Nóng hoặc lạnh' };
    // QT-9: trường trống thì bỏ trống + nhãn lưu ý, không in khoảng rỗng dạng "–g" hay "–%".
    const rangeText = (r, unit) => {
        if (!r) return '';
        if (r.min !== null && r.min !== undefined && r.max !== null && r.max !== undefined) return `${r.min}–${r.max}${unit}`;
        if (r.min !== null && r.min !== undefined) return `từ ${r.min}${unit}`;
        if (r.max !== null && r.max !== undefined) return `đến ${r.max}${unit}`;
        return '';
    };
    let html = `<h1 class="ld-title" style="margin-top:6px">${esc(d.name_vi)}</h1>`;
    html += `<p class="ld-subtitle" style="margin-top:4px">${esc(groupName[d.drink_group] || d.drink_group)} · ${d.kind === 'pha-che' ? 'Pha chế' : 'Đóng gói'}${d.temperature ? ' · ' + esc(tempName[d.temperature] || d.temperature) : ''}${d.serving_ml ? ' · ' + d.serving_ml + ' ml' : ''}</p>`;
    // Hàng badge — cùng kiểu ld-badge như trang món (ô ăn, thẻ phân loại).
    const badges = [`<span class="ld-badge">${esc(groupName[d.drink_group] || d.drink_group)}</span>`];
    badges.push(`<span class="ld-badge">${d.kind === 'pha-che' ? 'Pha chế' : 'Sản phẩm đóng gói'}</span>`);
    if (d.caffeine_level) badges.push(`<span class="ld-badge">${esc(cafName[d.caffeine_level] || d.caffeine_level)}</span>`);
    if (d.alcohol) badges.push('<span class="ld-badge" style="background:var(--color-danger-tint);color:var(--color-danger)">18+</span>');
    html += `<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:12px">${badges.join('')}</div>`;
    // Hộp cảnh báo — cùng kiểu ld-warn như khối mức/cảnh báo an toàn trên trang món.
    if (d.alcohol) html += `<div class="ld-warn" style="margin-top:12px"><span><b>18+</b> — Đồ uống có cồn. Không dùng khi lái xe và không dùng cho người dưới 18 tuổi.</span></div>`;
    if (d.uncertain) html += `<div class="ld-warn" style="margin-top:12px"><span><b>Chưa chắc chắn:</b> ${esc(d.uncertain_note || 'Thông tin về đồ uống này chưa được xác nhận chắc chắn — xem ghi chú nguồn bên dưới.')}</span></div>`;

    // Khối dinh dưỡng — đặt ngay sau đầu trang như trang món ("Năng lượng mỗi khẩu phần"
    // rồi mới tới các khối khác). Nước uống chưa tính được kcal: hiện đúng nhãn đó, không số giả.
    html += `<div class="ld-card" style="margin-top:18px">
        <p style="margin:0;color:var(--color-sub);font-size:14.5px;font-weight:600">Năng lượng mỗi khẩu phần</p>
        <p style="color:var(--color-faint);margin:10px 0 0">Chưa tính được kcal cho đồ uống này — hệ thống không hiện số thay vì đoán.${d.kcal_note ? ' ' + esc(d.kcal_note) : ''}</p>`;
    if (d.sugar_added_g !== null && d.sugar_added_g !== undefined) html += `<p class="ld-note" style="margin-top:10px">Đường thêm: ${d.sugar_added_g}g</p>`;
    if (d.sugar_note) html += `<p class="ld-note" style="margin-top:10px">${esc(d.sugar_note)}</p>`;
    if (d.caffeine_level) html += `<p class="ld-note" style="margin-top:10px">${esc(cafName[d.caffeine_level] || d.caffeine_level)} — chỉ mức định tính, không ghi mg.</p>`;
    if (d.alcohol_pct_estimate) html += `<p class="ld-note" style="margin-top:10px">Độ cồn ước tính trước pha loãng: ~${d.alcohol_pct_estimate}%</p>`;
    if (d.alcohol_note) html += `<p class="ld-note" style="margin-top:10px">${esc(d.alcohol_note)}</p>`;
    html += `</div>`;

    // Kết hợp cần lưu ý — chỉ hiện khi có cặp, đúng mẫu trang món (không hiện thẻ rỗng).
    if (combinations && combinations.length) {
        html += `<div class="ld-card" style="margin-top:30px">
            <p class="ld-group-label" style="margin:0 0 6px">Kết hợp cần lưu ý với đồ uống này</p>
            ${combinations.map(c => `<p class="ld-note" style="margin:8px 0 0"><b>${esc(c.tier_label)}:</b> ${esc(c.condition_vi || '')}</p>`).join('')}
            <p class="ld-note" style="margin-top:10px">${esc(combinations_disclaimer)}</p>
        </div>`;
    }

    // Ghi chú văn hoá — cùng vị trí/kiểu với trang món (sau khối lưu ý, trước nguyên liệu).
    if (d.cultural_note) html += `<h2 class="ld-section-title">Ghi chú văn hoá</h2><div class="ld-card"><p style="margin:0;font-size:15.5px">${esc(d.cultural_note)}</p></div>`;

    if (d.kind === 'dong-goi') {
        // Đặc thù nước uống được giữ nguyên: đóng gói CHỈ khối "Thông tin nhãn", không bịa công thức.
        html += `<h2 class="ld-section-title">Thông tin nhãn</h2><div class="ld-card"><ul>`;
        if (d.packaging) html += li('Bao bì bán phổ biến: ' + d.packaging);
        if (d.serving_ml) html += li('Dung tích tham chiếu: ' + d.serving_ml + ' ml');
        const sr = rangeText(d.sugar_range_g, 'g');
        if (sr) html += li('Đường theo loại: ' + sr);
        const ar = rangeText(d.alcohol_pct_range, '%');
        if (ar) html += li('Cồn theo loại: ' + ar);
        if (d.caffeine_level) html += li(cafName[d.caffeine_level] || d.caffeine_level);
        if (d.brand_examples?.length) html += li('Ví dụ thương hiệu (chỉ ví dụ, không phải chứng thực): ' + d.brand_examples.join(', '));
        html += `</ul>${d.label_note ? `<p class="ld-note">${esc(d.label_note)}</p>` : ''}</div>`;
    } else {
        html += `<h2 class="ld-section-title">Nguyên liệu</h2><div class="ld-card"><ul>${(d.ingredients || []).map(ing).join('') || '<li>Chưa ghi nguyên liệu.</li>'}</ul>`;
        if (d.glass) html += `<p>Ly: ${esc(d.glass)}</p>`;
        if (d.technique) html += `<p>Kỹ thuật: ${esc(d.technique)}</p>`;
        if (d.ice) html += `<p>Đá: ${esc(d.ice)}</p>`;
        if (d.garnish?.length) html += `<p>Trang trí: ${esc(d.garnish.join(', '))}</p>`;
        if (d.serving_ml) html += `<p>Dung tích: ${d.serving_ml} ml</p>`;
        html += `</div>`;
        // Cách pha — đánh số tròn như khối "Cách làm" của trang món.
        html += `<h2 class="ld-section-title">Cách pha</h2><div class="ld-group">`;
        html += (d.recipe_steps || []).map((s, i) => `<div class="ld-row" style="align-items:flex-start">
            <span style="flex:none;width:26px;height:26px;border-radius:50%;background:var(--color-accent);color:#fff;font-weight:700;font-size:14px;display:inline-flex;align-items:center;justify-content:center;margin-top:1px">${i + 1}</span>
            <span class="grow">${esc(s)}</span></div>`).join('') || '<p style="color:var(--color-faint);padding:16px;margin:0">Chưa ghi cách pha.</p>';
        html += `</div>`;
        if (d.mocktail_counterpart) html += `<p class="ld-note" style="margin-top:10px">Bản không cồn tương đương: <a href="/do-uong/${esc(d.mocktail_counterpart.slug)}">${esc(d.mocktail_counterpart.name_vi)}</a></p>`;
    }
    if (d.recipe_sources?.length) html += `<h2 class="ld-section-title">Nguồn công thức</h2><div class="ld-card"><ul>${d.recipe_sources.map(li).join('')}</ul></div>`;
    if (d.source_note) html += `<p class="ld-note" style="margin-top:10px">${esc(d.source_note)}</p>`;
    document.getElementById('body').innerHTML = html;
});
</script>
@endpush
