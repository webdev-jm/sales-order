<?php

namespace App\Imports;

use App\Models\InvoiceTerm;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class InvoiceTermImport implements ToModel, WithStartRow
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
        return new InvoiceTerm([
            'term_code' => $row[0],
            'description' => $row[1],
            'discount' => null,
            'discount_days' => null,
            'due_days' => $row[2]
        ]);
    }
}
