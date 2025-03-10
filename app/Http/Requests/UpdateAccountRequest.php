<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Account;

class UpdateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('account edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'invoice_term_id' => [
                'required'
            ],
            'company_id' => [
                'required'
            ],
            'account_code' => [
                'required', Rule::unique((new Account)->getTable())->ignore(decrypt($this->id))
            ],
            'account_name' => [
                'required'
            ],
            'short_name' => [
                'required'
            ],
            'discount_id' => [
                'required'
            ],
            'price_code' => [
                'required'
            ],
            'ship_to_address1' => [
                'required', 'max:1000'
            ],
            'ship_to_address2' => [
                'max:1000'
            ],
            'ship_to_address3' => [
                'max:1000'
            ],
            'postal_code' => [
                'max:11'
            ],
            'tax_number' => [
                'max:255'
            ],
            'on_hold' => [
                'max:1'
            ],
            'po_process_date' => [
                'max:10'
            ],
            'po_prefix' => [
                'max:255'
            ]
        ];
    }
}
