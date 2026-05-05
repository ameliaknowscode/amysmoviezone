<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * One-shot cleanup for junk rows that landed in the `types` table from
     * mis-aligned CSV imports. Reassigns any credits attached to non-canonical
     * types to "Actor" (the most common credit type), skipping ones that would
     * collide with the credits unique key on (movie_id, person_id, type_id).
     */
    public function up(): void
    {
        $canonical = ['Actor', 'Director', 'Writer', 'Executive Producer', 'Producer', 'Screenplay', 'Story'];

        $actorId = DB::table('types')->where('name', 'Actor')->value('id');
        if ($actorId === null) {
            return;
        }

        $junkTypeIds = DB::table('types')
            ->whereNotIn('name', $canonical)
            ->pluck('id')
            ->all();

        if (empty($junkTypeIds)) {
            return;
        }

        $junkCredits = DB::table('credits')
            ->whereIn('type_id', $junkTypeIds)
            ->get(['id', 'movie_id', 'person_id']);

        foreach ($junkCredits as $credit) {
            $collision = DB::table('credits')
                ->where('movie_id',  $credit->movie_id)
                ->where('person_id', $credit->person_id)
                ->where('type_id',   $actorId)
                ->where('id', '!=',  $credit->id)
                ->exists();

            if ($collision) {
                DB::table('credits')->where('id', $credit->id)->delete();
            } else {
                DB::table('credits')->where('id', $credit->id)->update([
                    'type_id'    => $actorId,
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('types')->whereNotIn('name', $canonical)->delete();
    }

    public function down(): void
    {
        // Data migrations are not reversible — junk rows cannot be safely restored.
    }
};
