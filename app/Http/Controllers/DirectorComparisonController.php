<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Genre;
use App\Models\Person;
use App\Models\Rating;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DirectorComparisonController extends Controller
{
    public function index()
    {
        return view('compare.index');
    }

    public function show(string $slugA, string $slugB)
    {
        $personA = Person::where('slug', $slugA)->firstOrFail();
        $personB = Person::where('slug', $slugB)->firstOrFail();

        abort_if($personA->id === $personB->id, 422);

        $directorType = Cache::rememberForever('director_type_id', fn() => Type::where('name', 'Director')->value('id'));

        [$dataA, $dataB] = collect([$personA, $personB])
            ->map(fn($person) => $this->buildDirectorData($person, $directorType))
            ->all();

        abort_if($dataA['films']->isEmpty() || $dataB['films']->isEmpty(), 404);

        $sharedCast = $this->buildSharedCast(
            $dataA['movie_ids'],
            $dataB['movie_ids'],
            $directorType,
            $personA,
            $personB,
        );

        return view('compare.show', compact('personA', 'personB', 'dataA', 'dataB', 'sharedCast'));
    }

    private function buildDirectorData(Person $person, int $directorTypeId): array
    {
        $films = Credit::with(['movie.genres'])
            ->where('person_id', $person->id)
            ->where('type_id', $directorTypeId)
            ->join('movies', 'credits.movie_id', '=', 'movies.id')
            ->orderBy('movies.release_year', 'desc')
            ->select('credits.*')
            ->get();

        $movieIds = $films->pluck('movie_id');

        $avgRatings = Rating::whereIn('movie_id', $movieIds)
            ->whereNotNull('stars')
            ->groupBy('movie_id')
            ->selectRaw('movie_id, ROUND(AVG(stars), 1) as avg_stars')
            ->pluck('avg_stars', 'movie_id');

        $overallAvg = $avgRatings->isNotEmpty()
            ? round($avgRatings->avg(), 1)
            : null;

        $years = $films->pluck('movie.release_year')->filter();
        $firstYear = $years->min();
        $lastYear  = $years->max();

        $topGenres = Genre::withCount(['movies' => fn($q) => $q->whereIn('movies.id', $movieIds)])
            ->having('movies_count', '>', 0)
            ->orderByDesc('movies_count')
            ->take(5)
            ->get();

        $decades = Credit::where('person_id', $person->id)
            ->where('type_id', $directorTypeId)
            ->join('movies', 'credits.movie_id', '=', 'movies.id')
            ->whereNotNull('movies.release_year')
            ->selectRaw('FLOOR(movies.release_year / 10) * 10 as decade, COUNT(*) as count')
            ->groupBy('decade')
            ->orderBy('decade')
            ->pluck('count', 'decade');

        return compact('films', 'movieIds', 'avgRatings', 'overallAvg', 'firstYear', 'lastYear', 'topGenres', 'decades');
    }

    private function buildSharedCast(
        $movieIdsA,
        $movieIdsB,
        int $directorTypeId,
        Person $directorA,
        Person $directorB,
    ): \Illuminate\Support\Collection {
        $castInA = Credit::whereIn('movie_id', $movieIdsA)
            ->where('type_id', '!=', $directorTypeId)
            ->whereNotIn('person_id', [$directorA->id, $directorB->id])
            ->pluck('person_id')
            ->unique();

        $castInB = Credit::whereIn('movie_id', $movieIdsB)
            ->where('type_id', '!=', $directorTypeId)
            ->whereNotIn('person_id', [$directorA->id, $directorB->id])
            ->pluck('person_id')
            ->unique();

        $sharedIds = $castInA->intersect($castInB);

        if ($sharedIds->isEmpty()) {
            return collect();
        }

        $countsA = Credit::whereIn('movie_id', $movieIdsA)
            ->whereIn('person_id', $sharedIds)
            ->where('type_id', '!=', $directorTypeId)
            ->groupBy('person_id')
            ->selectRaw('person_id, COUNT(DISTINCT movie_id) as count')
            ->pluck('count', 'person_id');

        $countsB = Credit::whereIn('movie_id', $movieIdsB)
            ->whereIn('person_id', $sharedIds)
            ->where('type_id', '!=', $directorTypeId)
            ->groupBy('person_id')
            ->selectRaw('person_id, COUNT(DISTINCT movie_id) as count')
            ->pluck('count', 'person_id');

        return Person::whereIn('id', $sharedIds)
            ->get()
            ->map(fn($p) => [
                'person'       => $p,
                'films_with_a' => $countsA->get($p->id, 0),
                'films_with_b' => $countsB->get($p->id, 0),
            ])
            ->sortByDesc(fn($r) => $r['films_with_a'] + $r['films_with_b'])
            ->take(8)
            ->values();
    }
}
