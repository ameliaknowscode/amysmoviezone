<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Review;
use App\Models\WatchlistEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FeedController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $followingIds = Cache::remember(
            "user.{$request->user()->id}.following_ids",
            now()->addMinutes(5),
            fn() => $request->user()->following()->pluck('users.id')->toArray()
        );
        $activities   = $this->getActivities($followingIds, null);
        $nextCursor   = $activities->last()?->created_at->toISOString();
        $hasMore      = $activities->count() === self::PER_PAGE;

        return view('feed', compact('activities', 'nextCursor', 'hasMore'));
    }

    public function more(Request $request): JsonResponse
    {
        $followingIds = Cache::remember(
            "user.{$request->user()->id}.following_ids",
            now()->addMinutes(5),
            fn() => $request->user()->following()->pluck('users.id')->toArray()
        );
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

        // Single UNION ALL query to get the globally-sorted top N IDs across all
        // three activity types, then hydrate each type with a targeted whereIn.
        $rows = DB::table('ratings')
            ->select(DB::raw("'rating' as type"), 'id', 'created_at')
            ->whereIn('user_id', $followingIds)
            ->when($before, fn($q) => $q->where('created_at', '<', $before))
            ->unionAll(
                DB::table('reviews')
                    ->select(DB::raw("'review' as type"), 'id', 'created_at')
                    ->whereIn('user_id', $followingIds)
                    ->when($before, fn($q) => $q->where('created_at', '<', $before))
            )
            ->unionAll(
                DB::table('watchlist_entries')
                    ->select(DB::raw("'watchlist' as type"), 'id', 'created_at')
                    ->whereIn('user_id', $followingIds)
                    ->when($before, fn($q) => $q->where('created_at', '<', $before))
            )
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        // Eager-load only the rows that actually appear in the top N
        $ratings   = Rating::with('movie', 'user')
            ->whereIn('id', $rows->where('type', 'rating')->pluck('id'))
            ->get()->keyBy('id');

        $reviews   = Review::with('movie', 'user')
            ->whereIn('id', $rows->where('type', 'review')->pluck('id'))
            ->get()->keyBy('id');

        $watchlist = WatchlistEntry::with('movie', 'user')
            ->whereIn('id', $rows->where('type', 'watchlist')->pluck('id'))
            ->get()->keyBy('id');

        return $rows->map(function ($row) use ($ratings, $reviews, $watchlist) {
            $item = match ($row->type) {
                'rating'    => $ratings->get($row->id),
                'review'    => $reviews->get($row->id),
                'watchlist' => $watchlist->get($row->id),
            };

            return $item
                ? (object) ['type' => $row->type, 'item' => $item, 'created_at' => Carbon::parse($row->created_at)]
                : null;
        })->filter()->values();
    }
}
