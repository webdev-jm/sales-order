<?php

namespace App\Imports;

use App\Models\PriceCode;
use App\Models\Company;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PriceCodeImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
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
        $company = Company::where('name', $row[0])->first();
        if(empty($company)) {
            $company = new Company([
                'name' => $row[0]
            ]);
            $company->save();
        }

        $product = Product::where('stock_code', $row[1])->first();
        if(!empty($product)) {
            return new PriceCode([
                'company_id' => $company->id,
                'product_id' => $product->id,
                'code' => $row[2],
                'selling_price' => $row[3],
                'price_basis' => $row[4],
            ]);
        } else {
            return null;
        }

    }
}
