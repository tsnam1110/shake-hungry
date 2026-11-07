<?php

declare(strict_types=1);

use Database\Seeders\DishSeeder;
use Database\Seeders\FruitSeeder;
use Database\Seeders\IngredientSeeder;
use Database\Seeders\RegionSeeder;
use Illuminate\Support\Facades\File;

// Export là thao tác ghi file: mọi test phải chạy trên bản sao data riêng.
beforeEach(function () {
    $this->exportSourceRoot = base_path();
    $this->exportSandbox = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lacdoi-export-'.bin2hex(random_bytes(12));
    File::ensureDirectoryExists($this->exportSandbox.'/data');
    foreach (File::allFiles(base_path('data')) as $file) {
        if (! in_array($file->getExtension(), ['json', 'csv'], true)) {
            continue;
        }
        $target = $this->exportSandbox.'/data/'.$file->getRelativePathname();
        File::ensureDirectoryExists(dirname($target));
        File::copy($file->getPathname(), $target);
    }
    $this->app->setBasePath($this->exportSandbox);
    $this->app->useStoragePath($this->exportSourceRoot.'/storage');
    $this->app->useDatabasePath($this->exportSourceRoot.'/database');
});

afterEach(function () {
    $this->app->setBasePath($this->exportSourceRoot);
    $target = realpath($this->exportSandbox);
    $allowed = realpath(sys_get_temp_dir()).DIRECTORY_SEPARATOR.'lacdoi-export-';
    if ($target !== false && str_starts_with($target, $allowed)) {
        File::deleteDirectory($target);
    }
});

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

/**
 * Hash toàn bộ cây data (mọi file .json) theo đường dẫn tương đối — dùng cho cổng
 * điểm cố định TOÀN CỤC (EXPORT-STABILITY): seed từ data/ rồi export phải
 * cho ra đúng từng byte trên mọi file (món + nguyên liệu + quả + index).
 *
 * @return array<string, string>
 */
function hashAllDataJson(): array
{
    $hashes = [];
    $base = base_path('data');
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.json')) {
            $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($base) + 1));
            $hashes[$rel] = hash_file('sha256', $file->getPathname());
        }
    }
    ksort($hashes);

    return $hashes;
}

it('vong khep kin toan cuc: seed tu data roi export khong doi mot byte nao tren toan cay data', function () {
    $before = hashAllDataJson();

    $this->seed(RegionSeeder::class);
    $this->seed(IngredientSeeder::class);
    $this->seed(DishSeeder::class);
    $this->seed(FruitSeeder::class);
    $this->artisan('lacdoi:export-data')->assertExitCode(0);

    // Khôngunset _index.json nào: cả hai index đều là file máy sinh đã nằm
    // trong repo và phải tự khớp sau export (điểm cố định gồm cả index).
    expect(hashAllDataJson())->toBe($before);
});
