<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Giới hạn tần suất đăng nhập/đăng ký người dùng và admin (chống brute-force)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Giới hạn tần suất engine Lắc (tránh bot spam làm cạn kiệt tài nguyên máy chủ)
        RateLimiter::for('shake', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
