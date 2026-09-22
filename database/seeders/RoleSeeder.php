<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();

        DB::table('roles')->upsert([
            ['id' => 1, 'name' => 'Admin', 'description' => 'Admin quản trị hệ thống', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['id' => 2, 'name' => 'HR', 'description' => 'Nhân sự', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['id' => 3, 'name' => 'Employee', 'description' => 'Nhân viên', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ], ['id'], ['name', 'description', 'updated_at']);
    }
}
