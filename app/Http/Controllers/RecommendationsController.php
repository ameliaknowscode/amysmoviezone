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
                $ratedMovieIds = $user->ratings()->pluck('movie_id')->all();

                if (count($ratedMovieIds) < self::MIN_RATINGS_NEEDED) {
                    return ['scores' => [], 'tooFew' => true, 'rated' => count($ratedMovieIds)];
                }

                // Find users who rated at least MIN_OVERLAP of the same movies
                $similarUserIds = Rating::whereIn('movie_id', $ratedMovieIds)
                    ->where('user_id', '!=', $user->id)
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(*) >= ?', [self::MIN_OVERLAP])
                    ->orderByRaw('COUNT(*) DESC')
                    ->limit(self::MAX_SIMILAR_USERS)
                    ->pluck('user_id')
                    ->all();

                if (empty($similarUserIds)) {
                    return ['scores' => [], 'tooFew' => false, 'rated' => count($ratedMovieIds)];
                }

                // Movies those users rated highly that the current user hasn't rated
                // Stored as plain arrays so L13 cache deserialization doesn't block Eloquent objects
                $scores = Rating::whereIn('user_id', $similarUserIds)
                    ->whereNotIn('movie_id', $ratedMovieIds)
                    ->whereNotNull('stars')
                    ->groupBy('movie_id')
                    ->selectRaw('movie_id, COUNT(*) as recommender_count, ROUND(AVG(stars), 1) as avg_stars')
                    ->orderByRaw('COUNT(*) DESC, AVG(stars) DESC')
                    ->limit(self::MAX_RESULTS)
                    ->get()
                    ->map(fn($s) => [
                        'movie_id'          => $s->movie_id,
                        'recommender_count' => $s->recommender_count,
                        'avg_stars'         => $s->avg_stars,
                    ])
                    ->all();

                return ['scores' => $scores, 'tooFew' => false, 'rated' => count($ratedMovieIds)];
            }
        );

        // Hydrate Movie models from cached plain-array scores (models can't be cached in L13)
        $scoresByMovieId = collect($data['scores'])->keyBy('movie_id');
        if ($scoresByMovieId->isNotEmpty()) {
            $movies = Movie::whereIn('id', $scoresByMovieId->keys())
                ->get()
                ->keyBy('id')
                ->map(function ($movie) use ($scoresByMovieId) {
                    $movie->recommender_count = $scoresByMovieId[$movie->id]['recommender_count'];
                    $movie->avg_stars         = $scoresByMovieId[$movie->id]['avg_stars'];
                    return $movie;
                })
                ->sortByDesc('recommender_count')
                ->values();
        } else {
            $movies = collect();
        }

        return view('recommendations', [
            'movies'  => $movies,
            'tooFew'  => $data['tooFew'],
            'needed'  => self::MIN_RATINGS_NEEDED,
            'rated'   => $data['rated'],
        ]);
    }
}
