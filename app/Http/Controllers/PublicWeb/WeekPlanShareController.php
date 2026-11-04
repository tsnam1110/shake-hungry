<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Http\Controllers\Controller;
use App\Models\WeekPlan;
use App\Models\WeekPlanShare;
use App\Week\ShoppingListBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Chia sẻ tuần ăn bằng mã chỉ-đọc (Giai đoạn 4, Task 2). */
class WeekPlanShareController extends Controller
{
    public function create(Request $request, WeekPlan $weekPlan): JsonResponse
    {
        abort_unless($weekPlan->user_id === $request->user()->id, 404);
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = substr($code, 0, 4).'-'.substr($code, 4);
            $hash = hash('sha256', $code);
        } while (WeekPlanShare::where('code_hash', $hash)->exists());
        $share = WeekPlanShare::create([
            'week_plan_id' => $weekPlan->id,
            'code_hash' => $hash,
            'expires_at' => now()->addDays(14),
        ]);

        return response()->json([
            'share_id' => $share->id,
            'code' => $code,
            'share_url' => '/chia-se/'.$code,
            'expires_at' => $share->expires_at->toDateTimeString(),
            'note' => 'Mã chỉ xem được món và đi chợ của tuần này, không thấy mục tiêu, ngân sách, hồ sơ hay thông tin của bạn. Mã hết hạn sau 14 ngày, có thể thu hồi bất cứ lúc nào.',
        ], 201);
    }

    public function revoke(Request $request, WeekPlan $weekPlan, WeekPlanShare $share): JsonResponse
    {
        abort_unless($weekPlan->user_id === $request->user()->id && $share->week_plan_id === $weekPlan->id, 404);
        $share->update(['revoked_at' => now()]);

        return response()->json(['revoked' => true]);
    }

    /** Xem tuần qua mã, không cần đăng nhập. Lọc riêng tư ở đây, không ở giao diện. */
    public function showPublic(string $code, ShoppingListBuilder $shopping): JsonResponse
    {
        $share = WeekPlanShare::where('code_hash', hash('sha256', $code))->first();
        if ($share === null || ! $share->isActive()) {
            abort(404);
        }
        $plan = $share->plan;
        $plan->load('days.meals.dish.names', 'days.meals.dish.nutrition', 'days.meals.dish.region');

        return response()->json([
            'plan' => [
                'start_date' => $plan->start_date->toDateString(),
                'end_date' => $plan->end_date->toDateString(),
                'status' => $plan->status,
            ],
            'days' => $plan->days->map(fn ($day) => [
                'date' => $day->date->toDateString(),
                'meals' => $day->meals->map(fn ($m) => [
                    'slot' => $m->slot, 'is_eat_out' => $m->is_eat_out,
                    'dish' => $m->dish === null ? null : [
                        'slug' => $m->dish->slug, 'name' => $m->dish->displayName(),
                        'region' => $m->dish->region?->name,
                        'nutrition' => $m->dish->nutrition === null ? null : [
                            'kcal_min' => $m->dish->nutrition->kcal_min,
                            'kcal_max' => $m->dish->nutrition->kcal_max,
                            'protein_g' => $m->dish->nutrition->protein_g,
                        ],
                    ],
                ])->values(),
            ])->values(),
            'shopping_list' => $shopping->build($plan),
            'note' => 'Bạn đang xem tuần ăn được chia sẻ. Chỉ thấy món và đi chợ, không thấy mục tiêu hay hồ sơ của người chia sẻ.',
        ]);
    }
}
