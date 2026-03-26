<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\Review;
use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_view_feed(): void
    {
        $this->get(route('feed'))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_view_feed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('feed'))
            ->assertOk();
    }

    public function test_feed_shows_rating_from_followed_user(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $movie = Movie::factory()->create(['title' => 'The Godfather']);

        $alice->following()->attach($bob->id);
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $movie->id, 'stars' => 5]);

        $this->actingAs($alice)
            ->get(route('feed'))
            ->assertSee('The Godfather');
    }

    public function test_feed_shows_review_from_followed_user(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $movie = Movie::factory()->create(['title' => 'Chinatown']);

        $alice->following()->attach($bob->id);
        Review::factory()->create(['user_id' => $bob->id, 'movie_id' => $movie->id]);

        $this->actingAs($alice)
            ->get(route('feed'))
            ->assertSee('Chinatown');
    }

    public function test_feed_shows_watchlist_entry_from_followed_user(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $movie = Movie::factory()->create(['title' => 'Vertigo']);

        $alice->following()->attach($bob->id);
        WatchlistEntry::factory()->create([
            'user_id'   => $bob->id,
            'movie_id'  => $movie->id,
            'list_type' => WatchlistEntry::WATCHED,
        ]);

        $this->actingAs($alice)
            ->get(route('feed'))
            ->assertSee('Vertigo');
    }

    public function test_feed_does_not_show_activity_from_unfollowed_user(): void
    {
        $alice   = User::factory()->create();
        $charlie = User::factory()->create();
        $movie   = Movie::factory()->create(['title' => 'Notorious']);

        // Alice does NOT follow Charlie
        Rating::factory()->create(['user_id' => $charlie->id, 'movie_id' => $movie->id, 'stars' => 4]);

        $this->actingAs($alice)
            ->get(route('feed'))
            ->assertDontSee('Notorious');
    }

    public function test_feed_is_empty_when_not_following_anyone(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('feed'))
            ->assertOk();
        // Page loads cleanly — no activities to show
    }

    public function test_feed_does_not_show_own_activity(): void
    {
        $alice = User::factory()->create();
        $movie = Movie::factory()->create(['title' => 'My Own Movie']);

        Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 3]);

        $this->actingAs($alice)
            ->get(route('feed'))
            ->assertDontSee('My Own Movie');
    }

    // -------------------------------------------------------------------------
    // More (AJAX pagination)
    // -------------------------------------------------------------------------

    public function test_more_endpoint_requires_authentication(): void
    {
        $this->get(route('feed.more'))
            ->assertRedirect(route('login'));
    }

    public function test_more_endpoint_returns_json(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('feed.more'))
            ->assertOk()
            ->assertJsonStructure(['html', 'next_cursor', 'has_more']);
    }

    public function test_more_endpoint_returns_activities_from_followed_users(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $movie = Movie::factory()->create(['title' => 'Rear Window']);

        $alice->following()->attach($bob->id);
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $movie->id, 'stars' => 5]);

        $response = $this->actingAs($alice)
            ->get(route('feed.more'))
            ->assertOk();

        $this->assertStringContainsString('Rear Window', $response->json('html'));
    }
}
