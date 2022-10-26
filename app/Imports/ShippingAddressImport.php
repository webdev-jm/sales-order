<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\ShippingAddress;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ShippingAddressImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
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
        $account = Account::where('account_code', $row[1])->first();

        // Check
        $check = ShippingAddress::where('account_id', $account->id)
        ->where('account_code', $row[2])->first();

        if(empty($check)) {
            return new ShippingAddress([
                'account_id' => $account->id ?? null,
                'address_code' => $row[2],
                'ship_to_name' => $row[3],
                'building' => $row[4],
                'street' => $row[5],
                'city' => $row[6],
                'tin' => $row[7],
                'postal' => $row[9],
            ]);
        } else {
            return null;
        }

    }
}
