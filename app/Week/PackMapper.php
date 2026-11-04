<?php

declare(strict_types=1);

namespace App\Week;

/**
 * Quy đổi đi chợ tuần sang gói từ snapshot giá 04/11 (Task F1).
 *
 * Nguồn: app/Week/pack-options-2026-11-04.json, sinh bằng script
 * ~/workspace/tuan-an-anh-xa-goi-f1.py từ snapshot 04/11 (BHX + WinMart,
 * tách kênh, không cộng chéo). Chỉ hiển thị ánh xạ 'khớp'; ánh xạ
 * 'ứng viên' chưa xác nhận thì đếm riêng (unverifiedCount) để ghi chú,
 * không hiển thị thành lựa chọn.
 *
 * Cách tính: gói khối lượng dùng tổng gram; gói dung tích dùng tổng ml
 * (gram / density, hoặc cộng dồn unit ml/l); gói đếm dùng tổng số quả/
 * trái/con từ unit_entries. Số gói luôn làm tròn LÊN. Giá/gói, kênh và
 * ngày snapshot đi kèm từng lựa chọn; khuyến mãi có điều kiện không
 * trừ vào giá suy ra. Tổng tiền tuần KHÔNG tính ở đây (budget_vnd vẫn
 * chỉ là mục tiêu mềm do người dùng nhập).
 */
final class PackMapper
{
    /** @var array<string, array<int, array<string, mixed>>>|null */
    private static ?array $options = null;

    /** @return array<string, array<int, array<string, mixed>>> */
    private static function all(): array
    {
        if (self::$options !== null) {
            return self::$options;
        }
        $raw = @file_get_contents(__DIR__.'/pack-options-2026-11-04.json');
        $data = $raw !== false ? json_decode($raw, true) : null;
        self::$options = is_array($data) ? $data : [];
        unset(self::$options['_meta']);

        return self::$options;
    }

    /**
     * Chuẩn hoá đơn vị đếm về dạng không dấu như trong JSON
     * ('quả' → 'qua', 'trái' → 'trai'), để khớp unit_entries có dấu.
     */
    private static function normalizeCountUnit(string $unit): string
    {
        $unit = mb_strtolower(trim($unit));

        return match ($unit) {
            'quả' => 'qua',
            'trái' => 'trai',
            default => $unit,
        };
    }

    /** Số lựa chọn ứng viên (chưa xác nhận ánh xạ) của một nguyên liệu. */
    public static function unverifiedCount(string $slug): int
    {
        $count = 0;
        foreach (self::all()[$slug] ?? [] as $opt) {
            if (($opt['confidence'] ?? null) !== 'khớp') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param array<int, array{unit_name: string, quantity: float}> $unitEntries
     * @return array<int, array<string, mixed>>
     */
    public static function optionsFor(string $slug, ?float $totalGrams, ?float $density, array $unitEntries): array
    {
        $needGrams = $totalGrams ?? 0.0;
        $needMl = ($totalGrams !== null && $density !== null && $density > 0)
            ? $totalGrams / $density
            : 0.0;
        $needCount = [];
        $hasNeed = $totalGrams !== null && $totalGrams > 0;
        foreach ($unitEntries as $entry) {
            $unit = mb_strtolower(trim((string) ($entry['unit_name'] ?? '')));
            $qty = (float) ($entry['quantity'] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            $hasNeed = true;
            if ($unit === 'g') {
                $needGrams += $qty;
            } elseif ($unit === 'kg') {
                $needGrams += $qty * 1000;
            } elseif ($unit === 'ml') {
                $needMl += $qty;
            } elseif ($unit === 'l') {
                $needMl += $qty * 1000;
            } elseif (in_array($unit, ['quả', 'trái', 'con'], true)) {
                $key = self::normalizeCountUnit($unit);
                $needCount[$key] = ($needCount[$key] ?? 0.0) + $qty;
            }
        }
        if (! $hasNeed) {
            return [];
        }

        $out = [];
        foreach (self::all()[$slug] ?? [] as $opt) {
            // Ứng viên: chưa hiển thị thành lựa chọn.
            if (($opt['confidence'] ?? null) !== 'khớp') {
                continue;
            }
            $packs = null;
            if (isset($opt['pack_grams'])) {
                $packs = $needGrams > 0 ? (int) ceil($needGrams / (float) $opt['pack_grams']) : null;
            } elseif (isset($opt['pack_ml'])) {
                $packs = $needMl > 0 ? (int) ceil($needMl / (float) $opt['pack_ml']) : null;
            } elseif (isset($opt['pack_count'])) {
                $need = $needCount[self::normalizeCountUnit($opt['pack_count_unit'] ?? 'quả')] ?? 0.0;
                $packs = $need > 0 ? (int) ceil($need / (float) $opt['pack_count']) : null;
            }
            $out[] = array_merge($opt, [
                'packs_needed' => $packs,
                'total_cost_vnd' => $packs !== null ? $packs * (int) $opt['price_vnd'] : null,
            ]);
        }
        // BHX trước, rồi theo đơn giá tăng dần — chỉ là thứ tự hiển thị,
        // mọi lựa chọn vẫn hiện đủ, không chốt ngầm một gói.
        usort($out, fn ($a, $b) => [
            $a['channel'] === 'BHX' ? 0 : 1, $a['unit_price_vnd'] ?? PHP_INT_MAX,
        ] <=> [
            $b['channel'] === 'BHX' ? 0 : 1, $b['unit_price_vnd'] ?? PHP_INT_MAX,
        ]);

        return $out;
    }
}
