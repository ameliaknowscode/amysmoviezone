<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewObserverTest extends TestCase
{
    use RefreshDatabase;

    private function watch(User $user, Movie $movie, string $date): Review
    {
        return Review::factory()->create([
            'user_id'    => $user->id,
            'movie_id'   => $movie->id,
            'watched_at' => $date,
        ]);
    }

    // -------------------------------------------------------------------------
    // Basic chronology
    // -------------------------------------------------------------------------

    public function test_first_watch_of_a_film_is_not_a_rewatch(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $first = $this->watch($user, $movie, '2025-01-01');

        $this->assertFalse($first->is_rewatch);
    }

    public function test_second_watch_of_a_film_is_a_rewatch(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->watch($user, $movie, '2025-01-01');
        $second = $this->watch($user, $movie, '2025-06-01');

        $this->assertTrue($second->is_rewatch);
    }

    public function test_rewatch_status_is_per_user_not_per_movie(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->watch($alice, $movie, '2025-01-01');
        $bobsFirst = $this->watch($bob, $movie, '2025-06-01');

        $this->assertFalse($bobsFirst->is_rewatch);
    }

    // -------------------------------------------------------------------------
    // Backdating (insert order ≠ chronological order)
    // -------------------------------------------------------------------------

    public function test_backdating_a_watch_makes_it_the_original_and_flips_the_existing_entry(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        // Logged later first.
        $later = $this->watch($user, $movie, '2025-06-01');
        $this->assertFalse($later->is_rewatch);

        // Then backdate-log an earlier watch.
        $earlier = $this->watch($user, $movie, '2025-01-01');

        $this->assertFalse($earlier->fresh()->is_rewatch, 'Backdated earlier watch should be the original.');
        $this->assertTrue($later->fresh()->is_rewatch,    'Previously-original watch should now be a rewatch.');
    }

    // -------------------------------------------------------------------------
    // Updating watched_at
    // -------------------------------------------------------------------------

    public function test_changing_watched_at_re_derives_the_group(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $a = $this->watch($user, $movie, '2025-01-01'); // is_rewatch=false
        $b = $this->watch($user, $movie, '2025-06-01'); // is_rewatch=true

        // Move A to AFTER B — A should become the rewatch, B the original.
        $a->update(['watched_at' => '2025-12-01']);

        $this->assertFalse($b->fresh()->is_rewatch);
        $this->assertTrue($a->fresh()->is_rewatch);
    }

    // -------------------------------------------------------------------------
    // Deleting
    // -------------------------------------------------------------------------

    public function test_deleting_the_original_promotes_the_next_watch(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $original = $this->watch($user, $movie, '2025-01-01');
        $rewatch  = $this->watch($user, $movie, '2025-06-01');

        $this->assertTrue($rewatch->fresh()->is_rewatch);

        $original->delete();

        $this->assertFalse($rewatch->fresh()->is_rewatch, 'Surviving entry should be promoted to original after the first is deleted.');
    }

    public function test_deleting_a_rewatch_leaves_the_original_untouched(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $original = $this->watch($user, $movie, '2025-01-01');
        $rewatch  = $this->watch($user, $movie, '2025-06-01');

        $rewatch->delete();

        $this->assertFalse($original->fresh()->is_rewatch);
    }

    // -------------------------------------------------------------------------
    // Tie-breaking on identical watched_at
    // -------------------------------------------------------------------------

    public function test_two_watches_on_the_same_day_tiebreak_by_insertion_order(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $first  = $this->watch($user, $movie, '2025-01-01');
        $second = $this->watch($user, $movie, '2025-01-01');

        $this->assertFalse($first->fresh()->is_rewatch);
        $this->assertTrue($second->fresh()->is_rewatch);
    }
}
