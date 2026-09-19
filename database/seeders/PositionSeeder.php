<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('positions')->insert([
            [
                'name' => 'Giám đốc',
                'description' => 'Quản lý và điều hành toàn bộ hoạt động của công ty',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Trưởng phòng',
                'description' => 'Quản lý, phân công và điều hành công việc trong phòng ban',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nhân viên chính thức',
                'description' => 'Thực hiện công việc chuyên môn theo hợp đồng lao động',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Thực tập sinh',
                'description' => 'Hỗ trợ công việc chuyên môn và tiếp nhận đào tạo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
