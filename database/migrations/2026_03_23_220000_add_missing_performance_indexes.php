<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * reviews.movie_id — the unique constraint covers [user_id, movie_id] but
     * queries filtering by movie_id alone (public reviews feed on movie pages)
     * cannot use it. A dedicated index fixes this.
     *
     * watchlist_entries (movie_id, list_type) — BuildMovieShowData runs two
     * separate count queries each filtering by movie_id + list_type. A
     * composite index lets both counts hit the index without a table scan.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('movie_id');
        });

        Schema::table('watchlist_entries', function (Blueprint $table) {
            $table->index(['movie_id', 'list_type']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['movie_id']);
        });

        Schema::table('watchlist_entries', function (Blueprint $table) {
            $table->dropIndex(['movie_id', 'list_type']);
        });
    }
};
