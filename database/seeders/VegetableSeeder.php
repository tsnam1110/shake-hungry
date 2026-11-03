<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Vegetable;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Đọc data/vegetables/<slug>.json → bảng vegetables.
 * File là nguồn sự thật (QT-3), theo catalogue rau theo mùa v2 (2026-11-03).
 * Chạy sau IngredientSeeder; ingredient_slug null được phép cho rau nhập
 * khẩu chưa có hồ sơ nguyên liệu trong repo (kcal khi đó cũng null).
 */
class VegetableSeeder extends Seeder
{
    public function run(): void
    {
        $dir = base_path('data/vegetables');
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

            $ingredientId = null;
            if (! empty($j['ingredient_slug'])) {
                $ingredientId = Ingredient::where('slug', $j['ingredient_slug'])->value('id')
                    ?? throw new RuntimeException("Hồ sơ rau {$j['slug']} trỏ tới nguyên liệu không tồn tại: {$j['ingredient_slug']}");
            }

            $regions = $j['availability_months_by_region'] ?? [];
            Vegetable::updateOrCreate(
                ['slug' => $j['slug']],
                [
                    'name_vi' => $j['name_vi'],
                    'ingredient_id' => $ingredientId,
                    'group' => $j['group'],
                    'supply' => $j['supply'],
                    'state' => $j['state'],
                    'months_bac' => $regions['bac'] ?? null,
                    'months_trung' => $regions['trung'] ?? null,
                    'months_nam' => $regions['nam'] ?? null,
                    'region_notes' => $j['region_notes'] ?? null,
                    'main_season_months' => $j['main_season_months'] ?? null,
                    'best_months' => $j['best_months'] ?? null,
                    'kcal_per_100g' => $j['kcal_per_100g'] ?? null,
                    'kcal_confidence' => $j['kcal_confidence'] ?? null,
                    'aliases' => $j['aliases'] ?? null,
                    'selection_note' => $j['selection_note'] ?? null,
                    'storage_note' => $j['storage_note'] ?? null,
                    'cooking_note' => $j['cooking_note'] ?? null,
                    'safety_note' => $j['safety_note'] ?? null,
                    'uncertain' => $j['uncertain'] ?? false,
                    'uncertain_note' => $j['uncertain_note'] ?? null,
                    'fresh_counterpart_slug' => $j['fresh_counterpart_slug'] ?? null,
                    'source_note' => $j['source_note'] ?? null,
                    'needs_review' => $j['needs_review'] ?? true,
                    'reviewed_by' => $j['reviewed_by'] ?? null,
                    'reviewed_at' => $j['reviewed_at'] ?? null,
                    'review_note' => $j['review_note'] ?? null,
                ],
            );
        }

        $this->command?->info('Đã seed '.count($files).' hồ sơ rau từ data/vegetables/.');
    }
}
