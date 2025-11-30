<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyers = User::where('role', 'buyer')->get();
        $shops = Shop::with('owner')->get();

        if ($buyers->isEmpty() || $shops->isEmpty()) {
            $this->command->warn('No buyers or shops found. Please run UserSeeder and ShopSeeder first.');
            return;
        }

        $messages = [
            ['sender' => 'buyer', 'message' => 'Hello, I have a question about this product.', 'timestamp' => now()->subHours(2)],
            ['sender' => 'seller', 'message' => 'Hi! How can I help you?', 'timestamp' => now()->subHours(1)->subMinutes(50)],
            ['sender' => 'buyer', 'message' => 'Is this product in stock?', 'timestamp' => now()->subHours(1)->subMinutes(40)],
            ['sender' => 'seller', 'message' => 'Yes, we have it in stock. Would you like to place an order?', 'timestamp' => now()->subHours(1)->subMinutes(30)],
        ];

        // Create chats between buyers and shop owners
        foreach ($buyers->random(min(5, $buyers->count())) as $buyer) {
            $shop = $shops->random();
            $seller = $shop->owner;

            Chat::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'messages' => $messages,
                'last_message' => 'Yes, we have it in stock. Would you like to place an order?',
            ]);
        }
    }
}
