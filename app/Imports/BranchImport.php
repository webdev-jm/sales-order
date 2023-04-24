<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Classification;
use App\Models\Area;
use App\Models\AccountGroup;
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

        $region = Region::where('region_name', $row[1])->first();
        if(empty($region)) {
            $region = new Region([
                'region_name' => $row[1]
            ]);
            $region->save();
        }

        $classification = Classification::where('classification_name', $row[2])
            ->orWhere('classification_code', $row[10])
            ->first();
        if(empty($classification)) {
            $classification = new Classification([
                'classification_name' => $row[2],
                'classification_code' => $row[10],
            ]);
            $classification->save();
        }

        $area = Area::where('area_code', $row[8])
            ->orWhere('area_name', $row[9])
            ->first();
        if(empty($area)) {
            $area = new Area([
                'area_code' => $row[8],
                'area_name' => $row[9]
            ]);
            $area->save();
        }

        // check
        $check = Branch::where('branch_code', $row[4])
        ->where('account_id', $account->id)->first();

        if(empty($check)) {
            if(!empty($account)) {
                return new Branch([
                    'account_id' => $account->id,
                    'region_id' => $region->id,
                    'classification_id' => $classification->id,
                    'area_id' => $area->id,
                    'branch_code' => $row[4],
                    'branch_name' => $row[3],
                ]);
            } else {
                return null;
            }
        } else {
            return null;
        }

    }
}
