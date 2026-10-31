<?php

declare(strict_types=1);

return [
    /*
     * Đăng nhập nhanh vào quản trị KHÔNG cần mật khẩu — CHỈ phục vụ kiểm thử
     * ở môi trường dev cục bộ. Mặc định TẮT. Bật bằng ADMIN_QUICK_LOGIN=true
     * trong .env (file không commit). TUYỆT ĐỐI không bật ở môi trường thật:
     * ai mở được trang đăng nhập cũng vào thẳng tài khoản quản trị cao nhất.
     */
    'quick_login' => env('ADMIN_QUICK_LOGIN', false),
];
