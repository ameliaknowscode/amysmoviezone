<?php

namespace App\Http\Controllers;

use App\Actions\BuildMovieShowData;
use App\Actions\SyncCredits;
use App\Http\Requests\MovieRequest;
use App\Models\Collection;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $direction      = $request->query('sort') === 'asc' ? 'asc' : 'desc';
        $search         = trim($request->query('search', ''));
        $directorTypeId = Cache::rememberForever('director_type_id', fn() => Type::where('name', 'Director')->value('id'));

        $movies = Movie::orderBy('release_year', $direction)
            ->when($search, fn($q) => $q
                ->where('title', 'like', '%' . $search . '%')
                ->orWhereHas('credits', fn($q) => $q
                    ->whereHas('person', fn($p) => $p->where('name', 'like', '%' . $search . '%'))
                    ->where('type_id', $directorTypeId)
                )
            )
            ->with(['credits' => fn($q) => $q
                ->with('person')
                ->where('type_id', $directorTypeId)
            ])
            ->paginate(20)
            ->appends(['sort' => $direction, 'search' => $search]);

        return view('movies.index', compact('movies', 'direction', 'search'));
    }

    public function create()
    {
        $types          = Type::orderBy('name')->get();
        $genres         = Genre::orderBy('name')->get();
        $collections    = Collection::orderBy('name')->get();
        $initialCredits = [['person_id' => '', 'query' => '', 'type_id' => '', 'character' => '']];
        return view('movies.create', compact('types', 'genres', 'collections', 'initialCredits'));
    }

    public function store(MovieRequest $request)
    {
        $validated = $request->validated();

        $movie = Movie::create([
            'title'          => $validated['title'],
            'slug'           => Str::slug($validated['title'] . ' ' . ($validated['release_year'] ?? '')),
            'release_year'   => $validated['release_year'],
            'synopsis'       => $validated['synopsis'] ?? null,
            'runtime'        => $validated['runtime'] ?? null,
            'country'        => $validated['country'] ?? null,
            'language'       => $validated['language'] ?? null,
            'imdb_url'       => $validated['imdb_url'] ?? null,
            'letterboxd_url' => $validated['letterboxd_url'] ?? null,
        ]);

        if ($request->hasFile('poster')) {
            $movie->poster = $request->file('poster')->store('posters', 'public');
            $movie->save();
        }

        SyncCredits::for($movie, $request->input('credits', []));
        $movie->genres()->sync($validated['genres'] ?? []);
        $movie->syncCollections($request->input('collections', []));
        Cache::forget('movies.release_years');

        return redirect()->route('admin.movies.index')->with('success', 'Movie added successfully.');
    }

    public function show(Movie $movie)
    {
        return view('movies.show', BuildMovieShowData::for($movie, Auth::id()));
    }

    public function edit(Movie $movie)
    {
        $types          = Type::orderBy('name')->get();
        $genres         = Genre::orderBy('name')->get();
        $collections    = Collection::orderBy('name')->get();
        $movie->load('credits.person', 'genres', 'collections');
        $initialCredits = $movie->credits->map(fn($c) => [
            'person_id' => (string) $c->person_id,
            'query'     => $c->person->name,
            'type_id'   => (string) $c->type_id,
            'character' => $c->character ?? '',
        ])->values()->toArray();
        $selectedGenres      = $movie->genres->pluck('id')->toArray();
        $selectedCollections = $movie->collections->pluck('id')->toArray();
        return view('movies.edit', compact('movie', 'types', 'genres', 'collections', 'selectedGenres', 'selectedCollections', 'initialCredits'));
    }

    public function update(MovieRequest $request, Movie $movie)
    {
        $validated = $request->validated();

        $movie->update([
            'title'          => $validated['title'],
            'slug'           => Str::slug($validated['title'] . ' ' . ($validated['release_year'] ?? '')),
            'release_year'   => $validated['release_year'],
            'synopsis'       => $validated['synopsis'] ?? null,
            'runtime'        => $validated['runtime'] ?? null,
            'country'        => $validated['country'] ?? null,
            'language'       => $validated['language'] ?? null,
            'imdb_url'       => $validated['imdb_url'] ?? null,
            'letterboxd_url' => $validated['letterboxd_url'] ?? null,
        ]);

        if ($request->boolean('remove_poster') && $movie->poster) {
            Storage::disk('public')->delete($movie->poster);
            $movie->poster = null;
            $movie->save();
        } elseif ($request->hasFile('poster')) {
            if ($movie->poster) {
                Storage::disk('public')->delete($movie->poster);
            }
            $movie->poster = $request->file('poster')->store('posters', 'public');
            $movie->save();
        }

        SyncCredits::for($movie, $request->input('credits', []));
        $movie->genres()->sync($validated['genres'] ?? []);
        $movie->syncCollections($request->input('collections', []));
        Cache::forget('movies.release_years');

        return redirect()->route('admin.movies.index')->with('success', 'Movie updated successfully.');
    }

    public function destroy(Movie $movie)
    {
        if ($movie->poster) {
            Storage::disk('public')->delete($movie->poster);
        }

        $movie->delete();
        Cache::forget('movies.release_years');

        return redirect()->route('admin.movies.index')->with('success', 'Movie deleted successfully.');
    }

}
