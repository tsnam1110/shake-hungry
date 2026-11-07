<?php

declare(strict_types=1);

use App\Cultural\TcmAggregator;
use App\Cultural\TcmDisplay;
use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\DishRecipeSource;
use App\Models\DishTcmSummary;
use App\Models\Ingredient;
use App\Models\IngredientTcmTrait;
use App\Models\Tag;
use App\Taxonomy\DishTagDeriver;
use App\Taxonomy\TaggingRules;
use Database\Seeders\IngredientSeeder;
use Database\Seeders\RegionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * TCM-000 — nền kỹ thuật lớp Mức 3: engine tổng hợp (TCM-002), cổng V20,
 * vòng seed → export cho tcm_traits/components, đường nâng bù "Mức 2
 * (có note)" và miễn trừ R8 đi kèm. Mọi nhãn trong test này là FIXTURE
 * trong DB test — tuyệt đối không phải dữ liệu biên soạn thật.
 */
function tcmIngredient(string $slug, array $overrides = []): Ingredient
{
    return Ingredient::create(array_merge([
        'slug' => $slug,
        'name_vi' => 'Nguyên liệu '.$slug,
        'mapping_confidence' => 'high',
        'category' => 'khac',
        'source' => 'curated',
        'confidence' => 'high',
    ], $overrides));
}

function tcmDish(array $overrides = []): Dish
{
    return Dish::create(array_merge([
        'slug' => 'mon-tcm-'.uniqid(),
        'country_code' => 'VN',
        'completeness_level' => 1,
        'source' => 'curated',
        'confidence' => 'medium',
    ], $overrides));
}

function tcmLine(Dish $dish, Ingredient $ing, float $grams): void
{
    DishIngredient::create([
        'dish_id' => $dish->id,
        'ingredient_id' => $ing->id,
        'quantity' => $grams,
        'unit_name' => 'g',
        'source' => 'curated',
    ]);
}

/** Đồng bộ nhãn suy như DishSeeder làm — thiếu bước này V16(DB) báo lệch. */
function tcmSyncDerivedTags(Dish $dish): void
{
    $dish->load('dishIngredients.ingredient.tags');
    $dish->tags()->sync(Tag::idsFor(DishTagDeriver::deriveForDish($dish)));
}

function tcmTrait(Ingredient $ing, ?string $nature, array $overrides = []): IngredientTcmTrait
{
    return IngredientTcmTrait::create(array_merge([
        'ingredient_id' => $ing->id,
        'nature' => $nature,
        'flavor' => null,
        'element' => null,
        'source_ref' => 'Fixture test TCM-000',
        'confidence' => 'high',
    ], $overrides));
}

// --- Engine tổng hợp (TCM-002) -------------------------------------------

it('TCM-002: tinh nature_score theo trong so khoi luong cong he so che bien (tinh tay)', function () {
    $a = tcmIngredient('tcm-a');
    $b = tcmIngredient('tcm-b');
    $c = tcmIngredient('tcm-c');
    tcmTrait($a, 'on', ['flavor' => 'tan']);       // +1, hành Kim theo vị
    tcmTrait($b, 'han', ['flavor' => 'ham']);      // −2, hành Thuỷ theo vị
    // c không nhãn — chỉ tham gia mẫu số coverage

    $dish = tcmDish(['cooking_method' => 'song']); // hệ số −0,5
    tcmLine($dish, $a, 100);
    tcmLine($dish, $b, 300);
    tcmLine($dish, $c, 100);

    $summary = app(TcmAggregator::class)->summarize($dish);

    // Điểm: (100×1 + 300×−2)/400 = −1,25; cộng hệ số sống −0,5 ⇒ −1,75.
    expect($summary)->not->toBeNull()
        ->and($summary->nature_score)->toBe(-1.75)
        // Coverage: 400/500 = 80%.
        ->and($summary->coverage_pct)->toBe(80.0)
        // Mọi nhãn đều high + phủ ≥60 + không mâu thuẫn — nhưng trần cấp
        // món là medium (TCM-001a: hệ số chế biến là quy ước dự án).
        ->and($summary->confidence)->toBe('medium')
        ->and($summary->has_conflicts)->toBeFalse()
        // Phiếu hành: Kim 100g < Thuỷ 300g ⇒ dominant Thuỷ.
        ->and($summary->dominant_element)->toBe('thuy');
});

