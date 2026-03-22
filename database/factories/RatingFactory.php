<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rating>
 */
class RatingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'  => User::factory(),
            'movie_id' => Movie::factory(),
            'stars'    => fake()->optional()->numberBetween(1, 5),
            'liked'    => fake()->optional()->boolean(),
        ];
    }
}
