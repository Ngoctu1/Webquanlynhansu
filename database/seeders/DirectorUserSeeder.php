<?php

namespace Database\Seeders;

use App\Models\Role;

class DirectorUserSeeder extends BootstrapUserSeeder
{
    public function run(): void
    {
        $this->seedAccounts([Role::DIRECTOR]);
    }
}