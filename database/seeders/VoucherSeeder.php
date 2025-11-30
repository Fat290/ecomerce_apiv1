<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shops = Shop::all();

        if ($shops->isEmpty()) {
            $this->command->warn('No shops found. Please run ShopSeeder first.');
            return;
        }

        foreach ($shops as $shop) {
            // Create percentage discount voucher
            Voucher::create([
                'code' => strtoupper(substr($shop->name, 0, 3)) . '10OFF',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'min_order_value' => 50.00,
                'shop_id' => $shop->id,
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
                'status' => 'active',
            ]);

            // Create fixed amount discount voucher
            Voucher::create([
                'code' => strtoupper(substr($shop->name, 0, 3)) . '20USD',
                'discount_type' => 'amount',
                'discount_value' => 20.00,
                'min_order_value' => 100.00,
                'shop_id' => $shop->id,
                'start_date' => now(),
                'end_date' => now()->addMonths(2),
                'status' => 'active',
            ]);
        }
    }
}
