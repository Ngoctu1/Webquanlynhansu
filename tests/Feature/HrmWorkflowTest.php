<?php
namespace Tests\Feature;

use App\Models\{Attendance, AuditLog, Contract, Department, Employee, PayrollDetail, PayrollPeriod, Role, User};
use App\Services\{AccountInvitationService, ContractService, PayrollService};
use Illuminate\Support\Facades\{DB, Hash, Mail, Schema};
use Illuminate\Support\Str;
use Tests\TestCase;

class HrmWorkflowTest extends TestCase
{
    private bool $transactionStarted = false;

    protected function setUp(): void
    {
        parent::setUp();
        // Never migrate or reset a database from this test suite.
        $database = DB::connection()->getDatabaseName();
        if (!app()->environment('testing') || !preg_match('/(_test|_testing)$/D', $database) ||
            !in_array(DB::connection()->getDriverName(), ['mysql','mariadb'], true)) {
            $this->markTestSkipped('Use a separately prepared MySQL/MariaDB database ending in _test or _testing.');
        }
        if (!Schema::hasTable('contracts') || !Schema::hasTable('work_schedules')) {
            $this->markTestSkipped('Prepare the test database with the current migrations first.');
        }
        DB::beginTransaction(); $this->transactionStarted = true;
        foreach ([Role::HR, Role::DIRECTOR, Role::EMPLOYEE, Role::ADMIN] as $role) { Role::firstOrCreate(['name'=>$role]); }
    }
    protected function tearDown(): void
    {
        if ($this->transactionStarted) { DB::rollBack(); }
        parent::tearDown();
    }
    private function user(string $role = Role::EMPLOYEE): User
    {
        return User::factory()->active(Str::random(24).'Aa1')->create(['role_id'=>Role::where('name',$role)->firstOrFail()->id]);
    }
    private function contract(Employee $employee, User $hr, string $from='2026-10-01', ?string $to='2026-10-31'): Contract
    {
        return Contract::create(['employee_id'=>$employee->id,'contract_code'=>Str::upper(Str::random(20)),
            'contract_type'=>'fixed_term','effective_from'=>$from,'effective_to'=>$to,
            'base_salary'=>'22000000.00','salary_coefficient'=>'1.000','allowance'=>'0.00','status'=>'pending_approval','created_by'=>$hr->id]);
    }
    public function test_guest_and_roles_have_correct_login_redirects(): void
    {
        $this->get('/login')->assertOk();
        foreach ([Role::HR=>'/admin',Role::DIRECTOR=>'/admin',Role::ADMIN=>'/admin',Role::EMPLOYEE=>'/user'] as $role=>$path) {
            $this->actingAs($this->user($role))->get('/login')->assertRedirect($path);
        }
    }
    public function test_email_login_status_and_logout(): void
    {
        $password = Str::random(24).'Aa1';
        $user = User::factory()->active($password)->create();
        $this->post('/login',['email'=>$user->email,'password'=>$password])->assertRedirect('/user');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        foreach (['locked','pending_reset'] as $status) {
            $user->update(['status'=>$status]);
            $this->post('/login',['email'=>$user->email,'password'=>$password])->assertSessionHasErrors('email');
            $this->assertGuest();
        }
    }
    public function test_direct_urls_do_not_bypass_roles(): void
    {
        $hr=$this->user(Role::HR); $director=$this->user(Role::DIRECTOR); $employee=$this->user();
        $contract=$this->contract($employee->employee,$hr);
        $this->actingAs($director)->get('/admin/employees/create')->assertOk();
        $this->actingAs($hr)->post(route('contracts.approve',$contract))->assertForbidden();
        $this->actingAs($employee)->get('/admin/employees')->assertForbidden();
    }
    public function test_director_and_admin_can_manage_hr_data_and_approve_contracts(): void
    {
        $employee=Employee::factory()->create();
        foreach ([Role::DIRECTOR, Role::ADMIN] as $role) {
            $actor=$this->user($role);
            $this->actingAs($actor)->get(route('employees.create'))->assertOk()->assertDontSee('name="employee_code"', false);
            $this->get(route('contracts.create'))->assertOk()->assertDontSee('name="contract_code"', false);
            foreach (['departments.create','positions.create','attendances.create','schedules.create','payrolls.create'] as $route) {
                $this->get(route($route))->assertOk();
            }
            $from=$role===Role::DIRECTOR?'2027-01-01':'2027-02-01';
            $to=$role===Role::DIRECTOR?'2027-01-31':'2027-02-28';
            $contract=app(ContractService::class)->save($actor,[
                'employee_id'=>$employee->id,'contract_type'=>'fixed_term','effective_from'=>$from,'effective_to'=>$to,
                'base_salary'=>'10000000.00','salary_coefficient'=>'1.000','allowance'=>'0.00',
            ]);
            $this->assertMatchesRegularExpression('/^HD[0-9]{3}$/D',$contract->contract_code);
            $this->post(route('contracts.submit',$contract))->assertSessionHasNoErrors();
            $this->post(route('contracts.approve',$contract))->assertSessionHasNoErrors();
            $this->assertSame('approved',$contract->fresh()->status);
            $this->assertSame($actor->id,$contract->fresh()->approved_by);
        }
    }
    public function test_admin_login_and_exclusive_account_administration(): void
    {
        $admin=$this->user(Role::ADMIN); $employee=$this->user();
        $admin->update(['password'=>Hash::make('Admin123')]);
        $this->post('/login',['email'=>$admin->email,'password'=>'Admin123'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        $hrRole=Role::where('name',Role::HR)->firstOrFail();
        $this->patch(route('users.role',$employee),['role_id'=>$hrRole->id])->assertSessionHasNoErrors();
        $this->assertTrue($employee->fresh()->hasRole(Role::HR));
        $this->patch(route('users.status',$employee),['status'=>'locked'])->assertSessionHasNoErrors();
        $this->assertSame('locked',$employee->fresh()->status);
        $this->patch(route('users.status',$employee),['status'=>'active'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('audit_logs',['action'=>'USER_ROLE_UPDATED','auditable_id'=>$employee->id]);
        $this->patch(route('users.role',$admin),['role_id'=>$hrRole->id])->assertSessionHasErrors('role_id');
        $this->assertTrue($admin->fresh()->hasRole(Role::ADMIN));
        $this->patch(route('users.status',$admin),['status'=>'locked'])->assertSessionHasErrors('status');
        foreach ([Role::HR,Role::DIRECTOR,Role::EMPLOYEE] as $role) {
            $this->actingAs($this->user($role))->patch(route('users.role',$employee),['role_id'=>$hrRole->id])->assertForbidden();
        }
    }
    public function test_employee_edit_and_resign_preserve_history(): void
    {
        $hr=$this->user(Role::HR); $employee=Employee::factory()->create();
        $payload=$employee->only(['employee_code','full_name','personal_email','department_id','position_id','work_schedule_id','employment_status']);
        $payload['full_name']='Tên cập nhật';
        $this->actingAs($hr)->put(route('employees.update',$employee),$payload)->assertSessionHasNoErrors();
        $this->assertSame('Tên cập nhật',$employee->fresh()->full_name);
        $this->delete(route('employees.destroy',$employee))->assertRedirect(route('employees.index'));
        $this->assertSame('resigned',$employee->fresh()->employment_status);
        $this->assertDatabaseHas('audit_logs',['auditable_id'=>$employee->id,'action'=>'EMPLOYEE_RESIGNED']);
    }
    public function test_catalog_name_can_repeat_but_code_cannot(): void
    {
        $this->actingAs($this->user(Role::HR));
        $code=Str::random(10);
        foreach ([$code,Str::random(10)] as $value) {
            $this->post(route('departments.store'),['code'=>$value,'name'=>'Cùng tên','status'=>'active'])->assertSessionHasNoErrors();
        }
        $this->post(route('departments.store'),['code'=>$code,'name'=>'Khác tên','status'=>'active'])->assertSessionHasErrors('code');
    }
    public function test_overlap_and_approved_contract_edits_are_rejected(): void
    {
        $hr=$this->user(Role::HR); $director=$this->user(Role::DIRECTOR); $employee=Employee::factory()->create();
        $first=$this->contract($employee,$hr); $second=$this->contract($employee,$hr,'2026-10-15','2026-11-15');
        $this->actingAs($director)->post(route('contracts.approve',$first))->assertSessionHasNoErrors();
        $this->post(route('contracts.approve',$second))->assertSessionHasErrors('contract');
        $this->actingAs($hr)->get(route('contracts.edit',$first))->assertForbidden();
        $this->assertSame('pending_approval',$second->fresh()->status);
    }
    public function test_invitation_requires_approved_contract_and_is_idempotent(): void
    {
        config(['mail.default'=>'smtp','mail.mailers.smtp.transport'=>'smtp']);
        $hr=$this->user(Role::HR); $employee=Employee::factory()->create();
        $this->actingAs($hr)->post(route('employees.account.store',$employee))->assertSessionHasErrors('invitation');
        $this->contract($employee,$hr)->update(['status'=>'approved']);
        $mail = new class {
            public int $sent=0;
            public function to($recipient) { return $this; }
            public function send($message) { $this->sent++; return new \stdClass; }
        };
        Mail::swap($mail);
        $this->post(route('employees.account.store',$employee))->assertSessionHasNoErrors();
        $this->post(route('employees.account.store',$employee))->assertSessionHasNoErrors();
        $account=$employee->user()->firstOrFail();
        $this->assertSame(1,$mail->sent); $this->assertNull($account->password);
        $this->assertSame('pending_reset',$account->status); $this->assertTrue($account->hasRole(Role::EMPLOYEE));
        $this->assertSame(64,strlen($account->activation_token_hash));
        $this->assertFalse(str_contains(AuditLog::where('auditable_type',User::class)->get()->toJson(),$account->activation_token_hash));
    }
    public function test_failed_mail_preserves_clear_pending_state(): void
    {
        config(['mail.default'=>'smtp','mail.mailers.smtp.transport'=>'smtp']);
        $hr=$this->user(Role::HR); $employee=Employee::factory()->create(); $this->contract($employee,$hr)->update(['status'=>'approved']);
        Mail::swap(new class {
            public function to($recipient) { return $this; }
            public function send($message) { throw new \RuntimeException('simulated'); }
        });
        $this->actingAs($hr)->post(route('employees.account.store',$employee))->assertSessionHasErrors('invitation');
        $account=$employee->user()->firstOrFail();
        $this->assertSame('pending_reset',$account->status);
        $this->assertNull($account->password); $this->assertNull($account->activation_token_hash);
        $this->assertDatabaseHas('audit_logs',['action'=>'ACCOUNT_INVITATION_FAILED','auditable_id'=>$account->id]);
    }
    public function test_activation_is_single_use_and_hashes_password(): void
    {
        $token=Str::random(64);
        $user=User::factory()->create(['activation_token_hash'=>hash('sha256',$token),'activation_expires_at'=>now()->addDay()]);
        $this->get(route('account.activate',$token))->assertOk();
        $this->post(route('account.activate.submit',$token),['password'=>'Abcd123','password_confirmation'=>'Abcd123'])->assertSessionHasErrors('password');
        $this->assertSame('pending_reset',$user->fresh()->status);
        $this->assertNull($user->fresh()->password);
        $password='Abcd1234';
        $this->post(route('account.activate.submit',$token),['password'=>$password,'password_confirmation'=>$password])->assertRedirect('/login');
        $this->assertTrue(Hash::check($password,$user->fresh()->password));
        $this->assertSame('active',$user->fresh()->status); $this->assertNull($user->fresh()->activation_token_hash);
        $this->get(route('account.activate',$token))->assertGone();
        $this->assertFalse(str_contains(AuditLog::get()->toJson(),$password));
    }
    public function test_expired_activation_is_rejected(): void
    {
        $token=Str::random(64);
        User::factory()->create(['activation_token_hash'=>hash('sha256',$token),'activation_expires_at'=>now()->subMinute()]);
        $this->get(route('account.activate',$token))->assertGone();
    }
    public function test_activation_request_checks_token_before_password_validation(): void
    {
        $token=Str::random(64);
        User::factory()->create(['activation_token_hash'=>hash('sha256',$token),'activation_expires_at'=>now()->subMinute()]);
        $this->post(route('account.activate.submit',$token),['password'=>'short','password_confirmation'=>'short'])->assertGone();
    }
    public function test_activation_request_rejects_non_string_password_without_a_server_error(): void
    {
        $token=Str::random(64);
        $user=User::factory()->create(['activation_token_hash'=>hash('sha256',$token),'activation_expires_at'=>now()->addDay()]);
        $this->post(route('account.activate.submit',$token),['password'=>['unexpected'],'password_confirmation'=>['unexpected']])
            ->assertSessionHasErrors('password');
        $this->assertSame('pending_reset',$user->fresh()->status);
        $this->assertNull($user->fresh()->password);
    }
    public function test_attendance_uses_server_time_and_prevents_duplicate(): void
    {
        $user=$this->user();
        $this->travelTo(now()->setDate(2026,10,16)->setTime(8,15));
        $this->actingAs($user)->post(route('user.attendances.check-in'))->assertSessionHasNoErrors();
        $this->post(route('user.attendances.check-in'))->assertSessionHasErrors('attendance');
        $this->travelTo(now()->setTime(17,0));
        $this->post(route('user.attendances.check-out'))->assertSessionHasNoErrors();
        $row=Attendance::where('employee_id',$user->employee_id)->firstOrFail();
        $this->assertSame(465,$row->regular_minutes); $this->assertSame('0.9688',$row->work_units);
        $this->assertSame(15,$row->late_minutes); $this->travelBack();
    }
    public function test_payroll_approval_automatically_publishes_only_own_snapshot(): void
    {
        $hr=$this->user(Role::HR); $director=$this->user(Role::DIRECTOR); $worker=$this->user(); $other=$this->user();
        $worker->employee->update(['hire_date'=>'2026-10-01']);
        $contract=$this->contract($worker->employee,$hr); $contract->update(['status'=>'approved']);
        $period=PayrollPeriod::create(['year'=>2026,'month'=>10,'standard_work_days'=>22,'status'=>'draft','created_by'=>$hr->id]);
        for ($day=1;$day<=31;$day++) {
            $date=\Illuminate\Support\Carbon::create(2026,10,$day);
            if ($date->isWeekday()) {
                Attendance::create(['employee_id'=>$worker->employee_id,'work_date'=>$date,'regular_minutes'=>480,'work_units'=>'1.0000',
                    'status'=>'adjusted','adjustment_reason'=>'Công hưởng lương đã xác nhận','adjusted_by'=>$hr->id,'overtime_status'=>'none']);
            }
        }
        $this->actingAs($hr)->post(route('payrolls.calculate',$period),['attendance_confirmed'=>1,
            'adjustments'=>[$worker->employee_id=>['deduction'=>'0','note'=>'Đã đối chiếu']]])->assertSessionHasNoErrors();
        $detail=$period->details()->firstOrFail();
        $this->assertSame('22000000.00',$detail->net_salary);
        $this->actingAs($worker)->get(route('user.payrolls'))->assertDontSee($contract->contract_code);
        $this->actingAs($hr)->post(route('payrolls.submit',$period))->assertSessionHasNoErrors();
        $this->actingAs($director)->post(route('payrolls.approve',$period))->assertSessionHasNoErrors();
        $this->assertSame('published',$period->fresh()->status); $this->assertNotNull($period->fresh()->published_at);
        $this->actingAs($worker)->get(route('user.payrolls'))->assertSee($contract->contract_code);
        $this->actingAs($other)->get(route('user.payrolls'))->assertDontSee($contract->contract_code);
        $this->actingAs($hr)->post(route('payrolls.calculate',$period),['attendance_confirmed'=>1,
            'adjustments'=>[$worker->employee_id=>['deduction'=>'0']]])->assertSessionHasErrors('payroll');
    }
    public function test_missing_attendance_is_not_silently_zero_paid(): void
    {
        $hr=$this->user(Role::HR); $worker=$this->user();
        $worker->employee->update(['hire_date'=>'2026-10-01']);
        $this->contract($worker->employee,$hr)->update(['status'=>'approved']);
        $period=PayrollPeriod::create(['year'=>2026,'month'=>10,'standard_work_days'=>22,'status'=>'draft','created_by'=>$hr->id]);
        $this->actingAs($hr)->post(route('payrolls.calculate',$period),['attendance_confirmed'=>1,
            'adjustments'=>[$worker->employee_id=>['deduction'=>'0']]])->assertSessionHasErrors('payroll');
        $this->assertSame(0,$period->details()->count());
    }
    public function test_schedule_cannot_change_after_attendance_exists(): void
    {
        $hr=$this->user(Role::HR); $employee=Employee::factory()->create();
        Attendance::create(['employee_id'=>$employee->id,'work_date'=>'2026-10-01','status'=>'adjusted',
            'regular_minutes'=>480,'work_units'=>1,'adjustment_reason'=>'Nghỉ phép hưởng lương','adjusted_by'=>$hr->id]);
        $schedule=$employee->workSchedule;
        $payload=$schedule->only(['name','late_grace_minutes','work_days','status']);
        $payload+=['start_time'=>'08:30','end_time'=>'17:00','break_start'=>'12:00','break_end'=>'13:00'];
        $this->actingAs($hr)->put(route('schedules.update',$schedule),$payload)->assertSessionHasErrors('schedule');
        $this->assertSame('08:00:00',$schedule->fresh()->start_time);
    }
    public function test_hr_cannot_bypass_activation_or_raise_role(): void
    {
        $hr=$this->user(Role::HR); $employee=Employee::factory()->create();
        $this->actingAs($hr)->post(route('employees.account.store',$employee),[
            'role_id'=>Role::where('name',Role::DIRECTOR)->firstOrFail()->id,'password'=>'ClientMustNotChoose',
        ])->assertSessionHasErrors(['role_id','password']);
        $this->assertNull($employee->user()->first());
    }
    public function test_hr_adjustment_requires_reason_and_payroll_rejection_requires_reason(): void
    {
        $hr=$this->user(Role::HR); $director=$this->user(Role::DIRECTOR); $employee=Employee::factory()->create();
        $this->actingAs($hr)->post(route('attendances.store'),['employee_id'=>$employee->id,'work_date'=>'2026-10-01',
            'regular_minutes'=>480,'late_minutes'=>0,'overtime_minutes'=>0,'overtime_status'=>'none'
        ])->assertSessionHasErrors('adjustment_reason');
        $period=PayrollPeriod::create(['year'=>2026,'month'=>10,'standard_work_days'=>22,'status'=>'pending_approval','created_by'=>$hr->id]);
        $this->actingAs($director)->post(route('payrolls.reject',$period))->assertSessionHasErrors('rejection_reason');
        $this->assertSame('pending_approval',$period->fresh()->status);
    }
}
