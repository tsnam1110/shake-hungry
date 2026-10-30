<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

/** Cây vùng miền cho ô chọn trong form nhập món (ADM-003). */
class RegionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Region::query()->select(['id', 'parent_id', 'code', 'name', 'level'])->orderBy('name')->get()
        );
    }
}
