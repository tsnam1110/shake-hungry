<?php

declare(strict_types=1);

use App\Models\Dish;
use App\Taxonomy\AllergenDisplay;
use Database\Seeders\DishSeeder;
use Database\Seeders\IngredientSeeder;
use Database\Seeders\RegionSeeder;

/**
 * QD-ALLERGEN (chủ dự án duyệt 2026-11-02) — tầng HIỂN THỊ dị ứng:
 *  - QD-01 = B: nhóm cá tách nhãn hiển thị "Cá" / "Nước mắm"; cờ và lọc
 *    cứng KHÔNG đổi (AllergenExclusionTest khoá phần lọc).
 *  - QD-02 = B: cờ chỉ sinh từ dòng nguyên liệu tuỳ chọn → certainty
 *    "possible" (hiển thị "có thể có"); lọc cứng vẫn loại món như cũ.
 *  - Bất biến nền: tập nhóm hiển thị của mọi món BẰNG tập cờ dị ứng của
 *    món (tầng hiển thị không được tự thêm/bớt nhóm so với tầng lọc).
 */

function displayRow(string $slug, array $tags, bool $optional = false, array $components = []): array
{
    return ['slug' => $slug, 'tags' => $tags, 'components' => $components, 'is_optional' => $optional];
}

/** @return array<string, array{group: string, label: string, certainty: string}> */
function displayByGroup(array $entries): array
{
    $byGroup = [];
    foreach ($entries as $entry) {
        $byGroup[$entry['group']] = $entry;
    }

    return $byGroup;
}

it('QD-01: chi co mam ca len men thi nhan hien thi la Nuoc mam', function () {
    $entries = AllergenDisplay::fromRows([
        displayRow('nuoc-mam-truyen-thong', ['allergen:ca']),
        displayRow('gao', []),
    ]);
    $byGroup = displayByGroup($entries);

    expect($byGroup['ca']['label'])->toBe('Nước mắm')
        ->and($byGroup['ca']['certainty'])->toBe('definite');
});

it('QD-01: co ca that thi nhan hien thi la Ca, ke ca co kem nuoc mam', function () {
    $entries = AllergenDisplay::fromRows([
        displayRow('ca-loc', ['allergen:ca']),
        displayRow('nuoc-mam-cong-nghiep', ['allergen:ca']),
    ]);
    $byGroup = displayByGroup($entries);

    expect($byGroup['ca']['label'])->toBe('Cá');
});

it('QD-01: gio/cha ca la ca that (ngoai danh sach mam ca)', function () {
    $entries = AllergenDisplay::fromRows([
        displayRow('gio-lua', ['allergen:ca']),
        displayRow('mam-nem', ['allergen:ca']),
    ]);
    $byGroup = displayByGroup($entries);

    expect($byGroup['ca']['label'])->toBe('Cá');
});

it('QD-01: ca qua thanh phan long nhau van duoc phan loai dung nguon', function () {
    // Chế phẩm không tự mang cờ cá, cờ đến từ thành phần mắm cá bên trong.
    $entries = AllergenDisplay::fromRows([
        displayRow('che-pham-x', [], false, [
            displayRow('nuoc-mam-truyen-thong', ['allergen:ca']),
        ]),
    ]);
    $byGroup = displayByGroup($entries);

    expect($byGroup['ca']['label'])->toBe('Nước mắm');
});

it('QD-02: co chi tu dong tuy chon thi certainty la possible', function () {
    $entries = AllergenDisplay::fromRows([
        displayRow('trung-ga', ['allergen:trung'], true),
        displayRow('gao', []),
    ]);
    $byGroup = displayByGroup($entries);

    expect($byGroup['trung']['certainty'])->toBe('possible');
});

it('QD-02: chi can mot dong bat buoc dong gop thi certainty la definite', function () {
    $entries = AllergenDisplay::fromRows([
        displayRow('trung-ga', ['allergen:trung'], true),
        displayRow('trung-vit', ['allergen:trung'], false),
    ]);
    $byGroup = displayByGroup($entries);

    expect($byGroup['trung']['certainty'])->toBe('definite');
});

it('QD-02: dong tuy chon qua thanh phan long nhau van la possible', function () {
    $entries = AllergenDisplay::fromRows([
        displayRow('che-pham-y', [], true, [
            displayRow('sua-tuoi', ['allergen:sua']),
        ]),
    ]);
    $byGroup = displayByGroup($entries);

    expect($byGroup['sua']['certainty'])->toBe('possible');
});

it('QD-ALLERGEN: tap nhom hien thi bang tap co di ung cua mon tren toan kho', function () {
    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);
    $this->seed(DishSeeder::class);

    $mismatch = [];
    foreach (Dish::with('tags')->get() as $dish) {
        $tagGroups = $dish->tags->pluck('slug')
            ->filter(fn (string $s) => str_starts_with($s, 'allergen:'))
            ->map(fn (string $s) => substr($s, strlen('allergen:')))
            ->sort()->values()->all();
        $displayGroups = collect(AllergenDisplay::forDish($dish))
            ->pluck('group')->sort()->values()->all();
        if ($tagGroups !== $displayGroups) {
            $mismatch[] = $dish->slug;
        }
    }

    expect($mismatch)->toBe([]);
});

it('QD-01: census hien thi tren kho that — hai nhan Ca va Nuoc mam cung ton tai', function () {
    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);
    $this->seed(DishSeeder::class);

    $labels = ['Cá' => 0, 'Nước mắm' => 0];
    foreach (Dish::all() as $dish) {
        foreach (AllergenDisplay::forDish($dish) as $entry) {
            if ($entry['group'] === 'ca' && isset($labels[$entry['label']])) {
                $labels[$entry['label']]++;
            }
        }
    }

    expect($labels['Nước mắm'])->toBeGreaterThan(0)
        ->and($labels['Cá'])->toBeGreaterThan(0)
        // Tổng hai nhãn = tổng số món mang cờ cá (hiển thị không thêm/bớt món nào)
        ->and($labels['Cá'] + $labels['Nước mắm'])
        ->toBe(Dish::whereHas('tags', fn ($q) => $q->where('slug', 'allergen:ca'))->count());
});
