<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\PPUForm;

class UpdatePPUFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $ppu_form = PPUForm::findOrFail($this->id);
        return auth()->user()->can('ppu form edit') && $ppu_form->status == 'draft';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'control_number' => [
                'required', Rule::unique((new PPUForm)->getTable())->ignore($this->id)
            ],
            'status' => [
                'required'
            ],
        ];
    }
}
