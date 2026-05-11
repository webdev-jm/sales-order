<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\AccountLogin;
use App\Models\Product;

class SalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        $accountLoginIds = AccountLogin::pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();

        if (empty($accountLoginIds) || empty($productIds)) {
            return;
        }

        SalesOrder::factory()->count(20)->create([
            'account_login_id' => fake()->randomElement($accountLoginIds),
        ])->each(function (SalesOrder $order) use ($productIds) {
            $lineCount = rand(2, 8);
            $sampledProducts = fake()->randomElements($productIds, min($lineCount, count($productIds)));

            foreach ($sampledProducts as $productId) {
                SalesOrderProduct::factory()->create([
                    'sales_order_id' => $order->id,
                    'product_id' => $productId,
                ]);
            }
        });
    }
}
