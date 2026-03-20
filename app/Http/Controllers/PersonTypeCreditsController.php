<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Person;
use App\Models\Type;

class PersonTypeCreditsController extends Controller
{
    public function __invoke(string $typeSlug, string $personSlug)
    {
        $type = Type::where('slug', $typeSlug)->first();
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

        $personTypes = Type::whereIn('id',
            Credit::where('person_id', $person->id)->select('type_id')->distinct()
        )->get();

        return view('credits.by-type', compact('person', 'type', 'credits', 'personTypes'));
    }
}
