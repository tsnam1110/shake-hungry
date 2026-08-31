<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ReferenceFood;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * lacdoi:import-usda — nhập và LỌC dữ liệu USDA FoodData Central (DATA-006).
 *
 * Quy trình (DATA-REPOSITORY §5):
 *  1. Người dùng tải bản CSV gốc từ https://fdc.nal.usda.gov/download-datasets/
 *     và giải nén vào storage/imports/<tên-bản>/ (đã gitignore — KHÔNG commit).
 *  2. Lệnh này lọc còn các cột dùng đến, ghi data/reference/usda-<nguồn>.csv (có commit),
 *     cập nhật MANIFEST kèm SHA-256 của file zip gốc, và nạp vào reference_foods.
 *
 * Bẫy kỹ thuật đã biết (RESEARCH-PROTOCOL, ghi tại DATA-006):
 *  - Năng lượng lọc theo NUTRIENT ID, không theo tên: 1008 = kcal, 1062 = kJ
 *    (lọc theo tên sai 4,184 lần). Foundation dùng thêm 2047/2048 (Atwater).
 *  - Bản ghi Foundation có thể KHÔNG có năng lượng — kiểm tra trước khi nạp.
 */
class LacdoiImportUsda extends Command
{
    protected $signature = 'lacdoi:import-usda
        {--source=sr_legacy : sr_legacy | foundation | cooking_yields}
        {--dir= : thư mục chứa CSV đã giải nén (mặc định storage/imports/<source>)}
        {--zip= : đường dẫn file zip gốc để ghi SHA-256 vào MANIFEST}';

    protected $description = 'Nhập + lọc USDA FoodData Central vào data/reference/ và bảng reference_foods (DATA-006)';

    /** Nutrient id theo fdc: kcal chuẩn, kcal Atwater (Foundation), protein, fat, carb, fiber. */
    private const int NUTRIENT_KCAL = 1008;

    private const array NUTRIENT_KCAL_ATWATER = [2047, 2048];

    private const array NUTRIENT_MACROS = [1003 => 'protein_g', 1004 => 'fat_g', 1005 => 'carb_g', 1079 => 'fiber_g'];

    public function handle(): int
    {
        $source = (string) $this->option('source');
        if (! in_array($source, ['sr_legacy', 'foundation', 'cooking_yields'], true)) {
            $this->error("--source phải là sr_legacy, foundation hoặc cooking_yields, nhận được '{$source}'.");

            return self::FAILURE;
        }

        if ($source === 'cooking_yields') {
            return $this->importCookingYields();
        }

        $dir = (string) ($this->option('dir') ?: storage_path("imports/{$source}"));
        if (! is_dir($dir) || ! is_file("{$dir}/food.csv") || ! is_file("{$dir}/food_nutrient.csv")) {
            $this->error("Không tìm thấy {$dir}/food.csv và food_nutrient.csv.");
            $this->line('Tải bản CSV từ https://fdc.nal.usda.gov/download-datasets/ (public domain / CC0),');
            $this->line("giải nén vào {$dir} rồi chạy lại. File gốc KHÔNG commit (DATA-REPOSITORY §5).");

            return self::FAILURE;
        }

        $this->info("Đọc {$dir}/food.csv …");
        $foods = [];
        foreach ($this->csvRows("{$dir}/food.csv") as $row) {
            $foods[$row['fdc_id']] = ['name_en' => $row['description']];
        }

        $this->info("Đọc {$dir}/food_nutrient.csv (lọc theo nutrient id, không theo tên) …");
        $nutrition = [];
        foreach ($this->csvRows("{$dir}/food_nutrient.csv") as $row) {
            $fdcId = $row['fdc_id'];
            $nutrientId = (int) $row['nutrient_id'];
            $amount = $row['amount'] === '' ? null : (float) $row['amount'];
            if ($amount === null || ! isset($foods[$fdcId])) {
                continue;
            }

            if ($nutrientId === self::NUTRIENT_KCAL) {
                $nutrition[$fdcId]['kcal_per_100g'] = $amount;
            } elseif (in_array($nutrientId, self::NUTRIENT_KCAL_ATWATER, true)) {
                // Chỉ dùng khi không có 1008 (Foundation nhiều bản ghi thiếu 1008).
                $nutrition[$fdcId]['kcal_atwater'] ??= $amount;
            } elseif (isset(self::NUTRIENT_MACROS[$nutrientId])) {
                $nutrition[$fdcId][self::NUTRIENT_MACROS[$nutrientId]] = $amount;
            }
        }

        $fctSource = "usda_{$source}";
        $outPath = base_path("data/reference/usda-{$source}.csv");
        $out = fopen($outPath, 'wb');
        if ($out === false) {
            throw new RuntimeException("Không ghi được {$outPath}");
        }
        fputcsv($out, ['external_id', 'name_en', 'kcal_per_100g', 'protein_g', 'fat_g', 'carb_g', 'fiber_g'], escape: '\\');

        $kept = 0;
        $skippedNoEnergy = 0;
        DB::transaction(function () use ($foods, $nutrition, $fctSource, $out, &$kept, &$skippedNoEnergy) {
            foreach ($foods as $fdcId => $food) {
                $n = $nutrition[$fdcId] ?? [];
                $kcal = $n['kcal_per_100g'] ?? $n['kcal_atwater'] ?? null;
                if ($kcal === null) {
                    // Không có năng lượng ⇒ không nạp — tránh nguyên liệu không tính được calo.
                    $skippedNoEnergy++;

                    continue;
                }

                fputcsv($out, [
                    $fdcId, $food['name_en'], $kcal,
                    $n['protein_g'] ?? '', $n['fat_g'] ?? '', $n['carb_g'] ?? '', $n['fiber_g'] ?? '',
                ], escape: '\\');

                ReferenceFood::updateOrCreate(
                    ['fct_source' => $fctSource, 'external_id' => (string) $fdcId],
                    [
                        'name_en' => $food['name_en'],
                        'kcal_per_100g' => $kcal,
                        'protein_g' => $n['protein_g'] ?? null,
                        'fat_g' => $n['fat_g'] ?? null,
                        'carb_g' => $n['carb_g'] ?? null,
                        'fiber_g' => $n['fiber_g'] ?? null,
                        'imported_at' => Date::now(),
                    ],
                );
                $kept++;
            }
        });
        fclose($out);

        $this->updateManifest($source, $kept);

        $this->info("Đã nạp {$kept} dòng vào reference_foods ({$fctSource}); bỏ {$skippedNoEnergy} bản ghi không có năng lượng.");
        $this->line("Bản đã lọc: {$outPath} — nhớ commit cùng MANIFEST.");

        return self::SUCCESS;
    }

