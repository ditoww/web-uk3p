<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $users = [];

        // Akun produksi: diisi lewat .env (SEED_ADMIN_EMAIL, SEED_ADMIN_PASSWORD, dst.)
        if (env('SEED_ADMIN_EMAIL') && env('SEED_ADMIN_PASSWORD')) {
            $users[] = [
                'name' => env('SEED_ADMIN_NAME', 'Super Admin UKKKP'),
                'username' => env('SEED_ADMIN_USERNAME', 'superadmin'),
                'email' => env('SEED_ADMIN_EMAIL'),
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD')),
                'privLevel' => 'super_admin',
                'phone_number' => env('SEED_ADMIN_PHONE', '081200000000'),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Akun uji: hanya di lingkungan lokal
        if (app()->environment('local')) {
            foreach ([
                ['Super Admin Uji', 'superadmin_test', 'superadmin@ukkkp.test', 'super_admin', '081234567890'],
                ['Admin Uji',       'admin1',          'admin1@ukkkp.test',      'admin',       '081234567891'],
            ] as [$name, $username, $email, $role, $phone]) {
                $users[] = [
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make('123'),
                    'privLevel' => $role,
                    'phone_number' => $phone,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(['email' => $user['email']], $user);
        }
    }
}