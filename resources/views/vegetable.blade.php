@extends('layouts.app')

@section('title', 'Rau theo mùa — Lắc Đói')

@section('content')
    <p style="margin-top:14px"><a href="/rau-mua" style="color:var(--color-link);text-decoration:none">← Rau theo mùa</a></p>
    <div id="detail"></div>
@endsection

@push('scripts')
<script>
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
function ranges(months) {
    if (!months || !months.length) return '—';
    const sorted = [...months].sort((a, b) => a - b);
    const parts = [];
    let start = sorted[0], prev = sorted[0];
    for (let i = 1; i <= sorted.length; i++) {
        const m = sorted[i];
        if (m === prev + 1) { prev = m; continue; }
        parts.push(start === prev ? `${start}` : `${start}–${prev}`);
        start = m; prev = m;
    }
    if (parts.length > 1 && parts[0].match(/^1–/) && parts[parts.length - 1].match(/–12$/)) {
        const first = parts.shift().split('–')[1];
        const last = parts.pop().split('–')[0];
        parts.push(`${last}–${first}`);
    }
    return parts.join(', ');
}
const groupName = { 'rau-la': 'Rau lá', 'rau-cu': 'Rau củ', 'rau-qua': 'Rau quả', 'dau-do': 'Đậu–đỗ rau, ngô, giá', 'rau-thom-an-kem': 'Rau thơm ăn kèm', 'dang-che-bien': 'Dạng chế biến' };
const supplyName = { 'trong-nuoc': 'Trồng trong nước', 'da-lat': 'Đà Lạt', 'nhap-khau': 'Nhập khẩu', 'ca-hai': 'Cả hai (tươi trong nước + đông lạnh nhập)' };
const stateName = { tuoi: 'Tươi', muoi: 'Muối', kho: 'Khô', chua: 'Chua' };
const slug = window.location.pathname.split('/').pop();
async function load() {
    const res = await fetch('/api/vegetables/' + encodeURIComponent(slug));
    if (!res.ok) { document.getElementById('detail').innerHTML = '<p class="ld-note" style="margin-top:20px">Không tìm thấy rau này.</p>'; return; }
    const { vegetable: v, ingredient: ing, combinations, combinations_disclaimer } = await res.json();
    const img = v.image ? `<img src="${esc(v.image.url)}" alt="" style="width:100%;max-width:520px;aspect-ratio:4/3;object-fit:cover;border-radius:16px">` : '';
    const kcal = v.kcal_per_100g !== null && v.kcal_per_100g !== undefined
        ? `${Math.round(v.kcal_per_100g)} kcal/100g${v.kcal_confidence ? ` (độ tin cậy: ${esc(v.kcal_confidence)})` : ''}`
        : 'Chưa đủ dữ liệu — repo chưa có số cho rau này, không mượn số loài/giống khác.';
    const regionRows = [['bac', 'Miền Bắc'], ['trung', 'Miền Trung'], ['nam', 'Miền Nam']].map(([r, label]) => {
        const months = v['months_' + r];
        const note = (v.region_notes && v.region_notes[r]) || '';
        const text = months === null || months === undefined ? 'Chưa có lịch chợ riêng — không gán tháng' : (note ? esc(note) : `Có bán tháng ${ranges(months)}`);
        return `<div class="ld-row"><span class="grow">${label}</span><span class="value" style="text-align:right">${text}</span></div>`;
    }).join('');
    const comboHtml = combinations.length ? combinations.map(c => `
        <div class="ld-card" style="margin-top:10px">
            <p style="margin:0;font-weight:700">${esc(c.side_a_label)} × ${esc(c.side_b_label)} — ${esc(c.tier_label)}</p>
            <p style="margin:6px 0 0;font-size:14.5px;line-height:1.6">${esc(c.condition_vi)}</p>
            <p class="ld-note" style="margin-top:6px">${esc(c.science_label)}</p>
        </div>`).join('') : '<p class="ld-note">Không có cặp kết hợp đã thẩm định nào chạm tới rau này trong kho.</p>';
    document.getElementById('detail').innerHTML = `
        <h1 class="ld-title" style="margin-top:10px">${esc(v.name_vi)}</h1>
        <p class="ld-subtitle">${esc(groupName[v.group] || v.group)} · ${esc(supplyName[v.supply] || v.supply)} · ${esc(stateName[v.state] || v.state)}${v.aliases && v.aliases.length ? ` · Tên khác: ${v.aliases.map(esc).join(', ')}` : ''}</p>
        ${img}
        <div class="ld-card" style="margin-top:16px">
            <div class="ld-row"><span class="grow">Năng lượng (100g)</span><span class="value">${kcal}</span></div>
            ${v.uncertain ? `<p class="ld-note" style="margin-top:8px">Tháng chưa chắc chắn: ${esc(v.uncertain_note || '')}</p>` : ''}
        </div>
        <details open style="margin-top:16px">
            <summary style="font-weight:700;font-size:17px;cursor:pointer">Mùa vụ theo vùng</summary>
            <div class="ld-card" style="margin-top:10px">
                ${regionRows}
                <div class="ld-row"><span class="grow">Chính vụ</span><span class="value">${v.main_season_months && v.main_season_months.length ? 'Tháng ' + ranges(v.main_season_months) : 'Không gán chính vụ tháng (quanh năm hoặc chưa chắc chắn)'}</span></div>
                <div class="ld-row"><span class="grow">Tốt nhất</span><span class="value">${v.best_months && v.best_months.length ? 'Tháng ' + ranges(v.best_months) : 'Chưa xác định — không bịa tháng tốt nhất'}</span></div>
                <p class="ld-note" style="margin-top:8px">Tháng là tháng có bán trên thị trường, không đồng nghĩa thu hoạch tại chỗ ở miền đó. Rau miền Nam mùa đông chủ yếu là hàng Đà Lạt.</p>
            </div>
        </details>
        <details style="margin-top:16px">
            <summary style="font-weight:700;font-size:17px;cursor:pointer">Chọn &amp; bảo quản</summary>
            <div class="ld-card" style="margin-top:10px">
                ${v.selection_note ? `<p style="margin:0;line-height:1.6">Chọn: ${esc(v.selection_note)}</p>` : ''}
                ${v.storage_note ? `<p style="margin:8px 0 0;line-height:1.6">Bảo quản: ${esc(v.storage_note)}</p>` : ''}
                ${!v.selection_note && !v.storage_note ? '<p class="ld-note" style="margin:0">Chưa có ghi chú chọn/bảo quản cho rau này.</p>' : ''}
            </div>
        </details>
        <details style="margin-top:16px">
            <summary style="font-weight:700;font-size:17px;cursor:pointer">Cách nấu &amp; lưu ý an toàn</summary>
            <div class="ld-card" style="margin-top:10px">
                ${v.cooking_note ? `<p style="margin:0;line-height:1.6">${esc(v.cooking_note)}</p>` : ''}
                ${v.safety_note ? `<p style="margin:8px 0 0;line-height:1.6">Lưu ý: ${esc(v.safety_note)}</p>` : ''}
                <p class="ld-note" style="margin-top:8px">Các lưu ý trên là an toàn sơ chế/nấu chín và vệ sinh, không phải cặp kiêng kỵ — chỉ cặp trong khối “Kết hợp cần lưu ý” dưới đây mới là cặp đã thẩm định.</p>
            </div>
        </details>
        <details style="margin-top:16px">
            <summary style="font-weight:700;font-size:17px;cursor:pointer">Kết hợp cần lưu ý</summary>
            <div style="margin-top:4px">
                <p class="ld-note">${esc(combinations_disclaimer)}</p>
                ${comboHtml}
            </div>
        </details>
        <details style="margin-top:16px">
            <summary style="font-weight:700;font-size:17px;cursor:pointer">Nguồn &amp; độ tin cậy</summary>
            <div class="ld-card" style="margin-top:10px">
                <p class="ld-note" style="margin:0">${esc(v.source_note || '')}</p>
                ${ing && ing.nutrition && ing.nutrition.unverified ? '<p class="ld-note" style="margin-top:8px">Số dinh dưỡng từ nguồn thứ cấp — chưa kiểm định.</p>' : ''}
                ${v.fresh_counterpart_slug ? `<p style="margin:8px 0 0">Đối tác tươi: <a href="/rau-mua/${esc(v.fresh_counterpart_slug)}" style="color:var(--color-link)">${esc(v.fresh_counterpart_slug)}</a></p>` : ''}
            </div>
        </details>`;
}
load();
</script>
@endpush
