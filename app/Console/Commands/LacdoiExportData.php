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

        foreach (Ingredient::with(['units', 'tags', 'aliases', 'media', 'tcmTraits', 'components'])->orderBy('slug')->get() as $ing) {
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
        foreach (Dish::with(['names', 'mealSlots', 'dishIngredients.ingredient', 'recipeSources', 'media', 'parent', 'tags'])->orderBy('slug')->get() as $dish) {
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

        // Hồ sơ hoa quả (FRUIT-005): định dạng riêng — JSON compact, khoá sắp
        // xếp theo bảng chữ cái ở mọi cấp, không newline cuối file.
        $fruitCount = 0;
        foreach (\App\Models\Fruit::with(['ingredient', 'prices'])->orderBy('slug')->get() as $fruit) {
            $this->writeCompactJson(base_path("data/fruits/{$fruit->slug}.json"), $this->fruitPayload($fruit));
            $fruitCount++;
        }

        $this->info(sprintf('Đã xuất %d nguyên liệu, %d món, %d hồ sơ quả.', count($index), count($dishIndex), $fruitCount));

        return self::SUCCESS;
    }

    /** @return array<string, mixed> */
    private function fruitPayload(\App\Models\Fruit $fruit): array
    {
        $prices = $fruit->prices->sortBy('id')->values()->map(function ($p) {
            $row = [
                'region_code' => $p->region_code,
                'month' => $p->month,
                'price_min' => $p->price_min,
                'price_max' => $p->price_max,
                'source_name' => $p->source_name,
                'collected_at' => $p->collected_at?->toDateString(),
            ];
            if ($p->source_url !== null) {
                $row['source_url'] = $p->source_url;
            }

            return $row;
        })->all();

        return [
            'slug' => $fruit->slug,
            'ingredient_slug' => $fruit->ingredient->slug,
            'classification' => [
                'group' => $fruit->classification_group,
                'family' => $fruit->family,
                'origin' => $fruit->origin,
            ],
            'grown_in' => $fruit->grown_in ?? [],
            'availability' => [
                'months' => $fruit->availability_months ?? [],
                'main_season_months' => $fruit->main_season_months ?? [],
                'availability_type' => $fruit->availability_type,
            ],
            'best_nutrition_window' => [
                'months' => $fruit->best_nutrition_months ?? [],
                'note' => $fruit->best_nutrition_note,
            ],
            'best_value_window' => [
                'months' => $fruit->best_value_months ?? [],
                'note' => $fruit->best_value_note,
            ],
            'pairing_cautions' => $fruit->pairing_cautions ?? [],
            // Ghi chú văn hoá (TCM-000): vắng khoá khi chưa có — JSON compact
            // tự sắp khoá theo bảng chữ cái nên vị trí ở đây không quan trọng.
            ...($fruit->cultural_note !== null ? ['cultural_note' => self::culturalNotePayload($fruit->cultural_note)] : []),
            'selection_note' => $fruit->selection_note,
            'storage_note' => $fruit->storage_note,
            'prices' => $prices,
            'needs_review' => $fruit->needs_review,
            'reviewed_by' => $fruit->reviewed_by,
            'reviewed_at' => $fruit->reviewed_at?->toDateString(),
            'review_note' => $fruit->review_note,
        ];
    }

    /** @param array<string, mixed> $payload */
    private function writeCompactJson(string $path, array $payload): void
    {
        $sort = function ($value) use (&$sort) {
            if (is_array($value)) {
                if (! array_is_list($value)) {
                    ksort($value);
                }
                return array_map($sort, $value);
            }

            return $value;
        };

        $json = json_encode(
            $sort($payload),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        file_put_contents($path, $json);
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

        $payload = [
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
            'reviewed_by' => $ing->reviewed_by,
            'reviewed_at' => $ing->reviewed_at?->toDateString(),
            'review_note' => $ing->review_note,
            'tags' => $this->orderedIngredientTags($ing),
        ];

        // Tên gọi khác (ALIAS-001): chỉ ghi khi có, để file không có alias
        // giữ nguyên từng byte; chèn sau name_en cho gần nhóm tên.
        $aliases = $ing->aliases->sortBy('name', SORT_STRING)->values()
            ->map(fn ($a) => array_filter(['name' => $a->name, 'note' => $a->note], fn ($v) => $v !== null))
            ->all();
        if ($aliases !== []) {
            $payload = array_merge(array_slice($payload, 0, 3), ['aliases' => $aliases], array_slice($payload, 3));
        }

        // Ảnh nguyên liệu (IMG-001): chỉ ghi khi có (file chưa có ảnh giữ
        // nguyên từng byte); chèn ngay sau 'units' cho gần nhóm định lượng.
        $media = $ing->media
            ->sortBy('path', SORT_STRING)
            ->values()
            ->map(fn ($m) => [
                'path' => $m->path,
                'license' => $m->license,
                'attribution' => $m->attribution,
                'is_primary' => $m->is_primary,
            ])
            ->all();
        if ($media !== []) {
            $pos = array_search('units', array_keys($payload), true);
            $payload = array_merge(
                array_slice($payload, 0, $pos + 1),
                ['media' => $media],
                array_slice($payload, $pos + 1),
            );
        }

        // Lớp tính vị (TCM-000): chỉ ghi khi có nhãn/liên kết — nguyên liệu
        // chưa có giữ nguyên từng byte. Thứ tự dòng tất định theo
        // (source_ref, nature, flavor) để vòng seed → export là điểm cố định.
        $traits = $ing->tcmTraits
            ->map(function ($t) {
                $row = [
                    'nature' => $t->nature,
                    'flavor' => $t->flavor,
                    'element' => $t->element,
                    'source_ref' => $t->source_ref,
                    'confidence' => $t->confidence,
                ];
                if ($t->usage_restricted) {
                    $row['usage_restricted'] = true;
                    $row['usage_note'] = $t->usage_note;
                }

                return $row;
            })
            ->all();
        usort($traits, fn ($a, $b) => strcmp(
            ($a['source_ref'] ?? '').'|'.($a['nature'] ?? '').'|'.($a['flavor'] ?? ''),
            ($b['source_ref'] ?? '').'|'.($b['nature'] ?? '').'|'.($b['flavor'] ?? ''),
        ));
        if ($traits !== []) {
            $payload = $this->insertAfterKey($payload, 'media', ['tcm_traits' => $traits], fallbackKey: 'units');
        }

        $components = $ing->components->pluck('slug')->sort(SORT_STRING)->values()->all();
        if ($components !== []) {
            $payload = $this->insertAfterKey($payload, 'tcm_traits', ['components' => $components], fallbackKey: 'media');
        }

        return $payload;
    }

    /**
     * Chèn khối $block ngay sau khoá $afterKey (hoặc $fallbackKey nếu khoá
     * chính vắng mặt; cuối mảng nếu cả hai đều vắng).
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    private function insertAfterKey(array $payload, string $afterKey, array $block, ?string $fallbackKey = null): array
    {
        $keys = array_keys($payload);
        $pos = array_search($afterKey, $keys, true);
        if ($pos === false && $fallbackKey !== null) {
            $pos = array_search($fallbackKey, $keys, true);
        }
        if ($pos === false) {
            return [...$payload, ...$block];
        }

        return array_merge(
            array_slice($payload, 0, $pos + 1),
            $block,
            array_slice($payload, $pos + 1),
        );
    }

    /**
     * Nhãn nguyên liệu ra file theo đúng thứ tự của TaggingRules::tagsFor
     * (origin, state, allergen...) để vòng seed → export là điểm cố định từng byte.
     * Slug lạ ngoài khuôn (không thể xảy ra qua seeder) được xếp cuối theo alphabet.
     *
     * @return list<string>
     */
    private function orderedIngredientTags(Ingredient $ing): array
    {
        $owned = $ing->tags->pluck('slug')->all();
        $ordered = array_values(array_intersect(\App\Taxonomy\TaggingRules::tagsFor($ing->slug, (string) $ing->category), $owned));
        $rest = array_values(array_diff($owned, $ordered));
        sort($rest);

        return [...$ordered, ...$rest];
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
                if ($n->note !== null) {
                    $row['note'] = $n->note;
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

        $payload = [
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
            'reviewed_by' => $dish->reviewed_by,
            'reviewed_at' => $dish->reviewed_at?->toDateString(),
            'review_note' => $dish->review_note,
            'tags' => $dish->tags->pluck('slug')
                // Chỉ xuất ngược các nhãn BIÊN TẬP (loai + dac-san — DATA-010);
                // nhãn suy từ thành phần không ghi vào file.
                ->filter(fn ($t) => str_starts_with((string) $t, 'loai:') || str_starts_with((string) $t, 'dac-san:'))
                ->sort()->values()->all(),
        ];

        // Đường nâng bù "Mức 2 (có note)" (TCM-000): chỉ ghi khi có căn cứ —
        // món thường giữ nguyên từng byte. Đặt ngay sau completeness_level.
        if ($dish->level_basis !== null) {
            $payload = $this->insertAfterKey($payload, 'completeness_level', [
                'level_basis' => $dish->level_basis,
                'level_note' => $dish->level_note,
            ]);
        }

        // Ghi chú văn hoá (TCM-000): chỉ ghi khi có, đặt sau import_note.
        if ($dish->cultural_note !== null) {
            $payload = $this->insertAfterKey($payload, 'import_note', [
                'cultural_note' => self::culturalNotePayload($dish->cultural_note),
            ], fallbackKey: 'external_ref');
        }

        return $payload;
    }

    /**
     * Chuẩn hoá khối ghi chú văn hoá về khuôn cố định {text, sources[{ref, url}]}
     * để vòng seed → export là điểm cố định từng byte.
     *
     * @param  array<string, mixed>  $note
     * @return array<string, mixed>
     */
    private static function culturalNotePayload(array $note): array
    {
        $sources = collect($note['sources'] ?? [])
            ->map(fn ($s) => [
                'ref' => $s['ref'] ?? '',
                'url' => $s['url'] ?? null,
            ])
            ->sortBy(fn ($s) => $s['ref'].'|'.($s['url'] ?? ''), SORT_STRING)
            ->values()
            ->all();

        return [
            'text' => $note['text'] ?? '',
            'sources' => $sources,
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
