<?php

namespace App\Actions;

use App\Models\Type;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BuildRecommendations
{
    private const MIN_RATINGS_NEEDED       = 3;
    private const MIN_OVERLAP              = 2;   // overlapping ratings to count as a similar user
    private const MAX_SIMILAR_USERS        = 50;
    private const MAX_RESULTS              = 12;  // movies per bucket
    private const MAX_GENRE_BUCKETS        = 3;
    private const MAX_DIRECTOR_BUCKETS     = 3;
    private const MIN_COMMUNITY_RATINGS    = 2;   // min ratings a film needs to appear in genre bucket
    private const MIN_GENRE_AVG_STARS      = 4; // community avg threshold for genre bucket

    public function execute(User $user, array $tasteProfile): array
    {
        $ratedMovieIds = $user->ratings()->pluck('movie_id')->all();
        $rated         = count($ratedMovieIds);

        if ($rated < self::MIN_RATINGS_NEEDED) {
            return [
                'too_few'          => true,
                'rated'            => $rated,
                'genre_buckets'    => [],
                'director_buckets' => [],
                'collaborative'    => [],
            ];
        }

        $excludeIds = $ratedMovieIds ?: [0];

        $directorTypeId = Cache::rememberForever(
            'type_id.director',
            fn () => Type::where('name', 'Director')->value('id')
        );

        // -------------------------------------------------------------------------
        // Genre buckets — top genres → highly-rated unrated films
        // -------------------------------------------------------------------------
        $genreBuckets = [];

        foreach (array_slice($tasteProfile['genres'], 0, self::MAX_GENRE_BUCKETS) as $genre) {
            $movies = DB::table('ratings')
                ->join('genre_movie', 'genre_movie.movie_id', '=', 'ratings.movie_id')
                ->where('genre_movie.genre_id', $genre['id'])
                ->whereNotIn('ratings.movie_id', $excludeIds)
                ->whereNotNull('ratings.stars')
                ->groupBy('ratings.movie_id')
                ->havingRaw('COUNT(*) >= ? AND AVG(ratings.stars) >= ?', [self::MIN_COMMUNITY_RATINGS, self::MIN_GENRE_AVG_STARS])
                ->orderByRaw('AVG(ratings.stars) DESC, COUNT(*) DESC')
                ->limit(self::MAX_RESULTS)
                ->select(
                    'ratings.movie_id',
                    DB::raw('COUNT(*) as rating_count'),
                    DB::raw('ROUND(AVG(ratings.stars), 1) as avg_stars')
                )
                ->get()
                ->map(fn ($r) => (array) $r)
                ->all();

            if (! empty($movies)) {
                $genreBuckets[] = [
                    'genre'    => $genre['name'],
                    'genre_id' => $genre['id'],
                    'movies'   => $movies,
                ];
            }
        }

        // -------------------------------------------------------------------------
        // Director buckets — top directors → their unrated films
        // -------------------------------------------------------------------------
        $directorBuckets = [];
        foreach (array_slice($tasteProfile['directors'], 0, self::MAX_DIRECTOR_BUCKETS) as $director) {
            $movies = DB::table('movies')
                ->join('credits', function ($join) use ($directorTypeId, $director) {
                    $join->on('credits.movie_id', '=', 'movies.id')
                         ->where('credits.type_id', '=', $directorTypeId)
                         ->where('credits.person_id', '=', $director['person_id']);
                })
                ->leftJoin('ratings as r', function ($join) {
                    $join->on('r.movie_id', '=', 'movies.id')
                         ->whereNotNull('r.stars');
                })
                ->whereNotIn('movies.id', $excludeIds)
                ->groupBy('movies.id', 'movies.release_year', 'movies.title')
                ->orderByRaw('AVG(r.stars) DESC, movies.release_year DESC, movies.title ASC')
                ->limit(self::MAX_RESULTS)
                ->select(
                    'movies.id as movie_id',
                    DB::raw('COUNT(r.id) as rating_count'),
                    DB::raw('ROUND(AVG(r.stars), 1) as avg_stars')
                )
                ->get()
                ->map(fn ($r) => (array) $r)
                ->all();

            if (! empty($movies)) {
                $directorBuckets[] = [
                    'person_id' => $director['person_id'],
                    'director'  => $director['name'],
                    'movies'    => $movies,
                ];
            }
        }

        // -------------------------------------------------------------------------
        // Collaborative — users with overlapping ratings → their unrated picks
        // -------------------------------------------------------------------------
        $similarUserIds = DB::table('ratings')
            ->whereIn('movie_id', $ratedMovieIds)
            ->where('user_id', '!=', $user->id)
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_OVERLAP])
            ->orderByRaw('COUNT(*) DESC')
            ->limit(self::MAX_SIMILAR_USERS)
            ->pluck('user_id')
            ->all();

        $collaborative = [];
        if (! empty($similarUserIds)) {
            $collaborative = DB::table('ratings')
                ->whereIn('user_id', $similarUserIds)
                ->whereNotIn('movie_id', $excludeIds)
                ->whereNotNull('stars')
                ->groupBy('movie_id')
                ->selectRaw('movie_id, COUNT(*) as recommender_count, ROUND(AVG(stars), 1) as avg_stars')
                ->orderByRaw('COUNT(*) DESC, AVG(stars) DESC')
                ->limit(self::MAX_RESULTS)
                ->get()
                ->map(fn ($s) => (array) $s)
                ->all();
        }

        return [
            'too_few'          => false,
            'rated'            => $rated,
            'genre_buckets'    => $genreBuckets,
            'director_buckets' => $directorBuckets,
            'collaborative'    => $collaborative,
        ];
    }
}
