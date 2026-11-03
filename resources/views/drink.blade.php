@extends('layouts.app')

@section('title', 'Nước uống — Lắc Đói')

@section('content')
    <p class="ld-note" style="margin-top:14px"><a href="/do-uong">← Thư viện nước uống</a></p>
    <div id="body" style="margin-top:16px"></div>
@endsection

@push('scripts')
<script>
const slug = location.pathname.split('/').pop();
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
function li(x) { return `<li>${esc(x)}</li>`; }
function ing(x) { return `<li>${esc(x.name)}${x.quantity !== null && x.quantity !== undefined ? ` — ${x.quantity}${x.unit ? ' ' + x.unit : ''}` : ''}</li>`; }
fetch('/api/drinks/' + slug).then(r => r.json()).then(({drink: d, combinations, combinations_disclaimer}) => {
    const groupName = { tra: 'Trà', 'ca-phe': 'Cà phê', 'sinh-to': 'Sinh tố', 'nuoc-ep': 'Nước ép', 'giai-khat': 'Giải khát', 'cocktail-co-con': 'Cocktail có cồn', mocktail: 'Mocktail', 'nuoc-ngot-co-ga': 'Nước ngọt có ga', 'tra-dong-chai': 'Trà đóng chai', 'ca-phe-dong-goi': 'Cà phê đóng gói', 'nuoc-ep-dong-hop': 'Nước ép đóng hộp', 'sua-dong-hop': 'Sữa đóng hộp', 'tang-luc-dien-giai': 'Tăng lực / điện giải', 'nuoc-dong-goi-khac': 'Nước đóng gói khác', bia: 'Bia', vang: 'Vang', ruou: 'Rượu', 'nuoc-giai-khat-dong-goi': 'Nước giải khát đóng gói' };
    const cafName = { khong: 'Không caffeine', thap: 'Caffeine thấp', 'trung-binh': 'Caffeine trung bình', cao: 'Caffeine cao' };
    let html = `<h1 class="ld-title">${esc(d.name_vi)}${d.uncertain ? ' <span style="font-size:16px;color:var(--color-faint)">(chưa chắc chắn)</span>' : ''}</h1>`;
    html += `<p class="ld-subtitle">${esc(groupName[d.drink_group] || d.drink_group)} · ${d.kind === 'pha-che' ? 'Pha chế' : 'Đóng gói'}${d.temperature ? ' · ' + (d.temperature === 'nong' ? 'Nóng' : d.temperature === 'lanh' ? 'Lạnh' : 'Nóng hoặc lạnh') : ''}</p>`;
    if (d.alcohol) html += `<div class="ld-card" style="background:#fee2e2;border-color:#fecaca"><b>18+</b> — Đồ uống có cồn. Không dùng khi lái xe và không dùng cho người dưới 18 tuổi.</div>`;
    if (d.uncertain && d.uncertain_note) html += `<div class="ld-card"><b>Chưa chắc chắn:</b> ${esc(d.uncertain_note)}</div>`;
    if (d.cultural_note) html += `<p>${esc(d.cultural_note)}</p>`;

    if (d.kind === 'dong-goi') {
        html += `<h2 class="ld-subtitle" style="margin-top:20px">Thông tin nhãn</h2><div class="ld-card"><ul>`;
        if (d.packaging) html += li('Bao bì bán phổ biến: ' + d.packaging);
        if (d.serving_ml) html += li('Dung tích tham chiếu: ' + d.serving_ml + ' ml');
        if (d.sugar_range_g) html += li(`Đường theo loại: ${d.sugar_range_g.min ?? ''}–${d.sugar_range_g.max ?? ''}g`);
        if (d.alcohol_pct_range) html += li(`Cồn theo loại: ${d.alcohol_pct_range.min ?? ''}–${d.alcohol_pct_range.max ?? ''}%`);
        if (d.caffeine_level) html += li(cafName[d.caffeine_level] || d.caffeine_level);
        if (d.brand_examples?.length) html += li('Ví dụ thương hiệu (chỉ ví dụ, không phải chứng thực): ' + d.brand_examples.join(', '));
        html += `</ul>${d.label_note ? `<p class="ld-note">${esc(d.label_note)}</p>` : ''}</div>`;
    } else {
        html += `<h2 class="ld-subtitle" style="margin-top:20px">Nguyên liệu</h2><div class="ld-card"><ul>${(d.ingredients || []).map(ing).join('') || '<li>Chưa ghi nguyên liệu.</li>'}</ul>`;
        if (d.glass) html += `<p>Ly: ${esc(d.glass)}</p>`;
        if (d.technique) html += `<p>Kỹ thuật: ${esc(d.technique)}</p>`;
        if (d.ice) html += `<p>Đá: ${esc(d.ice)}</p>`;
        if (d.garnish?.length) html += `<p>Trang trí: ${esc(d.garnish.join(', '))}</p>`;
        if (d.serving_ml) html += `<p>Dung tích: ${d.serving_ml} ml</p>`;
        html += `</div>`;
        html += `<h2 class="ld-subtitle" style="margin-top:20px">Cách pha</h2><div class="ld-card"><ol>${(d.recipe_steps || []).map(li).join('') || '<li>Chưa ghi cách pha.</li>'}</ol></div>`;
        if (d.mocktail_counterpart) html += `<p class="ld-note">Bản không cồn tương đương: <a href="/do-uong/${esc(d.mocktail_counterpart.slug)}">${esc(d.mocktail_counterpart.name_vi)}</a></p>`;
    }
    html += `<h2 class="ld-subtitle" style="margin-top:20px">Dinh dưỡng</h2><div class="ld-card"><p><b>Chưa tính được kcal.</b> ${esc(d.kcal_note)}</p>`;
    if (d.sugar_added_g !== null && d.sugar_added_g !== undefined) html += `<p>Đường thêm: ${d.sugar_added_g}g</p>`;
    if (d.sugar_note) html += `<p>${esc(d.sugar_note)}</p>`;
    if (d.caffeine_level) html += `<p>${esc(cafName[d.caffeine_level] || d.caffeine_level)} — chỉ mức định tính, không ghi mg.</p>`;
    if (d.alcohol_pct_estimate) html += `<p>Độ cồn ước tính trước pha loãng: ~${d.alcohol_pct_estimate}%</p>`;
    if (d.alcohol_note) html += `<p>${esc(d.alcohol_note)}</p>`;
    html += `</div>`;

    if (combinations && combinations.length) {
        html += `<h2 class="ld-subtitle" style="margin-top:20px">Kết hợp cần lưu ý</h2><div class="ld-card"><p class="ld-note">${esc(combinations_disclaimer)}</p><ul>`;
        combinations.forEach(c => { html += `<li><b>${esc(c.tier_label)}:</b> ${esc(c.condition_vi || '')}</li>`; });
        html += `</ul></div>`;
    } else {
        html += `<h2 class="ld-subtitle" style="margin-top:20px">Kết hợp cần lưu ý</h2><div class="ld-card"><p class="ld-note">Không có cặp kết hợp đã thẩm định nào chạm tới đồ uống này trong kho.</p></div>`;
    }
    if (d.recipe_sources?.length) html += `<h2 class="ld-subtitle" style="margin-top:20px">Nguồn công thức</h2><div class="ld-card"><ul>${d.recipe_sources.map(li).join('')}</ul></div>`;
    if (d.source_note) html += `<p class="ld-note">${esc(d.source_note)}</p>`;
    document.getElementById('body').innerHTML = html;
});
</script>
@endpush
