<?php

declare(strict_types=1);

namespace App\Week;

use App\Models\Dish;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WeekPlan;
use App\Models\WeekPlanDay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Xây tuần ăn từ thư viện Lắc Đói (Giai đoạn 2, Phần 2.3).
 *
 * KHÔNG dùng AI viết số dinh dưỡng (QT-1): mọi số lấy từ DishNutrition
 * đã tính bằng engine. Chỉ món "sạch" mới vào tuần — có protein và khoảng
 * kcal, confidence khác unknown, không có nguyên liệu missing/unverified.
 * Đó là quyết định Q3(a) đã chốt: món thiếu dữ kiện không vào ứng viên,
 * nên tuần do builder tạo không bao giờ bị gắn "đạt" một cách giả.
 *
 * Thuật toán chọn món có thể giải thích được, không giấu logic:
 * - Ứng viên mỗi bữa xếp theo protein giảm dần, rồi kcal_min tăng dần,
 *   rồi id tăng dần (tất định, không ngẫu nhiên).
 * - Mỗi món dùng tối đa 2 lần trong một tuần (lặp có chủ đích kiểu
 *   Mealbird, nhưng không hứa "11 món cho 21 bữa" vì kho Lắc Đói khác).
 *   Hết ứng viên dưới giới hạn mới cho phép lặp thêm, còn hơn bỏ trống bữa.
 * - Lọc cứng: excluded_ingredients của hồ sơ, và chế độ chay/chay-truong
 *   (nhãn diet:man + nhóm thịt/thủy sản, cùng logic Recommender). Chế độ
 *   low-carb KHÔNG lọc cứng ở đây vì chưa có ngưỡng carb nào được chốt —
 *   không tự đặt ngưỡng.
 * - Vùng miền yêu thích chỉ là ưu tiên khi kho vùng đó đủ lớn (>= 5 món
 *   cho bữa đó); kho vùng nhỏ thì dùng toàn kho, còn hơn tuần trống.
 * - Mục tiêu kcal/protein lấy từ hồ sơ người dùng đã nhập, KHÔNG suy từ
 *   goal vì tỉ lệ điều chỉnh kcal theo mục tiêu chưa được chốt. Tuần
 *   không có mục tiêu vẫn xây được, validator sẽ ghi chưa đủ dữ kiện
 *   để kết luận đạt/thiếu.
 * - Tạo tuần mới thì các tuần active cũ của cùng người dùng chuyển sang
 *   archived, để luôn có một tuần hiện tại rõ ràng.
 */
class WeekBuilder
{
    public const MAX_REPEAT_PER_WEEK = 2;

    public function build(User $user, string $startDate): WeekPlan
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $existing = WeekPlan::where('user_id', $user->id)
            ->where('start_date', $start->toDateString())
            ->first();
        if ($existing !== null) {
            return $existing->load('days.meals.dish');
        }

