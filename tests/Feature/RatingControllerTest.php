<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_rate_a_movie(): void
    {
        $movie = Movie::factory()->create();

        $this->post(route('movies.rate', $movie), ['stars' => 4])
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_delete_rating(): void
    {
        $movie = Movie::factory()->create();

        $this->delete(route('movies.rating.destroy', $movie))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Store (upsert)
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_rate_a_movie_with_stars(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.rate', $movie), ['stars' => 5])
            ->assertRedirect();

        $this->assertDatabaseHas('ratings', [
            'user_id'  => $user->id,
            'movie_id' => $movie->id,
            'stars'    => 5,
        ]);
    }

    public function test_authenticated_user_can_like_a_movie(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.rate', $movie), ['liked' => true])
            ->assertRedirect();

        $this->assertDatabaseHas('ratings', [
            'user_id'  => $user->id,
            'movie_id' => $movie->id,
            'liked'    => true,
        ]);
    }

    public function test_rating_is_upserted_not_duplicated(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.rate', $movie), ['stars' => 3]);

        $this->actingAs($user)
            ->post(route('movies.rate', $movie), ['stars' => 5]);

        $this->assertDatabaseCount('ratings', 1);
        $this->assertDatabaseHas('ratings', ['stars' => 5]);
    }

    public function test_store_validates_stars_range(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.rate', $movie), ['stars' => 6])
            ->assertSessionHasErrors('stars');

        $this->actingAs($user)
            ->post(route('movies.rate', $movie), ['stars' => 0])
            ->assertSessionHasErrors('stars');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_user_can_remove_their_rating(): void
    {
        $user   = User::factory()->create();
        $movie  = Movie::factory()->create();
        Rating::factory()->create([
            'user_id'  => $user->id,
            'movie_id' => $movie->id,
            'stars'    => 4,
        ]);

        $this->actingAs($user)
            ->delete(route('movies.rating.destroy', $movie))
            ->assertRedirect();

        $this->assertDatabaseHas('ratings', [
            'user_id'  => $user->id,
            'movie_id' => $movie->id,
            'stars'    => null,
        ]);
    }

    public function test_user_cannot_delete_another_users_rating(): void
    {
        $user1  = User::factory()->create();
        $user2  = User::factory()->create();
        $movie  = Movie::factory()->create();
        $rating = Rating::factory()->create([
            'user_id'  => $user1->id,
            'movie_id' => $movie->id,
            'stars'    => 4,
        ]);

        $this->actingAs($user2)
            ->delete(route('movies.rating.destroy', $movie));

        $this->assertDatabaseHas('ratings', ['id' => $rating->id]);
    }
}
