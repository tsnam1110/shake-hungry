<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Contribution;
use App\Models\Dish;
use App\Models\User;
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
        $query = Contribution::query();
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        // ADM-008: lọc theo loại đóng góp (món mới / báo sửa / quán mới).
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }
        // ADM-008 (đợt 2): sắp xếp theo cột.
        $sort = (string) $request->query('sort', '');
        if (in_array($sort, ['created_at', 'status', 'type'], true)) {
            $query->orderBy($sort, $request->query('dir') === 'desc' ? 'desc' : 'asc');
        } else {
            $query->latest();
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

        $createdDishId = $this->applyDecision($contribution, $data['decision'], $data['reject_reason'] ?? null, $request->user());

        return response()->json([...$contribution->fresh()->toArray(), 'created_dish_id' => $createdDishId]);
    }

    /** ADM-008 (đợt 2): xử lý hàng loạt — chỉ các đề xuất đang chờ mới được xử lý, còn lại tính là bỏ qua. */
    public function bulk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'ids' => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => ['integer'],
            'reject_reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $decision = $data['action'] === 'approve' ? 'approved' : 'rejected';
        $affected = 0;
        $skipped = 0;
        foreach (Contribution::whereIn('id', $data['ids'])->get() as $contribution) {
            if ($contribution->status !== 'pending') {
                $skipped++;
                continue;
            }
            $this->applyDecision($contribution, $decision, $data['reject_reason'] ?? null, $request->user(), bulk: true);
            $affected++;
        }

        return response()->json(['affected' => $affected, 'skipped' => $skipped]);
    }

    /** Áp một quyết định duyệt lên đề xuất; trả về id món nháp nếu vừa tạo (món mới được chấp nhận). */
    private function applyDecision(Contribution $contribution, string $decision, ?string $rejectReason, User $user, bool $bulk = false): ?int
    {
        $createdDishId = null;
        if ($decision === 'approved' && $contribution->type === 'new_dish') {
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
            'status' => $decision,
            'reviewed_by' => $user->id, // contributions.reviewed_by là FK số tới users (khác bảng dishes/ingredients lưu email)
            'reviewed_at' => now(),
            'reject_reason' => $decision === 'rejected' ? $rejectReason : null,
        ]);
        AdminAuditLog::record($decision === 'approved' ? 'approve' : 'reopen', $contribution, [
            'created_dish_id' => $createdDishId,
            ...($bulk ? ['bulk' => true] : []),
        ]);

        return $createdDishId;
    }
}
