<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ActivityPlanImport implements ToCollection, WithBatchInserts, WithChunkReading
{

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int {
        return 500;
    }
    
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }
}
