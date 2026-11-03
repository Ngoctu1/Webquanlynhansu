<?php

namespace Database\Factories;

use App\Models\{Department, Employee, Position};
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        $hireDate = today();

        return [
            'employee_code' => function () {
                for ($attempt = 0; $attempt < 999; $attempt++) {
                    $code = sprintf('NV%03d', fake()->unique()->numberBetween(1, 999));
                    if (!Employee::withTrashed()->where('employee_code', $code)->exists()) {
                        return $code;
                    }
                }
                throw new \RuntimeException('Không còn mã nhân viên mẫu NV001–NV999.');
            },
            'full_name' => fake()->name(),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-45 years', '-20 years')->format('Y-m-d'),
            'citizen_id' => fake()->unique()->numerify('############'),
            'phone' => fake()->unique()->numerify('09########'),
            'personal_email' => fake()->unique()->safeEmail(),
            'work_email' => fake()->unique()->companyEmail(),
            'address' => fake()->address(),
            'avatar' => null,
            'department_id' => fn () => Department::firstOrCreate(
                ['name' => 'Công nghệ thông tin'],
                ['status' => 'active', 'approval_status' => 'approved', 'approved_at' => now()]
            )->id,
            'position_id' => fn () => Position::firstOrCreate(
                ['name' => 'Nhân viên'],
                ['status' => 'active', 'approval_status' => 'approved', 'approved_at' => now()]
            )->id,
            'hire_date' => $hireDate->format('Y-m-d'),
            'employment_status' => 'working',
            'annual_leave_days' => 12,
            'resignation_date' => null,
            'resignation_reason' => null,
            'approval_status' => 'approved',
            'approved_by' => null,
            'approved_at' => now(),
            'rejection_reason' => null,
        ];
    }
}
