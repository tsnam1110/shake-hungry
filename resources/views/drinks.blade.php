@extends('layouts.app')

@section('title', 'Thư viện nước uống — Lắc Đói')
@section('page_class', 'ld-page-wide')

@section('content')
    <h1 class="ld-title" style="margin-top:14px">Thư viện nước uống</h1>
    <p class="ld-subtitle">Nội dung chi tiết như món ăn: có công thức thì ghi công thức, có cách chế biến thì ghi cả cách chế biến. Có hai loại: <b>pha chế</b> (có công thức) và <b>đóng gói</b> (chỉ khối “Thông tin nhãn”, không bịa công thức). Cocktail có badge 18+ ngay trên thẻ và <b>không</b> được gợi ý tự động trong Lắc món. Caffeine chỉ ghi mức định tính, không ghi mg.</p>

    <div class="ld-card" style="margin-top:18px">
        <div class="ld-search" role="search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
            <input id="q" type="search" placeholder="Tìm đồ uống…" aria-label="Tìm đồ uống">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" style="margin-top:12px">
            <div><label class="ld-field-label" for="group">Nhóm</label>
                <select id="group" class="ld-select">
                    <option value="">Mọi nhóm</option>
                    <option value="tra">Trà</option>
                    <option value="ca-phe">Cà phê</option>
                    <option value="sinh-to">Sinh tố</option>
                    <option value="nuoc-ep">Nước ép</option>
                    <option value="giai-khat">Giải khát</option>
                    <option value="cocktail-co-con">Cocktail có cồn</option>
                    <option value="mocktail">Mocktail</option>
                    <option value="nuoc-ngot-co-ga">Nước ngọt có ga</option>
                    <option value="tra-dong-chai">Trà đóng chai</option>
                    <option value="ca-phe-dong-goi">Cà phê đóng gói</option>
                    <option value="nuoc-ep-dong-hop">Nước ép đóng hộp</option>
                    <option value="sua-dong-hop">Sữa đóng hộp</option>
                    <option value="tang-luc-dien-giai">Tăng lực / điện giải</option>
                    <option value="nuoc-dong-goi-khac">Nước đóng gói khác</option>
                    <option value="bia">Bia</option>
                    <option value="vang">Vang</option>
                    <option value="ruou">Rượu</option>
                    <option value="nuoc-giai-khat-dong-goi">Nước giải khát đóng gói khác</option>
                </select></div>
            <div><label class="ld-field-label" for="kind">Loại</label>
                <select id="kind" class="ld-select">
                    <option value="">Cả hai loại</option>
                    <option value="pha-che">Pha chế (có công thức)</option>
                    <option value="dong-goi">Đóng gói (thông tin nhãn)</option>
                </select></div>
            <div><label class="ld-field-label" for="temperature">Nóng / lạnh</label>
                <select id="temperature" class="ld-select">
                    <option value="">Cả hai</option>
                    <option value="nong">Nóng</option>
                    <option value="lanh">Lạnh</option>
                    <option value="ca-hai">Cả hai</option>
                </select></div>
            <div><label class="ld-field-label" for="alcohol">Cồn</label>
                <select id="alcohol" class="ld-select">
                    <option value="">Cả có cồn và không cồn</option>
                    <option value="0">Không cồn</option>
                    <option value="1">Có cồn (18+)</option>
                </select></div>
            <div><label class="ld-field-label" for="caffeine_level">Caffeine</label>
                <select id="caffeine_level" class="ld-select">
                    <option value="">Mọi mức</option>
                    <option value="khong">Không</option>
                    <option value="thap">Thấp</option>
                    <option value="trung-binh">Trung bình</option>
                    <option value="cao">Cao</option>
                </select></div>
            <div><label class="ld-field-label" for="sweetness">Độ ngọt (đường thêm / khoảng đường)</label>
                <select id="sweetness" class="ld-select">
                    <option value="">Mọi mức</option>
                    <option value="khong">Ít ngọt (≤ 5g)</option>
                    <option value="vua">Vừa (6–25g)</option>
                    <option value="cao">Ngọt (trên 25g)</option>
                </select></div>
        </div>
        <div style="margin-top:6px">
            <div class="ld-row" style="padding-left:2px;padding-right:2px">
                <span class="grow">Không cồn trước (ẩn cocktail trừ khi lọc có cồn)</span>
                <button class="ld-switch" type="button" role="switch" aria-checked="true" id="no-alcohol-first" aria-label="Không cồn trước"></button>
            </div>
        </div>
    </div>

    <div id="list" class="ld-grid" style="margin-top:16px"></div>
    <p id="empty" class="ld-note" style="margin-top:20px" hidden>Không có đồ uống nào khớp bộ lọc.</p>
