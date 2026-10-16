<?php
namespace Database\Factories;

use App\Models\{Employee, Role};
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'role_id' => fn () => Role::firstOrCreate(['name' => Role::EMPLOYEE])->id,
            'email' => fake()->unique()->safeEmail(),
            'password' => null, 'status' => 'pending_reset',
        ];
    }

    public function active(string $password): static
    {
        return $this->state(fn () => ['password' => $password, 'status' => 'active', 'activated_at' => now()]);
    }
}
