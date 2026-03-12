<?php

namespace App\Http\Controllers;

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
        return view('movies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'director'     => 'required|string|max:255',
            'release_year' => 'required|integer|min:1888|max:' . (date('Y') + 5),
        ]);

        Movie::create($validated);

        return redirect()->route('movies.index')->with('success', 'Movie added successfully.');
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();

        return redirect()->route('movies.index')->with('success', 'Movie deleted successfully.');
    }
}
