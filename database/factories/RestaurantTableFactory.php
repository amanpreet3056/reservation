<?php

namespace Database\Factories;

use App\Models\RestaurantTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantTable>
 */
class RestaurantTableFactory extends Factory
{
    protected $model = RestaurantTable::class;

    public function definition(): array
    {
        return [
            'name' => 'Table ' . fake()->unique()->numberBetween(1, 40),
            'seats' => fake()->numberBetween(2, 8),
            'area_name' => fake()->randomElement(['Main Hall', 'Terrace', 'Private Room']),
            'priority' => fake()->numberBetween(1, 5),
            'status' => fake()->randomElement(['available', 'unavailable']),
        ];
    }
}