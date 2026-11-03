<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            1 => ['name' => Role::ADMIN, 'description' => 'Quản trị viên kỹ thuật'],
            2 => ['name' => Role::DIRECTOR, 'description' => 'Giám đốc điều hành - duyệt thay đổi'],
            3 => ['name' => Role::HR, 'description' => 'Quản lý nhân sự'],
            4 => ['name' => Role::EMPLOYEE, 'description' => 'Nhân viên'],
        ];

        foreach ($roles as $id => $data) {
            Role::updateOrCreate(['id' => $id], $data);
        }
    }
}
