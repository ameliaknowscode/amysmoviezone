<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $direction = $request->query('sort') === 'asc' ? 'asc' : 'desc';
        $movies = Movie::orderBy('release_year', $direction)->paginate(20)->appends(['sort' => $direction]);
        return view('movies.index', compact('movies', 'direction'));
    }

    public function create()
    {
        $actors      = Actor::orderBy('name')->get();
        $initialCast = [['actor_id' => '', 'role' => '']];
        return view('movies.create', compact('actors', 'initialCast'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'director'           => 'required|string|max:255',
            'release_year'       => 'required|integer|min:1888|max:' . (date('Y') + 5),
            'cast'               => 'nullable|array',
            'cast.*.actor_id'    => 'nullable|integer|exists:actors,id',
            'cast.*.role'        => 'nullable|string|max:255',
        ]);

        $movie = Movie::create([
            'title'        => $validated['title'],
            'director'     => $validated['director'],
            'release_year' => $validated['release_year'],
        ]);

        $syncData = [];
        foreach ($request->input('cast', []) as $row) {
            if (!empty($row['actor_id'])) {
                $syncData[(int)$row['actor_id']] = ['role' => $row['role'] ?? null];
            }
        }
        $movie->actors()->sync($syncData);

        return redirect()->route('admin.movies.index')->with('success', 'Movie added successfully.');
    }

    public function show(Movie $movie)
    {
        $movie->load(['actors' => fn($q) => $q->orderBy('name')]);
        return view('movies.show', compact('movie'));
    }

    public function edit(Movie $movie)
    {
        $actors      = Actor::orderBy('name')->get();
        $movie->load('actors');
        $initialCast = $movie->actors->map(fn($a) => [
            'actor_id' => (string) $a->id,
            'role'     => $a->pivot->role ?? '',
        ])->values()->toArray();
        return view('movies.edit', compact('movie', 'actors', 'initialCast'));
    }

    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'director'           => 'required|string|max:255',
            'release_year'       => 'required|integer|min:1888|max:' . (date('Y') + 5),
            'cast'               => 'nullable|array',
            'cast.*.actor_id'    => 'nullable|integer|exists:actors,id',
            'cast.*.role'        => 'nullable|string|max:255',
        ]);

        $movie->update([
            'title'        => $validated['title'],
            'director'     => $validated['director'],
            'release_year' => $validated['release_year'],
        ]);

        $syncData = [];
        foreach ($request->input('cast', []) as $row) {
            if (!empty($row['actor_id'])) {
                $syncData[(int)$row['actor_id']] = ['role' => $row['role'] ?? null];
            }
        }
        $movie->actors()->sync($syncData);

        return redirect()->route('admin.movies.index')->with('success', 'Movie updated successfully.');
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();

        return redirect()->route('admin.movies.index')->with('success', 'Movie deleted successfully.');
    }
}
