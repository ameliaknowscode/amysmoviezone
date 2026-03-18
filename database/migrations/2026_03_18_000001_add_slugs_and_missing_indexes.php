<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Add slug column to movies and populate from existing titles.
        Schema::table('movies', function (Blueprint $table) {
            $table->string('slug')->nullable()->index()->after('title');
        });
        DB::table('movies')->orderBy('id')->each(
            fn($m) => DB::table('movies')->where('id', $m->id)->update(['slug' => Str::slug($m->title)])
        );

        // Add slug column + name index to people.
        Schema::table('people', function (Blueprint $table) {
            $table->string('slug')->nullable()->index()->after('name');
            $table->index('name');
        });
        DB::table('people')->orderBy('id')->each(
            fn($p) => DB::table('people')->where('id', $p->id)->update(['slug' => Str::slug($p->name)])
        );

        // Add name index to types (used constantly in whereHas filters).
        Schema::table('types', function (Blueprint $table) {
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropIndex(['name']);
            $table->dropColumn('slug');
        });

        Schema::table('types', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};
