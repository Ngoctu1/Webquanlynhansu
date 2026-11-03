<?php

namespace Database\Seeders;

use App\Models\{Role, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public const EMAIL = 'admin@company.com';
    private const PASSWORD = 'password';

    public function run(): void
    {
        $this->call([RoleSeeder::class]);

        $role = Role::where('name', Role::ADMIN)->firstOrFail();

        User::updateOrCreate(
            ['email' => self::EMAIL],
            [
                'employee_id' => null,
                'role_id' => $role->id,
                'password' => Hash::make(self::PASSWORD),
                'status' => 'active',
                'activation_token' => null,
                'token_expires_at' => null,
            ]
        );

        $this->command?->info('Đã cập nhật tài khoản Admin: ' . self::EMAIL);
    }
}
