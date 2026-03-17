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
        $movies = Movie::orderBy('release_year', $direction)->get();
        return view('movies.index', compact('movies', 'direction'));
    }

    public function create()
    {
        $actors = Actor::orderBy('name')->get();
        return view('movies.create', compact('actors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'director'     => 'required|string|max:255',
            'release_year' => 'required|integer|min:1888|max:' . (date('Y') + 5),
            'actor_ids'    => 'nullable|array',
            'actor_ids.*'  => 'integer|exists:actors,id',
        ]);

        $movie = Movie::create([
            'title'        => $validated['title'],
            'director'     => $validated['director'],
            'release_year' => $validated['release_year'],
        ]);

        $movie->actors()->sync($validated['actor_ids'] ?? []);

        return redirect()->route('admin.movies.index')->with('success', 'Movie added successfully.');
    }

    public function show(Movie $movie)
    {
        $movie->load('actors');
        return view('movies.show', compact('movie'));
    }

    public function edit(Movie $movie)
    {
        $actors = Actor::orderBy('name')->get();
        $movie->load('actors');
        return view('movies.edit', compact('movie', 'actors'));
    }

    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'director'     => 'required|string|max:255',
            'release_year' => 'required|integer|min:1888|max:' . (date('Y') + 5),
            'actor_ids'    => 'nullable|array',
            'actor_ids.*'  => 'integer|exists:actors,id',
        ]);

        $movie->update([
            'title'        => $validated['title'],
            'director'     => $validated['director'],
            'release_year' => $validated['release_year'],
        ]);

        $movie->actors()->sync($validated['actor_ids'] ?? []);

        return redirect()->route('admin.movies.index')->with('success', 'Movie updated successfully.');
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();

        return redirect()->route('admin.movies.index')->with('success', 'Movie deleted successfully.');
    }
}
