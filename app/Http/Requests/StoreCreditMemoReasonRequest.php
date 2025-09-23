<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\CreditMemoReason;

class StoreCreditMemoReasonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('cm reason create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reason_code' => [
                'required',
                Rule::unique((new CreditMemoReason())->getTable())
            ],
            'reason_description' => [
                'required',
            ],
        ];
    }
}
