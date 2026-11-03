<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lắc Đói — Hôm nay ăn gì?')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-paper text-ink font-sans antialiased">
@php
    $path = request()->path();
    $user = auth()->user();
    $initials = $user ? collect(explode(' ', trim($user->name)))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('') : '';
    $navItems = [
        ['gợi ý', '/', 'Lắc món', 'dice', $path === '/'],
        ['gợi ý', '/thu-vien', 'Thư viện', 'search', str_starts_with($path, 'thu-vien') || str_starts_with($path, 'nguyen-lieu')],
        ['gợi ý', '/hoa-qua', 'Hoa quả theo mùa', 'fruit', str_starts_with($path, 'hoa-qua')],
        ['gợi ý', '/rau-mua', 'Rau theo mùa', 'leaf', str_starts_with($path, 'rau-mua')],
        ['gợi ý', '/do-uong', 'Thư viện nước uống', 'bowl', str_starts_with($path, 'do-uong')],
        ['gợi ý', '/ket-hop-can-luu-y', 'Kết hợp cần lưu ý', 'leaf', str_starts_with($path, 'ket-hop-can-luu-y')],
        ['của bạn', '/nhat-ky', 'Nhật ký bữa ăn', 'book', $path === 'nhat-ky'],
        ['của bạn', '/ho-so', 'Hồ sơ của tôi', 'person', $path === 'ho-so'],
        ['của bạn', '/ho-so#dong-gop', 'Đóng góp cộng đồng', 'heart', false],
    ];
    $icons = [
        'dice' => '<rect x="4" y="4" width="16" height="16" rx="4.5"/><circle cx="9.2" cy="9.2" r="1.25" fill="currentColor" stroke="none"/><circle cx="14.8" cy="14.8" r="1.25" fill="currentColor" stroke="none"/><circle cx="14.8" cy="9.2" r="1.25" fill="currentColor" stroke="none"/><circle cx="9.2" cy="14.8" r="1.25" fill="currentColor" stroke="none"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/>',
        'fruit' => '<path d="M12 8.2C10.8 6.6 8.9 6 7.2 6.8 4.9 7.9 4.2 11 5.4 14.6c1 3 2.5 4.9 4.2 4.3.8-.3 1.6-.3 2.4 0 1.7.6 3.2-1.3 4.2-4.3 1.2-3.6.5-6.7-1.8-7.8C15.1 6 13.2 6.6 12 8.2Z"/><path d="M12 8c0-2 .9-3.4 2.6-4"/>',
        'leaf' => '<path d="M11 20A7 7 0 0 1 4 13c0-5 4-9 16-9 0 12-5 16-9 16Z"/><path d="M4 21c4-6 8-9 12-11"/>',
        'book' => '<path d="M6 3.8h12V20a.8.8 0 0 1-.8.8H6.8A.8.8 0 0 1 6 20Z"/><path d="M9.5 8.5h5M9.5 12h5"/>',
        'person' => '<circle cx="12" cy="8" r="3.8"/><path d="M5 20.2c1.3-3.5 3.9-5.2 7-5.2s5.7 1.7 7 5.2"/>',
        'heart' => '<path d="M12 20.5C7 16.5 3.8 13.3 3.8 9.6 3.8 7 5.7 5 8.1 5c1.6 0 3.1.9 3.9 2.3C12.8 5.9 14.3 5 15.9 5c2.4 0 4.3 2 4.3 4.6 0 3.7-3.2 6.9-8.2 10.9Z"/>',
        'bowl' => '<path d="M4 12h16a8 8 0 0 1-16 0Z"/><path d="M9 8c1-1.6 4.5-1.8 6-.5"/><path d="M15.5 5.5c1.2-1 2.7-1.3 4-1"/>',
    ];
@endphp

