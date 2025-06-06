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
                Rule::unique((new SalesOrder)->getTable())
                    ->where(function($query) {
                        $query->where('status', '!=', 'cancelled');
                    }),
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
                function ($attribute, $value, $fail) use ($logged_account) {
                    
                    if (!empty($logged_account->account->po_process_date)) {
                        // Check if the ship date is at least 3 days from the order date
                        $leadDate = Carbon::parse($this->order_date)->addWeekdays($logged_account->account->po_process_date)->startOfDay();
                        $shipDate = Carbon::parse($value)->startOfDay();
                        
                        if($shipDate < $leadDate) {
                            $fail('The ship date must be at least '.$logged_account->account->po_process_date.' day/s from the order date excluding saturday and sunday.');
                        }
                    } else {
                        $leadDate = Carbon::parse($this->order_date)->addWeekdays(1)->startOfDay();
                        $shipDate = Carbon::parse($value)->startOfDay();

                        if($shipDate < $leadDate) {
                            $fail('The ship date must be at least 1 day from the order date.');
                        }
                    }
                },
            ],
            'shipping_instruction' => [
                'max:50'
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
