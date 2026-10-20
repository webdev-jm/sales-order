<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * GPS readings the browser kept for a branch login: a batch in points[] (each with the phone's recorded_at)
 * for the branch login named by branch_login_id, or one reading (latitude, longitude, accuracy) for the open
 * branch login, which is what the tracker sent before readings were kept on the phone.
 */
class StoreBranchLocationRequest extends FormRequest
{
    /**
     * Readings accepted in one request.
     */
    public const MAX_BATCH = 120;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'branch_login_id'      => ['nullable', 'integer'],
            'latitude'             => ['required_without:points', 'numeric', 'between:-90,90'],
            'longitude'            => ['required_without:points', 'numeric', 'between:-180,180'],
            'accuracy'             => ['nullable'],
            'points'               => ['sometimes', 'array', 'min:1', 'max:' . self::MAX_BATCH],
            'points.*.latitude'    => ['required', 'numeric', 'between:-90,90'],
            'points.*.longitude'   => ['required', 'numeric', 'between:-180,180'],
            'points.*.accuracy'    => ['nullable'],
            'points.*.recorded_at' => ['nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'points.max' => 'Send at most ' . self::MAX_BATCH . ' locations at a time.',
        ];
    }

    /**
     * The readings in the request as the browser sent them.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rawReadings(): array
    {
        return $this->has('points')
            ? $this->validated('points')
            : [$this->safe()->only(['latitude', 'longitude', 'accuracy'])];
    }
}
