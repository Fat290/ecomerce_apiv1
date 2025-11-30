<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sellers = User::where('role', 'seller')->get();

        if ($sellers->isEmpty()) {
            $this->command->warn('No sellers found. Please run UserSeeder first.');
            return;
        }

        $shops = [
            [
                'name' => 'Tech World',
                'description' => 'Your one-stop shop for all electronics and gadgets',
                'address' => '123 Tech Street, Silicon Valley, CA 94000',
                'rating' => 4.5,
                'status' => 'active',
            ],
            [
                'name' => 'Fashion Hub',
                'description' => 'Trendy clothing and accessories for everyone',
                'address' => '456 Fashion Avenue, New York, NY 10001',
                'rating' => 4.8,
                'status' => 'active',
            ],
            [
                'name' => 'Home Essentials',
                'description' => 'Everything you need for your home',
                'address' => '789 Home Road, Los Angeles, CA 90001',
                'rating' => 4.3,
                'status' => 'active',
            ],
        ];

        foreach ($shops as $index => $shopData) {
            if (isset($sellers[$index])) {
                Shop::create([
                    'owner_id' => $sellers[$index]->id,
                    'name' => $shopData['name'],
                    'description' => $shopData['description'],
                    'address' => $shopData['address'],
                    'rating' => $shopData['rating'],
                    'status' => $shopData['status'],
                ]);
            }
        }
    }
}
