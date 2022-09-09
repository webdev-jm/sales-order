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
            'BEVI',
            'BEVA',
        ];

        foreach($companies_arr as $company) {
            $company = new Company([
                'name' => $company
            ]);
            $company->save();
        }
    }
}
