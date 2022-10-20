<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BranchLoginFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id',
            'branch_id',
            'operation_process_id',
            'longitude',
            'latitude',
            'accuracy',
            'time_in',
            'time_out',
        ];
    }
}
