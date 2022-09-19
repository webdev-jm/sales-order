<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SalesOrder;

class StoreSalesOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('sales order create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'control_number' => [
                'required', Rule::unique((new SalesOrder)->getTable())
            ],
            'status' => [
                'required'
            ],
            'po_number' => [
                'required', Rule::unique((new SalesOrder)->getTable())
            ],
            'order_date' => [
                'required'
            ],
            'ship_date' => [
                'required'
            ],
            'shipping_instruction' => [
                'max:1000'
            ],
            'ship_to_name' => [
                'required'
            ],
            'ship_to_address1' => [
                'max:255'
            ],
            'ship_to_address2' => [
                'max:255'
            ],
            'ship_to_address3' => [
                'max:255'
            ],
            'postal_code' => [
                'max:255'
            ],
        ];
    }
}
