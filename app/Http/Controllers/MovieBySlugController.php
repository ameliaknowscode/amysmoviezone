<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Support\Str;

class MovieBySlugController extends Controller
{
    public function __invoke(string $movieSlug)
    {
        $movie = Movie::all()->first(fn($m) => Str::slug($m->title) === $movieSlug);
        abort_unless($movie, 404);

        $movie->load(['credits' => fn($q) => $q->with(['person', 'type'])->orderBy('id')]);
        $cast = $movie->credits->filter(fn($c) => ! $c->type->is_crew)->values();
        $crew = $movie->credits->filter(fn($c) =>   $c->type->is_crew)->groupBy(fn($c) => $c->type->name);

        return view('movies.show', compact('movie', 'cast', 'crew'));
    }
}
