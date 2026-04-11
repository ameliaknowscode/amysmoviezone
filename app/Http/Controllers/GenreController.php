<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenreRequest;
use App\Models\Genre;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::orderBy('name')->paginate(20);
        return view('genres.index', compact('genres'));
    }

    public function create()
    {
        return view('genres.create');
    }

    public function store(GenreRequest $request)
    {
        Genre::create([
            'name' => $request->validated()['name'],
            'slug' => Str::slug($request->validated()['name']),
        ]);

        Cache::forget('genres.all');

        return redirect()->route('admin.genres.index')->with('success', 'Genre added successfully.');
    }

    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    public function update(GenreRequest $request, Genre $genre)
    {
        $genre->update([
            'name' => $request->validated()['name'],
            'slug' => Str::slug($request->validated()['name']),
        ]);

        Cache::forget('genres.all');

        return redirect()->route('admin.genres.index')->with('success', 'Genre updated successfully.');
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();

        Cache::forget('genres.all');

        return redirect()->route('admin.genres.index')->with('success', 'Genre deleted successfully.');
    }
}
