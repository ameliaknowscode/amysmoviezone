<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Person;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q', '');

        $movies = $query
            ? Movie::where('title', 'like', '%' . $query . '%')
                ->with(['credits' => fn($q) => $q
                    ->with('person')
                    ->whereHas('type', fn($t) => $t->where('name', 'Director'))
                ])
                ->orderBy('release_year', 'desc')
                ->get()
            : collect();

        $people = $query
            ? Person::where('name', 'like', '%' . $query . '%')
                ->with('credits.type')
                ->orderBy('name')
                ->get()
            : collect();

        return view('search', compact('movies', 'people', 'query'));
    }
}
