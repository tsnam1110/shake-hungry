@extends('layouts.app')

@section('title', 'Hồ sơ của tôi — Lắc Đói')

@section('content')
@guest
    {{-- Giao diện hồ sơ cho khách chưa đăng nhập (UI-016) --}}
    <h1 class="ld-title" style="margin-top:14px">Hồ sơ của bạn</h1>
    <p class="ld-subtitle">Đăng nhập để Lắc Đói nhớ khẩu vị, thể trạng và nhật ký ăn uống của bạn — gợi ý món vì thế sát với bạn hơn hẳn so với dùng khách.</p>

    <div class="ld-group" style="margin-top:20px">
        <div class="ld-row sep-icon">
            <span class="ld-tile orange"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="5" width="14" height="14" rx="3.5"/><circle cx="9.3" cy="9.3" r="1" fill="currentColor" stroke="none"/><circle cx="14.7" cy="14.7" r="1" fill="currentColor" stroke="none"/><circle cx="14.7" cy="9.3" r="1" fill="currentColor" stroke="none"/><circle cx="9.3" cy="14.7" r="1" fill="currentColor" stroke="none"/></svg></span>
            <span class="grow"><span style="font-weight:600">Lắc món theo khẩu vị của riêng bạn</span><span class="sub">Chế độ ăn, vùng miền, kcal mục tiêu mỗi bữa được lưu trong hồ sơ và tự điền sẵn mỗi lần lắc</span></span>
        </div>
        <div class="ld-row sep-icon">
            <span class="ld-tile red"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M5.8 5.8l12.4 12.4"/></svg></span>
            <span class="grow"><span style="font-weight:600">Lọc cứng những thứ bạn không ăn được</span><span class="sub">Khai báo dị ứng và thành phần kiêng một lần — hệ thống không bao giờ gợi ý món chứa chúng</span></span>
        </div>
        <div class="ld-row sep-icon">
            <span class="ld-tile blue"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V4H6.5A2.5 2.5 0 0 0 4 6.5v13Z"/><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/></svg></span>
            <span class="grow"><span style="font-weight:600">Nhật ký và thống kê ăn uống</span><span class="sub">Ghi lại bữa ăn và hoa quả đã ăn, xem tổng năng lượng theo ngày, tuần và những món bạn ăn nhiều nhất</span></span>
        </div>
        <div class="ld-row sep-icon">
            <span class="ld-tile green"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 20.5C7 16.5 3.8 13.3 3.8 9.6 3.8 7 5.7 5 8.1 5c1.6 0 3.1.9 3.9 2.3C12.8 5.9 14.3 5 15.9 5c2.4 0 4.3 2 4.3 4.6 0 3.7-3.2 6.9-8.2 10.9Z"/></svg></span>
            <span class="grow"><span style="font-weight:600">Yêu thích và đóng góp cho cộng đồng</span><span class="sub">Lưu món và quả yêu thích, đóng góp món mới vào kho dữ liệu chung</span></span>
        </div>
    </div>

    <div style="display:flex;gap:10px;margin-top:22px;flex-wrap:wrap">
        <a href="/tai-khoan" class="ld-btn ld-btn-primary" style="text-decoration:none">Đăng nhập</a>
        <a href="/tai-khoan" class="ld-btn ld-btn-gray" style="text-decoration:none">Tạo tài khoản miễn phí</a>
    </div>
    <p class="ld-note" style="margin-top:16px">Thông tin hồ sơ (năm sinh, giới tính, chiều cao, cân nặng) chỉ dùng để tính nhu cầu năng lượng và cá nhân hoá gợi ý cho chính bạn — cách chúng tôi xử lý dữ liệu được nêu rõ trong <a href="/chinh-sach-bao-mat" style="color:var(--color-link)">Chính sách bảo mật</a>. Bạn luôn có thể dùng Lắc Đói mà không cần tài khoản.</p>
@endguest

{{-- Cài đặt lắc món — dùng chung cho khách và tài khoản (SHAKE-DEVICE-001).
     Không cần đăng nhập: lựa chọn lưu trên thiết bị (localStorage, cùng khoá
     'lacdoi_shake_gesture' với trang Lắc món), mặc định luôn bật. --}}
