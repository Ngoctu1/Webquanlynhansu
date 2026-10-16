<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE roles MODIFY COLUMN name ENUM('HR','Director','Employee','Admin') NOT NULL");
    }

    public function down(): void
    {
        $adminIds = DB::table('roles')->where('name', 'Admin')->pluck('id');
        if (DB::table('users')->whereIn('role_id', $adminIds)->exists()) {
            throw new RuntimeException('Còn tài khoản Admin. Chuyển vai trò các tài khoản này trước khi rollback.');
        }
        DB::table('roles')->where('name', 'Admin')->delete();
        DB::statement("ALTER TABLE roles MODIFY COLUMN name ENUM('HR','Director','Employee') NOT NULL");
    }
};
