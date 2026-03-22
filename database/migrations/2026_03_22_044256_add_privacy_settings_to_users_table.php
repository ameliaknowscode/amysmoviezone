<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ratings_private')->default(false)->after('avatar');
            $table->boolean('want_to_watch_private')->default(false)->after('ratings_private');
            $table->boolean('watched_private')->default(false)->after('want_to_watch_private');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ratings_private', 'want_to_watch_private', 'watched_private']);
        });
    }
};
