<?php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([Role::HR => 'Nhân sự', Role::DIRECTOR => 'Giám đốc và quản lý nhân sự', Role::EMPLOYEE => 'Nhân viên', Role::ADMIN => 'Quản trị toàn hệ thống'] as $name => $description) {
            Role::updateOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
