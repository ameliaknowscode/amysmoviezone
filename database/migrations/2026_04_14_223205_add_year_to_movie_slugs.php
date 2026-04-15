<?php

use App\Models\Movie;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Regenerate all slugs to include release year, resolving duplicate-title collisions.
        Movie::all()->each(function (Movie $movie) {
            $movie->updateQuietly([
                'slug' => Str::slug($movie->title . ' ' . ($movie->release_year ?? '')),
            ]);
        });

        Schema::table('movies', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        // Revert to title-only slugs (may leave duplicates)
        Movie::all()->each(function (Movie $movie) {
            $movie->updateQuietly(['slug' => Str::slug($movie->title)]);
        });
    }
};
