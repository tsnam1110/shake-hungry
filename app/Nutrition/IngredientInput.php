<?php

declare(strict_types=1);

namespace App\Nutrition;

/**
 * Đầu vào cho một nguyên liệu của phép tính — đã quy đổi sang GRAM SỐNG ĂN ĐƯỢC.
 *
 * Việc quy đổi đơn vị đời thường → gram (ingredient_units / density) và hệ số phần
 * ăn được (edible_portion_pct, DATA-008) diễn ra TRƯỚC khi tạo object này, ở tầng
 * DishNutritionService — engine giữ thuần tuý (CODING-STANDARDS §4).
 *
 * Giá trị dinh dưỡng null = "không tra được trong fct_source yêu cầu" ⇒ nguyên liệu
 * rơi vào missing[] (QT-1: không ước lượng thay thế).
 */
final readonly class IngredientInput
{
    public function __construct(
        public string $slug,
        public float $grams,
        public bool $isOptional = false,
        public bool $isDiscarded = false,
        public string $mappingConfidence = 'high',
        public ?string $fctSource = null,
        public ?float $kcalPer100g = null,
        public ?float $proteinG = null,
        public ?float $fatG = null,
        public ?float $carbG = null,
        public ?float $fiberG = null,
        public bool $isReadyToEat = false,
        public ?float $yieldFactor = null,
        public ?float $oilAbsorptionPct = null,
    ) {}
}
