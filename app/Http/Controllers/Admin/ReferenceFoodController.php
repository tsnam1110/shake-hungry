<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferenceFood;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Ô tìm thực phẩm tham chiếu để người biên soạn so sánh trước khi ánh xạ (ADM-002 mục 3). */
class ReferenceFoodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $query = ReferenceFood::query()
            ->select(['id', 'fct_source', 'external_id', 'name_en', 'name_vi', 'kcal_per_100g', 'protein_g', 'fat_g', 'carb_g']);
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name_en', 'like', "%{$q}%")->orWhere('name_vi', 'like', "%{$q}%");
            });
        }

        return response()->json($query->orderBy('name_en')->limit(20)->get());
    }
}
