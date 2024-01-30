<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Department;

class StoreDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('department create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'department_code' => [
                'required',
                Rule::unique((new Department)->getTable()),
            ],
            'department_name' => [
                'required',
            ],
            'department_head_id' => [
                'max:20',
            ],
            'department_admin_id' => [
                'max:20',
            ]
        ];
    }
}