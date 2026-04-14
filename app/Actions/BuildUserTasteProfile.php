<?php

namespace App\Actions;

use App\Models\Type;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BuildUserTasteProfile
{
    /** Minimum number of rated films required for a genre/director to appear in the profile. */
    private const MIN_FILMS = 2;

    /** Maximum genres and directors to surface. */
    private const MAX_GENRES    = 5;
    private const MAX_DIRECTORS = 5;

    public function execute(User $user): array
    {
        $directorTypeId = Cache::rememberForever(
            'type_id.director',
            fn () => Type::where('name', 'Director')->value('id')
        );

        $ratingCount = $user->ratings()->whereNotNull('stars')->count();

        // Genre affinities: genres where the user has rated ≥ MIN_FILMS films, ranked by avg stars
        $genres = DB::table('ratings')
            ->join('genre_movie', 'genre_movie.movie_id', '=', 'ratings.movie_id')
            ->join('genres', 'genres.id', '=', 'genre_movie.genre_id')
            ->where('ratings.user_id', $user->id)
            ->whereNotNull('ratings.stars')
            ->groupBy('genres.id', 'genres.name')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_FILMS])
            ->orderByRaw('AVG(ratings.stars) DESC')
            ->limit(self::MAX_GENRES)
            ->select(
                'genres.id',
                'genres.name',
                DB::raw('COUNT(*) as count'),
                DB::raw('ROUND(AVG(ratings.stars), 1) as avg_stars')
            )
            ->get()
            ->map(fn ($g) => (array) $g)
            ->all();

        // Director affinities: directors with ≥ MIN_FILMS rated films, ranked by avg stars
        $directors = DB::table('ratings')
            ->join('credits', function ($join) use ($directorTypeId) {
                $join->on('credits.movie_id', '=', 'ratings.movie_id')
                     ->where('credits.type_id', '=', $directorTypeId);
            })
            ->join('people', 'people.id', '=', 'credits.person_id')
            ->where('ratings.user_id', $user->id)
            ->whereNotNull('ratings.stars')
            ->groupBy('people.id', 'people.name')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_FILMS])
            ->orderByRaw('AVG(ratings.stars) DESC')
            ->limit(self::MAX_DIRECTORS)
            ->select(
                'people.id as person_id',
                'people.name',
                DB::raw('COUNT(*) as count'),
                DB::raw('ROUND(AVG(ratings.stars), 1) as avg_stars')
            )
            ->get()
            ->map(fn ($d) => (array) $d)
            ->all();

        return [
            'genres'       => $genres,
            'directors'    => $directors,
            'rating_count' => $ratingCount,
        ];
    }
}
