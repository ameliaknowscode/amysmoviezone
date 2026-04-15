<?php

namespace App\Http\Controllers;

use App\Http\Requests\CollectionRequest;
use App\Models\Collection;
use Illuminate\Support\Facades\Cache;
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
        return view('collections.edit', compact('collection'));
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
            ->with(['movies' => fn($q) => $q->orderBy('release_year', 'desc')])
            ->firstOrFail();

        return view('collections.show', compact('collection'));
    }
}
