<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\WatchlistEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $wantToWatch = $user->watchlistEntries()
            ->where('list_type', WatchlistEntry::WANT_TO_WATCH)
            ->with('movie')
            ->latest()
            ->get();

        $watched = $user->watchlistEntries()
            ->where('list_type', WatchlistEntry::WATCHED)
            ->with(['movie', 'movie.ratings' => fn($q) => $q->where('user_id', $user->id)])
            ->latest()
            ->get();

        // Attach the user's rating to each watched entry for convenience
        $ratings = $user->ratings()->get()->keyBy('movie_id');

        return view('watchlist.index', compact('wantToWatch', 'watched', 'ratings', 'user'));
    }

    public function store(Request $request, Movie $movie): RedirectResponse
    {
        $validated = $request->validate([
            'list_type' => ['required', 'in:want_to_watch,watched'],
        ]);

        // If already in the other list, update; otherwise create
        WatchlistEntry::updateOrCreate(
            ['user_id' => $request->user()->id, 'movie_id' => $movie->id],
            ['list_type' => $validated['list_type']],
        );

        return back()->with('status', 'watchlist-updated');
    }

    public function destroy(Request $request, Movie $movie): RedirectResponse
    {
        WatchlistEntry::where('user_id', $request->user()->id)
            ->where('movie_id', $movie->id)
            ->delete();

        return back()->with('status', 'watchlist-removed');
    }

    public function updatePrivacy(Request $request): RedirectResponse
    {
        $request->validate([
            'ratings_private'       => ['nullable', 'boolean'],
            'want_to_watch_private' => ['nullable', 'boolean'],
            'watched_private'       => ['nullable', 'boolean'],
        ]);

        $data = [];

        if ($request->has('ratings_private')) {
            $data['ratings_private'] = $request->boolean('ratings_private');
        }

        if ($request->has('want_to_watch_private')) {
            $data['want_to_watch_private'] = $request->boolean('want_to_watch_private');
        }

        if ($request->has('watched_private')) {
            $data['watched_private'] = $request->boolean('watched_private');
        }

        if (!empty($data)) {
            $request->user()->update($data);
        }

        return back()->with('status', 'privacy-updated');
    }
}
