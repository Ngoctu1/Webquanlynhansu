<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class HrmDemoSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            EmployeeSeeder::class,
        ]);
    }
}
