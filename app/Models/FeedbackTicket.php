<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackTicket extends Model
{
    protected $guarded = [];

    /** Loại phản hồi (UI-018): mã => nhãn hiển thị. */
    public const TYPES = [
        'gop-y' => 'Góp ý',
        'ho-tro' => 'Hỗ trợ',
        'phat-trien' => 'Phát triển',
        'loi' => 'Báo lỗi',
    ];

    /** Trạng thái xử lý: ticket mới luôn là da-ghi-nhan (đã ghi nhận, chờ duyệt). */
    public const STATUSES = [
        'da-ghi-nhan' => 'Đã ghi nhận · Chờ duyệt',
        'da-duyet' => 'Đã duyệt',
        'dang-xu-ly' => 'Đang xử lý',
        'da-xu-ly' => 'Đã xử lý',
        'da-dong' => 'Đã đóng',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    public function fruit(): BelongsTo
    {
        return $this->belongsTo(Fruit::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function drink(): BelongsTo
    {
        return $this->belongsTo(Drink::class);
    }

    /** Tên sản phẩm gắn với ticket (để hiển thị ngữ cảnh), null nếu phản hồi chung. */
    public function contextName(): ?string
    {
        $dishName = $this->dish?->names
            ->where('is_primary', true)
            ->sortBy(fn ($n) => $n->locale === 'vi' ? 0 : 1)
            ->first()?->name;

        return $dishName
            ?? $this->fruit?->ingredient?->name_vi
            ?? $this->ingredient?->name_vi
            ?? $this->drink?->name_vi;
    }
}
