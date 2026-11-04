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
        <div class="ld-card" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin-bottom:14px">
            <div style="flex:1;min-width:200px"><label class="ld-field-label" for="week-new-start">Đổi ngày bắt đầu tuần này</label>
                <input id="week-new-start" type="date" class="ld-input"></div>
            <button id="week-change-start" class="ld-btn ld-btn-gray" style="padding:11px 22px;font-size:15.5px">Đổi ngày bắt đầu</button>
            <div style="flex:1;min-width:200px"><label class="ld-field-label" for="week-restart-from">Bắt đầu lại từ ngày</label>
                <input id="week-restart-from" type="date" class="ld-input"></div>
            <button id="week-restart" class="ld-btn ld-btn-gray" style="padding:11px 22px;font-size:15.5px">Bắt đầu lại phần còn lại</button>
        </div>
        <p class="ld-note" style="margin-top:-6px;margin-bottom:14px">Bắt đầu lại chỉ xây lại các ngày từ ngày đã chọn trở đi; các ngày trước đó giữ nguyên, kể cả món bạn đã đổi tay.</p>
        <div style="margin-bottom:14px"><button id="week-shopping" class="ld-btn ld-btn-gray" style="padding:11px 22px;font-size:15.5px">Danh sách đi chợ</button></div>
        <div id="week-shopping-list" style="display:none"></div>
        <div class="ld-card" style="margin-bottom:14px">
            <label class="ld-field-label" for="week-exclude-q">Loại một thực phẩm khỏi tuần này</label>
            <div class="ld-search"><input id="week-exclude-q" type="search" placeholder="Gõ tên nguyên liệu, ví dụ: lạc…" aria-label="Tìm nguyên liệu để loại khỏi tuần"></div>
            <div id="week-exclude-results" class="ld-group" style="margin-top:10px"></div>
            <p class="ld-note" style="margin-top:8px">Loại khỏi tuần cũng thêm nguyên liệu đó vào danh sách không ăn được trong Hồ sơ, để tuần sau cũng không chọn món chứa nó. Bữa không còn ứng viên thay thế sẽ bị xoá khỏi ngày đó.</p>
        </div>
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
                    <button data-eatout="${m.id}" data-eatout-state="${m.is_eat_out ? '1' : '0'}" style="border:0;background:none;color:var(--color-link);font-size:14px;font-weight:600;cursor:pointer;font-family:inherit">${m.is_eat_out ? 'Bỏ ăn ngoài' : 'Ăn ngoài'}</button>
                </div>
                <div id="swap-${m.id}"></div>`).join('')}</div>
        </div>`;
    }).join('');
    document.querySelectorAll('[data-swap]').forEach(b => b.onclick = () => showSwapCandidates(+b.dataset.swap));
    document.querySelectorAll('[data-eatout]').forEach(b => b.onclick = async () => {
        const next = b.dataset.eatoutState !== '1';
        const resp = await fetch(`/api/week-plans/${currentPlanId}/meals/${b.dataset.eatout}/eat-out`, { method: 'POST', headers, body: JSON.stringify({ is_eat_out: next }) });
        if (!resp.ok) { alert('Đánh dấu ăn ngoài thất bại'); return; }
        if (window.ldToast) ldToast(next ? 'Đã đánh dấu ăn ngoài — bữa này không tính vào tổng ngày' : 'Đã bỏ đánh dấu ăn ngoài');
        showPlan(currentPlanId);
    });
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
let excludeTimer;
document.getElementById('week-exclude-q').addEventListener('input', (e) => {
    clearTimeout(excludeTimer);
    excludeTimer = setTimeout(async () => {
        const q = e.target.value.trim();
        if (!q) { document.getElementById('week-exclude-results').innerHTML = ''; return; }
        const data = await (await fetch('/api/ingredients?q=' + encodeURIComponent(q) + '&per_page=6')).json();
        document.getElementById('week-exclude-results').innerHTML = (data.data ?? []).map(ing => `
            <button data-exclude="${esc(ing.slug)}" class="ld-row" style="cursor:pointer;width:100%;text-align:left;background:none;border:0;font-family:inherit">
                <span class="grow">Loại <b>${esc(ing.name_vi ?? ing.slug)}</b> khỏi tuần này</span>
            </button>`).join('');
        document.querySelectorAll('[data-exclude]').forEach(b => b.onclick = async () => {
            if (!currentPlanId) return;
            if (!confirm(`Loại nguyên liệu này khỏi tuần và khỏi hồ sơ? Các bữa chứa nó sẽ được thay bằng món khác.`)) return;
            const resp = await fetch(`/api/week-plans/${currentPlanId}/exclude-ingredient`, { method: 'POST', headers, body: JSON.stringify({ ingredient_slug: b.dataset.exclude }) });
            const res = await resp.json();
            if (!resp.ok) { alert(res.message || 'Loại thực phẩm thất bại'); return; }
            if (window.ldToast) ldToast(`Đã thay ${res.replaced} bữa, xoá ${res.deleted} bữa không còn ứng viên`);
            showPlan(currentPlanId);
        });
    }, 350);
});
document.getElementById('week-shopping').onclick = async () => {
    if (!currentPlanId) return;
    const data = await (await fetch(`/api/week-plans/${currentPlanId}/shopping-list`)).json();
    const box = document.getElementById('week-shopping-list');
    box.style.display = 'block';
    box.innerHTML = `<h2 class="ld-section-title">Danh sách đi chợ</h2>
        <p class="ld-note" style="margin-top:-6px">${esc(data.note)}</p>
        ${data.groups.map(g => `
            <p class="ld-group-label">${esc(g.label)}</p>
            <div class="ld-group">${g.items.map(it => `
                <div class="ld-row"><span class="grow"><span style="font-weight:600">${esc(it.name)}</span>
                <span class="sub">${it.optional ? 'Tuỳ chọn · ' : ''}Dùng trong ${it.meals_count} bữa</span></span>
                <span class="ld-price"><span class="so ld-tnum">${it.total_grams != null ? it.total_grams + ' g' : it.unit_entries.map(u => u.quantity + ' ' + esc(u.unit_name)).join(' + ')}</span></span></div>`).join('')}
            </div>`).join('')}`;
    box.scrollIntoView({ behavior: 'smooth' });
};
document.getElementById('week-restart').onclick = async () => {
    const from = document.getElementById('week-restart-from').value;
    if (!from || !currentPlanId) return;
    if (!confirm('Bắt đầu lại phần còn lại từ ngày này? Các bữa đã lên cho các ngày đó sẽ được thay bằng món mới.')) return;
    const resp = await fetch(`/api/week-plans/${currentPlanId}/restart-remaining`, { method: 'POST', headers, body: JSON.stringify({ from_date: from }) });
    const res = await resp.json();
    if (!resp.ok) { alert(res.message || 'Bắt đầu lại thất bại'); return; }
    if (window.ldToast) ldToast('Đã bắt đầu lại phần còn lại của tuần');
    showPlan(currentPlanId);
};
document.getElementById('week-change-start').onclick = async () => {
    const start = document.getElementById('week-new-start').value;
    if (!start || !currentPlanId) return;
    const resp = await fetch(`/api/week-plans/${currentPlanId}/change-start`, { method: 'POST', headers, body: JSON.stringify({ start_date: start }) });
    const res = await resp.json();
    if (!resp.ok) { alert(res.message || 'Đổi ngày bắt đầu thất bại'); return; }
    if (window.ldToast) ldToast('Đã đổi ngày bắt đầu tuần');
    loadPlans();
    showPlan(currentPlanId);
};
loadPlans();
</script>
@endauth
@endpush
