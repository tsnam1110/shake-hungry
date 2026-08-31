<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\DishMealSlot;
use App\Models\DishMedia;
use App\Models\DishName;
use App\Models\DishRecipeSource;
use App\Models\Ingredient;
use App\Models\Region;
use App\Nutrition\DishNutritionService;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Đọc data/dishes/<country>/<slug>.json → nhóm bảng dishes (DATA-REPOSITORY §3).
 *
 * Hai lượt: lượt 1 tạo món, lượt 2 gán parent_dish_id (biến thể có thể được
 * liệt kê trước món chuẩn). Sau khi seed, tính dinh dưỡng bằng engine —
 * calo là dữ liệu DẪN XUẤT, không nằm trong file (trừ estimated_kcal Mức 1).
 */
class DishSeeder extends Seeder
{
    public function run(): void
    {
        $files = glob(base_path('data/dishes/*/*.json')) ?: [];
        $files = array_values(array_filter($files, fn ($f) => ! str_starts_with(basename($f), '_')));

        if ($files === []) {
            $this->command?->warn('data/dishes/ còn trống — chưa có món nào để seed (GĐ3 chưa bắt đầu).');

            return;
        }

        $parentLinks = [];

        foreach ($files as $file) {
            $j = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

            if (($j['slug'] ?? '') !== basename($file, '.json')) {
                throw new RuntimeException("Slug trong file không khớp tên file: {$file}");
            }

            $regionId = null;
            if (($j['region_code'] ?? null) !== null) {
                $regionId = Region::where('code', $j['region_code'])->value('id')
                    ?? throw new RuntimeException("{$file}: region_code '{$j['region_code']}' không có trong regions-vn.json.");
            }

            $dish = Dish::updateOrCreate(
                ['slug' => $j['slug']],
                [
                    'country_code' => $j['country_code'],
                    'region_id' => $regionId,
                    'is_eat_out' => $j['is_eat_out'] ?? false,
                    'is_home_cook' => $j['is_home_cook'] ?? false,
                    'cooking_method' => $j['cooking_method'] ?? null,
                    'completeness_level' => $j['completeness_level'] ?? 1,
                    'recipe_steps' => $j['recipe_steps'] ?? null,
                    'serving_desc' => $j['serving_desc'] ?? null,
                    'serving_grams' => $j['serving_grams'] ?? null,
                    'estimated_kcal' => $j['estimated_kcal'] ?? null,
                    'status' => $j['status'] ?? 'draft',
                    'source' => $j['source'],
                    'confidence' => $j['confidence'],
                    'needs_review' => $j['needs_review'] ?? true,
                ],
            );

            if (($j['parent_dish_slug'] ?? null) !== null) {
                $parentLinks[$dish->id] = $j['parent_dish_slug'];
            }

            DishName::where('dish_id', $dish->id)->delete();
            foreach ($j['names'] ?? [] as $name) {
                DishName::create([
                    'dish_id' => $dish->id,
                    'locale' => $name['locale'],
                    'name' => $name['name'],
                    'is_primary' => $name['is_primary'] ?? false,
                    'is_alias' => $name['is_alias'] ?? false,
                ]);
            }

            DishMealSlot::where('dish_id', $dish->id)->delete();
            foreach ($j['meal_slots'] ?? [] as $slot) {
                DishMealSlot::create([
                    'dish_id' => $dish->id,
                    'slot' => $slot['slot'],
                    'weight' => $slot['weight'],
                ]);
            }

            DishIngredient::where('dish_id', $dish->id)->delete();
            foreach ($j['ingredients'] ?? [] as $row) {
                // R9: slug phải trỏ tới nguyên liệu có thật — fail to, không bỏ qua.
                $ingredientId = Ingredient::where('slug', $row['ingredient_slug'])->value('id')
                    ?? throw new RuntimeException("{$file}: ingredient_slug '{$row['ingredient_slug']}' không tồn tại trong data/ingredients/ (kiểm tra V11).");

                DishIngredient::create([
                    'dish_id' => $dish->id,
                    'ingredient_id' => $ingredientId,
                    'quantity' => $row['quantity'],
                    'unit_name' => $row['unit_name'],
                    'is_optional' => $row['is_optional'] ?? false,
                    'is_discarded' => $row['is_discarded'] ?? false,
                    'is_edible_weight' => $row['is_edible_weight'] ?? false,
                    'note' => $row['note'] ?? null,
                    'source' => $row['source'] ?? 'curated',
                    'confidence' => $row['confidence'] ?? null,
                ]);
            }

            DishRecipeSource::where('dish_id', $dish->id)->delete();
            foreach ($j['recipe_sources'] ?? [] as $src) {
                DishRecipeSource::create([
                    'dish_id' => $dish->id,
                    'url' => $src['url'],
                    'accessed_at' => $src['accessed'],
                    'note' => $src['note'] ?? null,
                ]);
            }

            DishMedia::where('dish_id', $dish->id)->delete();
            foreach ($j['media'] ?? [] as $media) {
                DishMedia::create([
                    'dish_id' => $dish->id,
                    'path' => $media['path'],
                    'license' => $media['license'], // R4: thiếu license thì fail ngay tại đây
                    'attribution' => $media['attribution'] ?? null,
                    'is_primary' => $media['is_primary'] ?? false,
                ]);
            }
        }

        foreach ($parentLinks as $dishId => $parentSlug) {
            $parentId = Dish::where('slug', $parentSlug)->value('id')
                ?? throw new RuntimeException("parent_dish_slug '{$parentSlug}' không tồn tại.");
            Dish::whereKey($dishId)->update(['parent_dish_id' => $parentId]);
        }

        // Calo là dữ liệu dẫn xuất — tính lại lúc seed (DATA-REPOSITORY §7).
        $service = app(DishNutritionService::class);
        foreach (Dish::where('completeness_level', '>=', 2)->get() as $dish) {
            $service->computeAndStore($dish, $service->pickFctSource($dish));
        }

        $this->command?->info('Đã seed '.count($files).' món từ data/dishes/.');
    }
}
