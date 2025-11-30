<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Main Categories
        $electronics = Category::create(['name' => 'Electronics']);
        $clothing = Category::create(['name' => 'Clothing']);
        $home = Category::create(['name' => 'Home & Garden']);
        $sports = Category::create(['name' => 'Sports & Outdoors']);
        $books = Category::create(['name' => 'Books']);

        // Electronics Subcategories
        Category::create(['name' => 'Smartphones', 'parent_id' => $electronics->id]);
        Category::create(['name' => 'Laptops', 'parent_id' => $electronics->id]);
        Category::create(['name' => 'Cameras', 'parent_id' => $electronics->id]);
        Category::create(['name' => 'Headphones', 'parent_id' => $electronics->id]);
        Category::create(['name' => 'TVs', 'parent_id' => $electronics->id]);

        // Clothing Subcategories
        Category::create(['name' => 'Men\'s Clothing', 'parent_id' => $clothing->id]);
        Category::create(['name' => 'Women\'s Clothing', 'parent_id' => $clothing->id]);
        Category::create(['name' => 'Shoes', 'parent_id' => $clothing->id]);
        Category::create(['name' => 'Accessories', 'parent_id' => $clothing->id]);

        // Home Subcategories
        Category::create(['name' => 'Furniture', 'parent_id' => $home->id]);
        Category::create(['name' => 'Kitchen Appliances', 'parent_id' => $home->id]);
        Category::create(['name' => 'Decor', 'parent_id' => $home->id]);

        // Sports Subcategories
        Category::create(['name' => 'Fitness Equipment', 'parent_id' => $sports->id]);
        Category::create(['name' => 'Outdoor Gear', 'parent_id' => $sports->id]);
    }
}
