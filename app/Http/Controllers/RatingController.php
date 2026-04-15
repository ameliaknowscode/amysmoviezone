<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Rating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RatingController extends Controller
{
    public function store(Request $request, Movie $movie): RedirectResponse
    {
        $request->validate([
            'stars' => ['nullable', 'numeric', 'in:0.5,1,1.5,2,2.5,3,3.5,4,4.5,5'],
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

        Cache::forget("user.{$request->user()->id}.profile_stats");
        Cache::forget("movie.{$movie->id}.stats");

        return back()->with('status', 'rating-saved');
    }

    public function destroy(Request $request, Movie $movie): RedirectResponse
    {
        Rating::where('user_id', $request->user()->id)
            ->where('movie_id', $movie->id)
            ->update(['stars' => null]);

        Cache::forget("user.{$request->user()->id}.profile_stats");
        Cache::forget("movie.{$movie->id}.stats");

        return back()->with('status', 'rating-removed');
    }
}
