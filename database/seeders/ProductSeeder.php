<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shops = Shop::all();
        $categories = Category::whereNotNull('parent_id')->get();
        if ($shops->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Missing required data. Please run ShopSeeder and CategorySeeder first.');
            return;
        }

        $products = [
            // Electronics
            ['name' => 'iPhone 15 Pro', 'price' => 999.99, 'stock' => 50, 'category' => 'Smartphones'],
            ['name' => 'Samsung Galaxy S24', 'price' => 899.99, 'stock' => 40, 'category' => 'Smartphones'],
            ['name' => 'MacBook Pro 16"', 'price' => 2499.99, 'stock' => 20, 'category' => 'Laptops'],
            ['name' => 'Dell XPS 15', 'price' => 1799.99, 'stock' => 30, 'category' => 'Laptops'],
            ['name' => 'Sony WH-1000XM5', 'price' => 399.99, 'stock' => 60, 'category' => 'Headphones'],
            ['name' => 'Canon EOS R5', 'price' => 3899.99, 'stock' => 15, 'category' => 'Cameras'],
            ['name' => 'LG OLED TV 65"', 'price' => 1999.99, 'stock' => 25, 'category' => 'TVs'],

            // Clothing
            ['name' => 'Nike Air Max 270', 'price' => 150.00, 'stock' => 100, 'category' => 'Shoes'],
            ['name' => 'Adidas Ultraboost 22', 'price' => 180.00, 'stock' => 80, 'category' => 'Shoes'],
            ['name' => 'Men\'s Casual T-Shirt', 'price' => 29.99, 'stock' => 200, 'category' => 'Men\'s Clothing'],
            ['name' => 'Women\'s Summer Dress', 'price' => 49.99, 'stock' => 150, 'category' => 'Women\'s Clothing'],

            // Home
            ['name' => 'Modern Coffee Table', 'price' => 299.99, 'stock' => 40, 'category' => 'Furniture'],
            ['name' => 'Kitchen Stand Mixer', 'price' => 399.99, 'stock' => 30, 'category' => 'Kitchen Appliances'],
        ];

        foreach ($products as $productData) {
            $category = $categories->where('name', $productData['category'])->first();

            if ($category) {
                Product::create([
                    'shop_id' => $shops->random()->id,
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'description' => 'High quality ' . strtolower($productData['name']) . ' with excellent features.',
                    'images' => [
                        'https://example.com/images/' . str_replace(' ', '-', strtolower($productData['name'])) . '-1.jpg',
                        'https://example.com/images/' . str_replace(' ', '-', strtolower($productData['name'])) . '-2.jpg',
                    ],
                    'price' => $productData['price'],
                    'stock' => $productData['stock'],
                    'status' => 'active',
                    'rating' => round(rand(30, 50) / 10, 1), // Random rating between 3.0 and 5.0
                    'sold_count' => rand(0, 500),
                ]);
            }
        }
    }
}
