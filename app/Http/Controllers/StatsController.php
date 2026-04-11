<?php

namespace App\Http\Controllers;

use App\Models\WatchlistEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatsController extends Controller
{
    public function show(Request $request): View
    {
        $user   = $request->user();
        $userId = $user->id;

        $totalWatched = WatchlistEntry::where('user_id', $userId)
            ->where('list_type', WatchlistEntry::WATCHED)
            ->count();

        $avgRating = $user->ratings()->whereNotNull('stars')->avg('stars');
        $totalRated = $user->ratings()->whereNotNull('stars')->count();

        $totalMinutes = WatchlistEntry::where('watchlist_entries.user_id', $userId)
            ->where('list_type', WatchlistEntry::WATCHED)
            ->join('movies', 'movies.id', '=', 'watchlist_entries.movie_id')
            ->whereNotNull('movies.runtime')
            ->sum('movies.runtime');

        $byDecade = WatchlistEntry::where('watchlist_entries.user_id', $userId)
            ->where('list_type', WatchlistEntry::WATCHED)
            ->join('movies', 'movies.id', '=', 'watchlist_entries.movie_id')
            ->select('movies.release_year')
            ->get()
            ->groupBy(fn($e) => intdiv((int) $e->release_year, 10) * 10)
            ->map(fn($group, $decade) => (object) ['decade' => $decade, 'count' => $group->count()])
            ->sortKeys()
            ->values();

        $byGenre = WatchlistEntry::where('watchlist_entries.user_id', $userId)
            ->where('list_type', WatchlistEntry::WATCHED)
            ->join('genre_movie', 'genre_movie.movie_id', '=', 'watchlist_entries.movie_id')
            ->join('genres', 'genres.id', '=', 'genre_movie.genre_id')
            ->selectRaw('genres.name, COUNT(*) AS count')
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $byDirector = WatchlistEntry::where('watchlist_entries.user_id', $userId)
            ->where('list_type', WatchlistEntry::WATCHED)
            ->join('credits', 'credits.movie_id', '=', 'watchlist_entries.movie_id')
            ->join('types', 'types.id', '=', 'credits.type_id')
            ->join('people', 'people.id', '=', 'credits.person_id')
            ->where('types.name', 'Director')
            ->selectRaw('people.id, people.name, people.slug, COUNT(*) AS count')
            ->groupBy('people.id', 'people.name', 'people.slug')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $byYearWatched = WatchlistEntry::where('watchlist_entries.user_id', $userId)
            ->where('list_type', WatchlistEntry::WATCHED)
            ->whereNotNull('watched_at')
            ->select('watched_at')
            ->get()
            ->groupBy(fn($e) => $e->watched_at->year)
            ->map(fn($group, $year) => (object) ['year' => $year, 'count' => $group->count()])
            ->sortKeys()
            ->values();

        $ratingDist = $user->ratings()
            ->whereNotNull('stars')
            ->selectRaw('stars, COUNT(*) AS count')
            ->groupBy('stars')
            ->orderBy('stars')
            ->get()
            ->keyBy('stars');

        return view('stats.show', compact(
            'totalWatched',
            'avgRating',
            'totalRated',
            'totalMinutes',
            'byDecade',
            'byGenre',
            'byDirector',
            'byYearWatched',
            'ratingDist',
        ));
    }
}
