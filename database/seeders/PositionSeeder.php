<?php
namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'STAFF' => 'Nhân viên',
            'HR_STAFF' => 'Nhân viên nhân sự',
            'RECRUITER' => 'Chuyên viên tuyển dụng',
            'DEV' => 'Lập trình viên',
            'TECH_LEAD' => 'Trưởng nhóm kỹ thuật',
            'SALES' => 'Nhân viên kinh doanh',
            'MKT' => 'Chuyên viên Marketing',
            'ACCOUNTANT' => 'Kế toán viên',
            'HEAD' => 'Trưởng phòng',
            'DIRECTOR' => 'Giám đốc',
            'PENDING' => 'Chờ phê duyệt',
        ] as $code => $name) {
            Position::firstOrCreate(['code' => $code], ['name' => $name, 'status' => 'active']);
        }
    }
}
