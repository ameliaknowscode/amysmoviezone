<?php

namespace App\Http\Controllers;

use App\Models\MovieList;
use App\Models\Rating;
use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

        // Activity stats — watchlist counts in a single GROUP BY query
        $totalRated  = $private ? 0 : $profileUser->ratings()->count();
        $totalLogged = $private ? 0 : $profileUser->reviews()->count();

        $watchlistCounts  = $private ? collect() : $profileUser->watchlistEntries()
            ->selectRaw('list_type, COUNT(*) as total')
            ->groupBy('list_type')
            ->pluck('total', 'list_type');
        $totalWatched     = $watchlistCounts[WatchlistEntry::WATCHED] ?? 0;
        $wantToWatchCount = $watchlistCounts[WatchlistEntry::WANT_TO_WATCH] ?? 0;

        // User's own ratings keyed by movie_id — for showing stars on recent reviews
        $reviewRatings = $recentReviews->isNotEmpty()
            ? Rating::where('user_id', $profileUser->id)
                ->whereIn('movie_id', $recentReviews->pluck('movie_id'))
                ->whereNotNull('stars')
                ->get()
                ->keyBy('movie_id')
            : collect();

        $followerCount  = Cache::remember(
            "user.{$profileUser->id}.follower_count",
            now()->addMinutes(10),
            fn() => $profileUser->followers()->count()
        );
        $followingCount = Cache::remember(
            "user.{$profileUser->id}.following_count",
            now()->addMinutes(10),
            fn() => $profileUser->following()->count()
        );
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

        $paginator = null;
        $entries   = null;

        if (! ($profileUser->profile_private && Auth::id() !== $profileUser->id)) {
            $paginator = $profileUser->reviews()
                ->with('movie')
                ->whereNotNull('watched_at')
                ->orderByDesc('watched_at')
                ->orderByDesc('id')
                ->paginate(50);

            $entries = $paginator->getCollection()
                ->groupBy(fn($r) => $r->watched_at->format('Y-m'));
        }

        return view('profile.diary', compact('profileUser', 'entries', 'paginator'));
    }

    public function watchlist(string $username): View
    {
        $profileUser = User::where('username', $username)->firstOrFail();

        $wantToWatch = $profileUser->profile_private ? null
            : $profileUser->watchlistEntries()
                ->where('list_type', WatchlistEntry::WANT_TO_WATCH)
                ->with('movie')
                ->latest()
                ->paginate(48, ['*'], 'want_page');

        $watched = $profileUser->profile_private ? null
            : $profileUser->watchlistEntries()
                ->where('list_type', WatchlistEntry::WATCHED)
                ->with('movie')
                ->latest()
                ->paginate(48, ['*'], 'watched_page');

        return view('profile.watchlist', compact('profileUser', 'wantToWatch', 'watched'));
    }

    public function lists(string $username): View
    {
        $profileUser = User::where('username', $username)->firstOrFail();

        $isOwner = Auth::id() === $profileUser->id;
        $private = $profileUser->profile_private && !$isOwner;

        $lists = $private ? collect()
            : $profileUser->movieLists()
                ->withCount('items')
                ->when(!$isOwner, fn($q) => $q->where('is_public', true))
                ->orderBy('name')
                ->get();

        return view('profile.lists', compact('profileUser', 'lists', 'isOwner'));
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
