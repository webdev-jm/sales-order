<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHolidayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'month'       => ['required', 'integer', 'between:1,12'],
            'day'         => ['required', 'integer', 'between:1,31'],
            'year'        => ['nullable', 'integer'],
            'repeat'      => ['boolean'],
            'is_work_day' => ['boolean'],
        ];
    }
}
