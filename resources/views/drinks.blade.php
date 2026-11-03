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

    <p class="ld-note" id="count-line" style="margin:18px 2px 10px"></p>
    <div id="list" class="ld-grid"></div>
    <div style="text-align:center;margin-top:20px">
        <button id="more-btn" class="ld-btn ld-btn-gray" style="display:none">Xem thêm đồ uống</button>
    </div>
    <p id="empty" class="ld-note" style="margin-top:20px" hidden>Không có đồ uống nào khớp bộ lọc.</p>
@endsection

@push('scripts')
<script>
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
function isOn(id) { return document.getElementById(id).getAttribute('aria-checked') === 'true'; }
const groupName = { tra: 'Trà', 'ca-phe': 'Cà phê', 'sinh-to': 'Sinh tố', 'nuoc-ep': 'Nước ép', 'giai-khat': 'Giải khát', 'cocktail-co-con': 'Cocktail', mocktail: 'Mocktail', 'nuoc-ngot-co-ga': 'Nước ngọt có ga', 'tra-dong-chai': 'Trà đóng chai', 'ca-phe-dong-goi': 'Cà phê đóng gói', 'nuoc-ep-dong-hop': 'Nước ép đóng hộp', 'sua-dong-hop': 'Sữa đóng hộp', 'tang-luc-dien-giai': 'Tăng lực / điện giải', 'nuoc-dong-goi-khac': 'Nước đóng gói khác', bia: 'Bia', vang: 'Vang', ruou: 'Rượu', 'nuoc-giai-khat-dong-goi': 'Nước giải khát đóng gói' };
const cafName = { khong: 'Không caffeine', thap: 'Caffeine thấp', 'trung-binh': 'Caffeine trung bình', cao: 'Caffeine cao' };
const tempName = { nong: 'Nóng', lanh: 'Lạnh', 'ca-hai': 'Nóng hoặc lạnh' };
const heartSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"><path d="M12 20.5C7 16.5 3.8 13.3 3.8 9.6 3.8 7 5.7 5 8.1 5c1.6 0 3.1.9 3.9 2.3C12.8 5.9 14.3 5 15.9 5c2.4 0 4.3 2 4.3 4.6 0 3.7-3.2 6.9-8.2 10.9Z"/></svg>';
// Thẻ nước uống theo đúng khung ld-dcard của trang món: ảnh, tim, tiêu đề,
// dòng meta (nhóm · loại · nóng/lạnh), huy hiệu ld-badge, chân thẻ là thông
// tin khẩu phần đã có (dung tích · đường · caffeine · cồn). Khi chưa có ảnh
// thật, thẻ dùng placeholder-drink.svg (không phải ảnh thật của mục đó).
function card(d) {
    const badges = [];
    if (d.kind === 'dong-goi') badges.push('<span class="ld-badge">Sản phẩm đóng gói</span>');
    if (d.alcohol) badges.push('<span class="ld-badge" style="background:var(--color-danger-tint);color:var(--color-danger)">18+</span>');
    if (d.uncertain) badges.push('<span class="ld-badge">Chưa chắc chắn</span>');
    // Chân thẻ nối các phần đã có bằng " · ", không để dấu nối thừa ở đầu
    // khi phần đầu trống (VD: pha chế không có dung tích vẫn có đường thêm).
    // Khoảng rỗng thì bỏ trống, không in dạng "–g" hay "–%" (QT-9, theo mẫu
    // rangeText của trang chi tiết).
    let line = '';
    if (d.kind === 'pha-che') {
        const parts = [];
        if (d.serving_ml) parts.push(d.serving_ml + ' ml');
        if (d.sugar_added_g !== null && d.sugar_added_g !== undefined) parts.push(`đường thêm ${d.sugar_added_g}g`);
        if (d.caffeine_level) parts.push(esc(cafName[d.caffeine_level] || d.caffeine_level));
        line = parts.join(' · ');
    } else {
        const parts = [];
        if (d.packaging) parts.push(esc(d.packaging));
        if (d.sugar_range_g && (d.sugar_range_g.min !== null && d.sugar_range_g.min !== undefined || d.sugar_range_g.max !== null && d.sugar_range_g.max !== undefined)) {
            const sr = d.sugar_range_g.min !== null && d.sugar_range_g.min !== undefined && d.sugar_range_g.max !== null && d.sugar_range_g.max !== undefined
                ? `${d.sugar_range_g.min}–${d.sugar_range_g.max}g đường`
                : (d.sugar_range_g.min !== null && d.sugar_range_g.min !== undefined ? `từ ${d.sugar_range_g.min}g đường` : `đến ${d.sugar_range_g.max}g đường`);
            parts.push(sr);
        }
        if (d.alcohol_pct_range && (d.alcohol_pct_range.min !== null && d.alcohol_pct_range.min !== undefined || d.alcohol_pct_range.max !== null && d.alcohol_pct_range.max !== undefined)) {
            parts.push(d.alcohol_pct_range.min !== null && d.alcohol_pct_range.min !== undefined && d.alcohol_pct_range.max !== null && d.alcohol_pct_range.max !== undefined
                ? `cồn ${d.alcohol_pct_range.min}–${d.alcohol_pct_range.max}%`
                : (d.alcohol_pct_range.min !== null && d.alcohol_pct_range.min !== undefined ? `cồn từ ${d.alcohol_pct_range.min}%` : `cồn đến ${d.alcohol_pct_range.max}%`));
        } else if (d.alcohol_pct_estimate) parts.push(`cồn ~${d.alcohol_pct_estimate}%`);
        if (d.caffeine_level) parts.push(esc(cafName[d.caffeine_level] || d.caffeine_level));
        line = parts.join(' · ');
    }
    return `<a href="/do-uong/${esc(d.slug)}" class="ld-dcard">
        <img class="ld-dcard-img" src="${d.image?.thumb_url ?? d.image?.url ?? '/images/placeholder-drink.svg'}" srcset="${d.image?.srcset ?? ''}" sizes="(max-width: 640px) 50vw, 320px" alt="${esc(d.name_vi)}" loading="lazy" decoding="async">
        <span class="ld-heart" data-fav-drink="${d.id}" role="button" aria-label="Yêu thích đồ uống này">${heartSvg}</span>
        <span class="ld-dcard-body">
            <span class="ld-dcard-title">${esc(d.name_vi)}</span>
            <span class="ld-dcard-meta">${esc(groupName[d.drink_group] || d.drink_group)} · ${d.kind === 'pha-che' ? 'Pha chế' : 'Đóng gói'}${d.temperature ? ' · ' + esc(tempName[d.temperature] || d.temperature) : ''}</span>
            <span class="ld-dcard-badges">${badges.join('')}</span>
            <span class="ld-dcard-foot"><span style="font-size:13px;color:var(--color-sub);line-height:1.5">${line}</span></span>
        </span>
    </a>`;
}
// Phân trang theo mẫu trang món: 24 mục/trang + nút xem thêm + dòng đếm,
// để cả 230 mục đều tới được (trước đây API mặc định trả 100 mục, 130 mục
// còn lại không tới được từ trang danh sách).
let page = 1, total = 0, lastPage = 1;
async function loadPage(append) {
    const params = new URLSearchParams();
    ['q', 'group', 'kind', 'temperature', 'alcohol', 'caffeine_level', 'sweetness'].forEach(id => {
        const v = document.getElementById(id).value.trim();
        if (v) params.set(id, v);
    });
    if (isOn('no-alcohol-first') && !document.getElementById('alcohol').value) params.set('alcohol', '0');
    params.set('per_page', '24');
    params.set('page', String(page));
    const res = await fetch('/api/drinks?' + params.toString());
    const data = await res.json();
    total = data.total ?? 0; lastPage = data.last_page ?? 1;
    const html = (data.data || []).map(card).join('');
    const box = document.getElementById('list');
    box.innerHTML = append ? box.innerHTML + html : html;
    const shown = box.querySelectorAll('.ld-dcard').length;
    document.getElementById('count-line').textContent = total ? `Đang hiện ${shown} trong ${total} đồ uống` : '';
    document.getElementById('more-btn').style.display = page < lastPage ? '' : 'none';
    document.getElementById('empty').hidden = shown > 0;
    if (window.ldRefreshHearts) window.ldRefreshHearts();
}
function reload() { page = 1; loadPage(false); }
document.getElementById('more-btn').addEventListener('click', () => { page++; loadPage(true); });
['q', 'group', 'kind', 'temperature', 'alcohol', 'caffeine_level', 'sweetness'].forEach(id =>
    document.getElementById(id).addEventListener(id === 'q' ? 'input' : 'change', reload));
document.getElementById('no-alcohol-first').addEventListener('click', () => {
    const b = document.getElementById('no-alcohol-first');
    b.setAttribute('aria-checked', isOn('no-alcohol-first') ? 'false' : 'true');
    reload();
});
reload();
</script>
@endpush
