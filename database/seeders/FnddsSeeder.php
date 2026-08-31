<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

/**
 * Nạp bộ KIỂM CHỨNG engine (DATA-003) từ data/reference/usda-fndds-*.csv.
 *
 * Không phải dữ liệu hiển thị — chỉ dùng cho TEST-002.
 */
class FnddsSeeder extends Seeder
{
    public function run(): void
    {
        $foods = base_path('data/reference/usda-fndds-foods.csv');
        $links = base_path('data/reference/usda-fndds-srlinks.csv');
        if (! is_file($foods) || ! is_file($links)) {
            $this->command?->warn('Chưa có data/reference/usda-fndds-*.csv — bỏ qua (chạy lacdoi:import-usda --source=fndds).');

            return;
        }

        DB::table('fndds_food_inputs')->delete();
        DB::table('fndds_foods')->delete();

        $now = Date::now();
        $nFoods = 0;
        foreach (array_chunk(iterator_to_array($this->rows($foods)), 500) as $chunk) {
            DB::table('fndds_foods')->insert(array_map(fn ($r) => [
                'food_code' => $r['food_code'],
                'fdc_id' => $r['fdc_id'],
                'description' => $r['description'],
                'wweia_category' => $r['wweia_category'] ?: null,
                'kcal_per_100g' => $r['kcal_per_100g'],
                'protein_g' => $r['protein_g'] ?: null,
                'fat_g' => $r['fat_g'] ?: null,
                'carb_g' => $r['carb_g'] ?: null,
                'fiber_g' => $r['fiber_g'] ?: null,
                'cooking_method' => $r['cooking_method'] ?: null,
                'imported_at' => $now,
            ], $chunk));
            $nFoods += count($chunk);
        }

        $nLinks = 0;
        foreach (array_chunk(iterator_to_array($this->rows($links)), 500) as $chunk) {
            DB::table('fndds_food_inputs')->insert(array_map(fn ($r) => [
                'food_code' => $r['food_code'],
                'sr_code' => $r['sr_code'],
                'sr_fdc_id' => $r['sr_fdc_id'] ?: null,
                'sr_description' => $r['sr_description'],
                'gram_weight' => $r['gram_weight'],
                'retention_code' => $r['retention_code'] ?: null,
            ], $chunk));
            $nLinks += count($chunk);
        }

        $this->command?->info("Đã nạp {$nFoods} Food Code FNDDS và {$nLinks} dòng SR Links (bộ kiểm chứng engine).");
    }

    /** @return iterable<array<string, string>> */
    private function rows(string $path): iterable
    {
        $fh = fopen($path, 'rb');
        $header = fgetcsv($fh, escape: '\\');
        while (($r = fgetcsv($fh, escape: '\\')) !== false) {
            yield array_combine($header, $r);
        }
        fclose($fh);
    }
}
