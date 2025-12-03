<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BusinessTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Retail',
                'description' => 'Direct-to-consumer storefronts selling finished goods.',
            ],
            [
                'name' => 'Wholesale',
                'description' => 'Bulk distributors supplying other businesses.',
            ],
            [
                'name' => 'Dropshipping',
                'description' => 'Sellers that fulfill orders through third-party suppliers.',
            ],
            [
                'name' => 'Manufacturing',
                'description' => 'Makers producing goods for sale through the marketplace.',
            ],
            [
                'name' => 'Services',
                'description' => 'Service-based shops (repairs, customization, installations).',
            ],
        ];

        foreach ($types as $type) {
            BusinessType::updateOrCreate(
                ['slug' => Str::slug($type['name'])],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
