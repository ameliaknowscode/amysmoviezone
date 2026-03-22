<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WatchlistEntry>
 */
class WatchlistEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'movie_id'  => Movie::factory(),
            'list_type' => fake()->randomElement([WatchlistEntry::WANT_TO_WATCH, WatchlistEntry::WATCHED]),
        ];
    }

    public function wantToWatch(): static
    {
        return $this->state(['list_type' => WatchlistEntry::WANT_TO_WATCH]);
    }

    public function watched(): static
    {
        return $this->state(['list_type' => WatchlistEntry::WATCHED]);
    }
}
