<?php

namespace Database\Seeders;

use App\Models\{Contract, Department, Employee, Position, Role, User};
use App\Services\RecordCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash, Storage};
use RuntimeException;

class HrmDemoSeeder extends Seeder
{
    public const PASSWORD = 'nv12345678';

    /** @var array<int, array<string, mixed>> */
    private const PEOPLE = [
        ['name' => 'Nguyễn Minh Anh', 'gender' => 'female', 'department' => 'HR', 'position' => 'HR_STAFF', 'address' => 'Quận Ba Đình, Hà Nội'],
        ['name' => 'Trần Quốc Bảo', 'gender' => 'male', 'department' => 'IT', 'position' => 'DEV', 'address' => 'Quận Cầu Giấy, Hà Nội'],
        ['name' => 'Lê Thu Cẩm', 'gender' => 'female', 'department' => 'SALES', 'position' => 'SALES', 'address' => 'Quận Hải Châu, Đà Nẵng'],
        ['name' => 'Phạm Đức Duy', 'gender' => 'male', 'department' => 'MKT', 'position' => 'MKT', 'address' => 'Quận 3, TP. Hồ Chí Minh'],
        ['name' => 'Vũ Ngọc Em', 'gender' => 'female', 'department' => 'ACC', 'position' => 'ACCOUNTANT', 'address' => 'Quận Ninh Kiều, Cần Thơ'],
        ['name' => 'Đỗ Gia Hân', 'gender' => 'female', 'department' => 'IT', 'position' => 'TECH_LEAD', 'address' => 'Quận Thanh Xuân, Hà Nội'],
        ['name' => 'Bùi Hoàng Khang', 'gender' => 'male', 'department' => 'SALES', 'position' => 'SALES', 'address' => 'Quận Bình Thạnh, TP. Hồ Chí Minh'],
        ['name' => 'Nguyễn Lan Khanh', 'gender' => 'female', 'department' => 'HR', 'position' => 'RECRUITER', 'address' => 'Quận Sơn Trà, Đà Nẵng'],
        ['name' => 'Trương Minh Long', 'gender' => 'male', 'department' => 'IT', 'position' => 'DEV', 'address' => 'Quận Nam Từ Liêm, Hà Nội'],
        ['name' => 'Lý Thảo My', 'gender' => 'female', 'department' => 'MKT', 'position' => 'MKT', 'address' => 'Thành phố Thủ Đức, TP. Hồ Chí Minh'],
        ['name' => 'Phan Nhật Nam', 'gender' => 'male', 'department' => 'ACC', 'position' => 'ACCOUNTANT', 'address' => 'Quận Lê Chân, Hải Phòng'],
        ['name' => 'Hoàng Quỳnh Oanh', 'gender' => 'female', 'department' => 'SALES', 'position' => 'SALES', 'address' => 'Quận Liên Chiểu, Đà Nẵng'],
        ['name' => 'Mai Phương Quân', 'gender' => 'male', 'department' => 'IT', 'position' => 'DEV', 'address' => 'Quận 7, TP. Hồ Chí Minh'],
        ['name' => 'Ngô Thanh Sơn', 'gender' => 'male', 'department' => 'HR', 'position' => 'HR_STAFF', 'address' => 'Quận Hoàn Kiếm, Hà Nội'],
        ['name' => 'Đặng Tường Vy', 'gender' => 'female', 'department' => 'MKT', 'position' => 'MKT', 'address' => 'Quận Hồng Bàng, Hải Phòng'],
        ['name' => 'Cao Bảo Yến', 'gender' => 'female', 'department' => 'PENDING', 'position' => 'PENDING', 'address' => 'Quận Tây Hồ, Hà Nội'],
        ['name' => 'Dương Khánh Linh', 'gender' => 'female', 'department' => 'PENDING', 'position' => 'PENDING', 'address' => 'Quận Gò Vấp, TP. Hồ Chí Minh'],
        ['name' => 'Hồ Minh Phúc', 'gender' => 'male', 'department' => 'PENDING', 'position' => 'PENDING', 'address' => 'Quận Ngũ Hành Sơn, Đà Nẵng'],
        ['name' => 'Tạ Quốc Thành', 'gender' => 'male', 'department' => 'PENDING', 'position' => 'PENDING', 'address' => 'Quận Long Biên, Hà Nội'],
        ['name' => 'Võ Thu Trang', 'gender' => 'female', 'department' => 'PENDING', 'position' => 'PENDING', 'address' => 'Thành phố Biên Hòa, Đồng Nai'],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $this->call([RoleSeeder::class, DepartmentSeeder::class, PositionSeeder::class, BootstrapUserSeeder::class, AdminUserSeeder::class]);

            $employeeRole = Role::where('name', Role::EMPLOYEE)->firstOrFail();
            $hr = User::where('email', 'hr@hrm.com')->firstOrFail();
            $director = User::where('email', 'director@hrm.com')->firstOrFail();

            foreach (self::PEOPLE as $offset => $person) {
                $number = $offset + 5;
                $isWorking = $offset < 15;
                $email = sprintf('nv%02d@hrm.com', $number);
                $employee = $this->upsertEmployee($person, $number, $email, $isWorking);
                $this->upsertAccount($employee, $employeeRole, $email, $isWorking);
                $this->upsertContract($employee, $hr, $director, $number, $isWorking);
            }
        }, 3);

        $this->command?->info('Đã đồng bộ 20 hồ sơ nhân viên demo nv05@hrm.com đến nv24@hrm.com.');
    }

    private function upsertEmployee(array $person, int $number, string $email, bool $isWorking): Employee
    {
        $personalEmail = sprintf('nv%02d.personal@demo.hrm.local', $number);
        $departmentId = Department::where('code', $person['department'])->firstOrFail()->id;
        $positionId = Position::where('code', $person['position'])->firstOrFail()->id;
        $attributes = Employee::factory()->make([
            'full_name' => $person['name'],
            'gender' => $person['gender'],
            'date_of_birth' => now()->subYears(24 + ($number % 13))->subDays($number)->toDateString(),
            'phone' => sprintf('0905%06d', $number),
            'personal_email' => $personalEmail,
            'work_email' => $email,
            'address' => $person['address'],
            'department_id' => $departmentId,
            'position_id' => $positionId,
            'hire_date' => $isWorking ? now()->subMonths(3 + ($number % 18))->toDateString() : null,
            'employment_status' => $isWorking ? 'working' : 'pending',
            'profile_photo_path' => null,
        ])->getAttributes();
        unset($attributes['employee_code']);

        $employee = Employee::where('work_email', $email)->lockForUpdate()->first();
        if ($employee) {
            if ($employee->personal_email !== $personalEmail) {
                throw new RuntimeException("Email demo {$email} đang thuộc hồ sơ không phải dữ liệu demo.");
            }
            $employee->fill($attributes)->save();
            return $employee;
        }
        if (Employee::where('personal_email', $personalEmail)->exists() || User::where('email', $email)->exists()) {
            throw new RuntimeException("Email demo {$email} đã được sử dụng bởi dữ liệu khác.");
        }

        return app(RecordCodeService::class)->employee(
            fn (string $code) => Employee::create([...$attributes, 'employee_code' => $code])
        );
    }

    private function upsertAccount(Employee $employee, Role $role, string $email, bool $isWorking): void
    {
        $user = $employee->user()->lockForUpdate()->first();
        $emailOwner = User::where('email', $email)->lockForUpdate()->first();
        if ($emailOwner && (!$user || $emailOwner->id !== $user->id)) {
            throw new RuntimeException("Email demo {$email} đang thuộc tài khoản khác.");
        }
        if ($user && $user->email !== $email) {
            throw new RuntimeException("Hồ sơ {$employee->employee_code} đã liên kết với tài khoản không phải dữ liệu demo.");
        }

        $user ??= User::factory()->make(['employee_id' => $employee->id]);
        $user->forceFill($isWorking ? [
            'employee_id' => $employee->id,
            'role_id' => $role->id,
            'email' => $email,
            'password' => Hash::make(self::PASSWORD),
            'status' => 'active',
            'activated_at' => now(),
            'activation_token_hash' => null,
            'activation_expires_at' => null,
            'remember_token' => null,
        ] : [
            'employee_id' => $employee->id,
            'role_id' => $role->id,
            'email' => $email,
            'password' => null,
            'status' => 'pending_reset',
            'activated_at' => null,
            'activation_token_hash' => null,
            'activation_expires_at' => null,
            'remember_token' => null,
        ])->save();
    }

    private function upsertContract(Employee $employee, User $hr, User $director, int $number, bool $isWorking): void
    {
        $existing = Contract::where('employee_id', $employee->id)->lockForUpdate()->first();
        if ($existing && (int) $existing->created_by !== (int) $hr->id) {
            throw new RuntimeException("Hồ sơ {$employee->employee_code} đã có hợp đồng không phải dữ liệu demo.");
        }

        $baseSalary = match ($employee->position?->code) {
            'TECH_LEAD' => 25000000,
            'DEV' => 18000000,
            'HEAD' => 22000000,
            'ACCOUNTANT' => 16000000,
            default => 14000000,
        };
        $isProbation = $isWorking && $number % 3 === 0;
        $effectiveFrom = $isProbation ? today()->subDays(15) : today()->subMonths(3 + ($number % 12))->startOfMonth();
        $documentPath = $isWorking ? "contract-documents/demo/{$employee->employee_code}.pdf" : null;
        $attributes = Contract::factory()->make([
            'employee_id' => $employee->id,
            'previous_contract_id' => null,
            'contract_type' => $isProbation ? 'probation' : 'fixed_term',
            'signed_date' => $isWorking ? $effectiveFrom->copy()->subDays(7)->toDateString() : null,
            'effective_from' => $effectiveFrom->toDateString(),
            'effective_to' => $isProbation ? $effectiveFrom->copy()->addMonths(2)->subDay()->toDateString() : $effectiveFrom->copy()->addYears(2)->subDay()->toDateString(),
            'base_salary' => $baseSalary,
            'salary_coefficient' => 1.000,
            'allowance' => $baseSalary >= 18000000 ? 1500000 : 800000,
            'status' => $isWorking ? 'approved' : 'pending_approval',
            'created_by' => $hr->id,
            'submitted_by' => $hr->id,
            'submitted_at' => $effectiveFrom->copy()->subDays(10),
            'approved_by' => $isWorking ? $director->id : null,
            'approved_at' => $isWorking ? $effectiveFrom->copy()->subDays(6) : null,
            'rejection_reason' => null,
            'signed_document_path' => $documentPath,
        ])->getAttributes();
        unset($attributes['contract_code']);

        if ($existing) {
            $existing->fill($attributes)->save();
        } else {
            app(RecordCodeService::class)->contract(
                fn (string $code) => Contract::create([...$attributes, 'contract_code' => $code])
            );
        }

        if ($documentPath && !Storage::disk('local')->exists($documentPath)) {
            Storage::disk('local')->put($documentPath, $this->demoPdf($employee->employee_code));
        }
    }

    private function demoPdf(string $employeeCode): string
    {
        $stream = "BT /F1 16 Tf 72 760 Td (HRM demo signed contract {$employeeCode}) Tj ET\n";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length '.strlen($stream).' >>' . "\nstream\n{$stream}endstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        foreach ($offsets as $offset) { $pdf .= sprintf('%010d 00000 n '."\n", $offset); }
        return $pdf."trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }
}
