<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\InvoiceTerm;
use App\Models\Company;
use App\Models\Discount;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AccountImport implements ToModel, WithStartRow
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

        $invoice_term = InvoiceTerm::where('term_code', $row[5])->first();
        
        $company = Company::where('name', $row[0])->first();
        if(empty($company)) {
            $company = new Company([
                'name' => $row[0]
            ]);
            $company->save();
        }

        $discount = Discount::where('company_id', $company->id)
        ->where(function($query) use ($row) {
            $query->where('discount_code', $row[7])->orWhere('discount_code', $row[8]);
        })
        ->first();

        return new Account([
            'invoice_term_id' => $invoice_term->id ?? null,
            'company_id' => $company->id,
            'discount_id' => $discount->id ?? null,
            'account_code' => $row[1],
            'account_name' => $row[2],
            'short_name' => $row[3],
            'price_code' => $row[6],
            'ship_to_address1' => $row[9],
            'ship_to_address2' => $row[10],
            'ship_to_address3' => $row[11],
            'postal_code' => $row[12],
            'tax_number' => $row[13],
            'on_hold' => trim($row[14]) == 'N' ? true : false,
        ]);
    }
}
