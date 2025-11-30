<?php

namespace Database\Seeders;

use App\Models\Analytics;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create analytics for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays($i);

            // Get actual data for that date if available
            $ordersOnDate = Order::whereDate('created_at', $date)->get();
            $usersOnDate = User::whereDate('created_at', $date)->get();

            $totalOrders = $ordersOnDate->count();
            $totalRevenue = $ordersOnDate->sum('total_amount');
            $newUsers = $usersOnDate->count();

            // Get top products (by sold_count)
            $topProducts = Product::orderBy('sold_count', 'desc')
                ->limit(5)
                ->pluck('id')
                ->toArray();

            Analytics::create([
                'date' => $date->format('Y-m-d'),
                'total_orders' => $totalOrders > 0 ? $totalOrders : rand(10, 100),
                'total_revenue' => $totalRevenue > 0 ? $totalRevenue : rand(1000, 10000),
                'new_users' => $newUsers > 0 ? $newUsers : rand(0, 20),
                'top_products' => $topProducts,
            ]);
        }
    }
}
