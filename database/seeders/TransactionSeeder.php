<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::all();
        $users = User::all();

        if ($orders->isEmpty()) {
            $this->command->warn('No orders found. Please run OrderSeeder first.');
            return;
        }

        $methods = ['credit_card', 'debit_card', 'paypal', 'bank_transfer'];
        $statuses = ['success', 'pending', 'failed'];

        // Create transactions for orders
        foreach ($orders->take(15) as $order) {
            Transaction::create([
                'user_id' => $order->buyer_id,
                'order_id' => $order->id,
                'type' => 'purchase',
                'amount' => $order->total_amount,
                'method' => $methods[array_rand($methods)],
                'status' => $statuses[array_rand($statuses)],
            ]);
        }

        // Create some refund transactions
        foreach ($orders->where('status', 'cancelled')->take(3) as $order) {
            Transaction::create([
                'user_id' => $order->buyer_id,
                'order_id' => $order->id,
                'type' => 'refund',
                'amount' => $order->total_amount,
                'method' => 'original_payment_method',
                'status' => 'success',
            ]);
        }

        // Create withdraw transactions for sellers
        $sellers = User::where('role', 'seller')->get();
        foreach ($sellers->take(2) as $seller) {
            Transaction::create([
                'user_id' => $seller->id,
                'order_id' => null,
                'type' => 'withdraw',
                'amount' => rand(100, 1000),
                'method' => 'bank_transfer',
                'status' => 'pending',
            ]);
        }
    }
}
