<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonRequest;
use App\Models\Person;
use Illuminate\Support\Str;

class PersonController extends Controller
{
    public function index()
    {
        $people = Person::orderBy('name')->paginate(20);
        return view('people.index', compact('people'));
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
        return view('people.edit', compact('person'));
    }

    public function update(PersonRequest $request, Person $person)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);
        $person->update($validated);

        return redirect()->route('admin.people.index')->with('success', 'Person updated successfully.');
    }

    public function destroy(Person $person)
    {
        $person->delete();

        return redirect()->route('admin.people.index')->with('success', 'Person deleted successfully.');
    }
}
