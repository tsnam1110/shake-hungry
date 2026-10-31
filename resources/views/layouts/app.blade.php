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
        ['gợi ý', '/kham-pha', 'Khám phá món', 'search', str_starts_with($path, 'kham-pha')],
        ['gợi ý', '/hoa-qua', 'Hoa quả theo mùa', 'fruit', str_starts_with($path, 'hoa-qua')],
        ['của bạn', '/nhat-ky', 'Nhật ký bữa ăn', 'book', $path === 'nhat-ky'],
        ['của bạn', '/ho-so', 'Hồ sơ của tôi', 'person', $path === 'ho-so'],
        ['của bạn', '/ho-so#dong-gop', 'Đóng góp cộng đồng', 'heart', false],
    ];
    $icons = [
        'dice' => '<rect x="4" y="4" width="16" height="16" rx="4.5"/><circle cx="9.2" cy="9.2" r="1.25" fill="currentColor" stroke="none"/><circle cx="14.8" cy="14.8" r="1.25" fill="currentColor" stroke="none"/><circle cx="14.8" cy="9.2" r="1.25" fill="currentColor" stroke="none"/><circle cx="9.2" cy="14.8" r="1.25" fill="currentColor" stroke="none"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M20.5 20.5 16.6 16.6"/>',
        'fruit' => '<path d="M12 8.2C10.8 6.6 8.9 6 7.2 6.8 4.9 7.9 4.2 11 5.4 14.6c1 3 2.5 4.9 4.2 4.3.8-.3 1.6-.3 2.4 0 1.7.6 3.2-1.3 4.2-4.3 1.2-3.6.5-6.7-1.8-7.8C15.1 6 13.2 6.6 12 8.2Z"/><path d="M12 8c0-2 .9-3.4 2.6-4"/>',
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
            <footer class="ld-foot">
                Số calo trên Lắc Đói luôn là khoảng ước tính từ engine của dự án, kèm độ tin cậy và nguồn gốc con số — món chưa đủ dữ liệu sẽ không hiện số.
            </footer>
        </div>
    </main>
</div>

<nav class="ld-tabbar" aria-label="Điều hướng chính">
    <a href="/" class="{{ $path === '/' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">{!! $icons['dice'] !!}</svg>Lắc món</a>
    <a href="/kham-pha" class="{{ str_starts_with($path, 'kham-pha') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">{!! $icons['search'] !!}</svg>Khám phá</a>
    <a href="/hoa-qua" class="{{ str_starts_with($path, 'hoa-qua') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons['fruit'] !!}</svg>Hoa quả</a>
    <a href="/nhat-ky" class="{{ $path === 'nhat-ky' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">{!! $icons['book'] !!}</svg>Nhật ký</a>
    <a href="{{ $user ? '/ho-so' : '/tai-khoan' }}" class="{{ $path === 'ho-so' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">{!! $icons['person'] !!}</svg>Tôi</a>
</nav>
@else
<main style="min-height:100vh;display:flex;align-items:flex-start;justify-content:center;padding:28px 18px 60px">
    <div style="width:100%;max-width:430px">
        @yield('content')
    </div>
</main>
@endif

<div class="ld-toast" id="ld-toast" role="status"></div>
<script>
    window.ldToast = function (msg) {
        const t = document.getElementById('ld-toast');
        t.textContent = msg; t.classList.add('show');
        clearTimeout(t._h); t._h = setTimeout(() => t.classList.remove('show'), 2600);
    };

    /* Yêu thích (UI-014): dùng chung mọi trang — nút tim mang data-fav-dish
       hoặc data-fav-fruit; trạng thái lấy từ /api/favorites/ids. */
    window.__fav = { user: false, dishes: new Set(), fruits: new Set() };
    window.ldRefreshHearts = function () {
        document.querySelectorAll('[data-fav-dish]').forEach(el =>
            el.classList.toggle('on', window.__fav.dishes.has(+el.dataset.favDish)));
        document.querySelectorAll('[data-fav-fruit]').forEach(el =>
            el.classList.toggle('on', window.__fav.fruits.has(+el.dataset.favFruit)));
    };
    document.addEventListener('click', async (e) => {
        const el = e.target.closest('[data-fav-dish], [data-fav-fruit]');
        if (!el) return;
        e.preventDefault(); e.stopPropagation();
        if (!window.__fav.user) { ldToast('Đăng nhập để lưu mục yêu thích'); return; }
        const isDish = el.hasAttribute('data-fav-dish');
        const id = +(isDish ? el.dataset.favDish : el.dataset.favFruit);
        const resp = await fetch('/api/favorites/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify(isDish ? { dish_id: id } : { fruit_id: id }),
        });
        if (!resp.ok) { ldToast('Chưa lưu được — thử lại sau'); return; }
        const { favored } = await resp.json();
        (isDish ? window.__fav.dishes : window.__fav.fruits)[favored ? 'add' : 'delete'](id);
        window.ldRefreshHearts();
        ldToast(favored ? 'Đã thêm vào Yêu thích' : 'Đã bỏ khỏi Yêu thích');
        document.dispatchEvent(new CustomEvent('fav-changed', { detail: { isDish, id, favored } }));
    });
    fetch('/api/me').then(r => r.json()).then(async ({ user }) => {
        if (!user) return;
        window.__fav.user = true;
        const ids = await (await fetch('/api/favorites/ids')).json();
        window.__fav.dishes = new Set(ids.dish_ids ?? []);
        window.__fav.fruits = new Set(ids.fruit_ids ?? []);
        window.ldRefreshHearts();
    }).catch(() => {});
</script>
@stack('scripts')
</body>
</html>
