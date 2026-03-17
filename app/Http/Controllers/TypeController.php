<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255|unique:types,name',
            'is_crew' => 'boolean',
        ]);

        $validated['is_crew'] = $request->has('is_crew') ? $request->boolean('is_crew') : true;

        Type::create($validated);

        return redirect()->route('admin.types.index')->with('success', 'Type added successfully.');
    }

    public function edit(Type $type)
    {
        return view('types.edit', compact('type'));
    }

    public function update(Request $request, Type $type)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255|unique:types,name,' . $type->id,
            'is_crew' => 'boolean',
        ]);

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
