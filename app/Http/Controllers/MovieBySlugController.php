<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\WatchlistEntry;
use Illuminate\Http\Request;

class MovieBySlugController extends Controller
{
    public function __invoke(Request $request, string $movieSlug)
    {
        $movie = Movie::where('slug', $movieSlug)->first();
        abort_unless($movie, 404);

        $movie->load(['credits' => fn($q) => $q->with(['person', 'type'])->orderBy('id')]);

        $userRating        = null;
        $userWatchlistEntry = null;

        if ($userId = $request->user()?->id) {
            $userRating        = Rating::where('user_id', $userId)->where('movie_id', $movie->id)->first();
            $userWatchlistEntry = WatchlistEntry::where('user_id', $userId)->where('movie_id', $movie->id)->first();
        }

        $avgRating        = $movie->ratings()->whereNotNull('stars')->avg('stars');
        $ratingCount      = $movie->ratings()->whereNotNull('stars')->count();
        $wantToWatchCount = $movie->watchlistEntries()->where('list_type', WatchlistEntry::WANT_TO_WATCH)->count();
        $watchedCount     = $movie->watchlistEntries()->where('list_type', WatchlistEntry::WATCHED)->count();

        return view('movies.show', [
            'movie'              => $movie,
            'cast'               => $movie->getCast(),
            'crew'               => $movie->getCrew(),
            'userRating'         => $userRating,
            'userWatchlistEntry' => $userWatchlistEntry,
            'avgRating'          => $avgRating,
            'ratingCount'        => $ratingCount,
            'wantToWatchCount'   => $wantToWatchCount,
            'watchedCount'       => $watchedCount,
        ]);
    }
}
