<?php

namespace Tests\Unit;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_rating_can_be_created_with_factory(): void
    {
        $rating = Rating::factory()->create();

        $this->assertInstanceOf(Rating::class, $rating);
        $this->assertNotNull($rating->id);
    }

    public function test_rating_has_correct_fillable_fields(): void
    {
        $rating = new Rating();

        $this->assertEquals(['user_id', 'movie_id', 'stars', 'liked'], $rating->getFillable());
    }

    public function test_rating_belongs_to_user(): void
    {
        $rating = Rating::factory()->create();

        $this->assertInstanceOf(User::class, $rating->user);
    }

    public function test_rating_belongs_to_movie(): void
    {
        $rating = Rating::factory()->create();

        $this->assertInstanceOf(Movie::class, $rating->movie);
    }

    public function test_liked_is_cast_to_boolean(): void
    {
        $rating = Rating::factory()->create(['liked' => true]);

        $this->assertIsBool($rating->liked);
        $this->assertTrue($rating->liked);
    }

    public function test_stars_is_cast_to_integer(): void
    {
        $rating = Rating::factory()->create(['stars' => 4]);

        $this->assertIsInt($rating->stars);
        $this->assertSame(4, $rating->stars);
    }

    public function test_stars_and_liked_can_be_null(): void
    {
        $rating = Rating::factory()->create(['stars' => null, 'liked' => null]);

        $this->assertNull($rating->stars);
        $this->assertNull($rating->liked);
    }
}