@if (empty($hideNav))
<div class="ld-shell">
    <aside class="ld-sidebar">
        <a class="ld-brand" href="/">
            <span class="ld-app-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.9" stroke-linecap="round">{!! $icons['bowl'] !!}</svg>
            </span>
            <span><span class="ld-brand-name">Lắc Đói</span><br><span class="ld-brand-sub">Hôm nay ăn gì?</span></span>
        </a>
        <nav>
            @foreach (['gợi ý' => 'Gợi ý', 'của bạn' => 'Của bạn'] as $groupKey => $groupLabel)
                <div class="ld-nav-group">
                    <p class="ld-nav-title">{{ $groupLabel }}</p>
                    @foreach ($navItems as [$g, $href, $label, $icon, $active])
                        @if ($g === $groupKey)
                            <a class="ld-nav-item {{ $active ? 'active' : '' }}" href="{{ $href }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$icon] !!}</svg>
                                {{ $label }}
                            </a>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </nav>
        @auth
            <a class="ld-side-user" href="/ho-so">
                <span class="ld-avatar" style="width:40px;height:40px;font-size:15px">{{ $initials }}</span>
                <span style="flex:1;min-width:0">
                    <span style="display:block;font-size:14.5px;font-weight:600;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $user->name }}</span>
                    <span style="display:block;font-size:12.5px;color:var(--color-faint);line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $user->email }}</span>
                </span>
            </a>
        @else
            <div class="ld-side-user" style="cursor:default">
                <span style="flex:1;min-width:0;font-size:13.5px;color:var(--color-sub)">Đang dùng với tư cách khách.<br>Lắc món vẫn thoải mái.</span>
                <a class="ld-btn ld-btn-primary" style="padding:8px 16px;font-size:14px" href="/tai-khoan">Đăng nhập</a>
            </div>
        @endauth
    </aside>

    <header class="ld-topbar">
        <a href="/" style="display:flex;align-items:center;gap:9px;text-decoration:none;color:var(--color-ink)">
            <span class="ld-app-icon" style="width:32px;height:32px;border-radius:8px" aria-hidden="true">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.9" stroke-linecap="round">{!! $icons['bowl'] !!}</svg>
            </span>
            <span style="font-size:17.5px;font-weight:700;letter-spacing:-.02em">Lắc Đói</span>
        </a>
        @auth
            <a href="/ho-so" aria-label="Mở hồ sơ"><span class="ld-avatar" style="width:34px;height:34px;font-size:13px">{{ $initials }}</span></a>
        @else
            <a href="/tai-khoan" style="color:var(--color-link);font-weight:600;font-size:15px;text-decoration:none">Đăng nhập</a>
        @endauth
    </header>

    <main class="ld-main">
        <div class="@yield('page_class', 'ld-page')">
            @yield('content')
            <footer class="ld-footer">
                <nav>
                    <a href="/dieu-khoan">Điều khoản sử dụng</a>
                    <a href="/chinh-sach-bao-mat">Chính sách bảo mật</a>
                    <a href="/mien-tru-y-te">Miễn trừ y tế và dinh dưỡng</a>
                    <a href="/nguon-du-lieu">Nguồn dữ liệu và giấy phép</a>
                </nav>
                <p>Số calo trên Lắc Đói luôn là khoảng ước tính từ engine của dự án, kèm độ tin cậy và nguồn gốc con số — món chưa đủ dữ liệu sẽ không hiện số. Thông tin dinh dưỡng chỉ mang tính tham khảo.</p>
                <p>© 2026 Lắc Đói — mã nguồn MIT, dữ liệu tự biên soạn CC BY 4.0.</p>
            </footer>
        </div>
    </main>
</div>

<nav class="ld-tabbar" aria-label="Điều hướng chính">
    <a href="/" class="{{ $path === '/' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">{!! $icons['dice'] !!}</svg>Lắc món</a>
    <a href="/thu-vien" class="{{ str_starts_with($path, 'thu-vien') || str_starts_with($path, 'nguyen-lieu') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">{!! $icons['search'] !!}</svg>Thư viện</a>
    <a href="/hoa-qua" class="{{ str_starts_with($path, 'hoa-qua') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons['fruit'] !!}</svg>Hoa quả</a>
    <a href="/nhat-ky" class="{{ $path === 'nhat-ky' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">{!! $icons['book'] !!}</svg>Nhật ký</a>
    <a href="/ho-so" class="{{ $path === 'ho-so' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">{!! $icons['person'] !!}</svg>Tôi</a>
