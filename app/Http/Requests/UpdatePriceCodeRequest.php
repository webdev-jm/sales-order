<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\PriceCode;

class UpdatePriceCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('price code edit');
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
            'product_id' => [
                'required'
            ],
            'code' => [
                'required', Rule::unique((new PriceCode)->getTable())->where(function($query) {
                    $query->where('company_id', $this->company_id)->where('product_id', $this->product_id);
                })->ignore($this->id)
            ],
            'selling_price' => [
                'required', 'numeric'
            ],
            'price_basis' => [
                'required'
            ]
        ];
    }
}
