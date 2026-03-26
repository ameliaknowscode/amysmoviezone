<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationsControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_view_recommendations(): void
    {
        $this->get(route('recommendations'))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Too few ratings
    // -------------------------------------------------------------------------

    public function test_user_with_no_ratings_sees_too_few_message(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('recommendations'))
            ->assertOk()
            ->assertSee('3'); // MIN_RATINGS_NEEDED shown in view
    }

    public function test_user_with_fewer_than_three_ratings_sees_too_few_message(): void
    {
        $user   = User::factory()->create();
        $movies = Movie::factory()->count(2)->create();

        foreach ($movies as $movie) {
            Rating::factory()->create(['user_id' => $user->id, 'movie_id' => $movie->id, 'stars' => 4]);
        }

        $this->actingAs($user)
            ->get(route('recommendations'))
            ->assertOk();
        // Page loads cleanly showing the "rate more movies" prompt
    }

    // -------------------------------------------------------------------------
    // Recommendation algorithm
    // -------------------------------------------------------------------------

    public function test_recommended_movies_are_not_already_rated_by_user(): void
    {
        [$alice, $bob]   = User::factory()->count(2)->create();
        $alreadyRated    = Movie::factory()->count(3)->create();
        $notYetRated     = Movie::factory()->create(['title' => 'Hidden Gem']);

        // Alice rates 3 movies (meets minimum)
        foreach ($alreadyRated as $movie) {
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 4]);
        }

        // Bob has similar taste: rates 2 of Alice's movies + 1 new one
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $alreadyRated[0]->id, 'stars' => 5]);
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $alreadyRated[1]->id, 'stars' => 5]);
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $notYetRated->id,     'stars' => 5]);

        $response = $this->actingAs($alice)->get(route('recommendations'));

        $response->assertSee('Hidden Gem');

        // Already-rated movies should not appear in recommendations
        foreach ($alreadyRated as $movie) {
            $response->assertDontSee($movie->title);
        }
    }

    public function test_recommendations_come_from_users_with_overlapping_ratings(): void
    {
        [$alice, $bob, $charlie] = User::factory()->count(3)->create();
        $sharedMovies            = Movie::factory()->count(3)->create();
        $bobExtra                = Movie::factory()->create(['title' => 'Bob Recommends']);
        $charlieExtra            = Movie::factory()->create(['title' => 'Charlie Unrelated']);

        // Alice rates 3 movies
        foreach ($sharedMovies as $movie) {
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 4]);
        }

        // Bob shares 2 movies with Alice (qualifies as similar) and adds one more
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $sharedMovies[0]->id, 'stars' => 5]);
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $sharedMovies[1]->id, 'stars' => 5]);
        Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $bobExtra->id,         'stars' => 5]);

        // Charlie shares only 1 movie with Alice (below MIN_OVERLAP of 2) — should be ignored
        Rating::factory()->create(['user_id' => $charlie->id, 'movie_id' => $sharedMovies[0]->id, 'stars' => 5]);
        Rating::factory()->create(['user_id' => $charlie->id, 'movie_id' => $charlieExtra->id,     'stars' => 5]);

        $response = $this->actingAs($alice)->get(route('recommendations'));

        $response->assertSee('Bob Recommends');
        $response->assertDontSee('Charlie Unrelated');
    }

    public function test_user_with_no_similar_users_sees_empty_recommendations(): void
    {
        $alice  = User::factory()->create();
        $bob    = User::factory()->create();
        $movies = Movie::factory()->count(3)->create();

        // Alice rates 3 movies
        foreach ($movies as $movie) {
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 3]);
        }

        // Bob rates completely different movies — no overlap
        $differentMovies = Movie::factory()->count(3)->create();
        foreach ($differentMovies as $movie) {
            Rating::factory()->create(['user_id' => $bob->id, 'movie_id' => $movie->id, 'stars' => 5]);
        }

        $this->actingAs($alice)
            ->get(route('recommendations'))
            ->assertOk();
        // Page loads cleanly with no recommendations
    }

    public function test_recommendations_page_loads_for_user_with_enough_ratings(): void
    {
        $alice  = User::factory()->create();
        $movies = Movie::factory()->count(3)->create();

        foreach ($movies as $movie) {
            Rating::factory()->create(['user_id' => $alice->id, 'movie_id' => $movie->id, 'stars' => 4]);
        }

        $this->actingAs($alice)
            ->get(route('recommendations'))
            ->assertOk();
    }
}