<section>
    <h2 class="ld-section-title">Cài đặt lắc món</h2>
    <div class="ld-card">
        <div class="ld-row" style="padding:0">
            <span class="grow">Lắc máy để lắc món <span style="display:block;font-weight:400;color:var(--color-faint);font-size:13px;margin-top:1px">Bật lên rồi lắc nhẹ điện thoại — trang tự lắc một món cho bạn, khỏi bấm nút</span></span>
            <button class="ld-switch" type="button" role="switch" aria-checked="true" id="profile-shake-gesture" aria-label="Lắc máy để lắc món"></button>
        </div>
        <p class="ld-note" style="margin-top:10px">Cài đặt này lưu trên thiết bị của bạn, không cần đăng nhập, và áp dụng ngay ở trang Lắc món. Mặc định luôn bật — tắt đi nếu bạn chỉ muốn bấm nút. Trên iPhone, lần đầu dùng cần cho phép cảm biến chuyển động ở trang Lắc món.</p>
    </div>
</section>

@auth
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px">
        <div>
            <h1 class="ld-title" style="margin-top:14px">Hồ sơ</h1>
            <p id="whoami" class="ld-subtitle" style="font-size:15px"></p>
            <p style="margin-top:6px"><a href="/tuan-an" style="font-size:15px">Tuần ăn của bạn →</a></p>
        </div>
        <button id="logout" style="border:0;background:none;color:var(--color-danger);font-weight:600;font-size:15px;cursor:pointer;padding-top:22px;font-family:inherit">Đăng xuất</button>
    </div>

    {{-- Năng lượng (TDEE) — render bằng JS vào #tdee --}}
    <p class="ld-group-label">Năng lượng</p>
    <div id="tdee"></div>

    {{-- Nhật ký bữa ăn --}}
    <section id="nhat-ky" style="scroll-margin-top:80px">
        <div class="ld-section-head">
            <h2 class="ld-section-title">Nhật ký bữa ăn</h2>
            <span style="display:flex;align-items:center;gap:12px">
                <a href="/nhat-ky" style="font-size:14.5px">Trang Nhật ký đầy đủ</a>
                <input id="log-date" type="date" class="ld-input" style="width:auto;padding:8px 11px;font-size:14.5px">
            </span>
        </div>
        <div id="summary" style="font-size:15px;margin-bottom:12px"></div>
        <div class="ld-group" id="logs"></div>
        <div style="display:flex;gap:9px;margin-top:14px">
            <div class="ld-search" style="flex:1">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/></svg>
                <input id="manual-q" type="search" placeholder="Thêm món đã ăn: gõ tên món…" aria-label="Thêm món đã ăn">
            </div>
            <select id="manual-slot" class="ld-select" style="width:auto">
                <option value="sang">Sáng</option><option value="trua">Trưa</option><option value="chieu">Chiều</option><option value="toi">Tối</option><option value="an-vat">Ăn vặt</option>
            </select>
        </div>
        <div id="manual-results" class="ld-group" style="margin-top:10px"></div>
    </section>

    {{-- Thống kê ăn uống (UI-014) --}}
    <section id="thong-ke">
        <h2 class="ld-section-title">Thống kê ăn uống</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="stats-tiles"></div>
        <div class="grid sm:grid-cols-2 gap-4" style="margin-top:16px">
            <div>
                <p class="ld-group-label" style="margin-top:0">Món ăn nhiều nhất</p>
                <div class="ld-group" id="top-dishes"></div>
            </div>
            <div>
                <p class="ld-group-label" style="margin-top:0">Quả ăn nhiều nhất</p>
                <div class="ld-group" id="top-fruits"></div>
            </div>
        </div>
    </section>

    {{-- Yêu thích (UI-014) --}}
    <section id="yeu-thich">
        <h2 class="ld-section-title">Yêu thích</h2>
        <p class="ld-group-label" style="margin-top:0">Món ăn</p>
        <div class="ld-grid" id="fav-dishes"></div>
        <p class="ld-group-label">Hoa quả</p>
        <div class="ld-grid" id="fav-fruits"></div>
        <p class="ld-group-label">Nước uống</p>
        <div class="ld-grid" id="fav-drinks"></div>
    </section>

    {{-- Thể trạng & ăn uống --}}
    <section>
        <h2 class="ld-section-title">Thể trạng và ăn uống</h2>
        <form id="profile-form" class="ld-card">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="ld-field-label" for="f-height">Chiều cao (cm)</label>
                    <input id="f-height" name="height_cm" type="number" step="0.1" class="ld-input"></div>
                <div><label class="ld-field-label" for="f-weight">Cân nặng (kg)</label>
                    <input id="f-weight" name="weight_kg" type="number" step="0.1" class="ld-input"></div>
                <div><label class="ld-field-label" for="f-birth">Năm sinh</label>
                    <input id="f-birth" name="birth_year" type="number" class="ld-input"></div>
                <div><label class="ld-field-label" for="f-sex">Giới tính</label>
                    <select id="f-sex" name="sex" class="ld-select">
                        <option value="unspecified">Chưa rõ</option><option value="male">Nam</option><option value="female">Nữ</option>
                    </select></div>
                <div><label class="ld-field-label" for="f-act">Mức vận động</label>
                    <select id="f-act" name="activity_level" class="ld-select">
                        <option value="nhe">Ít / nhẹ (PAL 1,40–1,69)</option>
                        <option value="vua">Vừa (PAL 1,70–1,99)</option>
                        <option value="nang">Mạnh (PAL 2,00–2,40)</option>
                    </select></div>
                <div><label class="ld-field-label" for="f-diet">Chế độ ăn</label>
                    <select id="f-diet" name="diet_type" class="ld-select">
                        <option value="">Ăn thường</option><option value="chay">Ăn chay</option>
                        <option value="chay-truong">Chay trường (thuần chay)</option><option value="low-carb">Low-carb</option>
                    </select></div>
                <div><label class="ld-field-label" for="f-region">Vùng miền yêu thích (điền sẵn khi lắc)</label>
                    <select id="f-region" name="preferred_region_id" class="ld-select">
                        <option value="">Mọi vùng miền</option>
                    </select></div>
                <div><label class="ld-field-label" for="f-target">Kcal mục tiêu mỗi bữa (điền sẵn khi lắc)</label>
                    <input id="f-target" name="target_kcal_per_meal" type="number" min="100" max="2000" placeholder="Ví dụ: 600" class="ld-input"></div>
                {{-- WEEK-001, Phần 2.1, Task 3: mục tiêu tuần ăn. Chỉ là form
                     nhập + hiển thị gợi ý từ API Task 2; chưa có trang tuần ăn. --}}
                <div class="sm:col-span-2"><p class="ld-group-label" style="margin-top:0">Mục tiêu tuần ăn</p></div>
                <div><label class="ld-field-label" for="f-goal">Mục tiêu</label>
                    <select id="f-goal" name="goal" class="ld-select">
                        <option value="">Chưa chọn</option><option value="giam">Giảm mỡ</option>
                        <option value="duy-tri">Duy trì</option><option value="tang">Tăng cơ</option>
                    </select></div>
                <div><label class="ld-field-label" for="f-target-protein">Protein mục tiêu mỗi ngày (g)</label>
                    <input id="f-target-protein" name="target_protein_g_per_day" type="number" min="20" max="500" placeholder="Ví dụ: 175" class="ld-input"></div>
                <div><label class="ld-field-label" for="f-target-kcal-day">Kcal mục tiêu mỗi ngày</label>
                    <input id="f-target-kcal-day" name="target_kcal_per_day" type="number" min="800" max="6000" placeholder="Ví dụ: 2000" class="ld-input"></div>
                <div><label class="ld-field-label" for="f-shake-mode">Chế độ lắc mặc định</label>
                    <select id="f-shake-mode" name="shake_mode" class="ld-select">
                        <option value="">Ăn ngoài · 1 món</option>
                        <option value="home">Tự nấu · Mâm cơm</option>
                    </select></div>
                <div><label class="ld-field-label" for="f-tray-count">Số món trong mâm cơm (không tính cơm)</label>
                    <select id="f-tray-count" name="tray_dish_count" class="ld-select">
                        <option value="">3 món — mâm chuẩn</option>
                        <option value="1">1 món</option>
                        <option value="2">2 món</option>
                        <option value="3">3 món — mâm chuẩn</option>
                        <option value="4">4 món</option>
                    </select></div>
                <div class="sm:col-span-2">
                    <label style="display:flex;gap:10px;align-items:flex-start;cursor:pointer">
                        <input type="checkbox" id="f-binh-dan" name="tray_binh_dan" style="margin-top:3px;width:17px;height:17px;accent-color:#0071e3">
                        <span><span style="font-weight:600">Bữa cơm bình dân mặc định cho mâm cơm</span>
                        <span class="ld-note" style="display:block;margin-top:2px">Khi lắc mâm cơm, chỉ gợi ý món dễ nấu với nguyên liệu phổ biến dễ kiếm — như bữa cơm nhà hằng ngày.</span></span>
                    </label>
                </div>
                <div class="sm:col-span-2">
                    <label style="display:flex;gap:10px;align-items:flex-start;cursor:pointer">
                        <input type="checkbox" id="f-show-tcm" name="show_tcm" style="margin-top:3px;width:17px;height:17px;accent-color:#0071e3">
                        <span><span style="font-weight:600">Hiển thị tính vị theo đông y</span>
                        <span class="ld-note" style="display:block;margin-top:2px">Hiện mục Tính / Vị / Ngũ hành (quan niệm ẩm thực truyền thống) ở trang món, nguyên liệu và hoa quả. Chỉ là lớp thông tin văn hoá — không ảnh hưởng gợi ý món.</span></span>
                    </label>
                </div>
                <div class="sm:col-span-2"><span class="ld-field-label">Không ăn được / dị ứng — lọc cứng khi lắc</span>
                    <div class="ld-chips" id="excluded-chips"></div>
                    <p class="ld-note" style="margin-top:8px">Chạm để bật hoặc tắt từng mục. Cấu hình này dùng chung cho khối Lắc ở trang chủ và không bao giờ bị hệ thống tự nới lỏng.</p></div>
                {{-- WEEK-001, Task 3: gợi ý protein do API trả về; nút "Dùng gợi
                     ý này" chỉ điền vào ô protein, không tự lưu, không ghi đè
                     số đã lưu cho tới khi người dùng bấm "Lưu hồ sơ". --}}
                <div class="sm:col-span-2">
                    <span class="ld-field-label">Gợi ý protein cho tuần ăn</span>
                    <div id="week-target-suggestion" class="ld-note" style="margin-top:2px">Nhập chiều cao và kcal mục tiêu mỗi ngày rồi lưu hồ sơ để xem gợi ý protein.</div>
                    <p class="ld-note" style="margin-top:8px">Mục tiêu giảm / duy trì / tăng hiện chỉ được lưu, chưa dùng để tự tính kcal mục tiêu — tỉ lệ điều chỉnh kcal theo mục tiêu chưa được chốt. Gợi ý protein không tự ghi đè số bạn đã nhập. Mục tiêu chỉ là ước tính chung, không phải lời khuyên y tế — xem <a href="/mien-tru-y-te" style="color:var(--color-link)">Miễn trừ y tế</a>.</p>
                </div>
            </div>
            <button class="ld-btn ld-btn-primary" style="width:100%;margin-top:18px">Lưu hồ sơ</button>
        </form>
    </section>

    {{-- Đóng góp cộng đồng --}}
    <section id="dong-gop" style="scroll-margin-top:80px">
        <h2 class="ld-section-title">Đóng góp cho cộng đồng</h2>
        <div class="ld-card">
            <p style="color:var(--color-sub);font-size:15px;margin:0 0 14px">Đề xuất món mới, báo sửa món, hoặc giới thiệu quán ăn — mọi đề xuất đều qua duyệt mới hiển thị.</p>
            <form id="contrib-form" style="display:flex;flex-direction:column;gap:11px">
                <select name="type" class="ld-select">
                    <option value="new_dish">Đề xuất món mới</option>
                    <option value="fix_dish">Báo sửa thông tin món</option>
                    <option value="new_place">Giới thiệu quán ăn</option>
                </select>
                <input name="title" required placeholder="Tên món / quán" class="ld-input">
                <textarea name="detail" rows="3" placeholder="Chi tiết: nguyên liệu, cách làm, địa chỉ quán…" class="ld-input"></textarea>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                    <button class="ld-btn ld-btn-primary" style="padding:11px 22px;font-size:15.5px">Gửi đề xuất</button>
                    <span id="contrib-msg" style="font-size:14.5px"></span>
                </div>
            </form>
        </div>
    </section>

    {{-- Phản hồi của tôi (UI-018) --}}
    <section>
        <h2 class="ld-section-title">Phản hồi của tôi</h2>
        <div class="ld-group" id="my-feedback"></div>
        <p class="ld-note" style="margin-top:10px">Gửi phản hồi mới bằng nút "Gửi phản hồi" ở trang chi tiết của từng món, quả hoặc nguyên liệu. Mọi ticket đều bắt đầu ở trạng thái "Đã ghi nhận · Chờ duyệt".</p>
    </section>
@endauth
@endsection

@push('scripts')
@auth
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
function esc(s) { return (s ?? '').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
{{-- FIX-A1 (AUDIT-ALLERGEN): dùng chung danh sách sinh từ TaggingRules với trang Lắc (AllergenPreset). --}}
const ALLERGEN_PRESETS = @json(\App\Taxonomy\AllergenPreset::chips());
const excludedChips = document.getElementById('excluded-chips');
const selectedPresets = new Set();
excludedChips.innerHTML = ALLERGEN_PRESETS.map(([l], i) => `<button type="button" class="ld-chip allergen" data-i="${i}">${l}</button>`).join('');
excludedChips.querySelectorAll('button').forEach(b => b.onclick = () => {
    const i = +b.dataset.i;
    const on = b.classList.toggle('active');
    on ? selectedPresets.add(i) : selectedPresets.delete(i);
});

async function init() {
    const me = await (await fetch('/api/me')).json();
    if (!me.user) { location.href = '/tai-khoan'; return; }
    document.getElementById('whoami').textContent = `${me.user.name} · ${me.user.email}`;
    const regions = await (await fetch('/api/regions')).json().catch(() => []);
    const f0 = document.getElementById('profile-form');
    f0.elements['preferred_region_id'].innerHTML = '<option value="">Mọi vùng miền</option>' +
        regions.map(r => `<option value="${r.id}">${esc(r.name)}</option>`).join('');
    const { profile, tdee, week_target_suggestion } = await (await fetch('/api/profile')).json();
    if (profile) {
        const f = document.getElementById('profile-form');
        ['height_cm','weight_kg','birth_year','sex','activity_level','diet_type','preferred_region_id','target_kcal_per_meal','shake_mode','tray_dish_count','goal','target_protein_g_per_day','target_kcal_per_day'].forEach(k => { if (profile[k] != null && f.elements[k]) f.elements[k].value = profile[k]; });
        if (f.elements['tray_binh_dan']) f.elements['tray_binh_dan'].checked = !!profile.tray_binh_dan;
        if (f.elements['show_tcm']) f.elements['show_tcm'].checked = profile.show_tcm !== false && profile.show_tcm !== 0;
        const ex = new Set(profile.excluded_ingredients || []);
        excludedChips.querySelectorAll('button').forEach(b => {
            const i = +b.dataset.i;
            if (ALLERGEN_PRESETS[i][1].some(s => ex.has(s))) { b.classList.add('active'); selectedPresets.add(i); }
        });
    }
    renderTdee(tdee);
    renderWeekSuggestion(week_target_suggestion);
    document.getElementById('log-date').value = new Date().toISOString().slice(0, 10);
    loadLogs();
}
function renderTdee(tdee) {
    document.getElementById('tdee').innerHTML = tdee
        ? `<div class="ld-card" style="padding:18px 20px">
             <div style="display:flex;align-items:baseline;gap:9px;flex-wrap:wrap">
               <span class="ld-tnum" style="font-size:27px;font-weight:800;letter-spacing:-.02em">${tdee.tdee_min.toLocaleString('vi-VN')} – ${tdee.tdee_max.toLocaleString('vi-VN')}</span>
               <span style="color:var(--color-sub)">kcal mỗi ngày, theo hồ sơ thể trạng của bạn</span>
             </div>
             <p class="ld-note" style="margin-top:8px">BMR ≈ ${tdee.bmr} kcal · ${esc(tdee.formula)}. ${esc(tdee.note)}</p>
           </div>`
        : '<div class="ld-card" style="padding:18px 20px"><p style="color:var(--color-faint);margin:0">Điền cân nặng và năm sinh ở mục Thể trạng để xem nhu cầu năng lượng.</p></div>';
}
/* WEEK-001, Task 3: hiển thị gợi ý protein từ API Task 2. Gợi ý là null
   khi thiếu chiều cao hoặc thiếu kcal mục tiêu mỗi ngày — khi đó giữ
   nguyên câu hướng dẫn, không tự suy số từ TDEE hay cân nặng thật. */
function renderWeekSuggestion(s) {
    const el = document.getElementById('week-target-suggestion');
    if (!el) return;
    if (!s) {
        el.textContent = 'Nhập chiều cao và kcal mục tiêu mỗi ngày rồi lưu hồ sơ để xem gợi ý protein. Gợi ý chỉ tính khi đã có cả hai số này.';
        return;
    }
    el.innerHTML = `Gợi ý: <b>${s.suggested_g} g protein/ngày</b> — ${esc(s.steps)} ` +
        `<button type="button" id="week-use-suggestion" class="ld-btn ld-btn-gray" style="padding:7px 14px;font-size:14px;margin-top:8px">Dùng gợi ý này</button>`;
    const btn = document.getElementById('week-use-suggestion');
    if (btn) btn.onclick = () => {
        const f = document.getElementById('f-target-protein');
        if (f) f.value = s.suggested_g;
        if (window.ldToast) ldToast('Đã điền gợi ý vào ô protein — bấm Lưu hồ sơ để áp dụng');
    };
}
document.getElementById('profile-form').onsubmit = async (e) => {
    e.preventDefault();
    const raw = Object.fromEntries(new FormData(e.target).entries());
    const body = {};
    ['height_cm','weight_kg','birth_year','preferred_region_id','target_kcal_per_meal','tray_dish_count','target_protein_g_per_day','target_kcal_per_day'].forEach(k => body[k] = raw[k] === '' || raw[k] == null ? null : +raw[k]);
    ['sex','activity_level','diet_type','shake_mode','goal'].forEach(k => body[k] = raw[k] === '' ? null : raw[k]);
    body.tray_binh_dan = raw.tray_binh_dan === 'on';
    body.show_tcm = raw.show_tcm === 'on';
    body.excluded_ingredients = [...selectedPresets].flatMap(i => ALLERGEN_PRESETS[i][1]);
    const resp = await fetch('/api/profile', { method: 'PUT', headers, body: JSON.stringify(body) });
    const data = await resp.json();
    if (resp.ok) { renderTdee(data.tdee); renderWeekSuggestion(data.week_target_suggestion); ldToast('Đã lưu hồ sơ'); } else alert(data.message || 'Lưu thất bại');
};
const slotName = { sang: 'Sáng', trua: 'Trưa', chieu: 'Chiều', toi: 'Tối', 'an-vat': 'Ăn vặt' };
async function loadLogs() {
    const date = document.getElementById('log-date').value;
    const { logs, summary } = await (await fetch('/api/meal-logs?date=' + date)).json();
    document.getElementById('summary').innerHTML =
        `Tổng hôm nay: <b class="ld-tnum">${summary.kcal_min} – ${summary.kcal_max} kcal</b> từ ${summary.dishes_counted} món có dữ liệu` +
        (summary.dishes_unknown ? ` · <span style="color:var(--color-warn-ink)">${summary.dishes_unknown} món chưa đủ dữ liệu nên chưa tính vào tổng</span>` : '');
    document.getElementById('logs').innerHTML = logs.map(l => {
        const n = l.dish?.nutrition;
        const name = l.dish?.names?.[0]?.name ?? '?';
        return `<div class="ld-row sep-icon">
            <span class="ld-tile blue">${esc(name.trim().charAt(0).toUpperCase())}</span>
            <span class="grow"><span style="font-weight:600">${esc(name)}</span><span class="sub">${slotName[l.slot] ?? l.slot}</span></span>
            ${n ? `<span class="ld-price"><span class="so ld-tnum">${Math.round(n.kcal_min)}–${Math.round(n.kcal_max)}</span><small>kcal</small></span>`
                 : `<span class="ld-price"><span class="so" style="color:var(--color-faint);font-weight:600">Chưa đủ dữ liệu</span></span>`}
            <button data-del="${l.id}" style="border:0;background:none;color:var(--color-danger);font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit">Xoá</button>
        </div>`;
    }).join('') || '<p style="color:var(--color-faint);padding:16px;margin:0">Chưa ghi bữa nào.</p>';
    document.querySelectorAll('[data-del]').forEach(b => b.onclick = async () => {
        await fetch('/api/meal-logs/' + b.dataset.del, { method: 'DELETE', headers });
        loadLogs();
    });
}
document.getElementById('log-date').onchange = loadLogs;
let mq;
document.getElementById('manual-q').addEventListener('input', (e) => {
    clearTimeout(mq);
    mq = setTimeout(async () => {
        const q = e.target.value.trim(); if (!q) { document.getElementById('manual-results').innerHTML = ''; return; }
        const data = await (await fetch('/api/dishes?q=' + encodeURIComponent(q))).json();
        document.getElementById('manual-results').innerHTML = data.data.slice(0, 6).map(d =>
            `<button data-add="${d.id}" class="ld-row" style="cursor:pointer"><span class="grow">+ ${esc(d.names?.[0]?.name ?? d.slug)}</span></button>`).join('');
        document.querySelectorAll('[data-add]').forEach(b => b.onclick = async () => {
            await fetch('/api/meal-logs', { method: 'POST', headers, body: JSON.stringify({
                dish_id: +b.dataset.add, slot: document.getElementById('manual-slot').value, source: 'manual',
                eaten_at: document.getElementById('log-date').value + 'T12:00:00',
            })});
            ldToast('Đã thêm món vào nhật ký');
            loadLogs();
        });
    }, 350);
});
document.getElementById('contrib-form').onsubmit = async (e) => {
    e.preventDefault();
    const body = Object.fromEntries(new FormData(e.target).entries());
    const resp = await fetch('/api/contributions', { method: 'POST', headers, body: JSON.stringify(body) });
    document.getElementById('contrib-msg').textContent = resp.ok ? 'Đã gửi — cảm ơn bạn! Đề xuất đang chờ duyệt.' : 'Gửi thất bại, thử lại sau.';
    if (resp.ok) e.target.reset();
};
document.getElementById('logout').onclick = async () => { await fetch('/api/logout', { method: 'POST', headers }); location.href = '/'; };

/* ===== Thống kê + Yêu thích (UI-014) ===== */
const heartSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"><path d="M12 20.5C7 16.5 3.8 13.3 3.8 9.6 3.8 7 5.7 5 8.1 5c1.6 0 3.1.9 3.9 2.3C12.8 5.9 14.3 5 15.9 5c2.4 0 4.3 2 4.3 4.6 0 3.7-3.2 6.9-8.2 10.9Z"/></svg>';
const confShortP = { high: 'Tin cậy cao', medium: 'Tin cậy TB', low: 'Tin cậy thấp' };
async function loadStats() {
    try {
        const s = await (await fetch('/api/stats')).json();
        const tile = (v, l) => `<div class="ld-card" style="padding:16px;text-align:center"><div class="ld-tnum" style="font-size:26px;font-weight:800">${v}</div><div style="font-size:13px;color:var(--color-faint);margin-top:2px">${l}</div></div>`;
        document.getElementById('stats-tiles').innerHTML =
            tile(s.meals_total, 'Bữa đã ghi') + tile(s.dishes_distinct, 'Món khác nhau đã ăn') +
            tile(s.fruits_total, 'Lần ăn quả') + tile(s.fruits_distinct, 'Loại quả đã ăn');
        const topRow = (t, href) => `<a class="ld-row" href="${href}"><span class="grow" style="font-weight:600">${esc(t.name ?? '')}</span><span class="ld-badge vu">× ${t.count}</span></a>`;
        document.getElementById('top-dishes').innerHTML = (s.top_dishes ?? []).map(t => topRow(t, '/mon/' + t.slug)).join('')
            || '<p style="color:var(--color-faint);padding:14px 16px;margin:0">Chưa ghi bữa nào.</p>';
        document.getElementById('top-fruits').innerHTML = (s.top_fruits ?? []).map(t => topRow(t, '/hoa-qua/' + t.slug)).join('')
            || '<p style="color:var(--color-faint);padding:14px 16px;margin:0">Chưa ghi quả nào — bấm "Đã ăn quả này" ở trang quả.</p>';
    } catch { /* khối phụ */ }
}
async function loadFavorites() {
    try {
        const { dishes, fruits, drinks } = await (await fetch('/api/favorites')).json();
        document.getElementById('fav-dishes').innerHTML = (dishes ?? []).map(d => `
            <a href="/mon/${d.slug}" class="ld-dcard">
                <img class="ld-dcard-img" src="/images/placeholder-dish.svg" alt="" loading="lazy" decoding="async">
                <span class="ld-heart" data-fav-dish="${d.id}" role="button" aria-label="Bỏ yêu thích">${heartSvg}</span>
                <span class="ld-dcard-body">
                    <span class="ld-dcard-title">${esc(d.name ?? d.slug)}</span>
                    <span class="ld-dcard-meta">${esc(d.region ?? '')}</span>
                    <span class="ld-dcard-badges"></span>
                    <span class="ld-dcard-foot">${d.nutrition
                        ? `<span class="ld-dcard-kcal ld-tnum">${Math.round(d.nutrition.kcal_min)}–${Math.round(d.nutrition.kcal_max)} kcal</span><span class="ld-dcard-conf">${confShortP[d.nutrition.confidence] ?? ''}</span>`
                        : `<span class="ld-dcard-kcal none">Chưa đủ dữ liệu</span><span class="ld-dcard-conf">không hiện số</span>`}</span>
                </span>
            </a>`).join('') || '<p class="ld-note" style="margin:0">Chưa có món yêu thích — bấm biểu tượng tim trên thẻ món để lưu.</p>';
        document.getElementById('fav-drinks').innerHTML = (drinks ?? []).map(dr => `
            <a href="/do-uong/${dr.slug}" class="ld-dcard">
                <img class="ld-dcard-img" src="/images/placeholder-drink.svg" alt="" loading="lazy" decoding="async">
                <span class="ld-heart" data-fav-drink="${dr.id}" role="button" aria-label="Bỏ yêu thích">${heartSvg}</span>
                <span class="ld-dcard-body">
                    <span class="ld-dcard-title">${esc(dr.name_vi ?? dr.slug)}</span>
                    <span class="ld-dcard-meta">${esc(dr.drink_group ?? '')}${dr.kind ? ' · ' + (dr.kind === 'pha-che' ? 'Pha chế' : 'Đóng gói') : ''}</span>
                    <span class="ld-dcard-badges">${dr.alcohol ? '<span class="ld-badge" style="background:var(--color-danger-tint);color:var(--color-danger)">18+</span>' : ''}</span>
                    <span class="ld-dcard-foot"><span style="font-size:13px;color:var(--color-sub)">${dr.temperature ? esc(dr.temperature) : ''}</span></span>
                </span>
            </a>`).join('') || '<p class="ld-note" style="margin:0">Chưa có đồ uống yêu thích — bấm biểu tượng tim trên thẻ nước uống để lưu.</p>';
        document.getElementById('fav-fruits').innerHTML = (fruits ?? []).map(f => `
            <a href="/hoa-qua/${f.slug}" class="ld-dcard">
                <img class="ld-dcard-img" src="/images/placeholder-fruit.svg" alt="" loading="lazy" decoding="async">
                <span class="ld-heart" data-fav-fruit="${f.id}" role="button" aria-label="Bỏ yêu thích">${heartSvg}</span>
                <span class="ld-dcard-body">
                    <span class="ld-dcard-title">${esc(f.name_vi ?? f.slug)}</span>
                    <span class="ld-dcard-meta">${f.kcal_per_100g != null ? Math.round(f.kcal_per_100g) + ' kcal/100g' : 'Chưa có số calo'}</span>
                    <span class="ld-dcard-badges"></span>
                    <span class="ld-dcard-foot"><span class="ld-seasonbar" aria-hidden="true">${Array.from({length: 12}, (_, i) =>
                        `<i class="${(f.main_season_months ?? []).includes(i + 1) ? 'main' : (f.availability_months ?? []).includes(i + 1) ? 'on' : ''}"></i>`).join('')}</span></span>
                </span>
            </a>`).join('') || '<p class="ld-note" style="margin:0">Chưa có quả yêu thích.</p>';
        if (window.ldRefreshHearts) window.ldRefreshHearts();
    } catch { /* khối phụ */ }
}
document.addEventListener('fav-changed', () => loadFavorites());

/* ===== Phản hồi của tôi (UI-018) ===== */
const fbStatusStyle = { 'da-ghi-nhan': '', 'da-duyet': 'vu', 'dang-xu-ly': 'hoi', 'da-xu-ly': 'chay', 'da-dong': '' };
async function loadFeedback() {
    try {
        const tickets = await (await fetch('/api/feedback')).json();
        document.getElementById('my-feedback').innerHTML = (tickets ?? []).map(t => `
            <div class="ld-row">
                <span class="grow">
                    <span style="font-weight:600">${esc(t.title || t.content.slice(0, 60))}</span>
                    <span class="sub">${esc(t.type_label)}${t.context_name ? ' · Về: ' + esc(t.context_name) : ''} · ${new Date(t.created_at).toLocaleDateString('vi-VN')}</span>
                </span>
                <span class="ld-badge ${fbStatusStyle[t.status] ?? ''}">${esc(t.status_label)}</span>
            </div>`).join('') || '<p style="color:var(--color-faint);padding:16px;margin:0">Bạn chưa gửi phản hồi nào.</p>';
    } catch { /* khối phụ */ }
}
document.addEventListener('feedback-sent', () => loadFeedback());
init().then(() => { loadStats(); loadFavorites(); loadFeedback(); });
</script>
@endauth
@endpush

{{-- Công tắc lắc máy ở Hồ sơ — chạy cho cả khách lẫn tài khoản (SHAKE-DEVICE-001).
     Chỉ lưu lựa chọn vào localStorage, không gọi API, không cần đăng nhập. --}}
@push('scripts')
<script>
(function () {
    const btn = document.getElementById('profile-shake-gesture');
    if (!btn) return;
    const KEY = 'lacdoi_shake_gesture';
    // Mặc định luôn bật: chỉ khi đã lưu '0' mới là tắt.
    const on = localStorage.getItem(KEY) !== '0';
    btn.setAttribute('aria-checked', on ? 'true' : 'false');
    btn.addEventListener('click', () => {
        const next = btn.getAttribute('aria-checked') !== 'true';
        btn.setAttribute('aria-checked', next ? 'true' : 'false');
        localStorage.setItem(KEY, next ? '1' : '0');
        if (window.ldToast) ldToast(next ? 'Đã bật: lắc nhẹ máy để lắc món' : 'Đã tắt lắc máy — bấm nút để lắc món');
    });
})();
</script>
@endpush
