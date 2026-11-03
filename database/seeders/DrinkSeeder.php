<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Drink;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Đọc data/drinks/<slug>.json → bảng drinks.
 * File là nguồn sự thật (QT-3), theo catalogue nước uống Việt Nam v2.
 * Kind dong-goi không bao giờ có recipe_steps (validator ở tầng seed:
 * file đóng gói có bước làm sẽ fail to, không bỏ qua lặng lẽ).
 */
class DrinkSeeder extends Seeder
{
    public function run(): void
    {
        $dir = base_path('data/drinks');
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
            if (($j['kind'] ?? '') === 'dong-goi' && ! empty($j['recipe_steps'])) {
                throw new RuntimeException("Entry đóng gói {$j['slug']} có recipe_steps — cấm bịa công thức cho sản phẩm đóng gói.");
            }

            Drink::updateOrCreate(
                ['slug' => $j['slug']],
                [
                    'name_vi' => $j['name_vi'],
                    'drink_group' => $j['drink_group'],
                    'kind' => $j['kind'],
                    'temperature' => $j['temperature'] ?? null,
                    'glass' => $j['glass'] ?? null,
                    'serving_ml' => $j['serving_ml'] ?? null,
                    'technique' => $j['technique'] ?? null,
                    'ice' => $j['ice'] ?? null,
                    'garnish' => $j['garnish'] ?? null,
                    'ingredients' => $j['ingredients'] ?? null,
                    'recipe_steps' => $j['recipe_steps'] ?? null,
                    'sugar_added_g' => $j['sugar_added_g'] ?? null,
                    'sugar_note' => $j['sugar_note'] ?? null,
                    'caffeine_level' => $j['caffeine_level'] ?? null,
                    'alcohol' => $j['alcohol'] ?? false,
                    'alcohol_pct_estimate' => $j['alcohol_pct_estimate'] ?? null,
                    'alcohol_note' => $j['alcohol_note'] ?? null,
                    'safety_flags' => $j['safety_flags'] ?? null,
                    'difficulty' => $j['difficulty'] ?? null,
                    'prep_minutes' => $j['prep_minutes'] ?? null,
                    'best_occasion' => $j['best_occasion'] ?? null,
                    'food_pairing' => $j['food_pairing'] ?? null,
                    'packaging' => $j['packaging'] ?? null,
                    'sugar_range_g' => $j['sugar_range_g'] ?? null,
                    'alcohol_pct_range' => $j['alcohol_pct_range'] ?? null,
                    'label_note' => $j['label_note'] ?? null,
                    'brand_examples' => $j['brand_examples'] ?? null,
                    'cultural_note' => $j['cultural_note'] ?? null,
                    'recipe_sources' => $j['recipe_sources'] ?? null,
                    'mocktail_counterpart_slug' => $j['mocktail_counterpart_slug'] ?? null,
                    'uncertain' => $j['uncertain'] ?? false,
                    'uncertain_note' => $j['uncertain_note'] ?? null,
                    'source_note' => $j['source_note'] ?? null,
                    'needs_review' => $j['needs_review'] ?? true,
                    'reviewed_by' => $j['reviewed_by'] ?? null,
                    'reviewed_at' => $j['reviewed_at'] ?? null,
                    'review_note' => $j['review_note'] ?? null,
                ],
            );
        }

        $this->command?->info('Đã seed '.count($files).' entry nước uống từ data/drinks/.');
    }
}
