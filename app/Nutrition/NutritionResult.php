<?php

declare(strict_types=1);

namespace App\Nutrition;

/**
 * Kết quả tính dinh dưỡng — LUÔN là khoảng (QT-5).
 *
 * confidence = 'unknown' ⇒ mọi giá trị số là null và KHÔNG được hiển thị
 * hay ghi vào dish_nutrition (CALORIE-ENGINE §6).
 */
final readonly class NutritionResult
{
    public function __construct(
        public ?float $kcalMin,
        public ?float $kcalMax,
        public ?float $proteinG,
        public ?float $fatG,
        public ?float $carbG,
        public ?float $fiberG,
        public string $confidence,
        /** @var list<string> slug các nguyên liệu không tra được */
        public array $missing,
        public string $fctSource,
        public string $engineVersion,
        public float $totalRawG,
        public ?float $cookedGramsEstimate,
        /** @var list<string> ghi chú máy sinh: hiệu chỉnh xơ, thiếu hệ số dầu… */
        public array $notes = [],
    ) {}

    public function hasNumbers(): bool
    {
        return $this->confidence !== 'unknown' && $this->kcalMin !== null;
    }
}
