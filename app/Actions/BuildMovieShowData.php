<?php

namespace App\Actions;

use App\Models\Movie;
use App\Models\MovieList;
use App\Models\MovieListItem;
use App\Models\Rating;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\WatchlistEntry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BuildMovieShowData
{
    public static function for(Movie $movie, ?int $userId): array
    {
        $movie->load(['credits' => fn($q) => $q->with(['person', 'type'])->orderBy('id')]);

        $userRating         = null;
        $userWatchlistEntry = null;
        $userReviews        = collect();
        $userLists          = collect();
        $movieListIds       = collect();
        $friendActivity     = collect();

        if ($userId) {
            $userRating         = Rating::where('user_id', $userId)->where('movie_id', $movie->id)->first();
            $userWatchlistEntry = WatchlistEntry::where('user_id', $userId)->where('movie_id', $movie->id)->first();
            $userReviews        = Review::where('user_id', $userId)->where('movie_id', $movie->id)->latest()->get();
            $userLists    = MovieList::where('user_id', $userId)->orderBy('name')->get();
            // Single query instead of one exists() per list
            $movieListIds = MovieListItem::whereIn('movie_list_id', $userLists->pluck('id'))
                ->where('movie_id', $movie->id)
                ->pluck('movie_list_id');

            // Friend activity on this movie
            $followingIds = DB::table('follows')->where('follower_id', $userId)->pluck('following_id');

            if ($followingIds->isNotEmpty()) {
                $friendRatings = Rating::with('user')
                    ->whereIn('user_id', $followingIds)
                    ->where('movie_id', $movie->id)
                    ->get()
                    ->keyBy('user_id');

                $friendWatchlist = WatchlistEntry::with('user')
                    ->whereIn('user_id', $followingIds)
                    ->where('movie_id', $movie->id)
                    ->get()
                    ->keyBy('user_id');

                $friendReviewCounts = Review::whereIn('user_id', $followingIds)
                    ->where('movie_id', $movie->id)
                    ->selectRaw('user_id, COUNT(*) as cnt')
                    ->groupBy('user_id')
                    ->pluck('cnt', 'user_id');

                $friendActivity = $friendRatings->keys()
                    ->merge($friendWatchlist->keys())
                    ->unique()
                    ->map(function ($uid) use ($friendRatings, $friendWatchlist, $friendReviewCounts) {
                        $rating    = $friendRatings->get($uid);
                        $watchlist = $friendWatchlist->get($uid);
                        $user      = $rating?->user ?? $watchlist?->user;

                        return $user ? (object) [
                            'user'         => $user,
                            'rating'       => $rating,
                            'watchlist'    => $watchlist,
                            'review_count' => $friendReviewCounts->get($uid, 0),
                        ] : null;
                    })
                    ->filter()
                    ->values();
            }
        }

        // Public reviews from other users, most recent first
        $reviews = $movie->reviews()
            ->with('user')
            ->withCount('likes')
            ->when($userId, fn($q) => $q->where('user_id', '!=', $userId))
            ->whereHas('user', fn($q) => $q->where('profile_private', false))
            ->latest()
            ->get();

        // Star ratings keyed by user_id for displaying alongside reviews
        $reviewerRatings = Rating::whereIn('user_id', $reviews->pluck('user_id'))
            ->where('movie_id', $movie->id)
            ->whereNotNull('stars')
            ->get()
            ->keyBy('user_id');

        // Rating stats and watchlist counts are global (same for all visitors) —
        // cache per movie for 1 hour. Busted by RatingController and WatchlistController.
        $movieStats = Cache::remember("movie.{$movie->id}.stats", now()->addHour(), function () use ($movie) {
            $ratingStats = $movie->ratings()->whereNotNull('stars')
                ->selectRaw('AVG(stars) as avg_stars, COUNT(*) as count_stars')
                ->first();

            $watchlistCounts = $movie->watchlistEntries()
                ->selectRaw('list_type, COUNT(*) as total')
                ->groupBy('list_type')
                ->pluck('total', 'list_type');

            return [
                'avg_stars'      => $ratingStats->avg_stars,
                'count_stars'    => (int) $ratingStats->count_stars,
                'want_to_watch'  => $watchlistCounts[WatchlistEntry::WANT_TO_WATCH] ?? 0,
                'watched'        => $watchlistCounts[WatchlistEntry::WATCHED] ?? 0,
            ];
        });

        return [
            'movie'              => $movie,
            'cast'               => $movie->getCast(),
            'crew'               => $movie->getCrew(),
            'userRating'         => $userRating,
            'userWatchlistEntry' => $userWatchlistEntry,
            'userReviews'        => $userReviews,
            'reviews'            => $reviews,
            'avgRating'          => $movieStats['avg_stars'],
            'ratingCount'        => $movieStats['count_stars'],
            'wantToWatchCount'   => $movieStats['want_to_watch'],
            'watchedCount'       => $movieStats['watched'],
            'reviewerRatings'    => $reviewerRatings,
            'userLists'          => $userLists,
            'movieListIds'       => $movieListIds,
            'friendActivity'     => $friendActivity,
            'likedReviewIds'     => $userId
                ? ReviewLike::where('user_id', $userId)
                    ->whereIn('review_id', $reviews->pluck('id'))
                    ->pluck('review_id')
                : collect(),
        ];
    }
}
