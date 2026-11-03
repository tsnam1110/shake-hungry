@extends('layouts.app')

@section('title', 'Rau theo mùa — Lắc Đói')
@section('page_class', 'ld-page-wide')

@section('content')
    <h1 class="ld-title" style="margin-top:14px">Rau theo mùa</h1>
    <p class="ld-subtitle">Tháng nào nên ăn rau gì — theo tháng có bán trên thị trường, chia theo vùng. Rau miền Nam mùa đông chủ yếu là <b>hàng Đà Lạt</b>, không phải trồng tại đồng bằng Nam. Hàng nhập khẩu thuần túy ghi rõ <b>nhập khẩu quanh năm, không có mùa vụ Việt Nam</b>. Không có giá — giá cần đợt thu thập riêng.</p>

    <div class="ld-card" style="margin-top:18px">
        <div class="ld-search" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
            <input id="q" type="search" placeholder="Tìm rau… (cả tên gọi khác, VD: bạc hà, rau ngổ)" aria-label="Tìm rau">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" style="margin-top:12px">
            <div><label class="ld-field-label" for="month">Tháng</label><select id="month" class="ld-select"></select></div>
            <div><label class="ld-field-label" for="region">Vùng</label>
                <select id="region" class="ld-select">
                    <option value="">Tất cả các vùng</option>
                    <option value="bac">Miền Bắc</option>
                    <option value="trung">Miền Trung</option>
                    <option value="nam">Miền Nam</option>
                </select></div>
            <div><label class="ld-field-label" for="group">Nhóm rau</label>
                <select id="group" class="ld-select">
                    <option value="">Mọi nhóm rau</option>
                    <option value="rau-la">Rau lá</option>
                    <option value="rau-cu">Rau củ</option>
                    <option value="rau-qua">Rau quả</option>
                    <option value="dau-do">Đậu–đỗ rau, ngô, giá</option>
                    <option value="rau-thom-an-kem">Rau thơm ăn kèm</option>
                    <option value="dang-che-bien">Dạng chế biến (muối/khô/chua)</option>
                </select></div>
            <div><label class="ld-field-label" for="supply">Nguồn cung</label>
                <select id="supply" class="ld-select">
                    <option value="">Mọi nguồn cung</option>
                    <option value="trong-nuoc">Trồng trong nước</option>
                    <option value="da-lat">Đà Lạt</option>
                    <option value="nhap-khau">Nhập khẩu</option>
                    <option value="ca-hai">Cả hai (tươi trong nước + đông lạnh nhập)</option>
                </select></div>
            <div><label class="ld-field-label" for="state">Trạng thái</label>
                <select id="state" class="ld-select">
                    <option value="">Mọi trạng thái</option>
                    <option value="tuoi">Tươi</option>
                    <option value="muoi">Muối</option>
                    <option value="kho">Khô</option>
                    <option value="chua">Chua</option>
                </select></div>
        </div>
        <div style="margin-top:6px">
            <div class="ld-row" style="padding-left:2px;padding-right:2px">
                <span class="grow">Đang vào mùa tháng này</span>
                <button class="ld-switch" type="button" role="switch" aria-checked="true" id="only-season" aria-label="Đang vào mùa tháng này"></button>
            </div>
            <p class="ld-note" style="margin-top:4px">“Đang vào mùa” ở đây nghĩa là có bán trong tháng đã chọn; dạng chế biến (muối/khô/chua) bán quanh năm nên luôn hiện khi bật.</p>
        </div>
    </div>

    <div id="list" class="ld-grid" style="margin-top:16px"></div>
    <p id="empty" class="ld-note" style="margin-top:20px" hidden>Không có rau nào khớp bộ lọc.</p>
@endsection

