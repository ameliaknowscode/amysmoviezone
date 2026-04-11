<?php

namespace Database\Seeders;

use App\Models\Genre;
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

        $this->seedMovieGenres($movies);

        // Seed a primary user with rich, stats-page-friendly data.
        // Set SEED_PRIMARY_USER in .env to target a specific account.
        $primaryEmail = env('SEED_PRIMARY_USER', 'test@example.com');
        $primaryUser  = User::where('email', $primaryEmail)->first();

        if (! empty($movies)) {
            if ($primaryUser) {
                $this->seedTestUser($primaryUser, $movies);
                $this->command->info("Seeded stats data for {$primaryEmail}.");
            } else {
                $this->command->warn("SEED_PRIMARY_USER '{$primaryEmail}' not found — skipping primary user stats seeding.");
            }
        }

        // Create 20 additional users, all with password "password" for easy testing.
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

    /**
     * Assign 1–3 random genres to any movie that has none.
     * Skips movies that already have genres so it's safe to re-run.
     */
    private function seedMovieGenres(array $movieIds): void
    {
        $genreIds = Genre::pluck('id')->toArray();

        if (empty($genreIds)) {
            $this->command->warn('No genres found — skipping genre seeding. Run GenreSeeder first.');
            return;
        }

        $alreadyTagged = DB::table('genre_movie')
            ->whereIn('movie_id', $movieIds)
            ->pluck('movie_id')
            ->unique()
            ->toArray();

        $untagged = array_values(array_diff($movieIds, $alreadyTagged));

        if (empty($untagged)) {
            $this->command->info('All movies already have genres — skipping genre seeding.');
            return;
        }

        $rows = [];
        foreach ($untagged as $movieId) {
            $count    = fake()->numberBetween(1, 3);
            $assigned = fake()->randomElements($genreIds, min($count, count($genreIds)));
            foreach ($assigned as $genreId) {
                $rows[] = ['movie_id' => $movieId, 'genre_id' => $genreId];
            }
        }

        DB::table('genre_movie')->insertOrIgnore($rows);
        $this->command->info('Assigned random genres to ' . count($untagged) . ' movies.');
    }

    /**
     * Seed the testuser with enough variety to make the stats page interesting:
     * - ~50 watched films spread across multiple calendar years
     * - Ratings weighted toward 4-5 stars
     * - Reviews for roughly half of them
     * - ~8 rewatches flagged correctly
     */
    private function seedTestUser(User $user, array $movies): void
    {
        // Exclude movies the user already has on their watchlist.
        $existingIds = $user->watchlistEntries()->pluck('movie_id')->toArray();
        $available   = array_values(array_diff($movies, $existingIds));

        if (count($available) < 10) {
            $this->command->warn("Not enough unwatched movies to seed stats data for {$user->email}.");
            return;
        }

        $pool = fake()->randomElements($available, min(55, count($available)));

        // Distribute watched_at dates across the past four years so the
        // "Films Watched by Year" chart has multiple bars.
        $yearBuckets = [
            2022 => array_slice($pool, 0, 10),
            2023 => array_slice($pool, 10, 15),
            2024 => array_slice($pool, 25, 15),
            2025 => array_slice($pool, 40, 10),
        ];

        $watchedMovieIds = [];

        foreach ($yearBuckets as $year => $bucketIds) {
            foreach ($bucketIds as $movieId) {
                $date = fake()->dateTimeBetween("{$year}-01-01", "{$year}-12-31")->format('Y-m-d');

                // Cap 2025 at today so dates aren't in the future.
                if ($year === 2025 && $date > now()->format('Y-m-d')) {
                    $date = now()->format('Y-m-d');
                }

                WatchlistEntry::firstOrCreate(
                    ['user_id' => $user->id, 'movie_id' => $movieId],
                    ['list_type' => WatchlistEntry::WATCHED, 'watched_at' => $date],
                );

                // Rate ~85% of watched films, weighted toward 4-5 stars.
                // Skip if the user has already rated this movie.
                $alreadyRated = $user->ratings()->where('movie_id', $movieId)->exists();
                if (! $alreadyRated && fake()->boolean(85)) {
                    Rating::create([
                        'user_id'  => $user->id,
                        'movie_id' => $movieId,
                        'stars'    => fake()->randomElement([3, 4, 4, 4, 5, 5, 5, 5]),
                        'liked'    => fake()->boolean(60),
                    ]);
                }

                // Write a review for ~45% of films.
                if (fake()->boolean(45)) {
                    Review::create([
                        'user_id'    => $user->id,
                        'movie_id'   => $movieId,
                        'body'       => fake()->optional(0.7)->paragraphs(fake()->numberBetween(1, 2), true),
                        'watched_at' => $date,
                        'is_rewatch' => false,
                    ]);
                }

                $watchedMovieIds[] = $movieId;
            }
        }

        // Add 8 rewatch entries — pick from already-watched films.
        $rewatchCandidates = fake()->randomElements(
            $watchedMovieIds,
            min(8, count($watchedMovieIds))
        );

        foreach ($rewatchCandidates as $movieId) {
            $rewatchDate = fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d');

            Review::create([
                'user_id'    => $user->id,
                'movie_id'   => $movieId,
                'body'       => fake()->optional(0.5)->sentence(),
                'watched_at' => $rewatchDate,
                'is_rewatch' => true,
            ]);
        }

        // Add a small want-to-watch list from remaining films.
        $remaining = array_values(array_diff($movies, $watchedMovieIds));
        $wantPool  = fake()->randomElements($remaining, min(12, count($remaining)));

        foreach ($wantPool as $movieId) {
            WatchlistEntry::firstOrCreate(
                ['user_id' => $user->id, 'movie_id' => $movieId],
                ['list_type' => WatchlistEntry::WANT_TO_WATCH],
            );
        }
    }

    private function seedRatings(User $user, array $movies): void
    {
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
        $ratedIds = $user->ratings()->pluck('movie_id')->toArray();
        $unrated  = array_values(array_diff($movies, $ratedIds));

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
                'user_id'    => $user->id,
                'movie_id'   => $movieId,
                'list_type'  => WatchlistEntry::WATCHED,
                'watched_at' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            ]);
        }
    }

    private function seedReviews(User $user): void
    {
        $ratedMovieIds = $user->ratings()->pluck('movie_id')->toArray();

        if (empty($ratedMovieIds)) {
            return;
        }

        $count    = max(1, (int) round(count($ratedMovieIds) * 0.4));
        $toReview = fake()->randomElements($ratedMovieIds, min($count, count($ratedMovieIds)));

        foreach ($toReview as $movieId) {
            $watchedAt = fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d');

            Review::create([
                'user_id'    => $user->id,
                'movie_id'   => $movieId,
                'body'       => fake()->optional(0.75)->paragraphs(fake()->numberBetween(1, 3), true),
                'watched_at' => $watchedAt,
                'is_rewatch' => false,
            ]);

            // 20% chance of a rewatch entry for the same movie.
            if (fake()->boolean(20)) {
                Review::create([
                    'user_id'    => $user->id,
                    'movie_id'   => $movieId,
                    'body'       => fake()->optional(0.5)->paragraphs(1, true),
                    'watched_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                    'is_rewatch' => true,
                ]);
            }
        }
    }

    private function seedFollows($users): void
    {
        $ids = $users->pluck('id')->toArray();

        foreach ($users as $user) {
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
