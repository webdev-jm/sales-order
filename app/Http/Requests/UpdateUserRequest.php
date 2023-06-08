<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('user edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'firstname' => [
                'required', 'max:255'
            ],
            'middlename' => [
                'max:255'
            ],
            'lastname' => [
                'required', 'max:255'
            ],
            'email' => [
                'required', Rule::unique((new User)->getTable())->ignore($this->id)
            ],
            'notify_email' => [
                'required'
            ],
            'group_code' => [
                'required'
            ],
            'roles' => [
                'required'
            ],
            'coe' => [
                'required'
            ],
        ];
    }
}
