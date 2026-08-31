<?php

declare(strict_types=1);

use App\Nutrition\IngredientInput;
use App\Nutrition\NutritionCalculator;

// Engine là hàm thuần tuý (CALC-001) — test không cần database, chỉ fixture.

function calc(): NutritionCalculator
{
    return new NutritionCalculator;
}

function thitBo(float $grams = 100.0): IngredientInput
{
    return new IngredientInput(
        slug: 'thit-bo-test', grams: $grams,
        mappingConfidence: 'high', fctSource: 'usda_sr_legacy',
        kcalPer100g: 200.0, proteinG: 20.0, fatG: 10.0, carbG: 0.0, fiberG: 0.0,
    );
}

it('tinh dung tong kcal cho mon day du, cung mot bang, anh xa high', function () {
    $result = calc()->compute(
        [thitBo(100.0), new IngredientInput(
            slug: 'gao-test', grams: 50.0,
            mappingConfidence: 'high', fctSource: 'usda_sr_legacy',
            kcalPer100g: 100.0, proteinG: 4.0, fatG: 1.0, carbG: 20.0, fiberG: 1.0,
        )],
        cookingMethod: 'luoc', fctSource: 'usda_sr_legacy',
    );

    // 100g×200/100 + 50g×100/100 = 250 kcal; anh xa high ⇒ khong noi khoang
    expect($result->kcalMin)->toBe(250.0)
        ->and($result->kcalMax)->toBe(250.0)
        ->and($result->confidence)->toBe('high')
        ->and($result->missing)->toBe([])
        ->and($result->proteinG)->toBe(22.0)
        ->and($result->engineVersion)->toBe(NutritionCalculator::ENGINE_VERSION);
});

it('nguyen lieu khong tra duoc thi vao missing, khong uoc luong thay the (QT-1)', function () {
    $result = calc()->compute(
        [thitBo(100.0), new IngredientInput(slug: 'ca-loc', grams: 20.0)],
        cookingMethod: 'nau', fctSource: 'usda_sr_legacy',
    );

    // 20/120 ≈ 16,7% khoi luong thieu ⇒ van tinh duoc nhung confidence=low,
    // chi noi can TREN theo ty le thieu (CALORIE-ENGINE §5)
    expect($result->missing)->toBe(['ca-loc'])
        ->and($result->confidence)->toBe('low')
        ->and($result->kcalMin)->toBe(200.0)
        ->and($result->kcalMax)->toBe(233.3);
});

it('thieu >= 30% khoi luong thi confidence=unknown va khong tra so nao (QT-5)', function () {
    $result = calc()->compute(
        [thitBo(100.0), new IngredientInput(slug: 'ca-loc', grams: 50.0)],
        cookingMethod: 'nau', fctSource: 'usda_sr_legacy',
    );

    expect($result->confidence)->toBe('unknown')
        ->and($result->kcalMin)->toBeNull()
        ->and($result->kcalMax)->toBeNull()
        ->and($result->hasNumbers())->toBeFalse();
});

it('khong co nguyen lieu nao thi unknown', function () {
    $result = calc()->compute([], cookingMethod: 'nau', fctSource: 'usda_sr_legacy');

    expect($result->confidence)->toBe('unknown');
});

it('tu choi nguyen lieu ngoai fct_source chi dinh — khong tron bang (QT-4)', function () {
    $result = calc()->compute(
        [thitBo(100.0), new IngredientInput(
            slug: 'nuoc-mam-test', grams: 10.0,
            mappingConfidence: 'high', fctSource: 'nhan_san_pham', kcalPer100g: 50.0,
        )],
        cookingMethod: 'nau', fctSource: 'usda_sr_legacy',
    );

    // Nguyen lieu nguon khac bi coi la missing, khong duoc cong vao
    expect($result->missing)->toBe(['nuoc-mam-test'])
        ->and($result->kcalMin)->toBe(200.0);
});

it("fct_source='mixed' nhan moi nguon nhung confidence toi da medium (DATA-PIPELINE §7.2)", function () {
    $result = calc()->compute(
        [thitBo(100.0), new IngredientInput(
            slug: 'nuoc-mam-test', grams: 100.0,
            mappingConfidence: 'high', fctSource: 'nhan_san_pham', kcalPer100g: 50.0,
        )],
        cookingMethod: 'nau', fctSource: 'mixed',
    );

    expect($result->missing)->toBe([])
        ->and($result->kcalMin)->toBe(250.0)
        ->and($result->confidence)->toBe('medium');
});

it('nguyen lieu tuy chon chi nam o can tren cua khoang', function () {
    $result = calc()->compute(
        [thitBo(100.0), new IngredientInput(
            slug: 'dau-phong-test', grams: 20.0, isOptional: true,
            mappingConfidence: 'high', fctSource: 'usda_sr_legacy', kcalPer100g: 500.0,
        )],
        cookingMethod: 'nau', fctSource: 'usda_sr_legacy',
    );

    // Khong tuy chon: 200. Tuy chon them 20g×500/100 = 100 vao can tren.
    expect($result->kcalMin)->toBe(200.0)->and($result->kcalMax)->toBe(300.0);
});