it('TCM-002: he so che bien theo nhom — chien +0,5 va xao +0,25', function () {
    $a = tcmIngredient('tcm-hs-a');
    tcmTrait($a, 'binh'); // điểm 0 — điểm tổng hợp chỉ còn hệ số chế biến

    $chien = tcmDish(['cooking_method' => 'chien']);
    tcmLine($chien, $a, 200);
    $xao = tcmDish(['cooking_method' => 'xao']);
    tcmLine($xao, $a, 200);

    $agg = app(TcmAggregator::class);
    expect($agg->summarize($chien)->nature_score)->toBe(0.5)
        ->and($agg->summarize($xao)->nature_score)->toBe(0.25);
});

it('TCM-002: nguon mau thuan — diem trung binh cac dong, co co mau thuan, confidence bi ha', function () {
    $a = tcmIngredient('tcm-mt-a');
    tcmTrait($a, 'han', ['source_ref' => 'Nguồn 1 (fixture)']);
    tcmTrait($a, 'nhiet', ['source_ref' => 'Nguồn 2 (fixture)']);

    $dish = tcmDish(['cooking_method' => null]);
    tcmLine($dish, $a, 100);

    $summary = app(TcmAggregator::class)->summarize($dish);

    // (−2 + 2)/2 = 0 ⇒ bình; phủ 100% nhưng mâu thuẫn ⇒ confidence low.
    expect($summary->nature_score)->toBe(0.0)
        ->and($summary->coverage_pct)->toBe(100.0)
        ->and($summary->has_conflicts)->toBeTrue()
        ->and($summary->confidence)->toBe('low');
});

it('TCM-002: coverage duoi nguong — van luu tong hop nhung TcmDisplay khong ket luan', function () {
    $a = tcmIngredient('tcm-ng-a');
    $b = tcmIngredient('tcm-ng-b');
    tcmTrait($a, 'on');

    $dish = tcmDish();
    tcmLine($dish, $a, 100);   // có nhãn
    tcmLine($dish, $b, 400);   // không nhãn ⇒ coverage 20%

    $summary = app(TcmAggregator::class)->summarize($dish->fresh());
    expect($summary)->not->toBeNull()
        ->and($summary->coverage_pct)->toBe(20.0)
        ->and($summary->confidence)->toBe('low')
        ->and(TcmDisplay::forDish($dish->fresh()))->toBeNull();
});

it('TCM-002: coverage 30–60 — hien thi mot phan, confidence chan low', function () {
    $a = tcmIngredient('tcm-ng2-a');
    $b = tcmIngredient('tcm-ng2-b');
    tcmTrait($a, 'on', ['flavor' => 'cam']);

    $dish = tcmDish();
    tcmLine($dish, $a, 200);
    tcmLine($dish, $b, 300);   // coverage 40%

    app(TcmAggregator::class)->summarize($dish);
    $block = TcmDisplay::forDish($dish->fresh());

    expect($block)->not->toBeNull()
        ->and($block['is_partial'])->toBeTrue()
        ->and($block['confidence'])->toBe('low')
        ->and($block['nature'])->toBe('on')
        ->and($block['dominant_element'])->toBe('tho'); // cam → Thổ
});

