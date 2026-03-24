<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecommendationsController extends Controller
{
    private const MIN_RATINGS_NEEDED = 3;
    private const MIN_OVERLAP        = 2;
    private const MAX_SIMILAR_USERS  = 50;
    private const MAX_RESULTS        = 24;

    public function index(Request $request): View
    {
        $user          = $request->user();
        $ratedMovieIds = $user->ratings()->pluck('movie_id');

        if ($ratedMovieIds->count() < self::MIN_RATINGS_NEEDED) {
            return view('recommendations', [
                'movies'  => collect(),
                'tooFew'  => true,
                'needed'  => self::MIN_RATINGS_NEEDED,
                'rated'   => $ratedMovieIds->count(),
            ]);
        }

        // Find users who rated at least MIN_OVERLAP of the same movies
        $similarUserIds = Rating::whereIn('movie_id', $ratedMovieIds)
            ->where('user_id', '!=', $user->id)
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_OVERLAP])
            ->orderByRaw('COUNT(*) DESC')
            ->limit(self::MAX_SIMILAR_USERS)
            ->pluck('user_id');

        if ($similarUserIds->isEmpty()) {
            return view('recommendations', [
                'movies' => collect(),
                'tooFew' => false,
                'needed' => self::MIN_RATINGS_NEEDED,
                'rated'  => $ratedMovieIds->count(),
            ]);
        }

        // Movies those users rated highly that the current user hasn't rated
        $scores = Rating::whereIn('user_id', $similarUserIds)
            ->whereNotIn('movie_id', $ratedMovieIds)
            ->whereNotNull('stars')
            ->groupBy('movie_id')
            ->selectRaw('movie_id, COUNT(*) as recommender_count, ROUND(AVG(stars), 1) as avg_stars')
            ->orderByRaw('COUNT(*) DESC, AVG(stars) DESC')
            ->limit(self::MAX_RESULTS)
            ->get()
            ->keyBy('movie_id');

        $movies = Movie::whereIn('id', $scores->keys())
            ->get()
            ->keyBy('id')
            ->map(function ($movie) use ($scores) {
                $movie->recommender_count = $scores[$movie->id]->recommender_count;
                $movie->avg_stars         = $scores[$movie->id]->avg_stars;
                return $movie;
            })
            ->sortByDesc('recommender_count')
            ->values();

        return view('recommendations', [
            'movies' => $movies,
            'tooFew' => false,
            'needed' => self::MIN_RATINGS_NEEDED,
            'rated'  => $ratedMovieIds->count(),
        ]);
    }
}
