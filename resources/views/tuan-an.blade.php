@extends('layouts.app')

@section('title', 'Tuần ăn — Lắc Đói')

@section('content')
@guest
    <h1 class="ld-title" style="margin-top:14px">Tuần ăn</h1>
    <p class="ld-subtitle">Một tuần 7 ngày, mỗi ngày 4 bữa sáng / trưa / tối / ăn vặt, kiểm chứng từng ngày theo mục tiêu protein và kcal của bạn. Tuần thật cần đăng nhập để lưu theo hồ sơ của bạn.</p>
    <section>
        <h2 class="ld-section-title">Ngày ví dụ — không lưu, không cá nhân hoá</h2>
        <div class="ld-group" id="example-meals"></div>
        <p class="ld-note" style="margin-top:10px">Ngày ví dụ lấy món thật từ thư viện, xếp theo protein giảm dần. Nó không có mục tiêu của bạn nên không kết luận đạt hay thiếu.</p>
    </section>
    <div style="display:flex;gap:10px;margin-top:22px;flex-wrap:wrap">
        <a href="/tai-khoan" class="ld-btn ld-btn-primary" style="text-decoration:none">Đăng nhập để tạo tuần ăn</a>
        <a href="/ho-so" class="ld-btn ld-btn-gray" style="text-decoration:none">Về Hồ sơ</a>
    </div>
@endguest

@auth
    <h1 class="ld-title" style="margin-top:14px">Tuần ăn</h1>
    <p class="ld-subtitle">Tuần mới lấy mục tiêu protein và kcal từ Hồ sơ của bạn lúc tạo tuần. Đổi Hồ sơ sau đó không sửa ngược tuần cũ. Mỗi ngày được kiểm chứng riêng: đạt, thiếu kèm số thiếu, hoặc chưa đủ dữ kiện.</p>

    <section>
        <h2 class="ld-section-title">Tạo tuần mới</h2>
        <form id="week-create" class="ld-card" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end">
            <div style="flex:1;min-width:200px"><label class="ld-field-label" for="week-start">Ngày bắt đầu tuần</label>
                <input id="week-start" name="start_date" type="date" class="ld-input" required></div>
            <button class="ld-btn ld-btn-primary" style="padding:11px 22px;font-size:15.5px">Tạo tuần ăn</button>
        </form>
        <p class="ld-note" style="margin-top:8px">Tuần kéo dài 7 ngày từ ngày bắt đầu. Tạo tuần mới sẽ lưu trữ tuần đang hoạt động cũ. Mục tiêu tuần lấy từ Hồ sơ — nếu chưa đặt protein/kcal mỗi ngày, tuần vẫn tạo được nhưng validator sẽ ghi chưa đủ dữ kiện.</p>
    </section>

    <section>
        <h2 class="ld-section-title">Các tuần của bạn</h2>
        <div class="ld-group" id="week-list"></div>
    </section>

    <section id="week-detail" style="display:none">
        <h2 class="ld-section-title" id="week-detail-title">Chi tiết tuần</h2>
        <div id="week-days"></div>
        <p class="ld-note" style="margin-top:10px">Cảnh báo kết hợp chỉ là lớp thông tin ở trang món, không chặn, không lọc và không chấm điểm tuần ăn. Thao tác đổi bữa, ăn ngoài và đi chợ sẽ có ở các phần tiếp theo của lộ trình.</p>
    </section>
@endauth
@endsection

