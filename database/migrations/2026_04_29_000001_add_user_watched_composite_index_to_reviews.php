<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a composite index on reviews(user_id, watched_at) to support the
 * diary, Year in Review, and Directors Discovered queries — all of which
 * filter by user_id AND watched_at and order by watched_at.
 *
 * The existing lone user_id and lone watched_at indexes remain (they're
 * still useful for queries that only filter by one of the two columns),
 * and this new composite is purely additive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['user_id', 'watched_at'], 'reviews_user_id_watched_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_user_id_watched_at_index');
        });
    }
};
