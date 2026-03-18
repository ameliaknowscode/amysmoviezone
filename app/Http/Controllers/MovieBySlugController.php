<?php

namespace App\Http\Controllers;

use App\Models\Movie;

class MovieBySlugController extends Controller
{
    public function __invoke(string $movieSlug)
    {
        $movie = Movie::where('slug', $movieSlug)->first();
        abort_unless($movie, 404);

        $movie->load(['credits' => fn($q) => $q->with(['person', 'type'])->orderBy('id')]);

        return view('movies.show', [
            'movie' => $movie,
            'cast'  => $movie->getCast(),
            'crew'  => $movie->getCrew(),
        ]);
    }
}
