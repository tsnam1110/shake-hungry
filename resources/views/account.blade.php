@extends('layouts.app', ['hideNav' => true])

@section('title', 'Tài khoản — Lắc Đói')

@section('content')
    <div style="text-align:center;margin:14px 0 22px">
        <span class="ld-app-icon" style="width:56px;height:56px;border-radius:15px" aria-hidden="true">
            <svg width="31" height="31" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.9" stroke-linecap="round"><path d="M4 12h16a8 8 0 0 1-16 0Z"/><path d="M9 8c1-1.6 4.5-1.8 6-.5"/><path d="M15.5 5.5c1.2-1 2.7-1.3 4-1"/></svg>
        </span>
        <h1 style="font-size:27px;font-weight:800;letter-spacing:-.02em;margin:12px 0 4px">Lắc Đói</h1>
        <p style="color:var(--color-sub);font-size:15px;margin:0">Đăng nhập để lưu hồ sơ và nhật ký bữa ăn.<br>Khách vẫn lắc món bình thường.</p>
    </div>

    <div class="ld-card">
        <div class="ld-seg" style="margin-bottom:16px">
            <button type="button" data-tab="login" class="tabbtn" aria-pressed="true">Đăng nhập</button>
            <button type="button" data-tab="register" class="tabbtn" aria-pressed="false">Đăng ký</button>
            <button type="button" data-tab="forgot" class="tabbtn" aria-pressed="false">Quên mật khẩu</button>
        </div>
        <p id="msg" style="display:none"></p>

        <form id="form-login" style="display:flex;flex-direction:column;gap:11px">
            <input name="email" type="email" required placeholder="Email" class="ld-input" autocomplete="email">
            <input name="password" type="password" required placeholder="Mật khẩu" class="ld-input" autocomplete="current-password">
            <button class="ld-btn ld-btn-primary" style="width:100%">Đăng nhập</button>
        </form>

        <form id="form-register" style="display:none;flex-direction:column;gap:11px">
            <input name="name" required placeholder="Tên của bạn" class="ld-input" autocomplete="name">
            <input name="email" type="email" required placeholder="Email" class="ld-input" autocomplete="email">
            <input name="password" type="password" required minlength="8" placeholder="Mật khẩu (ít nhất 8 ký tự)" class="ld-input" autocomplete="new-password">
            <input name="password_confirmation" type="password" required placeholder="Nhập lại mật khẩu" class="ld-input" autocomplete="new-password">
            <button class="ld-btn ld-btn-primary" style="width:100%">Tạo tài khoản</button>
        </form>

        <form id="form-forgot" style="display:none;flex-direction:column;gap:11px">
            <input name="email" type="email" required placeholder="Email đã đăng ký" class="ld-input" autocomplete="email">
            <button class="ld-btn ld-btn-primary" style="width:100%">Gửi liên kết đặt lại</button>
            <p class="ld-note" style="margin:0">Nếu email tồn tại, hệ thống sẽ gửi liên kết đặt lại mật khẩu.</p>
        </form>

        <form id="form-reset" style="display:none;flex-direction:column;gap:11px">
            <input name="email" type="email" required placeholder="Email" class="ld-input" autocomplete="email">
            <input name="password" type="password" required minlength="8" placeholder="Mật khẩu mới" class="ld-input" autocomplete="new-password">
            <input name="password_confirmation" type="password" required placeholder="Nhập lại mật khẩu mới" class="ld-input" autocomplete="new-password">
            <button class="ld-btn ld-btn-primary" style="width:100%">Đặt lại mật khẩu</button>
        </form>
    </div>
    <p style="text-align:center;margin-top:18px"><a href="/" style="color:var(--color-link);text-decoration:none;font-size:15px">← Về trang Lắc món</a></p>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
const msg = document.getElementById('msg');
function showMsg(text, ok = true) {
    msg.textContent = text;
    msg.style.display = 'block';
    msg.style.cssText += ';border-radius:12px;padding:11px 13px;font-size:14px;font-weight:500;margin:0 0 14px;' +
        (ok ? 'background:var(--color-mint-tint);color:var(--color-mint-dark)' : 'background:var(--color-danger-tint);color:var(--color-danger)');
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
    tabs.forEach(b => b.setAttribute('aria-pressed', b.dataset.tab === name ? 'true' : 'false'));
    ['login', 'register', 'forgot', 'reset'].forEach(t => {
        const f = document.getElementById('form-' + t);
        f.style.display = t === name ? 'flex' : 'none';
    });
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
@endpush
