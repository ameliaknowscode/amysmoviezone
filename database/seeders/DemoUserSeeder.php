<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a fixed demo account ("Leia Watson") with a watch diary that visibly
 * exercises all four rating/review combinations plus a derived-rewatch pair,
 * for screenshots / build-in-public material.
 *
 * Invoke explicitly:  php artisan db:seed --class=DemoUserSeeder
 * Idempotent: re-running wipes Leia's existing reviews + ratings and recreates them.
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'leia@example.test'],
            [
                'name'                => 'Leia Watson',
                'username'            => 'leiawatson',
                'password'            => Hash::make('test12345'),
                'email_verified_at'   => now(),
                'welcomed_at'         => now(),
                'is_admin'            => false,
                'profile_private'     => false,
                'email_notifications' => true,
            ],
        );

        // Reset state so the seeder is idempotent.
        $user->reviews()->delete();
        $user->ratings()->delete();

        $starWars       = Movie::where('slug', 'star-wars-1977')->firstOrFail();
        $empireStrikes  = Movie::where('slug', 'the-empire-strikes-back-1980')->first()
            ?? Movie::where('title', 'The Empire Strikes Back')->firstOrFail();
        $phantomMenace  = Movie::where('slug', 'star-wars-episode-i-the-phantom-menace-1999')->firstOrFail();
        $attackOfClones = Movie::where('slug', 'star-wars-episode-ii-attack-of-the-clones-2002')->firstOrFail();

        // Ratings: per-(user, movie). Two films get ratings, two don't — that's
        // what makes "no rating" visible on the diary alongside "rated".
        Rating::create(['user_id' => $user->id, 'movie_id' => $starWars->id,      'stars' => 5.0, 'liked' => true]);
        Rating::create(['user_id' => $user->id, 'movie_id' => $empireStrikes->id, 'stars' => 4.5, 'liked' => true]);

        // Diary entries — chronological order isn't strictly required because
        // ReviewObserver derives is_rewatch from watched_at, but it keeps the
        // intent of the seed obvious.
        $entries = [
            // Rated + reviewed
            [
                'movie_id'   => $starWars->id,
                'watched_at' => '2025-08-12',
                'body'       => 'First time watching the original cut. Worth every minute of the hype.',
            ],
            // Rated, no review
            [
                'movie_id'   => $empireStrikes->id,
                'watched_at' => '2025-10-30',
                'body'       => null,
            ],
            // Reviewed, no rating
            [
                'movie_id'   => $phantomMenace->id,
                'watched_at' => '2026-01-14',
                'body'       => 'Hits different watching this in 2026 — kids in the back row were rapt the whole time.',
            ],
            // Bare — no rating, no review
            [
                'movie_id'   => $attackOfClones->id,
                'watched_at' => '2026-03-09',
                'body'       => null,
            ],
            // Rewatch (same film as #1)
            [
                'movie_id'   => $starWars->id,
                'watched_at' => '2026-05-19',
                'body'       => 'Annual rewatch. Cantina scene still the best three minutes in cinema.',
            ],
        ];

        foreach ($entries as $entry) {
            Review::create([
                'user_id'      => $user->id,
                'movie_id'     => $entry['movie_id'],
                'body'         => $entry['body'],
                'watched_at'   => $entry['watched_at'],
                'has_spoilers' => false,
            ]);
        }

        $this->command->info("Seeded demo diary for {$user->email} (id={$user->id}): " . count($entries) . ' entries across ' . $user->reviews()->distinct('movie_id')->count('movie_id') . ' films.');
    }
}
