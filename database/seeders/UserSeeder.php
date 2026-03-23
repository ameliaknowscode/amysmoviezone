<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $movies = Movie::pluck('id')->toArray();

        if (empty($movies)) {
            $this->command->warn('No movies found — skipping ratings and watchlist seeding.');
        }

        // Create 20 users, all with password "password" for easy testing.
        $users = User::factory(20)->create();

        if (empty($movies)) {
            $this->seedFollows($users);
            return;
        }

        foreach ($users as $user) {
            $this->seedRatings($user, $movies);
            $this->seedWatchlist($user, $movies);
        }

        $this->seedFollows($users);
    }

    private function seedRatings(User $user, array $movies): void
    {
        // Each user rates between 5 and 20 movies.
        $count   = fake()->numberBetween(5, 20);
        $sampled = fake()->randomElements($movies, min($count, count($movies)));

        foreach ($sampled as $movieId) {
            Rating::create([
                'user_id'  => $user->id,
                'movie_id' => $movieId,
                'stars'    => fake()->optional(0.85)->numberBetween(1, 5),
                'liked'    => fake()->optional(0.4)->boolean(),
            ]);
        }

        // 15% of users keep their ratings private.
        if (fake()->boolean(15)) {
            $user->update(['ratings_private' => true]);
        }
    }

    private function seedWatchlist(User $user, array $movies): void
    {
        // Pick a pool of movies not already rated by this user.
        $ratedIds   = $user->ratings()->pluck('movie_id')->toArray();
        $unrated    = array_values(array_diff($movies, $ratedIds));

        if (empty($unrated)) {
            return;
        }

        $wantCount    = fake()->numberBetween(3, 10);
        $watchedCount = fake()->numberBetween(2, 8);
        $total        = min($wantCount + $watchedCount, count($unrated));
        $pool         = fake()->randomElements($unrated, $total);

        foreach (array_slice($pool, 0, $wantCount) as $movieId) {
            WatchlistEntry::create([
                'user_id'   => $user->id,
                'movie_id'  => $movieId,
                'list_type' => WatchlistEntry::WANT_TO_WATCH,
            ]);
        }

        foreach (array_slice($pool, $wantCount) as $movieId) {
            WatchlistEntry::create([
                'user_id'   => $user->id,
                'movie_id'  => $movieId,
                'list_type' => WatchlistEntry::WATCHED,
            ]);
        }

        // 10% of users make one or both watchlists private.
        if (fake()->boolean(10)) {
            $user->update(['want_to_watch_private' => true]);
        }
        if (fake()->boolean(10)) {
            $user->update(['watched_private' => true]);
        }
    }

    private function seedFollows($users): void
    {
        $ids = $users->pluck('id')->toArray();

        foreach ($users as $user) {
            // Each user follows between 3 and 8 others.
            $others  = array_values(array_diff($ids, [$user->id]));
            $targets = fake()->randomElements($others, fake()->numberBetween(3, 8));

            $rows = array_map(fn ($targetId) => [
                'follower_id'  => $user->id,
                'following_id' => $targetId,
                'created_at'   => now(),
                'updated_at'   => now(),
            ], $targets);

            DB::table('follows')->insertOrIgnore($rows);
        }
    }
}
