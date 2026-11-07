<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Http\Controllers\Controller;
use App\Models\MealLog;
use App\Models\UserProfile;
use App\Profile\TdeeCalculator;
use App\Profile\WeekTargetSuggester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Hồ sơ thể trạng + TDEE (USR-002) và nhật ký bữa ăn (USR-003). */
class ProfileController extends Controller
{
    public function show(Request $request, TdeeCalculator $calc, WeekTargetSuggester $suggester): JsonResponse
    {
        $profile = $request->user()->profile;

        return response()->json([
            'profile' => $profile,
            'tdee' => $profile === null ? null : $calc->for($profile),
            'week_target_suggestion' => $this->weekTargetSuggestion($profile, $suggester),
        ]);
    }

    public function update(Request $request, TdeeCalculator $calc, WeekTargetSuggester $suggester): JsonResponse
    {
        $data = $request->validate([
            'height_cm' => ['nullable', 'numeric', 'min:80', 'max:250'],
            'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:400'],
            'birth_year' => ['nullable', 'integer', 'min:1930', 'max:'.(int) date('Y')],
            'sex' => ['nullable', Rule::in(['male', 'female', 'unspecified'])],
            'activity_level' => ['nullable', Rule::in(['nhe', 'vua', 'nang'])],
            'diet_type' => ['nullable', Rule::in(['chay', 'chay-truong', 'low-carb'])],
            'excluded_ingredients' => ['nullable', 'array'],
            'excluded_ingredients.*' => ['string', 'max:64'],
            'preferred_region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'target_kcal_per_meal' => ['nullable', 'integer', 'min:100', 'max:2000'],
            // REC-003: chế độ lắc + số món mâm cơm lưu trong hồ sơ.
            'shake_mode' => ['nullable', Rule::in(['eat_out', 'home'])],
            'tray_dish_count' => ['nullable', 'integer', 'min:1', 'max:4'],
            // REC-004: tuỳ chọn "Bữa cơm bình dân" lưu trong hồ sơ.
            'tray_binh_dan' => ['sometimes', 'boolean'],
            // TCM-003: tuỳ chọn hiển thị lớp tính vị (mặc định bật theo quyết
            // định chủ dự án 2026-11-01; chỉ là hiển thị, không đụng engine).
            'show_tcm' => ['sometimes', 'boolean'],
            // Quy định cocktail, bước 2/4 (2026-11-04): lọc tuổi cho đồ
            // uống có cồn — người dùng tự bật, mặc định tắt, không suy
            // từ birth_year. Hiện chỉ lưu cờ; Lắc vẫn chỉ gợi ý Dish.
            'allow_alcohol' => ['sometimes', 'boolean'],
            // WEEK-001, Phần 2.1, Task 2: mục tiêu tuần (đã duyệt Task 1).
            // goal chỉ lưu, chưa dùng để suy kcal vì tỉ lệ điều chỉnh kcal
            // theo mục tiêu chưa được chốt; gợi ý protein tính từ
            // target_kcal_per_day đã nhập, không tự suy kcal từ TDEE.
            'goal' => ['nullable', Rule::in(['giam', 'duy-tri', 'tang'])],
            'target_protein_g_per_day' => ['nullable', 'integer', 'min:20', 'max:500'],
            'target_kcal_per_day' => ['nullable', 'integer', 'min:800', 'max:6000'],
        ]);
        $profile = UserProfile::updateOrCreate(['user_id' => $request->user()->id], $data);