</nav>
@else
<main style="min-height:100vh;display:flex;align-items:flex-start;justify-content:center;padding:28px 18px 60px">
    <div style="width:100%;max-width:430px">
        @yield('content')
        <footer class="ld-footer" style="text-align:center">
            <nav style="justify-content:center">
                <a href="/dieu-khoan">Điều khoản</a>
                <a href="/chinh-sach-bao-mat">Bảo mật</a>
                <a href="/mien-tru-y-te">Miễn trừ y tế</a>
                <a href="/nguon-du-lieu">Nguồn dữ liệu</a>
            </nav>
            <p>© 2026 Lắc Đói.</p>
        </footer>
    </div>
</main>
@endif

{{-- Hộp thoại gửi phản hồi (UI-018) — nút mở mang thuộc tính data-feedback --}}
<div class="ld-sheet-overlay" id="fb-overlay">
    <div class="ld-sheet" role="dialog" aria-modal="true" aria-label="Gửi phản hồi">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
            <div>
                <h2 style="margin:0;font-size:20px;font-weight:800;letter-spacing:-.01em">Gửi phản hồi</h2>
                <p id="fb-context" style="margin:4px 0 0;color:var(--color-sub);font-size:14px"></p>
            </div>
            <button type="button" id="fb-close" aria-label="Đóng" style="border:0;background:var(--color-fill);width:32px;height:32px;border-radius:50%;font-size:15px;cursor:pointer;color:var(--color-sub)">✕</button>
        </div>
        <div id="fb-guest" style="display:none;margin-top:16px">
            <p style="color:var(--color-sub);font-size:15px;line-height:1.55;margin:0">Chỉ người dùng đã đăng nhập mới gửi được phản hồi, để hệ thống ghi nhận đúng ticket và bạn theo dõi được trạng thái xử lý trong hồ sơ.</p>
            <a href="/tai-khoan" class="ld-btn ld-btn-primary" style="text-decoration:none;margin-top:14px">Đăng nhập để gửi phản hồi</a>
        </div>
        <form id="fb-form" style="margin-top:16px">
            <span class="ld-field-label">Loại phản hồi</span>
            <div class="ld-chips" id="fb-types" style="margin-bottom:14px">
                <button type="button" class="ld-chip active" data-type="gop-y">Góp ý</button>
                <button type="button" class="ld-chip" data-type="ho-tro">Hỗ trợ</button>
                <button type="button" class="ld-chip" data-type="phat-trien">Phát triển</button>
                <button type="button" class="ld-chip" data-type="loi">Báo lỗi</button>
            </div>
            <label class="ld-field-label" for="fb-title">Tiêu đề (tuỳ chọn)</label>
            <input id="fb-title" class="ld-input" maxlength="160" placeholder="Tóm tắt ngắn gọn" style="margin-bottom:12px">
            <label class="ld-field-label" for="fb-content">Nội dung</label>
            <textarea id="fb-content" class="ld-input" rows="5" maxlength="5000" placeholder="Mô tả chi tiết góp ý, khó khăn bạn gặp, hoặc lỗi bạn thấy…"></textarea>
            <div style="display:flex;align-items:center;gap:12px;margin-top:16px;flex-wrap:wrap">
                <button class="ld-btn ld-btn-primary" id="fb-submit" style="padding:12px 24px">Gửi phản hồi</button>
                <span id="fb-msg" style="font-size:14.5px"></span>
            </div>
            <p class="ld-note" style="margin-top:12px">Phản hồi vào hệ thống ở trạng thái "Đã ghi nhận · Chờ duyệt". Bạn xem lại được mọi ticket của mình trong trang Hồ sơ.</p>
        </form>
        <div id="fb-done" style="display:none;margin-top:16px;text-align:center;padding:12px 0 4px">
            <p style="font-size:16.5px;font-weight:700;margin:0">Đã ghi nhận phản hồi của bạn</p>
            <p style="color:var(--color-sub);font-size:14.5px;margin:6px 0 0">Ticket đang ở trạng thái chờ duyệt. Theo dõi tiến độ trong mục "Phản hồi của tôi" ở trang Hồ sơ.</p>
            <button type="button" class="ld-btn ld-btn-gray" id="fb-done-close" style="margin-top:16px">Đóng</button>
        </div>
    </div>
