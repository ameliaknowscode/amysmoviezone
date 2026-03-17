<?php

namespace Database\Factories;

use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Type>
 */
class TypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'    => fake()->unique()->word(),
            'is_crew' => fake()->boolean(),
        ];
    }
}
