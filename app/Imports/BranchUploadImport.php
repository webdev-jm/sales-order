<?php

namespace App\Imports;

use App\Models\BranchUpload;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BranchUploadImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
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
        $account = trim($row[0]);
        $region = trim($row[1]);
        $classification = trim($row[2]);
        $branch_name = trim($row[3]);
        $branch_code = trim($row[4]);
        $account_group = trim($row[5]);
        $inventory = trim($row[6]);
        $type = trim($row[7]);
        $area_code = trim($row[8]);
        $area = trim($row[9]);
        $classification_code = trim($row[10]);

        // check duplicate
        $branch_upload = BranchUpload::where('account_code', $account)
            ->where('branch_code', $branch_code)
            ->first();

        if(empty($branch_upload)) {
            return new BranchUpload([
                'account_code' => $account,
                'region' => $region,
                'classification' => $classification,
                'branch_name' => $branch_name,
                'branch_code' => $branch_code,
                'account_group' => $account_group,
                'inventory' => $inventory,
                'type' => $type,
                'area_code' => $area_code,
                'area_name' => $area,
                'classification_code' => $classification_code,
            ]);
        } else {
            return NULL;
        }

    }
}
