<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movie>
 */
class MovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'        => fake()->sentence(3, false),
            'director'     => fake()->name(),
            'release_year' => fake()->numberBetween(1950, 2024),
        ];
    }
}
