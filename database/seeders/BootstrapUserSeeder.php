<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BootstrapUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            EmployeeSeeder::class,
        ]);
    }

    protected function seedAccounts(array $roles): void
    {
        $this->run();
    }
}
