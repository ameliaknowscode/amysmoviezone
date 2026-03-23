<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function store(string $username): RedirectResponse
    {
        $target = User::where('username', $username)->firstOrFail();

        abort_if(Auth::id() === $target->id, 403);

        Auth::user()->following()->syncWithoutDetaching([$target->id]);

        return back();
    }

    public function destroy(string $username): RedirectResponse
    {
        $target = User::where('username', $username)->firstOrFail();

        Auth::user()->following()->detach($target->id);

        return back();
    }
}
