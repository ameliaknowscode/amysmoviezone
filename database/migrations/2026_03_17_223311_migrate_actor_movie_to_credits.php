<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Copy every actor_movie row into the credits table.
     *
     * For each actor:
     *   - Find a person with the same name (case-insensitive), or create one
     *     using the actor's name, date_of_birth, and nationality.
     *   - Ensure an "Actor" type (is_crew = false) exists.
     *   - Insert a credit for each movie the actor appeared in, mapping
     *     actor_movie.role → credits.character.
     *   - Skip rows that would violate the unique(movie_id, person_id, type_id)
     *     constraint (e.g. a credit already added manually).
     */
    public function up(): void
    {
        // 1. Find or create the "Actor" type (not crew).
        $actorTypeId = DB::table('types')
            ->where('name', 'Actor')
            ->value('id');

        if ($actorTypeId === null) {
            $actorTypeId = DB::table('types')->insertGetId([
                'name'       => 'Actor',
                'is_crew'    => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // Ensure the existing type is flagged as non-crew.
            DB::table('types')
                ->where('id', $actorTypeId)
                ->update(['is_crew' => false, 'updated_at' => now()]);
        }

        // 2. Process each actor.
        DB::table('actors')->orderBy('id')->each(function ($actor) use ($actorTypeId) {

            // Find a person with the same name, or create one.
            $personId = DB::table('people')
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($actor->name)])
                ->value('id');

            if ($personId === null) {
                $personId = DB::table('people')->insertGetId([
                    'name'          => $actor->name,
                    'date_of_birth' => $actor->date_of_birth,
                    'nationality'   => $actor->nationality,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // 3. Insert a credit for every film this actor appeared in.
            DB::table('actor_movie')
                ->where('actor_id', $actor->id)
                ->orderBy('movie_id')
                ->each(function ($row) use ($actorTypeId, $personId) {

                    $alreadyExists = DB::table('credits')
                        ->where('movie_id',  $row->movie_id)
                        ->where('person_id', $personId)
                        ->where('type_id',   $actorTypeId)
                        ->exists();

                    if (!$alreadyExists) {
                        DB::table('credits')->insert([
                            'movie_id'   => $row->movie_id,
                            'person_id'  => $personId,
                            'type_id'    => $actorTypeId,
                            'character'  => $row->role,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                });
        });
    }

    /**
     * Data migrations are not reversible — credits and people records created
     * from actor data cannot be safely removed without risking deletion of
     * records that were added or edited manually after the migration ran.
     */
    public function down(): void
    {
        // intentionally left blank
    }
};
