<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([ //TODO: Replace with actual workers in the future, and put in env.
            'name' => "Aaron Haydes",
            'username' => "Aaron",
            'email' => "Aaron.esteban@gmail.com",
            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD_ONE')),
            'privLevel' => "super_admin",
            'phone_number' => "08123456789",
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
