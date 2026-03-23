<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function show(string $username): View
    {
        $profileUser = User::where('username', $username)->firstOrFail();

        $recentRatings = $profileUser->ratings_private ? collect()
            : $profileUser->ratings()->with('movie')->latest()->limit(4)->get();

        $followerCount  = $profileUser->followers()->count();
        $followingCount = $profileUser->following()->count();
        $isFollowing    = Auth::check() && Auth::id() !== $profileUser->id
            ? Auth::user()->isFollowing($profileUser)
            : false;

        return view('profile.show', compact(
            'profileUser', 'recentRatings', 'followerCount', 'followingCount', 'isFollowing'
        ));
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

    public function followers(string $username): View
    {
        $profileUser = User::where('username', $username)->firstOrFail();
        $followers   = $profileUser->followers()->latest('follows.created_at')->get();

        return view('profile.followers', compact('profileUser', 'followers'));
    }

    public function following(string $username): View
    {
        $profileUser = User::where('username', $username)->firstOrFail();
        $following   = $profileUser->following()->latest('follows.created_at')->get();

        return view('profile.following', compact('profileUser', 'following'));
    }
}
