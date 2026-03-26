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
        // Consolidate per-table counts into single aggregation queries
        $userCounts = User::selectRaw(
            'COUNT(*) as total_users,
             SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_users_7_days',
            [now()->subDays(7)]
        )->first();

        $ratingStats = Rating::selectRaw(
            'COUNT(CASE WHEN stars IS NOT NULL THEN 1 END) as total_ratings,
             AVG(CASE WHEN stars IS NOT NULL THEN stars END) as average_stars,
             COUNT(CASE WHEN liked = 1 THEN 1 END) as total_likes'
        )->first();

        $stats = [
            'total_users'      => $userCounts->total_users,
            'new_users_7_days' => $userCounts->new_users_7_days,
            'total_movies'     => Movie::count(),
            'total_ratings'    => $ratingStats->total_ratings,
            'average_stars'    => $ratingStats->average_stars,
            'total_likes'      => $ratingStats->total_likes,
            'total_watchlist'  => WatchlistEntry::count(),
            'total_follows'    => DB::table('follows')->count(),
        ];

        $recentUsers = User::latest()
            ->take(5)
            ->get(['id', 'name', 'username', 'email', 'email_verified_at', 'created_at', 'is_admin']);

        return view('admin.dashboard', compact('stats', 'recentUsers'));
    }
}
