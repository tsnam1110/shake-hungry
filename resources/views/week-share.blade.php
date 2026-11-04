@extends('layouts.app')

@section('title', 'Tuần ăn được chia sẻ — Lắc Đói')

@section('content')
    <h1 class="ld-title" style="margin-top:14px">Tuần ăn được chia sẻ</h1>
    <p class="ld-subtitle">Bạn đang xem tuần ăn qua mã chia sẻ. Chỉ thấy món và đi chợ, không thấy mục tiêu, ngân sách hay hồ sơ của người chia sẻ.</p>
    <div id="share-content"></div>
@endsection

@push('scripts')
<script>
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
const slotName = { sang: 'Sáng', trua: 'Trưa', toi: 'Tối', 'an-vat': 'Ăn vặt' };
(async () => {
    const code = @json($code);
    const resp = await fetch('/api/shares/' + encodeURIComponent(code));
    const box = document.getElementById('share-content');
    if (!resp.ok) {
        box.innerHTML = '<p class="ld-note">Mã này không tồn tại, đã hết hạn sau 14 ngày, hoặc đã bị thu hồi.</p>';
        return;
    }
    const data = await resp.json();
    box.innerHTML = `<p class="ld-note">${esc(data.plan.start_date)} – ${esc(data.plan.end_date)}</p>` +
        data.days.map(d => `<div class="ld-card" style="margin-bottom:14px">
            <p style="font-weight:700;margin:0 0 10px">${new Date(d.date + 'T00:00:00').toLocaleDateString('vi-VN', { weekday: 'long', day: 'numeric', month: 'numeric' })}</p>
            <div class="ld-group">${d.meals.map(m => `<div class="ld-row"><span class="grow"><span style="font-weight:600">${esc(slotName[m.slot] ?? m.slot)}</span><span class="sub">${m.dish ? esc(m.dish.name ?? m.dish.slug) : 'Chưa có món'}${m.is_eat_out ? ' · Ăn ngoài' : ''}</span></span></div>`).join('')}</div>
        </div>`).join('') +
        `<h2 class="ld-section-title">Danh sách đi chợ</h2>` +
        (data.shopping_list.aisle_groups ?? []).map(g => `<p class="ld-group-label">${esc(g.aisle)}</p><div class="ld-group">${g.items.map(it => `<div class="ld-row"><span class="grow"><span style="font-weight:600">${esc(it.name)}</span>
                ${(it.pack_options ?? []).length ? `<span class="sub" style="display:block;margin-top:4px">Gói (snapshot 04/11): ${(it.pack_options ?? []).map(p => `${esc(p.product)} — ${esc(p.pack_label)}${p.packs_needed != null ? ` × ${p.packs_needed} gói` : ' (chưa tính được số gói)'} · ${Number(p.price_vnd).toLocaleString('vi-VN')} đ · ${esc(p.channel)}`).join('<br>')}</span>` : ''}
                ${it.unverified_pack_count ? `<span class="sub" style="display:block">Còn ${it.unverified_pack_count} gói ứng viên chưa xác nhận ánh xạ, chưa hiển thị.</span>` : ''}</span><span class="ld-price"><span class="so ld-tnum">${it.total_grams != null ? it.total_grams + ' g' : it.unit_entries.map(u => u.quantity + ' ' + esc(u.unit_name)).join(' + ')}</span></span></div>`).join('')}</div>`).join('');
})();
</script>
@endpush
