<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Recompute every reviews.is_rewatch from chronology so legacy rows
     * written by the previous count-based logic agree with the new
     * observer-maintained derivation: the first row in each (user, movie)
     * group by (watched_at IS NULL, watched_at, id) is the original;
     * everything else is a rewatch.
     *
     * Uses the query builder so it runs the same on MySQL and SQLite.
     */
    public function up(): void
    {
        DB::table('reviews')
            ->select('user_id', 'movie_id')
            ->distinct()
            ->orderBy('user_id')
            ->orderBy('movie_id')
            ->get()
            ->each(function ($group) {
                $ids = DB::table('reviews')
                    ->where('user_id',  $group->user_id)
                    ->where('movie_id', $group->movie_id)
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
            });
    }

    public function down(): void
    {
        // Data migration — no reverse.
    }
};
