<?php

namespace App\Imports;

use App\Models\Discount;
use App\Models\Company;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class DiscountImport implements ToModel, WithStartRow
{

    public function startRow(): int
    {
        return 2;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $company = Company::where('name', $row[0])->first();
        if(empty($company)) {
            $company = new Company([
                'name' => $row[0]
            ]);
            $company->save();
        }

        $description = $row[2];
        // convert decimal value to percentage
        if(strpos('%', $description) >= 0) {
            $description = ($description * 100) . '%';
        }

        return new Discount([
            'company_id' => $company->id,
            'discount_code' => $row[1],
            'description' => $row[2],
            'discount_1' => $row[3],
            'discount_2' => $row[4],
            'discount_3' => $row[5],
        ]);
    }
}
