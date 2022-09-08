<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Account;

class StoreAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('account create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'company_id' => [
                'required'
            ],
            'account_code' => [
                'required', Rule::unique((new Account)->getTable())
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
        ];
    }
}
