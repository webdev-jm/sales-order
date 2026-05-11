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
        $provinces = ['Metro Manila', 'Cebu', 'Davao del Sur', 'Laguna', 'Cavite', 'Bulacan', 'Rizal', 'Pampanga'];
        $province = fake()->randomElement($provinces);

        return [
            'branch_code' => fake()->unique()->numerify('BR#####'),
            'branch_name' => fake()->company().' - '.fake()->city(),
            'province' => $province,
            'city' => fake()->city(),
            'barangay' => 'Brgy. '.fake()->lastName(),
            'address' => fake()->buildingNumber().' '.fake()->streetName().', '.fake()->city(),
        ];
    }
}
