<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies_arr = [
            [
                'BEVI',
                20
            ],
            [
                'BEVA',
                17,
            ]
        ];

        foreach($companies_arr as $company) {
            $company = new Company([
                'name' => $company[0],
                'order_limit' => $company[1]
            ]);
            $company->save();
        }
    }
}
