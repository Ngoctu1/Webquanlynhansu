<?php

namespace Tests\Feature;

use App\Models\{Contract, Employee, Role, User};
use App\Services\RecordCodeService;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class RecordCodeTest extends TestCase
{
    private bool $transactionStarted = false;
    private User $hr;
    private Employee $template;

    protected function setUp(): void
    {
        parent::setUp();
        if (!app()->environment('testing') || !preg_match('/(_test|_testing)$/D', DB::connection()->getDatabaseName())
            || !in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('Use a separately prepared MySQL/MariaDB test database. No migrations or resets are run here.');
        }
        if (!Schema::hasTable('contracts') || !Schema::hasTable('work_schedules')) {
            $this->markTestSkipped('Prepare the current schema first.');
        }
        if (Employee::where('employee_code', 'regexp', '^NV[0-9]{3}$')->exists()
            || Contract::where('contract_code', 'regexp', '^HD[0-9]{3}$')->exists()) {
            $this->markTestSkipped('Use an isolated test database without generated NV/HD codes for boundary tests.');
        }
        DB::beginTransaction();
        $this->transactionStarted = true;
        $role = Role::firstOrCreate(['name' => Role::HR]);
        $this->hr = User::factory()->active('Testing123')->create(['role_id' => $role->id]);
        $this->template = Employee::factory()->create();
        $this->actingAs($this->hr);
    }

    protected function tearDown(): void
    {
        if ($this->transactionStarted) { DB::rollBack(); }
        parent::tearDown();
    }

    private function employeeData(string $email): array
    {
        return [
            'full_name' => 'Nhân viên kiểm thử', 'personal_email' => $email,
            'department_id' => $this->template->department_id, 'position_id' => $this->template->position_id,
            'work_schedule_id' => $this->template->work_schedule_id, 'employment_status' => 'working',
        ];
    }

    private function contractData(): array
    {
        return [
            'employee_id' => $this->template->id, 'contract_type' => 'fixed_term',
            'effective_from' => '2027-01-01', 'effective_to' => '2027-12-31',
            'base_salary' => '10000000.00', 'salary_coefficient' => '1.000', 'allowance' => '0.00',
        ];
    }

    public function test_employee_codes_are_generated_and_cannot_be_supplied_or_changed(): void
    {
        $email = Str::lower(Str::random(16)).'@example.test';
        $payload = $this->employeeData($email);
        $this->get(route('employees.create'))->assertOk()->assertDontSee('name="employee_code"', false);
        $this->post(route('employees.store'), [...$payload, 'employee_code' => 'NV999'])->assertSessionHasErrors('employee_code');
        $this->assertFalse(Employee::where('personal_email', $email)->exists());
        $this->post(route('employees.store'), $payload)->assertSessionHasNoErrors();
        $first = Employee::where('personal_email', $email)->firstOrFail();
        $this->assertSame('NV001', $first->employee_code);
        $this->assertDatabaseHas('audit_logs', ['action' => 'EMPLOYEE_CREATED', 'auditable_id' => $first->id]);
        $this->put(route('employees.update', $first), [...$payload, 'employee_code' => 'NV099'])->assertSessionHasErrors('employee_code');
        $this->put(route('employees.update', $first), [...$payload, 'full_name' => 'Tên mới'])->assertSessionHasNoErrors();
        $this->assertSame('NV001', $first->fresh()->employee_code);
        $first->update(['employment_status' => 'resigned']);
        $secondEmail = Str::lower(Str::random(16)).'@example.test';
        $this->post(route('employees.store'), $this->employeeData($secondEmail))->assertSessionHasNoErrors();
        $this->assertSame('NV002', Employee::where('personal_email', $secondEmail)->firstOrFail()->employee_code);
    }

    public function test_contract_codes_are_generated_and_remain_unchanged_on_edit(): void
    {
        $data = $this->contractData();
        $this->get(route('contracts.create'))->assertOk()->assertDontSee('name="contract_code"', false);
        $this->post(route('contracts.store'), [...$data, 'contract_code' => 'HD999'])->assertSessionHasErrors('contract_code');
        $this->post(route('contracts.store'), $data)->assertSessionHasNoErrors();
        $first = Contract::where('contract_code', 'HD001')->firstOrFail();
        $this->assertSame('draft', $first->status);
        $this->assertSame($this->hr->id, $first->created_by);
        $this->put(route('contracts.update', $first), [...$data, 'contract_code' => 'HD099'])->assertSessionHasErrors('contract_code');
        $this->put(route('contracts.update', $first), [...$data, 'allowance' => '100000.00'])->assertSessionHasNoErrors();
        $this->assertSame('HD001', $first->fresh()->contract_code);
        $this->post(route('contracts.store'), $data)->assertSessionHasNoErrors();
        $this->assertTrue(Contract::where('contract_code', 'HD002')->exists());
    }

    public function test_employee_range_ends_at_999_without_overflow_or_duplicate(): void
    {
        $prefix = Str::lower(Str::random(12));
        $rows = [];
        for ($number = 1; $number <= 998; $number++) {
            $rows[] = [...$this->employeeData($prefix.$number.'@example.test'), 'employee_code' => sprintf('NV%03d', $number)];
        }
        foreach (array_chunk($rows, 200) as $chunk) { Employee::insert($chunk); }
        $this->post(route('employees.store'), $this->employeeData($prefix.'last@example.test'))->assertSessionHasNoErrors();
        $this->assertTrue(Employee::where('employee_code', 'NV999')->exists());
        $count = Employee::count();
        $this->post(route('employees.store'), $this->employeeData($prefix.'overflow@example.test'))->assertSessionHasErrors('employee_code');
        $this->assertSame($count, Employee::count());
        $this->assertFalse(Employee::where('employee_code', 'NV1000')->exists());
    }

    public function test_contract_range_ends_at_999_without_overflow_or_duplicate(): void
    {
        $rows = [];
        for ($number = 1; $number <= 998; $number++) {
            $rows[] = [...$this->contractData(), 'contract_code' => sprintf('HD%03d', $number), 'status' => 'draft', 'created_by' => $this->hr->id];
        }
        foreach (array_chunk($rows, 200) as $chunk) { Contract::insert($chunk); }
        $this->post(route('contracts.store'), $this->contractData())->assertSessionHasNoErrors();
        $this->assertTrue(Contract::where('contract_code', 'HD999')->exists());
        $count = Contract::count();
        $this->post(route('contracts.store'), $this->contractData())->assertSessionHasErrors('contract_code');
        $this->assertSame($count, Contract::count());
        $this->assertFalse(Contract::where('contract_code', 'HD1000')->exists());
    }

    public function test_failed_creation_rolls_back_the_record_and_code(): void
    {
        $email = Str::lower(Str::random(16)).'@example.test';
        try {
            app(RecordCodeService::class)->employee(function (string $code) use ($email) {
                Employee::create([...$this->employeeData($email), 'employee_code' => $code]);
                throw new RuntimeException('Simulated failure after insertion');
            });
            $this->fail('The failed transaction must be rolled back.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated failure after insertion', $exception->getMessage());
        }
        $this->assertFalse(Employee::where('personal_email', $email)->exists());
        $this->post(route('employees.store'), $this->employeeData($email))->assertSessionHasNoErrors();
        $this->assertSame('NV001', Employee::where('personal_email', $email)->firstOrFail()->employee_code);
    }
}
