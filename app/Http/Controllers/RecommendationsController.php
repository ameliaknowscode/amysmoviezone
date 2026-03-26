<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class RecommendationsController extends Controller
{
    private const MIN_RATINGS_NEEDED = 3;
    private const MIN_OVERLAP        = 2;
    private const MAX_SIMILAR_USERS  = 50;
    private const MAX_RESULTS        = 24;
    private const CACHE_TTL_MINUTES  = 30;

    public function index(Request $request): View
    {
        $user = $request->user();

        $data = Cache::remember(
            "recommendations.{$user->id}",
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            function () use ($user) {
                $ratedMovieIds = $user->ratings()->pluck('movie_id');

                if ($ratedMovieIds->count() < self::MIN_RATINGS_NEEDED) {
                    return [
                        'movies' => collect(),
                        'tooFew' => true,
                        'rated'  => $ratedMovieIds->count(),
                    ];
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
                    return [
                        'movies' => collect(),
                        'tooFew' => false,
                        'rated'  => $ratedMovieIds->count(),
                    ];
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

                return [
                    'movies' => $movies,
                    'tooFew' => false,
                    'rated'  => $ratedMovieIds->count(),
                ];
            }
        );

        return view('recommendations', [
            'movies'  => $data['movies'],
            'tooFew'  => $data['tooFew'],
            'needed'  => self::MIN_RATINGS_NEEDED,
            'rated'   => $data['rated'],
        ]);
    }
}
