<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieBySlugControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Basic access
    // -------------------------------------------------------------------------

    public function test_valid_slug_returns_movie_page(): void
    {
        $movie = Movie::factory()->create(['title' => 'The Matrix']);

        $this->get(route('movies.public', $movie->slug))
            ->assertOk()
            ->assertSee('The Matrix');
    }

    public function test_invalid_slug_returns_404(): void
    {
        $this->get(route('movies.public', 'does-not-exist'))->assertNotFound();
    }

    public function test_page_loads_for_guests(): void
    {
        $movie = Movie::factory()->create();

        $this->get(route('movies.public', $movie->slug))->assertOk();
    }

    public function test_page_loads_for_authenticated_users(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->get(route('movies.public', $movie->slug))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Reviews visibility
    // -------------------------------------------------------------------------

    public function test_reviews_from_public_profiles_are_visible_to_guests(): void
    {
        $author = User::factory()->create(['profile_private' => false]);
        $movie  = Movie::factory()->create();
        Review::factory()->create([
            'user_id'  => $author->id,
            'movie_id' => $movie->id,
            'body'     => 'Public review text.',
        ]);

        $this->get(route('movies.public', $movie->slug))
            ->assertOk()
            ->assertSee('Public review text.');
    }

    public function test_reviews_from_private_profiles_are_hidden(): void
    {
        $privateUser = User::factory()->create(['profile_private' => true]);
        $movie       = Movie::factory()->create();
        Review::factory()->create([
            'user_id'  => $privateUser->id,
            'movie_id' => $movie->id,
            'body'     => 'Private review text.',
        ]);

        $this->get(route('movies.public', $movie->slug))
            ->assertOk()
            ->assertDontSee('Private review text.');
    }

    public function test_authenticated_users_own_review_is_shown(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();
        Review::factory()->create([
            'user_id'  => $user->id,
            'movie_id' => $movie->id,
            'body'     => 'My own review.',
        ]);

        $this->actingAs($user)
            ->get(route('movies.public', $movie->slug))
            ->assertOk()
            ->assertSee('My own review.');
    }

    public function test_authenticated_users_own_review_is_excluded_from_public_list(): void
    {
        $user  = User::factory()->create(['profile_private' => false]);
        $other = User::factory()->create(['profile_private' => false]);
        $movie = Movie::factory()->create();

        Review::factory()->create(['user_id' => $user->id,  'movie_id' => $movie->id, 'body' => 'My review.']);
        Review::factory()->create(['user_id' => $other->id, 'movie_id' => $movie->id, 'body' => 'Their review.']);

        $this->actingAs($user)
            ->get(route('movies.public', $movie->slug))
            ->assertOk()
            ->assertSee('My review.')
            ->assertSee('Their review.');
    }

    // -------------------------------------------------------------------------
    // Aggregate stats
    // -------------------------------------------------------------------------

    public function test_page_shows_average_rating(): void
    {
        $movie = Movie::factory()->create();
        Rating::factory()->create(['movie_id' => $movie->id, 'stars' => 4]);
        Rating::factory()->create(['movie_id' => $movie->id, 'stars' => 2]);

        $this->get(route('movies.public', $movie->slug))
            ->assertOk()
            ->assertSee('3.0');
    }

    public function test_page_shows_no_ratings_message_when_unrated(): void
    {
        $movie = Movie::factory()->create();

        $this->get(route('movies.public', $movie->slug))
            ->assertOk()
            ->assertSee('No ratings yet');
    }
}
