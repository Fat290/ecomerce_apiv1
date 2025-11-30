<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyers = User::where('role', 'buyer')->get();
        $shops = Shop::all();

        if ($buyers->isEmpty() || $shops->isEmpty()) {
            $this->command->warn('No buyers or shops found. Please run UserSeeder and ShopSeeder first.');
            return;
        }

        $statuses = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
        $paymentMethods = ['credit_card', 'debit_card', 'paypal', 'bank_transfer', 'cash_on_delivery'];

        foreach ($buyers as $buyer) {
            // Create 2-5 orders per buyer
            $orderCount = rand(2, 5);

            for ($i = 0; $i < $orderCount; $i++) {
                $shop = $shops->random();
                $products = Product::where('shop_id', $shop->id)->inRandomOrder()->limit(rand(1, 3))->get();

                if ($products->isEmpty()) {
                    continue;
                }

                $items = [];
                $totalAmount = 0;

                foreach ($products as $product) {
                    $qty = rand(1, 3);
                    $items[] = [
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'price' => $product->price,
                    ];
                    $totalAmount += $product->price * $qty;
                }

                $shippingFee = rand(5, 20);
                $status = $statuses[array_rand($statuses)];

                Order::create([
                    'buyer_id' => $buyer->id,
                    'shop_id' => $shop->id,
                    'items' => $items,
                    'total_amount' => $totalAmount + $shippingFee,
                    'shipping_fee' => $shippingFee,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'status' => $status,
                    'shipping_address' => [
                        'street' => '123 Shipping Street',
                        'city' => 'Shipping City',
                        'state' => 'Shipping State',
                        'zip' => '12345',
                        'country' => 'USA',
                    ],
                ]);
            }
        }
    }
}
