<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesmenLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('salesman location create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'salesman_id' => [
                ''
            ],
            'province' => [
                'required',
                'max:255'
            ],
            'city' => [
                'required',
                'max:255'
            ]
        ];
    }
}
