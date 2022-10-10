<?php

namespace App\Imports;

use App\Models\Area;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AreaImport implements ToModel, WithStartRow
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
        $check = Area::where('area_code', $row[0])
        ->where('area_name', $row[1])->first();

        if(empty($check) && !empty($row[0]) && !empty($row[1])) {
            return new Area([
                'area_code' => $row[0],
                'area_name' => $row[1]
            ]);
        } else {
            return null;
        }
    }
}
