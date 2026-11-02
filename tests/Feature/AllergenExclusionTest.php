<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\User;
use App\Recommendation\Recommender;
use App\Taxonomy\AllergenPreset;
use App\Taxonomy\TaggingRules;
use Database\Seeders\DishSeeder;
use Database\Seeders\IngredientSeeder;
use Database\Seeders\RegionSeeder;

/**
 * AUDIT-ALLERGEN → ALLERGEN-FIX (FIX-A1…A3): khoá các tính chất an toàn của
 * lọc cứng dị ứng ở Lắc/Hồ sơ:
 *  1. Danh sách loại trừ ở giao diện SINH TỪ TaggingRules — trùng khớp tập
 *     slug có nhãn của từng nhóm (preset viết tay cũ đóng băng từ REC-001
 *     làm rò đậu nành 36 món, tôm 22, lúa mì 11... đo bằng pool() thật).
 *  2. Hai trang Lắc + Hồ sơ nhúng đúng danh sách sinh ra, đủ 12 nhóm
 *     (11 nhóm gốc + Hạt cây QD-03).
 *  3. Đo lại bằng Recommender::pool() trên toàn kho: chọn nhóm nào thì pool
 *     loại 100% món mang cờ nhóm đó (rò = 0).
 *  4. FIX-A2: cha-bong mang cờ ca (thành phần có nước mắm).
 *  5. FIX-A3: luật tiền tố ca- — cá thật (thuỷ sản) đều có cờ; ca- ngoài
 *     thuỷ sản (cà chua, cà rốt, cà phê...) không bao giờ thành cá.
 *  6. QD-03 (2026-11-02): nhóm Hạt cây mới — preset sinh đúng 2 slug hạt cây
 *     thật của catalog (hanh-nhan, hat-dieu-rang); pool() rò 0 cho nhóm mới
 *     (vòng lặp đo rò bên dưới tự phủ mọi nhóm trong preset).
 *  7. QD-04 (2026-11-02): sa-te mang cờ tom — 3 món trước đây thiếu cờ tôm
 *     nhờ sa-te nay có cờ và bị loại khi chọn nhóm Tôm.
 */

const ALLERGEN_GROUP_ORDER = [
    'dau-phong', 'hat-cay', 'tom', 'cua', 'dong-vat-co-vo', 'ca', 'trung', 'sua',
    'dau-nanh', 'lua-mi', 'me', 'mam-tom',
];

/** Tập slug có nhãn của từng nhóm, tính ĐỘC LẬP từ TaggingRules + catalog DB. */
function expectedAllergenSlugs(): array
{
    $expected = [];
    foreach (Ingredient::all() as $ingredient) {
        foreach (TaggingRules::allergensFor($ingredient->slug, (string) $ingredient->category) as $group) {
            $expected[$group][] = $ingredient->slug;
        }
    }
    foreach ($expected as &$slugs) {
        sort($slugs);
    }

    return $expected;
}

it('FIX-A1: preset loai tru trung khop tap slug co nhan cua tung nhom theo TaggingRules', function () {
    $this->seed(IngredientSeeder::class);

    $expected = expectedAllergenSlugs();
    $groups = AllergenPreset::groups();

    expect(array_column($groups, 'group'))->toBe(ALLERGEN_GROUP_ORDER);
    foreach ($groups as $group) {
        expect($group['slugs'])->toBe($expected[$group['group']] ?? []);
    }

    $byGroup = array_column($groups, 'slugs', 'group');
    // Các slug từng bị preset cũ bỏ sót (nguyên nhân rò rỉ đã đo ở AUDIT-ALLERGEN §5)
    expect($byGroup['dau-nanh'])->toContain('nuoc-tuong')
        ->and($byGroup['tom'])->toContain('tom-cang-xanh', 'muoi-tom')
        // QD-04: sa-te có tôm khô (định danh đã chốt 2026-11-02)
        ->and($byGroup['tom'])->toContain('sa-te')
        // QD-03: nhóm Hạt cây — census catalog chỉ có đúng 2 slug hạt cây thật
        ->and($byGroup['hat-cay'])->toBe(['hanh-nhan', 'hat-dieu-rang'])
        ->and($byGroup['cua'])->toContain('ghe')
        ->and($byGroup['mam-tom'])->toContain('mam-ruoc', 'mam-tom-chua')
        ->and($byGroup['me'])->toContain('vung', 'vung-rang', 'dau-me')
        ->and($byGroup['ca'])->toContain('nuoc-mam-truyen-thong', 'mam-nem')
        ->and($byGroup['dong-vat-co-vo'])->toContain('dau-hao', 'muc-ong')
        ->and($byGroup['lua-mi'])->toContain('bot-mi', 'mi-can')
        ->and($byGroup['trung'])->toContain('trung-vit-muoi')
        ->and($byGroup['sua'])->toContain('kem-whipping', 'pho-mai');
});

