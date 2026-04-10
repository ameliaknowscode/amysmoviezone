<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->text('synopsis')->nullable()->after('poster');
            $table->unsignedSmallInteger('runtime')->nullable()->after('synopsis');
            $table->string('country')->nullable()->after('runtime');
            $table->string('language')->nullable()->after('country');
            $table->string('imdb_url', 500)->nullable()->after('language');
            $table->string('letterboxd_url', 500)->nullable()->after('imdb_url');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['synopsis', 'runtime', 'country', 'language', 'imdb_url', 'letterboxd_url']);
        });
    }
};
