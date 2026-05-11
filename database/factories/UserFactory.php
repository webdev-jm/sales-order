<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'firstname' => fake()->firstName(),
            'middlename' => fake()->optional(0.6)->lastName(),
            'lastname' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'notify_email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('p4ssw0rd'),
            'group_code' => fake()->randomElement(['SALES', 'ADMIN', 'MANAGER', 'SUPERVISOR']),
            'status' => 'active',
            'coe' => false,
            'db_type' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
