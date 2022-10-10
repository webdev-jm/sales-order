<?php

namespace App\Imports;

use App\Models\Region;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class RegionImport implements ToModel, WithStartRow
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
        // check
        $check = Region::where('region_name', $row[0])->first();
        if(empty($check)) {
            return new Region([
                'region_name' => $row[0]
            ]);
        } else {
            return null;
        }

    }
}
