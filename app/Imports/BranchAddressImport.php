<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\BranchAddress;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BranchAddressImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
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
        $branch = Branch::where('branch_code', $row[0])
            ->where('branch_name', $row[1])
            ->first();

        $check = BranchAddress::where('branch_id', $branch->id ?? NULL)
                ->where('latitude', $row[2])
                ->where('longitude', $row[3])
                ->first();


        if(!empty($branch) && empty($check)) {

            return new BranchAddress([
                'branch_id' => $branch->id,
                'latitude' => $row[2] ?? NULL,
                'longitude' => $row[3] ?? NULL,
                'street1' => $row[4] ?? NULL,
                'street2' => $row[5] ?? NULL,
                'city' => $row[6] ?? NULL,
                'state' => $row[7] ?? NULL,
                'zip' => $row[8] ?? NULL,
                'country' => $row[9] ?? NULL,
                'address' => $row[10] ?? NULL,
            ]);
        } else {
            return null;
        }

    }
}
