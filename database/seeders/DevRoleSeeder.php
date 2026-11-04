<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('devRoles')->insert([
            'roleName' => 'Web Developer',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('devRoles')->insert([
            'roleName' => 'Desktop Developer',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('devRoles')->insert([
            'roleName' => 'Mobile Developer',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
