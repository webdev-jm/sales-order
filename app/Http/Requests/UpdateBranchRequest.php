<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Branch;

class UpdateBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('branch edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account_id' => [
                'required'
            ],
            'branch_code' => [
                'required', Rule::unique((new Branch)->getTable())->ignore($this->id)
            ],
            'branch_name' => [
                'required'
            ],
            'region' => [
                'required'
            ],
            'classification' => [
                'required'
            ],
            'area' => [
                'required'
            ]
        ];
    }
}
