<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWeeklyActivityReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('war edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status' => [
                'required'
            ],
            'accounts_visited' => [
                'required'
            ],
            'date_from' => [
                'required'
            ],
            'date_to' => [
                'required'
            ],
            'week' => [
                'required'
            ],
            // areas
            'area_date.*' => [
                'required'
            ],
            'area_day.*' => [
                'required'
            ],
            'area_covered.*' => [
                'max:255'
            ],
            'area_in_base.*' => [
                'max:255'
            ],
            'area_remarks.*' => [
                'max:255'
            ],
            // highlights
            'highlights' => [
                'required', 'max:1000'
            ],
        ];
    }
}
