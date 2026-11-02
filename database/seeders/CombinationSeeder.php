<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Combination;
use Illuminate\Database\Seeder;

/**
 * FOOD-COMBINE-001 — dựng bảng combinations từ data/combinations/*.json
 * (QT-3: file là nguồn sự thật). Chạy sau IngredientSeeder/FruitSeeder.
 */
class CombinationSeeder extends Seeder
{
    public function run(): void
    {
        foreach (glob(base_path('data/combinations/*.json')) ?: [] as $file) {
            $j = json_decode((string) file_get_contents($file), true);
            if (! is_array($j) || ! isset($j['slug'])) {
                continue;
            }
            $side = fn (array $s): array => [
                'type' => $s['type'],
                'ref' => $s['ref'],
                'label' => $s['label_vi'] ?? null,
                'members' => $s['members'] ?? null,
            ];
            $a = $side($j['side_a']);
            $b = $side($j['side_b']);
            Combination::updateOrCreate(
                ['slug' => $j['slug']],
                [
                    'side_a_type' => $a['type'], 'side_a_ref' => $a['ref'],
                    'side_a_label' => $a['label'], 'side_a_members' => $a['members'],
                    'side_b_type' => $b['type'], 'side_b_ref' => $b['ref'],
                    'side_b_label' => $b['label'], 'side_b_members' => $b['members'],
                    'category' => $j['category'],
                    'evidence_class' => $j['evidence_class'],
                    'science_status' => $j['science_status'],
                    'condition_vi' => $j['condition_vi'] ?? null,
                    'mechanism_vi' => $j['mechanism_vi'] ?? null,
                    'traditional_source' => $j['traditional_source'] ?? null,
                    'sources' => $j['sources'] ?? [],
                    'needs_review' => $j['needs_review'] ?? true,
                ],
            );
        }
    }
}
