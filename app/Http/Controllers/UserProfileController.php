<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function show(string $username): View
    {
        $profileUser = User::where('username', $username)->firstOrFail();

        $private = $profileUser->profile_private;

        $recentRatings = $private ? collect()
            : $profileUser->ratings()->with('movie')->latest()->limit(6)->get();

        $recentReviews = $private ? collect()
            : $profileUser->reviews()->with('movie')->whereNotNull('body')->latest()->limit(3)->get();

        // Activity stats
        $totalRated       = $private ? 0 : $profileUser->ratings()->count();
        $totalLogged      = $private ? 0 : $profileUser->reviews()->count();
        $totalWatched     = $private ? 0 : $profileUser->watchlistEntries()->where('list_type', WatchlistEntry::WATCHED)->count();
        $wantToWatchCount = $private ? 0 : $profileUser->watchlistEntries()->where('list_type', WatchlistEntry::WANT_TO_WATCH)->count();

        // User's own ratings keyed by movie_id — for showing stars on recent reviews
        $reviewRatings = $recentReviews->isNotEmpty()
            ? Rating::where('user_id', $profileUser->id)
                ->whereIn('movie_id', $recentReviews->pluck('movie_id'))
                ->whereNotNull('stars')
                ->get()
                ->keyBy('movie_id')
            : collect();

        $followerCount  = $profileUser->followers()->count();
        $followingCount = $profileUser->following()->count();
        $isFollowing    = Auth::check() && Auth::id() !== $profileUser->id
            ? Auth::user()->isFollowing($profileUser)
            : false;

        return view('profile.show', compact(
            'profileUser', 'recentRatings', 'recentReviews', 'reviewRatings',
            'followerCount', 'followingCount', 'isFollowing',
            'totalRated', 'totalLogged', 'totalWatched', 'wantToWatchCount'
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
