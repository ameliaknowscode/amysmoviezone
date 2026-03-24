<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Type;
use App\Models\WatchlistEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovieBrowseController extends Controller
{
    private const VALID_SORTS = ['rating', 'title_asc', 'year_desc', 'year_asc', 'most_watched'];

    public function __invoke(Request $request): View
    {
        $search   = trim($request->query('search', ''));
        $director = trim($request->query('director', ''));
        $yearFrom = $request->query('year_from');
        $yearTo   = $request->query('year_to');
        $sort     = in_array($request->query('sort'), self::VALID_SORTS)
                        ? $request->query('sort') : 'rating';

        $query = Movie::withAvg('ratings', 'stars');

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($yearFrom) {
            $query->where('release_year', '>=', $yearFrom);
        }

        if ($yearTo) {
            $query->where('release_year', '<=', $yearTo);
        }

        if ($director) {
            $directorTypeId = Type::where('name', 'Director')->value('id');
            if ($directorTypeId) {
                $query->whereHas('credits', function ($q) use ($director, $directorTypeId) {
                    $q->where('type_id', $directorTypeId)
                      ->whereHas('person', fn($p) => $p->where('name', 'like', '%' . $director . '%'));
                });
            }
        }

        match ($sort) {
            'title_asc'    => $query->orderBy('title'),
            'year_desc'    => $query->orderByDesc('release_year')->orderBy('title'),
            'year_asc'     => $query->orderBy('release_year')->orderBy('title'),
            'most_watched' => $query->orderByDesc(
                                WatchlistEntry::selectRaw('COUNT(*)')
                                    ->whereColumn('movie_id', 'movies.id')
                                    ->where('list_type', WatchlistEntry::WATCHED)
                              )->orderBy('title'),
            default        => $query->orderByDesc('ratings_avg_stars')->orderBy('title'),
        };

        $years      = Movie::whereNotNull('release_year')->distinct()->orderByDesc('release_year')->pluck('release_year');
        $movies     = $query->paginate(72)->withQueryString();
        $hasFilters = $search || $director || $yearFrom || $yearTo || $sort !== 'rating';

        return view('movies.browse', compact(
            'movies', 'search', 'director', 'yearFrom', 'yearTo', 'sort', 'years', 'hasFilters'
        ));
    }
}
