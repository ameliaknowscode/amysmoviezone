<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\MovieList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovieListController extends Controller
{
    public function index(Request $request): View
    {
        $lists = $request->user()
            ->movieLists()
            ->withCount('items')
            ->latest()
            ->get();

        return view('lists.index', compact('lists'));
    }

    public function create(): View
    {
        return view('lists.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_public'   => 'boolean',
            'is_ranked'   => 'boolean',
        ]);

        $list = $request->user()->movieLists()->create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_public'   => $request->boolean('is_public', true),
            'is_ranked'   => $request->boolean('is_ranked', false),
        ]);

        return redirect()->route('lists.show', $list)
            ->with('success', 'List created.');
    }

    public function show(Request $request, MovieList $movieList): View
    {
        abort_unless($movieList->visibleTo($request->user()), 403);

        $movieList->load(['user', 'items.movie']);

        $followerCount = $movieList->followers()->count();
        $isFollowing   = $request->user()
            ? $movieList->followers()->where('user_id', $request->user()->id)->exists()
            : false;

        return view('lists.show', compact('movieList', 'followerCount', 'isFollowing'));
    }

    public function edit(Request $request, MovieList $movieList): View
    {
        abort_unless($movieList->user_id === $request->user()->id, 403);

        $movieList->load('items.movie');

        return view('lists.edit', compact('movieList'));
    }

    public function update(Request $request, MovieList $movieList): RedirectResponse
    {
        abort_unless($movieList->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_public'   => 'boolean',
            'is_ranked'   => 'boolean',
        ]);

        $movieList->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_public'   => $request->boolean('is_public', true),
            'is_ranked'   => $request->boolean('is_ranked', false),
        ]);

        return redirect()->route('lists.show', $movieList)
            ->with('success', 'List updated.');
    }

    public function destroy(Request $request, MovieList $movieList): RedirectResponse
    {
        abort_unless($movieList->user_id === $request->user()->id, 403);

        $movieList->delete();

        return redirect()->route('lists.index')
            ->with('success', 'List deleted.');
    }
}
