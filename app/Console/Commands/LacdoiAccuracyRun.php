<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Nutrition\IngredientInput;
use App\Nutrition\NutritionCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * lacdoi:accuracy-run — TEST-002: đo độ chính xác engine trên bộ FNDDS.
 *
 * Với mỗi Food Code: chạy engine trên danh sách nguyên liệu SR Links (gram/100 g món)
 * rồi so với năng lượng USDA công bố cho CHÍNH món đó. Cả hai vế cùng nguồn USDA ⇒
 * lệch nhiều nghĩa là ENGINE SAI (ACCURACY-SPEC §4 bước 3).
 *
 * Báo cáo BỐN chỉ số (§4 bước 4), tách theo cách chế biến — một con số tổng thể
 * che được lỗi cụ thể ở nhóm chiên.
 */
class LacdoiAccuracyRun extends Command
{
    protected $signature = 'lacdoi:accuracy-run
        {--dataset=fndds : fndds | nutrition5k}
        {--write-report : ghi báo cáo vào docs/06-nutrition/accuracy-runs/}
        {--date= : ngày ghi trên báo cáo (YYYY-MM-DD), mặc định lấy từ --date của người chạy}';

    protected $description = 'Đo độ chính xác engine calo trên bộ kiểm chứng FNDDS (TEST-002)';

    public function handle(NutritionCalculator $calculator): int
    {
        if ($this->option('dataset') === 'nutrition5k') {
            return $this->runNutrition5k($calculator);
        }

        if ($this->option('dataset') !== 'fndds') {
            $this->error('--dataset phải là fndds hoặc nutrition5k.');

            return self::FAILURE;
        }

        $foods = DB::table('fndds_foods')->orderBy('food_code')->get();
        if ($foods->isEmpty()) {
            $this->error('Bảng fndds_foods trống — chạy `lacdoi:import-usda --source=fndds` trước.');

            return self::FAILURE;
        }

        // Bảng thành phần: SR Legacy, cùng nguồn với nguyên liệu SR Links (QT-4).
        $refs = DB::table('reference_foods')
            ->where('fct_source', 'usda_sr_legacy')
            ->get()
            ->keyBy('external_id');

        $inputsByFood = DB::table('fndds_food_inputs')->get()->groupBy('food_code');

        $results = [];
        $skipped = 0;

        foreach ($foods as $food) {
            $rows = $inputsByFood[$food->food_code] ?? collect();
            $ingredients = [];
            $unmapped = false;

            foreach ($rows as $row) {
                $ref = $row->sr_fdc_id !== null ? ($refs[$row->sr_fdc_id] ?? null) : null;
                if ($ref === null || $ref->kcal_per_100g === null) {
                    $unmapped = true;

                    break;
                }
                $ingredients[] = new IngredientInput(
                    slug: 'sr-'.$row->sr_code,
                    grams: (float) $row->gram_weight,
                    mappingConfidence: 'high', // ánh xạ do chính USDA cung cấp, không phải ta đoán
                    fctSource: 'usda_sr_legacy',
                    kcalPer100g: (float) $ref->kcal_per_100g,
                    proteinG: $ref->protein_g !== null ? (float) $ref->protein_g : null,
                    fatG: $ref->fat_g !== null ? (float) $ref->fat_g : null,
                    carbG: $ref->carb_g !== null ? (float) $ref->carb_g : null,
                    fiberG: $ref->fiber_g !== null ? (float) $ref->fiber_g : null,
                );
            }

            if ($unmapped || $ingredients === []) {
                $skipped++;

                continue;
            }

            // portionWeighed: true — SR Links cho gram chính xác, không phải khẩu phần
            // người dùng ước lượng. Nới ±15% ở đây sẽ đo nhầm thứ khác.
            $result = $calculator->compute(
                ingredients: $ingredients,
                cookingMethod: $food->cooking_method ?? 'song',
                fctSource: 'usda_sr_legacy',
                portionWeighed: true,
            );

            if (! $result->hasNumbers()) {
                $skipped++;

                continue;
            }

            $reference = (float) $food->kcal_per_100g;
            if ($reference <= 0 || $result->totalRawG <= 0) {
                $skipped++;

                continue;
            }

            // ⚠️ ĐÍNH CHÍNH (2026-08-31): `gram_weight` của SR Links là gram cho CẢ MẺ
            // công thức (có món tới 3.900 g), KHÔNG phải "gram trên 100 g món" như
            // DATA-003 bản đầu mô tả. So thẳng tổng kcal của mẻ với kcal/100 g công bố
            // cho bias +400%. Phải chuẩn hoá về 100 g trước khi so.
            //
            // Mẫu số là khối lượng THÀNH PHẨM: dùng cookedGramsEstimate khi engine suy
            // được (có yield factor), nếu không thì lấy tổng khối lượng nguyên liệu —
            // tức giả định bảo toàn khối lượng. Với món mất nước khi nấu, giả định này
            // làm engine THẤP hơn thực tế, và chính bias sẽ cho thấy điều đó.
            $finishedG = $result->cookedGramsEstimate ?? $result->totalRawG;
            $scale = 100.0 / $finishedG;

            $engineMin = $result->kcalMin * $scale;
            $engineMax = $result->kcalMax * $scale;
            $point = ($engineMin + $engineMax) / 2;
            $errPct = ($point - $reference) / $reference * 100;

            $results[] = [
                'food_code' => $food->food_code,
                'description' => $food->description,
                'method' => $food->cooking_method ?? 'khong-ro',
                'reference' => $reference,
                'engine' => $point,
                'kcal_min' => $engineMin,
                'kcal_max' => $engineMax,
                'err_pct' => $errPct,
                'in_range' => $engineMin <= $reference && $reference <= $engineMax,
                'confidence' => $result->confidence,
            ];
        }

        if ($results === []) {
            $this->error('Không tính được món nào — kiểm tra bảng reference_foods.');

            return self::FAILURE;
        }

        $this->renderReport($results, $skipped, $calculator::ENGINE_VERSION);

        if ($this->option('write-report')) {
            $this->writeReport($results, $skipped, $calculator::ENGINE_VERSION);
        }

        return self::SUCCESS;
    }

