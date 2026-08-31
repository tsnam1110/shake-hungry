<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Dish;
use App\Models\Ingredient;
use Illuminate\Console\Command;

/**
 * lacdoi:export-data — xuất database ngược ra data/**\/*.json (DATA-007).
 *
 * Yêu cầu cốt lõi: JSON ỔN ĐỊNH — khoá theo thứ tự cố định, số định dạng nhất
 * quán, mảng sắp theo khoá xác định, LF, UTF-8 không BOM, không escape Unicode,
 * thụt lề 2 dấu cách. Chạy hai lần liên tiếp phải cho git status sạch.
 *
 * KHÔNG xuất dữ liệu dẫn xuất: dish_nutrition, grams_resolved, dish_tcm_summary,
 * embedding — chúng được tính lại lúc seed (hai nguồn sự thật là điều QT-3 cấm).
 */
class LacdoiExportData extends Command
{
    protected $signature = 'lacdoi:export-data';

    protected $description = 'Xuất dữ liệu từ database ra data/**/*.json (JSON ổn định, DATA-007)';

    /** Thứ tự khoá cố định của khối reference_food — theo DATA-REPOSITORY §3. */
    private const array REFERENCE_FOOD_KEYS = [
        'fct_source', 'external_id', 'source_url', 'scientific_name',
        'kcal_per_100g', 'kcal_min', 'kcal_max',
        'protein_g', 'fat_g', 'carb_g', 'fiber_g', 'water_g', 'sodium_mg',
        'mapping_confidence', 'mapping_note', 'alternatives',
    ];

    public function handle(): int
    {
        $index = [];

        foreach (Ingredient::with('units')->orderBy('slug')->get() as $ing) {
            $payload = $this->ingredientPayload($ing);
            $this->writeJson(base_path("data/ingredients/{$ing->slug}.json"), $payload);
            $index[] = ['slug' => $ing->slug, 'name_vi' => $ing->name_vi, 'category' => $ing->category];
        }

        $this->writeJson(base_path('data/ingredients/_index.json'), [
            '_generated_by' => 'php artisan lacdoi:export-data — không sửa tay (DATA-REPOSITORY §2)',
            'count' => count($index),
            'ingredients' => $index,
        ]);

        $dishIndex = [];
        foreach (Dish::with(['names', 'mealSlots', 'dishIngredients.ingredient', 'recipeSources', 'media', 'parent'])->orderBy('slug')->get() as $dish) {
            $payload = $this->dishPayload($dish);
            $country = mb_strtolower($dish->country_code);
            @mkdir(base_path("data/dishes/{$country}"), 0777, true);
            $this->writeJson(base_path("data/dishes/{$country}/{$dish->slug}.json"), $payload);
            $primaryVi = $dish->names->first(fn ($n) => $n->locale === 'vi' && $n->is_primary)?->name;
            $dishIndex[] = ['slug' => $dish->slug, 'name' => $primaryVi, 'country_code' => $dish->country_code];
        }

        if ($dishIndex !== []) {
            $this->writeJson(base_path('data/dishes/_index.json'), [
                '_generated_by' => 'php artisan lacdoi:export-data — không sửa tay (DATA-REPOSITORY §2)',
                'count' => count($dishIndex),
                'dishes' => $dishIndex,
            ]);
        }

        $this->info(sprintf('Đã xuất %d nguyên liệu, %d món.', count($index), count($dishIndex)));

        return self::SUCCESS;
    }

    /** @return array<string, mixed> */
    private function ingredientPayload(Ingredient $ing): array
    {
        $snapshot = $ing->nutrition_snapshot ?? [];

        // Khoá theo thứ tự cố định; khoá lạ trong snapshot được giữ lại (sắp theo tên)
        // để không mất dữ liệu, nhưng thứ tự vẫn tất định.
        $ref = [];
        foreach (self::REFERENCE_FOOD_KEYS as $key) {
            if (array_key_exists($key, $snapshot)) {
                $ref[$key] = $snapshot[$key];
            }
        }
        $extra = array_diff_key($snapshot, array_flip(self::REFERENCE_FOOD_KEYS));
        ksort($extra);
        $ref += $extra;

        $units = $ing->units
            ->sortBy('unit_name', SORT_STRING)
            ->values()
            ->map(fn ($u) => [
                'unit_name' => $u->unit_name,
                'grams' => $u->grams,
                'note' => $u->note,
                'source' => $u->source,
            ])
            ->all();

        return [
            'slug' => $ing->slug,
            'name_vi' => $ing->name_vi,
            'name_en' => $ing->name_en,
            'category' => $ing->category,
            'reference_food' => $ref,
            'secondary_sources' => $ing->secondary_sources ?? [],
            'density_g_per_ml' => $ing->density_g_per_ml,
            'density_note' => $ing->density_note,
            'default_yield_factor' => $ing->default_yield_factor,
            'default_yield_factor_note' => $ing->default_yield_factor_note,
            'oil_absorption_pct' => $ing->oil_absorption_pct,
            'edible_portion_pct' => $ing->edible_portion_pct,
            'edible_portion_note' => $ing->edible_portion_note,
            'is_ready_to_eat' => $ing->is_ready_to_eat,
            'units' => $units,
            'source' => $ing->source,
            'confidence' => $ing->confidence,
            'needs_review' => $ing->needs_review,
        ];
    }

