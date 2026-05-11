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
        $uoms = ['CTN', 'CS', 'BOX', 'PCS', 'EA', 'PK', 'BDL'];
        $orderUom = fake()->randomElement($uoms);
        $otherUom = fake()->randomElement(array_diff($uoms, [$orderUom]));

        return [
            'stock_code' => fake()->unique()->numerify('SKU#####'),
            'description' => fake()->unique()->words(3, true),
            'size' => fake()->randomElement(['50ML', '100ML', '200ML', '250ML', '500ML', '1L', '5L']),
            'weight' => fake()->randomFloat(6, 0.05, 20),
            'volume' => fake()->randomFloat(6, 0.05, 10),
            'pallet' => fake()->numberBetween(10, 100),
            'category' => fake()->randomElement(['HAIR CARE', 'SKIN CARE', 'PERSONAL CARE', 'HOME CARE']),
            'product_class' => fake()->randomElement(['CORE', 'NON-CORE', 'PROMO']),
            'brand' => fake()->randomElement(['KOJIE SAN', 'BIOLINK', 'LIKAS', 'OATMEAL']),
            'core_group' => fake()->randomElement(['GROUP A', 'GROUP B', 'GROUP C']),
            'stock_uom' => 'EA',
            'order_uom' => $orderUom,
            'other_uom' => $otherUom,
            'order_uom_conversion' => fake()->numberBetween(6, 144),
            'other_uom_conversion' => fake()->numberBetween(1, 12),
            'order_uom_operator' => fake()->randomElement(['*', '/']),
            'other_uom_operator' => '*',
            'status' => 'ACTIVE',
            'special_product' => false,
            'bar_code' => fake()->optional()->ean13(),
            'warehouse' => fake()->optional()->randomElement(['WH01', 'WH02', 'WH03']),
        ];
    }
}
