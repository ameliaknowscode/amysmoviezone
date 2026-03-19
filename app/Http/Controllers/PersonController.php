<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonRequest;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Support\Str;

class PersonController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $search    = trim($request->query('search', ''));
        $sortBy    = in_array($request->query('sort_by'), ['name', 'date_of_birth', 'nationality'])
                        ? $request->query('sort_by')
                        : 'name';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $people = Person::orderBy($sortBy, $direction)
            ->when($search, fn($q) => $q
                ->where('name', 'like', '%' . $search . '%')
                ->orWhere('nationality', 'like', '%' . $search . '%')
            )
            ->paginate(20)
            ->withQueryString();

        return view('people.index', compact('people', 'search', 'sortBy', 'direction'));
    }

    public function create()
    {
        return view('people.create');
    }

    public function store(PersonRequest $request)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);
        Person::create($validated);

        return redirect()->route('admin.people.index')->with('success', 'Person added successfully.');
    }

    public function show(Person $person)
    {
        $person->load(['credits' => fn($q) => $q->with(['movie', 'type'])
            ->join('movies', 'credits.movie_id', '=', 'movies.id')
            ->orderBy('movies.release_year', 'desc')
            ->select('credits.*')]);
        return view('people.show', compact('person'));
    }

    public function edit(Person $person)
    {
        $movies  = Movie::orderBy('title')->get();
        $types   = Type::orderBy('name')->get();
        $person->load('credits');
        $initialCredits = $person->credits->map(fn($c) => [
            'movie_id'  => (string) $c->movie_id,
            'type_id'   => (string) $c->type_id,
            'character' => $c->character ?? '',
        ])->values()->toArray();

        return view('people.edit', compact('person', 'movies', 'types', 'initialCredits'));
    }

    public function update(PersonRequest $request, Person $person)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);
        $person->update($validated);

        $this->syncCredits($person, $request->input('credits', []));

        return redirect()->route('admin.people.index')->with('success', 'Person updated successfully.');
    }

    public function destroy(Person $person)
    {
        $person->delete();

        return redirect()->route('admin.people.index')->with('success', 'Person deleted successfully.');
    }

    private function syncCredits(Person $person, array $rows): void
    {
        $person->credits()->delete();
        foreach ($rows as $row) {
            if (!empty($row['movie_id']) && !empty($row['type_id'])) {
                $person->credits()->create([
                    'movie_id'  => (int) $row['movie_id'],
                    'type_id'   => (int) $row['type_id'],
                    'character' => $row['character'] ?? null,
                ]);
            }
        }
    }
}
