<?php

namespace App\Actions;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;

class SyncCredits
{
    public static function for(Movie|Person $owner, array $rows): void
    {
        $owner->credits()->delete();

        $anchorKey = $owner instanceof Movie ? 'movie_id' : 'person_id';
        $rowKey    = $owner instanceof Movie ? 'person_id' : 'movie_id';

        $batch = [];
        foreach ($rows as $row) {
            if (!empty($row[$rowKey]) && !empty($row['type_id'])) {
                $batch[] = [
                    $anchorKey   => $owner->id,
                    $rowKey      => (int) $row[$rowKey],
                    'type_id'    => (int) $row['type_id'],
                    'character'  => $row['character'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($batch)) {
            Credit::insert($batch);
        }
    }
}