    /** @return array<string, mixed> */
    private function dishPayload(Dish $dish): array
    {
        $names = $dish->names
            ->sortBy([['locale', 'asc'], ['is_primary', 'desc'], ['name', 'asc']])
            ->values()
            ->map(function ($n) {
                $row = ['locale' => $n->locale, 'name' => $n->name, 'is_primary' => $n->is_primary];
                if ($n->is_alias) {
                    $row['is_alias'] = true;
                }

                return $row;
            })
            ->all();

        $slots = $dish->mealSlots
            ->sortBy('slot', SORT_STRING)
            ->values()
            ->map(fn ($s) => ['slot' => $s->slot, 'weight' => $s->weight])
            ->all();

        $ingredients = $dish->dishIngredients
            ->sortBy(fn ($di) => $di->ingredient->slug, SORT_STRING)
            ->values()
            ->map(function ($di) {
                $row = [
                    'ingredient_slug' => $di->ingredient->slug,
                    'quantity' => $di->quantity,
                    'unit_name' => $di->unit_name,
                ];
                if ($di->is_optional) {
                    $row['is_optional'] = true;
                }
                if ($di->is_discarded) {
                    $row['is_discarded'] = true;
                }
                if ($di->is_edible_weight) {
                    $row['is_edible_weight'] = true;
                }
                if ($di->note !== null) {
                    $row['note'] = $di->note;
                }
                $row['source'] = $di->source;
                if ($di->confidence !== null) {
                    $row['confidence'] = $di->confidence;
                }

                return $row;
            })
            ->all();

        $sources = $dish->recipeSources
            ->sortBy('url', SORT_STRING)
            ->values()
            ->map(fn ($s) => [
                'url' => $s->url,
                'accessed' => $s->accessed_at->format('Y-m-d'),
                'note' => $s->note,
            ])
            ->all();

        $media = $dish->media
            ->sortBy('path', SORT_STRING)
            ->values()
            ->map(fn ($m) => [
                'path' => $m->path,
                'license' => $m->license,
                'attribution' => $m->attribution,
                'is_primary' => $m->is_primary,
            ])
            ->all();

        return [
            'slug' => $dish->slug,
            'parent_dish_slug' => $dish->parent?->slug,
            'country_code' => $dish->country_code,
            'region_code' => $dish->region?->code,
            'names' => $names,
            'is_eat_out' => $dish->is_eat_out,
            'is_home_cook' => $dish->is_home_cook,
            'cooking_method' => $dish->cooking_method,
            'completeness_level' => $dish->completeness_level,
            'meal_slots' => $slots,
            'serving_desc' => $dish->serving_desc,
            'serving_grams' => $dish->serving_grams,
            'ingredients' => $ingredients,
            'recipe_steps' => $dish->recipe_steps,
            'recipe_sources' => $sources,
            'media' => $media,
            'estimated_kcal' => $dish->estimated_kcal,
            'external_ref' => $dish->external_ref,
            'import_note' => $dish->import_note,
            'status' => $dish->status,
            'source' => $dish->source,
            'confidence' => $dish->confidence,
            'needs_review' => $dish->needs_review,
        ];
    }

    /** @param array<string, mixed> $payload */
    private function writeJson(string $path, array $payload): void
    {
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR,
        );

        // json_encode thụt 4 dấu cách — chuẩn dự án là 2 (DATA-007).
        $json = preg_replace_callback(
            '/^ +/m',
            fn (array $m) => str_repeat(' ', intdiv(strlen($m[0]), 2)),
            $json,
        );

        // LF + newline cuối file, UTF-8 không BOM.
        file_put_contents($path, $json."\n");
    }
}
