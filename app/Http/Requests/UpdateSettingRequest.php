<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('settings access');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'data_per_page' => [
                'required', 'numeric', 'min:1'
            ],
            'sales_order_limit' => [
                'required', 'numeric', 'min:1'
            ],
            'mcp_deadline' => [
                'required', 'min:1', 'max:2'
            ]
        ];
    }
}
