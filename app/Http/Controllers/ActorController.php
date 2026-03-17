<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use App\Models\Movie;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'date_of_birth'           => 'nullable|date|before:today',
            'nationality'             => 'nullable|string|max:255',
            'filmography'             => 'nullable|array',
            'filmography.*.movie_id'  => 'nullable|integer|exists:movies,id',
            'filmography.*.role'      => 'nullable|string|max:255',
        ]);

        $actor = Actor::create([
            'name'          => $validated['name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'nationality'   => $validated['nationality'] ?? null,
        ]);

        $syncData = [];
        foreach ($request->input('filmography', []) as $row) {
            if (!empty($row['movie_id'])) {
                $syncData[(int)$row['movie_id']] = ['role' => $row['role'] ?? null];
            }
        }
        $actor->movies()->sync($syncData);

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

    public function update(Request $request, Actor $actor)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'date_of_birth'           => 'nullable|date|before:today',
            'nationality'             => 'nullable|string|max:255',
            'filmography'             => 'nullable|array',
            'filmography.*.movie_id'  => 'nullable|integer|exists:movies,id',
            'filmography.*.role'      => 'nullable|string|max:255',
        ]);

        $actor->update([
            'name'          => $validated['name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'nationality'   => $validated['nationality'] ?? null,
        ]);

        $syncData = [];
        foreach ($request->input('filmography', []) as $row) {
            if (!empty($row['movie_id'])) {
                $syncData[(int)$row['movie_id']] = ['role' => $row['role'] ?? null];
            }
        }
        $actor->movies()->sync($syncData);

        return redirect()->route('admin.actors.index')->with('success', 'Actor updated successfully.');
    }

    public function destroy(Actor $actor)
    {
        $actor->delete();

        return redirect()->route('admin.actors.index')->with('success', 'Actor deleted successfully.');
    }
}
