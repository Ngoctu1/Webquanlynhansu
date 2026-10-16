<?php
namespace Database\Seeders;

use App\Models\{Department, Employee, Position, WorkSchedule};
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        Employee::firstOrCreate(['employee_code' => 'NV-DEMO'], [
            'full_name' => 'Nhân viên mẫu', 'personal_email' => 'employee@example.test',
            'department_id' => Department::where('code', 'IT')->firstOrFail()->id,
            'position_id' => Position::where('code', 'STAFF')->firstOrFail()->id,
            'work_schedule_id' => WorkSchedule::where('status', 'active')->firstOrFail()->id,
            'hire_date' => today(), 'employment_status' => 'pending',
        ]);
    }
}
