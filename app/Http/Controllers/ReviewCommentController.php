<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewComment;
use App\Notifications\ReviewCommented;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewCommentController extends Controller
{
    public function store(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        $comment = $review->comments()->create([
            'user_id' => $user->id,
            'body'    => $validated['body'],
        ]);

        if ($review->user_id !== $user->id) {
            $review->load('movie');
            $review->user->notify(new ReviewCommented($user, $review));
        }

        return back()->with('status', 'comment-posted');
    }

    public function destroy(Request $request, ReviewComment $comment): RedirectResponse
    {
        $user = $request->user();

        $isCommentOwner = $comment->user_id === $user->id;
        $isReviewOwner  = $comment->review->user_id === $user->id;

        abort_if(! $isCommentOwner && ! $isReviewOwner, 403);

        $comment->delete();

        return back()->with('status', 'comment-deleted');
    }
}
