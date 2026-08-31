<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CookingYield;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Nạp bảng hệ số hao hụt khi nấu từ data/reference/usda-cooking-yields.csv
 * (bản đã lọc, đã commit — DATA-004). Máy trắng không cần tải lại nguồn gốc.
 */
class CookingYieldSeeder extends Seeder
{
    public function run(): void
    {
        $csv = base_path('data/reference/usda-cooking-yields.csv');
        if (! is_file($csv)) {
            $this->command?->warn('Chưa có data/reference/usda-cooking-yields.csv — bỏ qua (chạy lacdoi:import-usda --source=cooking_yields).');

            return;
        }

        $manifest = json_decode((string) file_get_contents(base_path('data/reference/MANIFEST.json')), true) ?? [];
        $importedAt = isset($manifest['usda-cooking-yields.csv']['downloaded_at'])
            ? Carbon::parse($manifest['usda-cooking-yields.csv']['downloaded_at'])
            : Carbon::now();

        $fh = fopen($csv, 'rb');
        $header = fgetcsv($fh, escape: '\\');
        $rows = [];
        while (($r = fgetcsv($fh, escape: '\\')) !== false) {
            $r = array_combine($header, $r);
            $rows[] = array_map(fn ($v) => $v === '' ? null : $v, $r) + [
                'fct_source' => 'usda_cooking_yields_r2',
                'imported_at' => $importedAt,
            ];
        }
        fclose($fh);

        CookingYield::query()->delete();
        foreach (array_chunk($rows, 500) as $chunk) {
            CookingYield::insert($chunk);
        }

        $this->command?->info('Đã nạp '.count($rows).' dòng hệ số hao hụt từ data/reference/usda-cooking-yields.csv.');
    }
}
