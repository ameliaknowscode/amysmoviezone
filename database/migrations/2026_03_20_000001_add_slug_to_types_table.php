<?php

use App\Models\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('types', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        foreach (Type::all() as $type) {
            $type->update(['slug' => Str::slug($type->name)]);
        }

        Schema::table('types', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('types', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
