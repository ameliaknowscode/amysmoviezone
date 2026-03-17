<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $direction = $request->query('sort') === 'asc' ? 'asc' : 'desc';
        $search    = trim($request->query('search', ''));

        $movies = Movie::orderBy('release_year', $direction)
            ->when($search, fn($q) => $q
                ->where('title', 'like', '%' . $search . '%')
                ->orWhereHas('credits', fn($q) => $q
                    ->whereHas('person', fn($p) => $p->where('name', 'like', '%' . $search . '%'))
                    ->whereHas('type',   fn($t) => $t->where('name', 'Director'))
                )
            )
            ->with(['credits' => fn($q) => $q
                ->with('person')
                ->whereHas('type', fn($t) => $t->where('name', 'Director'))
            ])
            ->paginate(20)
            ->appends(['sort' => $direction, 'search' => $search]);

        return view('movies.index', compact('movies', 'direction', 'search'));
    }

    public function create()
    {
        $people         = Person::orderBy('name')->get();
        $types          = Type::orderBy('name')->get();
        $initialCredits = [['person_id' => '', 'type_id' => '', 'character' => '']];
        return view('movies.create', compact('people', 'types', 'initialCredits'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'release_year'        => 'required|integer|min:1888|max:' . (date('Y') + 5),
            'credits'             => 'nullable|array',
            'credits.*.person_id' => 'nullable|integer|exists:people,id',
            'credits.*.type_id'   => 'nullable|integer|exists:types,id',
            'credits.*.character' => 'nullable|string|max:255',
        ]);

        $movie = Movie::create([
            'title'        => $validated['title'],
            'release_year' => $validated['release_year'],
        ]);

        $this->syncCredits($movie, $request->input('credits', []));

        return redirect()->route('admin.movies.index')->with('success', 'Movie added successfully.');
    }

    public function show(Movie $movie)
    {
        $movie->load(['credits' => fn($q) => $q->with(['person', 'type'])->orderBy('id')]);
        $cast = $movie->credits->filter(fn($c) => !$c->type->is_crew)->values();
        $crew = $movie->credits->filter(fn($c) =>  $c->type->is_crew)->groupBy(fn($c) => $c->type->name);
        return view('movies.show', compact('movie', 'cast', 'crew'));
    }

    public function edit(Movie $movie)
    {
        $people         = Person::orderBy('name')->get();
        $types          = Type::orderBy('name')->get();
        $movie->load('credits');
        $initialCredits = $movie->credits->map(fn($c) => [
            'person_id' => (string) $c->person_id,
            'type_id'   => (string) $c->type_id,
            'character' => $c->character ?? '',
        ])->values()->toArray();
        return view('movies.edit', compact('movie', 'people', 'types', 'initialCredits'));
    }

    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'release_year'        => 'required|integer|min:1888|max:' . (date('Y') + 5),
            'credits'             => 'nullable|array',
            'credits.*.person_id' => 'nullable|integer|exists:people,id',
            'credits.*.type_id'   => 'nullable|integer|exists:types,id',
            'credits.*.character' => 'nullable|string|max:255',
        ]);

        $movie->update([
            'title'        => $validated['title'],
            'release_year' => $validated['release_year'],
        ]);

        $this->syncCredits($movie, $request->input('credits', []));

        return redirect()->route('admin.movies.index')->with('success', 'Movie updated successfully.');
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();

        return redirect()->route('admin.movies.index')->with('success', 'Movie deleted successfully.');
    }

    private function syncCredits(Movie $movie, array $rows): void
    {
        $movie->credits()->delete();
        foreach ($rows as $row) {
            if (!empty($row['person_id']) && !empty($row['type_id'])) {
                $movie->credits()->create([
                    'person_id' => (int) $row['person_id'],
                    'type_id'   => (int) $row['type_id'],
                    'character' => $row['character'] ?? null,
                ]);
            }
        }
    }
}
