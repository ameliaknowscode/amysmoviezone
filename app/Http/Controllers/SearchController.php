<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use App\Models\Movie;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query  = $request->query('q', '');
        $movies = $query
            ? Movie::where('title', 'like', '%' . $query . '%')->orderBy('release_year', 'desc')->get()
            : collect();
        $actors = $query
            ? Actor::where('name', 'like', '%' . $query . '%')->orderBy('name')->get()
            : collect();

        return view('search', compact('movies', 'actors', 'query'));
    }
}
