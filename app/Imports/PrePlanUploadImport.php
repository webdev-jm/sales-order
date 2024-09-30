<?php

namespace App\Imports;

use App\Models\PafPrePlan;
use App\Models\PafPrePlanDetail;
use App\Models\PafSupportType;
use App\Models\Account;
use App\Models\Product;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PrePlanUploadImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
{
    public function startRow(): int
    {
        return 2;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int {
        return 100;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $pre_plan_number = trim($row[0]);
        $year = trim($row[1]);
        $company = trim($row[2]);
        $account_code = trim($row[3]);
        $account_name = trim($row[4]);
        $start_date = trim($row[5]);
        $end_date = trim($row[6]);
        $title = trim($row[7]);
        $support_type = trim($row[8]);
        $concept = trim($row[9]);
        $detail_type = trim($row[10]);
        $components = trim($row[11]);
        $stock_code = trim($row[12]);
        $description = trim($row[13]);
        $price_code = trim($row[14]);
        $brand = trim($row[15]);
        $quantity = trim($row[16]);
        $branch = trim($row[17]);
        $gl_code = trim($row[18]);
        $io = trim($row[19]);
        $amount = trim($row[20]);

        $err = 0;

        // account
        $account = Account::where('account_code', $account_code)
            ->orWhere('account_name', $account_name)
            ->orWhere('short_name', $account_name)
            ->first();
        if(empty($account)) {
            $err = 1;
        }

        // product
        $product = Product::where('stock_code', $stock_code)
            ->first();

        // support type
        $support_type = PafSupportType::where('support', $support_type)
            ->first();

        // check if no error
        if($err == 0) {
            // check duplicate
            $pre_plan = PafPrePlan::where('pre_plan_number', $pre_plan_number)->first();
            if(empty($pre_plan)) {
                $pre_plan = new PafPrePlan([
                    'account_id' => $account->id,
                    'paf_support_type_id' => $support_type->id ?? NULL,
                    'pre_plan_number' => $pre_plan_number,
                    'year' => $year,
                    'start_date' => $this->transformDate($start_date),
                    'end_date' => $this->transformDate($end_date),
                    'title' => $title,
                    'concept' => $concept
                ]);
                $pre_plan->save();
            }

            return new PafPrePlanDetail([
                'paf_pre_plan_id' => $pre_plan->id,
                'product_id' => $product->id ?? NULL,
                'type' => $detail_type,
                'components' => $components,
                'stock_code' => $stock_code,
                'description' => $description,
                'price_code' => $price_code,
                'brand' => $brand,
                'quantity' => $quantity,
                'branch' => $branch,
                'GlCode' => $gl_code,
                'IO' => $io,
                'amount' => $amount,
            ]);

        } else {
            return NULL;
        }
    }

    private function transformDate($value, $format = 'Y-m-d')
    {
        try {
            return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        } catch (\ErrorException $e) {
            return \Carbon\Carbon::createFromFormat($format, $value);
        }
    }
}
