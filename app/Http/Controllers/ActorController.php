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
        $movies = Movie::orderBy('title')->get();
        return view('actors.create', compact('movies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'nationality'   => 'nullable|string|max:255',
            'movie_ids'     => 'nullable|array',
            'movie_ids.*'   => 'integer|exists:movies,id',
        ]);

        $actor = Actor::create([
            'name'          => $validated['name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'nationality'   => $validated['nationality'] ?? null,
        ]);

        $actor->movies()->sync($validated['movie_ids'] ?? []);

        return redirect()->route('admin.actors.index')->with('success', 'Actor added successfully.');
    }

    public function edit(Actor $actor)
    {
        $movies = Movie::orderBy('title')->get();
        $actor->load('movies');
        return view('actors.edit', compact('actor', 'movies'));
    }

    public function update(Request $request, Actor $actor)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'nationality'   => 'nullable|string|max:255',
            'movie_ids'     => 'nullable|array',
            'movie_ids.*'   => 'integer|exists:movies,id',
        ]);

        $actor->update([
            'name'          => $validated['name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'nationality'   => $validated['nationality'] ?? null,
        ]);

        $actor->movies()->sync($validated['movie_ids'] ?? []);

        return redirect()->route('admin.actors.index')->with('success', 'Actor updated successfully.');
    }

    public function show(Actor $actor)
    {
        $actor->load(['movies' => fn($q) => $q->orderBy('release_year', 'desc')]);
        return view('actors.show', compact('actor'));
    }

    public function destroy(Actor $actor)
    {
        $actor->delete();

        return redirect()->route('admin.actors.index')->with('success', 'Actor deleted successfully.');
    }
}
