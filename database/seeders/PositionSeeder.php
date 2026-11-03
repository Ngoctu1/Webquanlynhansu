<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'Giám đốc', 'description' => 'Giám đốc điều hành'],
            ['name' => 'Trưởng phòng', 'description' => 'Quản lý cấp phòng ban'],
            ['name' => 'Nhân viên nhân sự', 'description' => 'Chuyên viên hành chính nhân sự'],
            ['name' => 'Chuyên viên tuyển dụng', 'description' => 'Phụ trách tuyển dụng nhân tài'],
            ['name' => 'Lập trình viên', 'description' => 'Phát triển và bảo trì phần mềm'],
            ['name' => 'Trưởng nhóm kỹ thuật', 'description' => 'Quản lý nhóm kỹ thuật'],
            ['name' => 'Nhân viên kinh doanh', 'description' => 'Tư vấn và phát triển doanh số'],
            ['name' => 'Chuyên viên Marketing', 'description' => 'Triển khai chiến dịch Marketing'],
            ['name' => 'Kế toán viên', 'description' => 'Nghiệp vụ kế toán tổng hợp'],
            ['name' => 'Nhân viên', 'description' => 'Nhân viên chuyên môn'],
            ['name' => 'Chờ phê duyệt', 'description' => 'Vị trí chờ phân công chính thức'],
        ];

        foreach ($positions as $pos) {
            Position::updateOrCreate(
                ['name' => $pos['name']],
                [
                    'description' => $pos['description'],
                    'status' => 'active',
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                ]
            );
        }
    }
}
