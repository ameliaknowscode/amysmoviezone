<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add a plain index on user_id first so MySQL can use it for the FK
        // after we remove the composite unique index.
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('user_id', 'reviews_user_id_index');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('reviews_user_id_movie_id_unique');
            $table->date('watched_at')->nullable()->after('body');
            $table->text('body')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['user_id', 'movie_id']);
            $table->dropColumn('watched_at');
            $table->text('body')->nullable(false)->change();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_user_id_index');
        });
    }
};
