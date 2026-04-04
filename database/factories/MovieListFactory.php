<?php

namespace Database\Factories;

use App\Models\MovieList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovieList>
 */
class MovieListFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'name'        => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'is_public'   => true,
            'is_ranked'   => false,
        ];
    }
}
