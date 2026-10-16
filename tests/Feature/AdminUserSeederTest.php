<?php

namespace Tests\Feature;

use App\Models\{Employee, Role, User};
use Database\Seeders\{AdminUserSeeder, DatabaseSeeder};
use Illuminate\Support\Facades\{DB, Hash, Schema};
use RuntimeException;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    private bool $transactionStarted = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (!app()->environment('testing') || !preg_match('/(_test|_testing)$/D', DB::connection()->getDatabaseName())
            || !in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('Use an isolated MySQL/MariaDB test database; no migrations or resets are run here.');
        }
        if (!Schema::hasTable('work_schedules') || !Schema::hasTable('users')) {
            $this->markTestSkipped('Prepare the current schema, including the Admin role migration.');
        }
        if (User::whereIn('email', ['admin@hrm.com', 'director@hrm.com', 'hr@hrm.com', 'nv1@hrm.com'])->exists()
            || Employee::whereIn('personal_email', ['admin@hrm.com', 'director@hrm.com', 'hr@hrm.com', 'nv1@hrm.com'])->exists()
            || Employee::whereIn('employee_code', ['BOOT-DIRECTOR', 'BOOT-HR'])->exists()) {
            $this->markTestSkipped('Use a test database without the four demo accounts.');
        }
        DB::beginTransaction();
        $this->transactionStarted = true;
    }

    protected function tearDown(): void
    {
        if ($this->transactionStarted) { DB::rollBack(); }
        parent::tearDown();
    }

    public function test_admin_is_active_and_can_log_in_with_the_requested_credentials(): void
    {
        app(AdminUserSeeder::class)->run();
        $admin = User::where('email', 'admin@hrm.com')->firstOrFail();
        $this->assertTrue($admin->hasRole(Role::ADMIN));
        $this->assertSame('active', $admin->status);
        $this->assertNotNull($admin->activated_at);
        $this->assertNull($admin->activation_token_hash);
        $this->assertTrue(Hash::check('Admin1234', $admin->password));
        $this->assertNotSame('Admin1234', $admin->password);
        $this->assertSame('working', $admin->employee->employment_status);
        $this->assertMatchesRegularExpression('/^NV[0-9]{3}$/D', $admin->employee->employee_code);
        $this->post('/login', ['email' => 'admin@hrm.com', 'password' => 'Admin1234'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_reseeding_restores_the_admin_without_creating_duplicate_profiles(): void
    {
        app(AdminUserSeeder::class)->run();
        $admin = User::where('email', 'admin@hrm.com')->firstOrFail();
        $id = $admin->id;
        $employeeId = $admin->employee_id;
        $count = Employee::count();
        $admin->forceFill([
            'status' => 'locked', 'password' => Hash::make('Previous123'), 'activated_at' => null,
            'activation_token_hash' => hash('sha256', 'expired'), 'activation_expires_at' => now(),
            'remember_token' => 'old-token',
        ])->save();
        app(AdminUserSeeder::class)->run();
        $admin->refresh();
        $this->assertSame($id, $admin->id);
        $this->assertSame($employeeId, $admin->employee_id);
        $this->assertSame($count, Employee::count());
        $this->assertSame(1, User::where('email', 'admin@hrm.com')->count());
        $this->assertSame('active', $admin->status);
        $this->assertNotNull($admin->activated_at);
        $this->assertNull($admin->activation_token_hash);
        $this->assertNull($admin->activation_expires_at);
        $this->assertNull($admin->remember_token);
        $this->assertTrue(Hash::check('Admin1234', $admin->password));
    }

    public function test_default_seeder_works_after_admin_was_seeded_first(): void
    {
        app(AdminUserSeeder::class)->run();
        $adminEmployeeId = User::where('email', 'admin@hrm.com')->firstOrFail()->employee_id;
        app(DatabaseSeeder::class)->run();
        app(DatabaseSeeder::class)->run();
        $accounts = User::whereIn('email', ['admin@hrm.com', 'director@hrm.com', 'hr@hrm.com', 'nv1@hrm.com'])->get();
        $this->assertCount(4, $accounts);
        $this->assertCount(4, $accounts->pluck('employee_id')->unique());
        $this->assertSame($adminEmployeeId, $accounts->firstWhere('email', 'admin@hrm.com')->employee_id);
        $this->assertTrue($accounts->every(fn ($user) => $user->status === 'active'));
    }

    public function test_seeder_does_not_take_over_an_existing_employee_account(): void
    {
        $user = User::factory()->active('Existing123')->create(['email' => 'admin@hrm.com']);
        $hash = $user->password;
        try {
            app(AdminUserSeeder::class)->run();
            $this->fail('An unrelated account must not be promoted to Admin.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('vai trò khác', $exception->getMessage());
        }
        $this->assertTrue($user->fresh()->hasRole(Role::EMPLOYEE));
        $this->assertSame($hash, $user->fresh()->password);
    }
}
