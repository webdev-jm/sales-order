<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CreditMemo;
use App\Models\CreditMemoDetail;
use App\Models\Account;
use App\Models\User;
use App\Models\CreditMemoReason;
use App\Models\Product;

class CreditMemoSeeder extends Seeder
{
    public function run(): void
    {
        $accountIds = Account::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();
        $reasonIds = CreditMemoReason::pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();

        if (empty($accountIds) || empty($userIds)) {
            return;
        }

        CreditMemo::factory()->count(15)->create([
            'account_id' => fake()->randomElement($accountIds),
            'user_id' => fake()->randomElement($userIds),
            'credit_memo_reason_id' => !empty($reasonIds) ? fake()->randomElement($reasonIds) : null,
        ])->each(function (CreditMemo $cm) use ($productIds) {
            if (empty($productIds)) {
                return;
            }

            CreditMemoDetail::factory()->count(rand(1, 6))->create([
                'credit_memo_id' => $cm->id,
                'product_id' => fake()->randomElement($productIds),
            ]);
        });
    }
}
