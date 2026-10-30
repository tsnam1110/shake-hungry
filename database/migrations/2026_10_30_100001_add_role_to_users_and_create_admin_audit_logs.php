<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nền cho trang quản trị (ADM-001):
 * - users.role: hai vai trò `admin` (toàn quyền) và `editor` (biên soạn).
 *   Tài khoản người dùng cuối (GĐ5) KHÔNG dùng khu vực quản trị này (USR-001).
 * - admin_audit_logs: ghi ai đã sửa gì trong khu vực quản trị để truy nguyên
 *   (ADM-001 mục 4). Chỉ ghi thao tác qua giao diện/API quản trị.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 16)->default('editor');
        });
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'editor'))");

        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 32);
            $table->string('model_type', 64);
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('changes')->nullable();
            $table->timestamps();
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
        DB::statement('ALTER TABLE users DROP CHECK users_role_check');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
