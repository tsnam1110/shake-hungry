<?php

declare(strict_types=1);

namespace App\Nutrition;

use App\Models\Drink;
use App\Models\Ingredient;

/**
 * Tính kcal cho nước uống pha chế từ công thức đã gắn `ingredient_slug`
 * (hoàn thiện lõi nước uống, 2026-11-03).
 *
 * KẾT LUẬN VỀ ETHANOL (ghi rõ để không hỏi lại): engine KHÔNG cần logic
 * ethanol riêng. NutritionCalculator cộng trực tiếp `kcalPer100g` từ bảng
 * tham chiếu, không suy kcal từ protein/fat/carb — mà kcal USDA của rượu
 * mạnh/rượu mùi đã bao gồm năng lượng ethanol (USDA dùng 6,93 kcal/g;
 * vodka 40% = 231 kcal/100g với protein/fat/carb đều 0). "Lỗ hổng ethanol"
 * trong các báo cáo trước thực chất là lỗ hổng GẮN KẾT: công thức nước uống
 * chỉ là chữ tự do, không trỏ tới hồ sơ nguyên liệu nào, nên không tính
 * được gì. Khi dòng nguyên liệu đã gắn slug và có kcal tham chiếu (kể cả
 * rượu mạnh), phép tính đã bao gồm ethanol; khi dòng có cồn chỉ có số
 * /100ml của nhãn hãng mà chưa đo density (triple sec, Kahlúa, Campari,
 * Aperol, vermouth, orgeat, grenadine, ginger beer) thì dòng đó rơi vào
 * missing và đồ uống giữ trạng thái "chưa tính được" — với lý do cụ thể
 * là thiếu density, không phải thiếu logic ethanol.
 *
 * Quy tắc quy đổi (QT-9 — không đoán):
 * - `g` → trực tiếp; `ml` → × density_g_per_ml (null ⇒ missing).
 * - `quả` → qua bảng units của nguyên liệu (tắc: 1 quả = 19g).
 * - `thìa cà phê` → alias của `muỗng cà phê` trong bảng units (đường: 4,2g).
 * - `viên` (đường viên) → alias của `viên đường` (2,3g).
 * - Dòng có kcal = 0 (soda) dù quantity null vẫn tính 0, không missing.
 * - Dòng garnish không đáng kể (lá, nhánh, dash, giọt, muối/tiêu viền,
 *   quantity null là gia vị) → bỏ qua, ghi vào negligible, tối đa ~1 kcal
 *   mỗi dòng — khoảng nới của engine đã phủ.
 * - Mọi dòng còn lại không quy đổi được hoặc không gắn slug mà có khối
 *   lượng ml/g → missing (ghi tên), không ước lượng thay thế (QT-1) — và
 *   khối lượng của chúng ĐƯỢC đưa vào engine dưới dạng input null-kcal để
 *   ngưỡng missing ≥30% khối lượng của engine có tác dụng (tránh trường hợp
 *   Old Fashioned chỉ tính đường viên ~9 kcal trong khi whiskey 45ml chưa
 *   tính — đó là số thiếu đội lốt số đủ). Với dòng ml chưa biết density,
 *   khối lượng dùng cho ngưỡng này lấy bằng chính số ml (sai số density
 *   của rượu mùi/siro chỉ ~0–20%, không đổi kết luận ngưỡng).
 * - Dòng nguyên liệu pha (bột cà phê phin, túi trà lọc) là đầu vào quá
 *   trình, không phải phần uống — bỏ qua như negligible (phần chiết xuất
 *   đã có dòng riêng mang kcal).
 * - Đồ đóng gói KHÔNG qua service này: số theo loại, không phải số của
 *   một sản phẩm cụ thể — chỉ đọc nhãn (xem khối "Thông tin nhãn").
 */
final class DrinkNutritionService
{
    /** Tên dòng được coi là nước/đá = 0 kcal (sự thật vật lý, không phải ước lượng). */
    private const array ZERO_KCAL_NAMES = ['nước', 'nước nóng', 'nước lọc', 'nước sôi', 'nước ấm', 'đá', 'nước đá'];

    /** Đầu vào quá trình pha (bã/túi lọc) — không uống, phần chiết xuất đã có dòng riêng. */
    private const array PROCESS_INPUT_NAMES = ['bột cà phê phin', 'trà đào túi lọc'];

    /** Đơn vị garnish không đáng kể. */
    private const array NEGLIGIBLE_UNITS = ['lá', 'nhánh', 'dash', 'giọt'];

    /** Alias đơn vị trong file nước uống → tên unit trong bảng ingredient_units. */
    private const array UNIT_ALIASES = [
        'thìa cà phê' => 'muỗng cà phê',
        'viên' => 'viên đường',
    ];

    public function __construct(private readonly NutritionCalculator $calculator)
    {
    }

