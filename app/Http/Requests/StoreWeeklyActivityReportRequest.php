<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeeklyActivityReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('war create');
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
            'area_id' => [
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
            'objective' => [
                'required', 'max:1000'
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
