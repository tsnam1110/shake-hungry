<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Http\Controllers\Controller;
use App\Models\FeedbackTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Góp ý / phản hồi dạng ticket (UI-018). Chỉ người dùng đã đăng nhập. */
class FeedbackController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(FeedbackTicket::TYPES))],
            'title' => ['nullable', 'string', 'max:160'],
            'content' => ['required', 'string', 'min:5', 'max:5000'],
            'dish_id' => ['nullable', 'integer', 'exists:dishes,id'],
            'fruit_id' => ['nullable', 'integer', 'exists:fruits,id'],
            'ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'drink_id' => ['nullable', 'integer', 'exists:drinks,id'],
            'page' => ['nullable', 'string', 'max:255'],
        ]);
        $ticket = $request->user()->feedbackTickets()->create([
            ...$data,
            'status' => 'da-ghi-nhan', // đã ghi nhận, chờ duyệt
        ]);

        return response()->json($this->present($ticket), 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $tickets = $request->user()->feedbackTickets()
            ->with(['dish.names', 'fruit.ingredient:id,name_vi', 'ingredient:id,name_vi', 'drink:id,name_vi'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (FeedbackTicket $t) => $this->present($t));

        return response()->json($tickets);
    }

    /** @return array<string, mixed> */
    private function present(FeedbackTicket $t): array
    {
        return [
            'id' => $t->id,
            'type' => $t->type,
            'type_label' => FeedbackTicket::TYPES[$t->type] ?? $t->type,
            'title' => $t->title,
            'content' => $t->content,
            'status' => $t->status,
            'status_label' => FeedbackTicket::STATUSES[$t->status] ?? $t->status,
            'context_name' => $t->contextName(),
            'page' => $t->page,
            'created_at' => $t->created_at?->toIso8601String(),
        ];
    }
}
