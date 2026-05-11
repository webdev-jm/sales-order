<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Brand;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $brandIds = Brand::pluck('id')->toArray();

        Product::factory()->count(50)->create([
            'brand_id' => !empty($brandIds) ? fake()->randomElement($brandIds) : 1,
        ]);
    }
}
