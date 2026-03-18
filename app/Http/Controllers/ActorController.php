<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActorRequest;
use App\Models\Actor;
use App\Models\Movie;

class ActorController extends Controller
{
    public function index()
    {
        $actors = Actor::orderBy('name')->paginate(20);
        return view('actors.index', compact('actors'));
    }

    public function create()
    {
        $movies             = Movie::orderBy('title')->get();
        $initialFilmography = [['movie_id' => '', 'role' => '']];
        return view('actors.create', compact('movies', 'initialFilmography'));
    }

    public function store(ActorRequest $request)
    {
        $validated = $request->validated();

        $actor = Actor::create([
            'name'          => $validated['name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'nationality'   => $validated['nationality'] ?? null,
        ]);

        $this->syncFilmography($actor, $request->input('filmography', []));

        return redirect()->route('admin.actors.index')->with('success', 'Actor added successfully.');
    }

    public function show(Actor $actor)
    {
        $actor->load(['movies' => fn($q) => $q->orderBy('release_year', 'desc')]);
        return view('actors.show', compact('actor'));
    }

    public function edit(Actor $actor)
    {
        $movies = Movie::orderBy('title')->get();
        $actor->load('movies');
        $initialFilmography = $actor->movies->map(fn($m) => [
            'movie_id' => (string) $m->id,
            'role'     => $m->pivot->role ?? '',
        ])->values()->toArray();
        return view('actors.edit', compact('actor', 'movies', 'initialFilmography'));
    }

    public function update(ActorRequest $request, Actor $actor)
    {
        $validated = $request->validated();

        $actor->update([
            'name'          => $validated['name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'nationality'   => $validated['nationality'] ?? null,
        ]);

        $this->syncFilmography($actor, $request->input('filmography', []));

        return redirect()->route('admin.actors.index')->with('success', 'Actor updated successfully.');
    }

    public function destroy(Actor $actor)
    {
        $actor->delete();

        return redirect()->route('admin.actors.index')->with('success', 'Actor deleted successfully.');
    }

    private function syncFilmography(Actor $actor, array $rows): void
    {
        $syncData = [];
        foreach ($rows as $row) {
            if (!empty($row['movie_id'])) {
                $syncData[(int)$row['movie_id']] = ['role' => $row['role'] ?? null];
            }
        }
        $actor->movies()->sync($syncData);
    }
}