        return response()->json([
            'profile' => $profile,
            'tdee' => $calc->for($profile),
            'week_target_suggestion' => $this->weekTargetSuggestion($profile, $suggester),
        ]);
    }

    public function mealLogs(Request $request): JsonResponse
    {
        // Chế độ khoảng ngày (UI-015, trang Nhật ký): trả tổng theo từng ngày
        // để vẽ tổng quan tuần. Không có from/to thì giữ hành vi theo ngày cũ.
        if ($request->filled('from') && $request->filled('to')) {
            $from = $request->query('from');
            $to = $request->query('to');
            $logs = MealLog::with(['dish.nutrition'])
                ->where('user_id', $request->user()->id)
                ->whereDate('eaten_at', '>=', $from)
                ->whereDate('eaten_at', '<=', $to)
                ->get()
                ->groupBy(fn (MealLog $l) => $l->eaten_at->toDateString());
            $days = [];
            for ($d = strtotime($from); $d <= strtotime($to); $d += 86400) {
                $key = date('Y-m-d', $d);
                $days[$key] = $this->summarize($logs->get($key, collect()));
            }

            return response()->json(['from' => $from, 'to' => $to, 'days' => $days]);
        }
        $date = $request->query('date', now()->toDateString());
        $logs = MealLog::with(['dish.names' => fn ($q) => $q->where('is_primary', true), 'dish.nutrition'])
            ->where('user_id', $request->user()->id)
            ->whereDate('eaten_at', $date)
            ->orderBy('eaten_at')
            ->get();

        return response()->json(['date' => $date, 'logs' => $logs, 'summary' => $this->summarize($logs)]);
    }

    public function storeMealLog(Request $request): JsonResponse
    {
        $data = $request->validate([
            'dish_id' => ['required', 'integer', 'exists:dishes,id'],
            'slot' => ['required', Rule::in(['sang', 'trua', 'chieu', 'toi', 'an-vat'])],
            'source' => ['nullable', Rule::in(['shake_accepted', 'manual'])],
            'eaten_at' => ['nullable', 'date'],
        ]);
        $log = MealLog::create([
            ...$data,
            'user_id' => $request->user()->id,
            'source' => $data['source'] ?? 'manual',
            'eaten_at' => $data['eaten_at'] ?? now(),
        ]);

        return response()->json($log, 201);
    }

    public function updateMealLog(Request $request, MealLog $mealLog): JsonResponse
    {
        abort_unless($mealLog->user_id === $request->user()->id, 404);
        $data = $request->validate([
            'slot' => ['nullable', Rule::in(['sang', 'trua', 'chieu', 'toi', 'an-vat'])],
            'eaten_at' => ['nullable', 'date'],
        ]);
        $mealLog->update($data);

        return response()->json($mealLog);
    }

    public function destroyMealLog(Request $request, MealLog $mealLog): JsonResponse
    {
        abort_unless($mealLog->user_id === $request->user()->id, 404);
        $mealLog->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Gợi ý protein cho tuần ăn: chỉ tính khi hồ sơ đã có chiều cao VÀ
     * người dùng đã nhập target_kcal_per_day. Thiếu một trong hai thì
     * trả null (QT-9), không tự lấy TDEE hay cân nặng thật làm thay thế.
     */
    private function weekTargetSuggestion(?UserProfile $profile, WeekTargetSuggester $suggester): ?array
    {
        if ($profile === null) {
            return null;
        }

        return $suggester->suggest($profile, $profile->target_kcal_per_day);
    }

    /**
     * Tổng calo ngày dạng khoảng; món không có số dinh dưỡng (chưa đủ dữ liệu)
     * bị bỏ qua VÀ được đếm rõ — DoD USR-003.
     */
    private function summarize($logs): array
    {
        $min = 0.0;
        $max = 0.0;
        $unknown = 0;
        foreach ($logs as $log) {
            $n = $log->dish?->nutrition;
            if ($n === null || $n->confidence === 'unknown') {
                $unknown++;

                continue;
            }
            $min += (float) $n->kcal_min;
            $max += (float) $n->kcal_max;
        }

        return [
            'kcal_min' => round($min), 'kcal_max' => round($max),
            'dishes_counted' => $logs->count() - $unknown,
            'dishes_unknown' => $unknown,
        ];
    }
}
