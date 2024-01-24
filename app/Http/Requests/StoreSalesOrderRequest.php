<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SalesOrder;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

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
        $logged_account = Session::get('logged_account');

        return [
            'control_number' => [
                'required', //Rule::unique((new SalesOrder)->getTable())
            ],
            'status' => [
                'required'
            ],
            'po_number' => [
                'required',
                // 'alpha_dash',
                'regex:/^[a-zA-Z0-9\s\-]+$/',
                Rule::unique((new SalesOrder)->getTable()),
                Rule::unique('purchase_order_numbers')->where('company_id', $logged_account->account->company_id),
                'max:30'
            ],
            'paf_number' => [
                'nullable',
                'max:12',
                'min:12',
                'yyyy_a_number'
            ],
            'order_date' => [
                'required'
            ],
            'ship_date' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Check if the ship date is at least 3 days from the current date
                    $currentDate = now()->addDays(3)->startOfDay();
                    $shipDate = Carbon::parse($value)->startOfDay();
    
                    if ($shipDate < $currentDate) {
                        $fail('The ship date must be at least 3 days from the current date.');
                    }
                },
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

    public function messages() {
        return [
            'paf_number.yyyy_a_number' => 'The input field must be in the format "YYYY-A-#####".',
        ];
    }
}
