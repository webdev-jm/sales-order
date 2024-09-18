<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\InvoiceTerm;
use App\Models\Company;
use App\Models\Discount;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Account::factory()->count(20)->create([
            'invoice_term_id' => InvoiceTerm::inRandomOrder()->limit(1)->pluck('id')[0],
            'company_id' => rand(1,2),
            'discount_id' => NULL,
        ]);
    }
}
