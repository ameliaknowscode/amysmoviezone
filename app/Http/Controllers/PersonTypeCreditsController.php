<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Person;
use App\Models\Rating;
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

        $avgRatings = Rating::whereIn('movie_id', $credits->pluck('movie_id'))
            ->whereNotNull('stars')
            ->groupBy('movie_id')
            ->selectRaw('movie_id, ROUND(AVG(stars), 1) as avg_stars, COUNT(*) as rating_count')
            ->get()
            ->keyBy('movie_id');

        $overallAvg = $avgRatings->isNotEmpty()
            ? round($avgRatings->avg('avg_stars'), 1)
            : null;

        return view('credits.by-type', compact('person', 'type', 'credits', 'personTypes', 'avgRatings', 'overallAvg'));
    }
}
