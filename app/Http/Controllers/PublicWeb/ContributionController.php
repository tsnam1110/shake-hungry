<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Đóng góp cộng đồng (CTR-001): vào hàng đợi duyệt, không hiển thị khi chưa duyệt. */
class ContributionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['new_dish', 'fix_dish', 'new_place'])],
            'title' => ['required', 'string', 'max:255'],
            'detail' => ['nullable', 'string', 'max:5000'],
        ]);
        $contribution = Contribution::create([
            'type' => $data['type'],
            'payload' => [
                'title' => $data['title'],
                'detail' => $data['detail'] ?? null,
                'submitted_by' => $request->user()->email,
                'submitted_by_name' => $request->user()->name,
            ],
            'status' => 'pending',
        ]);

        return response()->json($contribution, 201);
    }

    public function mine(Request $request): JsonResponse
    {
        return response()->json(
            Contribution::where('payload->submitted_by', $request->user()->email)
                ->latest()->limit(50)->get()
        );
    }
}