@endsection

@push('scripts')
<script>
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
function isOn(id) { return document.getElementById(id).getAttribute('aria-checked') === 'true'; }
const groupName = { tra: 'Trà', 'ca-phe': 'Cà phê', 'sinh-to': 'Sinh tố', 'nuoc-ep': 'Nước ép', 'giai-khat': 'Giải khát', 'cocktail-co-con': 'Cocktail', mocktail: 'Mocktail', 'nuoc-ngot-co-ga': 'Nước ngọt có ga', 'tra-dong-chai': 'Trà đóng chai', 'ca-phe-dong-goi': 'Cà phê đóng gói', 'nuoc-ep-dong-hop': 'Nước ép đóng hộp', 'sua-dong-hop': 'Sữa đóng hộp', 'tang-luc-dien-giai': 'Tăng lực / điện giải', 'nuoc-dong-goi-khac': 'Nước đóng gói khác', bia: 'Bia', vang: 'Vang', ruou: 'Rượu', 'nuoc-giai-khat-dong-goi': 'Nước giải khát đóng gói' };
const cafName = { khong: 'Không caffeine', thap: 'Caffeine thấp', 'trung-binh': 'Caffeine trung bình', cao: 'Caffeine cao' };
function card(d) {
    const badges = [];
    badges.push(`<span class="ld-chip active" style="cursor:default">${esc(groupName[d.drink_group] || d.drink_group)}</span>`);
    if (d.alcohol) badges.push('<span class="ld-chip" style="cursor:default;background:#fee2e2;color:#991b1b">18+</span>');
    if (d.kind === 'dong-goi') badges.push('<span class="ld-chip" style="cursor:default">Sản phẩm đóng gói</span>');
    let line = '';
    if (d.kind === 'pha-che') {
        line = `${d.serving_ml ? d.serving_ml + ' ml' : ''}${d.sugar_added_g !== null && d.sugar_added_g !== undefined ? ` · đường thêm ${d.sugar_added_g}g` : ''}${d.caffeine_level ? ` · ${esc(cafName[d.caffeine_level] || d.caffeine_level)}` : ''}`;
    } else {
        const sr = d.sugar_range_g ? `${d.sugar_range_g.min ?? ''}–${d.sugar_range_g.max ?? ''}g đường` : '';
        const ar = d.alcohol_pct_range ? ` · cồn ${d.alcohol_pct_range.min ?? ''}–${d.alcohol_pct_range.max ?? ''}%` : (d.alcohol_pct_estimate ? ` · cồn ~${d.alcohol_pct_estimate}%` : '');
        line = `${d.packaging ? esc(d.packaging) : ''}${sr ? ` · ${sr}` : ''}${ar}${d.caffeine_level ? ` · ${esc(cafName[d.caffeine_level] || d.caffeine_level)}` : ''}`;
    }
    return `<a class="ld-card" href="/do-uong/${esc(d.slug)}" style="text-decoration:none;color:inherit;display:block">
        <div style="display:flex;flex-wrap:wrap;gap:8px">${badges.join('')}</div>
        <h2 style="margin:8px 0 0;font-size:17px;font-weight:700">${esc(d.name_vi)}${d.uncertain ? ' <span style="font-size:13px;color:var(--color-faint)">(chưa chắc chắn)</span>' : ''}</h2>
        <p style="margin:4px 0 0;font-size:14px;color:var(--color-sub)">${line}</p>
    </a>`;
}
async function load() {
    const params = new URLSearchParams();
    ['q', 'group', 'kind', 'temperature', 'alcohol', 'caffeine_level', 'sweetness'].forEach(id => {
        const v = document.getElementById(id).value.trim();
        if (v) params.set(id, v);
    });
    if (isOn('no-alcohol-first') && !document.getElementById('alcohol').value) params.set('alcohol', '0');
    const res = await fetch('/api/drinks?' + params.toString());
    const data = await res.json();
    const items = data.data || [];
    document.getElementById('list').innerHTML = items.map(card).join('');
    document.getElementById('empty').hidden = items.length > 0;
}
['q', 'group', 'kind', 'temperature', 'alcohol', 'caffeine_level', 'sweetness'].forEach(id =>
    document.getElementById(id).addEventListener(id === 'q' ? 'input' : 'change', load));
document.getElementById('no-alcohol-first').addEventListener('click', () => {
    const b = document.getElementById('no-alcohol-first');
    b.setAttribute('aria-checked', isOn('no-alcohol-first') ? 'false' : 'true');
    load();
});
load();
</script>
@endpush
