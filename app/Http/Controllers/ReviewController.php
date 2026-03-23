<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Movie $movie): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'movie_id' => $movie->id],
            ['body' => $validated['body']],
        );

        return back()->with('status', 'review-saved');
    }

    public function destroy(Request $request, Movie $movie): RedirectResponse
    {
        Review::where('user_id', $request->user()->id)
            ->where('movie_id', $movie->id)
            ->delete();

        return back()->with('status', 'review-deleted');
    }
}
