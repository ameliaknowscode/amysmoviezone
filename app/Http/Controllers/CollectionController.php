<?php

namespace App\Http\Controllers;

use App\Http\Requests\CollectionRequest;
use App\Models\Collection;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    // ── Admin ──────────────────────────────────────────────────────────────

    public function index()
    {
        $collections = Collection::withCount('movies')->orderBy('name')->paginate(25);
        return view('collections.index', compact('collections'));
    }

    public function create()
    {
        return view('collections.create');
    }

    public function store(CollectionRequest $request)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        Collection::create($validated);
        Cache::forget('collections.all');

        return redirect()->route('admin.collections.index')->with('success', 'Collection created successfully.');
    }

    public function edit(Collection $collection)
    {
        $collection->load('movies');
        return view('collections.edit', compact('collection'));
    }

    public function attachMovie(Request $request, Collection $collection)
    {
        $request->validate(['movie_id' => 'required|exists:movies,id']);
        $movieId = $request->integer('movie_id');

        if (!$collection->movies()->where('movies.id', $movieId)->exists()) {
            $maxPosition = DB::table('collection_movie')
                ->where('collection_id', $collection->id)
                ->max('position') ?? 0;
            $collection->movies()->attach($movieId, ['position' => $maxPosition + 1]);
            Cache::forget('collections.all');
        }

        return redirect()->route('admin.collections.edit', $collection)
            ->with('success', 'Film added to collection.');
    }

    public function detachMovie(Collection $collection, Movie $movie)
    {
        $collection->movies()->detach($movie->id);
        Cache::forget('collections.all');

        return redirect()->route('admin.collections.edit', $collection)
            ->with('success', 'Film removed from collection.');
    }

    public function reorder(Request $request, Collection $collection): JsonResponse
    {
        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:movies,id',
        ]);

        foreach ($request->input('order') as $position => $movieId) {
            DB::table('collection_movie')
                ->where('collection_id', $collection->id)
                ->where('movie_id', $movieId)
                ->update(['position' => $position + 1]);
        }

        return response()->json(['ok' => true]);
    }

    public function searchMovies(Request $request, Collection $collection): JsonResponse
    {
        $q = trim($request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $alreadyIn = $collection->movies()->pluck('movies.id')->all();

        $movies = Movie::where('title', 'like', '%' . $q . '%')
            ->whereNotIn('id', $alreadyIn)
            ->orderBy('title')
            ->limit(15)
            ->get(['id', 'title', 'release_year']);

        return response()->json($movies);
    }

    public function update(CollectionRequest $request, Collection $collection)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        $collection->update($validated);
        Cache::forget('collections.all');

        return redirect()->route('admin.collections.index')->with('success', 'Collection updated successfully.');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();
        Cache::forget('collections.all');

        return redirect()->route('admin.collections.index')->with('success', 'Collection deleted successfully.');
    }

    // ── Public ─────────────────────────────────────────────────────────────

    public function publicIndex()
    {
        $collections = Collection::withCount('movies')
            ->having('movies_count', '>', 0)
            ->orderBy('name')
            ->get();

        return view('collections.show-all', compact('collections'));
    }

    public function publicShow(string $slug)
    {
        $collection = Collection::where('slug', $slug)
            ->with('movies')
            ->firstOrFail();

        return view('collections.show', compact('collection'));
    }
}
