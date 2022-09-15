<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SalesPerson;

class StoreSalesPersonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('sales person create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => [
                'required', Rule::unique((new SalesPerson)->getTable())
            ],
            'accounts' => [
                'required'
            ],
            'code' => [
                'required', Rule::unique((new SalesPerson)->getTable())
            ]
        ];
    }
}
