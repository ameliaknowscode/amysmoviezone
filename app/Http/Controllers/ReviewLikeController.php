<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewLike;
use App\Notifications\ReviewLiked;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewLikeController extends Controller
{
    public function store(Request $request, Review $review): RedirectResponse
    {
        $user = $request->user();

        // Cannot like your own review
        if ($review->user_id === $user->id) {
            return back();
        }

        $like = ReviewLike::firstOrCreate([
            'user_id'   => $user->id,
            'review_id' => $review->id,
        ]);

        if ($like->wasRecentlyCreated) {
            // Notify the review author (load movie for the notification payload)
            $review->load('movie');
            $review->user->notify(new ReviewLiked($user, $review));
        }

        return back();
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        ReviewLike::where('user_id', $request->user()->id)
            ->where('review_id', $review->id)
            ->delete();

        return back();
    }
}
