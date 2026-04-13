<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_list_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('movie_list_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'movie_list_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_list_follows');
    }
};
