<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        User::create([
            'role' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345678'),
            'phone' => '+1234567890',
            'status' => 'active',
            'address' => [
                'street' => '123 Admin Street',
                'city' => 'Admin City',
                'state' => 'Admin State',
                'zip' => '12345',
                'country' => 'USA',
            ],
        ]);
    }
}
