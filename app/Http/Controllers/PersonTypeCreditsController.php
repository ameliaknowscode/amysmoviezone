<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Support\Str;

class PersonTypeCreditsController extends Controller
{
    public function __invoke(string $typeSlug, string $personSlug)
    {
        // Types are few — loading all is negligible, avoids needing a slug column on types.
        $type = Type::all()->first(fn($t) => Str::slug($t->name) === $typeSlug);
        abort_unless($type, 404);

        $person = Person::where('slug', $personSlug)->first();
        abort_unless($person, 404);

        $credits = Credit::with('movie')
            ->where('person_id', $person->id)
            ->where('type_id', $type->id)
            ->join('movies', 'credits.movie_id', '=', 'movies.id')
            ->orderBy('movies.release_year', 'desc')
            ->select('credits.*')
            ->get();

        abort_if($credits->isEmpty(), 404);

        return view('credits.by-type', compact('person', 'type', 'credits'));
    }
}
