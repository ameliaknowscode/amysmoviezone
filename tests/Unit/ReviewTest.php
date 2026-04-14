<?php

namespace Tests\Unit;

use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_can_be_created_with_factory(): void
    {
        $review = Review::factory()->create();

        $this->assertInstanceOf(Review::class, $review);
        $this->assertNotNull($review->id);
    }

    public function test_review_has_correct_fillable_fields(): void
    {
        $review = new Review();

        $this->assertEquals(['user_id', 'movie_id', 'body', 'watched_at', 'is_rewatch', 'has_spoilers'], $review->getFillable());
    }

    public function test_is_rewatch_is_cast_to_boolean(): void
    {
        $review = Review::factory()->create(['is_rewatch' => true]);

        $this->assertIsBool($review->is_rewatch);
        $this->assertTrue($review->is_rewatch);
    }

    public function test_is_rewatch_defaults_to_false(): void
    {
        $review = Review::factory()->create();

        $this->assertFalse($review->is_rewatch);
    }

    public function test_review_belongs_to_a_user(): void
    {
        $review = Review::factory()->create();

        $this->assertInstanceOf(User::class, $review->user);
    }

    public function test_review_belongs_to_a_movie(): void
    {
        $review = Review::factory()->create();

        $this->assertInstanceOf(Movie::class, $review->movie);
    }
}
