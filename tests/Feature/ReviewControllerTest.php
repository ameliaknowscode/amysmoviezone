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
        $review = Review::factory()->create();

        $this->delete(route('reviews.destroy', $review))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Store
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

    public function test_user_can_log_same_movie_multiple_times(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => 'First watch.']);

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => 'Second watch.']);

        $this->assertDatabaseCount('reviews', 2);
    }

    // -------------------------------------------------------------------------
    // Rewatch detection
    // -------------------------------------------------------------------------

    public function test_first_log_is_not_marked_as_rewatch(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => 'First watch.']);

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $user->id,
            'movie_id'   => $movie->id,
            'is_rewatch' => false,
        ]);
    }

    public function test_second_log_is_marked_as_rewatch(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        Review::factory()->create(['user_id' => $user->id, 'movie_id' => $movie->id]);

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => 'Rewatch.']);

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $user->id,
            'movie_id'   => $movie->id,
            'body'       => 'Rewatch.',
            'is_rewatch' => true,
        ]);
    }

    public function test_rewatch_flag_is_per_user_not_per_movie(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $movie = Movie::factory()->create();

        // user1 has already logged the movie
        Review::factory()->create(['user_id' => $user1->id, 'movie_id' => $movie->id]);

        // user2 logs it for the first time — should NOT be flagged as rewatch
        $this->actingAs($user2)
            ->post(route('movies.review.store', $movie), ['body' => 'My first watch.']);

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $user2->id,
            'movie_id'   => $movie->id,
            'is_rewatch' => false,
        ]);
    }

    public function test_watched_at_defaults_to_today_if_not_provided(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => 'Good film.']);

        $review = Review::where('user_id', $user->id)->first();
        $this->assertEquals(now()->toDateString(), $review->watched_at->format('Y-m-d'));
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

    public function test_body_is_optional(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.review.store', $movie), ['body' => ''])
            ->assertRedirect()
            ->assertSessionMissing('errors');

        $this->assertDatabaseHas('reviews', [
            'user_id'  => $user->id,
            'movie_id' => $movie->id,
            'body'     => null,
        ]);
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
    // Update
    // -------------------------------------------------------------------------

    public function test_user_can_update_their_own_review(): void
    {
        $user   = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'body' => 'Original.']);

        $this->actingAs($user)
            ->patch(route('reviews.update', $review), ['body' => 'Updated.', 'watched_at' => '2026-01-15'])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'body' => 'Updated.']);
        $this->assertEquals('2026-01-15', $review->fresh()->watched_at->format('Y-m-d'));
    }

    public function test_user_cannot_update_another_users_review(): void
    {
        $user1  = User::factory()->create();
        $user2  = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user1->id, 'body' => 'Original.']);

        $this->actingAs($user2)
            ->patch(route('reviews.update', $review), ['body' => 'Hacked.'])
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'body' => 'Original.']);
    }

    public function test_update_redirects_with_review_saved_status(): void
    {
        $user   = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patch(route('reviews.update', $review), ['body' => 'Updated.'])
            ->assertRedirect()
            ->assertSessionHas('status', 'review-saved');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_user_can_delete_their_own_review(): void
    {
        $user   = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete(route('reviews.destroy', $review))
            ->assertRedirect();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_destroy_redirects_with_review_deleted_status(): void
    {
        $user   = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete(route('reviews.destroy', $review))
            ->assertRedirect()
            ->assertSessionHas('status', 'review-deleted');
    }

    public function test_user_cannot_delete_another_users_review(): void
    {
        $user1  = User::factory()->create();
        $user2  = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user1->id]);

        $this->actingAs($user2)
            ->delete(route('reviews.destroy', $review))
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
