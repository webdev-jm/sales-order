<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SalesOrder;

use Illuminate\Support\Facades\Session;

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
        $logged_account = Session::get('logged_account');

        return [
            'control_number' => [
                'required', Rule::unique((new SalesOrder)->getTable())->ignore($this->id)
            ],
            'status' => [
                'required'
            ],
            'po_number' => [
                'regex:/^[a-zA-Z0-9\s\-]+$/',
                'required',
                Rule::unique((new SalesOrder)->getTable())->ignore($this->id),
                Rule::unique('purchase_order_numbers')->where('company_id', $logged_account->account->company_id)
            ],
            'paf_number' => [
                'max:11'
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
