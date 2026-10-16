<?php

namespace Database\Seeders;

use App\Models\{Department, Employee, Position, Role, User, WorkSchedule};
use App\Services\RecordCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash};
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public const EMAIL = 'admin@hrm.com';
    private const PASSWORD = 'Admin1234';

    public function run(): void
    {
        $this->call([RoleSeeder::class, DepartmentSeeder::class, PositionSeeder::class, WorkScheduleSeeder::class]);
        $existing = User::with('role')->where('email', self::EMAIL)->first();
        if ($existing && !$existing->hasRole(Role::ADMIN)) {
            throw new RuntimeException('Email Admin đã thuộc tài khoản có vai trò khác. Seeder không tự nâng quyền tài khoản đó.');
        }
        $employee = $existing?->employee ?? Employee::where('personal_email', self::EMAIL)->first();

        $save = function (?string $code = null) use ($employee): Employee {
            $role = Role::where('name', Role::ADMIN)->lockForUpdate()->firstOrFail();
            $user = User::with('role')->where('email', self::EMAIL)->lockForUpdate()->first();
            if ($user && !$user->hasRole(Role::ADMIN)) {
                throw new RuntimeException('Email Admin đã thuộc tài khoản có vai trò khác.');
            }

            $employeeId = $user?->employee_id ?? $employee?->id;
            if ($employeeId) {
                $profile = Employee::lockForUpdate()->findOrFail($employeeId);
                $linked = $profile->user()->lockForUpdate()->first();
                if ($linked && $linked->id !== $user?->id) {
                    throw new RuntimeException('Hồ sơ Admin đã liên kết với tài khoản khác.');
                }
                $profile->update(['employment_status' => 'working', 'hire_date' => $profile->hire_date ?? today()]);
            } else {
                $profile = Employee::create([
                    'employee_code' => $code, 'full_name' => 'Quản trị hệ thống', 'personal_email' => self::EMAIL,
                    'department_id' => Department::where('code', 'HR')->where('status', 'active')->firstOrFail()->id,
                    'position_id' => Position::where('code', 'HEAD')->where('status', 'active')->firstOrFail()->id,
                    'work_schedule_id' => WorkSchedule::where('status', 'active')->orderBy('id')->firstOrFail()->id,
                    'employment_status' => 'working', 'hire_date' => today(),
                ]);
            }

            $user ??= new User(['employee_id' => $profile->id]);
            $user->forceFill([
                'role_id' => $role->id, 'email' => self::EMAIL,
                'password' => $user->password && Hash::check(self::PASSWORD, $user->password)
                    ? $user->password : Hash::make(self::PASSWORD),
                'status' => 'active', 'activated_at' => $user->activated_at ?? now(),
                'activation_token_hash' => null, 'activation_expires_at' => null, 'remember_token' => null,
            ])->save();
            return $profile;
        };

        if ($employee) { DB::transaction(fn () => $save(), 3); }
        else { app(RecordCodeService::class)->employee($save); }

        $this->command?->info('Đã cập nhật tài khoản Admin: '.self::EMAIL);
    }
}