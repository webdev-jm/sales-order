<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ShippingAddress;

class StoreShippingAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('shipping address create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account_id' => [
                'required'
            ],
            'address_code' => [
                'required', Rule::unique((new ShippingAddress)->getTable())->where(function($query) {
                    $query->where('account_id', $this->account_id);
                })
            ],
            'ship_to_name' => [
                'required'
            ],
            'building' => [
                'max:255'
            ],
            'street' => [
                'max:255',
            ],
            'city' => [
                'max:255',
            ],
            'postal' => [
                'max:255'
            ],
            'tin' => [
                'max:255'
            ]
        ];
    }
}
