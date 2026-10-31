<?php

namespace Tests\Feature;

use App\Models\{Contract, Department, Employee, Position, Role, User};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Schema, Storage};
use Tests\TestCase;

class EmployeeCrudTest extends TestCase
{
    private bool $transactionStarted = false;
    private User $hr;
    private Department $department;
    private Position $position;

    protected function setUp(): void
    {
        parent::setUp();

        if (!app()->environment('testing')
            || !preg_match('/(_test|_testing)$/D', DB::connection()->getDatabaseName())
            || !in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)
            || !Schema::hasTable('employees')) {
            $this->markTestSkipped('Use a separately prepared MySQL/MariaDB test database. This test never resets a database.');
        }

        DB::beginTransaction();
        $this->transactionStarted = true;
        $role = Role::firstOrCreate(['name' => Role::HR], ['description' => 'Test HR']);
        $this->department = Department::firstOrCreate(['code' => 'TEST-CRUD'], ['name' => 'Phòng kiểm thử', 'status' => 'active']);
        $this->position = Position::firstOrCreate(['code' => 'TEST-CRUD'], ['name' => 'Nhân viên kiểm thử', 'status' => 'active']);
        $hrEmployee = Employee::factory()->create([
            'employee_code' => 'TEST-HR',
            'personal_email' => 'hr-crud@example.test',
            'work_email' => 'hr-crud-work@example.test',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
        ]);
        $this->hr = User::factory()->active('Testing123')->create([
            'employee_id' => $hrEmployee->id,
            'role_id' => $role->id,
            'email' => 'hr-crud@example.test',
        ]);
        $this->actingAs($this->hr);
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        if ($this->transactionStarted) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    public function test_hr_can_create_update_and_mark_employee_resigned_without_losing_related_records(): void
    {
        $payload = $this->payload('crud-created@example.test');
        $payload['profile_photo'] = UploadedFile::fake()->image('avatar.png', 80, 80);

        $this->get(route('employees.create'))->assertOk();
        $this->post(route('employees.store'), $payload)->assertRedirect();

        $employee = Employee::where('personal_email', 'crud-created@example.test')->firstOrFail();
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'working',
        ]);
        $this->assertNotNull($employee->profile_photo_path);
        Storage::disk('local')->assertExists($employee->profile_photo_path);

        $oldPhoto = $employee->profile_photo_path;
        $this->put(route('employees.update', $employee), [
            ...$this->payload('crud-created@example.test'),
            'full_name' => 'Nhân viên đã cập nhật',
            'phone' => '0912345678',
            'address' => 'Địa chỉ sau cập nhật',
        ])->assertRedirect();
        $employee->refresh();
        $this->assertSame('Nhân viên đã cập nhật', $employee->full_name);
        $this->assertSame('0912345678', $employee->phone);
        $this->assertSame($oldPhoto, $employee->profile_photo_path);

        $employeeRole = Role::firstOrCreate(['name' => Role::EMPLOYEE], ['description' => 'Test employee']);
        $linkedUser = User::factory()->active('Testing123')->create([
            'employee_id' => $employee->id,
            'role_id' => $employeeRole->id,
            'email' => 'linked-crud@example.test',
        ]);
        $contract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'created_by' => $this->hr->id,
            'contract_code' => 'TEST-CRUD-CONTRACT',
        ]);

        $this->delete(route('employees.destroy', $employee))->assertRedirect(route('employees.index'));
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'employment_status' => 'resigned']);
        $this->assertDatabaseHas('users', ['id' => $linkedUser->id, 'employee_id' => $employee->id]);
        $this->assertDatabaseHas('contracts', ['id' => $contract->id, 'employee_id' => $employee->id]);
    }

    public function test_validation_rejects_required_invalid_and_duplicate_values(): void
    {
        $existing = Employee::factory()->create([
            'employee_code' => 'TEST-EXISTING',
            'personal_email' => 'existing-crud@example.test',
            'work_email' => 'existing-work-crud@example.test',
            'phone' => '0987654321',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
        ]);

        $this->post(route('employees.store'), [])->assertSessionHasErrors(['full_name', 'personal_email', 'department_id', 'position_id', 'employment_status']);
        $this->post(route('employees.store'), [
            ...$this->payload('not-an-email'),
            'personal_email' => 'existing-crud@example.test',
            'phone' => 'bad-phone',
        ])->assertSessionHasErrors(['personal_email', 'phone']);
        $this->put(route('employees.update', $existing), $this->payload('existing-crud@example.test'))
            ->assertSessionHasNoErrors();
    }

    public function test_employee_role_cannot_access_employee_crud_urls(): void
    {
        $employeeRole = Role::firstOrCreate(['name' => Role::EMPLOYEE], ['description' => 'Test employee']);
        $employee = Employee::factory()->create([
            'employee_code' => 'TEST-UNAUTHORIZED',
            'personal_email' => 'unauthorized-crud@example.test',
            'work_email' => 'unauthorized-work-crud@example.test',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
        ]);
        $user = User::factory()->active('Testing123')->create([
            'employee_id' => $employee->id,
            'role_id' => $employeeRole->id,
            'email' => 'unauthorized-user@example.test',
        ]);

        $this->actingAs($user)->get(route('employees.create'))->assertForbidden();
        $this->put(route('employees.update', $employee), $this->payload('unauthorized-crud@example.test'))->assertForbidden();
        $this->delete(route('employees.destroy', $employee))->assertForbidden();
    }

    private function payload(string $email): array
    {
        return [
            'full_name' => 'Nhân viên kiểm thử',
            'gender' => 'female',
            'date_of_birth' => '1998-01-01',
            'phone' => '0901234567',
            'personal_email' => $email,
            'work_email' => str_replace('@', '.work@', $email),
            'address' => 'Hà Nội',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'hire_date' => '2026-01-01',
            'employment_status' => 'working',
        ];
    }
}
