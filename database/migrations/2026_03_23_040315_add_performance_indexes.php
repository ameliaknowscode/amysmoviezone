<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The composite unique indexes on ratings and watchlist_entries cover
     * lookups by user_id (leftmost prefix) but not movie_id alone.
     * The follows composite covers follower_id but not following_id alone.
     * The credits composite covers movie_id but not person_id or type_id alone.
     */
    public function up(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->index('movie_id');
        });

        Schema::table('watchlist_entries', function (Blueprint $table) {
            $table->index('movie_id');
        });

        Schema::table('follows', function (Blueprint $table) {
            $table->index('following_id');
        });

        Schema::table('credits', function (Blueprint $table) {
            $table->index('person_id');
            $table->index('type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropIndex(['movie_id']);
        });

        Schema::table('watchlist_entries', function (Blueprint $table) {
            $table->dropIndex(['movie_id']);
        });

        Schema::table('follows', function (Blueprint $table) {
            $table->dropIndex(['following_id']);
        });

        Schema::table('credits', function (Blueprint $table) {
            $table->dropIndex(['person_id']);
            $table->dropIndex(['type_id']);
        });
    }
};
