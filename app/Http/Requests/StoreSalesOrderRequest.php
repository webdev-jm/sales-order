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
            'po_number' => [
                'required'
            ],
            'order_date' => [
                'required'
            ],
            'ship_date' => [
                'required'
            ],
            'ship_to_name'
        ];
    }
}
