<?php

namespace App\Console\Commands;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DcExport extends Command
{
    protected $signature = 'dc:export {--output= : Output filename (default: dc-export.json)}';

    protected $description = 'Export all people, movies, types, and credits to a JSON file for import into the Director Connections plugin';

    public function handle(): int
    {
        $filename = $this->option('output') ?: 'dc-export.json';

        $this->info('Exporting Director Connections data...');

        $people = Person::orderBy('name')->get()->map(fn ($p) => [
            'name'          => $p->name,
            'slug'          => $p->slug,
            'date_of_birth' => $p->date_of_birth,
            'date_of_death' => $p->date_of_death,
            'nationality'   => $p->nationality,
        ]);

        $movies = Movie::orderBy('title')->get()->map(fn ($m) => [
            'title'        => $m->title,
            'slug'         => $m->slug,
            'release_year' => $m->release_year,
            'poster'       => $m->poster,
        ]);

        $types = Type::orderBy('name')->get()->map(fn ($t) => [
            'name'    => $t->name,
            'slug'    => $t->slug,
            'is_crew' => (bool) $t->is_crew,
        ]);

        $credits = Credit::with(['movie', 'person', 'type'])
            ->get()
            ->map(fn ($c) => [
                'movie_slug'  => $c->movie->slug,
                'person_slug' => $c->person->slug,
                'type_slug'   => $c->type->slug,
                'character'   => $c->character,
            ]);

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'counts'      => [
                'people'  => $people->count(),
                'movies'  => $movies->count(),
                'types'   => $types->count(),
                'credits' => $credits->count(),
            ],
            'people'  => $people,
            'movies'  => $movies,
            'types'   => $types,
            'credits' => $credits,
        ];

        Storage::disk('local')->put($filename, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $path = Storage::disk('local')->path($filename);

        $this->table(
            ['Type', 'Count'],
            [
                ['People',  $people->count()],
                ['Movies',  $movies->count()],
                ['Types',   $types->count()],
                ['Credits', $credits->count()],
            ]
        );

        $this->info("Written to: {$path}");

        return self::SUCCESS;
    }
}
