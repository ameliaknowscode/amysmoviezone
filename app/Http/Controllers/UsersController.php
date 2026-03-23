<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function index(): View
    {
        $users = User::withCount([
            'watchlistEntries as watched_count'       => fn ($q) => $q->where('list_type', WatchlistEntry::WATCHED),
            'watchlistEntries as want_to_watch_count' => fn ($q) => $q->where('list_type', WatchlistEntry::WANT_TO_WATCH),
            'ratings as likes_count'                  => fn ($q) => $q->where('liked', true),
        ])
        ->orderBy('name')
        ->paginate(50);

        $followingIds = Auth::check()
            ? Auth::user()->following()->pluck('users.id')->flip()
            : collect();

        return view('users.directory', compact('users', 'followingIds'));
    }
}