it('FIX-A1: trang Lac va trang Ho so nhung dung danh sach sinh ra, du 12 nhom', function () {
    $this->seed(IngredientSeeder::class);
    $chips = AllergenPreset::chips();
    expect($chips)->toHaveCount(12);

    $extract = function (string $html, string $var): ?array {
        if (! preg_match('/const '.$var.' = (\[[^\n]*\]);/', $html, $m)) {
            return null;
        }

        return json_decode($m[1], true);
    };

    $shakeHtml = $this->get('/')->assertOk()->getContent();
    expect($extract($shakeHtml, 'ALLERGENS'))->toBe($chips);

    $user = User::factory()->create();
    $profileHtml = $this->actingAs($user)->get('/ho-so')->assertOk()->getContent();
    expect($extract($profileHtml, 'ALLERGEN_PRESETS'))->toBe($chips);
});

it('FIX-A1: pool() voi danh sach sinh ra loai 100% mon mang co cua nhom — ro ri ve 0', function () {
    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);
    $this->seed(DishSeeder::class);

    $dishGroups = [];
    foreach (Dish::with('tags')->get() as $dish) {
        $dishGroups[$dish->slug] = array_values(array_map(
            fn (string $t) => substr($t, strlen('allergen:')),
            array_filter($dish->tags->pluck('slug')->all(), fn (string $t) => str_starts_with($t, 'allergen:')),
        ));
    }

    $pool = new ReflectionMethod(Recommender::class, 'pool');
    $pool->setAccessible(true);
    $recommender = app(Recommender::class);
    $callPool = fn (array $slugs) => $pool->invoke($recommender, ['slot' => 'trua'], 0, $slugs);

    // Sanity: không lọc thì pool vẫn chứa món mang cờ (phép đo không rỗng vô nghĩa)
    $unfiltered = $callPool([]);
    expect($unfiltered)->not->toBeEmpty()
        ->and($unfiltered->contains(fn (Dish $d) => in_array('ca', $dishGroups[$d->slug] ?? [], true)))->toBeTrue();

    foreach (AllergenPreset::groups() as $group) {
        $leaks = $callPool($group['slugs'])
            ->filter(fn (Dish $d) => in_array($group['group'], $dishGroups[$d->slug] ?? [], true));
        expect($leaks)->toBeEmpty("Nhóm {$group['group']} còn lọt: ".$leaks->pluck('slug')->implode(', '));
    }

    // Ca hồi quy cụ thể của AUDIT: bánh canh tôm càng từng lọt qua preset "Tôm" cũ
    $tomSlugs = array_column(AllergenPreset::groups(), 'slugs', 'group')['tom'];
    expect($callPool($tomSlugs)->pluck('slug'))->not->toContain('banh-canh-tom-cang');

    // QD-03 hồi quy: chè khúc bạch (có hạnh nhân) bị loại khi chọn nhóm Hạt cây
    $hatCaySlugs = array_column(AllergenPreset::groups(), 'slugs', 'group')['hat-cay'];
    expect($callPool($hatCaySlugs)->pluck('slug'))->not->toContain('che-khuc-bach');
});

