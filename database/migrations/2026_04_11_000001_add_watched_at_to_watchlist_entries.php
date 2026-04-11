<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watchlist_entries', function (Blueprint $table) {
            $table->date('watched_at')->nullable()->after('list_type');
        });

        // Backfill from reviews.watched_at where available (ANSI SQL — works on MySQL and SQLite)
        DB::statement("
            UPDATE watchlist_entries
            SET watched_at = (
                SELECT MAX(r.watched_at)
                FROM reviews r
                WHERE r.user_id  = watchlist_entries.user_id
                  AND r.movie_id = watchlist_entries.movie_id
                  AND r.watched_at IS NOT NULL
            )
            WHERE list_type = 'watched'
              AND EXISTS (
                SELECT 1 FROM reviews r
                WHERE r.user_id  = watchlist_entries.user_id
                  AND r.movie_id = watchlist_entries.movie_id
                  AND r.watched_at IS NOT NULL
              )
        ");
    }

    public function down(): void
    {
        Schema::table('watchlist_entries', function (Blueprint $table) {
            $table->dropColumn('watched_at');
        });
    }
};
