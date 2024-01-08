<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesOrderCutOffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('SO cut-off create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_date' => [
                'required',
            ],
            'start_time' => [
                'required'
            ],
            'end_date' => [
                'required',
            ],
            'end_time' => [
                'required'
            ],
            'message' => [
                'required'
            ]
        ];
    }
}
