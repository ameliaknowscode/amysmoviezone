<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_movie', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('movie_id');
        });

        // Backfill existing rows: order each collection's films by release_year DESC
        // (matches the previous public-show ordering so nothing visibly changes).
        $collectionIds = DB::table('collection_movie')->distinct()->pluck('collection_id');

        foreach ($collectionIds as $collectionId) {
            $rows = DB::table('collection_movie')
                ->join('movies', 'movies.id', '=', 'collection_movie.movie_id')
                ->where('collection_movie.collection_id', $collectionId)
                ->orderByDesc('movies.release_year')
                ->orderBy('movies.title')
                ->select('collection_movie.movie_id')
                ->get();

            foreach ($rows as $i => $row) {
                DB::table('collection_movie')
                    ->where('collection_id', $collectionId)
                    ->where('movie_id', $row->movie_id)
                    ->update(['position' => $i + 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('collection_movie', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
