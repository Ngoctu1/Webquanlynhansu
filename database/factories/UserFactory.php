<?php

namespace Database\Factories;

use App\Models\{Employee, Role};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'role_id' => fn () => Role::firstOrCreate(['name' => Role::EMPLOYEE], ['description' => 'Nhân viên'])->id,
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'status' => 'active',
            'activation_token' => null,
            'token_expires_at' => null,
            'last_login_at' => null,
        ];
    }

    public function active(string $password = 'password'): static
    {
        return $this->state(fn () => [
            'password' => Hash::make($password),
            'status' => 'active',
            'activation_token' => null,
            'token_expires_at' => null,
        ]);
    }

    public function pendingReset(): static
    {
        return $this->state(fn () => [
            'password' => null,
            'status' => 'pending_reset',
            'activation_token' => hash('sha256', fake()->uuid()),
            'token_expires_at' => now()->addDays(7),
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn () => [
            'status' => 'locked',
        ]);
    }
}
