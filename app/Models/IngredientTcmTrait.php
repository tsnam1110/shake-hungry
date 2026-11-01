<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Nhãn tính vị của một nguyên liệu theo MỘT nguồn (QT-6, GĐ6).
 * Một nguyên liệu có nhiều dòng khi các nguồn mâu thuẫn — mâu thuẫn được
 * giữ và hiển thị, không ép chọn. `source_ref` bắt buộc (R5).
 * `element_basis` (TCM-001a): căn cứ của `element` — 'flavor' (suy từ vị,
 * mặc định), 'nature' (nguồn trình bày hành theo tính) hoặc 'direct'
 * (nguồn ghi hành trực tiếp). Chỉ dòng basis='flavor' bỏ phiếu hành chủ
 * đạo khi tổng hợp món.
 * `usage_restricted`/`usage_note`: cờ theo chính sách lưu trữ nguồn hạn chế
 * (kế hoạch Mức 3 §2) — dữ liệu vẫn lưu trong thư viện, ghi chú "không được
 * dùng" đi kèm ở mọi nơi dữ liệu xuất hiện.
 * `derived_from` (TCM-001c, chính sách P-DERIVE): slug của nguyên liệu gốc
 * khi dòng này là nhãn SUY DIỄN cho dạng chế biến cơ học đơn thành phần —
 * null nghĩa là nhãn nguồn trực tiếp. Dòng suy diễn gương đúng một dòng
 * nguồn thật của gốc, confidence khoá ở 'low', cấm chuỗi và cấm cho
 * nguyên liệu hỗn hợp; V20 kiểm toàn bộ các điều kiện này.
 */
class IngredientTcmTrait extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'usage_restricted' => 'boolean',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
