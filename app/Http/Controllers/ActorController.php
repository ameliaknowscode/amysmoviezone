<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use Illuminate\Http\Request;

class ActorController extends Controller
{
    public function index()
    {
        $actors = Actor::orderBy('name')->get();
        return view('actors.index', compact('actors'));
    }

    public function create()
    {
        return view('actors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'nationality'   => 'nullable|string|max:255',
        ]);

        Actor::create($validated);

        return redirect()->route('admin.actors.index')->with('success', 'Actor added successfully.');
    }

    public function show(Actor $actor)
    {
        return view('actors.show', compact('actor'));
    }

    public function destroy(Actor $actor)
    {
        $actor->delete();

        return redirect()->route('admin.actors.index')->with('success', 'Actor deleted successfully.');
    }
}
