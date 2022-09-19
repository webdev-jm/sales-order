<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SalesOrder;

class UpdateSalesOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $sales_order = SalesOrder::findOrFail($this->id);
        return auth()->user()->can('sales order edit') && $sales_order->status == 'draft';
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
                'required', Rule::unique((new SalesOrder)->getTable())->ignore($this->id)
            ],
            'status' => [
                'required'
            ],
            'po_number' => [
                'required', Rule::unique((new SalesOrder)->getTable())->ignore($this->id)
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
