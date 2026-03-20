<?php

namespace Database\Factories;

use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Type>
 */
class TypeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();
        return [
            'name'    => $name,
            'slug'    => Str::slug($name),
            'is_crew' => fake()->boolean(),
        ];
    }
}
