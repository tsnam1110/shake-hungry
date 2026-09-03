<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * lacdoi:validate-data — 15 kiểm tra V1–V15 theo DATA-PIPELINE §5.
 *
 * Chạy trước mỗi commit dữ liệu và trong CI. Lỗi 🔴 ⇒ exit code 1 (CI chặn được).
 * Kiểm tra được thực hiện trên FILE trong data/ (nguồn sự thật, QT-3); các kiểm tra
 * chỉ có nghĩa trên dữ liệu dẫn xuất (dish_nutrition…) chạy trên database — nếu
 * database không kết nối được, chúng được ghi rõ là BỎ QUA, không im lặng.
 */
class LacdoiValidateData extends Command
{
    protected $signature = 'lacdoi:validate-data';

    protected $description = 'Kiểm tra toàn vẹn dữ liệu trong data/ và database (V1–V15, DATA-PIPELINE §5)';

    /** @var list<string> */
    private array $errors = [];

    /** @var list<string> */
    private array $warnings = [];

    private int $checked = 0;

    public function handle(): int
    {
        $ingredients = $this->loadJsonDir(base_path('data/ingredients'));
        $dishes = $this->loadJsonDir(base_path('data/dishes'), recursive: true);

        $this->validateIngredientFiles($ingredients);
        $this->validateDishFiles($dishes, array_keys($ingredients));
        $this->validateMediaManifest();
        $this->validateDatabase();

        $this->newLine();
        $this->line(sprintf(
            'Đã kiểm %d bản ghi — %d lỗi 🔴, %d cảnh báo 🟡',
            $this->checked, count($this->errors), count($this->warnings),
        ));

        foreach ($this->errors as $e) {
            $this->error('🔴 '.$e);
        }
        foreach ($this->warnings as $w) {
            $this->warn('🟡 '.$w);
        }

        return $this->errors === [] ? self::SUCCESS : self::FAILURE;
    }

