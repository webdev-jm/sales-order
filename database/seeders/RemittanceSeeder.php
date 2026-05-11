<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Remittance;
use App\Models\Account;

class RemittanceSeeder extends Seeder
{
    public function run(): void
    {
        $accountIds = Account::pluck('id')->toArray();

        if (empty($accountIds)) {
            return;
        }

        Remittance::factory()->count(20)->create([
            'account_id' => fake()->randomElement($accountIds),
        ]);
    }
}
