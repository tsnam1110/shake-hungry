<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Fruit;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Đọc data/fruits/<slug>.json → bảng fruits + fruit_prices.
 *
 * File là nguồn sự thật (QT-3). Định dạng: FRUIT-MODULE.md §2.
 * Chạy sau IngredientSeeder (hồ sơ tham chiếu nguyên liệu trai-cay).
 * Thư mục chưa có file nào thì bỏ qua (nhánh đang triển khai theo đợt).
 */
class FruitSeeder extends Seeder
{
    public function run(): void
    {
        $dir = base_path('data/fruits');
        if (! is_dir($dir)) {
            return;
        }
        $files = glob($dir.'/*.json') ?: [];
        $files = array_values(array_filter($files, fn ($f) => ! str_starts_with(basename($f), '_')));

        foreach ($files as $file) {
            $j = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

            if (($j['slug'] ?? '') !== basename($file, '.json')) {
                throw new RuntimeException("Slug trong file không khớp tên file: {$file}");
            }

            $ingredient = Ingredient::where('slug', $j['ingredient_slug'] ?? '')->first();
            if ($ingredient === null) {
                throw new RuntimeException("Hồ sơ quả {$j['slug']} trỏ tới nguyên liệu không tồn tại: ".($j['ingredient_slug'] ?? ''));
            }

            $c = $j['classification'] ?? [];
            $a = $j['availability'] ?? [];
            $fruit = Fruit::updateOrCreate(
                ['slug' => $j['slug']],
                [
                    'ingredient_id' => $ingredient->id,
                    'classification_group' => $c['group'] ?? '',
                    'family' => $c['family'] ?? null,
                    'origin' => $c['origin'] ?? '',
                    'grown_in' => $j['grown_in'] ?? null,
                    'availability_months' => $a['months'] ?? [],
                    'main_season_months' => $a['main_season_months'] ?? null,
                    'availability_type' => $a['availability_type'] ?? '',
                    'best_nutrition_months' => $j['best_nutrition_window']['months'] ?? null,
                    'best_nutrition_note' => $j['best_nutrition_window']['note'] ?? null,
                    'best_value_months' => $j['best_value_window']['months'] ?? null,
                    'best_value_note' => $j['best_value_window']['note'] ?? null,
                    'pairing_cautions' => $j['pairing_cautions'] ?? null,
                    'selection_note' => $j['selection_note'] ?? null,
                    'storage_note' => $j['storage_note'] ?? null,
                    'needs_review' => $j['needs_review'] ?? true,
                    'reviewed_by' => $j['reviewed_by'] ?? null,
                    'reviewed_at' => $j['reviewed_at'] ?? null,
                    'review_note' => $j['review_note'] ?? null,
                ],
            );

            $fruit->prices()->delete();
            foreach ($j['prices'] ?? [] as $p) {
                $fruit->prices()->create([
                    'region_code' => $p['region_code'],
                    'month' => $p['month'],
                    'price_min' => $p['price_min'],
                    'price_max' => $p['price_max'],
                    'source_name' => $p['source_name'],
                    'source_url' => $p['source_url'] ?? null,
                    'collected_at' => $p['collected_at'],
                ]);
            }
        }
    }
}
