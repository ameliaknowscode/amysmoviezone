<?php

namespace App\Http\Controllers;

use App\Actions\BuildRecommendations;
use App\Actions\BuildUserTasteProfile;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class RecommendationsController extends Controller
{
    private const MIN_RATINGS_NEEDED = 3;
    private const CACHE_TTL_MINUTES  = 30;

    public function index(Request $request): View
    {
        $user = $request->user();

        [$tasteProfile, $recommendations] = Cache::remember(
            "recommendations.v2.{$user->id}",
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            function () use ($user) {
                $tasteProfile    = (new BuildUserTasteProfile())->execute($user);

                $recommendations = (new BuildRecommendations())->execute($user, $tasteProfile);

                return [$tasteProfile, $recommendations];
            }
        );

        // Collect every movie ID referenced across all buckets in one query
        $allMovieIds = collect()
            ->merge(collect($recommendations['genre_buckets'])->flatMap(fn ($b) => collect($b['movies'])->pluck('movie_id')))
            ->merge(collect($recommendations['director_buckets'])->flatMap(fn ($b) => collect($b['movies'])->pluck('movie_id')))
            ->merge(collect($recommendations['collaborative'])->pluck('movie_id'))
            ->unique()
            ->all();

        $moviesById = Movie::whereIn('id', $allMovieIds)->get()->keyBy('id');

        // Attach score data from a plain array of rows onto hydrated Movie models
        $hydrateMovies = function (array $scores) use ($moviesById): \Illuminate\Support\Collection {
            return collect($scores)
                ->map(function ($score) use ($moviesById) {
                    $movie = $moviesById->get($score['movie_id']);
                    if (! $movie) {
                        return null;
                    }
                    foreach ($score as $key => $value) {
                        if ($key !== 'movie_id') {
                            $movie->$key = $value;
                        }
                    }
                    return $movie;
                })
                ->filter()
                ->values();
        };

        $genreBuckets = collect($recommendations['genre_buckets'])
            ->map(fn ($b) => [
                'genre'  => $b['genre'],
                'movies' => $hydrateMovies($b['movies']),
            ])
            ->filter(fn ($b) => $b['movies']->isNotEmpty())
            ->values();

        $directorBuckets = collect($recommendations['director_buckets'])
            ->map(fn ($b) => [
                'director' => $b['director'],
                'movies'   => $hydrateMovies($b['movies']),
            ])
            ->filter(fn ($b) => $b['movies']->isNotEmpty())
            ->values();

        $collaborativeMovies = $hydrateMovies($recommendations['collaborative']);

        $hasAnyRecommendations = $genreBuckets->isNotEmpty()
            || $directorBuckets->isNotEmpty()
            || $collaborativeMovies->isNotEmpty();

        return view('recommendations', [
            'tooFew'                => $recommendations['too_few'],
            'needed'                => self::MIN_RATINGS_NEEDED,
            'rated'                 => $recommendations['rated'],
            'tasteProfile'          => $tasteProfile,
            'genreBuckets'          => $genreBuckets,
            'directorBuckets'       => $directorBuckets,
            'collaborativeMovies'   => $collaborativeMovies,
            'hasAnyRecommendations' => $hasAnyRecommendations,
        ]);
    }
}
