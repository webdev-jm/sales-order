<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Product;
use App\Models\AccountProductReference;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AccountProductReferenceImport implements ToModel, WithStartRow
{

    public function startRow(): int
    {
        return 2;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $account = Account::where('account_code', $row[1])->first();
        $product = Product::where('stock_code', $row[3])->first();

        if(!empty($account) && !empty($product)) {

            return new AccountProductReference([
                'account_id' => $account->id,
                'product_id' => $product->id,
                'account_reference' => $row[2],
                'description' => $row[4],
            ]);

        } else {
            return null;
        }
        
    }
}
