<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * USR-001: tài khoản người dùng cuối dùng vai trò 'user' trong cùng bảng users
 * nhưng tách biệt quyền: middleware khu vực quản trị chỉ nhận admin/editor,
 * nên tài khoản 'user' không bao giờ vào được /admin. Mặc định cột chuyển từ
 * 'editor' sang 'user' để tài khoản tạo mới không vô tình có quyền biên soạn.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users DROP CHECK users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'editor', 'user'))");
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'user'");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CHECK users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'editor'))");
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'editor'");
    }
};
