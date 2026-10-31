@extends('layouts.app')

@section('title', 'Chi tiết món — Lắc Đói')

@section('content')
    <a href="/#kham-pha" style="display:inline-flex;align-items:center;gap:3px;color:var(--color-link);font-size:16.5px;font-weight:500;text-decoration:none;padding-top:10px">
        <svg width="11" height="18" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="transform:rotate(180deg)"><path d="M1 1l6 6-6 6"/></svg>
        Khám phá
    </a>
    <div id="content" style="color:var(--color-faint);margin-top:14px">Đang tải…</div>
@endsection

@push('scripts')
<script>
const slug = location.pathname.split('/').pop();
const slotName = { sang: 'Sáng', trua: 'Trưa', chieu: 'Chiều', toi: 'Tối', 'an-vat': 'Ăn vặt' };
const confLabel = { high: 'Tin cậy cao', medium: 'Tin cậy trung bình', low: 'Tin cậy thấp · số tham khảo' };
const confClass = { high: '', medium: 'medium', low: 'low' };
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
const chev = '<svg class="ld-chev" width="8" height="14" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M1 1l6 6-6 6"/></svg>';

fetch('/api/dishes/' + encodeURIComponent(slug), { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(({ dish: d, variants, provenance }) => {
        const n = d.nutrition;
        const name = d.names?.find(x => x.is_primary)?.name ?? d.slug;
        const aliases = (d.names ?? []).filter(x => !x.is_primary).map(x => x.name);
        document.title = name + ' — Lắc Đói';
        const unv = provenance?.unverified_ingredients ?? [];
        const allergenTags = (d.tags ?? []).filter(t => t.slug.startsWith('allergen:'));
        const otherTags = (d.tags ?? []).filter(t => !t.slug.startsWith('allergen:'));
        document.getElementById('content').innerHTML = `
        <h1 class="ld-title" style="margin-top:6px">${esc(name)}</h1>
        ${aliases.length ? `<p style="color:var(--color-faint);margin:0">Tên gọi khác: ${aliases.map(esc).join(' · ')}</p>` : ''}
        <p class="ld-subtitle" style="margin-top:4px">${esc(d.region?.name ?? '')}${d.cooking_method ? ' · Cách làm: ' + esc(d.cooking_method) : ''}${d.serving_desc ? ' · ' + esc(d.serving_desc) : ''}</p>
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:12px">
            ${(d.meal_slots ?? []).map(s => `<span class="ld-badge vu">${slotName[s.slot] ?? esc(s.slot)}</span>`).join('')}
            ${otherTags.map(t => `<span class="ld-badge">${esc(t.name)}</span>`).join('')}
            ${allergenTags.map(t => `<span class="ld-badge" style="background:var(--color-danger-tint);color:var(--color-danger)">Dị ứng: ${esc(t.name)}</span>`).join('')}
        </div>
        ${d.parent ? `<div class="ld-group" style="margin-top:16px"><a class="ld-row" href="/mon/${d.parent.slug}"><span class="grow">Biến thể của <b>${esc(d.parent.names?.[0]?.name ?? '')}</b></span>${chev}</a></div>` : ''}
        ${variants.length ? `<div class="ld-group" style="margin-top:12px">${variants.map(v => `<a class="ld-row" href="/mon/${v.slug}"><span class="grow">Biến thể: <b>${esc(v.name)}</b></span>${chev}</a>`).join('')}</div>` : ''}

        <div class="ld-card" style="margin-top:18px">
            <p style="margin:0;color:var(--color-sub);font-size:14.5px;font-weight:600">Năng lượng mỗi khẩu phần</p>
            ${n ? `<div style="display:flex;align-items:baseline;gap:9px;margin-top:4px;flex-wrap:wrap">
                    <span class="ld-kcal-so ld-tnum">${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)}</span>
                    <span style="color:var(--color-sub);font-size:14.5px">kcal</span>
                    <span class="ld-capsule ${confClass[n.confidence] ?? ''}">${confLabel[n.confidence] ?? esc(n.confidence)}</span></div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5" style="margin-top:14px">
                    <div class="ld-macro"><div class="gt ld-tnum">${n.protein_g ?? '?'} g</div><div class="nhan">Đạm</div></div>
                    <div class="ld-macro"><div class="gt ld-tnum">${n.fat_g ?? '?'} g</div><div class="nhan">Béo</div></div>
                    <div class="ld-macro"><div class="gt ld-tnum">${n.carb_g ?? '?'} g</div><div class="nhan">Bột đường</div></div>
                    <div class="ld-macro"><div class="gt ld-tnum">${n.fiber_g ?? '?'} g</div><div class="nhan">Chất xơ</div></div>
                </div>
                ${unv.length ? `<div class="ld-warn" style="margin-top:14px"><span><b>Số liệu tham khảo — chưa kiểm định:</b> món có nguyên liệu dùng nguồn thứ cấp (${esc(unv.join(', '))}). Thông tin đã có nhưng chưa được kiểm chứng bằng nguồn chính thống.</span></div>` : ''}`
            : '<p style="color:var(--color-faint);margin:10px 0 0">Chưa đủ dữ liệu dinh dưỡng cho món này — hệ thống không hiện số thay vì đoán.</p>'}
        </div>

        ${provenance ? `
        <h2 class="ld-section-title">Nguồn gốc con số</h2>
        <div class="ld-group">
            <div class="ld-row"><span class="grow" style="color:var(--color-sub)">Bảng thành phần đã dùng</span><span class="value" style="color:var(--color-ink)">${esc(provenance.fct_source)}</span></div>
            <div class="ld-row"><span class="grow" style="color:var(--color-sub)">Engine tính</span><span class="value" style="color:var(--color-ink)">Phiên bản ${esc(provenance.engine_version)}</span></div>
            <div class="ld-row"><span class="grow" style="color:var(--color-sub)">Ngày tính</span><span class="value" style="color:var(--color-ink)">${new Date(provenance.computed_at).toLocaleDateString('vi-VN')}</span></div>
            <div class="ld-row"><span class="grow" style="color:var(--color-sub)">Nguyên liệu tham gia tính</span><span class="value" style="color:var(--color-ink);white-space:normal;text-align:right">${esc((provenance.contributing_ingredients ?? []).join(', ') || '—')}</span></div>
            ${(provenance.missing_ingredients ?? []).length ? `<div class="ld-row"><span class="grow" style="color:var(--color-sub)">Chưa tra được (không tính vào số trên)</span><span class="value" style="color:var(--color-ink);white-space:normal;text-align:right">${esc(provenance.missing_ingredients.join(', '))}</span></div>` : ''}
            ${unv.length ? `<div class="ld-row"><span class="grow" style="color:var(--color-sub)">Nguyên liệu nguồn thứ cấp</span><span class="value" style="color:var(--color-ink);white-space:normal;text-align:right">${esc(unv.join(', '))}</span></div>` : ''}
        </div>
        <p class="ld-note" style="margin-top:10px">Khối này luôn mở sẵn — minh bạch nguồn là điểm khác biệt của Lắc Đói, bạn kiểm chứng được mọi con số ngay tại trang này.</p>` : ''}

        <h2 class="ld-section-title">Nguyên liệu · một khẩu phần</h2>
        <div class="ld-group">
            ${(d.dish_ingredients ?? []).map(di => `<div class="ld-row"><span class="grow">${esc(di.ingredient?.name_vi ?? '')}${di.is_optional ? ' <span style="color:var(--color-faint);font-size:13.5px">(tuỳ chọn)</span>' : ''}</span><span class="value ld-tnum" style="color:var(--color-ink);font-weight:600">${esc(di.quantity ?? '')} ${esc(di.unit_name ?? '')}</span></div>`).join('')
              || '<div class="ld-row"><span style="color:var(--color-faint)">Chưa có định lượng.</span></div>'}
        </div>

        ${d.recipe_steps?.length ? `
        <h2 class="ld-section-title">Cách làm</h2>
        <div class="ld-group">
            ${d.recipe_steps.map((s, i) => `<div class="ld-row" style="align-items:flex-start">
                <span style="flex:none;width:26px;height:26px;border-radius:50%;background:var(--color-accent);color:#fff;font-weight:700;font-size:14px;display:inline-flex;align-items:center;justify-content:center;margin-top:1px">${i + 1}</span>
                <span class="grow">${esc(s)}</span></div>`).join('')}
        </div>` : ''}
        ${d.needs_review ? '<div class="ld-warn" style="margin-top:18px"><span>Món đang trong giai đoạn biên soạn dữ liệu — thông tin có thể được cập nhật sau khi duyệt.</span></div>' : ''}`;
    })
    .catch(() => { document.getElementById('content').textContent = 'Không tải được món này.'; });
</script>
@endpush
