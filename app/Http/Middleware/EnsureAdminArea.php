<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Chỉ tài khoản có vai trò admin/editor mới vào được khu vực quản trị (ADM-001). */
class EnsureAdminArea
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            abort(401, 'Chưa đăng nhập.');
        }
        if (! in_array($user->role, ['admin', 'editor'], true)) {
            abort(403, 'Tài khoản không có quyền vào khu vực quản trị.');
        }

        return $next($request);
    }
}