@push('scripts')
<script>
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
const slotName = { sang: 'Sáng', trua: 'Trưa', toi: 'Tối', 'an-vat': 'Ăn vặt' };
const statusLabel = { dat: 'Đạt', thieu: 'Thiếu', 'chua-du-du-lieu': 'Chưa đủ dữ kiện' };
const statusBadge = { dat: 'vu', thieu: 'hoi', 'chua-du-du-lieu': 'unv' };
const csrf = document.querySelector('meta[name=csrf-token]').content;
const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
</script>
@guest
<script>
(async () => {
    try {
        const data = await (await fetch('/api/week-plans/example')).json();
        document.getElementById('example-meals').innerHTML = (data.meals ?? []).map(m => `
            <div class="ld-row">
                <span class="grow"><span style="font-weight:600">${esc(slotName[m.slot] ?? m.slot)}</span>
                <span class="sub"><a href="/mon/${esc(m.dish.slug)}">${esc(m.dish.name ?? m.dish.slug)}</a>${m.dish.region ? ' · ' + esc(m.dish.region) : ''}</span></span>
                ${m.dish.nutrition ? `<span class="ld-price"><span class="so ld-tnum">${Math.round(m.dish.nutrition.kcal_min)}–${Math.round(m.dish.nutrition.kcal_max)}</span><small>kcal · ${Math.round(m.dish.nutrition.protein_g)} g protein</small></span>` : ''}
            </div>`).join('') || '<p style="color:var(--color-faint);padding:16px;margin:0">Chưa có món ví dụ.</p>';
    } catch { /* khối ví dụ */ }
})();
</script>
@endguest
@auth
<script>
async function loadPlans() {
    const { plans } = await (await fetch('/api/week-plans')).json();
    document.getElementById('week-list').innerHTML = (plans ?? []).map(p => {
        const dat = p.days.filter(d => d.status === 'dat').length;
        const thieu = p.days.filter(d => d.status === 'thieu').length;
        const chua = p.days.filter(d => d.status === 'chua-du-du-lieu').length;
        return `<button data-plan="${p.id}" class="ld-row" style="cursor:pointer;text-align:left;width:100%;font-family:inherit;background:none;border:0">
            <span class="grow"><span style="font-weight:600">${esc(p.start_date)} – ${esc(p.end_date)}</span>
            <span class="sub">${p.status === 'active' ? 'Đang hoạt động' : 'Đã lưu trữ'}${p.target_protein_g_per_day ? ` · Mục tiêu ${p.target_protein_g_per_day} g protein, ${p.target_kcal_per_day} kcal/ngày` : ' · Chưa đặt mục tiêu'}</span></span>
            <span><span class="ld-badge vu">${dat} đạt</span><span class="ld-badge hoi">${thieu} thiếu</span><span class="ld-badge unv">${chua} chưa đủ dữ kiện</span></span>
        </button>`;
    }).join('') || '<p style="color:var(--color-faint);padding:16px;margin:0">Chưa có tuần nào — tạo tuần mới ở trên.</p>';
    document.querySelectorAll('[data-plan]').forEach(b => b.onclick = () => showPlan(+b.dataset.plan));
}
let currentPlanId = null;
async function showPlan(id) {
    currentPlanId = id;
    const data = await (await fetch('/api/week-plans/' + id)).json();
    const p = data.plan;
    document.getElementById('week-detail').style.display = 'block';
    document.getElementById('week-detail-title').textContent = `Chi tiết tuần ${p.start_date} – ${p.end_date}`;
    document.getElementById('week-days').innerHTML = data.days.map(d => {
        const v = d.validation;
        const extra = v.status === 'thieu'
            ? (v.protein_shortfall_g ? ` · Thiếu ${v.protein_shortfall_g} g protein` : '') + (v.kcal_shortfall ? ` · Thiếu ${v.kcal_shortfall} kcal` : '') + (v.kcal_excess ? ` · Vượt ${v.kcal_excess} kcal` : '')
            : (v.status === 'chua-du-du-lieu' ? (v.unknown_meals ? ` · ${v.unknown_meals} bữa chưa đủ dữ kiện` : '') + (v.missing_slots.length ? ` · Thiếu bữa: ${v.missing_slots.map(s => slotName[s] ?? s).join(', ')}` : '') + (!v.target_protein_g_per_day || !v.target_kcal_per_day ? ' · Chưa đặt mục tiêu' : '') : '');
        return `<div class="ld-card" style="margin-bottom:14px">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span style="font-weight:700">${new Date(v.date + 'T00:00:00').toLocaleDateString('vi-VN', { weekday: 'long', day: 'numeric', month: 'numeric' })}</span>
                <span class="ld-badge ${statusBadge[v.status] ?? ''}">${statusLabel[v.status] ?? v.status}</span>
                <span style="color:var(--color-sub);font-size:14.5px">${v.protein_g} g protein · ${v.kcal_min}–${v.kcal_max} kcal${extra}</span>
            </div>
            <div class="ld-group" style="margin-top:12px">${d.meals.map(m => `
                <div class="ld-row">
                    <span class="grow"><span style="font-weight:600">${esc(slotName[m.slot] ?? m.slot)}</span>
                    <span class="sub">${m.dish ? `<a href="/mon/${esc(m.dish.slug)}">${esc(m.dish.name ?? m.dish.slug)}</a>` : 'Chưa có món'}${m.is_eat_out ? ' · Ăn ngoài' : ''}</span></span>
                    ${m.dish?.nutrition ? `<span class="ld-price"><span class="so ld-tnum">${Math.round(m.dish.nutrition.kcal_min)}–${Math.round(m.dish.nutrition.kcal_max)}</span><small>kcal · ${Math.round(m.dish.nutrition.protein_g)} g protein</small></span>` : ''}
                    <button data-swap="${m.id}" style="border:0;background:none;color:var(--color-link);font-size:14px;font-weight:600;cursor:pointer;font-family:inherit">Đổi</button>
                </div>
                <div id="swap-${m.id}"></div>`).join('')}</div>
        </div>`;
    }).join('');
    document.querySelectorAll('[data-swap]').forEach(b => b.onclick = () => showSwapCandidates(+b.dataset.swap));
    document.getElementById('week-detail').scrollIntoView({ behavior: 'smooth' });
}
async function showSwapCandidates(mealId) {
    const box = document.getElementById('swap-' + mealId);
    const data = await (await fetch(`/api/week-plans/${currentPlanId}/meals/${mealId}/candidates`)).json();
    box.innerHTML = `<div class="ld-card" style="margin:10px 0;padding:12px 14px">
        <p style="margin:0 0 8px;font-weight:600;font-size:14.5px">Chọn món thay thế — xem dinh dưỡng trước khi áp dụng:</p>
        ${(data.candidates ?? []).slice(0, 20).map(c => `
            <button data-choose="${c.id}" data-meal="${mealId}" class="ld-row" style="cursor:pointer;width:100%;text-align:left;background:none;border:0;font-family:inherit">
                <span class="grow"><span style="font-weight:600">${esc(c.name ?? c.slug)}</span><span class="sub">${c.region ? esc(c.region) + ' · ' : ''}${c.nutrition ? Math.round(c.nutrition.kcal_min) + '–' + Math.round(c.nutrition.kcal_max) + ' kcal · ' + Math.round(c.nutrition.protein_g) + ' g protein' : 'Chưa đủ dữ liệu'}</span></span>
            </button>`).join('') || '<p style="color:var(--color-faint);margin:0">Không còn món khác đủ điều kiện cho bữa này.</p>'}
    </div>`;
    box.querySelectorAll('[data-choose]').forEach(b => b.onclick = async () => {
        const resp = await fetch(`/api/week-plans/${currentPlanId}/meals/${mealId}/swap`, { method: 'POST', headers, body: JSON.stringify({ dish_id: +b.dataset.choose }) });
        const res = await resp.json();
        if (!resp.ok) { alert(res.message || 'Đổi bữa thất bại'); return; }
        if (window.ldToast) ldToast('Đã đổi bữa');
        showPlan(currentPlanId);
    });
}
document.getElementById('week-create').onsubmit = async (e) => {
    e.preventDefault();
    const start = document.getElementById('week-start').value;
    const resp = await fetch('/api/week-plans', { method: 'POST', headers, body: JSON.stringify({ start_date: start }) });
    const data = await resp.json();
    if (!resp.ok) { alert(data.message || 'Tạo tuần thất bại'); return; }
    if (window.ldToast) ldToast('Đã tạo tuần ăn');
    loadPlans();
    showPlan(data.plan.id);
};
document.getElementById('week-start').value = new Date().toISOString().slice(0, 10);
loadPlans();
</script>
@endauth
@endpush
