<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Http\Controllers\Controller;
use App\Models\MealLog;
use App\Models\UserProfile;
use App\Profile\TdeeCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Hồ sơ thể trạng + TDEE (USR-002) và nhật ký bữa ăn (USR-003). */
class ProfileController extends Controller
{
    public function show(Request $request, TdeeCalculator $calc): JsonResponse
    {
        $profile = $request->user()->profile;

        return response()->json([
            'profile' => $profile,
            'tdee' => $profile === null ? null : $calc->for($profile),
        ]);
    }

    public function update(Request $request, TdeeCalculator $calc): JsonResponse
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
        ]);
        $profile = UserProfile::updateOrCreate(['user_id' => $request->user()->id], $data);

        return response()->json(['profile' => $profile, 'tdee' => $calc->for($profile)]);
    }

    public function mealLogs(Request $request): JsonResponse
    {
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
