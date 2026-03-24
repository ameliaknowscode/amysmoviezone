<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Feed cursor queries: WHERE created_at < ? ORDER BY created_at DESC
        Schema::table('ratings', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('created_at');
            // Diary: whereNotNull('watched_at') ORDER BY watched_at DESC
            $table->index('watched_at');
        });

        Schema::table('watchlist_entries', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['watched_at']);
        });

        Schema::table('watchlist_entries', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
