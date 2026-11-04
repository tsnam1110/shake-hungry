<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Combination;
use App\Models\Drink;
use App\Models\Vegetable;
use Illuminate\Console\Command;

/**
 * Nạp lại 3 module file (kết hợp, rau, đồ uống) từ data/ vào DB.
 *
 * Đây là bước "áp dụng" sau khi sửa file qua API quản trị /admin — thay
 * thế cho `migrate:fresh --seed` mà /quan-tri cũ vẫn dặn, nhưng KHÔNG
 * bao giờ được chạy migrate:fresh từ đây hay từ web: migrate:fresh xóa
 * cả bảng người dùng (tài khoản, yêu thích, phản hồi, tuần ăn).
 *
 * An toàn nhờ updateOrCreate theo slug trong 3 seeder: hàng đã có giữ
 * nguyên id nên khóa ngoại favorites.drink_id / feedback_tickets.drink_id
 * không bị ảnh hưởng; hàng mới trong file thì được thêm. Seeder đồ uống
 * đồng bộ lại drink_media từ trường `media` trong file.
 *
 * Sau lệnh này vẫn chạy `lacdoi:validate-data` để kiểm chứng như mọi
 * thay đổi dữ liệu khác.
 */
class LacdoiReloadModules extends Command
{
    protected $signature = 'lacdoi:reload-modules
        {module? : ket-hop|rau-mua|do-uong (mặc định: cả 3)}';

    protected $description = 'Nạp lại kết hợp/rau/đồ uống từ data/ vào DB mà không đụng bảng người dùng (không migrate:fresh).';

    /** @var array<string, array{seeder: class-string, model: class-string, label: string}> */
    private const array MODULES = [
        'ket-hop' => ['seeder' => \Database\Seeders\CombinationSeeder::class, 'model' => Combination::class, 'label' => 'Kết hợp cần lưu ý'],
        'rau-mua' => ['seeder' => \Database\Seeders\VegetableSeeder::class, 'model' => Vegetable::class, 'label' => 'Rau theo mùa'],
        'do-uong' => ['seeder' => \Database\Seeders\DrinkSeeder::class, 'model' => Drink::class, 'label' => 'Nước uống'],
    ];

    public function handle(): int
    {
        $module = $this->argument('module');
        if ($module !== null && ! isset(self::MODULES[$module])) {
            $this->error("Module không hợp lệ: {$module}. Dùng ket-hop, rau-mua hoặc do-uong, hoặc bỏ trống để nạp cả 3.");

            return self::FAILURE;
        }

        $targets = $module !== null ? [$module => self::MODULES[$module]] : self::MODULES;
        foreach ($targets as $key => $m) {
            $this->call('db:seed', ['--class' => $m['seeder'], '--force' => true]);
            $count = $m['model']::count();
            $this->info("Đã nạp {$m['label']} ({$key}): {$count} hàng trong DB.");
        }
        $this->line('Xong. Chạy `php artisan lacdoi:validate-data` để kiểm chứng trước khi công bố.');

        return self::SUCCESS;
    }
}
