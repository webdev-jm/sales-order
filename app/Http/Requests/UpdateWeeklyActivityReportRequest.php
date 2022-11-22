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
            // collections
            'beginning_ar' => [
                'required'
            ],
            'due_for_collection' => [
                'required'
            ],
            'beginning_hanging_balance' => [
                'required'
            ],
            'target_reconciliations' => [
                'required'
            ],
            'week_to_date' => [
                'required'
            ],
            'month_to_date' => [
                'required'
            ],
            'month_target' => [
                'required'
            ],
            'balance_to_sell' => [
                'required'
            ],
            // action plans
            'action_plan.*' => [
                'max:255'
            ],
            'time_table.*' => [
                'max:255'
            ],
            'person_responsible.*' => [
                'max:255'
            ],
            // activities
            'activity.*' => [
                'required'
            ],
            'no_of_days_weekly.*' => [
                'max:11'
            ],
            'no_of_days_mtd.*' => [
                'max:11'
            ],
            'activity_remarks.*' => [
                'max:255'
            ],
            'no_of_days_ytd.*' => [
                'max:11'
            ],
            'total_working_days' => [
                'max:255'
            ]
        ];
    }
}
