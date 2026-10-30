<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Contribution;
use App\Models\Dish;
use App\Support\NameSimilarity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Hàng đợi duyệt đóng góp cộng đồng trong trang quản trị (CTR-001):
 * chấp nhận món mới → tạo món nháp source='curated' (đã qua tay biên tập viên),
 * vẫn needs_review=true để biên soạn tiếp như mọi món khác.
 */
class ContributionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Contribution::query()->latest();
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json($query->paginate(20));
    }

    public function review(Request $request, Contribution $contribution): JsonResponse
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'reject_reason' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($contribution->status !== 'pending') {
            return response()->json(['message' => 'Đề xuất này đã được xử lý trước đó.'], 422);
        }

        $createdDishId = null;
        if ($data['decision'] === 'approved' && $contribution->type === 'new_dish') {
            $title = $contribution->payload['title'] ?? 'Món mới';
            $slug = Str::slug(NameSimilarity::normalize($title)) ?: 'mon-moi';
            $candidate = $slug;
            $i = 2;
            while (Dish::where('slug', $candidate)->exists()) {
                $candidate = $slug.'-'.$i++;
            }
            $dish = Dish::create([
                'slug' => $candidate, 'country_code' => 'VN', 'status' => 'draft',
                'source' => 'curated', 'confidence' => 'low', 'needs_review' => true,
                'completeness_level' => 1,
            ]);
            $dish->names()->create(['locale' => 'vi', 'name' => $title, 'is_primary' => true, 'is_alias' => false]);
            $createdDishId = $dish->id;
        }

        $contribution->update([
            'status' => $data['decision'],
            'reviewed_by' => $request->user()->id, // contributions.reviewed_by là FK số tới users (khác bảng dishes/ingredients lưu email)
            'reviewed_at' => now(),
            'reject_reason' => $data['decision'] === 'rejected' ? ($data['reject_reason'] ?? null) : null,
        ]);
        AdminAuditLog::record($data['decision'] === 'approved' ? 'approve' : 'reopen', $contribution, [
            'created_dish_id' => $createdDishId,
        ]);

        return response()->json([...$contribution->fresh()->toArray(), 'created_dish_id' => $createdDishId]);
    }
}
