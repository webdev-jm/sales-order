<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Branch;

class StoreBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('branch create');
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
                'nullable', Rule::unique((new Branch)->getTable())
            ],
            'branch_name' => [
                'required'
            ],
            'region_id' => [
                'required'
            ],
            'classification_id' => [
                'required'
            ],
            'area_id' => [
                'required'
            ]
        ];
    }
}
