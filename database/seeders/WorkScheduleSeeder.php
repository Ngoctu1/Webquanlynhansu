<?php
namespace Database\Seeders;

use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        WorkSchedule::firstOrCreate(['name' => 'Hành chính 08:00–17:00'], [
            'start_time' => '08:00:00', 'end_time' => '17:00:00', 'break_start' => '12:00:00', 'break_end' => '13:00:00',
            'standard_minutes' => 480, 'late_grace_minutes' => 5, 'work_days' => [1, 2, 3, 4, 5], 'status' => 'active',
        ]);
    }
}
