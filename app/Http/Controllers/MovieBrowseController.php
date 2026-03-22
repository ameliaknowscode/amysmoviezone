<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovieBrowseController extends Controller
{
    public function __invoke(Request $request): View
    {
        $movies = Movie::withAvg('ratings', 'stars')
            ->orderByDesc('ratings_avg_stars')
            ->orderBy('title')
            ->paginate(72);

        return view('movies.browse', compact('movies'));
    }
}
