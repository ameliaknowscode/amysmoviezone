<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class YearReviewController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $latestYear = Review::where('user_id', $userId)
            ->whereNotNull('watched_at')
            ->selectRaw('YEAR(watched_at) as year')
            ->orderByDesc('year')
            ->value('year');

        return redirect()->route('year-review.show', $latestYear ?? now()->year);
    }

    public function show(Request $request, int $year): View
    {
        $user   = $request->user();
        $userId = $user->id;

        $availableYears = Review::where('user_id', $userId)
            ->whereNotNull('watched_at')
            ->selectRaw('YEAR(watched_at) as year')
            ->distinct()
            ->orderBy('year')
            ->pluck('year');

        $totalWatched = $user->reviews()
            ->whereYear('watched_at', $year)
            ->count();

        $totalMinutes = Review::where('reviews.user_id', $userId)
            ->whereYear('reviews.watched_at', $year)
            ->join('movies', 'movies.id', '=', 'reviews.movie_id')
            ->whereNotNull('movies.runtime')
            ->sum('movies.runtime');

        $avgRating = DB::table('reviews')
            ->join('ratings', function ($join) use ($userId) {
                $join->on('ratings.movie_id', '=', 'reviews.movie_id')
                     ->where('ratings.user_id', $userId)
                     ->whereNotNull('ratings.stars');
            })
            ->where('reviews.user_id', $userId)
            ->whereYear('reviews.watched_at', $year)
            ->avg('ratings.stars');

        $totalRewatches = $user->reviews()
            ->where('is_rewatch', true)
            ->whereYear('watched_at', $year)
            ->count();

        $highestRated = DB::table('reviews')
            ->join('ratings', function ($join) use ($userId) {
                $join->on('ratings.movie_id', '=', 'reviews.movie_id')
                     ->where('ratings.user_id', $userId)
                     ->whereNotNull('ratings.stars');
            })
            ->join('movies', 'movies.id', '=', 'reviews.movie_id')
            ->where('reviews.user_id', $userId)
            ->whereYear('reviews.watched_at', $year)
            ->select('movies.title', 'movies.slug', 'movies.poster', 'movies.release_year', 'ratings.stars')
            ->orderByDesc('ratings.stars')
            ->orderByDesc('movies.release_year')
            ->first();

        $watchedThisYear = $user->reviews()
            ->whereYear('watched_at', $year)
            ->whereNotNull('watched_at')
            ->select('watched_at')
            ->get();

        $byMonth = collect(range(1, 12))->map(fn ($month) => (object) [
            'month' => $month,
            'count' => $watchedThisYear->filter(fn ($r) => $r->watched_at->month === $month)->count(),
        ]);

        $byGenre = Review::where('reviews.user_id', $userId)
            ->whereYear('reviews.watched_at', $year)
            ->join('genre_movie', 'genre_movie.movie_id', '=', 'reviews.movie_id')
            ->join('genres', 'genres.id', '=', 'genre_movie.genre_id')
            ->selectRaw('genres.name, COUNT(*) AS count')
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $byDirector = Review::where('reviews.user_id', $userId)
            ->whereYear('reviews.watched_at', $year)
            ->join('credits', 'credits.movie_id', '=', 'reviews.movie_id')
            ->join('types', 'types.id', '=', 'credits.type_id')
            ->join('people', 'people.id', '=', 'credits.person_id')
            ->where('types.name', 'Director')
            ->selectRaw('people.id, people.name, people.slug, COUNT(*) AS count')
            ->groupBy('people.id', 'people.name', 'people.slug')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $byDecade = Review::where('reviews.user_id', $userId)
            ->whereYear('reviews.watched_at', $year)
            ->join('movies', 'movies.id', '=', 'reviews.movie_id')
            ->select('movies.release_year')
            ->get()
            ->groupBy(fn ($r) => intdiv((int) $r->release_year, 10) * 10)
            ->map(fn ($group, $decade) => (object) ['decade' => $decade, 'count' => $group->count()])
            ->sortKeys()
            ->values();

        $ratingDist = DB::table('reviews')
            ->join('ratings', function ($join) use ($userId) {
                $join->on('ratings.movie_id', '=', 'reviews.movie_id')
                     ->where('ratings.user_id', $userId)
                     ->whereNotNull('ratings.stars');
            })
            ->where('reviews.user_id', $userId)
            ->whereYear('reviews.watched_at', $year)
            ->selectRaw('ratings.stars, COUNT(DISTINCT reviews.movie_id) AS count')
            ->groupBy('ratings.stars')
            ->orderBy('ratings.stars')
            ->get()
            ->keyBy('stars');

        return view('year-review.show', compact(
            'year',
            'availableYears',
            'totalWatched',
            'totalMinutes',
            'avgRating',
            'totalRewatches',
            'highestRated',
            'byMonth',
            'byGenre',
            'byDirector',
            'byDecade',
            'ratingDist',
        ));
    }
}
