<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_post_a_review(): void
    {
        $movie = Movie::factory()->create();

        $this->post(route('movies.review.store', $movie), ['body' => 'Great film.'])
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_delete_a_review(): void
    {
        $movie = Movie::factory()->create();

        $this->delete(route('movies.review.destroy', $movie))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Store (upsert)
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_submit_a_review(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => 'A great film.'])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'user_id'  => $user->id,
            'movie_id' => $movie->id,
            'body'     => 'A great film.',
        ]);
    }

    public function test_review_is_upserted_not_duplicated(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => 'First review.']);

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => 'Updated review.']);

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseHas('reviews', ['body' => 'Updated review.']);
    }

    public function test_store_redirects_back_with_review_saved_status(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => 'Good film.'])
            ->assertRedirect()
            ->assertSessionHas('status', 'review-saved');
    }

    public function test_body_is_required(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => ''])
            ->assertSessionHasErrors('body');
    }

    public function test_body_cannot_exceed_5000_characters(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => str_repeat('a', 5001)])
            ->assertSessionHasErrors('body');
    }

    public function test_body_at_exactly_5000_characters_is_accepted(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => str_repeat('a', 5000)])
            ->assertRedirect();

        $this->assertDatabaseCount('reviews', 1);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_user_can_delete_their_own_review(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'movie_id' => $movie->id]);

        $this->actingAs($user)
            ->delete(route('movies.review.destroy', $movie))
            ->assertRedirect();

        $this->assertDatabaseMissing('reviews', [
            'user_id'  => $user->id,
            'movie_id' => $movie->id,
        ]);
    }

    public function test_destroy_redirects_with_review_deleted_status(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'movie_id' => $movie->id]);

        $this->actingAs($user)
            ->delete(route('movies.review.destroy', $movie))
            ->assertRedirect()
            ->assertSessionHas('status', 'review-deleted');
    }

    public function test_deleting_a_review_only_removes_the_current_users_review(): void
    {
        $user1  = User::factory()->create();
        $user2  = User::factory()->create();
        $movie  = Movie::factory()->create();
        $review = Review::factory()->create(['user_id' => $user1->id, 'movie_id' => $movie->id]);

        $this->actingAs($user2)
            ->delete(route('movies.review.destroy', $movie));

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
