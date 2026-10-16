<?php

namespace Database\Seeders;

use App\Models\{Department, Employee, Position, Role, User, WorkSchedule};
use App\Services\RecordCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash, Validator};
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class BootstrapUserSeeder extends Seeder
{
    // Fixed demo credentials explicitly requested for these three seeded accounts.
    private const ACCOUNTS = [
        Role::DIRECTOR => [
            'employee_code' => 'BOOT-DIRECTOR', 'full_name' => 'Giám đốc hệ thống',
            'email' => 'director@hrm.com', 'password' => 'Abcd1234',
            'department_code' => 'HR', 'position_code' => 'DIRECTOR',
        ],
        Role::HR => [
            'employee_code' => 'BOOT-HR', 'full_name' => 'Nhân sự hệ thống',
            'email' => 'hr@hrm.com', 'password' => 'aBcd1234',
            'department_code' => 'HR', 'position_code' => 'HEAD',
        ],
        Role::EMPLOYEE => [
            'full_name' => 'Nhân viên 1',
            'email' => 'nv1@hrm.com', 'password' => 'abCd1234',
            'department_code' => 'IT', 'position_code' => 'STAFF',
        ],
    ];

    public function run(): void
    {
        $this->seedAccounts([Role::DIRECTOR, Role::HR, Role::EMPLOYEE]);
    }

    protected function seedAccounts(array $roles): void
    {
        DB::transaction(function () use ($roles) {
            // Both this seeder and DirectorUserSeeder can run independently.
            $this->call([RoleSeeder::class, DepartmentSeeder::class, PositionSeeder::class, WorkScheduleSeeder::class]);
            $schedule = WorkSchedule::where('name', 'Hành chính 08:00–17:00')->where('status', 'active')->firstOrFail();

            foreach ($roles as $roleName) {
                $account = self::ACCOUNTS[$roleName];
                Validator::make($account, [
                    'email' => ['required', 'email', 'max:255'],
                    'password' => ['required', 'string', 'max:72', Password::min(8)->mixedCase()->numbers()],
                ])->validate();

                $role = Role::where('name', $roleName)->lockForUpdate()->firstOrFail();
                if ($roleName === Role::EMPLOYEE) {
                    $existingUser = User::where('email', $account['email'])->first();
                    $employee = $existingUser
                        ? Employee::lockForUpdate()->findOrFail($existingUser->employee_id)
                        : Employee::where('personal_email', $account['email'])->lockForUpdate()->first();
                } else {
                    $employee = Employee::where('employee_code', $account['employee_code'])->lockForUpdate()->first();
                }
                $user = $employee ? $employee->user()->lockForUpdate()->first() : null;

                if (User::where('email', $account['email'])
                    ->when($user, fn ($query) => $query->where('id', '<>', $user->id))->exists()
                    || Employee::where('personal_email', $account['email'])
                    ->when($employee, fn ($query) => $query->where('id', '<>', $employee->id))->exists()) {
                    throw new RuntimeException('Email '.$account['email'].' đã thuộc hồ sơ khác. Seeder không tự gộp hồ sơ hoặc chuyển quyền tài khoản.');
                }
                if ($user && !$user->hasRole($roleName)) {
                    throw new RuntimeException('Hồ sơ '.$employee->employee_code.' đã có tài khoản thuộc vai trò khác.');
                }

                $attributes = [
                    'full_name' => $account['full_name'], 'personal_email' => $account['email'],
                    'department_id' => Department::where('code', $account['department_code'])->where('status', 'active')->firstOrFail()->id,
                    'position_id' => Position::where('code', $account['position_code'])->where('status', 'active')->firstOrFail()->id,
                    // Preserve an existing schedule because attendance may already reference it.
                    'work_schedule_id' => $employee->work_schedule_id ?? $schedule->id,
                    'employment_status' => 'working', 'hire_date' => $employee->hire_date ?? today(),
                ];
                if ($employee) {
                    $employee->fill($attributes)->save();
                } elseif ($roleName === Role::EMPLOYEE) {
                    $employee = app(RecordCodeService::class)->employee(
                        fn (string $code) => Employee::create([...$attributes, 'employee_code' => $code])
                    );
                } else {
                    $employee = Employee::create([...$attributes, 'employee_code' => $account['employee_code']]);
                }

                $user ??= new User(['employee_id' => $employee->id]);
                $user->forceFill([
                    'role_id' => $role->id, 'email' => $account['email'],
                    'password' => $user->password && Hash::check($account['password'], $user->password)
                        ? $user->password : Hash::make($account['password']),
                    'status' => 'active', 'activated_at' => $user->activated_at ?? now(),
                    'activation_token_hash' => null, 'activation_expires_at' => null, 'remember_token' => null,
                ])->save();
            }
        }, 3);

        $this->command?->info('Đã cập nhật các tài khoản mẫu: '.implode(', ', array_map(fn ($role) => self::ACCOUNTS[$role]['email'], $roles)));
    }
}
