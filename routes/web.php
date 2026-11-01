<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DishController;
use App\Http\Controllers\Admin\FruitController as AdminFruitController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\ReferenceFoodController;
use App\Http\Controllers\Admin\RegionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('shake');
});

// --- API công khai cho trang người dùng (GĐ4) ---
Route::get('/tai-khoan', fn () => view('account'))->name('login');
Route::get('/ho-so', fn () => view('profile')); // khách chưa đăng nhập thấy giao diện giới thiệu (UI-016)
Route::view('/dieu-khoan', 'legal.terms');
Route::view('/chinh-sach-bao-mat', 'legal.privacy');
Route::view('/mien-tru-y-te', 'legal.disclaimer');
Route::view('/nguon-du-lieu', 'legal.data');
Route::get('/mon/{slug}', fn () => view('dish'));
// IMG-001: ảnh trong data/media/ (đã qua kiểm license R4) — chỉ .webp ở hai nhóm dishes/ingredients.
Route::get('/media/{path}', [\App\Http\Controllers\PublicWeb\MediaController::class, 'show'])->where('path', '.*');
Route::get('/thu-vien', fn () => view('explore')); // tên cũ /kham-pha — đổi thành Thư viện 2026-11-01
Route::get('/thu-vien/hoa-qua', fn () => view('fruits', ['library' => true])); // tab Hoa quả trong Thư viện (UI-023)
Route::redirect('/kham-pha', '/thu-vien');
Route::get('/nguyen-lieu', fn () => view('ingredients'));
Route::get('/nguyen-lieu/{slug}', fn () => view('ingredient'));
Route::get('/nhat-ky', fn () => view('diary'))->middleware('auth');
Route::get('/hoa-qua', fn () => view('fruits'));
Route::get('/hoa-qua/{slug}', fn () => view('fruit'));

Route::prefix('api')->group(function () {
    Route::post('/register', [\App\Http\Controllers\PublicWeb\UserAuthController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\PublicWeb\UserAuthController::class, 'login']);
    Route::post('/logout', [\App\Http\Controllers\PublicWeb\UserAuthController::class, 'logout']);
    Route::get('/me', [\App\Http\Controllers\PublicWeb\UserAuthController::class, 'me']);
    Route::post('/forgot-password', [\App\Http\Controllers\PublicWeb\UserAuthController::class, 'forgot']);
    Route::post('/reset-password', [\App\Http\Controllers\PublicWeb\UserAuthController::class, 'reset']);
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\PublicWeb\ProfileController::class, 'show']);
        Route::put('/profile', [\App\Http\Controllers\PublicWeb\ProfileController::class, 'update']);
        Route::get('/meal-logs', [\App\Http\Controllers\PublicWeb\ProfileController::class, 'mealLogs']);
        Route::post('/meal-logs', [\App\Http\Controllers\PublicWeb\ProfileController::class, 'storeMealLog']);
        Route::put('/meal-logs/{mealLog}', [\App\Http\Controllers\PublicWeb\ProfileController::class, 'updateMealLog']);
        Route::delete('/meal-logs/{mealLog}', [\App\Http\Controllers\PublicWeb\ProfileController::class, 'destroyMealLog']);
        Route::post('/contributions', [\App\Http\Controllers\PublicWeb\ContributionController::class, 'store']);
        Route::get('/contributions', [\App\Http\Controllers\PublicWeb\ContributionController::class, 'mine']);
        Route::get('/favorites', [\App\Http\Controllers\PublicWeb\EngagementController::class, 'index']);
        Route::get('/favorites/ids', [\App\Http\Controllers\PublicWeb\EngagementController::class, 'ids']);
        Route::post('/favorites/toggle', [\App\Http\Controllers\PublicWeb\EngagementController::class, 'toggle']);
        Route::post('/fruit-logs', [\App\Http\Controllers\PublicWeb\EngagementController::class, 'storeFruitLog']);
        Route::get('/fruit-logs', [\App\Http\Controllers\PublicWeb\EngagementController::class, 'fruitLogs']);
        Route::delete('/fruit-logs/{fruitLog}', [\App\Http\Controllers\PublicWeb\EngagementController::class, 'destroyFruitLog']);
        Route::get('/stats', [\App\Http\Controllers\PublicWeb\EngagementController::class, 'stats']);
        Route::post('/feedback', [\App\Http\Controllers\PublicWeb\FeedbackController::class, 'store']);
        Route::get('/feedback', [\App\Http\Controllers\PublicWeb\FeedbackController::class, 'mine']);
    });
    Route::get('/regions', [\App\Http\Controllers\PublicWeb\CatalogController::class, 'regions']);
    Route::get('/tags', [\App\Http\Controllers\PublicWeb\TaxonomyController::class, 'tags']);
    Route::get('/ingredients', [\App\Http\Controllers\PublicWeb\TaxonomyController::class, 'ingredients']);
    Route::get('/ingredients/{ingredient:slug}', [\App\Http\Controllers\PublicWeb\TaxonomyController::class, 'ingredient']);
    Route::get('/dishes', [\App\Http\Controllers\PublicWeb\CatalogController::class, 'index']);
    Route::get('/dishes/{dish:slug}', [\App\Http\Controllers\PublicWeb\CatalogController::class, 'show']);
    Route::get('/fruits', [\App\Http\Controllers\PublicWeb\FruitController::class, 'index']);
    Route::get('/fruits/{fruit:slug}', [\App\Http\Controllers\PublicWeb\FruitController::class, 'show']);
    Route::post('/shake', [\App\Http\Controllers\PublicWeb\CatalogController::class, 'shake']);
});

