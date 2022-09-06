<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\InvoiceTerm;

class StoreInvoiceTermRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('invoice term create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'term_code' => [
                'required', Rule::unique((new InvoiceTerm)->getTable())
            ],
            'description' => [
                'required'
            ],
            'discount' => [
                'required'
            ],
            'discount_days' => [
                'required'
            ],
            'due_days' => [
                'required'
            ]
        ];
    }
}
