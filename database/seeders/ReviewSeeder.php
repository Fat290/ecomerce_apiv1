<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyers = User::where('role', 'buyer')->get();
        $products = Product::all();

        if ($buyers->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No buyers or products found. Please run UserSeeder and ProductSeeder first.');
            return;
        }

        $comments = [
            'Great product! Highly recommended.',
            'Good quality and fast shipping.',
            'Not as expected, but okay.',
            'Excellent value for money.',
            'Amazing product, will buy again!',
            'Good but could be better.',
            'Perfect! Exactly what I needed.',
            'Disappointed with the quality.',
            'Very satisfied with my purchase.',
            'Great service and product quality.',
        ];

        // Create reviews for random products
        foreach ($products->random(min(10, $products->count())) as $product) {
            $buyer = $buyers->random();
            $rating = rand(3, 5); // Mostly positive reviews
            $hasComment = rand(0, 1);

            Review::create([
                'product_id' => $product->id,
                'buyer_id' => $buyer->id,
                'rating' => $rating,
                'comment' => $hasComment ? $comments[array_rand($comments)] : null,
                'reply' => rand(0, 1) ? 'Thank you for your feedback!' : null,
            ]);
        }
    }
}