        return DB::transaction(function () use ($user, $start) {
            $profile = $user->profile;
            WeekPlan::where('user_id', $user->id)->where('status', 'active')
                ->update(['status' => 'archived']);
            $plan = WeekPlan::create([
                'user_id' => $user->id,
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addDays(6)->toDateString(),
                'goal' => $profile?->goal,
                'target_protein_g_per_day' => $profile?->target_protein_g_per_day,
                'target_kcal_per_day' => $profile?->target_kcal_per_day,
                'status' => 'active',
            ]);
            $usage = [];
            $validator = app(WeekValidator::class);
            for ($i = 0; $i < 7; $i++) {
                $day = WeekPlanDay::create([
                    'week_plan_id' => $plan->id,
                    'date' => $start->copy()->addDays($i)->toDateString(),
                    'status' => 'chua-du-du-lieu',
                ]);
                foreach (WeekValidator::SLOTS as $slot) {
                    $dish = $this->pick($slot, $profile, $usage);
                    if ($dish === null) {
                        continue;
                    }
                    $usage[$dish->id] = ($usage[$dish->id] ?? 0) + 1;
                    $day->meals()->create([
                        'dish_id' => $dish->id, 'slot' => $slot, 'servings' => 1,
                    ]);
                }
                $validator->refreshDay($day, $plan);
            }

            return $plan->fresh()->load('days.meals.dish');
        });
    }

    /** @return Collection<int, Dish> */
    public function candidates(string $slot, ?UserProfile $profile, array $excludeDishIds = []): Collection
    {
        $dishes = Dish::query()
            ->with(['nutrition', 'mealSlots', 'tags:id,slug', 'dishIngredients.ingredient:id,slug,category'])
            ->whereHas('nutrition', function ($q) {
                $q->whereNotNull('protein_g')->whereNotNull('kcal_min')->whereNotNull('kcal_max')
                    ->where('confidence', '!=', 'unknown');
            })
            ->whereHas('mealSlots', fn ($q) => $q->where('slot', $slot))
            ->get()
            ->filter(function (Dish $dish) use ($profile, $excludeDishIds) {
                if (in_array($dish->id, $excludeDishIds, true)) {
                    return false;
                }
                $nutrition = $dish->nutrition;
                if ($nutrition === null || ! empty($nutrition->missing_ingredients)
                    || ! empty($nutrition->unverified_ingredients)) {
                    return false;
                }
                $ingredientSlugs = $dish->dishIngredients->map(fn ($di) => $di->ingredient?->slug)->filter()->all();
                $excluded = $profile?->excluded_ingredients ?? [];
                if ($excluded !== [] && array_intersect($excluded, $ingredientSlugs) !== []) {
                    return false;
                }
                if (in_array($profile?->diet_type, ['chay', 'chay-truong'], true)) {
                    if ($dish->tags->contains('slug', 'diet:man')) {
                        return false;
                    }
                    $categories = $dish->dishIngredients->map(fn ($di) => $di->ingredient?->category)->filter()->all();
                    if (array_intersect(['thit', 'thuy-san'], $categories) !== []) {
                        return false;
                    }
                }

                return true;
            });
        if ($profile?->preferred_region_id !== null) {
            $regional = $dishes->where('region_id', $profile->preferred_region_id);
            if ($regional->count() >= 5) {
                $dishes = $regional;
            }
        }

        return $dishes->sort(function (Dish $a, Dish $b) {
            $pa = (float) ($a->nutrition->protein_g ?? 0);
            $pb = (float) ($b->nutrition->protein_g ?? 0);
            if ($pa !== $pb) {
                return $pb <=> $pa;
            }
            $ka = (float) ($a->nutrition->kcal_min ?? 0);
            $kb = (float) ($b->nutrition->kcal_min ?? 0);
            if ($ka !== $kb) {
                return $ka <=> $kb;
            }

            return $a->id <=> $b->id;
        })->values();
    }

    /**
     * Bắt đầu lại phần còn lại của tuần từ ngày $fromDate (Phần 2.5,
     * Task 3): các ngày trước đó giữ nguyên món (kể cả món đã đổi tay),
     * các ngày từ $fromDate trở đi xoá bữa cũ và xây lại bằng cùng logic
     * ứng viên của build(), với số lần dùng tính từ các ngày giữ lại +
     * các ngày vừa xây lại. Trả về id các ngày đã xây lại.
     *
     * @return array<int, int>
     */
    public function rebuildFrom(WeekPlan $plan, string $fromDate): array
    {
        $from = Carbon::parse($fromDate)->startOfDay();
        $profile = $plan->user->profile;
        $usage = [];
        foreach ($plan->days()->where('date', '<', $from->toDateString())->with('meals')->get() as $keptDay) {
            foreach ($keptDay->meals as $meal) {
                $usage[$meal->dish_id] = ($usage[$meal->dish_id] ?? 0) + 1;
            }
        }
        $validator = app(WeekValidator::class);
        $rebuilt = [];
        $days = $plan->days()->where('date', '>=', $from->toDateString())->orderBy('date')->get();
        foreach ($days as $day) {
            $day->meals()->delete();
            foreach (WeekValidator::SLOTS as $slot) {
                $dish = $this->pick($slot, $profile, $usage);
                if ($dish === null) {
                    continue;
                }
                $usage[$dish->id] = ($usage[$dish->id] ?? 0) + 1;
                $day->meals()->create(['dish_id' => $dish->id, 'slot' => $slot, 'servings' => 1]);
            }
            $validator->refreshDay($day, $plan);
            $rebuilt[] = $day->id;
        }

        return $rebuilt;
    }

    /**
     * Tuần ví dụ cho khách (tiếp tục Phần 2.4, quyết định "khách xem tuần
     * ví dụ không lưu"): 7 ngày, mỗi ngày 4 bữa, từ ứng viên thật với
     * hồ sơ null — KHÔNG lưu DB, KHÔNG cá nhân hoá, KHÔNG có mục tiêu
     * nên không kết luận đạt/thiếu. Lặp món tối đa 2 lần/tuần như builder
     * chính; kho ví dụ nhỏ thì cho phép lặp thêm còn hơn bỏ trống bữa.
     *
     * @return array<int, array{day: int, meals: array<int, array{slot: string, dish: Dish}>}>
     */
    public function exampleWeek(): array
    {
        $usage = [];
        $days = [];
        for ($day = 1; $day <= 7; $day++) {
            $meals = [];
            foreach (WeekValidator::SLOTS as $slot) {
                $dish = $this->pick($slot, null, $usage);
                if ($dish === null) {
                    continue;
                }
                $usage[$dish->id] = ($usage[$dish->id] ?? 0) + 1;
                $meals[] = ['slot' => $slot, 'dish' => $dish];
            }
            $days[] = ['day' => $day, 'meals' => $meals];
        }

        return $days;
    }

    private function pick(string $slot, ?UserProfile $profile, array $usage): ?Dish
    {
        $candidates = $this->candidates($slot, $profile);
        if ($candidates->isEmpty()) {
            return null;
        }
        foreach ($candidates as $dish) {
            if (($usage[$dish->id] ?? 0) < self::MAX_REPEAT_PER_WEEK) {
                return $dish;
            }
        }

        return $candidates->first();
    }
}
