<?php

namespace App\Imports;

use App\Models\CostCenter;
use App\Models\Company;
use App\Models\User;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CostCenterImport implements ToModel, WithStartRow
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
        // Company
        $company = Company::where('name', trim($row[0]))
        ->first();
        // User
        $user = User::where('email', trim($row[3]))
        ->first();

        if(!empty($company) && !empty($user)) {
            return new CostCenter([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'cost_center' => trim($row[1])
            ]);
        } else {
            return null;
        }
    }
}
