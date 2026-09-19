<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    DB::table('roles')->insert([
    ['id' => 1, 'name' => 'Admin'],
    ['id' => 2, 'name' => 'HR'],
    ['id' => 3, 'name' => 'Employee'],
    ]);

    DB::table('users')->insert([
    'username' => 'admin',
    'password' => bcrypt('123456'),
    'role_id'  => 1, // Đảm bảo role_id = 1 đã tồn tại ở trên
    'status'   => 1,
    ]);
}
}
