<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Discount;

class StoreDiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('discount create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'discount_code' => [
                'required', Rule::unique((new Discount)->getTable())
            ],
            'description' => [
                'required'
            ],
            'discount_1' => [
                'required', 'max:3'
            ],
            'discount_2' => [
                'max:3'
            ],
            'discount_3' => [
                'max:3'
            ],
        ];
    }
}
