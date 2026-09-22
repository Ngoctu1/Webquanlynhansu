<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Cho phép chạy riêng seeder này khi chưa có dữ liệu roles.
            $this->call(RoleSeeder::class);

            if (!DB::table('roles')->where('id', 1)->where('name', 'Admin')->exists()) {
                throw new RuntimeException('Role Admin phải có id = 1. Vui lòng kiểm tra dữ liệu roles.');
            }

            $admin = User::where('username', 'admin')->lockForUpdate()->first()
                ?? new User(['username' => 'admin']);

            // Không biến tài khoản nhân viên trùng username thành quản trị viên.
            if ($admin->exists && (int) $admin->role_id !== 1) {
                throw new RuntimeException('Username admin đang thuộc tài khoản không phải Admin.');
            }

            // Tài khoản mẫu cho môi trường phát triển. Chạy lại sẽ đặt lại mật khẩu.
            // Tài khoản mới có employee_id = NULL theo schema; giữ liên kết nếu đã tồn tại.
            $admin->forceFill([
                'role_id' => 1,
                'password' => Hash::make('123456'),
                'status' => 'active',
                'password_changed_at' => now(),
                'remember_token' => null,
            ])->save();
        });
    }
}
