<?php

namespace App\Http\Controllers;

use App\Http\Requests\TypeRequest;
use App\Models\Type;

class TypeController extends Controller
{
    public function index()
    {
        $types = Type::orderBy('name')->paginate(20);
        return view('types.index', compact('types'));
    }

    public function create()
    {
        return view('types.create');
    }

    public function store(TypeRequest $request)
    {
        $validated = $request->validated();
        $validated['is_crew'] = $request->has('is_crew') ? $request->boolean('is_crew') : true;
        Type::create($validated);

        return redirect()->route('admin.types.index')->with('success', 'Type added successfully.');
    }

    public function edit(Type $type)
    {
        return view('types.edit', compact('type'));
    }

    public function update(TypeRequest $request, Type $type)
    {
        $validated = $request->validated();
        $validated['is_crew'] = $request->has('is_crew') ? $request->boolean('is_crew') : true;
        $type->update($validated);

        return redirect()->route('admin.types.index')->with('success', 'Type updated successfully.');
    }

    public function destroy(Type $type)
    {
        $type->delete();

        return redirect()->route('admin.types.index')->with('success', 'Type deleted successfully.');
    }
}
