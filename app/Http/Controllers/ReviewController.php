<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Review;
use App\Notifications\SharedLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Movie $movie): RedirectResponse
    {
        $validated = $request->validate([
            'body'       => ['nullable', 'string', 'max:5000'],
            'watched_at' => ['nullable', 'date'],
        ]);

        $movie->reviews()->create([
            'user_id'    => $request->user()->id,
            'body'       => $validated['body'] ?? null,
            'watched_at' => $validated['watched_at'] ?? now()->toDateString(),
        ]);

        // Notify followers who have also logged this movie (lazy chunks to avoid memory spike)
        $request->user()
            ->followers()
            ->whereHas('reviews', fn($q) => $q->where('movie_id', $movie->id))
            ->lazyById()
            ->each(fn($follower) => $follower->notify(new SharedLog($request->user(), $movie)));

        return back()->with('status', 'review-saved');
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        abort_if($review->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'body'       => ['nullable', 'string', 'max:5000'],
            'watched_at' => ['nullable', 'date'],
        ]);

        $review->update([
            'body'       => $validated['body'] ?? null,
            'watched_at' => $validated['watched_at'] ?? null,
        ]);

        return back()->with('status', 'review-saved');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        abort_if($review->user_id !== $request->user()->id, 403);

        $review->delete();

        return back()->with('status', 'review-deleted');
    }
}
