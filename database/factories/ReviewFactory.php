<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'movie_id'   => Movie::factory(),
            'body'       => fake()->paragraph(),
            'watched_at' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'is_rewatch' => false,
        ];
    }
}