</div>
<script>
    window.__fb = { user: undefined, type: 'gop-y', ctx: {} };
    fetch('/api/me').then(r => r.json()).then(({ user }) => { window.__fb.user = !!user; }).catch(() => { window.__fb.user = false; });
    function fbOpen() {
        const u = window.__fb.user;
        document.getElementById('fb-guest').style.display = u ? 'none' : '';
        document.getElementById('fb-form').style.display = u ? '' : 'none';
        document.getElementById('fb-done').style.display = 'none';
        document.getElementById('fb-msg').textContent = '';
        document.getElementById('fb-overlay').classList.add('open');
    }
    function fbClose() { document.getElementById('fb-overlay').classList.remove('open'); }
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-feedback]');
        if (!btn) return;
        e.preventDefault();
        window.__fb.ctx = {};
        if (btn.dataset.dish) window.__fb.ctx.dish_id = +btn.dataset.dish;
        if (btn.dataset.fruit) window.__fb.ctx.fruit_id = +btn.dataset.fruit;
        if (btn.dataset.ingredient) window.__fb.ctx.ingredient_id = +btn.dataset.ingredient;
        if (btn.dataset.drink) window.__fb.ctx.drink_id = +btn.dataset.drink;
        document.getElementById('fb-context').textContent = btn.dataset.context ? 'Về: ' + btn.dataset.context : 'Phản hồi chung cho Lắc Đói';
        fbOpen();
    });
    document.getElementById('fb-close').addEventListener('click', fbClose);
    document.getElementById('fb-done-close').addEventListener('click', fbClose);
    document.getElementById('fb-overlay').addEventListener('click', (e) => { if (e.target.id === 'fb-overlay') fbClose(); });
    document.querySelectorAll('#fb-types .ld-chip').forEach(b => b.addEventListener('click', () => {
        document.querySelectorAll('#fb-types .ld-chip').forEach(x => x.classList.remove('active'));
        b.classList.add('active');
        window.__fb.type = b.dataset.type;
    }));
    document.getElementById('fb-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const content = document.getElementById('fb-content').value.trim();
        const msg = document.getElementById('fb-msg');
        if (content.length < 5) { msg.style.color = 'var(--color-danger)'; msg.textContent = 'Nội dung cần ít nhất 5 ký tự.'; return; }
        const btn = document.getElementById('fb-submit'); btn.disabled = true;
        const resp = await fetch('/api/feedback', { method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ type: window.__fb.type, title: document.getElementById('fb-title').value.trim() || null,
                content, ...window.__fb.ctx, page: location.pathname }) });
        btn.disabled = false;
        if (resp.status === 401) { document.getElementById('fb-guest').style.display = ''; document.getElementById('fb-form').style.display = 'none'; return; }
        if (!resp.ok) { msg.style.color = 'var(--color-danger)'; msg.textContent = 'Chưa gửi được — kiểm tra lại nội dung và thử lại.'; return; }
        document.getElementById('fb-form').style.display = 'none';
        document.getElementById('fb-done').style.display = '';
        document.getElementById('fb-content').value = '';
        document.getElementById('fb-title').value = '';
        document.dispatchEvent(new CustomEvent('feedback-sent'));
        if (window.ldToast) ldToast('Đã ghi nhận phản hồi — chờ duyệt');
    });
</script>

{{-- Hộp thoại chi tiết tính vị (TCM-003 bản gọn, 2026-11-01) — trang chi tiết chỉ hiện
     giá trị chính + nút "Nguồn & chi tiết" mang data-tcm-open + data-tcm-title; toàn bộ
     thông tin thêm nằm trong <template id="tcm-detail"> của trang và được chép vào đây. --}}
<div class="ld-sheet-overlay" id="tcm-overlay">
    <div class="ld-sheet" role="dialog" aria-modal="true" aria-label="Chi tiết tính vị">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
            <h2 id="tcm-title" style="margin:0;font-size:20px;font-weight:800;letter-spacing:-.01em">Tính vị:</h2>
            <button type="button" id="tcm-close" aria-label="Đóng" style="border:0;background:var(--color-fill);width:32px;height:32px;border-radius:50%;font-size:15px;cursor:pointer;color:var(--color-sub);flex:none">✕</button>
        </div>
        <div id="tcm-body" style="margin-top:14px"></div>
    </div>
