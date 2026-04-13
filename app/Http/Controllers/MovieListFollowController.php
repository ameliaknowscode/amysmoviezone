<?php

namespace App\Http\Controllers;

use App\Models\MovieList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MovieListFollowController extends Controller
{
    public function store(Request $request, MovieList $movieList): RedirectResponse
    {
        $user = $request->user();

        abort_if($movieList->user_id === $user->id, 403);
        abort_unless($movieList->is_public, 403);

        $user->followedLists()->syncWithoutDetaching([$movieList->id]);

        return back()->with('success', 'You are now following this list.');
    }

    public function destroy(Request $request, MovieList $movieList): RedirectResponse
    {
        $request->user()->followedLists()->detach($movieList->id);

        return back()->with('success', 'You unfollowed this list.');
    }
}
