<?php
namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'HR' => 'Nhân sự',
            'IT' => 'Công nghệ thông tin',
            'SALES' => 'Kinh doanh',
            'MKT' => 'Marketing',
            'ACC' => 'Kế toán',
            'PENDING' => 'Chờ phân công',
        ] as $code => $name) {
            Department::firstOrCreate(['code' => $code], ['name' => $name, 'status' => 'active']);
        }
    }
}
