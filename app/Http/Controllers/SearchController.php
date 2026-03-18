<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q', '');

        $movies = $query
            ? Movie::where('title', 'like', '%' . $query . '%')
                ->with(['credits' => fn($q) => $q
                    ->with('person')
                    ->whereHas('type', fn($t) => $t->where('name', 'Director'))
                ])
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
        $directors = Person::whereHas('credits', fn($q) =>
            $q->whereHas('type', fn($t) => $t->where('name', 'Director'))
        )->orderBy('name')->get();

        $ids = array_filter($request->query('directors', []));

        $actors = collect();
        $selectedDirectors = collect();

        if (!empty($ids)) {
            $selectedDirectors = $directors->whereIn('id', $ids)->values();

            // For each director, find the set of actor IDs who appeared in their films.
            // Then intersect all sets to keep only actors who worked with every director.
            $actorSets = collect($ids)->map(function ($directorId) {
                $movieIds = Credit::where('person_id', $directorId)
                    ->whereHas('type', fn($q) => $q->where('name', 'Director'))
                    ->pluck('movie_id');

                return Credit::whereIn('movie_id', $movieIds)
                    ->whereHas('type', fn($q) => $q->where('name', 'Actor'))
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
