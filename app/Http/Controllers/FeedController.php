<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Review;
use App\Models\WatchlistEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class FeedController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $followingIds = $request->user()->following()->pluck('users.id')->toArray();
        $activities   = $this->getActivities($followingIds, null);
        $nextCursor   = $activities->last()?->created_at->toISOString();
        $hasMore      = $activities->count() === self::PER_PAGE;

        return view('feed', compact('activities', 'nextCursor', 'hasMore'));
    }

    public function more(Request $request): JsonResponse
    {
        $followingIds = $request->user()->following()->pluck('users.id')->toArray();
        $activities   = $this->getActivities($followingIds, $request->query('before'));
        $nextCursor   = $activities->last()?->created_at->toISOString();
        $hasMore      = $activities->count() === self::PER_PAGE;

        return response()->json([
            'html'        => view('feed._items', compact('activities'))->render(),
            'next_cursor' => $nextCursor,
            'has_more'    => $hasMore,
        ]);
    }

    private function getActivities(array $followingIds, ?string $before): Collection
    {
        if (empty($followingIds)) {
            return collect();
        }

        $limit = self::PER_PAGE;

        $ratings = Rating::with('movie', 'user')
            ->whereIn('user_id', $followingIds)
            ->when($before, fn($q) => $q->where('created_at', '<', $before))
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($r) => (object) ['type' => 'rating', 'item' => $r, 'created_at' => $r->created_at]);

        $reviews = Review::with('movie', 'user')
            ->whereIn('user_id', $followingIds)
            ->when($before, fn($q) => $q->where('created_at', '<', $before))
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($r) => (object) ['type' => 'review', 'item' => $r, 'created_at' => $r->created_at]);

        $watchlist = WatchlistEntry::with('movie', 'user')
            ->whereIn('user_id', $followingIds)
            ->when($before, fn($q) => $q->where('created_at', '<', $before))
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($r) => (object) ['type' => 'watchlist', 'item' => $r, 'created_at' => $r->created_at]);

        return $ratings->concat($reviews)->concat($watchlist)
            ->sortByDesc(fn($a) => $a->created_at->timestamp)
            ->take($limit)
            ->values();
    }
}
