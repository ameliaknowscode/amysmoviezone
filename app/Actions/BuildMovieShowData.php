<?php

namespace App\Actions;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\Review;
use App\Models\WatchlistEntry;

class BuildMovieShowData
{
    public static function for(Movie $movie, ?int $userId): array
    {
        $movie->load(['credits' => fn($q) => $q->with(['person', 'type'])->orderBy('id')]);

        $userRating         = null;
        $userWatchlistEntry = null;
        $userReviews        = collect();

        if ($userId) {
            $userRating         = Rating::where('user_id', $userId)->where('movie_id', $movie->id)->first();
            $userWatchlistEntry = WatchlistEntry::where('user_id', $userId)->where('movie_id', $movie->id)->first();
            $userReviews        = Review::where('user_id', $userId)->where('movie_id', $movie->id)->latest()->get();
        }

        // Public reviews from other users, most recent first
        $reviews = $movie->reviews()
            ->with('user')
            ->when($userId, fn($q) => $q->where('user_id', '!=', $userId))
            ->whereHas('user', fn($q) => $q->where('profile_private', false))
            ->latest()
            ->get();

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
        ];
    }
}
