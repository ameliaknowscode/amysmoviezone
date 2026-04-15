<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Movie>
 */
class MovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'          => fake()->sentence(3, false),
            'release_year'   => fake()->numberBetween(1950, 2024),
            'synopsis'       => null,
            'runtime'        => null,
            'country'        => null,
            'language'       => null,
            'imdb_url'       => null,
            'letterboxd_url' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(
            fn(Movie $movie) => $movie->update(['slug' => Str::slug($movie->title . ' ' . ($movie->release_year ?? ''))])
        );
    }
}
