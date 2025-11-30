<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            'Nike',
            'Adidas',
            'Samsung',
            'Apple',
            'Sony',
            'LG',
            'Canon',
            'Nikon',
            'Dell',
            'HP',
            'Lenovo',
            'Microsoft',
            'Google',
            'Xiaomi',
            'Huawei',
        ];

        foreach ($brands as $brandName) {
            Brand::create([
                'name' => $brandName,
            ]);
        }
    }
}
