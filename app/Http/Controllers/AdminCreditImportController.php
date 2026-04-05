<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\View\View;

class AdminCreditImportController extends Controller
{
    public function create(): View
    {
        $movies = Movie::orderBy('title')->get(['id', 'title', 'release_year']);

        return view('admin.import-credits-select', compact('movies'));
    }
}
