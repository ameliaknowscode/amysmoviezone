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

        $actors           = collect();
        $selectedDirectors = collect();

        if (!empty($ids)) {
            // Build display labels for selected slots.
            $selectedDirectors = collect($ids)->map(function ($id) use ($allDirectors, $coens) {
                if ($id === 'coen-brothers') {
                    return (object) ['id' => 'coen-brothers', 'name' => 'The Coen Brothers'];
                }
                return $allDirectors->firstWhere('id', (int) $id);
            })->filter()->values();

            // For each slot, collect the actor IDs who appeared in that director's films.
            $actorSets = collect($ids)->map(function ($directorId) use ($coenIds, $directorTypeId, $actorTypeId) {
                if ($directorId === 'coen-brothers') {
                    // Union every film where Joel OR Ethan is credited as Director.
                    $movieIds = Credit::whereIn('person_id', $coenIds)
                        ->where('type_id', $directorTypeId)
                        ->pluck('movie_id')
                        ->unique();
                } else {
                    $movieIds = Credit::where('person_id', (int) $directorId)
                        ->where('type_id', $directorTypeId)
                        ->pluck('movie_id');
                }

                return Credit::whereIn('movie_id', $movieIds)
                    ->where('type_id', $actorTypeId)
                    ->pluck('person_id')
                    ->unique()
                    ->values();
            });

            $sharedActorIds = $actorSets->reduce(
                fn($carry, $set) => $carry === null ? $set : $carry->intersect($set)->values()
            );

            $actors = $sharedActorIds && $sharedActorIds->isNotEmpty()
                ? Person::whereIn('id', $sharedActorIds)->with('credits.type')->orderBy('name')->get()
                : collect();
        }

        return view('director-connections', compact('directors', 'selectedDirectors', 'actors'));
    }
}
