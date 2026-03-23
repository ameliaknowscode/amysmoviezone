<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\Review;
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
            $this->seedReviews($user);
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

        // 15% of users keep their profile private.
        if (fake()->boolean(15)) {
            $user->update(['profile_private' => true]);
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

    }

    private function seedReviews(User $user): void
    {
        // Review roughly 30% of the movies they've rated.
        $ratedMovieIds = $user->ratings()->pluck('movie_id')->toArray();

        if (empty($ratedMovieIds)) {
            return;
        }

        $count    = max(1, (int) round(count($ratedMovieIds) * 0.3));
        $toReview = fake()->randomElements($ratedMovieIds, min($count, count($ratedMovieIds)));

        foreach ($toReview as $movieId) {
            Review::create([
                'user_id'  => $user->id,
                'movie_id' => $movieId,
                'body'     => fake()->paragraphs(fake()->numberBetween(1, 3), true),
            ]);
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
