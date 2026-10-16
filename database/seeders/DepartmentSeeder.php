<?php
namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['HR' => 'Nhân sự', 'IT' => 'Công nghệ thông tin', 'FIN' => 'Tài chính'] as $code => $name) {
            Department::firstOrCreate(['code' => $code], ['name' => $name, 'status' => 'active']);
        }
    }
}
