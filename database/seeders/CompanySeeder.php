<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Discount;
use App\Models\InvoiceTerm;
use App\Models\Account;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Company::factory()->count(5)->create()->each(function($company) {
        //     Discount::factory()->count(5)->create([
        //         'company_id' => $company->id
        //     ]);
        // });

        $company_arr = [
            'BEVI' => 20,
            'BEVA' => 20,
        ];

        foreach($company_arr as $name => $limit) {
            $company = new Company([
                'name' => $name,
                'order_limit' => $limit
            ]);
            $company->save();
        }
    }
}
