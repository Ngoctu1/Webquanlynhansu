<?php

namespace Tests\Feature;

use App\Models\{Employee, Role, User};
use Database\Seeders\{BootstrapUserSeeder, DirectorUserSeeder};
use Illuminate\Support\Facades\{DB, Hash, Schema};
use RuntimeException;
use Tests\TestCase;

class DirectorUserSeederTest extends TestCase
{
    private bool $transactionStarted = false;
    private const ACCOUNTS = [
        'director@hrm.com' => ['Abcd1234', Role::DIRECTOR, 'BOOT-DIRECTOR', 'DIRECTOR', '/admin'],
        'hr@hrm.com' => ['aBcd1234', Role::HR, 'BOOT-HR', 'HEAD', '/admin'],
        'nv1@hrm.com' => ['abCd1234', Role::EMPLOYEE, 'NV001', 'STAFF', '/user'],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        if (!app()->environment('testing') || !preg_match('/(_test|_testing)$/D', DB::connection()->getDatabaseName())
            || !in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('Use an isolated MySQL/MariaDB test database; this test never migrates or resets it.');
        }
        if (!Schema::hasTable('work_schedules') || !Schema::hasTable('users')) {
            $this->markTestSkipped('Prepare the current schema first.');
        }
        if (Employee::whereIn('employee_code', ['BOOT-DIRECTOR', 'BOOT-HR', 'NV001'])->exists()
            || Employee::whereIn('personal_email', array_keys(self::ACCOUNTS))->exists()
            || User::whereIn('email', array_keys(self::ACCOUNTS))->exists()) {
            $this->markTestSkipped('Use a test database without the three demo accounts to test their creation.');
        }
        DB::beginTransaction();
        $this->transactionStarted = true;
    }

    protected function tearDown(): void
    {
        if ($this->transactionStarted) { DB::rollBack(); }
        parent::tearDown();
    }

    public function test_all_three_seeded_accounts_can_log_in_without_invitations(): void
    {
        app(BootstrapUserSeeder::class)->run();

        foreach (self::ACCOUNTS as $email => [$password, $role, $code, $position, $path]) {
            $user = User::where('email', $email)->firstOrFail();
            $this->assertTrue($user->hasRole($role));
            $this->assertSame('active', $user->status);
            $this->assertNotNull($user->activated_at);
            $this->assertNull($user->activation_token_hash);
            $this->assertNull($user->activation_expires_at);
            $this->assertNotSame($password, $user->password);
            $this->assertTrue(Hash::check($password, $user->password));
            $this->assertSame($code, $user->employee->employee_code);
            $this->assertSame($position, $user->employee->position->code);
            $this->assertSame('working', $user->employee->employment_status);
            $this->assertSame($email, $user->employee->personal_email);
            $this->assertSame('active', $user->employee->department->status);
            $this->assertSame('active', $user->employee->position->status);
            $this->assertSame('active', $user->employee->workSchedule->status);

            $this->post('/login', ['email' => $email, 'password' => $password])->assertRedirect($path);
            $this->assertAuthenticatedAs($user);
            $this->post('/logout')->assertRedirect('/login');
            $this->assertGuest();
        }
    }

    public function test_repeated_seed_restores_credentials_and_activation_without_duplicates(): void
    {
        app(BootstrapUserSeeder::class)->run();
        $ids = User::whereIn('email', array_keys(self::ACCOUNTS))->orderBy('id')->pluck('id')->all();
        $employeeCount = Employee::count();
        $userCount = User::count();
        $user = User::where('email', 'hr@hrm.com')->firstOrFail();
        $employeeId = $user->employee_id;
        $user->forceFill([
            'password' => Hash::make('Different123'), 'status' => 'locked', 'activated_at' => null,
            'activation_token_hash' => hash('sha256', 'old-invitation'),
            'activation_expires_at' => now()->addDay(), 'remember_token' => 'old-remember-token',
        ])->save();
        $user->employee->update(['employment_status' => 'on_leave']);

        app(BootstrapUserSeeder::class)->run();

        $this->assertSame($ids, User::whereIn('email', array_keys(self::ACCOUNTS))->orderBy('id')->pluck('id')->all());
        $this->assertSame($employeeCount, Employee::count());
        $this->assertSame($userCount, User::count());
        $user->refresh();
        $this->assertSame($employeeId, $user->employee_id);
        $this->assertTrue(Hash::check('aBcd1234', $user->password));
        $this->assertSame('active', $user->status);
        $this->assertNotNull($user->activated_at);
        $this->assertNull($user->activation_token_hash);
        $this->assertNull($user->activation_expires_at);
        $this->assertNull($user->remember_token);
        $this->assertSame('working', $user->employee->employment_status);
    }

    public function test_director_seeder_runs_independently_and_updates_the_old_seeded_email(): void
    {
        app(DirectorUserSeeder::class)->run();
        $user = User::where('email', 'director@hrm.com')->firstOrFail();
        $id = $user->id;
        $employeeId = $user->employee_id;
        $user->update(['email' => 'director@hrm.test', 'password' => Hash::make('Previous123')]);
        $user->employee->update(['personal_email' => 'director@hrm.test']);

        app(DirectorUserSeeder::class)->run();

        $user->refresh();
        $this->assertSame($id, $user->id);
        $this->assertSame($employeeId, $user->employee_id);
        $this->assertSame('director@hrm.com', $user->email);
        $this->assertSame('director@hrm.com', $user->employee->personal_email);
        $this->assertTrue(Hash::check('Abcd1234', $user->password));
        $this->assertSame(1, Employee::where('employee_code', 'BOOT-DIRECTOR')->count());
        $this->assertFalse(User::whereIn('email', ['director@hrm.test', 'hr@hrm.com', 'nv1@hrm.com'])->exists());
    }

    public function test_email_collision_rolls_back_the_entire_batch_without_promoting_another_user(): void
    {
        $existing = User::factory()->active('Existing123')->create(['email' => 'hr@hrm.com']);
        $hash = $existing->password;

        try {
            app(BootstrapUserSeeder::class)->run();
            $this->fail('A duplicate email must not take over an unrelated account.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('hồ sơ khác', $exception->getMessage());
        }

        $this->assertTrue($existing->fresh()->hasRole(Role::EMPLOYEE));
        $this->assertSame($hash, $existing->fresh()->password);
        $this->assertFalse(User::whereIn('email', ['director@hrm.com', 'nv1@hrm.com'])->exists());
        $this->assertFalse(Employee::whereIn('employee_code', ['BOOT-DIRECTOR', 'BOOT-HR', 'NV001'])->exists());
    }
}