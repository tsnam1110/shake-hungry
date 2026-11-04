<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Http\Controllers\Controller;
use App\Models\WeekPlan;
use App\Week\WeekBuilder;
use App\Week\WeekValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Tuần ăn (Giai đoạn 2, Phần 2.3): API cho tuần của chính người dùng. */
class WeekPlanController extends Controller
{
    /**
     * Tuần ví dụ cho khách (Phần 2.4): 1 ngày mẫu từ ứng viên thật, KHÔNG
     * lưu, KHÔNG cá nhân hoá, KHÔNG có mục tiêu nên không kết luận đạt/thiếu.
     * Chỉ để khách thấy cấu trúc một ngày trong tuần ăn trước khi đăng nhập.
     */
    public function example(WeekBuilder $builder): JsonResponse
    {
        $meals = [];
        foreach (WeekValidator::SLOTS as $slot) {
            $dish = $builder->candidates($slot, null)->first();
            if ($dish === null) {
                continue;
            }
            $dish->loadMissing('names', 'nutrition', 'region');
            $meals[] = [
                'slot' => $slot,
                'dish' => [
                    'id' => $dish->id, 'slug' => $dish->slug, 'name' => $dish->displayName(),
                    'region' => $dish->region?->name,
                    'nutrition' => $dish->nutrition === null ? null : [
                        'kcal_min' => $dish->nutrition->kcal_min, 'kcal_max' => $dish->nutrition->kcal_max,
                        'protein_g' => $dish->nutrition->protein_g,
                    ],
                ],
            ];
        }

        return response()->json(['meals' => $meals, 'note' => 'Ngày ví dụ, không lưu, không cá nhân hoá theo hồ sơ.']);
    }

    public function index(Request $request): JsonResponse
    {
        $plans = WeekPlan::where('user_id', $request->user()->id)
            ->with('days')
            ->orderByDesc('start_date')
            ->get()
            ->map(fn (WeekPlan $p) => $this->presentPlan($p));

        return response()->json(['plans' => $plans]);
    }

    public function show(Request $request, WeekPlan $weekPlan, WeekValidator $validator): JsonResponse
    {
        $this->authorizePlan($request, $weekPlan);
        $weekPlan->load('days.meals.dish.names', 'days.meals.dish.nutrition', 'days.meals.dish.region');
        $validations = [];
        foreach ($weekPlan->days as $day) {
            $validations[] = $validator->validateDay($day, $weekPlan);
        }

        return response()->json([
            'plan' => $this->presentPlan($weekPlan, $validations),
            'days' => $weekPlan->days->map(function ($day) use ($weekPlan, $validator) {
                $validation = $validator->validateDay($day, $weekPlan);

                return [
                    'id' => $day->id, 'date' => $day->date->toDateString(),
                    'validation' => $validation,
                    'meals' => $day->meals->map(fn ($m) => $this->presentMeal($m))->values(),
                ];
            })->values(),
        ]);
    }

