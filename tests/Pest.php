<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Ép kiểm R6/R8 ngay tại điểm đang đứng trong test.
 * PostgreSQL: constraint trigger DEFERRABLE nên ra lệnh SET CONSTRAINTS ALL
 * IMMEDIATE. MySQL không có deferred trigger (xem migration 100008) nên chạm
 * một UPDATE giữ nguyên giá trị vào món để trigger AFTER UPDATE chạy kiểm.
 */
function forceCompletenessCheck(int $dishId): void
{
    if (DB::connection()->getDriverName() === 'pgsql') {
        DB::statement('SET CONSTRAINTS ALL IMMEDIATE');

        return;
    }
    DB::table('dishes')->where('id', $dishId)
        ->update(['completeness_level' => DB::raw('completeness_level')]);
}
