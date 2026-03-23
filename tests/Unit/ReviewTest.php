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

        $this->assertEquals(['user_id', 'movie_id', 'body'], $review->getFillable());
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
