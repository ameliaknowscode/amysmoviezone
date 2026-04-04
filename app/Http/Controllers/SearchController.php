<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query          = $request->query('q', '');
        $directorTypeId = Cache::rememberForever('director_type_id', fn() => Type::where('name', 'Director')->value('id'));

        $movies = $query
            ? Movie::where('title', 'like', '%' . $query . '%')
                ->with(['credits' => fn($q) => $q
                    ->with('person')
                    ->where('type_id', $directorTypeId)
                ])
                ->withAvg('ratings', 'stars')
                ->orderBy('release_year', 'desc')
                ->get()
            : collect();

        $people = $query
            ? Person::where('name', 'like', '%' . $query . '%')
                ->with('credits.type')
                ->orderBy('name')
                ->get()
            : collect();

        return view('search', compact('movies', 'people', 'query'));
    }

    public function directorConnections(Request $request)
    {
        $directorTypeId = Cache::rememberForever('director_type_id', fn() => Type::where('name', 'Director')->value('id'));
        $actorTypeId    = Cache::rememberForever('actor_type_id',    fn() => Type::where('name', 'Actor')->value('id'));

        $allDirectors = Cache::remember('all_directors', now()->addHour(), fn() =>
            Person::whereHas('credits', fn($q) => $q->where('type_id', $directorTypeId))
                ->orderBy('name')
                ->get()
        );

        // Build virtual group entries (e.g. The Coen Brothers) from config/director_groups.php.
        // Each group collapses multiple individual directors into one dropdown option.
        // $groupMemberIds maps group ID => [person IDs] for use in queries.
        $groupMemberIds = [];
        $directors      = $allDirectors;

        foreach (config('director_groups') as $group) {
            $members = $allDirectors->whereIn('name', $group['members']);
            $groupMemberIds[$group['id']] = $members->pluck('id')->all();
            $directors = $directors->reject(fn($d) => in_array($d->name, $group['members']));
            if ($members->isNotEmpty()) {
                $directors = $directors->push((object) ['id' => $group['id'], 'name' => $group['name']]);
            }
        }

        $directors = $directors->sortBy('name')->values();

        $ids = array_filter($request->query('directors', []));

        $actors            = collect();
        $filmsByActor      = [];
        $selectedDirectors = collect();

        if (!empty($ids)) {
            // Build display labels for selected slots.
            $selectedDirectors = collect($ids)->map(
                fn($id) => $directors->firstWhere('id', isset($groupMemberIds[$id]) ? $id : (int) $id)
            )->filter()->values();

            // Cache actor intersection + connecting films per unique director combo (order-independent).
            $sortedIds = collect($ids)->sort()->values()->implode('_');
            ['actors' => $actors, 'filmsByActor' => $filmsByActor] = Cache::remember(
                "director_connections.{$sortedIds}",
                now()->addHour(),
                function () use ($ids, $groupMemberIds, $directorTypeId, $actorTypeId) {
                    // For each slot, collect the movie IDs directed by that person (or group).
                    $movieIdsByDirector = [];
                    $actorSets = collect($ids)->map(function ($directorId) use ($groupMemberIds, $directorTypeId, $actorTypeId, &$movieIdsByDirector) {
                        $movieIds = isset($groupMemberIds[$directorId])
                            ? Credit::whereIn('person_id', $groupMemberIds[$directorId])
                                ->where('type_id', $directorTypeId)
                                ->pluck('movie_id')
                                ->unique()
                            : Credit::where('person_id', (int) $directorId)
                                ->where('type_id', $directorTypeId)
                                ->pluck('movie_id');

                        $movieIdsByDirector[$directorId] = $movieIds;

                        return Credit::whereIn('movie_id', $movieIds)
                            ->where('type_id', $actorTypeId)
                            ->pluck('person_id')
                            ->unique()
                            ->values();
                    });

                    $sharedActorIds = $actorSets->reduce(
                        fn($carry, $set) => $carry === null ? $set : $carry->intersect($set)->values()
                    );

                    if (!$sharedActorIds || $sharedActorIds->isEmpty()) {
                        return ['actors' => collect(), 'filmsByActor' => []];
                    }

                    $actors = Person::whereIn('id', $sharedActorIds)->with('credits.type')->orderBy('name')->get();

                    // Build a map: actor_id => [director_id => [movie titles]]
                    $filmsByActor = [];
                    foreach ($ids as $directorId) {
                        $movieIds = $movieIdsByDirector[$directorId];
                        $credits  = Credit::whereIn('movie_id', $movieIds)
                            ->where('type_id', $actorTypeId)
                            ->whereIn('person_id', $sharedActorIds)
                            ->with('movie')
                            ->get();

                        foreach ($credits as $credit) {
                            $filmsByActor[$credit->person_id][$directorId][] = $credit->movie->title;
                        }
                    }

                    return ['actors' => $actors, 'filmsByActor' => $filmsByActor];
                }
            );
        }

        return view('director-connections', compact('directors', 'selectedDirectors', 'actors', 'filmsByActor'));
    }
}
