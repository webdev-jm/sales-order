<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Account;

class UpdateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('account edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account_code' => [
                'required', Rule::unique((new Account)->getTable())->ignore($this->id)
            ],
            'account_name' => [
                'required'
            ],
            'short_name' => [
                'required'
            ],
            'discount_id' => [
                'required'
            ]
        ];
    }
}
