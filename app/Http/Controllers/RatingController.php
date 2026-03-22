<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Rating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Movie $movie): RedirectResponse
    {
        $request->validate([
            'stars' => ['nullable', 'integer', 'min:1', 'max:5'],
            'liked' => ['nullable', 'boolean'],
        ]);

        $data = [];

        if ($request->has('stars')) {
            $data['stars'] = $request->input('stars');
        }

        if ($request->has('liked')) {
            $data['liked'] = $request->boolean('liked');
        }

        Rating::updateOrCreate(
            ['user_id' => $request->user()->id, 'movie_id' => $movie->id],
            $data,
        );

        return back()->with('status', 'rating-saved');
    }

    public function destroy(Request $request, Movie $movie): RedirectResponse
    {
        Rating::where('user_id', $request->user()->id)
            ->where('movie_id', $movie->id)
            ->update(['stars' => null]);

        return back()->with('status', 'rating-removed');
    }
}