</div>
<script>
    function tcmClose() { document.getElementById('tcm-overlay').classList.remove('open'); }
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-tcm-open]');
        if (!btn) return;
        e.preventDefault();
        const tpl = document.getElementById('tcm-detail');
        if (!tpl) return;
        document.getElementById('tcm-title').textContent = btn.dataset.tcmTitle || 'Tính vị:';
        const body = document.getElementById('tcm-body');
        body.innerHTML = '';
        body.appendChild(tpl.content.cloneNode(true));
        document.getElementById('tcm-overlay').classList.add('open');
    });
    document.getElementById('tcm-close').addEventListener('click', tcmClose);
    document.getElementById('tcm-overlay').addEventListener('click', (e) => { if (e.target.id === 'tcm-overlay') tcmClose(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') tcmClose(); });
</script>
<script>
    /* TCM-003: tuỳ chọn hiển thị tính vị của người dùng (cột show_tcm, mặc
       định bật theo quyết định chủ dự án 2026-11-01; khách chưa đăng nhập
       luôn dùng mặc định). Các khối tính vị ở trang món/nguyên liệu/quả
       chỉ dựng khi cờ này bật — thuần hiển thị, không đụng engine gợi ý. */
    window.ldShowTcm = @json(auth()->user()?->profile?->show_tcm ?? true);
</script>
<script>
    /* TCM-003 (2026-11-01): bảng màu + icon cho từng giá trị tính/vị/hành —
       nguồn sự thật duy nhất của tầng trình bày tính vị. Màu: tính theo dải
       lạnh→nóng; vị theo hành tương ứng; hành theo 5 màu ngũ hành (Kim dùng
       bạc + viền, Thuỷ dùng xanh dương đậm cho dễ đọc). Icon SVG tự vẽ theo
       hệ thiết kế; icon của hành luôn động, tính/vị động nhẹ khi hợp lý
       (CSS .ld-tcm-ic.anim-* ở app.css, tắt khi prefers-reduced-motion).
       Mọi hàm nhận NHÃN ĐÃ esc() từ phía gọi và trả HTML. */
    window.ldTcm = (() => {
        const svg = (inner) => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${inner}</svg>`;
        const icons = {
            snow: svg('<path d="M12 3v18M4.2 7.5l15.6 9M19.8 7.5l-15.6 9"/>'),
            wind: svg('<path d="M3 8h9.5a2.75 2.75 0 1 0-2.75-2.75M2 12h13.5A2.75 2.75 0 1 1 12.75 14.75M3 16h6"/>'),
            balance: svg('<circle cx="12" cy="12" r="8.5"/><path d="M3.5 12h17"/><circle cx="12" cy="12" r="1.3" fill="currentColor" stroke="none"/>'),
            sun: svg('<circle cx="12" cy="12" r="4"/><path d="M12 2.5v2.3M12 19.2v2.3M2.5 12h2.3M19.2 12h2.3M5 5l1.6 1.6M17.4 17.4 19 19M19 5l-1.6 1.6M6.6 17.4 5 19"/>'),
            flame: svg('<path d="M12 2.8c.5 4.3-1.6 6-3 7.7C7.7 12 7 13.4 7 15.2A5 5 0 0 0 12 20.2a5 5 0 0 0 5-5c0-1.5-.5-2.8-1.2-4-.4.8-1 1.5-1.8 2 .3-3.4-.5-7.3-2-10.4Z"/>'),
            leaf: svg('<path d="M11 20A7 7 0 0 1 4 13c0-5 4-9 16-9 0 12-5 16-9 16Z"/><path d="M4 21c4-6 8-9 12-11"/>'),
            lemon: svg('<circle cx="12" cy="12" r="8.5"/><path d="M12 12V3.5M12 12l7.4 4.3M12 12l-7.4 4.3"/>'),
            herb: svg('<path d="M12 21v-9"/><path d="M12 9.5C12 6.3 10 4.2 6.3 4.2c0 3.6 2.1 5.5 5.7 5.3Z"/><path d="M12 13.5c0-3.2 2-5.3 5.7-5.3 0 3.6-2.1 5.5-5.7 5.3Z"/>'),
            honey: svg('<path d="M9.2 3.5h5.6M10 3.5v2c-1.5.7-2.4 1.8-2.4 3.4V17a3.6 3.6 0 0 0 3.6 3.6h1.6A3.6 3.6 0 0 0 16.4 17V8.9c0-1.6-.9-2.7-2.4-3.4v-2"/><path d="M7.6 12.8h8.8"/>'),
            chili: svg('<path d="M5.8 15.2C5.4 9.9 9.6 5.6 15.2 5.2c.5 0 1 .1 1.5.2-1.4 1.3-2 3-1.9 4.8.3 4.6-2.9 8.7-7.7 9.7-1 .2-1.2-2.5-1.3-4.7Z"/><path d="M15.4 5.2c.4-1.3 1.4-2.2 2.8-2.5-.2 1.1-.8 2-1.7 2.7"/>'),
            seadrop: svg('<path d="M12 3.5c2.2 2.9 3.8 5.2 3.8 7.3a3.8 3.8 0 0 1-7.6 0c0-2.1 1.6-4.4 3.8-7.3Z"/><path d="M4 20.2c1.7-1.3 3.3-1.3 5 0s3.3 1.3 5 0 3.3-1.3 5 0"/>'),
            droplet: svg('<path d="M12 3.5c2.4 3.1 4 5.5 4 7.8a4 4 0 0 1-8 0c0-2.3 1.6-4.7 4-7.8Z"/>'),
            mountain: svg('<path d="M3 19.5 9.5 8l3.8 5.6 2.2-3.4L21 19.5Z"/><path d="M9.5 8 8.3 9.9h2.4Z"/>'),
            sparkle: svg('<path d="M12 3l1.9 6.1L20 11l-6.1 1.9L12 19l-1.9-6.1L4 11l6.1-1.9Z"/>'),
            wave: svg('<path d="M2.5 9c2-2 4-2 6 0s4 2 6 0 4-2 6 0"/><path d="M2.5 15c2-2 4-2 6 0s4 2 6 0 4-2 6 0"/>'),
        };
        const MAPS = {
            nature: {
                han:   { c: '#0058b3', b: '#e1edfb', i: 'snow', a: 'spin' },
                luong: { c: '#3f8fd2', b: '#e6f2fc', i: 'wind', a: 'bob' },
                binh:  { c: '#5f7a61', b: '#e9efe9', i: 'balance', a: '' },
                on:    { c: '#c77400', b: '#fdf0dd', i: 'sun', a: 'pulse' },
                nhiet: { c: '#d70015', b: '#ffe9e7', i: 'flame', a: 'flicker' },
            },
            flavor: {
                toan: { c: '#248a3d', b: '#e4f8ea', i: 'lemon', a: 'sway' },
                kho:  { c: '#d70015', b: '#ffe9e7', i: 'herb', a: 'sway' },
                cam:  { c: '#7a5200', b: '#fff3d0', i: 'honey', a: 'bob' },
                tan:  { c: '#636368', b: '#eeeef1', bd: '#d5d5db', i: 'chili', a: 'sway' },
                ham:  { c: '#0058b3', b: '#e1edfb', i: 'seadrop', a: 'wave' },
                dam:  { c: '#a3863f', b: '#f7efdb', i: 'droplet', a: 'bob' },
            },
            element: {
                moc:  { c: '#248a3d', b: '#e4f8ea', i: 'leaf', a: 'sway' },
                hoa:  { c: '#d70015', b: '#ffe9e7', i: 'flame', a: 'flicker' },
                tho:  { c: '#96690a', b: '#f5ecd2', i: 'mountain', a: 'bob' },
                kim:  { c: '#6e6e73', b: '#f1f1f4', bd: '#d8d8de', i: 'sparkle', a: 'sparkle' },
                thuy: { c: '#0058b3', b: '#e1edfb', i: 'wave', a: 'wave' },
            },
        };
        const meta = (kind, key) => (key ? MAPS[kind]?.[key] ?? null : null);
        function chip(kind, key) {
            const m = meta(kind, key);
            if (!m) return '';
            return `<span class="ld-tcm-ic${m.a ? ' anim-' + m.a : ''}" style="color:${m.c};background:${m.b}${m.bd ? ';border:1px solid ' + m.bd : ''}">${icons[m.i]}</span>`;
        }
        function line(kind, key, title, label) {
            const m = meta(kind, key);
            if (!m || !label) return '';
            return `<div class="ld-tcm-line">${chip(kind, key)}<span class="ld-tcm-name">${title}</span><b style="color:${m.c}">${label}</b></div>`;
        }
        function seg(kind, key, label) {
            const m = meta(kind, key);
            if (!m || !label) return '';
            return `<span class="ld-tcm-seg" style="color:${m.c}">${icons[m.i]}${label}</span>`;
        }
        function segments(t) {
            return [seg('nature', t.nature, t.nature_label), seg('flavor', t.flavor, t.flavor_label), seg('element', t.element, t.element_label)]
                .filter(Boolean).join(' · ');
        }
        function color(kind, key) { return meta(kind, key)?.c ?? ''; }
        return { line, seg, segments, color, chip };
    })();
</script>

<div class="ld-toast" id="ld-toast" role="status"></div>
<script>
    window.ldToast = function (msg) {
        const t = document.getElementById('ld-toast');
        t.textContent = msg; t.classList.add('show');
        clearTimeout(t._h); t._h = setTimeout(() => t.classList.remove('show'), 2600);
    };

    /* Yêu thích (UI-014): dùng chung mọi trang — nút tim mang data-fav-dish,
       data-fav-fruit hoặc data-fav-drink; trạng thái lấy từ /api/favorites/ids. */
    window.__fav = { user: false, dishes: new Set(), fruits: new Set(), drinks: new Set() };
    window.ldRefreshHearts = function () {
        document.querySelectorAll('[data-fav-dish]').forEach(el =>
            el.classList.toggle('on', window.__fav.dishes.has(+el.dataset.favDish)));
        document.querySelectorAll('[data-fav-fruit]').forEach(el =>
            el.classList.toggle('on', window.__fav.fruits.has(+el.dataset.favFruit)));
        document.querySelectorAll('[data-fav-drink]').forEach(el =>
            el.classList.toggle('on', window.__fav.drinks.has(+el.dataset.favDrink)));
    };
    document.addEventListener('click', async (e) => {
        const el = e.target.closest('[data-fav-dish], [data-fav-fruit], [data-fav-drink]');
        if (!el) return;
        e.preventDefault(); e.stopPropagation();
        if (!window.__fav.user) { ldToast('Đăng nhập để lưu mục yêu thích'); return; }
        const kind = el.hasAttribute('data-fav-dish') ? 'dish' : (el.hasAttribute('data-fav-fruit') ? 'fruit' : 'drink');
        const id = +(kind === 'dish' ? el.dataset.favDish : (kind === 'fruit' ? el.dataset.favFruit : el.dataset.favDrink));
        const resp = await fetch('/api/favorites/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify(kind === 'dish' ? { dish_id: id } : (kind === 'fruit' ? { fruit_id: id } : { drink_id: id })),
        });
        if (!resp.ok) { ldToast('Chưa lưu được — thử lại sau'); return; }
        const { favored } = await resp.json();
        (kind === 'dish' ? window.__fav.dishes : (kind === 'fruit' ? window.__fav.fruits : window.__fav.drinks))[favored ? 'add' : 'delete'](id);
        window.ldRefreshHearts();
        ldToast(favored ? 'Đã thêm vào Yêu thích' : 'Đã bỏ khỏi Yêu thích');
        document.dispatchEvent(new CustomEvent('fav-changed', { detail: { isDish: kind === 'dish', isDrink: kind === 'drink', id, favored } }));
    });
    fetch('/api/me').then(r => r.json()).then(async ({ user }) => {
        if (!user) return;
        window.__fav.user = true;
        const ids = await (await fetch('/api/favorites/ids')).json();
        window.__fav.dishes = new Set(ids.dish_ids ?? []);
        window.__fav.fruits = new Set(ids.fruit_ids ?? []);
        window.__fav.drinks = new Set(ids.drink_ids ?? []);
        window.ldRefreshHearts();
    }).catch(() => {});
</script>
@stack('scripts')
</body>
</html>
