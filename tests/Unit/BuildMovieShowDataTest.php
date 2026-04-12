<?php

namespace Tests\Unit;

use App\Actions\BuildMovieShowData;
use App\Models\Credit;
use App\Models\Movie;
use App\Models\MovieList;
use App\Models\MovieListItem;
use App\Models\Person;
use App\Models\Rating;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\Type;
use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BuildMovieShowDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_expected_keys(): void
    {
        $movie = Movie::factory()->create();

        $data = BuildMovieShowData::for($movie, null);

        $this->assertArrayHasKey('movie', $data);
        $this->assertArrayHasKey('cast', $data);
        $this->assertArrayHasKey('crew', $data);
        $this->assertArrayHasKey('userRating', $data);
        $this->assertArrayHasKey('userWatchlistEntry', $data);
        $this->assertArrayHasKey('userReviews', $data);
        $this->assertArrayHasKey('reviews', $data);
        $this->assertArrayHasKey('avgRating', $data);
        $this->assertArrayHasKey('ratingCount', $data);
        $this->assertArrayHasKey('wantToWatchCount', $data);
        $this->assertArrayHasKey('watchedCount', $data);
        $this->assertArrayHasKey('reviewerRatings', $data);
        $this->assertArrayHasKey('userLists', $data);
        $this->assertArrayHasKey('movieListIds', $data);
        $this->assertArrayHasKey('friendActivity', $data);
        $this->assertArrayHasKey('likedReviewIds', $data);
        $this->assertArrayHasKey('moreByDirector', $data);
    }

    public function test_guest_gets_null_user_specific_data(): void
    {
        $movie = Movie::factory()->create();

        $data = BuildMovieShowData::for($movie, null);

        $this->assertNull($data['userRating']);
        $this->assertNull($data['userWatchlistEntry']);
        $this->assertTrue($data['userReviews']->isEmpty());
        $this->assertTrue($data['userLists']->isEmpty());
        $this->assertTrue($data['friendActivity']->isEmpty());
    }

    public function test_returns_users_own_rating(): void
    {
        $user   = User::factory()->create();
        $movie  = Movie::factory()->create();
        $rating = Rating::factory()->create(['user_id' => $user->id, 'movie_id' => $movie->id, 'stars' => 4]);

        $data = BuildMovieShowData::for($movie, $user->id);

        $this->assertTrue($data['userRating']->is($rating));
    }

    public function test_returns_users_watchlist_entry(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();
        $entry = WatchlistEntry::factory()->create([
            'user_id'   => $user->id,
            'movie_id'  => $movie->id,
            'list_type' => WatchlistEntry::WATCHED,
        ]);

        $data = BuildMovieShowData::for($movie, $user->id);

        $this->assertTrue($data['userWatchlistEntry']->is($entry));
    }

    public function test_returns_users_own_reviews(): void
    {
        $user   = User::factory()->create();
        $movie  = Movie::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'movie_id' => $movie->id]);

        $data = BuildMovieShowData::for($movie, $user->id);

        $this->assertTrue($data['userReviews']->contains($review));
    }

    public function test_public_reviews_exclude_the_current_user(): void
    {
        $user   = User::factory()->create();
        $other  = User::factory()->create(['profile_private' => false]);
        $movie  = Movie::factory()->create();
        $own    = Review::factory()->create(['user_id' => $user->id,  'movie_id' => $movie->id]);
        $others = Review::factory()->create(['user_id' => $other->id, 'movie_id' => $movie->id]);

        $data = BuildMovieShowData::for($movie, $user->id);

        $this->assertFalse($data['reviews']->contains($own));
        $this->assertTrue($data['reviews']->contains($others));
    }

    public function test_public_reviews_exclude_private_profile_users(): void
    {
        $user    = User::factory()->create();
        $private = User::factory()->create(['profile_private' => true]);
        $movie   = Movie::factory()->create();
        Review::factory()->create(['user_id' => $private->id, 'movie_id' => $movie->id]);

        $data = BuildMovieShowData::for($movie, null);

        $this->assertTrue($data['reviews']->isEmpty());
    }

    public function test_rating_stats_are_cached(): void
    {
        $movie = Movie::factory()->create();
        Cache::flush();

        BuildMovieShowData::for($movie, null);

        $this->assertTrue(Cache::has("movie.{$movie->id}.stats"));
    }

    public function test_cached_stats_are_used_on_second_call(): void
    {
        $movie = Movie::factory()->create();
        Cache::flush();

        $data1 = BuildMovieShowData::for($movie, null);

        // Add a rating after caching — second call should return the cached (stale) count
        Rating::factory()->create(['movie_id' => $movie->id, 'stars' => 5]);

        $data2 = BuildMovieShowData::for($movie, null);

        $this->assertSame($data1['ratingCount'], $data2['ratingCount']);
    }

    public function test_returns_avg_rating_and_count(): void
    {
        $movie = Movie::factory()->create();
        Cache::flush();
        Rating::factory()->create(['movie_id' => $movie->id, 'stars' => 4]);
        Rating::factory()->create(['movie_id' => $movie->id, 'stars' => 2]);

        $data = BuildMovieShowData::for($movie, null);

        $this->assertEquals(3.0, round($data['avgRating'], 1));
        $this->assertSame(2, $data['ratingCount']);
    }

    public function test_returns_watchlist_counts(): void
    {
        $movie = Movie::factory()->create();
        Cache::flush();
        WatchlistEntry::factory()->create(['movie_id' => $movie->id, 'list_type' => WatchlistEntry::WATCHED]);
        WatchlistEntry::factory()->create(['movie_id' => $movie->id, 'list_type' => WatchlistEntry::WANT_TO_WATCH]);

        $data = BuildMovieShowData::for($movie, null);

        $this->assertSame(1, $data['watchedCount']);
        $this->assertSame(1, $data['wantToWatchCount']);
    }

    public function test_returns_user_lists_and_which_contain_the_movie(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();
        $list1 = MovieList::factory()->create(['user_id' => $user->id]);
        $list2 = MovieList::factory()->create(['user_id' => $user->id]);
        MovieListItem::create(['movie_list_id' => $list1->id, 'movie_id' => $movie->id, 'position' => 1]);

        $data = BuildMovieShowData::for($movie, $user->id);

        $this->assertCount(2, $data['userLists']);
        $this->assertTrue($data['movieListIds']->contains($list1->id));
        $this->assertFalse($data['movieListIds']->contains($list2->id));
    }

    public function test_friend_activity_includes_friends_ratings(): void
    {
        $user   = User::factory()->create();
        $friend = User::factory()->create();
        $user->following()->attach($friend->id);
        $movie  = Movie::factory()->create();
        Rating::factory()->create(['user_id' => $friend->id, 'movie_id' => $movie->id, 'stars' => 5]);

        $data = BuildMovieShowData::for($movie, $user->id);

        $this->assertCount(1, $data['friendActivity']);
        $this->assertTrue($data['friendActivity']->first()->user->is($friend));
    }

    public function test_liked_review_ids_are_returned_for_user(): void
    {
        $user   = User::factory()->create();
        $other  = User::factory()->create(['profile_private' => false]);
        $movie  = Movie::factory()->create();
        $review = Review::factory()->create(['user_id' => $other->id, 'movie_id' => $movie->id]);
        ReviewLike::create(['user_id' => $user->id, 'review_id' => $review->id]);

        $data = BuildMovieShowData::for($movie, $user->id);

        $this->assertTrue($data['likedReviewIds']->contains($review->id));
    }

    public function test_more_by_director_returns_other_films_by_same_director(): void
    {
        $directorType = Type::factory()->create(['name' => 'Director']);
        Cache::forget('director_type_id');

        $director = Person::factory()->create();
        $movie    = Movie::factory()->create();
        $other    = Movie::factory()->create();
        $unrelated = Movie::factory()->create();

        Credit::factory()->create(['movie_id' => $movie->id,   'person_id' => $director->id, 'type_id' => $directorType->id]);
        Credit::factory()->create(['movie_id' => $other->id,   'person_id' => $director->id, 'type_id' => $directorType->id]);
        // $unrelated has no credits for this director

        $data = BuildMovieShowData::for($movie, null);

        $this->assertTrue($data['moreByDirector']->contains($other));
        $this->assertFalse($data['moreByDirector']->contains($movie));
        $this->assertFalse($data['moreByDirector']->contains($unrelated));
    }

    public function test_more_by_director_is_empty_when_no_director_credited(): void
    {
        Cache::forget('director_type_id');
        $movie = Movie::factory()->create();

        $data = BuildMovieShowData::for($movie, null);

        $this->assertTrue($data['moreByDirector']->isEmpty());
    }
}