it('TCM-001a: dominant_element chi nhan phieu tu dong basis flavor; dong nature/direct van tinh diem tinh', function () {
    $a = tcmIngredient('tcm-basis-a');
    $b = tcmIngredient('tcm-basis-b');
    $c = tcmIngredient('tcm-basis-c');
    tcmTrait($a, 'on', ['flavor' => 'cam']); // basis flavor (mặc định) → Thổ, 100g
    // basis direct ghi hành Hoả trực tiếp — 300g NHƯNG không được bỏ phiếu.
    tcmTrait($b, 'binh', ['flavor' => 'cam', 'element' => 'hoa', 'element_basis' => 'direct']);
    // basis nature: hàn → Thuỷ theo tính — 200g NHƯNG không được bỏ phiếu.
    tcmTrait($c, 'han', ['element' => 'thuy', 'element_basis' => 'nature']);

    $dish = tcmDish(['cooking_method' => null]);
    tcmLine($dish, $a, 100);
    tcmLine($dish, $b, 300);
    tcmLine($dish, $c, 200);

    $summary = app(TcmAggregator::class)->summarize($dish);

    // Điểm tính vẫn gồm cả 3 dòng: (100×1 + 300×0 + 200×−2)/600 = −0,5.
    expect($summary->nature_score)->toBe(-0.5)
        ->and($summary->coverage_pct)->toBe(100.0)
        // Chỉ phiếu flavor (Thổ 100g) được tính — Hoả/Thuỷ của hai dòng
        // basis khác bị loại khỏi cuộc bỏ phiếu dù khối lượng lớn hơn.
        ->and($summary->dominant_element)->toBe('tho')
        // Nhãn nguyên liệu vẫn được giữ 'high'; trần medium chỉ ở cấp món.
        ->and($summary->confidence)->toBe('medium')
        ->and(IngredientTcmTrait::where('ingredient_id', $a->id)->first()->confidence)->toBe('high');

    // Hiển thị tầng nguyên liệu: element hiệu lực theo đúng basis của dòng.
    $rowB = TcmDisplay::traitRow(IngredientTcmTrait::where('ingredient_id', $b->id)->first());
    $rowC = TcmDisplay::traitRow(IngredientTcmTrait::where('ingredient_id', $c->id)->first());
    expect($rowB['element'])->toBe('hoa')
        ->and($rowB['element_basis'])->toBe('direct')
        ->and($rowC['element'])->toBe('thuy')
        ->and($rowC['element_basis'])->toBe('nature');
});

it('TCM-002: mon khong co nhan nao thi khong co tong hop; recompute idempotent', function () {
    $a = tcmIngredient('tcm-id-a');
    $b = tcmIngredient('tcm-id-b');
    tcmTrait($a, 'luong', ['flavor' => 'toan']);
    $dish = tcmDish(['cooking_method' => 'luoc']);
    tcmLine($dish, $a, 150);
    $plain = tcmDish();
    tcmLine($plain, $b, 100);

    $agg = app(TcmAggregator::class);
    $first = $agg->recomputeAll();
    $snapshot = DishTcmSummary::pluck('nature_score', 'dish_id')->all();
    $second = $agg->recomputeAll();

    expect($first['computed'])->toBe(1)
        ->and($second)->toBe($first)
        ->and(DishTcmSummary::pluck('nature_score', 'dish_id')->all())->toBe($snapshot)
        ->and(DishTcmSummary::where('dish_id', $plain->id)->exists())->toBeFalse();
});

// --- Đường nâng bù + R8 (TCM-000) -----------------------------------------

it('R8: mon nang bu tcm_compensation duoc mien cong 3 nguon cong thuc', function () {
    $ing = tcmIngredient('tcm-r8-a');
    $dish = tcmDish([
        'completeness_level' => 2,
        'level_basis' => 'tcm_compensation',
        'level_note' => 'Fixture: thiếu nguồn công thức thứ 3; đủ cổng thông tin Mức 3.',
    ]);
    tcmLine($dish, $ing, 100);
    DishRecipeSource::create(['dish_id' => $dish->id, 'url' => 'https://example.com/1', 'accessed_at' => '2026-11-01']);
    DishRecipeSource::create(['dish_id' => $dish->id, 'url' => 'https://example.com/2', 'accessed_at' => '2026-11-01']);

    // UPDATE chạm món: trigger R8 phải bỏ qua cho món nâng bù (chỉ 2 nguồn).
    forceCompletenessCheck($dish->id);
    expect(Dish::find($dish->id)->levelLabel())->toBe('Mức 2 (có note)');
});

it('R8: mon Muc 2 thuong van bi chan khi chua du 3 nguon (mien tru khong lo)', function () {
    $ing = tcmIngredient('tcm-r8-b');
    $dish = tcmDish(['completeness_level' => 2]);
    tcmLine($dish, $ing, 100);
    DishRecipeSource::create(['dish_id' => $dish->id, 'url' => 'https://example.com/1', 'accessed_at' => '2026-11-01']);

    expect(fn () => forceCompletenessCheck($dish->id))
        ->toThrow(QueryException::class, 'R8');
});

