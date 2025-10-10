<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\BranchAddress;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

// Increase the maximum execution time to 5 minutes
set_time_limit(300);

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

        if (!$branch) {
            return null;
        }

        $exists = BranchAddress::where('branch_id', $branch->id)
            ->where('latitude', $row[2])
            ->where('longitude', $row[3])
            ->exists();

        if ($exists) {
            return null;
        }

        return new BranchAddress([
            'branch_id' => $branch->id,
            'latitude' => $row[2] ?? null,
            'longitude' => $row[3] ?? null,
            'street1' => $row[4] ?? null,
            'street2' => $row[5] ?? null,
            'city' => $row[6] ?? null,
            'state' => $row[7] ?? null,
            'zip' => $row[8] ?? null,
            'country' => $row[9] ?? null,
            'address' => $row[10] ?? null,
        ]);

    }
}
