<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\MovieList;
use App\Models\MovieListItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MovieListItemController extends Controller
{
    public function store(Request $request, MovieList $movieList): JsonResponse|RedirectResponse
    {
        abort_unless($movieList->user_id === $request->user()->id, 403);

        $request->validate(['movie_id' => 'required|exists:movies,id']);

        $movieId = $request->integer('movie_id');

        if ($movieList->items()->where('movie_id', $movieId)->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['in_list' => true]);
            }
            return back();
        }

        $position = $movieList->items()->max('position') + 1;

        $movieList->items()->create([
            'movie_id' => $movieId,
            'position' => $position,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['in_list' => true, 'position' => $position]);
        }

        return back()->with('success', 'Movie added to list.');
    }

    public function destroy(Request $request, MovieList $movieList, Movie $movie): JsonResponse|RedirectResponse
    {
        abort_unless($movieList->user_id === $request->user()->id, 403);

        $movieList->items()->where('movie_id', $movie->id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['in_list' => false]);
        }

        return back()->with('success', 'Movie removed from list.');
    }

    public function reorder(Request $request, MovieList $movieList): JsonResponse
    {
        abort_unless($movieList->user_id === $request->user()->id, 403);

        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:movie_list_items,id',
        ]);

        foreach ($request->input('order') as $position => $itemId) {
            $movieList->items()->where('id', $itemId)->update(['position' => $position + 1]);
        }

        return response()->json(['ok' => true]);
    }
}