    /** @return array<string, array<string, mixed>> slug → nội dung file */
    private function loadJsonDir(string $dir, bool $recursive = false): array
    {
        $pattern = $recursive ? $dir.'/*/*.json' : $dir.'/*.json';
        $out = [];
        foreach (glob($pattern) ?: [] as $file) {
            if (str_starts_with(basename($file), '_')) {
                continue;
            }
            try {
                $out[basename($file, '.json')] = json_decode(
                    (string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR,
                );
            } catch (Throwable $e) {
                $this->errors[] = "JSON hỏng: {$file} — {$e->getMessage()}";
            }
        }

        return $out;
    }

    /** @param array<string, array<string, mixed>> $ingredients */
    private function validateIngredientFiles(array $ingredients): void
    {
        $seenNames = [];
        foreach ($ingredients as $slug => $j) {
            $this->checked++;
            $ref = $j['reference_food'] ?? [];

            // V3 — mapping_confidence ≠ high mà thiếu mapping_note (🔴, ràng buộc R7).
            $mc = $ref['mapping_confidence'] ?? null;
            if ($mc !== null && $mc !== 'high' && blank($ref['mapping_note'] ?? null)) {
                $this->errors[] = "V3 [{$slug}]: mapping_confidence='{$mc}' nhưng thiếu mapping_note";
            }

            // V5 (tầng file) — khoảng kcal ngược trong bản chụp.
            $min = $ref['kcal_min'] ?? null;
            $max = $ref['kcal_max'] ?? null;
            if ($min !== null && $max !== null && $min > $max) {
                $this->errors[] = "V5 [{$slug}]: kcal_min ({$min}) > kcal_max ({$max})";
            }

            // V10 — kcal/100g > 900: chỉ dầu mỡ nguyên chất mới vượt (🟡).
            $kcal = $ref['kcal_per_100g'] ?? null;
            if ($kcal !== null && $kcal > 900) {
                $this->warnings[] = "V10 [{$slug}]: kcal_per_100g = {$kcal} (>900) — kiểm tra lại nếu không phải dầu mỡ nguyên chất";
            }

            // V13 — có density mà thiếu density_note (🔴).
            if (($j['density_g_per_ml'] ?? null) !== null && blank($j['density_note'] ?? null)) {
                $this->errors[] = "V13 [{$slug}]: có density_g_per_ml nhưng thiếu density_note (đo hay suy ra?)";
            }

            // V13c (DATA-004) — có default_yield_factor mà không ghi suy từ đâu (🔴).
            if (($j['default_yield_factor'] ?? null) !== null && blank($j['default_yield_factor_note'] ?? null)) {
                $this->errors[] = "V13c [{$slug}]: có default_yield_factor nhưng thiếu default_yield_factor_note (suy từ dòng nào của bảng USDA CY R2?)";
            }

            // V13b (DATA-008) — có edible_portion_pct mà thiếu nguồn/phương pháp (🔴).
            if (($j['edible_portion_pct'] ?? null) !== null && blank($j['edible_portion_note'] ?? null)) {
                $this->errors[] = "V13b [{$slug}]: có edible_portion_pct nhưng thiếu edible_portion_note (nguồn foodMeasures hay phép cân?)";
            }

            // V14 — có số liệu mà thiếu source_url (🔴): mọi con số phải truy nguyên được.
            if ($kcal !== null && blank($ref['source_url'] ?? null)) {
                $this->errors[] = "V14 [{$slug}]: có kcal_per_100g nhưng thiếu source_url";
            }

            // V15 (R10) — hạ needs_review mà không ghi ai duyệt / duyệt khi nào (🔴).
            // Cờ duyệt không có bằng chứng thì không phải là duyệt (QT-1).
            $this->validateReviewTrail("[{$slug}]", $j);

            // V7 (tầng file) — trùng tên nguyên liệu tuyệt đối (🟡); phần so trigram >90% chạy ở DB.
            $nameKey = mb_strtolower(trim((string) ($j['name_vi'] ?? '')));
            if ($nameKey !== '' && isset($seenNames[$nameKey])) {
                $this->warnings[] = "V7 [{$slug}]: name_vi trùng với [{$seenNames[$nameKey]}]";
            }
            $seenNames[$nameKey] = $slug;
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $dishes
     * @param  list<string>  $ingredientSlugs
     */
    private function validateDishFiles(array $dishes, array $ingredientSlugs): void
    {
        $lookup = array_flip($ingredientSlugs);
        $seenSlugs = [];

        foreach ($dishes as $slug => $j) {
            $this->checked++;
            $level = (int) ($j['completeness_level'] ?? 1);
            $rows = $j['ingredients'] ?? [];

            // V7 — trùng slug món (🟡).
            if (isset($seenSlugs[$slug])) {
                $this->warnings[] = "V7 [{$slug}]: slug món bị trùng";
            }
            $seenSlugs[$slug] = true;

            // V4 — Mức ≥2 mà không có nguyên liệu (🔴, ràng buộc R6).
            if ($level >= 2 && $rows === []) {
                $this->errors[] = "V4 [{$slug}]: completeness_level={$level} nhưng không có nguyên liệu nào";
            }

            // V11 — ingredient_slug không tồn tại (🔴): bắt slug lỗi thời sau khi tách/đổi tên.
            foreach ($rows as $row) {
                $is = $row['ingredient_slug'] ?? '';
                if (! isset($lookup[$is])) {
                    $this->errors[] = "V11 [{$slug}]: ingredient_slug '{$is}' không tồn tại trong data/ingredients/";
                }
            }

            // V12 — Mức ≥2 mà <3 nguồn công thức (🔴, ràng buộc R8).
            if ($level >= 2 && count($j['recipe_sources'] ?? []) < 3) {
                $this->errors[] = "V12 [{$slug}]: completeness_level={$level} nhưng chỉ có ".count($j['recipe_sources'] ?? []).' recipe_sources (cần ≥3)';
            }

            // V15 (R10) — dấu vết duyệt, cùng luật với nguyên liệu.
            $this->validateReviewTrail("[{$slug}]", $j);

            // V9 — estimated_kcal ngoài khoảng 50–2000 (🟡, thường là sai đơn vị).
            $est = $j['estimated_kcal'] ?? null;
            if (is_array($est)) {
                if (blank($est['source_note'] ?? null)) {
                    $this->errors[] = "V14 [{$slug}]: estimated_kcal thiếu source_note — ước lượng không nguồn không được nhập (QT-1)";
                }
                foreach (['min', 'max'] as $k) {
                    $v = $est[$k] ?? null;
                    if ($v !== null && ($v < 50 || $v > 2000)) {
                        $this->warnings[] = "V9 [{$slug}]: estimated_kcal.{$k} = {$v} ngoài khoảng 50–2000";
                    }
                }
                if (($est['min'] ?? null) !== null && ($est['max'] ?? null) !== null && $est['min'] > $est['max']) {
                    $this->errors[] = "V5 [{$slug}]: estimated_kcal.min > max";
                }
            }
        }
    }

    /**
     * V15 / R10 — dấu vết duyệt (ING-004).
     *
     * `needs_review = false` là lời khẳng định "đã có người duyệt". Lời khẳng định
     * đó phải kèm bằng chứng: ai duyệt và duyệt lúc nào. Không có bằng chứng thì
     * cờ đã bị hạ nhầm hoặc hạ lặng lẽ — cả hai đều là lỗi 🔴.
     *
     * @param  array<string, mixed>  $j
     */
    private function validateReviewTrail(string $label, array $j): void
    {
        $by = $j['reviewed_by'] ?? null;
        $at = $j['reviewed_at'] ?? null;

        if (($j['needs_review'] ?? true) === false && (blank($by) || blank($at))) {
            $this->errors[] = "V15 {$label}: needs_review=false nhưng thiếu reviewed_by/reviewed_at — duyệt không có bằng chứng (R10)";
        }

        if (blank($by) !== blank($at)) {
            $this->errors[] = "V15 {$label}: reviewed_by và reviewed_at phải cùng có hoặc cùng không";
        }

        if (filled($at) && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $at)) {
            $this->errors[] = "V15 {$label}: reviewed_at = '{$at}' không đúng dạng YYYY-MM-DD";
        }
    }

    private function validateMediaManifest(): void
    {
        $mediaDir = base_path('data/media');
        $manifestPath = $mediaDir.'/MANIFEST.json';
        $manifest = [];
        if (is_file($manifestPath)) {
            $manifest = json_decode((string) file_get_contents($manifestPath), true) ?? [];
        }

        $files = glob($mediaDir.'/*/*.{webp,avif,jpg,jpeg,png}', GLOB_BRACE) ?: [];
        foreach ($files as $file) {
            $this->checked++;
            $rel = str_replace('\\', '/', substr($file, strlen($mediaDir) + 1));
            $entry = $manifest[$rel] ?? null;

            // V2 — ảnh không có mục MANIFEST kèm license thì không được commit (🔴, R4/QT-2).
            if ($entry === null || blank($entry['license'] ?? null)) {
                $this->errors[] = "V2 [{$rel}]: ảnh không có mục trong media/MANIFEST.json kèm license";
            }
        }
    }

    private function validateDatabase(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            $this->warnings[] = 'Database không kết nối được — BỎ QUA V1, V5(DB), V6, V7(trigram), V8. Chạy lại sau khi migrate:fresh --seed.';

            return;
        }

        if (! DB::getSchemaBuilder()->hasTable('dish_nutrition')) {
            $this->warnings[] = 'Chưa có bảng dish_nutrition (chưa migrate) — BỎ QUA các kiểm tra database.';

            return;
        }

        // V1 — chốt chặn QT-1: không bản ghi dinh dưỡng nào do LLM sinh ra (🔴).
        $n = DB::table('dish_nutrition')->where('source', 'llm_suggested')->count();
        if ($n > 0) {
            $this->errors[] = "V1: {$n} bản ghi dish_nutrition có source='llm_suggested' — vi phạm QT-1";
        }

        // V5 (DB) — khoảng kcal ngược trong kết quả tính (🔴).
        $n = DB::table('dish_nutrition')->whereColumn('kcal_min', '>', 'kcal_max')->count();
        if ($n > 0) {
            $this->errors[] = "V5: {$n} bản ghi dish_nutrition có kcal_min > kcal_max";
        }

        // V6 — thiếu nguyên liệu mà vẫn confidence='high' (🔴).
        $n = DB::table('dish_nutrition')
            ->whereRaw("missing_ingredients <> '[]'::jsonb")
            ->where('confidence', 'high')
            ->count();
        if ($n > 0) {
            $this->errors[] = "V6: {$n} bản ghi dish_nutrition thiếu nguyên liệu nhưng confidence='high'";
        }

        // V9 (DB) — kcal/khẩu phần tính ra ngoài khoảng 50–2000 (🟡).
        $rows = DB::table('dish_nutrition')
            ->join('dishes', 'dishes.id', '=', 'dish_nutrition.dish_id')
            ->where('basis', 'per_serving')
            ->where(fn ($q) => $q->where('kcal_max', '>', 2000)->orWhere('kcal_min', '<', 50))
            ->pluck('dishes.slug');
        foreach ($rows as $slug) {
            $this->warnings[] = "V9 [{$slug}]: kcal/khẩu phần tính ra ngoài khoảng 50–2000 — thường là sai đơn vị";
        }

        $dishCount = (int) DB::table('dishes')->count();

        // V8 — nguyên liệu mồ côi (🟡). Chỉ có nghĩa khi đã có món.
        if ($dishCount > 0) {
            $orphans = DB::table('ingredients')
                ->whereNotIn('id', DB::table('dish_ingredients')->select('ingredient_id'))
                ->count();
            if ($orphans > 0) {
                $this->warnings[] = "V8: {$orphans} nguyên liệu chưa được món nào dùng";
            }

            // V7 (trigram) — hai món tên giống nhau >90% (🟡).
            $pairs = DB::select(<<<'SQL'
                SELECT a.name AS name_a, b.name AS name_b
                FROM dish_names a
                JOIN dish_names b ON a.id < b.id AND a.dish_id <> b.dish_id
                WHERE a.is_primary AND b.is_primary AND a.locale = b.locale
                  AND similarity(lower(unaccent(a.name)), lower(unaccent(b.name))) > 0.9
            SQL);
            foreach ($pairs as $p) {
                $this->warnings[] = "V7: hai món tên giống nhau >90%: \"{$p->name_a}\" ↔ \"{$p->name_b}\"";
            }
        }

        // V4 (DB) — đối chiếu lại ở tầng database (đã có trigger R6 nhưng kiểm cho chắc).
        $rows = DB::table('dishes')
            ->where('completeness_level', '>=', 2)
            ->whereNotIn('id', DB::table('dish_ingredients')->select('dish_id'))
            ->pluck('slug');
        foreach ($rows as $slug) {
            $this->errors[] = "V4 [{$slug}]: completeness_level >= 2 trong DB nhưng không có dish_ingredients";
        }
    }
}
