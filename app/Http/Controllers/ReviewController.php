<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Review;
use App\Notifications\SharedLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReviewController extends Controller
{
    public function store(Request $request, Movie $movie): RedirectResponse
    {
        $validated = $request->validate([
            'body'       => ['nullable', 'string', 'max:5000'],
            'watched_at' => ['nullable', 'date'],
        ]);

        $userId = $request->user()->id;

        $isRewatch = $movie->reviews()->where('user_id', $userId)->exists();

        $movie->reviews()->create([
            'user_id'    => $userId,
            'body'       => $validated['body'] ?? null,
            'watched_at' => $validated['watched_at'] ?? now()->toDateString(),
            'is_rewatch' => $isRewatch,
        ]);

        Cache::forget("user.{$userId}.profile_stats");

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

        Cache::forget("user.{$request->user()->id}.profile_stats");
        $review->delete();

        return back()->with('status', 'review-deleted');
    }
}
