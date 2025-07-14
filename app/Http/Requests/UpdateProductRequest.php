<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('product edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'stock_code' => [
                'required', Rule::unique((new Product)->getTable())->ignore($this->id)
            ],
            'description' => [
                'required', 'max:255'
            ],
            'size' => [
                'max:255'
            ],
            'category' => [
                'max:255'
            ],
            'product_class' => [
                'required'
            ],
            'brand' => [
                'required'
            ],
            'core_group' => [
                'required'
            ],
            'stock_uom' => [
                'required'
            ],
            'order_uom' => [
                'required'
            ],
            'other_uom' => [
                'required'
            ],
            'order_uom_conversion' => [
                'required'
            ],
            'other_uom_conversion' => [
                'required'
            ],
            'order_uom_operator' => [
                'required'
            ],
            'other_uom_operator' => [
                'required'
            ],
            'status' => [
                'required'
            ],
            'special_product' => [
                'max:2'
            ],
            'warehouse' => [
                'max:255'
            ]
        ];
    }
}