    /**
     * DATA-004 — USDA Table of Cooking Yields for Meat and Poultry, Release 2
     * (public domain, Ag Data Commons DOI 10.15482/USDA.ADC/1436671; file
     * USDA_CookingYields_MeatPoultry02.csv). NDB number được nối sang fdc_id
     * SR Legacy qua sr_legacy_food.csv để tra được theo nguyên liệu đã ánh xạ.
     */
    private function importCookingYields(): int
    {
        $csv = (string) ($this->option('dir') ?: storage_path('imports')).'/USDA_CookingYields_MeatPoultry02.csv';
        if (! is_file($csv)) {
            $this->error("Không tìm thấy {$csv}.");
            $this->line('Tải USDA_CookingYields_MeatPoultry02.csv từ Ag Data Commons (public domain):');
            $this->line('https://agdatacommons.nal.usda.gov/articles/dataset/USDA_Table_of_Cooking_Yields_for_Meat_and_Poultry/24660864');

            return self::FAILURE;
        }

        // Bảng NDB → fdc_id từ bộ SR Legacy đã giải nén.
        $ndbToFdc = [];
        $srMap = storage_path('imports/sr_legacy_extract/FoodData_Central_sr_legacy_food_csv_2018-04/sr_legacy_food.csv');
        if (is_file($srMap)) {
            foreach ($this->csvRows($srMap) as $row) {
                $ndbToFdc[ltrim($row['NDB_number'], '0')] = $row['fdc_id'];
            }
        } else {
            $this->warn('Thiếu sr_legacy_food.csv — external_id (fdc_id) sẽ để trống.');
        }

        $outPath = base_path('data/reference/usda-cooking-yields.csv');
        $out = fopen($outPath, 'wb');
        fputcsv($out, ['ndb_number', 'external_id', 'food_description', 'preparation_method', 'yield_pct', 'yield_min_pct', 'yield_max_pct', 'moisture_change_pct', 'fat_change_pct', 'sample_n', 'release_year'], escape: '\\');

        $na = fn (string $v): ?string => in_array(trim($v), ['', 'n/a'], true) ? null : trim($v);
        $kept = 0;

        DB::transaction(function () use ($csv, $ndbToFdc, $out, $na, &$kept) {
            DB::table('cooking_yields')->delete();
            foreach ($this->csvRows($csv) as $row) {
                // Header gốc có khoảng trắng thừa — chuẩn hoá khoá.
                $r = [];
                foreach ($row as $k => $v) {
                    $r[trim((string) $k)] = $v;
                }
                $ndb = ltrim(trim((string) $r['NDB']), '0');
                $yield = $na((string) $r['Cooking Yield %']);
                if ($ndb === '' || $yield === null) {
                    continue;
                }
                $record = [
                    'ndb_number' => $ndb,
                    'external_id' => $ndbToFdc[$ndb] ?? null,
                    'food_description' => trim((string) $r['Yield Description']),
                    'preparation_method' => trim((string) $r['Preparation Method1']),
                    'yield_pct' => round((float) $yield, 2),
                    'yield_min_pct' => $na((string) $r['Yield Minimum %']),
                    'yield_max_pct' => $na((string) $r['Yield Maximum %']),
                    'moisture_change_pct' => $na((string) $r['Moisture Gain/ Loss %']),
                    'fat_change_pct' => $na((string) $r['Fat   Gain/ Loss %']),
                    'sample_n' => $na((string) $r['n']),
                    'release_year' => $na((string) $r['Release Year2']),
                ];
                fputcsv($out, array_values($record), escape: '\\');
                DB::table('cooking_yields')->insert($record + [
                    'fct_source' => 'usda_cooking_yields_r2',
                    'imported_at' => Date::now(),
                ]);
                $kept++;
            }
        });
        fclose($out);

        $manifestPath = base_path('data/reference/MANIFEST.json');
        $manifest = json_decode((string) file_get_contents($manifestPath), true) ?? [];
        $manifest['usda-cooking-yields.csv'] = [
            'source_url' => 'https://agdatacommons.nal.usda.gov/articles/dataset/USDA_Table_of_Cooking_Yields_for_Meat_and_Poultry/24660864',
            'source_release' => 'USDA Table of Cooking Yields for Meat and Poultry, Release 2 (file USDA_CookingYields_MeatPoultry02.csv)',
            'downloaded_at' => Date::now()->toDateString(),
            'original_sha256' => hash_file('sha256', $csv),
            'filtered_rows' => $kept,
            'license' => 'Public domain / CC0 (USDA ARS)',
            'filter_note' => 'Giữ toàn bộ dòng có Cooking Yield %; chuẩn hoá n/a → rỗng; nối NDB → fdc_id SR Legacy qua sr_legacy_food.csv. Bỏ cột Food Group Code.',
        ];
        $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $json = preg_replace_callback('/^ +/m', fn (array $m) => str_repeat(' ', intdiv(strlen($m[0]), 2)), $json);
        file_put_contents($manifestPath, $json."\n");

        $this->info("Đã nạp {$kept} dòng hệ số hao hụt vào cooking_yields; bản lọc: {$outPath}.");

        return self::SUCCESS;
    }

