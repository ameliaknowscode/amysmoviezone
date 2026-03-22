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

        return view('movies.show', [
            'movie'             => $movie,
            'cast'              => $movie->getCast(),
            'crew'              => $movie->getCrew(),
            'userRating'        => $userRating,
            'userWatchlistEntry' => $userWatchlistEntry,
        ]);
    }
}
