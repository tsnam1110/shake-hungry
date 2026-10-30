<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tài khoản — Lắc Đói</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-orange-50 min-h-screen text-neutral-800">
<main class="mx-auto max-w-md px-4 pt-10 pb-16">
    <div class="text-center mb-6"><div class="text-5xl">🎲</div>
        <h1 class="text-2xl font-bold mt-2">Lắc Đói</h1>
        <p class="text-neutral-500 text-sm">Đăng nhập để lưu hồ sơ và nhật ký bữa ăn. Khách vẫn lắc món bình thường.</p>
    </div>
    <div class="bg-white rounded-2xl shadow p-5">
        <div class="flex gap-2 mb-4">
            <button data-tab="login" class="tabbtn flex-1 rounded-xl py-2 font-semibold bg-orange-600 text-white">Đăng nhập</button>
            <button data-tab="register" class="tabbtn flex-1 rounded-xl py-2 font-semibold bg-neutral-100">Đăng ký</button>
            <button data-tab="forgot" class="tabbtn flex-1 rounded-xl py-2 font-semibold bg-neutral-100">Quên MK</button>
        </div>
        <p id="msg" class="text-sm mb-3 hidden"></p>

        <form id="form-login" class="space-y-3">
            <input name="email" type="email" required placeholder="Email" class="w-full border rounded-xl px-3 py-2.5">
            <input name="password" type="password" required placeholder="Mật khẩu" class="w-full border rounded-xl px-3 py-2.5">
            <button class="w-full bg-orange-600 text-white font-bold rounded-xl py-3">Đăng nhập</button>
        </form>

        <form id="form-register" class="space-y-3 hidden">
            <input name="name" required placeholder="Tên của bạn" class="w-full border rounded-xl px-3 py-2.5">
            <input name="email" type="email" required placeholder="Email" class="w-full border rounded-xl px-3 py-2.5">
            <input name="password" type="password" required minlength="8" placeholder="Mật khẩu (ít nhất 8 ký tự)" class="w-full border rounded-xl px-3 py-2.5">
            <input name="password_confirmation" type="password" required placeholder="Nhập lại mật khẩu" class="w-full border rounded-xl px-3 py-2.5">
            <button class="w-full bg-orange-600 text-white font-bold rounded-xl py-3">Tạo tài khoản</button>
        </form>

        <form id="form-forgot" class="space-y-3 hidden">
            <input name="email" type="email" required placeholder="Email đã đăng ký" class="w-full border rounded-xl px-3 py-2.5">
            <button class="w-full bg-orange-600 text-white font-bold rounded-xl py-3">Gửi liên kết đặt lại</button>
            <p class="text-xs text-neutral-400">Nếu email tồn tại, hệ thống sẽ gửi liên kết đặt lại mật khẩu.</p>
        </form>

        <form id="form-reset" class="space-y-3 hidden">
            <input name="email" type="email" required placeholder="Email" class="w-full border rounded-xl px-3 py-2.5">
            <input name="password" type="password" required minlength="8" placeholder="Mật khẩu mới" class="w-full border rounded-xl px-3 py-2.5">
            <input name="password_confirmation" type="password" required placeholder="Nhập lại mật khẩu mới" class="w-full border rounded-xl px-3 py-2.5">
            <button class="w-full bg-orange-600 text-white font-bold rounded-xl py-3">Đặt lại mật khẩu</button>
        </form>
    </div>
    <p class="text-center mt-4 text-sm"><a class="text-orange-700 underline" href="/">← Về trang Lắc món</a></p>
</main>
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
const msg = document.getElementById('msg');
function showMsg(text, ok = true) {
    msg.textContent = text;
    msg.className = 'text-sm mb-3 rounded-xl px-3 py-2 ' + (ok ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600');
}
async function post(url, form) {
    const body = Object.fromEntries(new FormData(form).entries());
    const resp = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(body) });
    const data = await resp.json().catch(() => ({}));
    if (!resp.ok) {
        const first = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Có lỗi xảy ra');
        throw new Error(first);
    }
    return data;
}
const tabs = document.querySelectorAll('.tabbtn');
function activate(name) {
    tabs.forEach(b => b.className = 'tabbtn flex-1 rounded-xl py-2 font-semibold ' + (b.dataset.tab === name ? 'bg-orange-600 text-white' : 'bg-neutral-100'));
    ['login', 'register', 'forgot', 'reset'].forEach(t => document.getElementById('form-' + t).classList.toggle('hidden', t !== name));
}
tabs.forEach(b => b.onclick = () => activate(b.dataset.tab));
document.getElementById('form-login').onsubmit = async (e) => { e.preventDefault(); try { await post('/api/login', e.target); location.href = '/ho-so'; } catch (err) { showMsg(err.message, false); } };
document.getElementById('form-register').onsubmit = async (e) => { e.preventDefault(); try { await post('/api/register', e.target); location.href = '/ho-so'; } catch (err) { showMsg(err.message, false); } };
document.getElementById('form-forgot').onsubmit = async (e) => { e.preventDefault(); try { await post('/api/forgot-password', e.target); showMsg('Đã gửi (nếu email tồn tại). Kiểm tra hộp thư của bạn.'); } catch (err) { showMsg(err.message, false); } };
document.getElementById('form-reset').onsubmit = async (e) => {
    e.preventDefault();
    const token = new URLSearchParams(location.search).get('token');
    try {
        const form = e.target;
        const body = { ...Object.fromEntries(new FormData(form).entries()), token };
        const resp = await fetch('/api/reset-password', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(body) });
        if (!resp.ok) throw new Error('Liên kết không hợp lệ hoặc đã hết hạn.');
        showMsg('Đặt lại mật khẩu thành công — hãy đăng nhập.'); activate('login');
    } catch (err) { showMsg(err.message, false); }
};
if (new URLSearchParams(location.search).get('token')) activate('reset');
fetch('/api/me').then(r => r.json()).then(({ user }) => { if (user) location.href = '/ho-so'; });
</script>
</body>
</html>
