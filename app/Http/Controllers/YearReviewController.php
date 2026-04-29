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

        // Directors Discovered: first-ever watch of a director's work in this year.
        // A director qualifies if they appear in this year's reviews but in no
        // earlier year's reviews. We attach the introducing film (the earliest
        // film of theirs the user watched in this year) via a second query.
        $directorsDiscovered = DB::table('reviews')
            ->join('credits', 'credits.movie_id', '=', 'reviews.movie_id')
            ->join('types', function ($j) {
                $j->on('types.id', '=', 'credits.type_id')->where('types.name', 'Director');
            })
            ->join('people', 'people.id', '=', 'credits.person_id')
            ->where('reviews.user_id', $userId)
            ->whereYear('reviews.watched_at', $year)
            ->whereNotIn('people.id', function ($sub) use ($userId, $year) {
                $sub->from('reviews as r2')
                    ->join('credits as c2', 'c2.movie_id', '=', 'r2.movie_id')
                    ->join('types as t2', function ($j) {
                        $j->on('t2.id', '=', 'c2.type_id')->where('t2.name', 'Director');
                    })
                    ->where('r2.user_id', $userId)
                    ->whereYear('r2.watched_at', '<', $year)
                    ->select('c2.person_id');
            })
            ->select('people.id', 'people.name', 'people.slug')
            ->selectRaw('COUNT(DISTINCT reviews.movie_id) AS count')
            ->selectRaw('MIN(reviews.watched_at) AS first_watched')
            ->groupBy('people.id', 'people.name', 'people.slug')
            ->orderBy('first_watched')
            ->orderBy('people.name')
            ->limit(10)
            ->get();

        if ($directorsDiscovered->isNotEmpty()) {
            $discoveredIds = $directorsDiscovered->pluck('id');

            $firstFilms = DB::table('reviews')
                ->join('credits', 'credits.movie_id', '=', 'reviews.movie_id')
                ->join('types', function ($j) {
                    $j->on('types.id', '=', 'credits.type_id')->where('types.name', 'Director');
                })
                ->join('movies', 'movies.id', '=', 'reviews.movie_id')
                ->where('reviews.user_id', $userId)
                ->whereYear('reviews.watched_at', $year)
                ->whereIn('credits.person_id', $discoveredIds)
                ->select(
                    'credits.person_id',
                    'movies.title',
                    'movies.slug',
                    'movies.poster',
                    'movies.release_year',
                    'reviews.watched_at',
                )
                ->orderBy('reviews.watched_at')
                ->orderBy('movies.title')
                ->get()
                ->unique('person_id')
                ->keyBy('person_id');

            foreach ($directorsDiscovered as $director) {
                $director->first_film = $firstFilms->get($director->id);
            }
        }

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
            'directorsDiscovered',
            'byDecade',
            'ratingDist',
        ));
    }
}
