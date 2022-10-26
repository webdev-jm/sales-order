<?php

namespace App\Imports;

use App\Models\Classification;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ClassificationImport implements ToModel, WithStartRow
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
        $check = Classification::where('classification_name', $row[0])
        ->where('classification_code', $row[1])->first();

        if(empty($check)) {
            return new Classification([
                'classification_name' => $row[0],
                'classification_code' => $row[1],
            ]);
        } else {
            $check->update([
                'classification_name' => $row[0],
                'classification_code' => $row[1],
            ]);
            return null;
        }

    }
}