    /**
     * Nutrition5k (Google Research, CC BY 4.0 — ghi công trong README).
     *
     * ⚠️ ĐÍNH CHÍNH 2026-08-31: ACCURACY-SPEC §4 và TEST-002 mô tả bộ này là "calo ĐO
     * BẰNG THIẾT BỊ". Kiểm tra thực tế trên toàn bộ 4.766 món: `total_calories` bằng
     * đúng tổng (gram × cal/g) tra từ chính `ingredients_metadata.csv` của bộ — sai số
     * trung vị 0,00%, 100% số món khớp dưới 0,5 kcal. Thứ đo bằng thiết bị là **khối
     * lượng**, còn calo là **tính ra** từ một bảng thành phần.
     *
     * ⇒ Chạy engine trên chính các nguyên liệu đó rồi so với `total_calories` là một
     * phép thử VÒNG TRÒN. Nó chỉ kiểm được số học của engine (cộng dồn, quy đổi 100 g,
     * xử lý tuỳ chọn/vớt bỏ) — KHÔNG nói gì về chất lượng dinh dưỡng. Lệnh này vì vậy
     * báo cáo kết quả dưới nhãn "sanity check", không phải "độ chính xác".
     */
    private function runNutrition5k(NutritionCalculator $calculator): int
    {
        $dir = storage_path('imports/nutrition5k');
        if (! is_file("{$dir}/ingredients_metadata.csv")) {
            $this->error("Không tìm thấy {$dir}/ingredients_metadata.csv.");
            $this->line('Tải metadata (CC BY 4.0) từ https://storage.googleapis.com/nutrition5k_dataset/');

            return self::FAILURE;
        }

        $ingredients = [];
        $fh = fopen("{$dir}/ingredients_metadata.csv", 'rb');
        fgetcsv($fh, escape: '\\');
        while (($r = fgetcsv($fh, escape: '\\')) !== false) {
            $ingredients[(int) $r[1]] = [
                'name' => $r[0],
                'kcal_per_100g' => (float) $r[2] * 100,
                'fat_g' => (float) $r[3] * 100,
                'carb_g' => (float) $r[4] * 100,
                'protein_g' => (float) $r[5] * 100,
            ];
        }
        fclose($fh);

        $errs = [];
        $dishes = 0;
        $skipped = 0;

        foreach (['dish_metadata_cafe1.csv', 'dish_metadata_cafe2.csv'] as $file) {
            $path = "{$dir}/{$file}";
            if (! is_file($path)) {
                continue;
            }
            $fh = fopen($path, 'rb');
            while (($r = fgetcsv($fh, escape: '\\')) !== false) {
                if (count($r) < 7) {
                    continue;
                }
                $reference = (float) $r[1];
                $inputs = [];
                for ($i = 6; $i + 6 <= count($r); $i += 7) {
                    $id = (int) ltrim($r[$i], 'ingr_');
                    $grams = (float) $r[$i + 2];
                    if (! isset($ingredients[$id]) || $grams <= 0) {
                        continue;
                    }
                    $m = $ingredients[$id];
                    $inputs[] = new IngredientInput(
                        slug: 'n5k-'.$id,
                        grams: $grams,
                        mappingConfidence: 'high',
                        fctSource: 'nutrition5k',
                        kcalPer100g: $m['kcal_per_100g'],
                        proteinG: $m['protein_g'],
                        fatG: $m['fat_g'],
                        carbG: $m['carb_g'],
                    );
                }
                if ($inputs === [] || $reference <= 0) {
                    $skipped++;

                    continue;
                }

                $result = $calculator->compute($inputs, 'song', 'nutrition5k', portionWeighed: true);
                if (! $result->hasNumbers()) {
                    $skipped++;

                    continue;
                }
                $point = ($result->kcalMin + $result->kcalMax) / 2;
                $errs[] = ($point - $reference) / $reference * 100;
                $dishes++;
            }
            fclose($fh);
        }

        if ($errs === []) {
            $this->error('Không tính được món nào.');

            return self::FAILURE;
        }

        $within = count(array_filter($errs, fn ($e) => abs($e) <= 20.0));
        $mape = array_sum(array_map('abs', $errs)) / count($errs);
        $bias = array_sum($errs) / count($errs);
        $maxErr = max(array_map('abs', $errs));

        $this->newLine();
        $this->line('<options=bold>NUTRITION5K — SANITY CHECK SỐ HỌC (không phải đo độ chính xác)</>');
        $this->warn('⚠ Calo của bộ này được TÍNH từ chính bảng nguyên liệu của nó (đã kiểm: 100% số món');
        $this->warn('  khớp dưới 0,5 kcal), KHÔNG phải đo độc lập bằng thiết bị như ACCURACY-SPEC mô tả.');
        $this->warn('  Vì vậy đây là phép thử VÒNG TRÒN: nó chỉ chứng minh engine cộng đúng, không nói gì');
        $this->warn('  về chất lượng dinh dưỡng. Không được trích con số này như "độ chính xác của app".');
        $this->newLine();
        $this->table(['Chỉ số', 'Giá trị'], [
            ['Số món chạy được', (string) $dishes],
            ['Bỏ qua', (string) $skipped],
            ['% trong ±20%', sprintf('%.2f%%', $within / count($errs) * 100)],
            ['MAPE', sprintf('%.4f%%', $mape)],
            ['Bias', sprintf('%+.4f%%', $bias)],
            ['Sai lệch tuyệt đối lớn nhất', sprintf('%.4f%%', $maxErr)],
        ]);
        $this->line('  <fg=gray>Kỳ vọng đúng cho phép thử vòng tròn: MAPE ~0%. Lệch đáng kể ở đây nghĩa là engine có lỗi số học.</>');

        return self::SUCCESS;
    }

