<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\DishName;
use App\Models\Ingredient;
use App\Models\Tag;
use App\Taxonomy\DishTagDeriver;
use App\Taxonomy\TaggingRules;

/**
 * Hệ nhãn phân loại (TAXONOMY.md) — khoá 3 hành vi làm nên độ chính xác:
 *  1. Nhãn nguyên liệu sinh từ TaggingRules (đầu vào của mọi phép suy).
 *  2. DishTagDeriver suy diet/protein/allergen từ thành phần theo đúng định nghĩa
 *     (nước mắm = mặn; trứng/sữa đơn độc = chay-trứng-sữa; đạm hỗn hợp theo tỉ lệ).
 *  3. API lọc: AND giữa các facet, OR trong một facet, allergen_free loại đúng món.
 */
function taxonomyIngredient(string $slug, string $category): Ingredient
{
    $ingredient = Ingredient::create([
        'slug' => $slug,
        'name_vi' => 'Test '.$slug,
        'category' => $category,
        'mapping_confidence' => 'high',
        'source' => 'curated',
        'confidence' => 'high',
    ]);
    $ingredient->tags()->sync(Tag::idsFor(TaggingRules::tagsFor($slug, $category)));

    return $ingredient;
}

/** @param  list<array{0: Ingredient, 1: float}>  $rows */
function taxonomyDish(string $slug, array $rows, array $fileTags = []): Dish
{
    $dish = Dish::create([
        'slug' => $slug,
        'country_code' => 'VN',
        'completeness_level' => 1,
        'cooking_method' => 'nau',
        'source' => 'curated',
        'confidence' => 'medium',
    ]);
    DishName::create(['dish_id' => $dish->id, 'locale' => 'vi', 'name' => 'Món '.$slug, 'is_primary' => true]);
    foreach ($rows as [$ingredient, $grams]) {
        DishIngredient::create([
            'dish_id' => $dish->id, 'ingredient_id' => $ingredient->id,
            'quantity' => $grams, 'unit_name' => 'g', 'source' => 'curated',
        ]);
    }
    // Giống hệt DishSeeder: nhãn file + nhãn suy.
    $dish->load('dishIngredients.ingredient.tags');
    $dish->tags()->sync(Tag::idsFor([...$fileTags, ...DishTagDeriver::deriveForDish($dish)]));

    return $dish;
}

it('suy mon co nuoc mam la MAN du khong co thit ca nhin thay', function () {
    $dish = taxonomyDish('test-tax-ca', [
        [taxonomyIngredient('gao-te', 'tinh-bot'), 200],
        [taxonomyIngredient('nuoc-mam-truyen-thong', 'gia-vi'), 15],
    ]);
    expect(DishTagDeriver::deriveForDish($dish->load('dishIngredients.ingredient.tags')))
        ->toContain('diet:man')
        ->not->toContain('protein:ca'); // nước mắm không tính là đạm chính cá
});

it('suy mon dau phu + rau la CHAY thuan, dam dau', function () {
    $dish = taxonomyDish('test-tax-chay', [
        [taxonomyIngredient('dau-phu', 'khac'), 150],
        [taxonomyIngredient('rau-muong', 'rau-cu'), 100],
    ]);
    $derived = DishTagDeriver::deriveForDish($dish->load('dishIngredients.ingredient.tags'));
    expect($derived)->toContain('diet:chay')->toContain('protein:dau-phu-dau');
});

it('suy mon chi co trung la CHAY-TRUNG-SUA, dam trung', function () {
    $dish = taxonomyDish('test-tax-trung', [
        [taxonomyIngredient('trung-ga', 'thit'), 100],
        [taxonomyIngredient('ca-chua', 'rau-cu'), 80],
    ]);
    $derived = DishTagDeriver::deriveForDish($dish->load('dishIngredients.ingredient.tags'));
    expect($derived)->toContain('diet:chay-trung-sua')->toContain('protein:trung');
});

it('suy dam HON-HOP khi hai nhom dong vat san san nhau, mot nhom ap dao thi lay nhom do', function () {
    $bo = taxonomyIngredient('thit-bo-nam', 'thit');
    $ga = taxonomyIngredient('thit-ga-co-da', 'thit');
    $mixed = taxonomyDish('test-tax-hon-hop', [[$bo, 90], [$ga, 80]]);
    expect(DishTagDeriver::deriveForDish($mixed->load('dishIngredients.ingredient.tags')))
        ->toContain('protein:hon-hop')->toContain('diet:man');

    $single = taxonomyDish('test-tax-don-dam', [[$bo, 100], [$ga, 10]]);
    expect(DishTagDeriver::deriveForDish($single->load('dishIngredients.ingredient.tags')))
        ->toContain('protein:thit-bo');
});

it('hop nhat allergen tu moi nguyen lieu len mon', function () {
    $dish = taxonomyDish('test-tax-di-ung', [
        [taxonomyIngredient('lac-rang', 'khac'), 20],
        [taxonomyIngredient('ca-loc', 'thuy-san'), 150],
    ]);
    $derived = DishTagDeriver::deriveForDish($dish->load('dishIngredients.ingredient.tags'));
    expect($derived)->toContain('allergen:dau-phong')->toContain('allergen:ca');
});

it('API loc: AND giua facet, OR trong facet, allergen_free loai dung mon', function () {
    $a = taxonomyDish('test-api-com-chay', [[taxonomyIngredient('dau-phu', 'khac'), 150]], ['loai:com']);
    $b = taxonomyDish('test-api-com-man', [[taxonomyIngredient('thit-lon-nac-vai', 'thit'), 150]], ['loai:com']);
    $c = taxonomyDish('test-api-banh-lac', [[taxonomyIngredient('lac-rang', 'khac'), 60]], ['loai:banh']);

    $slugs = fn (string $url) => collect($this->getJson($url)->assertOk()->json('data'))->pluck('slug')->sort()->values()->all();

    expect($slugs('/api/dishes?tag=loai:com'))->toBe([$a->slug, $b->slug])
        ->and($slugs('/api/dishes?tag=loai:com&tag=diet:chay'))->toBe([$a->slug])
        ->and($slugs('/api/dishes?tag=loai:com,loai:banh'))->toBe([$c->slug, $a->slug, $b->slug]) // đã sort alphabet
        ->and($slugs('/api/dishes?allergen_free=dau-phong'))->toBe([$a->slug, $b->slug]);

    $tags = collect($this->getJson('/api/tags')->assertOk()->json())->keyBy('slug');
    expect($tags['loai:com']['dishes'])->toBe(2)
        ->and($tags['diet:chay']['dishes'])->toBe(2);
});

it('slug nhan ngoai dang ky bi tu choi ngay tai Tag::idsFor', function () {
    expect(fn () => Tag::idsFor(['loai:khong-ton-tai']))->toThrow(RuntimeException::class);
});
