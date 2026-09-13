<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@rsifatimah.com'],
            [
                'name' => 'Tim SDI RSI Fatimah Banyuwangi',
                'role' => User::ROLE_ADMIN,
                'password' => Hash::make('password123'),
                'phone' => '081234567890',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
