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

        $recentRatings = $profileUser->profile_private ? collect()
            : $profileUser->ratings()->with('movie')->latest()->limit(4)->get();

        $recentReviews = $profileUser->profile_private ? collect()
            : $profileUser->reviews()->with('movie')->whereNotNull('body')->latest()->limit(3)->get();

        $followerCount  = $profileUser->followers()->count();
        $followingCount = $profileUser->following()->count();
        $isFollowing    = Auth::check() && Auth::id() !== $profileUser->id
            ? Auth::user()->isFollowing($profileUser)
            : false;

        return view('profile.show', compact(
            'profileUser', 'recentRatings', 'recentReviews', 'followerCount', 'followingCount', 'isFollowing'
        ));
    }

    public function diary(string $username): View
    {
        $profileUser = User::where('username', $username)->firstOrFail();

        $entries = $profileUser->profile_private && Auth::id() !== $profileUser->id
            ? null
            : $profileUser->reviews()
                ->with('movie')
                ->whereNotNull('watched_at')
                ->orderByDesc('watched_at')
                ->orderByDesc('id')
                ->get()
                ->groupBy(fn($r) => $r->watched_at->format('Y-m'));

        return view('profile.diary', compact('profileUser', 'entries'));
    }

    public function watchlist(string $username): View
    {
        $profileUser = User::where('username', $username)->firstOrFail();

        $wantToWatch = $profileUser->profile_private ? null
            : $profileUser->watchlistEntries()
                ->where('list_type', WatchlistEntry::WANT_TO_WATCH)
                ->with('movie')
                ->latest()
                ->get();

        $watched = $profileUser->profile_private ? null
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