    /** @param list<array<string, mixed>> $results */
    private function renderReport(array $results, int $skipped, string $engineVersion): void
    {
        $overall = $this->metrics($results);

        $this->newLine();
        $this->line("<options=bold>KIỂM CHỨNG ENGINE TRÊN FNDDS — engine {$engineVersion}</>");
        $this->line(sprintf('Bộ mẫu: %d món tính được, %d bỏ qua (thiếu ánh xạ hoặc confidence=unknown).', count($results), $skipped));
        $this->newLine();

        $this->line('<options=bold>Bốn chỉ số (ACCURACY-SPEC §4):</>');
        $this->table(
            ['Chỉ số', 'Giá trị', 'Mục tiêu'],
            [
                ['% món trong ±20%', sprintf('%.1f%%', $overall['pct_within_20']), '≥80%'],
                ['MAPE', sprintf('%.1f%%', $overall['mape']), '—'],
                ['Bias (sai lệch TB)', sprintf('%+.1f%%', $overall['bias']), 'gần 0'],
                ['Bland–Altman: chênh TB', sprintf('%+.1f kcal', $overall['ba_mean']), '—'],
                ['Bland–Altman: LoA 95%', sprintf('%+.1f … %+.1f kcal', $overall['ba_lower'], $overall['ba_upper']), '—'],
                ['% tham chiếu nằm trong khoảng engine', sprintf('%.1f%%', $overall['pct_in_interval']), '—'],
            ],
        );

        $byMethod = [];
        foreach ($results as $r) {
            $byMethod[$r['method']][] = $r;
        }
        ksort($byMethod);

        $rows = [];
        foreach ($byMethod as $method => $group) {
            $m = $this->metrics($group);
            $target = in_array($method, ['chien', 'ran', 'chien-ngap-dau'], true) ? 70.0 : 80.0;
            $rows[] = [
                $method,
                count($group),
                sprintf('%.1f%%', $m['pct_within_20']),
                sprintf('%.1f%%', $m['mape']),
                sprintf('%+.1f%%', $m['bias']),
                sprintf('%.2f', $m['implied_yield']),
                $m['pct_within_20'] >= $target ? 'ĐẠT' : 'CHƯA ĐẠT',
            ];
        }
        $this->line('<options=bold>Tách theo cách chế biến (bắt buộc — TEST-002):</>');
        $this->table(['Cách chế biến', 'n', '% trong ±20%', 'MAPE', 'Bias', 'Hệ số KL ngầm định', 'Ngưỡng'], $rows);
        $this->line('  <fg=gray>Hệ số KL ngầm định = engine ÷ tham chiếu. Lệch 1,0 đều đặn ⇒ hiệu ứng mất/thêm nước khi nấu, không phải lỗi số học.</>');

        $friedCount = count(array_filter($results, fn ($r) => in_array($r['method'], ['chien', 'ran', 'chien-ngap-dau'], true)));
        if ($friedCount === 0) {
            $this->newLine();
            $this->warn('⚠ KHÔNG có món chiên nào trong kết quả: engine từ chối tính vì nguyên liệu thiếu');
            $this->warn('  `oil_absorption_pct` (CALC-002 — không bịa hệ số, QT-1). Nghĩa là nhóm sai số');
            $this->warn('  LỚN NHẤT của món Việt CHƯA được kiểm chứng. Đây là việc còn lại của TEST-002.');
        }

        $worst = $results;
        usort($worst, fn ($a, $b) => abs($b['err_pct']) <=> abs($a['err_pct']));
        $this->line('<options=bold>10 món lệch nhất — để điều tra nguyên nhân, không phải để bỏ qua:</>');
        $this->table(
            ['Food Code', 'Món', 'Chế biến', 'USDA', 'Engine', 'Lệch'],
            array_map(fn ($r) => [
                $r['food_code'],
                mb_substr($r['description'], 0, 45),
                $r['method'],
                sprintf('%.0f', $r['reference']),
                sprintf('%.0f', $r['engine']),
                sprintf('%+.0f%%', $r['err_pct']),
            ], array_slice($worst, 0, 10)),
        );
    }

