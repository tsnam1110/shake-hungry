<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * FOOD-COMBINE-001 — một cặp "kết hợp cần lưu ý" (nguồn sự thật là file
 * trong data/combinations/, bảng này dựng lại từ file).
 *
 * NGUYÊN TẮC ĐỎ (hồ sơ nghiên cứu 2026-11-02 §7.5): đây là lớp THÔNG TIN
 * thuần — không lọc cứng, không chặn Lắc, không loại/trừ điểm món, không
 * dùng khung cảnh báo dị ứng. Tầng hiển thị suy ra từ evidence_class bằng
 * hàm thuần dưới đây, không lưu tay ở dữ liệu.
 */
class Combination extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'side_a_members' => 'array',
            'side_b_members' => 'array',
            'traditional_source' => 'array',
            'sources' => 'array',
            'needs_review' => 'boolean',
        ];
    }

    /** Tầng hiển thị: E1 → 1 "Tránh kết hợp", E2 → 2, E3 → 3 (dân gian). */
    public function tier(): int
    {
        return match ($this->evidence_class) {
            'E1' => 1,
            'E2' => 2,
            default => 3,
        };
    }

    public function tierLabel(): string
    {
        return match ($this->tier()) {
            1 => 'Tránh kết hợp',
            2 => 'Không nên kết hợp',
            default => 'Kiêng kỵ theo dân gian / đông y',
        };
    }

    public function scienceLabel(): string
    {
        return match ($this->science_status) {
            'supported' => 'Đã xác nhận trong y văn / nhãn thuốc chính thống',
            'conditional' => 'Có thật nhưng chỉ trong điều kiện cụ thể dưới đây',
            'refuted' => 'Đã bị thực nghiệm bác bỏ — không cần kiêng',
            default => 'Chưa có bằng chứng khoa học — chỉ là quan niệm truyền thống',
        };
    }

    /** @return array{type: string, ref: string, label: ?string, members: ?array} */
    public function side(string $which): array
    {
        return [
            'type' => $this->{"side_{$which}_type"},
            'ref' => $this->{"side_{$which}_ref"},
            'label' => $this->{"side_{$which}_label"},
            'members' => $this->{"side_{$which}_members"},
        ];
    }

    /** Một phía có "chạm" tới slug kho này không (nguyên liệu/quả/nhóm). */
    public static function sideMatchesSlug(array $side, string $slug): bool
    {
        return match ($side['type']) {
            'ingredient', 'fruit' => $side['ref'] === $slug,
            'food_group' => in_array($slug, $side['members'] ?? [], true),
            default => false, // drug_group không gắn vào trang thực phẩm theo slug
        };
    }

    /** Phía này có phải phía thực phẩm (không phải thuốc) không. */
    public static function sideIsFood(array $side): bool
    {
        return $side['type'] !== 'drug_group';
    }
}
