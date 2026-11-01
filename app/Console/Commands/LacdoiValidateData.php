<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * lacdoi:validate-data — 16 kiểm tra V1–V16 theo DATA-PIPELINE §5.
 *
 * Chạy trước mỗi commit dữ liệu và trong CI. Lỗi 🔴 ⇒ exit code 1 (CI chặn được).
 * Kiểm tra được thực hiện trên FILE trong data/ (nguồn sự thật, QT-3); các kiểm tra
 * chỉ có nghĩa trên dữ liệu dẫn xuất (dish_nutrition…) chạy trên database — nếu
 * database không kết nối được, chúng được ghi rõ là BỎ QUA, không im lặng.
 */
class LacdoiValidateData extends Command
{
    protected $signature = 'lacdoi:validate-data';

    protected $description = 'Kiểm tra toàn vẹn dữ liệu trong data/ và database (V1–V20, DATA-PIPELINE §5)';

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
        $fruits = is_dir(base_path('data/fruits')) ? $this->loadJsonDir(base_path('data/fruits')) : [];
        $this->validateFruitFiles($fruits, $ingredients);
        $this->validateTaxonomy($ingredients, $dishes);
        $this->validateMediaManifest();
        $this->validateMediaEntries($ingredients, $dishes);
        $this->validateDatabase();
        $this->validateTcmDatabase();

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
    /** Khoá so trùng tên cho V18: thường hoá, bỏ khoảng trắng thừa (giữ dấu). */
    private static function nameKey(string $name): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $name)));
    }

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

            // V18 — tên gọi khác của nguyên liệu (🔴, ALIAS-001): mỗi alias là
            // {name, note?}; tên không trống, không trùng tên chính, không trùng nhau.
            $primaryKey = self::nameKey((string) ($j['name_vi'] ?? ''));
            $seenAliases = [];
            foreach ($j['aliases'] ?? [] as $alias) {
                $aliasName = is_array($alias) ? trim((string) ($alias['name'] ?? '')) : '';
                if ($aliasName === '') {
                    $this->errors[] = "V18 [{$slug}]: aliases có mục thiếu name";
                    continue;
                }
                $key = self::nameKey($aliasName);
                if ($key === $primaryKey) {
                    $this->errors[] = "V18 [{$slug}]: alias '{$aliasName}' trùng tên chính";
                }
                if (isset($seenAliases[$key])) {
                    $this->errors[] = "V18 [{$slug}]: alias '{$aliasName}' bị trùng trong cùng nguyên liệu";
                }
                $seenAliases[$key] = true;
                if (isset($alias['note']) && (! is_string($alias['note']) || mb_strlen($alias['note']) > 255)) {
                    $this->errors[] = "V18 [{$slug}]: note của alias '{$aliasName}' phải là chuỗi ≤255 ký tự";
                }
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

            // V20 — lớp tính vị + liên kết thành phần (🔴, TCM-000): nhãn
            // không nguồn bị cấm tuyệt đối (R5/QT-6); components phải trỏ
            // tới nguyên liệu có thật, không tự trỏ, không trùng.
            $this->validateTcmTraits("[{$slug}]", $j, $ingredients);
            $this->validateComponents("[{$slug}]", $j, $ingredients);

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

            // V18 — tên gọi khác của món (🔴, ALIAS-001): dòng names có
            // is_alias=true phải is_primary=false, tên không trùng tên chính
            // (locale vi) và không trùng nhau; note (nhãn vùng miền) ≤255 ký tự.
            $dishNames = $j['names'] ?? [];
            $primaryVi = collect($dishNames)->first(fn ($n) => ($n['locale'] ?? '') === 'vi' && ($n['is_primary'] ?? false));
            $primaryKey = $primaryVi ? self::nameKey((string) $primaryVi['name']) : null;
            $seenAliases = [];
            foreach ($dishNames as $n) {
                if (! ($n['is_alias'] ?? false)) {
                    continue;
                }
                $aliasName = trim((string) ($n['name'] ?? ''));
                if (($n['is_primary'] ?? false)) {
                    $this->errors[] = "V18 [{$slug}]: alias '{$aliasName}' không được đồng thời là tên chính";
                }
                if ($aliasName === '') {
                    $this->errors[] = "V18 [{$slug}]: alias thiếu name";
                    continue;
                }
                $key = self::nameKey($aliasName);
                if ($primaryKey !== null && $key === $primaryKey) {
                    $this->errors[] = "V18 [{$slug}]: alias '{$aliasName}' trùng tên chính";
                }
                if (isset($seenAliases[$key])) {
                    $this->errors[] = "V18 [{$slug}]: alias '{$aliasName}' bị trùng trong cùng món";
                }
                $seenAliases[$key] = true;
                if (isset($n['note']) && (! is_string($n['note']) || mb_strlen($n['note']) > 255)) {
                    $this->errors[] = "V18 [{$slug}]: note của alias '{$aliasName}' phải là chuỗi ≤255 ký tự";
                }
            }

            // V11 — ingredient_slug không tồn tại (🔴): bắt slug lỗi thời sau khi tách/đổi tên.
            foreach ($rows as $row) {
                $is = $row['ingredient_slug'] ?? '';
                if (! isset($lookup[$is])) {
                    $this->errors[] = "V11 [{$slug}]: ingredient_slug '{$is}' không tồn tại trong data/ingredients/";
                }
            }

            // V12 — Mức ≥2 mà <3 nguồn công thức (🔴, ràng buộc R8).
            // Ngoại lệ duy nhất (TCM-000): món nâng bù level_basis=
            // 'tcm_compensation' đạt Mức 2 bằng cổng thông tin Mức 3 —
            // cổng đó do V20 kiểm (note + ghi chú văn hoá ở tầng file,
            // coverage ≥60 ở tầng DB), không áp R8 cho nhóm này.
            $isCompensation = ($j['level_basis'] ?? null) === 'tcm_compensation';
            if ($level >= 2 && ! $isCompensation && count($j['recipe_sources'] ?? []) < 3) {
                $this->errors[] = "V12 [{$slug}]: completeness_level={$level} nhưng chỉ có ".count($j['recipe_sources'] ?? []).' recipe_sources (cần ≥3)';
            }

            // V20 (tầng file) — đường nâng bù + ghi chú văn hoá (🔴, TCM-000).
            $this->validateCulturalNote("[{$slug}]", $j['cultural_note'] ?? null);
            $basis = $j['level_basis'] ?? null;
            if ($basis !== null && $basis !== 'tcm_compensation') {
                $this->errors[] = "V20 [{$slug}]: level_basis '{$basis}' không hợp lệ (chỉ có 'tcm_compensation')";
            }
            if ($isCompensation) {
                if ($level !== 2) {
                    $this->errors[] = "V20 [{$slug}]: level_basis='tcm_compensation' chỉ đi với completeness_level=2 (đang là {$level})";
                }
                if (blank($j['level_note'] ?? null)) {
                    $this->errors[] = "V20 [{$slug}]: Mức 2 (có note) nhưng thiếu level_note — note phải ghi rõ cổng chưa đạt và căn cứ nâng";
                }
                if (($j['cultural_note'] ?? null) === null) {
                    $this->errors[] = "V20 [{$slug}]: Mức 2 (có note) nhưng thiếu cultural_note — đường nâng bù yêu cầu đủ thông tin Mức 3 (ghi chú văn hoá + phủ tính vị ≥60%, phần phủ kiểm ở tầng DB)";
                }
            }
            if ($level >= 3 && ($j['cultural_note'] ?? null) === null) {
                $this->errors[] = "V20 [{$slug}]: completeness_level=3 nhưng thiếu cultural_note — cổng Mức 3 yêu cầu ghi chú văn hoá";
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
     * V20 (tầng file) — nhãn tính vị của một nguyên liệu (TCM-000, QT-6/R5):
     * mỗi dòng nhãn phải có source_ref, từ vựng phải thuộc hệ phân loại,
     * element (khi ghi) phải khớp ánh xạ cổ điển từ vị, và cờ hạn chế sử
     * dụng phải đi kèm ghi chú (chính sách lưu trữ nguồn hạn chế — kế hoạch
     * Mức 3 §2). Ghi chú thiếu cụm "không được dùng" chỉ là 🟡: câu chữ
     * chính xác do người biên soạn chịu trách nhiệm, máy chỉ nhắc.
     *
     * @param  array<string, mixed>  $j
     * @param  array<string, array<string, mixed>>  $ingredients
     */
    private function validateTcmTraits(string $label, array $j, array $ingredients = []): void
    {
        $natures = array_keys(\App\Cultural\TcmVocabulary::NATURE_POINTS);
        $flavors = array_keys(\App\Cultural\TcmVocabulary::FLAVOR_LABELS);
        $elements = array_keys(\App\Cultural\TcmVocabulary::ELEMENT_LABELS);
        $confidences = ['high', 'medium', 'low'];

        foreach ($j['tcm_traits'] ?? [] as $i => $t) {
            $row = "V20 {$label} tcm_traits[".($i + 1).']';
            if (! is_array($t)) {
                $this->errors[] = "{$row}: dòng nhãn phải là một object";

                continue;
            }
            if (blank($t['source_ref'] ?? null)) {
                $this->errors[] = "{$row}: thiếu source_ref — nhãn tính vị không nguồn bị cấm tuyệt đối (R5)";
            }
            $nature = $t['nature'] ?? null;
            $flavor = $t['flavor'] ?? null;
            $element = $t['element'] ?? null;
            if ($nature !== null && ! in_array($nature, $natures, true)) {
                $this->errors[] = "{$row}: nature '{$nature}' ngoài từ vựng (han/luong/binh/on/nhiet)";
            }
            if ($flavor !== null && ! in_array($flavor, $flavors, true)) {
                $this->errors[] = "{$row}: flavor '{$flavor}' ngoài từ vựng (toan/kho/cam/tan/ham/dam)";
            }
            if ($element !== null && ! in_array($element, $elements, true)) {
                $this->errors[] = "{$row}: element '{$element}' ngoài từ vựng (moc/hoa/tho/kim/thuy)";
            }
            // element_basis (TCM-001a): element của dòng đứng trên căn cứ
            // nào — 'flavor' (mặc định khi vắng mặt), 'nature' hay 'direct'.
            // Mỗi basis có luật nhất quán riêng; trộn hệ mà không khai báo
            // là lỗi, khai báo rồi vẫn phải khớp hệ đã khai.
            $basis = $t['element_basis'] ?? 'flavor';
            if (! in_array($basis, \App\Cultural\TcmVocabulary::ELEMENT_BASES, true)) {
                $this->errors[] = "{$row}: element_basis '{$basis}' ngoài từ vựng (flavor/nature/direct)";
                $basis = 'flavor';
            }
            if ($basis === 'flavor') {
                $derived = \App\Cultural\TcmVocabulary::elementForFlavor($flavor);
                if ($element !== null && $derived !== null && $element !== $derived) {
                    $this->errors[] = "{$row}: element '{$element}' lệch ánh xạ cổ điển từ flavor '{$flavor}' (phải là '{$derived}') — nếu nguồn ghi theo tính hoặc ghi trực tiếp, khai element_basis tương ứng kèm nguồn đó";
                }
            } elseif ($basis === 'nature') {
                if ($nature === null) {
                    $this->errors[] = "{$row}: element_basis='nature' nhưng thiếu nature — dòng theo tính phải có tính để đối chiếu hành";
                } elseif ($element === null) {
                    $this->errors[] = "{$row}: element_basis='nature' nhưng element trống — dòng theo tính phải ghi element như nguồn ghi";
                } else {
                    $derivedByNature = \App\Cultural\TcmVocabulary::elementForNature($nature);
                    if ($derivedByNature !== null && $element !== $derivedByNature) {
                        $this->errors[] = "{$row}: element '{$element}' lệch ánh xạ theo tính '{$nature}' (phải là '{$derivedByNature}' theo trình bày §3) — nếu nguồn ghi hành trực tiếp khác cả hai hệ, dùng element_basis='direct'";
                    }
                }
            }
            // basis='direct': nguồn ghi hành trực tiếp — chỉ kiểm từ vựng
            // element ở trên, không ép khớp hệ vị hay hệ tính nào.
            if (! in_array($t['confidence'] ?? null, $confidences, true)) {
                $this->errors[] = "{$row}: confidence phải là high/medium/low";
            }
            if (! empty($t['usage_restricted'])) {
                if (blank($t['usage_note'] ?? null)) {
                    $this->errors[] = "{$row}: usage_restricted=true nhưng thiếu usage_note — cờ hạn chế phải kèm ghi chú 'không được dùng'";
                } elseif (mb_stripos((string) $t['usage_note'], 'không được dùng') === false) {
                    $this->warnings[] = "{$row}: usage_note chưa chứa cụm 'không được dùng' — kiểm lại câu chữ theo chính sách nguồn hạn chế";
                }
            }
            // P-DERIVE (TCM-001c): dòng có derived_from là nhãn SUY DIỄN
            // cho dạng chế biến cơ học đơn thành phần — phải gương đúng
            // một dòng nguồn thật của nguyên liệu gốc, trần confidence
            // 'low', source_ref mang dấu 'P-DERIVE', và bị cấm cho
            // nguyên liệu hỗn hợp (có components).
            if (($t['derived_from'] ?? null) !== null) {
                $this->validateDerivedTrait($row, $t, $j, $ingredients);
            }
        }
    }

    /**
     * V20 (tầng file) — kỷ luật P-DERIVE cho MỘT dòng nhãn suy diễn
     * (TCM-001c). Mọi điều kiện đều là 🔴: suy diễn là đường tắt duy nhất
     * được phép trong lớp tính vị, nên cổng của nó phải chặt hơn cổng của
     * nhãn nguồn thật.
     *
     * @param  array<string, mixed>  $t
     * @param  array<string, mixed>  $j
     * @param  array<string, array<string, mixed>>  $ingredients
     */
    private function validateDerivedTrait(string $row, array $t, array $j, array $ingredients): void
    {
        $originSlug = $t['derived_from'];
        $ownSlug = $j['slug'] ?? null;
        if (! is_string($originSlug) || $originSlug === '') {
            $this->errors[] = "{$row}: derived_from phải là slug của nguyên liệu gốc";

            return;
        }
        if ($originSlug === $ownSlug) {
            $this->errors[] = "{$row}: derived_from tự trỏ chính mình — suy diễn phải từ nguyên liệu gốc khác";
        }
        if (! empty($j['components'])) {
            $this->errors[] = "{$row}: nguyên liệu có components (đa thành phần/hỗn hợp) bị cấm mang nhãn suy diễn P-DERIVE";
        }
        if (($t['confidence'] ?? null) !== 'low') {
            $this->errors[] = "{$row}: nhãn suy diễn P-DERIVE bị khoá confidence='low' (đang là '".($t['confidence'] ?? '')."')";
        }
        if (! str_contains((string) ($t['source_ref'] ?? ''), \App\Cultural\TcmVocabulary::DERIVED_MARKER)) {
            $this->errors[] = "{$row}: source_ref của nhãn suy diễn phải ghi rõ chính sách ".\App\Cultural\TcmVocabulary::DERIVED_MARKER.' (kèm nguồn của nhãn gốc)';
        }
        $origin = $ingredients[$originSlug] ?? null;
        if ($origin === null) {
            $this->errors[] = "{$row}: derived_from trỏ tới nguyên liệu không tồn tại '{$originSlug}'";

            return;
        }
        $originTraits = array_values(array_filter(
            is_array($origin['tcm_traits'] ?? null) ? $origin['tcm_traits'] : [],
            fn ($o) => is_array($o),
        ));
        $realTraits = array_values(array_filter(
            $originTraits,
            fn ($o) => ($o['derived_from'] ?? null) === null,
        ));
        if ($realTraits === []) {
            $this->errors[] = $originTraits === []
                ? "{$row}: nguyên liệu gốc '{$originSlug}' chưa có nhãn nào — không có gì để suy diễn"
                : "{$row}: nguyên liệu gốc '{$originSlug}' chỉ có nhãn suy diễn — cấm suy diễn chuỗi (P-DERIVE)";

            return;
        }
        $effectiveElement = function (array $r): ?string {
            if (($r['element'] ?? null) !== null) {
                return $r['element'];
            }
            $b = $r['element_basis'] ?? 'flavor';

            return match ($b) {
                'flavor' => \App\Cultural\TcmVocabulary::elementForFlavor($r['flavor'] ?? null),
                'nature' => \App\Cultural\TcmVocabulary::elementForNature($r['nature'] ?? null),
                default => null,
            };
        };
        $mirror = null;
        foreach ($realTraits as $o) {
            if (($o['nature'] ?? null) === ($t['nature'] ?? null)
                && ($o['flavor'] ?? null) === ($t['flavor'] ?? null)
                && (($o['element_basis'] ?? 'flavor') === ($t['element_basis'] ?? 'flavor'))
                && $effectiveElement($o) === $effectiveElement($t)) {
                $mirror = $o;
                break;
            }
        }
        if ($mirror === null) {
            $this->errors[] = "{$row}: nhãn suy diễn không gương đúng dòng nguồn thật nào của '{$originSlug}' (nature/flavor/element/element_basis phải khớp nguyên vẹn một dòng gốc)";
        } elseif (! empty($mirror['usage_restricted']) && empty($t['usage_restricted'])) {
            $this->errors[] = "{$row}: nhãn gốc bị hạn chế sử dụng (usage_restricted) — dòng suy diễn phải mang theo cờ + ghi chú hạn chế của gốc";
        }
    }

    /**
     * V20 (tầng file) — liên kết thành phần của nguyên liệu hỗn hợp:
     * slug phải tồn tại, không tự trỏ chính mình, không trùng (🔴).
     *
     * @param  array<string, mixed>  $j
     * @param  array<string, array<string, mixed>>  $ingredients
     */
    private function validateComponents(string $label, array $j, array $ingredients): void
    {
        $ownSlug = $j['slug'] ?? null;
        $seen = [];
        foreach ($j['components'] ?? [] as $componentSlug) {
            if (! is_string($componentSlug) || ! isset($ingredients[$componentSlug])) {
                $this->errors[] = "V20 {$label}: components trỏ tới nguyên liệu không tồn tại '".(string) $componentSlug."'";
                continue;
            }
            if ($componentSlug === $ownSlug) {
                $this->errors[] = "V20 {$label}: components tự trỏ chính mình";
            }
            if (isset($seen[$componentSlug])) {
                $this->errors[] = "V20 {$label}: components trùng '{$componentSlug}'";
            }
            $seen[$componentSlug] = true;
        }
    }

    /**
     * V20 — cấu trúc ghi chú văn hoá (món + quả): có ghi chú thì text không
     * được rỗng và phải có ít nhất một nguồn có ref (🔴). Vắng khoá = chưa
     * có ghi chú — hợp lệ, việc "bắt buộc có" do cổng mức kiểm riêng.
     */
    private function validateCulturalNote(string $label, mixed $note): void
    {
        if ($note === null) {
            return;
        }
        if (! is_array($note) || blank($note['text'] ?? null)) {
            $this->errors[] = "V20 {$label}: cultural_note phải có text không rỗng";

            return;
        }
        $sources = $note['sources'] ?? [];
        if (! is_array($sources) || $sources === []) {
            $this->errors[] = "V20 {$label}: cultural_note thiếu sources — ghi chú văn hoá không nguồn không được nhập";

            return;
        }
        foreach ($sources as $s) {
            if (! is_array($s) || blank($s['ref'] ?? null)) {
                $this->errors[] = "V20 {$label}: cultural_note.sources có mục thiếu ref";
            }
        }
    }

    /**
     * V20 (tầng DB) — chốt chặn thứ hai sau tầng file, chạy trên dữ liệu
     * dẫn xuất: coverage chỉ tồn tại trong DB nên cổng phủ ≥60% của Mức 3
     * và đường nâng bù chỉ kiểm được ở đây. Bỏ qua lặng lẽ là điều cấm —
     * không kết nối được thì ghi 🟡 rõ ràng như validateDatabase.
     */
    private function validateTcmDatabase(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            $this->warnings[] = 'Database không kết nối được — BỎ QUA V20 (tầng DB).';

            return;
        }
        if (! DB::getSchemaBuilder()->hasTable('ingredient_tcm_traits')
            || ! DB::getSchemaBuilder()->hasTable('dish_tcm_summary')
            || ! DB::getSchemaBuilder()->hasColumn('dishes', 'level_basis')) {
            $this->warnings[] = 'Chưa có hạ tầng TCM-000 trong DB (chưa migrate) — BỎ QUA V20 (tầng DB).';

            return;
        }

        // Nhãn trắng nguồn lọt vào DB (R5 là NOT NULL nhưng chuỗi rỗng vẫn lọt).
        $n = DB::table('ingredient_tcm_traits')->whereRaw("TRIM(source_ref) = ''")->count();
        if ($n > 0) {
            $this->errors[] = "V20: {$n} nhãn tính vị trong DB có source_ref rỗng — vi phạm R5";
        }

        // P-DERIVE (TCM-001c): dòng suy diễn trong DB phải ở trần 'low'
        // (CHECK ở migration 100012 cũng khoá — đây là chốt tường minh).
        if (DB::getSchemaBuilder()->hasColumn('ingredient_tcm_traits', 'derived_from')) {
            $n = DB::table('ingredient_tcm_traits')
                ->whereNotNull('derived_from')
                ->where('confidence', '<>', 'low')
                ->count();
            if ($n > 0) {
                $this->errors[] = "V20: {$n} nhãn suy diễn P-DERIVE trong DB có confidence khác 'low'";
            }
        }

        // Cổng Mức 3 + đường nâng bù: cần ghi chú văn hoá VÀ phủ ≥60%.
        $candidates = DB::table('dishes')
            ->where(fn ($q) => $q->where('completeness_level', '>=', 3)
                ->orWhere('level_basis', 'tcm_compensation'))
            ->get(['id', 'slug', 'completeness_level', 'level_basis', 'level_note', 'cultural_note']);
        foreach ($candidates as $dish) {
            $label = "V20 [{$dish->slug}]";
            if ($dish->level_basis === 'tcm_compensation' && blank($dish->level_note)) {
                $this->errors[] = "{$label}: Mức 2 (có note) trong DB nhưng level_note rỗng";
            }
            if (blank($dish->cultural_note)) {
                $this->errors[] = "{$label}: thiếu cultural_note — chưa đủ cổng thông tin Mức 3";
            }
            $coverage = DB::table('dish_tcm_summary')->where('dish_id', $dish->id)->value('coverage_pct');
            if ($coverage === null || (float) $coverage < \App\Cultural\TcmVocabulary::LEVEL3_MIN_COVERAGE) {
                $this->errors[] = "{$label}: coverage tính vị = ".($coverage === null ? 'chưa tổng hợp' : $coverage.'%').' — cổng yêu cầu ≥60%';
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

    /**
     * V17 — hồ sơ hoa quả (FRUIT-MODULE.md): cấu trúc file, tham chiếu nguyên liệu
     * trai-cay, tháng mùa vụ hợp lệ, cửa sổ là tập con của tháng có hàng, lưu ý
     * kết hợp có mức bằng chứng, và kỷ luật giá (mỗi dòng giá có nguồn + ngày
     * thu thập, min ≤ max).
     *
     * @param  array<string, array<string, mixed>>  $fruits
     * @param  array<string, array<string, mixed>>  $ingredients
     */
    private function validateFruitFiles(array $fruits, array $ingredients): void
    {
        $groups = ['co-mui', 'mong', 'hach', 'nhiet-doi', 'dua', 'khac'];
        foreach ($fruits as $slug => $j) {
            $this->checked++;
            $label = "[{$slug}]";

            if (($j['slug'] ?? '') !== $slug) {
                $this->errors[] = "V17 {$label}: slug trong file không khớp tên file";
            }

            $ingSlug = $j['ingredient_slug'] ?? '';
            if (! isset($ingredients[$ingSlug])) {
                $this->errors[] = "V17 {$label}: ingredient_slug '{$ingSlug}' không tồn tại";

                continue;
            }
            if (($ingredients[$ingSlug]['category'] ?? '') !== 'trai-cay') {
                $this->errors[] = "V17 {$label}: nguyên liệu '{$ingSlug}' không thuộc nhóm trai-cay";
            }

            $c = $j['classification'] ?? [];
            if (! in_array($c['group'] ?? '', $groups, true)) {
                $this->errors[] = "V17 {$label}: classification.group không hợp lệ: ".($c['group'] ?? '');
            }
            if (! in_array($c['origin'] ?? '', ['trong-nuoc', 'nhap-khau', 'ca-hai'], true)) {
                $this->errors[] = "V17 {$label}: classification.origin không hợp lệ: ".($c['origin'] ?? '');
            }

            $a = $j['availability'] ?? [];
            $months = $a['months'] ?? [];
            $validMonths = is_array($months) && $months !== []
                && array_filter($months, fn ($m) => ! is_int($m) || $m < 1 || $m > 12) === []
                && count($months) === count(array_unique($months));
            if (! $validMonths) {
                $this->errors[] = "V17 {$label}: availability.months phải là các tháng 1–12 không trùng, không rỗng";
                $months = [];
            }
            if (! in_array($a['availability_type'] ?? '', ['theo-mua', 'quanh-nam', 'quanh-nam-nhap-khau'], true)) {
                $this->errors[] = "V17 {$label}: availability.availability_type không hợp lệ";
            }
            foreach (['main_season_months' => $a['main_season_months'] ?? [], 'best_nutrition_window' => $j['best_nutrition_window']['months'] ?? [], 'best_value_window' => $j['best_value_window']['months'] ?? []] as $field => $win) {
                if (array_diff($win, $months) !== []) {
                    $this->errors[] = "V17 {$label}: {$field} chứa tháng ngoài availability.months";
                }
            }

            foreach ($j['pairing_cautions'] ?? [] as $pc) {
                if (blank($pc['with'] ?? null) || blank($pc['note'] ?? null)) {
                    $this->errors[] = "V17 {$label}: pairing_cautions thiếu with/note";
                }
                if (! in_array($pc['level'] ?? '', ['co-so', 'dan-gian'], true)) {
                    $this->errors[] = "V17 {$label}: pairing_cautions.level phải là co-so hoặc dan-gian";
                }
            }

            // V20 — ghi chú văn hoá của hồ sơ quả (🔴 khi có mà hỏng cấu trúc).
            $this->validateCulturalNote($label, $j['cultural_note'] ?? null);

            foreach ($j['prices'] ?? [] as $p) {
                if (! in_array($p['region_code'] ?? '', ['bac', 'trung', 'nam'], true)) {
                    $this->errors[] = "V17 {$label}: prices.region_code phải là bac/trung/nam";
                }
                if (! is_int($p['month'] ?? null) || $p['month'] < 1 || $p['month'] > 12) {
                    $this->errors[] = "V17 {$label}: prices.month ngoài 1–12";
                }
                if (($p['price_min'] ?? 0) <= 0 || ($p['price_max'] ?? 0) < ($p['price_min'] ?? 0)) {
                    $this->errors[] = "V17 {$label}: prices phải có 0 < price_min ≤ price_max";
                }
                if (blank($p['source_name'] ?? null)) {
                    $this->errors[] = "V17 {$label}: dòng giá thiếu source_name — giá vô danh bị cấm (FRUIT-MODULE §3)";
                }
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($p['collected_at'] ?? ''))) {
                    $this->errors[] = "V17 {$label}: dòng giá thiếu/sai collected_at (YYYY-MM-DD)";
                }
            }

            $this->validateReviewTrail($label, $j);
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

    /**
     * V19 — mục media trong file món/nguyên liệu (IMG-001): mỗi mục phải có
     * license (R4), file ảnh phải tồn tại trong data/, tối đa 1 ảnh chính,
     * và license phải khớp mục MANIFEST của cùng ảnh (V2 soi chiều file →
     * manifest, V19 soi chiều mục khai báo → file + manifest).
     */
    private function validateMediaEntries(array $ingredients, array $dishes): void
    {
        $manifestPath = base_path('data/media/MANIFEST.json');
        $manifest = is_file($manifestPath)
            ? (json_decode((string) file_get_contents($manifestPath), true) ?? [])
            : [];

        foreach (['món' => $dishes, 'nguyên liệu' => $ingredients] as $kind => $items) {
            foreach ($items as $slug => $j) {
                $primary = 0;
                foreach ($j['media'] ?? [] as $media) {
                    $this->checked++;
                    $path = (string) ($media['path'] ?? '');
                    $license = (string) ($media['license'] ?? '');
                    if ($license === '') {
                        $this->errors[] = "V19 [{$slug}]: mục media của {$kind} thiếu license (R4)";
                    }
                    if ($path === '' || ! is_file(base_path('data/'.$path))) {
                        $this->errors[] = "V19 [{$slug}]: file ảnh '{$path}' không tồn tại trong data/";
                    }
                    $manifestKey = preg_replace('#^media/#', '', $path);
                    $entry = $manifest[$manifestKey] ?? null;
                    if ($entry === null) {
                        $this->errors[] = "V19 [{$slug}]: ảnh '{$path}' chưa có mục trong media/MANIFEST.json (R4)";
                    } elseif (($entry['license'] ?? null) !== $license) {
                        $this->errors[] = "V19 [{$slug}]: license của '{$path}' lệch MANIFEST ({$license} ≠ ".($entry['license'] ?? '∅').')';
                    }
                    if (! empty($media['is_primary'])) {
                        $primary++;
                    }
                }
                if ($primary > 1) {
                    $this->errors[] = "V19 [{$slug}]: {$kind} có {$primary} ảnh chính — tối đa 1";
                }
            }
        }
    }

    /**
     * V16 — hệ nhãn phân loại (TAXONOMY.md).
     * File nguyên liệu: bộ nhãn phải BẰNG ĐÚNG kết quả của TaggingRules (quy tắc
     * là nguồn sự thật — nhãn gán tay lệch quy tắc là lỗi). File món: chỉ được
     * mang các nhãn BIÊN TẬP — đúng 1 nhãn loai: và tối đa 1 nhãn dac-san:
     * (DATA-010); nhãn suy được (diet/protein/allergen) cấm ghi tay vào file
     * vì seeder suy từ thành phần.
     */
    private function validateTaxonomy(array $ingredients, array $dishes): void
    {
        $registry = array_keys(config('taxonomy.tags', []));

        foreach ($ingredients as $slug => $j) {
            $actual = $j['tags'] ?? [];
            sort($actual);
            $expected = \App\Taxonomy\TaggingRules::tagsFor($slug, (string) ($j['category'] ?? ''));
            sort($expected);
            if ($actual !== $expected) {
                $this->errors[] = "V16 [{$slug}]: nhãn trong file lệch TaggingRules — file có ["
                    .implode(', ', $actual).'], quy tắc cho ['.implode(', ', $expected).']';
            }
        }

        foreach ($dishes as $slug => $j) {
            $tags = $j['tags'] ?? [];
            $loai = array_values(array_filter($tags, fn ($t) => str_starts_with((string) $t, 'loai:')));
            if (count($loai) !== 1) {
                $this->errors[] = "V16 [{$slug}]: món phải có đúng 1 nhãn loai: trong file (đang có ".count($loai).')';
            }
            $dacSan = array_values(array_filter($tags, fn ($t) => str_starts_with((string) $t, 'dac-san:')));
            if (count($dacSan) > 1) {
                $this->errors[] = "V16 [{$slug}]: món có tối đa 1 nhãn dac-san: trong file (đang có ".count($dacSan).')';
            }
            foreach ($tags as $t) {
                if (! in_array($t, $registry, true)) {
                    $this->errors[] = "V16 [{$slug}]: nhãn '{$t}' không có trong đăng ký config/taxonomy.php";
                } elseif (! str_starts_with((string) $t, 'loai:') && ! str_starts_with((string) $t, 'dac-san:')) {
                    $this->errors[] = "V16 [{$slug}]: nhãn '{$t}' là nhãn SUY TỪ THÀNH PHẦN — không ghi tay vào file món";
                }
            }
        }
    }

    /** V16 (DB) — nhãn suy của món trong DB phải khớp phép suy lại từ thành phần. */
    private function validateDishTagsDatabase(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('ingredient_tags')) {
            return;
        }
        $drift = [];
        \App\Models\Dish::with(['tags', 'dishIngredients.ingredient.tags'])
            ->chunkById(200, function ($chunk) use (&$drift) {
                foreach ($chunk as $dish) {
                    $expected = \App\Taxonomy\DishTagDeriver::deriveForDish($dish);
                    sort($expected);
                    $actual = $dish->tags->pluck('slug')
                        ->filter(fn ($t) => preg_match('/^(diet|protein|allergen):/', (string) $t) === 1)
                        ->values()->all();
                    sort($actual);
                    if ($actual !== $expected) {
                        $drift[] = $dish->slug;
                    }
                }
            });
        if ($drift !== []) {
            $this->errors[] = 'V16: '.count($drift).' món có nhãn suy trong DB lệch phép suy từ thành phần (ví dụ: '
                .implode(', ', array_slice($drift, 0, 10)).') — chạy lại migrate:fresh --seed';
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

        $this->validateDishTagsDatabase();

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
            ->whereRaw('JSON_LENGTH(missing_ingredients) > 0')
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
            // Độ giống trigram tính ở tầng PHP qua App\Support\NameSimilarity
            // (dùng chung với cảnh báo trùng món ADM-005 trong trang quản trị).
            $primaries = DB::table('dish_names')
                ->where('is_primary', true)
                ->get(['id', 'dish_id', 'locale', 'name']);
            $byLocale = $primaries->groupBy('locale');
            foreach ($byLocale as $rows) {
                $rows = $rows->values();
                $total = $rows->count();
                for ($i = 0; $i < $total; $i++) {
                    for ($j = $i + 1; $j < $total; $j++) {
                        $a = $rows[$i];
                        $b = $rows[$j];
                        if ($a->dish_id === $b->dish_id) {
                            continue;
                        }
                        if (\App\Support\NameSimilarity::similarity($a->name, $b->name) > 0.9) {
                            $this->warnings[] = "V7: hai món tên giống nhau >90%: \"{$a->name}\" ↔ \"{$b->name}\"";
                        }
                    }
                }
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
