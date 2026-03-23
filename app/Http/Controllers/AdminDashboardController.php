<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'      => User::count(),
            'new_users_7_days' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'total_movies'     => Movie::count(),
            'total_ratings'    => Rating::whereNotNull('stars')->count(),
            'average_stars'    => Rating::whereNotNull('stars')->avg('stars'),
            'total_likes'      => Rating::where('liked', true)->count(),
            'total_watchlist'  => WatchlistEntry::count(),
            'total_follows'    => DB::table('follows')->count(),
        ];

        $recentUsers = User::latest()
            ->take(5)
            ->get(['id', 'name', 'username', 'email', 'email_verified_at', 'created_at', 'is_admin']);

        return view('admin.dashboard', compact('stats', 'recentUsers'));
    }
}
