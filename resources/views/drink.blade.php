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
const chev = '<svg class="ld-chev" width="8" height="14" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M1 1l6 6-6 6"/></svg>';
const heartSvg = '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"><path d="M12 20.5C7 16.5 3.8 13.3 3.8 9.6 3.8 7 5.7 5 8.1 5c1.6 0 3.1.9 3.9 2.3C12.8 5.9 14.3 5 15.9 5c2.4 0 4.3 2 4.3 4.6 0 3.7-3.2 6.9-8.2 10.9Z"/></svg>';
// Nhãn độ tin cậy và class capsule — cùng bảng với trang món.
const confLabel = { high: 'Tin cậy cao', medium: 'Tin cậy trung bình', low: 'Tin cậy thấp · số tham khảo' };
const confClass = { high: '', medium: 'medium', low: 'low' };
// Hàng nguyên liệu theo mẫu trang món (ld-row): có ingredient_slug thì là
// link tới trang nguyên liệu kèm chevron, không có thì là hàng thường —
// không tự gán nguyên liệu cho dòng chưa gắn slug.
function ingRow(x) {
    const inner = `<span class="grow">
        <span style="font-weight:600">${esc(x.name)}</span>
        <span class="sub ld-tnum">${x.quantity !== null && x.quantity !== undefined ? esc(x.quantity) + (x.unit ? ' ' + esc(x.unit) : '') : ''}</span>
    </span>`;
    return x.ingredient_slug
        ? `<a class="ld-row ld-row-compact" href="/nguyen-lieu/${esc(x.ingredient_slug)}">${inner}${chev}</a>`
        : `<div class="ld-row ld-row-compact">${inner}</div>`;
}
// Hàng nhãn/giá trị theo mẫu khối nguồn gốc của trang món.
function labelRow(label, value) {
    return `<div class="ld-row"><span class="grow" style="color:var(--color-sub)">${label}</span><span class="value" style="color:var(--color-ink)">${esc(value)}</span></div>`;
}
fetch('/api/drinks/' + slug).then(r => r.json()).then(({drink: d, allergen, combinations, combinations_disclaimer}) => {
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
    // Ảnh chính — cùng mẫu trang món (16/10, chú thích nguồn); chưa có ảnh
    // thì không hiện figure, không dùng placeholder làm ảnh thật.
    if (d.image) html += `<figure style="margin:18px 0 0">
        <img src="${esc(d.image.url)}" alt="${esc(d.name_vi)}" style="width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:18px;display:block" fetchpriority="high" decoding="async">
        <figcaption style="color:var(--color-faint);font-size:12.5px;margin-top:6px">${d.image.is_ai ? 'Ảnh minh họa' : 'Ảnh: ' + esc(d.image.attribution ?? '')}</figcaption>
    </figure>`;

    // Khối dinh dưỡng — đặt ngay sau đầu trang như trang món ("Năng lượng mỗi khẩu phần"
    // rồi mới tới các khối khác). Tính được thì hiện khoảng như trang món; chưa tính
    // được thì hiện đúng nhãn đó + lý do thiếu, không số giả.
    const nut = d.nutrition;
    html += `<div class="ld-card" style="margin-top:18px">
        <p style="margin:0;color:var(--color-sub);font-size:14.5px;font-weight:600">Năng lượng mỗi khẩu phần</p>
        ${nut && nut.status === 'computed'
            ? `<div style="display:flex;align-items:baseline;gap:9px;margin-top:4px;flex-wrap:wrap">
                    <span class="ld-kcal-so ld-tnum">${Math.round(nut.kcal_min)}–${Math.round(nut.kcal_max)}</span>
                    <span style="color:var(--color-sub);font-size:14.5px">kcal</span>
                    ${nut.confidence ? `<span class="ld-capsule ${confClass[nut.confidence] ?? ''}">${confLabel[nut.confidence] ?? ''}</span>` : ''}</div>
                <p class="ld-note" style="margin-top:10px">${esc(d.kcal_note || '')}</p>
                ${nut.missing?.length ? `<p class="ld-note" style="margin-top:10px">Chưa tính được một phần (đã nới cận trên theo tỷ lệ thiếu): ${nut.missing.map(esc).join('; ')}</p>` : ''}
                ${nut.negligible?.length ? `<p class="ld-note" style="margin-top:10px">Không tính (garnish không đáng kể, mỗi dòng ≈ 1 kcal): ${nut.negligible.map(esc).join('; ')}</p>` : ''}`
            : `<p style="color:var(--color-faint);margin:10px 0 0">Chưa tính được kcal cho đồ uống này — hệ thống không hiện số thay vì đoán.${d.kcal_note ? ' ' + esc(d.kcal_note) : ''}</p>`}`;
    if (d.sugar_added_g !== null && d.sugar_added_g !== undefined) html += `<p class="ld-note" style="margin-top:10px">Đường thêm: ${d.sugar_added_g}g</p>`;
    if (d.sugar_note) html += `<p class="ld-note" style="margin-top:10px">${esc(d.sugar_note)}</p>`;
    if (d.caffeine_level) html += `<p class="ld-note" style="margin-top:10px">${esc(cafName[d.caffeine_level] || d.caffeine_level)} — chỉ mức định tính, không ghi mg.</p>`;
    if (d.alcohol_pct_estimate) html += `<p class="ld-note" style="margin-top:10px">Độ cồn ước tính trước pha loãng: ~${d.alcohol_pct_estimate}%</p>`;
    if (d.alcohol_note) html += `<p class="ld-note" style="margin-top:10px">${esc(d.alcohol_note)}</p>`;
    html += `</div>`;

    // Hàng thao tác — cùng mẫu trang món (sau khối dinh dưỡng): yêu thích + phản hồi.
    html += `<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px">
        <button type="button" class="ld-btn ld-btn-gray ld-fav-btn" data-fav-drink="${d.id}" style="padding:11px 20px;font-size:15px"><span class="heart-ic">${heartSvg}</span>Yêu thích</button>
        <button type="button" class="ld-btn ld-btn-gray" data-feedback data-drink="${d.id}" data-context="${esc(d.name_vi)}" style="padding:11px 20px;font-size:15px">Gửi phản hồi</button>
    </div>`;

    // Dị ứng — cùng vị trí/kiểu với trang món (sau hàng thao tác, trước kết hợp):
    // chỉ đọc "không có dị ứng" khi độ phủ đủ; độ phủ một phần hoặc không
    // có dòng nào để suy thì hiện rõ "chưa đủ dữ kiện", không kết luận an toàn.
    {
        const alg = allergen ?? { display: [], linked: 0, total: 0, coverage: 'none' };
        const covNote = alg.coverage === 'partial'
            ? `<p class="ld-note" style="margin-top:10px">Chỉ tính từ ${alg.linked}/${alg.total} dòng nguyên liệu đã gắn hồ sơ — các dòng chưa gắn chưa được tính, nên danh sách này có thể thiếu.</p>`
            : '';
        html += `<div class="ld-card" style="margin-top:30px">
            <p class="ld-group-label" style="margin:0 0 6px">Dị ứng</p>
            ${alg.display.length
                ? `<div style="display:flex;flex-wrap:wrap;gap:6px">${alg.display.map(e => `<span class="ld-badge" style="background:var(--color-danger-tint);color:var(--color-danger)">Dị ứng: ${esc(e.label)}${e.certainty === 'possible' ? ' (có thể có)' : ''}</span>`).join('')}</div>
                ${covNote}
                <p class="ld-note" style="margin-top:10px">Nhãn dị ứng suy từ thành phần theo dữ liệu dự án biên soạn — có thể chưa đầy đủ. Nếu bạn dị ứng nặng, hãy tự kiểm tra thành phần trước khi uống. <a href="/mien-tru-y-te">Đọc Miễn trừ y tế</a>.</p>`
                : (alg.coverage === 'full'
                    ? `<p class="ld-note" style="margin:0">Dữ liệu hiện tại chưa ghi nhận chất gây dị ứng phổ biến trong đồ uống này. Điều này không phải cam kết an toàn tuyệt đối — nếu bạn bị dị ứng, hãy kiểm tra kỹ thành phần. <a href="/mien-tru-y-te">Đọc Miễn trừ y tế</a>.</p>`
                    : `<p class="ld-note" style="margin:0">Chưa đủ dữ kiện để kết luận dị ứng: ${alg.total === 0 ? 'đồ uống này không có dòng nguyên liệu nào để suy' : `chỉ ${alg.linked}/${alg.total} dòng nguyên liệu đã gắn hồ sơ`}. Đừng đọc đây là "không có dị ứng" — nếu bạn bị dị ứng, hãy kiểm tra kỹ thành phần. <a href="/mien-tru-y-te">Đọc Miễn trừ y tế</a>.</p>`)}
        </div>`;
    }

    // Kết hợp cần lưu ý — chỉ hiện khi có cặp, đúng mẫu trang món (không hiện thẻ rỗng):
    // mỗi cặp ghi cả hai phía + mức + điều kiện/khoa học, kèm cơ chế, nguồn truyền
    // thống và nguồn tham khảo khi có — không rút gọn còn mức + điều kiện như trước.
    if (combinations && combinations.length) {
        html += `<div class="ld-card" style="margin-top:30px">
            <p class="ld-group-label" style="margin:0 0 6px">Kết hợp cần lưu ý với đồ uống này</p>
            ${combinations.map(c => `<div style="padding:10px 0;border-bottom:1px solid var(--color-border)">
                <div style="display:flex;gap:8px;align-items:flex-start;flex-wrap:wrap">
                    <span class="ld-badge" style="background:var(--color-danger-tint);color:var(--color-danger)">${esc(c.tier_label)}</span>
                    <span style="font-weight:700;font-size:15.5px">${esc(c.side_a_label)} + ${esc(c.side_b_label)}</span>
                </div>
                <p class="ld-note" style="margin:8px 0 0"><b>${esc(c.science_label || '')}.</b> ${esc(c.condition_vi || '')}</p>
                ${c.mechanism_vi ? `<p class="ld-note" style="margin:6px 0 0">Cơ chế: ${esc(c.mechanism_vi)}</p>` : ''}
                ${c.traditional_source ? `<p class="ld-note" style="margin:6px 0 0">Nguồn truyền thống: ${esc(c.traditional_source)}</p>` : ''}
                ${c.sources?.length ? `<p class="ld-note" style="margin:6px 0 0">${c.sources.map(s => s.url ? `<a href="${esc(s.url)}" target="_blank" rel="noopener">${esc(s.note || s.url)}</a>` : esc(s.note || '')).join(' · ')}</p>` : ''}
            </div>`).join('')}
            <p class="ld-note" style="margin-top:10px">${esc(combinations_disclaimer)}</p>
        </div>`;
    }

    // Ghi chú văn hoá — cùng vị trí/kiểu với trang món (sau khối lưu ý, trước nguyên liệu).
    if (d.cultural_note) html += `<h2 class="ld-section-title">Ghi chú văn hoá</h2><div class="ld-card"><p style="margin:0;font-size:15.5px">${esc(d.cultural_note)}</p></div>`;

    if (d.kind === 'dong-goi') {
        // Đặc thù nước uống được giữ nguyên: đóng gói CHỈ khối "Thông tin nhãn", không bịa công thức.
        // Chỉ đổi hình thức từ danh sách ul sang hàng ld-group như khối nguồn gốc của trang món.
        let labelHtml = `<h2 class="ld-section-title">Thông tin nhãn</h2><div class="ld-group">`;
        if (d.packaging) labelHtml += labelRow('Bao bì bán phổ biến', d.packaging);
        if (d.serving_ml) labelHtml += labelRow('Dung tích tham chiếu', d.serving_ml + ' ml');
        const sr = rangeText(d.sugar_range_g, 'g');
        if (sr) labelHtml += labelRow('Đường theo loại', sr);
        const ar = rangeText(d.alcohol_pct_range, '%');
        if (ar) labelHtml += labelRow('Cồn theo loại', ar);
        if (d.caffeine_level) labelHtml += labelRow('Caffeine', cafName[d.caffeine_level] || d.caffeine_level);
        if (d.brand_examples?.length) labelHtml += labelRow('Ví dụ thương hiệu (chỉ ví dụ, không phải chứng thực)', d.brand_examples.join(', '));
        labelHtml += `</div>`;
        if (d.label_note) labelHtml += `<p class="ld-note" style="margin-top:10px">${esc(d.label_note)}</p>`;
        html += labelHtml;
    } else {
        // Nguyên liệu thành hàng ld-group như trang món, có link khi có ingredient_slug.
        // Ly/kỹ thuật/đá/trang trí/dung tích giữ nguyên nội dung, đổi sang hàng nhãn/giá trị.
        html += `<h2 class="ld-section-title">Nguyên liệu</h2><div class="ld-group">${(d.ingredients || []).map(ingRow).join('') || '<p style="color:var(--color-faint);padding:16px;margin:0">Chưa ghi nguyên liệu.</p>'}</div>`;
        let serveHtml = `<div class="ld-group" style="margin-top:12px">`;
        if (d.glass) serveHtml += labelRow('Ly', d.glass);
        if (d.technique) serveHtml += labelRow('Kỹ thuật', d.technique);
        if (d.ice) serveHtml += labelRow('Đá', d.ice);
        if (d.garnish?.length) serveHtml += labelRow('Trang trí', d.garnish.join(', '));
        if (d.serving_ml) serveHtml += labelRow('Dung tích', d.serving_ml + ' ml');
        serveHtml += `</div>`;
        if (d.glass || d.technique || d.ice || d.garnish?.length || d.serving_ml) html += serveHtml;
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
    // Cảnh báo chưa duyệt — cùng mẫu trang món (đặt cuối trang, chỉ hiện khi có cờ).
    if (d.needs_review) html += `<div class="ld-warn" style="margin-top:30px"><span><b>Đang biên soạn:</b> Đồ uống này đang trong giai đoạn biên soạn dữ liệu — thông tin có thể được cập nhật sau khi duyệt.</span></div>`;
    document.getElementById('body').innerHTML = html;
});
</script>
@endpush