    /**
     * @return array{status: string, kcal_min: ?float, kcal_max: ?float, confidence: ?string, missing: list<string>, negligible: list<string>}
     */
    public function compute(Drink $drink): array
    {
        if ($drink->kind !== 'pha-che') {
            return $this->unknown(['đồ đóng gói: chỉ đọc nhãn, không tính từ công thức']);
        }

        $lines = $drink->ingredients ?? [];
        $inputs = [];
        $missing = [];
        $negligible = [];

        foreach ($lines as $line) {
            $name = (string) ($line['name'] ?? '');
            $slug = $line['ingredient_slug'] ?? null;
            $qty = $line['quantity'] ?? null;
            $unit = $line['unit'] ?? null;

            if (in_array(mb_strtolower(trim($name)), self::ZERO_KCAL_NAMES, true)) {
                continue;
            }
            if (in_array($name, self::PROCESS_INPUT_NAMES, true)) {
                $negligible[] = $name.' (đầu vào pha, không uống)';
                continue;
            }

            if ($slug === null) {
                if (in_array($unit, self::NEGLIGIBLE_UNITS, true)
                    || str_contains($name, 'muối') || str_contains($name, 'tiêu')) {
                    $negligible[] = $name;
                    continue;
                }
                if ($qty === null) {
                    // Lượng không ghi mà lại là thành phần chính (nền trà sữa,
                    // sữa hấp, trái cây cắt lát…) ⇒ missing, không coi là garnish.
                    $missing[] = $name.' (chưa ghi lượng)';
                    continue;
                }
                $missing[] = $name.' (chưa gắn hồ sơ nguyên liệu)';
                // Đưa khối lượng vào engine để ngưỡng missing ≥30% có tác dụng.
                if (in_array($unit, ['ml', 'g'], true) && $qty !== null) {
                    $inputs[] = new IngredientInput(slug: 'missing:'.$name, grams: (float) $qty, fctSource: null, kcalPer100g: null);
                }
                continue;
            }

            $ingredient = Ingredient::where('slug', $slug)->first();
            if ($ingredient === null) {
                $missing[] = $name.' (slug không có trong kho)';
                continue;
            }

            $ref = $ingredient->nutrition_snapshot ?? [];
            $kcal = $ref['kcal_per_100g'] ?? null;
            if ($kcal === null) {
                if ((float) $qty === 0.0) {
                    continue;
                }
                $missing[] = $name.' (chưa có kcal tham chiếu — xem mapping_note)';
                if (in_array($unit, ['ml', 'g'], true) && $qty !== null) {
                    $inputs[] = new IngredientInput(slug: 'missing:'.$slug, grams: (float) $qty, fctSource: null, kcalPer100g: null);
                }
                continue;
            }
            if ($qty === null) {
                if ((float) $kcal === 0.0) {
                    continue;
                }
                $missing[] = $name.' (chưa ghi lượng)';
                continue;
            }

            $grams = $this->resolveGrams($ingredient, $qty, $unit);
            if ($grams === null) {
                $missing[] = $name.' (không quy đổi được đơn vị '.($unit ?? 'trống').')';
                continue;
            }

            $inputs[] = new IngredientInput(
                slug: $slug,
                grams: (float) $grams,
                mappingConfidence: $ingredient->mapping_confidence ?? 'low',
                fctSource: $ref['fct_source'] ?? null,
                kcalPer100g: (float) $kcal,
                proteinG: isset($ref['protein_g']) ? (float) $ref['protein_g'] : null,
                fatG: isset($ref['fat_g']) ? (float) $ref['fat_g'] : null,
                carbG: isset($ref['carb_g']) ? (float) $ref['carb_g'] : null,
                fiberG: isset($ref['fiber_g']) ? (float) $ref['fiber_g'] : null,
                isReadyToEat: true,
            );
        }

        // 'mixed': công thức nước uống trộn nhiều bảng (USDA + Nhật + nhãn hãng
        // qua hồ sơ nguyên liệu) — engine hạ confidence xuống ≤ medium, đúng QT-4
        // ngoại lệ có kiểm soát (DATA-PIPELINE §7.2).
        $result = $this->calculator->compute($inputs, 'song', 'mixed', true);

        if (! $result->hasNumbers()) {
            return $this->unknown(array_values(array_unique(array_merge($missing, $result->missing))));
        }

        return [
            'status' => 'computed',
            'kcal_min' => $result->kcalMin,
            'kcal_max' => $result->kcalMax,
            'confidence' => $result->confidence,
            'missing' => array_values(array_unique(array_merge($missing, $result->missing))),
            'negligible' => array_values(array_unique($negligible)),
        ];
    }

    /** @return array{status: string, kcal_min: null, kcal_max: null, confidence: null, missing: list<string>, negligible: list<string>} */
    private function unknown(array $missing): array
    {
        return [
            'status' => 'unknown',
            'kcal_min' => null,
            'kcal_max' => null,
            'confidence' => null,
            'missing' => array_values(array_unique($missing)),
            'negligible' => [],
        ];
    }

    private function resolveGrams(Ingredient $ingredient, mixed $qty, ?string $unit): ?float
    {
        $qty = (float) $qty;
        if ($unit === 'g') {
            return $qty;
        }
        if ($unit === 'ml') {
            return $ingredient->density_g_per_ml !== null ? $qty * (float) $ingredient->density_g_per_ml : null;
        }
        $unitName = self::UNIT_ALIASES[$unit ?? ''] ?? $unit;
        $row = $ingredient->units->firstWhere(fn ($u) => mb_strtolower(trim($u->unit_name)) === mb_strtolower(trim((string) $unitName)));
        if ($row !== null) {
            return $qty * (float) $row->grams;
        }
        // Đơn vị "quả" của tắc đã có trong bảng units; các đơn vị đếm khác
        // không có quy đổi đã kiểm chứng ⇒ null (missing), không đoán.
        return null;
    }
}
