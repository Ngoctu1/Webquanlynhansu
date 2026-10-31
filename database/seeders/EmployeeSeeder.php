<?php
namespace Database\Seeders;

use App\Models\{Department, Employee, Position};
use App\Services\RecordCodeService;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        if (Employee::where('personal_email', 'employee@example.test')->exists()) { return; }
        app(RecordCodeService::class)->employee(fn (string $code) => Employee::create([
            'employee_code' => $code, 'full_name' => 'Nhân viên mẫu', 'personal_email' => 'employee@example.test',
            'department_id' => Department::where('code', 'IT')->firstOrFail()->id,
            'position_id' => Position::where('code', 'STAFF')->firstOrFail()->id,
            'hire_date' => today(), 'employment_status' => 'pending',
        ]));
    }
}
