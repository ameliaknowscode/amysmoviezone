<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchlistControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_view_watchlist(): void
    {
        $this->get(route('watchlist.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_add_to_watchlist(): void
    {
        $movie = Movie::factory()->create();

        $this->post(route('movies.watchlist.store', $movie), ['list_type' => 'watched'])
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_remove_from_watchlist(): void
    {
        $movie = Movie::factory()->create();

        $this->delete(route('movies.watchlist.destroy', $movie))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('watchlist.index'))
            ->assertOk();
    }

    public function test_index_shows_want_to_watch_entries(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create(['title' => 'My Favourite Film']);
        WatchlistEntry::factory()->create([
            'user_id'   => $user->id,
            'movie_id'  => $movie->id,
            'list_type' => WatchlistEntry::WANT_TO_WATCH,
        ]);

        $this->actingAs($user)
            ->get(route('watchlist.index'))
            ->assertSee('My Favourite Film');
    }

    public function test_index_shows_watched_entries(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create(['title' => 'A Great Movie']);
        WatchlistEntry::factory()->create([
            'user_id'   => $user->id,
            'movie_id'  => $movie->id,
            'list_type' => WatchlistEntry::WATCHED,
        ]);

        $this->actingAs($user)
            ->get(route('watchlist.index'))
            ->assertSee('A Great Movie');
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_user_can_add_movie_to_want_to_watch(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.watchlist.store', $movie), ['list_type' => 'want_to_watch'])
            ->assertRedirect();

        $this->assertDatabaseHas('watchlist_entries', [
            'user_id'   => $user->id,
            'movie_id'  => $movie->id,
            'list_type' => 'want_to_watch',
        ]);
    }

    public function test_user_can_add_movie_to_watched(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.watchlist.store', $movie), ['list_type' => 'watched'])
            ->assertRedirect();

        $this->assertDatabaseHas('watchlist_entries', [
            'user_id'   => $user->id,
            'movie_id'  => $movie->id,
            'list_type' => 'watched',
        ]);
    }

    public function test_adding_to_watchlist_updates_existing_entry(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.watchlist.store', $movie), ['list_type' => 'want_to_watch']);

        $this->actingAs($user)
            ->post(route('movies.watchlist.store', $movie), ['list_type' => 'watched']);

        $this->assertDatabaseCount('watchlist_entries', 1);
        $this->assertDatabaseHas('watchlist_entries', ['list_type' => 'watched']);
    }

    public function test_store_validates_list_type(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.watchlist.store', $movie), ['list_type' => 'invalid_type'])
            ->assertSessionHasErrors('list_type');
    }

    public function test_store_saves_watched_at_when_marking_as_watched(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)->post(route('movies.watchlist.store', $movie), [
            'list_type'  => 'watched',
            'watched_at' => '2025-03-10',
        ]);

        $this->assertDatabaseHas('watchlist_entries', [
            'user_id'   => $user->id,
            'movie_id'  => $movie->id,
            'list_type' => 'watched',
        ]);
        $entry = WatchlistEntry::where('user_id', $user->id)->where('movie_id', $movie->id)->first();
        $this->assertEquals('2025-03-10', $entry->watched_at->format('Y-m-d'));
    }

    public function test_store_clears_watched_at_when_marking_as_want_to_watch(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        WatchlistEntry::factory()->create([
            'user_id'    => $user->id,
            'movie_id'   => $movie->id,
            'list_type'  => WatchlistEntry::WATCHED,
            'watched_at' => '2025-03-10',
        ]);

        $this->actingAs($user)->post(route('movies.watchlist.store', $movie), [
            'list_type' => 'want_to_watch',
        ]);

        $this->assertDatabaseHas('watchlist_entries', [
            'user_id'    => $user->id,
            'movie_id'   => $movie->id,
            'list_type'  => 'want_to_watch',
            'watched_at' => null,
        ]);
    }

    public function test_store_rejects_future_watched_at_date(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)->post(route('movies.watchlist.store', $movie), [
            'list_type'  => 'watched',
            'watched_at' => now()->addDay()->format('Y-m-d'),
        ])->assertSessionHasErrors('watched_at');
    }

    // -------------------------------------------------------------------------
    // Update watched_at
    // -------------------------------------------------------------------------

    public function test_user_can_update_watched_at_date(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();
        WatchlistEntry::factory()->create([
            'user_id'    => $user->id,
            'movie_id'   => $movie->id,
            'list_type'  => WatchlistEntry::WATCHED,
            'watched_at' => '2025-01-01',
        ]);

        $this->actingAs($user)
            ->patch(route('movies.watchlist.watched-at', $movie), ['watched_at' => '2025-06-15'])
            ->assertRedirect();

        $this->assertDatabaseHas('watchlist_entries', [
            'user_id'    => $user->id,
            'movie_id'   => $movie->id,
            'watched_at' => '2025-06-15',
        ]);
    }

    public function test_user_can_clear_watched_at_date(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();
        WatchlistEntry::factory()->create([
            'user_id'    => $user->id,
            'movie_id'   => $movie->id,
            'list_type'  => WatchlistEntry::WATCHED,
            'watched_at' => '2025-01-01',
        ]);

        $this->actingAs($user)
            ->patch(route('movies.watchlist.watched-at', $movie), ['watched_at' => null])
            ->assertRedirect();

        $this->assertDatabaseHas('watchlist_entries', [
            'user_id'    => $user->id,
            'movie_id'   => $movie->id,
            'watched_at' => null,
        ]);
    }

    public function test_unauthenticated_user_cannot_update_watched_at(): void
    {
        $movie = Movie::factory()->create();

        $this->patch(route('movies.watchlist.watched-at', $movie), ['watched_at' => '2025-06-15'])
            ->assertRedirect(route('login'));
    }

    public function test_update_watched_at_rejects_future_date(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();
        WatchlistEntry::factory()->create([
            'user_id'   => $user->id,
            'movie_id'  => $movie->id,
            'list_type' => WatchlistEntry::WATCHED,
        ]);

        $this->actingAs($user)
            ->patch(route('movies.watchlist.watched-at', $movie), [
                'watched_at' => now()->addDay()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('watched_at');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_user_can_remove_movie_from_watchlist(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();
        WatchlistEntry::factory()->create([
            'user_id'  => $user->id,
            'movie_id' => $movie->id,
        ]);

        $this->actingAs($user)
            ->delete(route('movies.watchlist.destroy', $movie))
            ->assertRedirect();

        $this->assertDatabaseMissing('watchlist_entries', [
            'user_id'  => $user->id,
            'movie_id' => $movie->id,
        ]);
    }

    public function test_user_cannot_remove_another_users_watchlist_entry(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $movie = Movie::factory()->create();
        $entry = WatchlistEntry::factory()->create([
            'user_id'  => $user1->id,
            'movie_id' => $movie->id,
        ]);

        $this->actingAs($user2)
            ->delete(route('movies.watchlist.destroy', $movie));

        $this->assertDatabaseHas('watchlist_entries', ['id' => $entry->id]);
    }

    // -------------------------------------------------------------------------
    // Privacy
    // -------------------------------------------------------------------------

    public function test_user_can_make_profile_private(): void
    {
        $user = User::factory()->create(['profile_private' => false]);

        $this->actingAs($user)
            ->patch(route('watchlist.privacy'), ['profile_private' => '1'])
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->profile_private);
    }

    public function test_user_can_make_profile_public(): void
    {
        $user = User::factory()->create(['profile_private' => true]);

        $this->actingAs($user)
            ->patch(route('watchlist.privacy'), ['profile_private' => '0'])
            ->assertRedirect();

        $user->refresh();
        $this->assertFalse($user->profile_private);
    }

    public function test_unauthenticated_user_cannot_update_privacy(): void
    {
        $this->patch(route('watchlist.privacy'), ['profile_private' => '1'])
            ->assertRedirect(route('login'));
    }
}
