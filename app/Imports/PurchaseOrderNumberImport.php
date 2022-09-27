<?php

namespace App\Imports;

use App\Models\PurchaseOrderNumber;
use App\Models\Company;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PurchaseOrderNumberImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
{
    public function startRow(): int
    {
        return 2;
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int {
        return 500;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $company = Company::where('name', trim($row[0]))->first();
        if(empty($company)) {
            $company = new Company([
                'name' => trim($row[0])
            ]);
            $company->save();
        }

        return new PurchaseOrderNumber([
            'company_id' => $company->id,
            'po_number' => trim($row[1]),
        ]);
    }
}
