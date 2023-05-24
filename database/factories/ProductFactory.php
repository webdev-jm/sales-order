<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'stock_code' => $this->faker->unique()->randomNumber(5),
            'description' => $this->faker->unique()->sentence(3),
            'size' => $this->faker->text(6),
            'category' => $this->faker->text(5),
            'product_class' => $this->faker->text(5),
            'brand' => $this->faker->text(5),
            'core_group' => $this->faker->text(5),
            'stock_uom',
            'order_uom',
            'other_uom',
            'order_uom_conversion',
            'other_uom_conversion',
            'order_uom_operator',
            'other_uom_operator',
            'status',
            'special_product',
            'bar_code',
        ];
    }
}