    public function store(Request $request, WeekBuilder $builder): JsonResponse
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
        ]);
        $plan = $builder->build($request->user(), $data['start_date']);

        return response()->json([
            'plan' => $this->presentPlan($plan->fresh()->load('days')),
            'days_count' => $plan->days()->count(),
            'meals_count' => $plan->meals()->count(),
        ], 201);
    }

    /** Phần 2.5, Task 1 — đổi một bữa: cho thấy ứng viên kèm dinh dưỡng trước khi áp (Mealbird: xem thay đổi trước khi áp). */
    public function mealCandidates(Request $request, WeekPlan $weekPlan, $mealId, WeekBuilder $builder): JsonResponse
    {
        $this->authorizePlan($request, $weekPlan);
        $meal = $this->findMeal($weekPlan, $mealId);
        $candidates = $builder->candidates($meal->slot, $request->user()->profile, [$meal->dish_id]);

        return response()->json(['candidates' => $candidates->map(fn ($dish) => [
            'id' => $dish->id, 'slug' => $dish->slug, 'name' => $dish->displayName(),
            'region' => $dish->region?->name,
            'nutrition' => $dish->nutrition === null ? null : [
                'kcal_min' => $dish->nutrition->kcal_min, 'kcal_max' => $dish->nutrition->kcal_max,
                'protein_g' => $dish->nutrition->protein_g,
            ],
        ])->values()]);
    }

    public function swapMeal(Request $request, WeekPlan $weekPlan, $mealId, WeekBuilder $builder, WeekValidator $validator): JsonResponse
    {
        $this->authorizePlan($request, $weekPlan);
        $data = $request->validate(['dish_id' => ['required', 'integer', 'exists:dishes,id']]);
        $meal = $this->findMeal($weekPlan, $mealId);
        $eligible = $builder->candidates($meal->slot, $request->user()->profile);
        if (! $eligible->contains('id', $data['dish_id'])) {
            return response()->json(['message' => 'Món không đủ điều kiện cho bữa này (thiếu dinh dưỡng, có nguyên liệu missing/unverified, hoặc vi phạm loại trừ/chay).'], 422);
        }
        $oldDishId = $meal->dish_id;
        $meal->update([
            'dish_id' => $data['dish_id'], 'replaced_from_dish_id' => $oldDishId, 'is_eat_out' => false,
        ]);
        $day = $meal->day;
        $validation = $validator->refreshDay($day, $weekPlan);

        return response()->json(['meal' => $this->presentMeal($meal->fresh()), 'validation' => $validation]);
    }

    /** Phần 2.5, Task 2 — đổi ngày bắt đầu: dịch toàn bộ ngày trong tuần theo cùng độ lệch, giữ nguyên món. */
    public function changeStart(Request $request, WeekPlan $weekPlan, WeekValidator $validator): JsonResponse
    {
        $this->authorizePlan($request, $weekPlan);
        $data = $request->validate(['start_date' => ['required', 'date']]);
        $newStart = \Carbon\Carbon::parse($data['start_date'])->startOfDay();
        $oldStart = $weekPlan->start_date->copy()->startOfDay();
        $conflict = WeekPlan::where('user_id', $request->user()->id)
            ->where('start_date', $newStart->toDateString())
            ->where('id', '!=', $weekPlan->id)->exists();
        if ($conflict) {
            return response()->json(['message' => 'Đã có tuần khác bắt đầu ngày này.'], 422);
        }
        $delta = $newStart->greaterThan($oldStart)
            ? $oldStart->diffInDays($newStart)
            : -$newStart->diffInDays($oldStart);
        \Illuminate\Support\Facades\DB::transaction(function () use ($weekPlan, $newStart, $delta, $validator) {
            $weekPlan->update([
                'start_date' => $newStart->toDateString(),
                'end_date' => $newStart->copy()->addDays(6)->toDateString(),
            ]);
            // Cập nhật theo thứ tự tránh va chạm unique (plan, date) khi
            // ngày mới chồng lên ngày cũ: dịch lên thì từ cuối về đầu,
            // dịch xuống thì từ đầu về cuối. Dùng query trực tiếp vì
            // quan hệ days() đã orderBy('date') sẵn và sẽ thắng orderBy sau.
            $days = \App\Models\WeekPlanDay::where('week_plan_id', $weekPlan->id)
                ->orderBy('date', $delta >= 0 ? 'desc' : 'asc')->get();
            foreach ($days as $day) {
                $day->update(['date' => $day->date->copy()->addDays($delta)->toDateString()]);
            }
            foreach ($weekPlan->fresh()->days as $day) {
                $validator->refreshDay($day, $weekPlan);
            }
        });

        return response()->json(['plan' => $this->presentPlan($weekPlan->fresh()->load('days'))]);
    }

    /** Phần 2.5, Task 3 — bắt đầu lại phần còn lại của tuần từ một ngày. */
    public function restartRemaining(Request $request, WeekPlan $weekPlan, WeekBuilder $builder): JsonResponse
    {
        $this->authorizePlan($request, $weekPlan);
        $data = $request->validate(['from_date' => ['required', 'date']]);
        $from = \Carbon\Carbon::parse($data['from_date'])->startOfDay();
        if ($from->lt($weekPlan->start_date) || $from->gt($weekPlan->end_date)) {
            return response()->json(['message' => 'Ngày bắt đầu lại phải nằm trong tuần này.'], 422);
        }
        $rebuilt = $builder->rebuildFrom($weekPlan, $from->toDateString());

        return response()->json([
            'plan' => $this->presentPlan($weekPlan->fresh()->load('days')),
            'rebuilt_day_ids' => $rebuilt,
        ]);
    }

    /** Phần 2.5, Task 4 — đánh dấu ăn ngoài: bữa đó không tính vào tổng ngày, ngày thành chưa đủ dữ kiện (không biết đã ăn gì). */
    public function eatOut(Request $request, WeekPlan $weekPlan, $mealId, WeekValidator $validator): JsonResponse
    {
        $this->authorizePlan($request, $weekPlan);
        $data = $request->validate(['is_eat_out' => ['required', 'boolean']]);
        $meal = $this->findMeal($weekPlan, $mealId);
        $meal->update(['is_eat_out' => $data['is_eat_out']]);
        $validation = $validator->refreshDay($meal->day, $weekPlan);

        return response()->json(['meal' => $this->presentMeal($meal->fresh()), 'validation' => $validation]);
    }

    private function findMeal(WeekPlan $weekPlan, $mealId)
    {
        $meal = \App\Models\WeekPlanMeal::with('day')->find($mealId);
        abort_unless($meal !== null && $meal->day->week_plan_id === $weekPlan->id, 404);

        return $meal;
    }

    private function authorizePlan(Request $request, WeekPlan $plan): void
    {
        abort_unless($plan->user_id === $request->user()->id, 404);
    }

    /** @param array<int, array<string, mixed>>|null $validations */
    private function presentPlan(WeekPlan $plan, ?array $validations = null): array
    {
        $days = $plan->days;
        $summary = null;
        if ($validations !== null) {
            $summary = [
                'dat' => count(array_filter($validations, fn ($v) => $v['status'] === 'dat')),
                'thieu' => count(array_filter($validations, fn ($v) => $v['status'] === 'thieu')),
                'chua_du_du_lieu' => count(array_filter($validations, fn ($v) => $v['status'] === 'chua-du-du-lieu')),
            ];
        }

        return [
            'id' => $plan->id,
            'start_date' => $plan->start_date->toDateString(),
            'end_date' => $plan->end_date->toDateString(),
            'goal' => $plan->goal,
            'target_protein_g_per_day' => $plan->target_protein_g_per_day,
            'target_kcal_per_day' => $plan->target_kcal_per_day,
            'status' => $plan->status,
            'days' => $days->map(fn ($d) => [
                'id' => $d->id, 'date' => $d->date->toDateString(), 'status' => $d->status,
                'protein_g' => $d->protein_g, 'kcal_min' => $d->kcal_min, 'kcal_max' => $d->kcal_max,
            ])->values(),
            'summary' => $summary,
        ];
    }

    private function presentMeal($meal): array
    {
        $dish = $meal->dish;
        $nutrition = $dish?->nutrition;

        return [
            'id' => $meal->id, 'slot' => $meal->slot, 'servings' => $meal->servings,
            'is_eat_out' => $meal->is_eat_out,
            'dish' => $dish === null ? null : [
                'id' => $dish->id, 'slug' => $dish->slug, 'name' => $dish->displayName(),
                'region' => $dish->region?->name,
                'nutrition' => $nutrition === null ? null : [
                    'kcal_min' => $nutrition->kcal_min, 'kcal_max' => $nutrition->kcal_max,
                    'protein_g' => $nutrition->protein_g, 'confidence' => $nutrition->confidence,
                ],
            ],
        ];
    }
}
