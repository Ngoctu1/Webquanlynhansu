<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('departments')->insert([
            [
                'name' => 'Phòng Hành chính - Nhân sự',
                'description' => 'Quản lý nhân sự, tuyển dụng và các hoạt động hành chính nội bộ',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Phòng Công nghệ thông tin',
                'description' => 'Phát triển, vận hành và bảo trì hệ thống phần mềm công ty',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Phòng Kế toán - Tài chính',
                'description' => 'Quản lý dòng tiền, quyết toán thuế và hạch toán kế toán',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Phòng Kinh doanh & Marketing',
                'description' => 'Tìm kiếm khách hàng, phát triển thị trường và truyền thông',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
