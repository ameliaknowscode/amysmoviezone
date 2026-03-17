<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->index('release_year');
            $table->index('title');
        });

        Schema::table('actors', function (Blueprint $table) {
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropIndex(['release_year']);
            $table->dropIndex(['title']);
        });

        Schema::table('actors', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};
