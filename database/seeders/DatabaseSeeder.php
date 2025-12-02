<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            ShopSeeder::class,
            ProductSeeder::class,

            ReviewSeeder::class,

            AnalyticsSeeder::class,
        ]);
    }
}
