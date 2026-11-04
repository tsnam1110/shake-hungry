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
