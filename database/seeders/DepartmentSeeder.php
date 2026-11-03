<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Ban Giám đốc', 'description' => 'Ban điều hành và phê duyệt chiến lược công ty'],
            ['name' => 'Phòng Hành chính - Nhân sự', 'description' => 'Quản lý nhân sự, tuyển dụng và hành chính'],
            ['name' => 'Nhân sự', 'description' => 'Bộ phận quản lý nhân sự'],
            ['name' => 'Công nghệ thông tin', 'description' => 'Phát triển phần mềm và hạ tầng kỹ thuật'],
            ['name' => 'Kinh doanh', 'description' => 'Phát triển thị trường và chăm sóc khách hàng'],
            ['name' => 'Marketing', 'description' => 'Truyền thông và quảng bá thương hiệu'],
            ['name' => 'Kế toán', 'description' => 'Quản lý tài chính và kế toán doanh nghiệp'],
            ['name' => 'Chờ phân công', 'description' => 'Phòng ban tạm thời cho nhân sự mới'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['name' => $dept['name']],
                [
                    'description' => $dept['description'],
                    'status' => 'active',
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                ]
            );
        }
    }
}
