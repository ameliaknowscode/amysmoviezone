<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_view_stats(): void
    {
        $this->get(route('stats.show'))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Empty state
    // -------------------------------------------------------------------------

    public function test_stats_page_loads_for_user_with_no_watches(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('stats.show'))
            ->assertOk()
            ->assertSee('No stats yet');
    }

    // -------------------------------------------------------------------------
    // Overview
    // -------------------------------------------------------------------------

    public function test_stats_page_shows_correct_watched_count(): void
    {
        $user = User::factory()->create();

        WatchlistEntry::factory()->count(3)->create([
            'user_id'   => $user->id,
            'list_type' => WatchlistEntry::WATCHED,
        ]);

        $this->actingAs($user)
            ->get(route('stats.show'))
            ->assertOk()
            ->assertSee('3');
    }

    public function test_want_to_watch_entries_are_not_counted_in_watched(): void
    {
        $user = User::factory()->create();

        WatchlistEntry::factory()->create([
            'user_id'   => $user->id,
            'list_type' => WatchlistEntry::WANT_TO_WATCH,
        ]);

        $this->actingAs($user)
            ->get(route('stats.show'))
            ->assertOk()
            ->assertSee('No stats yet');
    }

    public function test_stats_page_shows_average_rating(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        WatchlistEntry::factory()->create([
            'user_id'   => $user->id,
            'movie_id'  => $movie->id,
            'list_type' => WatchlistEntry::WATCHED,
        ]);
        Rating::factory()->create([
            'user_id'  => $user->id,
            'movie_id' => $movie->id,
            'stars'    => 4,
        ]);

        $this->actingAs($user)
            ->get(route('stats.show'))
            ->assertOk()
            ->assertSee('4.0');
    }

    // -------------------------------------------------------------------------
    // Decade breakdown
    // -------------------------------------------------------------------------

    public function test_stats_page_shows_release_decade(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create(['release_year' => 1994]);

        WatchlistEntry::factory()->create([
            'user_id'   => $user->id,
            'movie_id'  => $movie->id,
            'list_type' => WatchlistEntry::WATCHED,
        ]);

        $this->actingAs($user)
            ->get(route('stats.show'))
            ->assertOk()
            ->assertSee('1990s');
    }

    // -------------------------------------------------------------------------
    // Year watched breakdown
    // -------------------------------------------------------------------------

    public function test_stats_page_shows_year_watched_chart_when_dates_exist(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        WatchlistEntry::factory()->create([
            'user_id'    => $user->id,
            'movie_id'   => $movie->id,
            'list_type'  => WatchlistEntry::WATCHED,
            'watched_at' => '2025-04-10',
        ]);

        $this->actingAs($user)
            ->get(route('stats.show'))
            ->assertOk()
            ->assertSee('Films Watched by Year')
            ->assertSee('2025');
    }

    public function test_stats_page_omits_year_watched_chart_when_no_dates_set(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        WatchlistEntry::factory()->create([
            'user_id'    => $user->id,
            'movie_id'   => $movie->id,
            'list_type'  => WatchlistEntry::WATCHED,
            'watched_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('stats.show'))
            ->assertOk()
            ->assertDontSee('Films Watched by Year');
    }

    // -------------------------------------------------------------------------
    // Isolation between users
    // -------------------------------------------------------------------------

    public function test_stats_only_reflect_the_authenticated_users_watches(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        WatchlistEntry::factory()->count(5)->create([
            'user_id'   => $user1->id,
            'list_type' => WatchlistEntry::WATCHED,
        ]);

        // user2 has no watches — should still see empty state
        $this->actingAs($user2)
            ->get(route('stats.show'))
            ->assertOk()
            ->assertSee('No stats yet');
    }
}
