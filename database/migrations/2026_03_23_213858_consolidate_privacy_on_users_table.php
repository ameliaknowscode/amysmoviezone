<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('profile_private')->default(false)->after('is_admin');
        });

        // Copy any existing privacy intent: if any of the three old flags were
        // true for a user, mark their whole profile as private.
        DB::table('users')
            ->where(function ($q) {
                $q->where('ratings_private', true)
                  ->orWhere('want_to_watch_private', true)
                  ->orWhere('watched_private', true);
            })
            ->update(['profile_private' => true]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ratings_private', 'want_to_watch_private', 'watched_private']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ratings_private')->default(false);
            $table->boolean('want_to_watch_private')->default(false);
            $table->boolean('watched_private')->default(false);
        });

        DB::table('users')
            ->where('profile_private', true)
            ->update([
                'ratings_private'       => true,
                'want_to_watch_private' => true,
                'watched_private'       => true,
            ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_private');
        });
    }
};
