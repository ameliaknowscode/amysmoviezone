<?php

namespace App\Actions;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\WatchlistEntry;

class BuildMovieShowData
{
    public static function for(Movie $movie, ?int $userId): array
    {
        $movie->load(['credits' => fn($q) => $q->with(['person', 'type'])->orderBy('id')]);

        $userRating         = null;
        $userWatchlistEntry = null;

        if ($userId) {
            $userRating         = Rating::where('user_id', $userId)->where('movie_id', $movie->id)->first();
            $userWatchlistEntry = WatchlistEntry::where('user_id', $userId)->where('movie_id', $movie->id)->first();
        }

        return [
            'movie'              => $movie,
            'cast'               => $movie->getCast(),
            'crew'               => $movie->getCrew(),
            'userRating'         => $userRating,
            'userWatchlistEntry' => $userWatchlistEntry,
            'avgRating'          => $movie->ratings()->whereNotNull('stars')->avg('stars'),
            'ratingCount'        => $movie->ratings()->whereNotNull('stars')->count(),
            'wantToWatchCount'   => $movie->watchlistEntries()->where('list_type', WatchlistEntry::WANT_TO_WATCH)->count(),
            'watchedCount'       => $movie->watchlistEntries()->where('list_type', WatchlistEntry::WATCHED)->count(),
        ];
    }
}
