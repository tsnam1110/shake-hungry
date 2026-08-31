<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Dựng lại toàn bộ database từ data/ — file là nguồn sự thật (QT-3).
     * Thứ tự: regions → ingredients → dishes (món tham chiếu nguyên liệu và vùng).
     */
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            ReferenceFoodSeeder::class,
            CookingYieldSeeder::class,
            FnddsSeeder::class,
            IngredientSeeder::class,
            DishSeeder::class,
        ]);
    }
}