// --- Khu vực quản trị (ADM-001): SPA React + antd tại /admin, API nội bộ dùng phiên web ---
Route::post('/admin/api/login', [AuthController::class, 'login']);
Route::get('/admin/api/login-options', [AuthController::class, 'loginOptions']);
Route::post('/admin/api/quick-login', [AuthController::class, 'quickLogin']);

Route::middleware(['auth', 'admin'])->prefix('admin/api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', DashboardController::class);
    Route::get('/regions', [RegionController::class, 'index']);
    Route::get('/reference-foods', [ReferenceFoodController::class, 'index']);

    Route::post('/ingredients/bulk', [IngredientController::class, 'bulk']);
    Route::apiResource('ingredients', IngredientController::class);
    Route::post('/ingredients/{ingredient}/review', [IngredientController::class, 'review']);

    Route::get('/dishes/similar', [DishController::class, 'similar']);
    Route::post('/dishes/preview-nutrition', [DishController::class, 'previewNutrition']);
    Route::post('/dishes/bulk', [DishController::class, 'bulk']);
    Route::apiResource('dishes', DishController::class);
    Route::post('/dishes/{dish}/review', [DishController::class, 'review']);

    Route::post('/fruits/bulk', [AdminFruitController::class, 'bulk']);
    Route::apiResource('fruits', AdminFruitController::class);
    Route::post('/fruits/{fruit}/review', [AdminFruitController::class, 'review']);
    Route::get('/contributions', [\App\Http\Controllers\Admin\ContributionController::class, 'index']);
    Route::post('/contributions/bulk', [\App\Http\Controllers\Admin\ContributionController::class, 'bulk']);
    Route::post('/contributions/{contribution}/review', [\App\Http\Controllers\Admin\ContributionController::class, 'review']);
    Route::get('/feedback', [\App\Http\Controllers\Admin\FeedbackController::class, 'index']);
    Route::post('/feedback/{ticket}/status', [\App\Http\Controllers\Admin\FeedbackController::class, 'updateStatus']);
    Route::post('/dishes/{dish}/compute', [DishController::class, 'compute']);
});

Route::get('/admin/{any?}', fn () => view('admin'))
    ->where('any', '^(?!api($|/)).*$');
