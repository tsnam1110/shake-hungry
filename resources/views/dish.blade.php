@extends('layouts.app')

@section('title', 'Chi tiết món — Lắc Đói')

@section('content')
    <a href="/#kham-pha" style="display:inline-flex;align-items:center;gap:3px;color:var(--color-link);font-size:16.5px;font-weight:500;text-decoration:none;padding-top:10px">
        <svg width="11" height="18" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="transform:rotate(180deg)"><path d="M1 1l6 6-6 6"/></svg>
        Thư viện
    </a>
    <div id="content" style="color:var(--color-faint);margin-top:14px">Đang tải…</div>
@endsection

@push('scripts')
<script>
const slug = location.pathname.split('/').pop();
const slotName = { sang: 'Sáng', trua: 'Trưa', chieu: 'Chiều', toi: 'Tối', 'an-vat': 'Ăn vặt' };
const methodName = { nau: 'Nấu', luoc: 'Luộc', hap: 'Hấp', kho: 'Kho', xao: 'Xào', chien: 'Chiên', 'chien-ngap-dau': 'Chiên ngập dầu', ran: 'Rán', nuong: 'Nướng', tron: 'Trộn', song: 'Sống' };
const confLabel = { high: 'Tin cậy cao', medium: 'Tin cậy trung bình', low: 'Tin cậy thấp · số tham khảo' };
const confClass = { high: '', medium: 'medium', low: 'low' };
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
const heartSvg = '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"><path d="M12 20.5C7 16.5 3.8 13.3 3.8 9.6 3.8 7 5.7 5 8.1 5c1.6 0 3.1.9 3.9 2.3C12.8 5.9 14.3 5 15.9 5c2.4 0 4.3 2 4.3 4.6 0 3.7-3.2 6.9-8.2 10.9Z"/></svg>';
const chev = '<svg class="ld-chev" width="8" height="14" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M1 1l6 6-6 6"/></svg>';