    /**
     * @param  list<array<string, mixed>>  $results
     * @return array<string, float>
     */
    private function metrics(array $results): array
    {
        $n = count($results);
        $errs = array_column($results, 'err_pct');
        $diffs = array_map(fn ($r) => $r['engine'] - $r['reference'], $results);

        $within = count(array_filter($errs, fn ($e) => abs($e) <= 20.0));
        $mape = array_sum(array_map('abs', $errs)) / $n;
        $bias = array_sum($errs) / $n;

        $baMean = array_sum($diffs) / $n;
        $variance = $n > 1
            ? array_sum(array_map(fn ($d) => ($d - $baMean) ** 2, $diffs)) / ($n - 1)
            : 0.0;
        $sd = sqrt($variance);

        // Chẩn đoán: "hệ số khối lượng ngầm định" = engine ÷ tham chiếu. Phép đo giả
        // định khối lượng thành phẩm = tổng khối lượng nguyên liệu (bảo toàn khối lượng).
        // Nhóm nào có giá trị này lệch 1,0 một cách ĐỀU ĐẶN thì đó là hiệu ứng mất/thêm
        // nước khi nấu (thiếu yield factor), KHÔNG phải engine tính sai số học.
        $implied = array_map(fn ($r) => $r['engine'] / $r['reference'], $results);
        sort($implied);
        $mid = intdiv($n, 2);
        $impliedMedian = $n % 2 === 1 ? $implied[$mid] : ($implied[$mid - 1] + $implied[$mid]) / 2;

        return [
            'n' => (float) $n,
            'implied_yield' => $impliedMedian,
            'pct_within_20' => $within / $n * 100,
            'mape' => $mape,
            'bias' => $bias,
            'ba_mean' => $baMean,
            'ba_lower' => $baMean - 1.96 * $sd,
            'ba_upper' => $baMean + 1.96 * $sd,
            'pct_in_interval' => count(array_filter($results, fn ($r) => $r['in_range'])) / $n * 100,
        ];
    }

