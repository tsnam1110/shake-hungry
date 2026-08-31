<?php

declare(strict_types=1);

use Database\Seeders\IngredientSeeder;
use Database\Seeders\RegionSeeder;

/**
 * DATA-007 DoD + CODING-STANDARDS §6: JSON export phải ỔN ĐỊNH — chạy hai lần
 * liên tiếp cho ra từng byte giống nhau, nếu không sẽ sinh diff giả trên hàng
 * trăm file và không ai review được nữa.
 */
function hashDataDir(): array
{
    $hashes = [];
    foreach (glob(base_path('data/ingredients/*.json')) ?: [] as $file) {
        $hashes[basename($file)] = hash_file('sha256', $file);
    }

    return $hashes;
}

it('xuat hai lan lien tiep cho ket qua giong het tung byte', function () {
    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);

    $this->artisan('lacdoi:export-data')->assertExitCode(0);
    $first = hashDataDir();

    $this->artisan('lacdoi:export-data')->assertExitCode(0);
    $second = hashDataDir();

    expect($second)->toBe($first)
        ->and(count($first))->toBeGreaterThanOrEqual(150);
});

it('vong khep kin: seed tu file roi export lai khong doi noi dung file', function () {
    $before = hashDataDir();

    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);
    $this->artisan('lacdoi:export-data')->assertExitCode(0);

    // _index.json là file máy sinh, có thể xuất hiện lần đầu — so các file nguyên liệu
    $after = hashDataDir();
    unset($before['_index.json'], $after['_index.json']);

    expect($after)->toBe($before);
});
