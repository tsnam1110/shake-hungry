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
    // RET-001: Món của ngày — chọn tất định theo ngày trong năm từ các món Mức ≥2
    // đã tính được dinh dưỡng và có ảnh chính; cache tới cuối ngày để mọi khách
    // trong ngày thấy cùng một món (đúng tính chất "của ngày").
    $dishOfDay = \Illuminate\Support\Facades\Cache::remember(
        'dish_of_day_' . now()->format('Y-m-d'),
        now()->endOfDay(),
        function () {
            $ids = \App\Models\Dish::query()
                ->where('completeness_level', '>=', 2)
                ->whereHas('nutrition')
                ->whereHas('media', fn ($q) => $q->where('is_primary', true))
                ->orderBy('id')
                ->pluck('id');
            if ($ids->isEmpty()) {
                return null;
            }
            $dish = \App\Models\Dish::with(['nutrition', 'region'])->find($ids[now()->dayOfYear % $ids->count()]);
            $image = $dish?->primaryImage();
            if (! $dish || ! $image) {
                return null;
            }

            return [
                'slug' => $dish->slug,
                'name' => $dish->displayName(),
                'image' => $image['url'],
                'region' => $dish->region?->name,
                'kcal_label' => $dish->nutrition
                    ? ' · ' . round($dish->nutrition->kcal_min) . '–' . round($dish->nutrition->kcal_max) . ' kcal/khẩu phần'
                    : '',
            ];
        }
    );

    return view('shake', ['dishOfDay' => $dishOfDay]);
});

// --- API công khai cho trang người dùng (GĐ4) ---
Route::get('/tai-khoan', fn () => view('account'))->name('login');
Route::get('/ho-so', fn () => view('profile')); // khách chưa đăng nhập thấy giao diện giới thiệu (UI-016)
Route::get('/tuan-an', fn () => view('tuan-an')); // WEEK-001, Phần 2.4: trang Tuần ăn riêng
Route::view('/dieu-khoan', 'legal.terms');
Route::view('/chinh-sach-bao-mat', 'legal.privacy');
Route::view('/mien-tru-y-te', 'legal.disclaimer');
Route::view('/nguon-du-lieu', 'legal.data');
Route::get('/mon/{slug}', fn () => view('dish'));
// IMG-001: ảnh trong data/media/ (đã qua kiểm license R4) — chỉ .webp ở hai nhóm dishes/ingredients.
// Thumbnail phải khai báo TRƯỚC route /media chung, kẻo bị route chung nuốt mất.
Route::get('/media/thumb/{width}/{path}', [\App\Http\Controllers\PublicWeb\MediaController::class, 'thumb'])->where('width', '[0-9]+')->where('path', '.*');
Route::get('/media/{path}', [\App\Http\Controllers\PublicWeb\MediaController::class, 'show'])->where('path', '.*');
Route::get('/thu-vien', fn () => view('explore')); // tên cũ /kham-pha — đổi thành Thư viện 2026-11-01
Route::get('/thu-vien/hoa-qua', fn () => view('fruits', ['library' => true])); // tab Hoa quả trong Thư viện (UI-023)
Route::redirect('/kham-pha', '/thu-vien');
Route::get('/nguyen-lieu', fn () => view('ingredients'));
Route::get('/nguyen-lieu/{slug}', fn () => view('ingredient'));
Route::get('/nhat-ky', fn () => view('diary'))->middleware('auth');
Route::get('/hoa-qua', fn () => view('fruits'));
Route::get('/hoa-qua/{slug}', fn () => view('fruit'));
Route::get('/rau-mua', fn () => view('vegetables'));
Route::get('/rau-mua/{slug}', fn () => view('vegetable'));
Route::get('/do-uong', fn () => view('drinks'));
Route::get('/do-uong/{slug}', fn () => view('drink'));
// Bảng nguyên liệu lưu ý — trang riêng, liệt kê toàn bộ cặp kết hợp (kể cả
// cặp thuốc × thực phẩm và cặp có phía là đồ uống), dùng CombinationDisplay
// + DISCLAIMER bắt buộc. Lớp THÔNG TIN thuần, không chặn hay lọc Lắc.
Route::get('/ket-hop-can-luu-y', fn () => view('combinations', [
    'combinations' => \App\Combinations\CombinationDisplay::all(),
    'disclaimer' => \App\Combinations\CombinationDisplay::DISCLAIMER,
]));

