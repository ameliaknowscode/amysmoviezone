<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->welcomed_at) {
            return redirect()->route('home');
        }

        return view('onboarding');
    }

    public function complete(Request $request): RedirectResponse
    {
        $request->user()->update(['welcomed_at' => now()]);

        return redirect()->route('movies.browse');
    }
}