    /** @param list<array<string, mixed>> $results */
    private function writeReport(array $results, int $skipped, string $engineVersion): void
    {
        $date = (string) ($this->option('date') ?: '0000-00-00');
        $dir = base_path('docs/06-nutrition/accuracy-runs');
        @mkdir($dir, 0777, true);
        $path = "{$dir}/{$date}-fndds.md";

        $overall = $this->metrics($results);
        $byMethod = [];
        foreach ($results as $r) {
            $byMethod[$r['method']][] = $r;
        }
        ksort($byMethod);

        $md = "# Kết quả đo độ chính xác — FNDDS, {$date}\n\n";
        $md .= "**engine_version:** {$engineVersion}\n";
        $md .= '**Bộ mẫu:** '.count($results)." Food Code FNDDS tính được ({$skipped} bỏ qua)\n";
        $md .= "**Bảng thành phần:** `usda_sr_legacy` (một nguồn duy nhất — QT-4)\n";
        $md .= "**Lệnh tái lập:** `php artisan lacdoi:accuracy-run --dataset=fndds`\n\n";
        $md .= "> Cả nguyên liệu (SR Links) lẫn giá trị đối chiếu đều từ USDA ⇒ lệch nhiều nghĩa là\n> **engine sai**, không phải dữ liệu sai (ACCURACY-SPEC §4 bước 3).\n\n";
        $md .= "## Bốn chỉ số\n\n| Chỉ số | Giá trị | Mục tiêu |\n|---|---|---|\n";
        $md .= sprintf("| %% món trong ±20%% | **%.1f%%** | ≥80%% |\n", $overall['pct_within_20']);
        $md .= sprintf("| MAPE | %.1f%% | — |\n", $overall['mape']);
        $md .= sprintf("| Bias | %+.1f%% | gần 0 |\n", $overall['bias']);
        $md .= sprintf("| Bland–Altman: chênh TB | %+.1f kcal | — |\n", $overall['ba_mean']);
        $md .= sprintf("| Bland–Altman: LoA 95%% | %+.1f … %+.1f kcal | — |\n", $overall['ba_lower'], $overall['ba_upper']);
        $md .= sprintf("| %% tham chiếu nằm trong khoảng engine trả về | %.1f%% | — |\n\n", $overall['pct_in_interval']);

        $md .= "## Tách theo cách chế biến\n\n";
        $md .= "| Cách chế biến | n | % trong ±20% | MAPE | Bias | Hệ số KL ngầm định | Ngưỡng | Kết luận |\n|---|---|---|---|---|---|---|---|\n";
        foreach ($byMethod as $method => $group) {
            $m = $this->metrics($group);
            $target = in_array($method, ['chien', 'ran', 'chien-ngap-dau'], true) ? 70.0 : 80.0;
            $md .= sprintf(
                "| `%s` | %d | %.1f%% | %.1f%% | %+.1f%% | %.2f | ≥%.0f%% | %s |\n",
                $method, count($group), $m['pct_within_20'], $m['mape'], $m['bias'],
                $m['implied_yield'], $target,
                $m['pct_within_20'] >= $target ? '✅ đạt' : '❌ chưa đạt',
            );
        }
        $md .= "\n> **Hệ số KL ngầm định** = engine ÷ tham chiếu. Phép đo giả định khối lượng thành phẩm\n";
        $md .= "> bằng tổng khối lượng nguyên liệu. Nhóm nào lệch 1,0 một cách đều đặn thì đó là hiệu ứng\n";
        $md .= "> mất/thêm nước khi nấu (thiếu yield factor cho nguyên liệu FNDDS), **không phải lỗi số học của engine**.\n";

        $friedCount = count(array_filter($results, fn ($r) => in_array($r['method'], ['chien', 'ran', 'chien-ngap-dau'], true)));
        if ($friedCount === 0) {
            $md .= "\n> ⚠️ **Nhóm chiên/rán KHÔNG có mặt trong kết quả.** Engine từ chối tính mọi món chiên\n";
            $md .= "> vì nguyên liệu thiếu `oil_absorption_pct` — đúng thiết kế CALC-002 (không bịa hệ số),\n";
            $md .= "> nhưng nghĩa là **nhóm sai số lớn nhất của món Việt chưa được kiểm chứng**. Cho tới khi\n";
            $md .= "> có hệ số hấp thụ dầu có nguồn, tiêu chí \"nhóm chiên ≥70%\" của TEST-002 chưa đánh giá được.\n";
        }

        $worst = $results;
        usort($worst, fn ($a, $b) => abs($b['err_pct']) <=> abs($a['err_pct']));
        $md .= "\n## 15 món lệch nhất — đầu vào để điều tra\n\n| Food Code | Món | Chế biến | USDA kcal/100g | Engine | Lệch |\n|---|---|---|---|---|---|\n";
        foreach (array_slice($worst, 0, 15) as $r) {
            $md .= sprintf(
                "| %s | %s | `%s` | %.0f | %.0f | %+.0f%% |\n",
                $r['food_code'], str_replace('|', '\\|', mb_substr($r['description'], 0, 60)),
                $r['method'], $r['reference'], $r['engine'], $r['err_pct'],
            );
        }

        // Kết luận tự sinh: đối chiếu từng tiêu chí DoD của TEST-002, không tô hồng.
        $md .= "\n## Kết luận (tự sinh)\n\n";
        $md .= sprintf(
            "- Tiêu chí chính **≥80%% món trong ±20%%**: %s (%.1f%%).\n",
            $overall['pct_within_20'] >= 80 ? '✅ **ĐẠT**' : '❌ **CHƯA ĐẠT**',
            $overall['pct_within_20'],
        );
        $md .= sprintf("- Chạy trên %d món FNDDS (DoD yêu cầu ≥200): %s.\n",
            count($results), count($results) >= 200 ? '✅ đạt' : '❌ chưa đạt');

        $suspects = [];
        foreach ($byMethod as $method => $group) {
            $m = $this->metrics($group);
            $target = in_array($method, ['chien', 'ran', 'chien-ngap-dau'], true) ? 70.0 : 80.0;
            if ($m['pct_within_20'] < $target) {
                $suspects[] = sprintf(
                    '  - `%s` (n=%d): %.1f%% trong ±20%%, hệ số KL ngầm định **%.2f**. %s',
                    $method, count($group), $m['pct_within_20'], $m['implied_yield'],
                    abs($m['implied_yield'] - 1.0) > 0.05
                        ? sprintf('Lệch 1,0 đều đặn ⇒ nghi **thiếu yield factor** (khối lượng thành phẩm ≈ %.0f%% khối lượng nguyên liệu), không phải lỗi số học.', $m['implied_yield'] * 100)
                        : 'Hệ số ngầm định ~1,0 ⇒ phần lớn món khớp, sai lệch tập trung ở vài ngoại lệ — xem bảng món lệch nhất.',
                );
            }
        }
        if ($suspects !== []) {
            $md .= "- Nhóm chưa đạt ngưỡng, kèm chẩn đoán:\n".implode("\n", $suspects)."\n";
        }
        $md .= "\n**Việc cần làm tiếp** (không được hiệu chỉnh hệ số để đạt số đẹp — TEST-002 cạm bẫy #2):\n\n";
        $md .= "1. Bổ sung `default_yield_factor` có nguồn cho nhóm bị nghi thiếu, rồi chạy lại lệnh này và so hai lần đo.\n";
        $md .= "2. Bổ sung `oil_absorption_pct` có nguồn để nhóm chiên/rán được đưa vào phép đo.\n";
        $md .= "3. Điều tra từng món trong bảng \"lệch nhất\" ở trên — không loại chúng khỏi bộ mẫu.\n";

        file_put_contents($path, $md);
        $this->info("Đã ghi báo cáo: docs/06-nutrition/accuracy-runs/{$date}-fndds.md");
    }
}
