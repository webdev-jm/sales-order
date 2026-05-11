<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PriceCode;
use App\Models\Company;
use App\Models\Product;
use App\Models\Account;

class PriceCodeSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        $productIds = Product::pluck('id')->all();
        $codes = ['P1', 'P2', 'P3', 'P4'];

        if ($companies->isEmpty() || empty($productIds)) {
            return;
        }

        foreach ($companies as $company) {
            foreach ($codes as $code) {
                $total = \count($productIds);
                $sampledProducts = fake()->randomElements($productIds, min(10, $total));
                foreach ($sampledProducts as $productId) {
                    PriceCode::factory()->create([
                        'company_id' => $company->getKey(),
                        'product_id' => $productId,
                        'code' => $code,
                    ]);
                }
            }
        }

        Account::query()->each(function (Account $account) use ($codes) {
            $account->update([
                'price_code' => fake()->randomElement($codes),
            ]);
        });
    }
}
