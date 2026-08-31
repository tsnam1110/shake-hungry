<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use RuntimeException;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('data/reference/regions-vn.json');
        if (! is_file($path)) {
            throw new RuntimeException("Thiếu file {$path} — cây regions là dữ liệu bắt buộc (DATA-001).");
        }

        $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        foreach ($payload['regions'] as $row) {
            $parentId = null;
            if ($row['parent_code'] !== null) {
                $parent = Region::where('code', $row['parent_code'])->first();
                if ($parent === null) {
                    throw new RuntimeException("regions-vn.json: parent_code '{$row['parent_code']}' chưa tồn tại — file phải liệt kê cha trước con.");
                }
                $parentId = $parent->id;
            }

            Region::updateOrCreate(
                ['code' => $row['code']],
                [
                    'parent_id' => $parentId,
                    'country_code' => $row['country_code'],
                    'name' => $row['name'],
                    'level' => $row['level'],
                ],
            );
        }
    }
}
