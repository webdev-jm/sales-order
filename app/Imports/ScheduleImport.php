<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserBranchSchedule;
use App\Helpers\UploadDateHelper;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ScheduleImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading, WithCalculatedFormulas
{
    /**
     * Why rows were rejected, in sheet order, so the uploader can fix the file.
     *
     * @var string[]
     */
    public array $rowErrors = [];

    /** Sheet row currently being read, so a rejected row can be named. */
    private int $rowNumber = 1;

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
        $this->rowNumber++;

        $user = User::where('email', $row[0])->first();
        $branch = Branch::where('branch_code', $row[1])->first();

        if(empty($user) || empty($branch)) {
            return null;
        }

        $date_error = UploadDateHelper::error($row[2] ?? null, 'Schedule date');
        if(!empty($date_error)) {
            $this->rowErrors[] = 'Row '.$this->rowNumber.': '.$date_error;

            return null;
        }

        $date = UploadDateHelper::parse($row[2]);

        $check = UserBranchSchedule::where('user_id', $user->id)
            ->where('branch_id', $branch->id)
            ->where('date', $date)
            ->first();

        if(!empty($check)) {
            return null;
        }

        return new UserBranchSchedule([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'date' => $date,
            'source' => 'upload'
        ]);
    }
}
