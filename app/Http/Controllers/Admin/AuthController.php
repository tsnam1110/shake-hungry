<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/** Đăng nhập khu vực quản trị bằng email/mật khẩu, phiên web (ADM-001). */
class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng.'],
            ]);
        }
        if (! in_array($request->user()->role, ['admin', 'editor'], true)) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Tài khoản không có quyền vào khu vực quản trị.'],
            ]);
        }
        $request->session()->regenerate();

        return response()->json(['user' => $this->userPayload($request)]);
    }

    /**
     * Đăng nhập nhanh kiểm thử: vào thẳng tài khoản admin đầu tiên, KHÔNG mật khẩu.
     * Chỉ hoạt động khi ADMIN_QUICK_LOGIN=true (mặc định tắt — xem config/admin.php).
     * Mỗi lần dùng đều ghi log cảnh báo kèm IP.
     */
    public function quickLogin(Request $request): JsonResponse
    {
        abort_if(app()->environment('production'), 403, 'Đăng nhập nhanh bị vô hiệu hóa trên môi trường production.');
        abort_unless(config('admin.quick_login') === true, 403, 'Đăng nhập nhanh đang tắt.');
        $admin = User::where('role', 'admin')->orderBy('id')->first();
        abort_if($admin === null, 422, 'Chưa có tài khoản admin nào trong hệ thống.');
        Auth::login($admin);
        $request->session()->regenerate();
        Log::warning('ADMIN QUICK-LOGIN đã được dùng', [
            'admin' => $admin->email, 'ip' => $request->ip(), 'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['user' => $this->userPayload($request)]);
    }

    /** Cho trang đăng nhập biết có hiện nút đăng nhập nhanh hay không. */
    public function loginOptions(): JsonResponse
    {
        if (app()->environment('production')) {
            return response()->json(['quick_login' => false]);
        }

        return response()->json(['quick_login' => config('admin.quick_login') === true]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['ok' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userPayload($request)]);
    }

    private function userPayload(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }
}
