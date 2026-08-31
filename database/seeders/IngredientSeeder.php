<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\IngredientUnit;
use App\Models\ReferenceFood;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Đọc data/ingredients/<slug>.json → bảng ingredients + ingredient_units.
 *
 * File là nguồn sự thật (QT-3). Định dạng: DATA-REPOSITORY §3.
 * Khối reference_food của file được giữ nguyên vào nutrition_snapshot
 * (bản chụp lúc tra cứu — DATA-MODEL §3.1); reference_food_id chỉ được
 * gán khi DATA-002 nhập bảng tham chiếu và đối chiếu khớp.
 */
class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $dir = base_path('data/ingredients');
        $files = glob($dir.'/*.json') ?: [];
        $files = array_values(array_filter($files, fn ($f) => ! str_starts_with(basename($f), '_')));

        if ($files === []) {
            throw new RuntimeException("Không có file nguyên liệu nào trong {$dir} — kiểm tra lại checkout.");
        }

        foreach ($files as $file) {
            $j = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            $ref = $j['reference_food'] ?? [];

            if (($j['slug'] ?? '') !== basename($file, '.json')) {
                throw new RuntimeException("Slug trong file không khớp tên file: {$file}");
            }

            $ingredient = Ingredient::updateOrCreate(
                ['slug' => $j['slug']],
                [
                    'name_vi' => $j['name_vi'],
                    'name_en' => $j['name_en'] ?? null,
                    'reference_food_id' => $this->linkReference($j['slug'], $ref),
                    'mapping_confidence' => $ref['mapping_confidence'] ?? 'low',
                    'mapping_note' => $ref['mapping_note'] ?? null,
                    'category' => $j['category'],
                    'density_g_per_ml' => $j['density_g_per_ml'] ?? null,
                    'default_yield_factor' => $j['default_yield_factor'] ?? null,
                    'default_yield_factor_note' => $j['default_yield_factor_note'] ?? null,
                    'oil_absorption_pct' => $j['oil_absorption_pct'] ?? null,
                    'edible_portion_pct' => $j['edible_portion_pct'] ?? null,
                    'edible_portion_note' => $j['edible_portion_note'] ?? null,
                    'is_ready_to_eat' => $j['is_ready_to_eat'] ?? false,
                    'density_note' => $j['density_note'] ?? null,
                    'source_url' => $ref['source_url'] ?? null,
                    'scientific_name' => $ref['scientific_name'] ?? null,
                    'nutrition_snapshot' => $ref,
                    'alternatives' => $ref['alternatives'] ?? [],
                    'secondary_sources' => $j['secondary_sources'] ?? [],
                    'source' => $j['source'],
                    'confidence' => $j['confidence'],
                    'needs_review' => $j['needs_review'] ?? true,
                ],
            );

            IngredientUnit::where('ingredient_id', $ingredient->id)->delete();
            foreach ($j['units'] ?? [] as $unit) {
                IngredientUnit::create([
                    'ingredient_id' => $ingredient->id,
                    'unit_name' => $unit['unit_name'],
                    'grams' => $unit['grams'],
                    'note' => $unit['note'] ?? null,
                    'source' => $unit['source'],
                ]);
            }
        }

        $this->command?->info('Đã seed '.count($files).' nguyên liệu từ data/ingredients/.');
        if ($this->linked > 0 || $this->mismatched > 0) {
            $this->command?->info("Ánh xạ bảng tham chiếu: {$this->linked} khớp, {$this->mismatched} LỆCH (giữ null, cần điều tra — DATA-MODEL §3.1).");
        }
    }

    private int $linked = 0;

    private int $mismatched = 0;

    /**
     * Đối chiếu bản chụp với bảng tham chiếu (DATA-MODEL §3.1):
     *  - Khớp (chênh kcal ≤ 0,5) ⇒ gán reference_food_id, engine dùng reference_foods,
     *    snapshot giữ lại làm dấu vết.
     *  - LỆCH ⇒ giữ null và CẢNH BÁO — một trong hai sai, phải điều tra,
     *    không âm thầm ghi đè.
     *
     * @param  array<string, mixed>  $ref  khối reference_food của file
     */
    private function linkReference(string $slug, array $ref): ?int
    {
        $source = $ref['fct_source'] ?? null;
        $externalId = $ref['external_id'] ?? null;
        $snapKcal = $ref['kcal_per_100g'] ?? null;
        if ($source === null || $externalId === null || $snapKcal === null) {
            return null;
        }

        $row = ReferenceFood::where('fct_source', $source)
            ->where('external_id', (string) $externalId)
            ->first();
        if ($row === null || $row->kcal_per_100g === null) {
            return null; // nguồn chưa được nhập vào data/reference/ — hợp lệ
        }

        if (abs($row->kcal_per_100g - (float) $snapKcal) > 0.5) {
            $this->mismatched++;
            $this->command?->warn(sprintf(
                '  LỆCH [%s]: snapshot %s kcal ≠ reference_foods %s kcal (%s %s) — giữ null, cần điều tra',
                $slug, $snapKcal, $row->kcal_per_100g, $source, $externalId,
            ));

            return null;
        }

        $this->linked++;

        return $row->id;
    }
}
