<?php

namespace App\Observers;

use App\Models\Review;
use Illuminate\Support\Facades\DB;

/**
 * Keeps reviews.is_rewatch consistent with chronology — the first watch of a
 * given (user, movie) pair (by watched_at, then id) is the original; every
 * later one is a rewatch. Maintains the flag through creates, watched_at
 * edits, and deletes so the column stays correct regardless of insert order.
 *
 * The column is therefore a cache of a derived fact; the SQL recompute is the
 * source of truth and runs on every write to a (user, movie) group.
 */
class ReviewObserver
{
    public function created(Review $review): void
    {
        $this->recomputeGroup($review->user_id, $review->movie_id);
        $review->refresh();
    }

    public function updated(Review $review): void
    {
        if ($review->wasChanged('watched_at')) {
            $this->recomputeGroup($review->user_id, $review->movie_id);
            $review->refresh();
        }
    }

    public function deleted(Review $review): void
    {
        $this->recomputeGroup($review->user_id, $review->movie_id);
    }

    /**
     * Recompute is_rewatch for every review in the given (user, movie) group.
     * Ordering: dated rows before undated, then by watched_at, then by id.
     * The first row is the original (is_rewatch=false); the rest are rewatches.
     *
     * Implemented with the query builder so it works on MySQL (prod) and
     * SQLite (tests) without dialect-specific SQL.
     */
    private function recomputeGroup(int $userId, int $movieId): void
    {
        $ids = DB::table('reviews')
            ->where('user_id', $userId)
            ->where('movie_id', $movieId)
            ->orderByRaw('watched_at IS NULL')
            ->orderBy('watched_at')
            ->orderBy('id')
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('reviews')
            ->where('id', $ids->first())
            ->update(['is_rewatch' => false]);

        $rewatchIds = $ids->slice(1)->values();

        if ($rewatchIds->isNotEmpty()) {
            DB::table('reviews')
                ->whereIn('id', $rewatchIds)
                ->update(['is_rewatch' => true]);
        }
    }
}
