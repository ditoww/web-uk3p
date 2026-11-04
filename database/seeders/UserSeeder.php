<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SEED_ADMIN_PASSWORD');

        if (!$password || strlen($password) < 8) {
            throw new \RuntimeException(
                'Isi SEED_ADMIN_PASSWORD di .env (minimal 8 karakter) sebelum menjalankan seeder.'
            );
        }

        $users = [
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'alditochristianputra@gmail.com',
                'password' => Hash::make($password),
                'privLevel' => 'super_admin',
                'phone_number' => '08xxxxxxxxxx',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }
    }
}