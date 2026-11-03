<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdminArea::class,
        ]);
        // PROXY-001 (2026-11-03): app luôn chạy sau một reverse proxy — Pinggy
        // khi public tạm, nginx khi chạy Docker. Proxy ngoài chấm dứt TLS và
        // chuyển tiếp bằng HTTP tới app, kèm X-Forwarded-Proto/Host/For. Nếu
        // Laravel không tin các header này, nó tưởng request là http và sinh
        // URL asset Vite dạng http://… — trình duyệt mở trang bằng https sẽ
        // chặn hết CSS/JS vì mixed content, giao diện vỡ thành HTML trần
        // (logo SVG phình to, nav chỉ còn link trần). Tin proxy để scheme/host
        // của URL sinh ra khớp với những gì người xem thực sự thấy.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
