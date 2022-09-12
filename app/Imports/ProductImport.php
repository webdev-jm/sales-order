<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ProductImport implements ToModel, WithStartRow
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
        $check = Product::where('stock_code', $row['1'])->first();
        if(empty($check)) {
            return new Product([
                'stock_code' => $row[1],
                'description' => $row[2],
                'size' => $row[3],
                'category' => $row[11],
                'product_class' => $row[12],
                'core_group' => $row[13],
                'stock_uom' => $row[4],
                'order_uom' => $row[5],
                'other_uom' => $row[6],
                'order_uom_conversion' => $row[7],
                'other_uom_conversion' => $row[8],
                'order_uom_operator' => $row[9],
                'other_uom_operator' => $row[10],
            ]);
        } else {
            return null;
        }

    }
}
