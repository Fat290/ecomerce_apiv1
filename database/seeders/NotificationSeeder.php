<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $orders = Order::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Create order notifications
        foreach ($orders->take(10) as $order) {
            Notification::create([
                'user_id' => $order->buyer_id,
                'title' => 'Order Status Update',
                'message' => "Your order #{$order->id} status has been updated to {$order->status}.",
                'type' => 'order',
                'is_read' => rand(0, 1) === 1,
            ]);
        }

        // Create promotion notifications
        foreach ($users->random(min(5, $users->count())) as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Special Promotion',
                'message' => 'Get 20% off on all electronics this weekend!',
                'type' => 'promotion',
                'is_read' => false,
            ]);
        }

        // Create system notifications
        foreach ($users->random(min(3, $users->count())) as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'System Update',
                'message' => 'We have updated our terms and conditions. Please review them.',
                'type' => 'system',
                'is_read' => false,
            ]);
        }
    }
}
