<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ReferenceFood;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * Nạp bảng tham chiếu từ data/reference/usda-*.csv — bản ĐÃ LỌC do
 * lacdoi:import-usda sinh ra và đã commit (DATA-REPOSITORY §5).
 *
 * Nhờ vậy máy trắng clone về + migrate:fresh --seed là có đủ bảng tham chiếu,
 * không cần tải lại 1,6 GB USDA gốc (QT-3).
 */
class ReferenceFoodSeeder extends Seeder
{
    public function run(): void
    {
        $manifestPath = base_path('data/reference/MANIFEST.json');
        $manifest = is_file($manifestPath)
            ? (json_decode((string) file_get_contents($manifestPath), true) ?? [])
            : [];

        $total = 0;
        foreach (glob(base_path('data/reference/usda-*.csv')) ?: [] as $csv) {
            $file = basename($csv);
            $source = 'usda_'.str_replace(['usda-', '.csv'], '', $file);
            $importedAt = isset($manifest[$file]['downloaded_at'])
                ? Carbon::parse($manifest[$file]['downloaded_at'])
                : Carbon::now();

            $fh = fopen($csv, 'rb');
            if ($fh === false) {
                throw new RuntimeException("Không đọc được {$csv}");
            }
            $header = fgetcsv($fh, escape: '\\');
            $rows = [];
            while (($row = fgetcsv($fh, escape: '\\')) !== false) {
                $r = array_combine($header, $row);
                $rows[] = [
                    'fct_source' => $source,
                    'external_id' => $r['external_id'],
                    'name_en' => $r['name_en'],
                    'kcal_per_100g' => $r['kcal_per_100g'] === '' ? null : $r['kcal_per_100g'],
                    'protein_g' => $r['protein_g'] === '' ? null : $r['protein_g'],
                    'fat_g' => $r['fat_g'] === '' ? null : $r['fat_g'],
                    'carb_g' => $r['carb_g'] === '' ? null : $r['carb_g'],
                    'fiber_g' => $r['fiber_g'] === '' ? null : $r['fiber_g'],
                    'imported_at' => $importedAt,
                ];
                if (count($rows) === 500) {
                    ReferenceFood::upsert($rows, ['fct_source', 'external_id']);
                    $total += count($rows);
                    $rows = [];
                }
            }
            fclose($fh);
            if ($rows !== []) {
                ReferenceFood::upsert($rows, ['fct_source', 'external_id']);
                $total += count($rows);
            }
        }

        $this->command?->info("Đã nạp {$total} dòng tham chiếu từ data/reference/usda-*.csv.");
    }
}
