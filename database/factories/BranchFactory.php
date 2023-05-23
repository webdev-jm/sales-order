<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'account_id',
            'region_id',
            'classification_id',
            'area_id',
            'branch_code',
            'branch_name',
            'province',
            'city',
            'barangay',
            'address'
        ];
    }
}
