<?php
namespace Database\Factories;

use App\Models\{Department, Position, WorkSchedule};
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_code' => fake()->unique()->bothify('NV-########'),
            'full_name' => fake()->name(), 'personal_email' => fake()->unique()->safeEmail(),
            'department_id' => fn () => Department::firstOrCreate(['code' => 'TEST'], ['name' => 'Phòng thử nghiệm', 'status' => 'active'])->id,
            'position_id' => fn () => Position::firstOrCreate(['code' => 'TEST'], ['name' => 'Vị trí thử nghiệm', 'status' => 'active'])->id,
            'work_schedule_id' => fn () => WorkSchedule::firstOrCreate(['name' => 'Test schedule'], [
                'start_time' => '08:00', 'end_time' => '17:00', 'break_start' => '12:00', 'break_end' => '13:00',
                'standard_minutes' => 480, 'late_grace_minutes' => 5, 'work_days' => [1,2,3,4,5], 'status' => 'active',
            ])->id,
            'employment_status' => 'working', 'hire_date' => today(),
        ];
    }
}
