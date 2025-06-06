<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\District;

class UpdateDistrictRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('district edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'district_code' => [
                'required', 
                'max:255', 
                'min:2',
                Rule::unique((new District)->getTable())->ignore($this->id)
            ],
            'district_name' => [
                'required', 
                'max:255',
            ]
        ];
    }
}
