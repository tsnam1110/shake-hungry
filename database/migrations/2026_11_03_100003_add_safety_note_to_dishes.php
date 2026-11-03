<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cột cảnh báo an toàn thực phẩm ở tầng món (hoàn thiện Task 5, 2026-11-03).
 *
 * Lý do: file tiet-canh ghi "cảnh báo an toàn thực phẩm hiện có" nhưng thực
 * tế không có trường nào và giao diện không hiện gì — cảnh báo chỉ nằm trong
 * import_note nội bộ. Cột này là lớp THÔNG TIN thuần (như "Kết hợp cần lưu
 * ý"): hiển thị, không chặn, không lọc, không phải tư vấn y tế.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->text('safety_note')->nullable()->after('import_note');
        });
    }

    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn('safety_note');
        });
    }
};
