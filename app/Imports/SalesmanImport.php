<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class SalesmanImport implements ToCollection, WithStartRow
{

    public function startRow(): int
    {
        return 2;
    }
    
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }
}