fetch('/api/dishes/' + encodeURIComponent(slug), { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(({ dish: d, variants, provenance }) => {
        const n = d.nutrition;
        const name = d.names?.find(x => x.is_primary && x.locale === 'vi')?.name ?? d.names?.find(x => x.is_primary)?.name ?? d.slug;
        const aliases = (d.names ?? []).filter(x => !x.is_primary).map(x => x.name);
        document.title = name + ' — Lắc Đói';
        const unv = provenance?.unverified_ingredients ?? [];
        const allergenTags = (d.tags ?? []).filter(t => t.slug.startsWith('allergen:'));
        const otherTags = (d.tags ?? []).filter(t => !t.slug.startsWith('allergen:'));
        document.getElementById('content').innerHTML = `
        <h1 class="ld-title" style="margin-top:6px">${esc(name)}</h1>
        ${aliases.length ? `<p style="color:var(--color-faint);margin:0">Tên gọi khác: ${aliases.map(esc).join(' · ')}</p>` : ''}
        <p class="ld-subtitle" style="margin-top:4px">${esc(d.region?.name ?? '')}${d.cooking_method ? ' · Cách làm: ' + esc(d.cooking_method_label ?? methodName[d.cooking_method] ?? d.cooking_method) : ''}${d.serving_desc ? ' · ' + esc(d.serving_desc) : ''}</p>
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:12px">
            ${(d.meal_slots ?? []).map(s => `<span class="ld-badge vu">${slotName[s.slot] ?? esc(s.slot)}</span>`).join('')}
            ${otherTags.map(t => `<span class="ld-badge">${esc(t.name)}</span>`).join('')}
        </div>
        ${d.image ? `<figure style="margin:18px 0 0">
            <img src="${esc(d.image.url)}" alt="${esc(name)}" style="width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:18px;display:block" fetchpriority="high" decoding="async">
            <figcaption style="color:var(--color-faint);font-size:12.5px;margin-top:6px">${d.image.is_ai ? 'Ảnh minh họa' : 'Ảnh: ' + esc(d.image.attribution ?? '')}</figcaption>
        </figure>` : ''}
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

        <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap">
            <button type="button" class="ld-btn ld-btn-gray ld-fav-btn" data-fav-dish="${d.id}" style="padding:11px 20px;font-size:15px"><span class="heart-ic">${heartSvg}</span>Yêu thích món này</button>
            <button type="button" class="ld-btn ld-btn-gray" data-feedback data-dish="${d.id}" data-context="${esc(name)}" style="padding:11px 20px;font-size:15px">Gửi phản hồi</button>
        </div>

        <h2 class="ld-section-title">Thông tin dị ứng</h2>
        <div class="ld-card">
            ${allergenTags.length ? `<div style="display:flex;flex-wrap:wrap;gap:6px">${allergenTags.map(t => `<span class="ld-badge" style="background:var(--color-danger-tint);color:var(--color-danger)">Dị ứng: ${esc(t.name)}</span>`).join('')}</div>
                <p class="ld-note" style="margin-top:10px">Nhãn dị ứng suy từ thành phần theo dữ liệu dự án biên soạn — có thể chưa đầy đủ (gia vị, nước chấm, cách chế biến tại quán). Nếu bạn dị ứng nặng, hãy tự kiểm tra thành phần trước khi ăn. <a href="/mien-tru-y-te">Đọc Miễn trừ y tế</a>.</p>`
            : `<p class="ld-note" style="margin:0">Dữ liệu hiện tại chưa ghi nhận chất gây dị ứng phổ biến trong món này. Điều này không phải cam kết an toàn tuyệt đối — nếu bạn bị dị ứng, hãy kiểm tra kỹ thành phần. <a href="/mien-tru-y-te">Đọc Miễn trừ y tế</a>.</p>`}
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
        <div class="ld-grid-ing">
            ${(d.dish_ingredients ?? []).map(di => `<a href="/nguyen-lieu/${di.ingredient?.slug ?? ''}" class="ld-dcard">
                <img class="ld-dcard-img" src="${di.ingredient?.image?.url ?? '/images/placeholder-ingredient.svg'}" alt="${esc(di.ingredient?.name_vi ?? '')}" loading="lazy" decoding="async">
                <span class="ld-dcard-body">
                    <span class="ld-dcard-title">${esc(di.ingredient?.name_vi ?? '')}</span>
                    <span class="ld-dcard-meta ld-tnum">${esc(di.quantity ?? '')} ${esc(di.unit_name ?? '')}</span>
                    <span class="ld-dcard-badges">${di.is_optional ? '<span class="ld-badge">Tuỳ chọn</span>' : ''}</span>
                    <span class="ld-dcard-foot"><span class="ld-dcard-conf">${di.ingredient?.nutrition?.kcal_per_100g != null ? Math.round(di.ingredient.nutrition.kcal_per_100g) + ' kcal/100g' : ''}</span></span>
                </span>
            </a>`).join('')
              || '<p style="color:var(--color-faint);margin:0">Chưa có định lượng.</p>'}
        </div>

        ${d.recipe_steps?.length ? `
        <h2 class="ld-section-title">Cách làm</h2>
        <div class="ld-group">
            ${d.recipe_steps.map((s, i) => `<div class="ld-row" style="align-items:flex-start">
                <span style="flex:none;width:26px;height:26px;border-radius:50%;background:var(--color-accent);color:#fff;font-weight:700;font-size:14px;display:inline-flex;align-items:center;justify-content:center;margin-top:1px">${i + 1}</span>
                <span class="grow">${esc(s)}</span></div>`).join('')}
        </div>` : ''}
        ${d.needs_review ? '<div class="ld-warn" style="margin-top:18px"><span>Món đang trong giai đoạn biên soạn dữ liệu — thông tin có thể được cập nhật sau khi duyệt.</span></div>' : ''}`;
        if (window.ldRefreshHearts) window.ldRefreshHearts();
    })
    .catch(() => { document.getElementById('content').textContent = 'Không tải được món này.'; });
</script>
@endpush