it('TCM-000: level_basis bat buoc level_note va chi di voi Muc 2 (CHECK tang DB)', function () {
    expect(fn () => tcmDish([
        'completeness_level' => 2,
        'level_basis' => 'tcm_compensation',
        'level_note' => null,
    ]))->toThrow(QueryException::class);

    expect(fn () => tcmDish([
        'completeness_level' => 1,
        'level_basis' => 'tcm_compensation',
        'level_note' => 'Fixture note.',
    ]))->toThrow(QueryException::class);
});

// --- Cổng V20 --------------------------------------------------------------

it('V20: bao do khi mon nang bu thieu tong hop phu >=60 trong DB', function () {
    $ing = tcmIngredient('tcm-v20-a');
    $dish = tcmDish([
        'completeness_level' => 2,
        'level_basis' => 'tcm_compensation',
        'level_note' => 'Fixture: đủ thông tin Mức 3.',
        'cultural_note' => ['text' => 'Ghi chú fixture.', 'sources' => [['ref' => 'Fixture', 'url' => null]]],
    ]);
    tcmLine($dish, $ing, 100);
    tcmSyncDerivedTags($dish);
    // Không có dish_tcm_summary nào ⇒ cổng phủ chưa đạt ⇒ 🔴
    $this->artisan('lacdoi:validate-data')->assertExitCode(1);
});

it('V20: mon nang bu du cong (note + ghi chu van hoa + phu 80) thi validate xanh', function () {
    $ing = tcmIngredient('tcm-v20-b');
    tcmTrait($ing, 'on');
    $other = tcmIngredient('tcm-v20-c');
    $dish = tcmDish([
        'completeness_level' => 2,
        'level_basis' => 'tcm_compensation',
        'level_note' => 'Fixture: đủ thông tin Mức 3.',
        'cultural_note' => ['text' => 'Ghi chú fixture.', 'sources' => [['ref' => 'Fixture', 'url' => null]]],
    ]);
    tcmLine($dish, $ing, 400);
    tcmLine($dish, $other, 100); // coverage 80%
    tcmSyncDerivedTags($dish);
    app(TcmAggregator::class)->summarize($dish);

    $this->artisan('lacdoi:validate-data')->assertExitCode(0);
});

