<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserBranchSchedule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ScheduleImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading, WithCalculatedFormulas
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

        $user = User::where('email', $row[0])->first();
        $branch = Branch::where('branch_code', $row[1])->first();

        if(!empty($user) && !empty($branch)) {
            $check = UserBranchSchedule::where('user_id', $user->id)
            ->where('branch_id', $branch->id)
            ->where('date', $this->transformDate($row[2]))
            ->first();

            if(empty($check)) {
                return new UserBranchSchedule([
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'date' => $this->transformDate($row[2]),
                ]);
            } else {
                return null;
            }

        } else {
            return null;
        }
        
    }

    public function transformDate($value, $format = 'Y-m-d')
    {
        try {
            return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        } catch (\ErrorException $e) {
            return \Carbon\Carbon::createFromFormat($format, $value);
        }
    }
}