it('nguyen lieu vot bo (sa, la chanh) tinh khoi luong nhung khong cong calo (§4a)', function () {
    $result = calc()->compute(
        [thitBo(100.0), new IngredientInput(
            slug: 'sa-cay', grams: 100.0, isDiscarded: true,
            mappingConfidence: 'high', fctSource: 'usda_sr_legacy', kcalPer100g: 99.0,
        )],
        cookingMethod: 'nau', fctSource: 'usda_sr_legacy',
    );

    expect($result->kcalMin)->toBe(200.0)
        ->and($result->kcalMax)->toBe(200.0)
        ->and($result->totalRawG)->toBe(200.0);
});

it('mon chien cong dau hap thu va noi khoang 0,80-1,25, confidence <= medium (§4)', function () {
    $result = calc()->compute(
        [new IngredientInput(
            slug: 'khoai-tay-test', grams: 100.0,
            mappingConfidence: 'high', fctSource: 'usda_sr_legacy',
            kcalPer100g: 77.0, fatG: 0.2, oilAbsorptionPct: 14.0,
        )],
        cookingMethod: 'chien', fctSource: 'usda_sr_legacy',
    );

    // 77 + 14g dau × 9 = 203; ×0,80 / ×1,25
    expect($result->kcalMin)->toBe(162.4)
        ->and($result->kcalMax)->toBe(253.8)
        ->and($result->confidence)->toBe('medium')
        ->and($result->fatG)->toBe(14.2);
});

it('mon chien co nguyen lieu thieu oil_absorption_pct thi nguyen lieu do vao missing (CALC-002)', function () {
    // thitBo không có oil_absorption_pct ⇒ trong món chiên bị coi là thiếu dữ liệu,
    // không bịa hệ số (QT-1). Chiếm 100% khối lượng ⇒ unknown.
    $result = calc()->compute([thitBo(100.0)], cookingMethod: 'chien', fctSource: 'usda_sr_legacy');

    expect($result->missing)->toContain('thit-bo-test')
        ->and($result->notes)->toContain('thieu-he-so-hap-thu-dau:thit-bo-test')
        ->and($result->confidence)->toBe('unknown');
});

it('cung nguyen lieu, luoc vs chien cho ket qua khac nhau ro ret (CALC-002)', function () {
    $khoai = fn () => new IngredientInput(
        slug: 'khoai-tay-test', grams: 100.0,
        mappingConfidence: 'high', fctSource: 'usda_sr_legacy',
        kcalPer100g: 77.0, fatG: 0.2, oilAbsorptionPct: 14.0,
    );
    $luoc = calc()->compute([$khoai()], 'luoc', 'usda_sr_legacy');
    $chien = calc()->compute([$khoai()], 'chien', 'usda_sr_legacy');

    // Luộc: 77 kcal điểm. Chiên: 77 + 126 kcal dầu, nới 0,80–1,25.
    expect($luoc->kcalMax)->toBe(77.0)
        ->and($chien->kcalMin)->toBeGreaterThan($luoc->kcalMax * 2)
        ->and($chien->confidence)->toBe('medium')
        ->and($luoc->confidence)->toBe('high');
});

it('hieu chinh nang luong thuc pham giau xo — moc nhi kho (§4b)', function () {
    $result = calc()->compute(
        [new IngredientInput(
            slug: 'moc-nhi-kho', grams: 100.0,
            mappingConfidence: 'high', fctSource: 'usda_sr_legacy',
            kcalPer100g: 284.0, carbG: 73.0, fiberG: 70.1,
        )],
        cookingMethod: 'nau', fctSource: 'usda_sr_legacy',
    );

    // xo/carb = 0,96 > 0,5 ⇒ kcal 284 − 70,1×2 = 143,8 (he so EU 2 kcal/g)
    expect($result->kcalMin)->toBe(143.8)
        ->and($result->notes)->toContain('da-hieu-chinh-xo:moc-nhi-kho');
});

it('khau phan khong can thi noi khoang ±15% va confidence <= medium (§5)', function () {
    $result = calc()->compute(
        [thitBo(100.0)],
        cookingMethod: 'luoc', fctSource: 'usda_sr_legacy', portionWeighed: false,
    );

    expect($result->kcalMin)->toBe(170.0)
        ->and($result->kcalMax)->toBe(230.0)
        ->and($result->confidence)->toBe('medium');
});

it('anh xa medium noi khoang ±5% theo dong gop nguyen lieu (§5)', function () {
    $result = calc()->compute(
        [new IngredientInput(
            slug: 'dau-phu-test', grams: 100.0,
            mappingConfidence: 'medium', fctSource: 'usda_sr_legacy', kcalPer100g: 100.0,
        )],
        cookingMethod: 'luoc', fctSource: 'usda_sr_legacy',
    );

    expect($result->kcalMin)->toBe(95.0)
        ->and($result->kcalMax)->toBe(105.0)
        ->and($result->confidence)->toBe('medium');
});

it('cung dau vao luon cho cung dau ra — ham thuan tuy', function () {
    $input = [thitBo(123.0)];
    $a = calc()->compute($input, 'xao', 'usda_sr_legacy');
    $b = calc()->compute($input, 'xao', 'usda_sr_legacy');

    expect($a)->toEqual($b);
});
