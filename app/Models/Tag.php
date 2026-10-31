<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'dish_tags');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_tags');
    }

    /**
     * Lấy (tạo nếu chưa có) các Tag theo slug trong ĐĂNG KÝ config/taxonomy.php.
     * Slug lạ ngoài đăng ký = lỗi dữ liệu — ném ngoại lệ để seeder dừng ngay
     * thay vì nạp nhãn tự do (TAXONOMY.md: từ vựng có kiểm soát).
     *
     * @param  list<string>  $slugs
     * @return list<int>
     */
    public static function idsFor(array $slugs): array
    {
        $registry = config('taxonomy.tags', []);
        $ids = [];
        foreach ($slugs as $slug) {
            if (! isset($registry[$slug])) {
                throw new \RuntimeException("Nhãn '{$slug}' không có trong đăng ký config/taxonomy.php");
            }
            $ids[] = self::firstOrCreate(['slug' => $slug], ['name' => $registry[$slug]['name']])->id;
        }

        return $ids;
    }
}
