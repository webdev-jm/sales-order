<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('product create');
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
                'required', Rule::unique((new Product)->getTable())
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
            'brand' => [
                'max:255'
            ],
            'alternative_code' => [
                'max:255'
            ],
            'stock_uom1' => [
                'required'
            ],
            'stock_uom2' => [
                'max:255'
            ],
            'stock_uom3' => [
                'max:255'
            ],
            'uom_price1' => [
                'required_unless:stock_uom1,null'
            ],
            'uom_price2' => [
                'required_unless:stock_uom2,null'
            ],
            'uom_price3' => [
                'required_unless:stock_uom3,null'
            ],
        ];
    }
}