    /** @return iterable<array<string, string>> */
    private function csvRows(string $path): iterable
    {
        $fh = fopen($path, 'rb');
        if ($fh === false) {
            throw new RuntimeException("Không đọc được {$path}");
        }
        $header = fgetcsv($fh, escape: '\\');
        if ($header === false) {
            throw new RuntimeException("File rỗng: {$path}");
        }
        while (($row = fgetcsv($fh, escape: '\\')) !== false) {
            yield array_combine($header, $row);
        }
        fclose($fh);
    }

    private function updateManifest(string $source, int $rows): void
    {
        $manifestPath = base_path('data/reference/MANIFEST.json');
        $manifest = is_file($manifestPath)
            ? (json_decode((string) file_get_contents($manifestPath), true) ?? [])
            : [];

        $zip = (string) ($this->option('zip') ?? '');
        $sha = ($zip !== '' && is_file($zip)) ? hash_file('sha256', $zip) : null;
        if ($sha === null) {
            $this->warn('Không có --zip trỏ tới file gốc — MANIFEST sẽ thiếu original_sha256. Bổ sung để người sau tải đúng bản (DATA-006).');
        }

        $manifest["usda-{$source}.csv"] = [
            'source_url' => 'https://fdc.nal.usda.gov/download-datasets/',
            'source_release' => $source === 'sr_legacy' ? 'SR Legacy (April 2018, final)' : 'Foundation Foods',
            'downloaded_at' => Date::now()->toDateString(),
            'original_sha256' => $sha,
            'filtered_rows' => $rows,
            'license' => 'Public domain / CC0 1.0',
            'filter_note' => 'Chỉ giữ external_id, tên và 5 giá trị dinh dưỡng dùng đến (kcal 1008/2047-2048, protein 1003, fat 1004, carb 1005, fiber 1079). Bỏ bản ghi không có năng lượng.',
        ];

        $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $json = preg_replace_callback('/^ +/m', fn (array $m) => str_repeat(' ', intdiv(strlen($m[0]), 2)), $json);
        file_put_contents($manifestPath, $json."\n");
    }
}
