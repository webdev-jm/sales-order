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
            'company_id' => [
                'required'
            ],
            'discount_code' => [
                'required', Rule::unique((new Discount)->getTable())->where(function($query) {
                    $query->where('company_id', $this->company_id);
                })
            ],
            'description' => [
                'required'
            ],
            'discount_1' => [
                'required', 'max:5'
            ],
            'discount_2' => [
                'max:5'
            ],
            'discount_3' => [
                'max:5'
            ],
        ];
    }
}
