<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\WatchlistEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            'list_type'  => ['required', 'in:want_to_watch,watched'],
            'watched_at' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $attributes = ['list_type' => $validated['list_type']];

        if ($validated['list_type'] === WatchlistEntry::WATCHED) {
            $attributes['watched_at'] = $validated['watched_at'] ?? null;
        } else {
            $attributes['watched_at'] = null;
        }

        WatchlistEntry::updateOrCreate(
            ['user_id' => $request->user()->id, 'movie_id' => $movie->id],
            $attributes,
        );

        Cache::forget("user.{$request->user()->id}.profile_stats");
        Cache::forget("movie.{$movie->id}.stats");

        return back()->with('status', 'watchlist-updated');
    }

    public function updateWatchedAt(Request $request, Movie $movie): RedirectResponse
    {
        $validated = $request->validate([
            'watched_at' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        WatchlistEntry::where('user_id', $request->user()->id)
            ->where('movie_id', $movie->id)
            ->where('list_type', WatchlistEntry::WATCHED)
            ->update(['watched_at' => $validated['watched_at']]);

        Cache::forget("user.{$request->user()->id}.profile_stats");

        return back()->with('status', 'watched-at-updated');
    }

    public function destroy(Request $request, Movie $movie): RedirectResponse
    {
        WatchlistEntry::where('user_id', $request->user()->id)
            ->where('movie_id', $movie->id)
            ->delete();

        Cache::forget("user.{$request->user()->id}.profile_stats");
        Cache::forget("movie.{$movie->id}.stats");

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
