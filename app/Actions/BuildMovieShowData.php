<?php

namespace App\Actions;

use App\Models\Movie;
use App\Models\MovieList;
use App\Models\Rating;
use App\Models\Review;
use App\Models\User;
use App\Models\WatchlistEntry;

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
            $userLists      = MovieList::where('user_id', $userId)->orderBy('name')->get();
            $movieListIds   = $userLists->filter(
                fn($list) => $list->items()->where('movie_id', $movie->id)->exists()
            )->pluck('id');

            // Friend activity on this movie
            $followingIds = User::find($userId)->following()->pluck('users.id');

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

        // Single query for both rating stats
        $ratingStats  = $movie->ratings()->whereNotNull('stars')
            ->selectRaw('AVG(stars) as avg_stars, COUNT(*) as count_stars')
            ->first();

        // Single query for both watchlist counts
        $watchlistCounts = $movie->watchlistEntries()
            ->selectRaw('list_type, COUNT(*) as total')
            ->groupBy('list_type')
            ->pluck('total', 'list_type');

        return [
            'movie'              => $movie,
            'cast'               => $movie->getCast(),
            'crew'               => $movie->getCrew(),
            'userRating'         => $userRating,
            'userWatchlistEntry' => $userWatchlistEntry,
            'userReviews'        => $userReviews,
            'reviews'            => $reviews,
            'avgRating'          => $ratingStats->avg_stars,
            'ratingCount'        => (int) $ratingStats->count_stars,
            'wantToWatchCount'   => $watchlistCounts[WatchlistEntry::WANT_TO_WATCH] ?? 0,
            'watchedCount'       => $watchlistCounts[WatchlistEntry::WATCHED] ?? 0,
            'reviewerRatings'    => $reviewerRatings,
            'userLists'          => $userLists,
            'movieListIds'       => $movieListIds,
            'friendActivity'     => $friendActivity,
        ];
    }
}
