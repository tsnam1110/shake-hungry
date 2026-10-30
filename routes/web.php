<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DishController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\ReferenceFoodController;
use App\Http\Controllers\Admin\RegionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// --- Khu vực quản trị (ADM-001): SPA React + antd tại /admin, API nội bộ dùng phiên web ---
Route::post('/admin/api/login', [AuthController::class, 'login']);

Route::middleware(['auth', 'admin'])->prefix('admin/api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', DashboardController::class);
    Route::get('/regions', [RegionController::class, 'index']);
    Route::get('/reference-foods', [ReferenceFoodController::class, 'index']);

    Route::apiResource('ingredients', IngredientController::class);
    Route::post('/ingredients/{ingredient}/review', [IngredientController::class, 'review']);

    Route::get('/dishes/similar', [DishController::class, 'similar']);
    Route::post('/dishes/preview-nutrition', [DishController::class, 'previewNutrition']);
    Route::apiResource('dishes', DishController::class);
    Route::post('/dishes/{dish}/review', [DishController::class, 'review']);
    Route::post('/dishes/{dish}/compute', [DishController::class, 'compute']);
});

Route::get('/admin/{any?}', fn () => view('admin'))
    ->where('any', '^(?!api($|/)).*$');
