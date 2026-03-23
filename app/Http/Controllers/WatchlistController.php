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

        // Derive ratings from the already-loaded movie.ratings relationship
        $ratings = $watched->mapWithKeys(
            fn($entry) => [$entry->movie_id => $entry->movie->ratings->first()]
        )->filter();

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
            'profile_private' => ['required', 'boolean'],
        ]);

        $request->user()->update([
            'profile_private' => $request->boolean('profile_private'),
        ]);

        return back()->with('status', 'privacy-updated');
    }
}
