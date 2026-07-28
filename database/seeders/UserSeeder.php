<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
<<<<<<< Updated upstream
        $users = [
            [
                'name' => "Aaron Haydes",
                'username' => "Aaron",
                'email' => "Aaron.esteban@gmail.com",
                'password' => Hash::make('123'),
                'privLevel' => "super_admin",
                'phone_number' => "08123456789",
=======
        DB::table('users')->insert([
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'bag12dwi.s@gmail.com',
                'password' => Hash::make('123'),
                'privLevel' => 'super_admin',
                'phone_number' => null,
>>>>>>> Stashed changes
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
<<<<<<< Updated upstream
                'name' => "Bagus Dwi (Super Admin)",
                'username' => "bagus_superadmin",
                'email' => "bag12dwi.s@gmail.com",
                'password' => Hash::make('123'),
                'privLevel' => "super_admin",
                'phone_number' => "081234567890",
=======
                'name' => 'Admin 1',
                'username' => 'admin1',
                'email' => 'admin1@ticketing.dev',
                'password' => Hash::make('123'),
                'privLevel' => 'admin',
                'phone_number' => null,
>>>>>>> Stashed changes
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
<<<<<<< Updated upstream
                'name' => "Admin 1",
                'username' => "admin1",
                'email' => "admin1@ticketing.dev",
                'password' => Hash::make('123'),
                'privLevel' => "admin",
                'phone_number' => "081234567891",
=======
                'name' => 'Developer 1',
                'username' => 'dev1',
                'email' => 'dev1@ticketing.dev',
                'password' => Hash::make('123'),
                'privLevel' => 'developer',
                'phone_number' => null,
>>>>>>> Stashed changes
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
<<<<<<< Updated upstream
                'name' => "Developer 1",
                'username' => "dev1",
                'email' => "dev1@ticketing.dev",
                'password' => Hash::make('123'),
                'privLevel' => "developer",
                'phone_number' => "081234567892",
=======
                'name' => 'SPV',
                'username' => 'spv',
                'email' => 'spv@ticketing.dev',
                'password' => Hash::make('123'),
                'privLevel' => 'spv',
                'phone_number' => null,
>>>>>>> Stashed changes
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
<<<<<<< Updated upstream
                'name' => "Supervisor",
                'username' => "spv",
                'email' => "spv@ticketing.dev",
                'password' => Hash::make('123'),
                'privLevel' => "spv",
                'phone_number' => "081234567893",
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => "Support 1",
                'username' => "support1",
                'email' => "support1@ticketing.dev",
                'password' => Hash::make('123'),
                'privLevel' => "support",
                'phone_number' => "081234567894",
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }
=======
                'name' => 'Support 1',
                'username' => 'support1',
                'email' => 'support1@ticketing.dev',
                'password' => Hash::make('123'),
                'privLevel' => 'support',
                'phone_number' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
>>>>>>> Stashed changes
    }
}