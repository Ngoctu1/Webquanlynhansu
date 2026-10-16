<?php
namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['STAFF' => 'Nhân viên', 'HEAD' => 'Trưởng phòng', 'DIRECTOR' => 'Giám đốc'] as $code => $name) {
            Position::firstOrCreate(['code' => $code], ['name' => $name, 'status' => 'active']);
        }
    }
}
