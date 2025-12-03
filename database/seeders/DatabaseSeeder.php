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
            BusinessTypeSeeder::class,
            UserSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,

            ProductSeeder::class,

            ReviewSeeder::class,

            AnalyticsSeeder::class,
        ]);
    }
}
