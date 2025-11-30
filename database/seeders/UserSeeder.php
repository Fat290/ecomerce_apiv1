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
            'password' => Hash::make('password123'),
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

        // Create Sellers
        $sellers = [
            [
                'name' => 'John Seller',
                'email' => 'seller1@example.com',
                'phone' => '+1234567891',
            ],
            [
                'name' => 'Jane Merchant',
                'email' => 'seller2@example.com',
                'phone' => '+1234567892',
            ],
            [
                'name' => 'Bob Store Owner',
                'email' => 'seller3@example.com',
                'phone' => '+1234567893',
            ],
        ];

        foreach ($sellers as $seller) {
            User::create([
                'role' => 'seller',
                'name' => $seller['name'],
                'email' => $seller['email'],
                'password' => Hash::make('password123'),
                'phone' => $seller['phone'],
                'status' => 'active',
                'address' => [
                    'street' => '123 Seller Street',
                    'city' => 'Seller City',
                    'state' => 'Seller State',
                    'zip' => '12345',
                    'country' => 'USA',
                ],
            ]);
        }

        // Create Buyers
        $buyers = [
            ['name' => 'Alice Buyer', 'email' => 'buyer1@example.com', 'phone' => '+1234567901'],
            ['name' => 'Charlie Customer', 'email' => 'buyer2@example.com', 'phone' => '+1234567902'],
            ['name' => 'Diana Shopper', 'email' => 'buyer3@example.com', 'phone' => '+1234567903'],
            ['name' => 'Eve Consumer', 'email' => 'buyer4@example.com', 'phone' => '+1234567904'],
            ['name' => 'Frank Buyer', 'email' => 'buyer5@example.com', 'phone' => '+1234567905'],
        ];

        foreach ($buyers as $buyer) {
            User::create([
                'role' => 'buyer',
                'name' => $buyer['name'],
                'email' => $buyer['email'],
                'password' => Hash::make('password123'),
                'phone' => $buyer['phone'],
                'status' => 'active',
                'address' => [
                    'street' => '123 Buyer Street',
                    'city' => 'Buyer City',
                    'state' => 'Buyer State',
                    'zip' => '12345',
                    'country' => 'USA',
                ],
            ]);
        }
    }
}
