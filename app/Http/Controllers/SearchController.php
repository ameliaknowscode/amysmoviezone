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

        // Build the Coen Brothers virtual entry: any film directed by Joel or Ethan counts.
        $coenNames   = ['Joel Coen', 'Ethan Coen'];
        $coens       = $allDirectors->whereIn('name', $coenNames);
        $coenIds     = $coens->pluck('id')->all();

        // Replace individual Coen entries with one virtual entry in the dropdown list.
        $directors = $allDirectors->reject(fn($d) => in_array($d->name, $coenNames))->values();
        if ($coens->isNotEmpty()) {
            $coenEntry = (object) ['id' => 'coen-brothers', 'name' => 'The Coen Brothers'];
            $directors = $directors->push($coenEntry)->sortBy('name')->values();
        }

        $ids = array_filter($request->query('directors', []));

        $actors            = collect();
        $filmsByActor      = [];
        $selectedDirectors = collect();

        if (!empty($ids)) {
            // Build display labels for selected slots.
            $selectedDirectors = collect($ids)->map(function ($id) use ($allDirectors, $coens) {
                if ($id === 'coen-brothers') {
                    return (object) ['id' => 'coen-brothers', 'name' => 'The Coen Brothers'];
                }
                return $allDirectors->firstWhere('id', (int) $id);
            })->filter()->values();

            // Cache actor intersection + connecting films per unique director combo (order-independent).
            $sortedIds = collect($ids)->sort()->values()->implode('_');
            ['actors' => $actors, 'filmsByActor' => $filmsByActor] = Cache::remember(
                "director_connections.{$sortedIds}",
                now()->addHour(),
                function () use ($ids, $coenIds, $directorTypeId, $actorTypeId) {
                    // For each slot, collect the movie IDs and actor IDs for that director.
                    $movieIdsByDirector = [];
                    $actorSets = collect($ids)->map(function ($directorId) use ($coenIds, $directorTypeId, $actorTypeId, &$movieIdsByDirector) {
                        if ($directorId === 'coen-brothers') {
                            $movieIds = Credit::whereIn('person_id', $coenIds)
                                ->where('type_id', $directorTypeId)
                                ->pluck('movie_id')
                                ->unique();
                        } else {
                            $movieIds = Credit::where('person_id', (int) $directorId)
                                ->where('type_id', $directorTypeId)
                                ->pluck('movie_id');
                        }

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
