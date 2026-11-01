<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeedbackTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Quản trị ticket góp ý / phản hồi (UI-018): danh sách chờ duyệt + đổi trạng thái. */
class FeedbackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = FeedbackTicket::query()
            ->with(['user:id,name,email', 'dish.names', 'fruit.ingredient:id,name_vi', 'ingredient:id,name_vi'])
            ->latest();
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }
        $page = $query->paginate(30);
        $page->getCollection()->transform(fn (FeedbackTicket $t) => [
            'id' => $t->id,
            'type' => $t->type,
            'type_label' => FeedbackTicket::TYPES[$t->type] ?? $t->type,
            'title' => $t->title,
            'content' => $t->content,
            'status' => $t->status,
            'status_label' => FeedbackTicket::STATUSES[$t->status] ?? $t->status,
            'admin_note' => $t->admin_note,
            'context_name' => $t->contextName(),
            'page' => $t->page,
            'user' => $t->user === null ? null : ['name' => $t->user->name, 'email' => $t->user->email],
            'created_at' => $t->created_at?->toIso8601String(),
        ]);

        return response()->json($page);
    }

    public function updateStatus(Request $request, FeedbackTicket $ticket): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(FeedbackTicket::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $ticket->update($data);

        return response()->json(['ok' => true, 'status' => $ticket->status]);
    }
}
