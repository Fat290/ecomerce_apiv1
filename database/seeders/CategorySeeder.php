<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Variant;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $definitions = [
            [
                'name' => 'Electronics',
                'image' => 'https://placehold.co/600x400?text=Electronics',
                'variants' => [
                    ['name' => 'Color', 'options' => ['Black', 'Silver', 'White'], 'is_required' => true],
                    ['name' => 'Warranty', 'options' => ['1 Year', '2 Years', 'Extended'], 'is_required' => false],
                ],
                'children' => [
                    [
                        'name' => 'Smartphones',
                        'image' => 'https://placehold.co/600x400?text=Smartphones',
                        'variants' => [
                            ['name' => 'Storage', 'options' => ['64GB', '128GB', '256GB'], 'is_required' => true],
                            ['name' => 'Color', 'options' => ['Black', 'Blue', 'Gold']],
                        ],
                    ],
                    [
                        'name' => 'Laptops',
                        'image' => 'https://placehold.co/600x400?text=Laptops',
                        'variants' => [
                            ['name' => 'RAM', 'options' => ['8GB', '16GB', '32GB'], 'is_required' => true],
                            ['name' => 'Storage', 'options' => ['256GB SSD', '512GB SSD', '1TB SSD']],
                        ],
                    ],
                    [
                        'name' => 'Headphones',
                        'image' => 'https://placehold.co/600x400?text=Headphones',
                        'variants' => [
                            ['name' => 'Type', 'options' => ['In-ear', 'Over-ear', 'On-ear'], 'is_required' => true],
                            ['name' => 'Color', 'options' => ['Black', 'White', 'Red']],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Clothing',
                'image' => 'https://placehold.co/600x400?text=Clothing',
                'variants' => [
                    ['name' => 'Size', 'options' => ['XS', 'S', 'M', 'L', 'XL'], 'is_required' => true],
                    ['name' => 'Color', 'options' => ['Black', 'White', 'Navy', 'Beige']],
                ],
                'children' => [
                    [
                        'name' => 'Men\'s Clothing',
                        'image' => 'https://placehold.co/600x400?text=Mens',
                        'variants' => [
                            ['name' => 'Fit', 'options' => ['Slim', 'Regular', 'Relaxed']],
                        ],
                    ],
                    [
                        'name' => 'Women\'s Clothing',
                        'image' => 'https://placehold.co/600x400?text=Womens',
                        'variants' => [
                            ['name' => 'Style', 'options' => ['Casual', 'Formal', 'Athleisure']],
                        ],
                    ],
                    [
                        'name' => 'Shoes',
                        'image' => 'https://placehold.co/600x400?text=Shoes',
                        'variants' => [
                            ['name' => 'Size', 'options' => ['35', '36', '37', '38', '39', '40', '41', '42'], 'is_required' => true],
                            ['name' => 'Material', 'options' => ['Leather', 'Canvas', 'Synthetic']],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Home & Garden',
                'image' => 'https://placehold.co/600x400?text=Home',
                'variants' => [
                    ['name' => 'Material', 'options' => ['Wood', 'Metal', 'Glass', 'Fabric']],
                ],
                'children' => [
                    [
                        'name' => 'Furniture',
                        'image' => 'https://placehold.co/600x400?text=Furniture',
                        'variants' => [
                            ['name' => 'Size', 'options' => ['Small', 'Medium', 'Large']],
                            ['name' => 'Color', 'options' => ['Natural', 'White', 'Black']],
                        ],
                    ],
                    [
                        'name' => 'Kitchen Appliances',
                        'image' => 'https://placehold.co/600x400?text=Kitchen',
                        'variants' => [
                            ['name' => 'Voltage', 'options' => ['110V', '220V'], 'is_required' => true],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Sports & Outdoors',
                'image' => 'https://placehold.co/600x400?text=Sports',
                'variants' => [
                    ['name' => 'Size', 'options' => ['S', 'M', 'L']],
                ],
                'children' => [
                    [
                        'name' => 'Fitness Equipment',
                        'image' => 'https://placehold.co/600x400?text=Fitness',
                        'variants' => [
                            ['name' => 'Weight', 'options' => ['5kg', '10kg', '20kg']],
                        ],
                    ],
                    [
                        'name' => 'Outdoor Gear',
                        'image' => 'https://placehold.co/600x400?text=Outdoor',
                        'variants' => [
                            ['name' => 'Season', 'options' => ['Summer', 'Winter', 'All-season']],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Books',
                'image' => 'https://placehold.co/600x400?text=Books',
                'variants' => [
                    ['name' => 'Format', 'options' => ['Hardcover', 'Paperback', 'E-book'], 'is_required' => true],
                    ['name' => 'Language', 'options' => ['English', 'Spanish', 'French']],
                ],
            ],
        ];

        foreach ($definitions as $definition) {
            $this->createCategoryWithVariants($definition);
        }
    }

    /**
     * Recursively create a category and its variants/children.
     */
    protected function createCategoryWithVariants(array $data, ?Category $parent = null): Category
    {
        $category = Category::create([
            'name' => $data['name'],
            'parent_id' => $parent?->id,
            'image' => $data['image'] ?? null,
        ]);

        foreach ($data['variants'] ?? [] as $index => $variant) {
            Variant::create([
                'category_id' => $category->id,
                'name' => $variant['name'],
                'options' => $variant['options'] ?? [],
                'is_required' => $variant['is_required'] ?? false,
                'position' => $index,
            ]);
        }

        foreach ($data['children'] ?? [] as $child) {
            $this->createCategoryWithVariants($child, $category);
        }

        return $category;
    }
}