Route::prefix('api')->group(function () {
    // WEEK-001, Phần 2.4: tuần ví dụ công khai, đặt trước route /week-plans/{id} để không bị nuốt.
    Route::get('/week-plans/example', [\App\Http\Controllers\PublicWeb\WeekPlanController::class, 'example']);
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
        // WEEK-001, Phần 2.3: tuần ăn của chính người dùng.
        Route::get('/week-plans', [\App\Http\Controllers\PublicWeb\WeekPlanController::class, 'index']);
        Route::post('/week-plans', [\App\Http\Controllers\PublicWeb\WeekPlanController::class, 'store']);
        Route::get('/week-plans/{weekPlan}', [\App\Http\Controllers\PublicWeb\WeekPlanController::class, 'show']);
        // WEEK-001, Phần 2.5, Task 1: đổi một bữa.
        Route::get('/week-plans/{weekPlan}/meals/{meal}/candidates', [\App\Http\Controllers\PublicWeb\WeekPlanController::class, 'mealCandidates']);
        Route::post('/week-plans/{weekPlan}/meals/{meal}/swap', [\App\Http\Controllers\PublicWeb\WeekPlanController::class, 'swapMeal']);
        // WEEK-001, Phần 2.5, Task 2: đổi ngày bắt đầu tuần.
        Route::post('/week-plans/{weekPlan}/change-start', [\App\Http\Controllers\PublicWeb\WeekPlanController::class, 'changeStart']);
        // WEEK-001, Phần 2.5, Task 3: bắt đầu lại phần còn lại của tuần.
        Route::post('/week-plans/{weekPlan}/restart-remaining', [\App\Http\Controllers\PublicWeb\WeekPlanController::class, 'restartRemaining']);
        // WEEK-001, Phần 2.5, Task 4: đánh dấu ăn ngoài.
        Route::post('/week-plans/{weekPlan}/meals/{meal}/eat-out', [\App\Http\Controllers\PublicWeb\WeekPlanController::class, 'eatOut']);
        // WEEK-001, Phần 2.5, Task 5: loại một thực phẩm khỏi tuần.
        Route::post('/week-plans/{weekPlan}/exclude-ingredient', [\App\Http\Controllers\PublicWeb\WeekPlanController::class, 'excludeIngredient']);
        // WEEK-001, Phần 2.6: danh sách đi chợ theo gram.
        Route::get('/week-plans/{weekPlan}/shopping-list', [\App\Http\Controllers\PublicWeb\WeekPlanController::class, 'shoppingList']);
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
    Route::get('/vegetables', [\App\Http\Controllers\PublicWeb\VegetableController::class, 'index']);
    Route::get('/vegetables/{vegetable:slug}', [\App\Http\Controllers\PublicWeb\VegetableController::class, 'show']);
    Route::get('/drinks', [\App\Http\Controllers\PublicWeb\DrinkController::class, 'index']);
    Route::get('/drinks/{drink:slug}', [\App\Http\Controllers\PublicWeb\DrinkController::class, 'show']);
    Route::post('/shake', [\App\Http\Controllers\PublicWeb\CatalogController::class, 'shake']);
    Route::post('/shake/tray-swap', [\App\Http\Controllers\PublicWeb\CatalogController::class, 'traySwapRandom']);
    Route::get('/shake/tray-candidates', [\App\Http\Controllers\PublicWeb\CatalogController::class, 'traySwapCandidates']);
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

// --- Quản trị tối thiểu cho 3 module chưa có trong admin React (hoàn thiện 5b) ---
// Sửa trực tiếp file data/ (QT-3); xem lý do chọn Blade trong QuanTriController.
Route::middleware(['auth', 'admin'])->prefix('quan-tri')->name('quantri.')->group(function () {
    Route::get('/', [\App\Http\Controllers\QuanTriController::class, 'index'])->name('index');
    Route::get('/{module}', [\App\Http\Controllers\QuanTriController::class, 'list'])->name('list')->where('module', 'ket-hop|rau-mua|do-uong');
    Route::get('/{module}/{slug}/sua', [\App\Http\Controllers\QuanTriController::class, 'edit'])->name('edit')->where('module', 'ket-hop|rau-mua|do-uong');
    Route::post('/{module}/{slug}/sua', [\App\Http\Controllers\QuanTriController::class, 'update'])->name('update')->where('module', 'ket-hop|rau-mua|do-uong');
});