it('V20: bao do khi file nguyen lieu co nhan thieu source_ref (tang file)', function () {
    $path = base_path('data/ingredients/z-tcm000-fixture.json');
    $tags = TaggingRules::tagsFor('z-tcm000-fixture', 'khac');
    file_put_contents($path, json_encode([
        'slug' => 'z-tcm000-fixture',
        'name_vi' => 'Fixture TCM-000 (xoá sau test)',
        'name_en' => null,
        'category' => 'khac',
        'reference_food' => ['mapping_confidence' => 'high', 'alternatives' => []],
        'secondary_sources' => [],
        'is_ready_to_eat' => false,
        'units' => [],
        'tcm_traits' => [
            ['nature' => 'han', 'flavor' => 'cam', 'element' => 'tho', 'confidence' => 'low'],
        ],
        'source' => 'curated',
        'confidence' => 'low',
        'needs_review' => true,
        'tags' => $tags,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    try {
        $this->artisan('lacdoi:validate-data')->assertExitCode(1);
    } finally {
        @unlink($path);
    }
});

it('TCM-001a: V20 chap nhan dong basis nature/direct nhat quan voi he da khai', function () {
    $path = base_path('data/ingredients/z-tcm000-fixture.json');
    $tags = TaggingRules::tagsFor('z-tcm000-fixture', 'khac');
    file_put_contents($path, json_encode([
        'slug' => 'z-tcm000-fixture',
        'name_vi' => 'Fixture TCM-001a (xoá sau test)',
        'name_en' => null,
        'category' => 'khac',
        'reference_food' => ['mapping_confidence' => 'high', 'alternatives' => []],
        'secondary_sources' => [],
        'is_ready_to_eat' => false,
        'units' => [],
        'tcm_traits' => [
            // Theo tính: ôn → Mộc (trình bày §3) — khớp.
            ['nature' => 'on', 'flavor' => null, 'element' => 'moc', 'element_basis' => 'nature', 'source_ref' => 'Fixture basis nature', 'confidence' => 'medium'],
            // Ghi trực tiếp: vị cam thường suy Thổ, nhưng nguồn ghi Hoả —
            // basis direct thì không ép khớp hệ vị.
            ['nature' => 'binh', 'flavor' => 'cam', 'element' => 'hoa', 'element_basis' => 'direct', 'source_ref' => 'Fixture basis direct', 'confidence' => 'low'],
        ],
        'source' => 'curated',
        'confidence' => 'low',
        'needs_review' => true,
        'tags' => $tags,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    try {
        $this->artisan('lacdoi:validate-data')->assertExitCode(0);
    } finally {
        @unlink($path);
    }
});

it('TCM-001a: V20 bao do khi element lech he basis da khai hoac basis sai tu vung', function () {
    $path = base_path('data/ingredients/z-tcm000-fixture.json');
    $tags = TaggingRules::tagsFor('z-tcm000-fixture', 'khac');
    $write = function (array $traits) use ($path, $tags) {
        file_put_contents($path, json_encode([
            'slug' => 'z-tcm000-fixture',
            'name_vi' => 'Fixture TCM-001a (xoá sau test)',
            'name_en' => null,
            'category' => 'khac',
            'reference_food' => ['mapping_confidence' => 'high', 'alternatives' => []],
            'secondary_sources' => [],
            'is_ready_to_eat' => false,
            'units' => [],
            'tcm_traits' => $traits,
            'source' => 'curated',
            'confidence' => 'low',
            'needs_review' => true,
            'tags' => $tags,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    };

    try {
        // basis nature nhưng element không theo ánh xạ tính (ôn → Mộc, ghi Hoả).
        $write([
            ['nature' => 'on', 'flavor' => null, 'element' => 'hoa', 'element_basis' => 'nature', 'source_ref' => 'Fixture lệch tính', 'confidence' => 'low'],
        ]);
        $this->artisan('lacdoi:validate-data')->assertExitCode(1);

        // element_basis ngoài từ vựng.
        $write([
            ['nature' => 'on', 'flavor' => 'tan', 'element' => 'kim', 'element_basis' => 'sai-basis', 'source_ref' => 'Fixture sai basis', 'confidence' => 'low'],
        ]);
        $this->artisan('lacdoi:validate-data')->assertExitCode(1);
    } finally {
        @unlink($path);
    }
});

it('TCM-001a: seed -> export giu element_basis khac mac dinh, dong mac dinh khong ghi thua', function () {
    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);

    $host = Ingredient::orderBy('slug')->firstOrFail();
    $path = base_path("data/ingredients/{$host->slug}.json");
    $originalBytes = (string) file_get_contents($path);

    IngredientTcmTrait::create([
        'ingredient_id' => $host->id,
        'nature' => 'on',
        'flavor' => null,
        'element' => 'moc',
        'element_basis' => 'nature',
        'source_ref' => 'Fixture basis roundtrip TCM-001a',
        'confidence' => 'medium',
    ]);
    IngredientTcmTrait::create([
        'ingredient_id' => $host->id,
        'nature' => 'binh',
        'flavor' => 'cam',
        'element' => 'tho',
        'element_basis' => 'flavor',
        'source_ref' => 'Fixture basis mac dinh TCM-001a',
        'confidence' => 'low',
    ]);

    try {
        $this->artisan('lacdoi:export-data')->assertExitCode(0);
        $exported = json_decode((string) file_get_contents($path), true);
        $byRef = collect($exported['tcm_traits'])->keyBy('source_ref');

        expect($byRef['Fixture basis roundtrip TCM-001a']['element_basis'])->toBe('nature')
            ->and($byRef)->toHaveKey('Fixture basis mac dinh TCM-001a')
            ->and($byRef['Fixture basis mac dinh TCM-001a'])->not->toHaveKey('element_basis');

        // Nhập lại: basis phải dựng lại đúng ở DB.
        IngredientTcmTrait::where('ingredient_id', $host->id)->delete();
        $this->seed(IngredientSeeder::class);
        $fresh = Ingredient::where('slug', $host->slug)->firstOrFail();
        expect($fresh->tcmTraits->firstWhere('source_ref', 'Fixture basis roundtrip TCM-001a')->element_basis)->toBe('nature')
            ->and($fresh->tcmTraits->firstWhere('source_ref', 'Fixture basis mac dinh TCM-001a')->element_basis)->toBe('flavor');
    } finally {
        file_put_contents($path, $originalBytes);
    }
});

it('TCM-001c: V20 chap nhan dong suy dien P-DERIVE guong dung nhan goc (gao-te)', function () {
    $path = base_path('data/ingredients/z-tcm001c-fixture.json');
    $tags = TaggingRules::tagsFor('z-tcm001c-fixture', 'khac');
    file_put_contents($path, json_encode([
        'slug' => 'z-tcm001c-fixture',
        'name_vi' => 'Fixture TCM-001c (xoá sau test)',
        'name_en' => null,
        'category' => 'khac',
        'reference_food' => ['mapping_confidence' => 'high', 'alternatives' => []],
        'secondary_sources' => [],
        'is_ready_to_eat' => false,
        'units' => [],
        'tcm_traits' => [
            // Gương dòng nguồn thật của gao-te (ôn/cam/Thổ, basis flavor).
            ['nature' => 'on', 'flavor' => 'cam', 'element' => 'tho', 'derived_from' => 'gao-te', 'source_ref' => 'Suy diễn từ dạng gốc Gạo tẻ (gao-te) theo chính sách P-DERIVE (TCM-001c). Nhãn gốc: fixture.', 'confidence' => 'low'],
        ],
        'source' => 'curated',
        'confidence' => 'low',
        'needs_review' => true,
        'tags' => $tags,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    try {
        $this->artisan('lacdoi:validate-data')->assertExitCode(0);
    } finally {
        @unlink($path);
    }
});

it('TCM-001c: V20 bao do cho suy dien sai ky luat (tran low, guong, chuoi, hon hop, dau P-DERIVE)', function () {
    $path = base_path('data/ingredients/z-tcm001c-fixture.json');
    $pathOrigin = base_path('data/ingredients/z-tcm001c-origin.json');
    $tags = TaggingRules::tagsFor('z-tcm001c-fixture', 'khac');
    $write = function (array $traits, array $extra = []) use ($path, $tags) {
        file_put_contents($path, json_encode(array_merge([
            'slug' => 'z-tcm001c-fixture',
            'name_vi' => 'Fixture TCM-001c (xoá sau test)',
            'name_en' => null,
            'category' => 'khac',
            'reference_food' => ['mapping_confidence' => 'high', 'alternatives' => []],
            'secondary_sources' => [],
            'is_ready_to_eat' => false,
            'units' => [],
            'tcm_traits' => $traits,
            'source' => 'curated',
            'confidence' => 'low',
            'needs_review' => true,
            'tags' => $tags,
        ], $extra), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    };
    $okRef = 'Suy diễn từ dạng gốc Gạo tẻ (gao-te) theo chính sách P-DERIVE (TCM-001c).';

    try {
        // (a) confidence trên trần low.
        $write([
            ['nature' => 'on', 'flavor' => 'cam', 'element' => 'tho', 'derived_from' => 'gao-te', 'source_ref' => $okRef, 'confidence' => 'medium'],
        ]);
        $this->artisan('lacdoi:validate-data')->assertExitCode(1);

        // (b) không gương dòng gốc (gao-te là ôn, ghi hàn).
        $write([
            ['nature' => 'han', 'flavor' => 'cam', 'element' => 'tho', 'derived_from' => 'gao-te', 'source_ref' => $okRef, 'confidence' => 'low'],
        ]);
        $this->artisan('lacdoi:validate-data')->assertExitCode(1);

        // (c) thiếu dấu P-DERIVE trong source_ref.
        $write([
            ['nature' => 'on', 'flavor' => 'cam', 'element' => 'tho', 'derived_from' => 'gao-te', 'source_ref' => 'Suy diễn từ gạo tẻ.', 'confidence' => 'low'],
        ]);
        $this->artisan('lacdoi:validate-data')->assertExitCode(1);

        // (d) nguyên liệu hỗn hợp (có components) mang nhãn suy diễn.
        $write([
            ['nature' => 'on', 'flavor' => 'cam', 'element' => 'tho', 'derived_from' => 'gao-te', 'source_ref' => $okRef, 'confidence' => 'low'],
        ], ['components' => ['gao-te']]);
        $this->artisan('lacdoi:validate-data')->assertExitCode(1);

        // (e) suy diễn chuỗi: gốc z-tcm001c-origin tự nó chỉ có nhãn suy diễn.
        $tagsOrigin = TaggingRules::tagsFor('z-tcm001c-origin', 'khac');
        file_put_contents($pathOrigin, json_encode([
            'slug' => 'z-tcm001c-origin',
            'name_vi' => 'Fixture goc TCM-001c (xoá sau test)',
            'name_en' => null,
            'category' => 'khac',
            'reference_food' => ['mapping_confidence' => 'high', 'alternatives' => []],
            'secondary_sources' => [],
            'is_ready_to_eat' => false,
            'units' => [],
            'tcm_traits' => [
                ['nature' => 'on', 'flavor' => 'cam', 'element' => 'tho', 'derived_from' => 'gao-te', 'source_ref' => $okRef, 'confidence' => 'low'],
            ],
            'source' => 'curated',
            'confidence' => 'low',
            'needs_review' => true,
            'tags' => $tagsOrigin,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $write([
            ['nature' => 'on', 'flavor' => 'cam', 'element' => 'tho', 'derived_from' => 'z-tcm001c-origin', 'source_ref' => 'Suy diễn từ dạng gốc fixture origin theo chính sách P-DERIVE (TCM-001c).', 'confidence' => 'low'],
        ]);
        $this->artisan('lacdoi:validate-data')->assertExitCode(1);
    } finally {
        @unlink($path);
        @unlink($pathOrigin);
    }
});

it('TCM-001c: seed -> export giu derived_from va DB khoa confidence cua dong suy dien o low', function () {
    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);

    $host = Ingredient::orderBy('slug')->firstOrFail();
    $path = base_path("data/ingredients/{$host->slug}.json");
    $originalBytes = (string) file_get_contents($path);

    IngredientTcmTrait::create([
        'ingredient_id' => $host->id,
        'nature' => 'on',
        'flavor' => 'cam',
        'element' => 'tho',
        'derived_from' => 'gao-te',
        'source_ref' => 'Fixture suy dien P-DERIVE TCM-001c',
        'confidence' => 'low',
    ]);

    try {
        $this->artisan('lacdoi:export-data')->assertExitCode(0);
        $exported = json_decode((string) file_get_contents($path), true);
        $byRef = collect($exported['tcm_traits'])->keyBy('source_ref');
        expect($byRef['Fixture suy dien P-DERIVE TCM-001c']['derived_from'])->toBe('gao-te');

        IngredientTcmTrait::where('ingredient_id', $host->id)->delete();
        $this->seed(IngredientSeeder::class);
        $fresh = Ingredient::where('slug', $host->slug)->firstOrFail();
        expect($fresh->tcmTraits->firstWhere('source_ref', 'Fixture suy dien P-DERIVE TCM-001c')->derived_from)->toBe('gao-te');

        // CHECK ở DB: suy diễn mà confidence khác low thì không ghi được.
        expect(fn () => IngredientTcmTrait::create([
            'ingredient_id' => $host->id,
            'nature' => 'on',
            'flavor' => 'cam',
            'element' => 'tho',
            'derived_from' => 'gao-te',
            'source_ref' => 'Fixture suy dien sai tran TCM-001c',
            'confidence' => 'high',
        ]))->toThrow(QueryException::class);
    } finally {
        IngredientTcmTrait::where('ingredient_id', $host->id)
            ->whereIn('source_ref', ['Fixture suy dien P-DERIVE TCM-001c', 'Fixture suy dien sai tran TCM-001c'])
            ->delete();
        file_put_contents($path, $originalBytes);
    }
});

it('V20: bao do khi components tro toi slug khong ton tai (tang file)', function () {
    $path = base_path('data/ingredients/z-tcm000-fixture.json');
    $tags = TaggingRules::tagsFor('z-tcm000-fixture', 'khac');
    file_put_contents($path, json_encode([
        'slug' => 'z-tcm000-fixture',
        'name_vi' => 'Fixture TCM-000 (xoá sau test)',
        'name_en' => null,
        'category' => 'khac',
        'reference_food' => ['mapping_confidence' => 'high', 'alternatives' => []],
        'secondary_sources' => [],
        'is_ready_to_eat' => false,
        'units' => [],
        'components' => ['slug-khong-he-ton-tai'],
        'source' => 'curated',
        'confidence' => 'low',
        'needs_review' => true,
        'tags' => $tags,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    try {
        $this->artisan('lacdoi:validate-data')->assertExitCode(1);
    } finally {
        @unlink($path);
    }
});

// --- Vòng seed → export cho tcm_traits + components ------------------------

it('TCM-000: seed -> export giu nguyen khoi tcm_traits va components (diem co dinh)', function () {
    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);

    // Host phải bắt đầu KHÔNG có nhãn thật nào: khi kho lớn dần, nguyên liệu
    // đầu tiên theo slug có thể đã mang nhãn (vd bach-tuoc từ TCM-001c đợt 2)
    // và làm sai tiền đề "export ra đúng 1 dòng fixture" của test này.
    $host = Ingredient::doesntHave('tcmTraits')->orderBy('slug')->firstOrFail();
    $component = Ingredient::where('id', '<>', $host->id)->orderBy('slug')->firstOrFail();
    $path = base_path("data/ingredients/{$host->slug}.json");
    $originalBytes = (string) file_get_contents($path);

    IngredientTcmTrait::create([
        'ingredient_id' => $host->id,
        'nature' => 'binh',
        'flavor' => 'cam',
        'element' => 'tho',
        'source_ref' => 'Fixture roundtrip TCM-000',
        'confidence' => 'medium',
        'usage_restricted' => true,
        'usage_note' => 'Không được dùng — fixture kiểm vòng export.',
    ]);
    $host->components()->sync([$component->id]);

    try {
        $this->artisan('lacdoi:export-data')->assertExitCode(0);
        $exported = json_decode((string) file_get_contents($path), true);

        expect($exported['tcm_traits'])->toHaveCount(1)
            ->and($exported['tcm_traits'][0]['nature'])->toBe('binh')
            ->and($exported['tcm_traits'][0]['source_ref'])->toBe('Fixture roundtrip TCM-000')
            ->and($exported['tcm_traits'][0]['usage_restricted'])->toBeTrue()
            ->and($exported['components'])->toBe([$component->slug]);

        // Nhập lại từ chính file vừa xuất: nhãn + liên kết phải dựng lại đủ.
        IngredientTcmTrait::where('ingredient_id', $host->id)->delete();
        $host->components()->sync([]);
        $this->seed(IngredientSeeder::class);

        $fresh = Ingredient::where('slug', $host->slug)->firstOrFail();
        expect($fresh->tcmTraits)->toHaveCount(1)
            ->and($fresh->tcmTraits->first()->source_ref)->toBe('Fixture roundtrip TCM-000')
            ->and($fresh->components->pluck('slug')->all())->toBe([$component->slug]);

        // Xuất lần nữa từ trạng thái vừa nhập lại: từng byte phải như lần đầu.
        $firstExportBytes = (string) file_get_contents($path);
        $this->artisan('lacdoi:export-data')->assertExitCode(0);
        expect((string) file_get_contents($path))->toBe($firstExportBytes);
    } finally {
        file_put_contents($path, $originalBytes);
    }
});

// --- API hiển thị -----------------------------------------------------------

it('TCM-003: API nguyen lieu tra khoi tinh vi + lien ket thanh phan; mon duoi nguong thi tcm=null', function () {
    $ing = tcmIngredient('tcm-api-a', ['name_vi' => 'Nguyên liệu API']);
    tcmTrait($ing, 'han', ['flavor' => 'toan']);
    $host = tcmIngredient('tcm-api-host', ['name_vi' => 'Nguyên liệu hỗn hợp']);
    $host->components()->sync([$ing->id]);

    $this->getJson("/api/ingredients/{$host->slug}")
        ->assertOk()
        ->assertJsonPath('ingredient.component_links.0.slug', 'tcm-api-a')
        ->assertJsonPath('ingredient.tcm', null);

    $this->getJson("/api/ingredients/{$ing->slug}")
        ->assertOk()
        ->assertJsonPath('ingredient.tcm.traits.0.nature', 'han')
        ->assertJsonPath('ingredient.tcm.traits.0.element', 'moc')
        ->assertJsonPath('ingredient.tcm.has_conflicts', false);

    $dish = tcmDish();
    tcmLine($dish, $ing, 50);
    $this->getJson("/api/dishes/{$dish->slug}")
        ->assertOk()
        ->assertJsonPath('tcm', null)
        ->assertJsonPath('dish.level_label', 'Mức 1');
});
