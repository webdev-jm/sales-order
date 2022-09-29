<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Branch;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BranchImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
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
        $account = Account::where('account_code', $row[0])->first();
        if(!empty($account)) {
            return new Branch([
                'account_id' => $account->id,
                'branch_code' => $row[4],
                'branch_name' => $row[3],
                'region' => $row[1],
                'classification' => $row[2],
                'area' => $row[8],
            ]);
        } else {
            return null;
        }

    }
}
