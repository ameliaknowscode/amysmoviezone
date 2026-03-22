<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function show(string $username): View
    {
        $profileUser = User::where('username', $username)->firstOrFail();

        $recentRatings = $profileUser->ratings_private ? collect()
            : $profileUser->ratings()->with('movie')->latest()->limit(4)->get();

        return view('profile.show', compact('profileUser', 'recentRatings'));
    }

    public function watchlist(string $username): View
    {
        $profileUser = User::where('username', $username)->firstOrFail();

        $wantToWatch = $profileUser->want_to_watch_private ? null
            : $profileUser->watchlistEntries()
                ->where('list_type', WatchlistEntry::WANT_TO_WATCH)
                ->with('movie')
                ->latest()
                ->get();

        $watched = $profileUser->watched_private ? null
            : $profileUser->watchlistEntries()
                ->where('list_type', WatchlistEntry::WATCHED)
                ->with('movie')
                ->latest()
                ->get();

        return view('profile.watchlist', compact('profileUser', 'wantToWatch', 'watched'));
    }
}
