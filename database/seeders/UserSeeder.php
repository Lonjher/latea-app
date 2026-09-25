<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role_id' => 1,
            ],
            [
                'name' => 'Cashier One',
                'email' => 'cashier1@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role_id' => 2,
                'store_id' => 1,
            ],
            [
                'name' => 'Cashier Two',
                'email' => 'cashier2@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role_id' => 2,
                'store_id' => 2,
            ],
            [
                'name' => 'Cashier Three',
                'email' => 'cashier3@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role_id' => 2,
                'store_id' => 3,
            ],
        ];

        foreach ($users as $user) {
            \App\Models\User::create($user);
        }
    }
}