@push('scripts')
<script>
const now = new Date();
const monthSel = document.getElementById('month');
monthSel.innerHTML = '<option value="">Tất cả các tháng</option>' + Array.from({length: 12}, (_, i) =>
    `<option value="${i + 1}" ${i + 1 === now.getMonth() + 1 ? 'selected' : ''}>Tháng ${i + 1}</option>`).join('');
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
function isOn(id) { return document.getElementById(id).getAttribute('aria-checked') === 'true'; }
function ranges(months) {
    if (!months || !months.length) return '—';
    const sorted = [...months].sort((a, b) => a - b);
    // Gộp thành đoạn, kể cả đoạn quấn qua năm (11–2).
    const parts = [];
    let start = sorted[0], prev = sorted[0];
    for (let i = 1; i <= sorted.length; i++) {
        const m = sorted[i];
        if (m === prev + 1) { prev = m; continue; }
        parts.push(start === prev ? `${start}` : `${start}–${prev}`);
        start = m; prev = m;
    }
    // Đoạn quấn năm: nếu có cả 1–… và …–12 thì gộp đầu–cuối.
    if (parts.length > 1 && parts[0].match(/^1–/) && parts[parts.length - 1].match(/–12$/)) {
        const first = parts.shift().split('–')[1];
        const last = parts.pop().split('–')[0];
        parts.push(`${last}–${first}`);
    }
    return parts.join(', ');
}
function badge(v, month) {
    if (v.state !== 'tuoi') return 'Bán quanh năm';
    if (v.supply === 'nhap-khau') return 'Nhập khẩu quanh năm';
    if (v.main_season_months && month && v.main_season_months.includes(month)) return `Chính vụ tháng ${month}`;
    if (v.main_season_months && v.main_season_months.length) return `Chính vụ ${ranges(v.main_season_months)}`;
    if (v.supply === 'da-lat') return 'Hàng Đà Lạt';
    return 'Có bán';
}
function regionLine(v, region) {
    const names = { bac: 'Bắc', trung: 'Trung', nam: 'Nam' };
    const show = region ? [region] : ['bac', 'trung', 'nam'];
    return show.map(r => {
        const months = v['months_' + r];
        const note = (v.region_notes && v.region_notes[r]) || '';
        if (months === null || months === undefined) return `${names[r]}: chưa có lịch riêng`;
        if (note) return `${names[r]}: ${esc(note)}`;
        return `${names[r]}: ${ranges(months)}`;
    }).join(' · ');
}
function card(v, month, region) {
    const img = v.image ? `<img src="${esc(v.image.url)}" alt="" loading="lazy" style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:12px">` : '';
    const kcal = v.kcal_per_100g !== null && v.kcal_per_100g !== undefined
        ? `${Math.round(v.kcal_per_100g)} kcal/100g${v.kcal_confidence ? ` <span style="color:var(--color-faint)">(${esc(v.kcal_confidence)})</span>` : ''}`
        : 'Chưa đủ dữ liệu';
    const unc = v.uncertain ? ' <span style="color:var(--color-faint)">· tháng chưa chắc chắn</span>' : '';
    return `<a class="ld-card" href="/rau-mua/${esc(v.slug)}" style="text-decoration:none;color:inherit;display:block">
        ${img}
        <div style="margin-top:10px"><span class="ld-chip active" style="cursor:default">${esc(badge(v, month))}</span></div>
        <h2 style="margin:8px 0 0;font-size:17px;font-weight:700">${esc(v.name_vi)}</h2>
        <p style="margin:4px 0 0;font-size:14px;color:var(--color-sub)">${kcal}${unc}</p>
        <p style="margin:4px 0 0;font-size:13.5px;color:var(--color-faint)">${regionLine(v, region)}</p>
    </a>`;
}
async function load() {
    const month = monthSel.value;
    const region = document.getElementById('region').value;
    const params = new URLSearchParams();
    if (document.getElementById('q').value.trim()) params.set('q', document.getElementById('q').value.trim());
    if (document.getElementById('group').value) params.set('group', document.getElementById('group').value);
    if (document.getElementById('supply').value) params.set('supply', document.getElementById('supply').value);
    if (document.getElementById('state').value) params.set('state', document.getElementById('state').value);
    // Công tắc bật (mặc định): lọc theo tháng đã chọn. Tắt đi: xem toàn danh sách, tháng chỉ dùng để tính badge chính vụ trên thẻ.
    if (isOn('only-season') && month) { params.set('month', month); if (region) params.set('region', region); }
    const res = await fetch('/api/vegetables?' + params.toString());
    const data = await res.json();
    const items = data.data || [];
    document.getElementById('list').innerHTML = items.map(v => card(v, month ? +month : null, region)).join('');
    document.getElementById('empty').hidden = items.length > 0;
}
['q', 'month', 'region', 'group', 'supply', 'state'].forEach(id =>
    document.getElementById(id).addEventListener(id === 'q' ? 'input' : 'change', load));
document.getElementById('only-season').addEventListener('click', () => {
    const b = document.getElementById('only-season');
    b.setAttribute('aria-checked', isOn('only-season') ? 'false' : 'true');
    load();
});
load();
</script>
@endpush
