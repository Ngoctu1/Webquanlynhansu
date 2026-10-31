<?php
namespace Database\Factories;

use App\Models\{Department, Employee, Position};
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_code' => function () {
                for ($attempt = 0; $attempt < 999; $attempt++) {
                    $code = sprintf('NV%03d', fake()->unique()->numberBetween(1, 999));
                    if (!Employee::where('employee_code', $code)->exists()) { return $code; }
                }
                throw new \RuntimeException('Không còn mã nhân viên mẫu NV001–NV999.');
            },
            'full_name' => fake()->name(),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-45 years', '-22 years')->format('Y-m-d'),
            'phone' => fake()->unique()->numerify('09########'),
            'personal_email' => fake()->unique()->safeEmail(),
            'work_email' => fake()->unique()->companyEmail(),
            'address' => fake()->address(),
            'department_id' => fn () => Department::firstOrCreate(['code' => 'TEST'], ['name' => 'Phòng thử nghiệm', 'status' => 'active'])->id,
            'position_id' => fn () => Position::firstOrCreate(['code' => 'TEST'], ['name' => 'Vị trí thử nghiệm', 'status' => 'active'])->id,
            'employment_status' => 'working', 'hire_date' => today(), 'profile_photo_path' => null,
        ];
    }
}