it('QD-03/QD-04: sa-te co co tom o luat va file; 3 mon dung sa-te truoc day thieu co tom nay da co co', function () {
    expect(TaggingRules::allergensFor('sa-te', 'gia-vi'))->toContain('tom');
    $saTeFile = json_decode((string) file_get_contents(base_path('data/ingredients/sa-te.json')), true);
    expect($saTeFile['tags'])->toBe(TaggingRules::tagsFor('sa-te', (string) $saTeFile['category']));

    expect(TaggingRules::allergensFor('hat-dieu-rang', 'khac'))->toContain('hat-cay')
        ->and(TaggingRules::allergensFor('hanh-nhan', 'khac'))->toContain('hat-cay')
        // Hạt điều MÀU là annatto — không phải hạt điều, không gắn hạt cây
        ->and(TaggingRules::allergensFor('hat-dieu-mau', 'gia-vi'))->not->toContain('hat-cay');

    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);
    $this->seed(DishSeeder::class);

    $groupsOf = fn (string $slug) => Dish::where('slug', $slug)->firstOrFail()->tags
        ->pluck('slug')
        ->filter(fn (string $t) => str_starts_with($t, 'allergen:'))
        ->map(fn (string $t) => substr($t, strlen('allergen:')))
        ->all();

    // Trước QD-04 ba món này không có nguồn cờ tôm nào ngoài sa-te (đo baseline
    // 2026-11-02: banh-trang-phoi-suong [dau-phong, trung], lau-bo [ca,
    // dau-nanh, trung], vit-nuong-chao [dau-nanh]).
    foreach (['banh-trang-phoi-suong', 'lau-bo', 'vit-nuong-chao'] as $slug) {
        expect($groupsOf($slug))->toContain('tom');
    }
    // Chè khúc bạch là món duy nhất trong kho dùng hạnh nhân → có cờ hạt cây
    expect($groupsOf('che-khuc-bach'))->toContain('hat-cay');
});

it('FIX-A2: cha-bong mang co ca o ca bang luat va tags trong file', function () {
    expect(TaggingRules::allergensFor('cha-bong', 'thit'))->toContain('ca');

    $file = json_decode((string) file_get_contents(base_path('data/ingredients/cha-bong.json')), true);
    expect($file['tags'])->toBe(TaggingRules::tagsFor('cha-bong', (string) $file['category']));
});

it('FIX-A3: tien to ca- — ca that deu co co, ca- ngoai thuy san khong bao gio la ca', function () {
    $this->seed(IngredientSeeder::class);

    $caPrefix = Ingredient::query()->where('slug', 'like', 'ca-%')->get();
    expect($caPrefix)->not->toBeEmpty();

    $violations = [];
    foreach ($caPrefix as $ingredient) {
        $allergens = TaggingRules::allergensFor($ingredient->slug, (string) $ingredient->category);
        if ($ingredient->category === 'thuy-san' && ! in_array('ca', $allergens, true)) {
            $violations[] = "Cá thật {$ingredient->slug} thiếu cờ ca";
        }
        if ($ingredient->category !== 'thuy-san' && in_array('ca', $allergens, true)) {
            $violations[] = "{$ingredient->slug} không phải cá nhưng bị gắn cờ ca";
        }
    }
    expect($violations)->toBe([]);

    // Các ca biên đã biết đích danh: cà (rau/quả) + cà phê không phải cá
    foreach (['ca-chua', 'ca-rot', 'ca-tim', 'ca-na', 'ca-phe-den'] as $notFish) {
        $ingredient = Ingredient::where('slug', $notFish)->first();
        expect($ingredient)->not->toBeNull()
            ->and(TaggingRules::allergensFor($notFish, (string) $ingredient->category))->not->toContain('ca');
    }

    // Thuỷ sản không bắt đầu bằng ca- chỉ có cờ ca khi nằm tường minh trong bảng luật
    $explicitFish = ['luon', 'cha-ca-vien'];
    $violations = [];
    foreach (Ingredient::query()->where('category', 'thuy-san')->where('slug', 'not like', 'ca-%')->get() as $ingredient) {
        $hasFish = in_array('ca', TaggingRules::allergensFor($ingredient->slug, 'thuy-san'), true);
        if ($hasFish !== in_array($ingredient->slug, $explicitFish, true)) {
            $violations[] = $ingredient->slug;
        }
    }
    expect($violations)->toBe([]);
});
