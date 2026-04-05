<?php

namespace App\Http\Controllers;

use App\Actions\SyncCredits;
use App\Http\Requests\PersonRequest;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        SyncCredits::for($person, $request->input('credits', []));

        return redirect()->route('admin.people.index')->with('success', 'Person updated successfully.');
    }

    public function destroy(Person $person)
    {
        $person->delete();

        return redirect()->route('admin.people.index')->with('success', 'Person deleted successfully.');
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        return response()->json(
            Person::where('name', 'like', '%' . $q . '%')
                ->orderBy('name')
                ->limit(15)
                ->get(['id', 'name'])
        );
    }

}
