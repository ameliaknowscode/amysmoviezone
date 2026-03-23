<?php

namespace App\Http\Controllers;

use App\Actions\BuildMovieShowData;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieBySlugController extends Controller
{
    public function __invoke(Request $request, string $movieSlug)
    {
        $movie = Movie::where('slug', $movieSlug)->firstOrFail();

        return view('movies.show', BuildMovieShowData::for($movie, $request->user()?->id));
    }
}
