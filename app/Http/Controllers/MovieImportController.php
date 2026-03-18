<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MovieImportController extends Controller
{
    public function create(): View
    {
        return view('movies.import');
    }

    public function store(Request $request): View|RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path   = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        // Read and validate header row
        $header = fgetcsv($handle);
        $header = array_map('trim', $header);

        if (! in_array('title', $header) || ! in_array('release_year', $header)) {
            fclose($handle);
            return back()->withErrors(['file' => 'CSV must have "title" and "release_year" columns.']);
        }

        $titleIdx = array_search('title', $header);
        $yearIdx  = array_search('release_year', $header);

        $imported = 0;
        $errors   = [];
        $row      = 1;

        $maxYear = (int) date('Y') + 5;

        while (($line = fgetcsv($handle)) !== false) {
            $row++;

            $title = isset($line[$titleIdx]) ? trim($line[$titleIdx]) : '';
            $year  = isset($line[$yearIdx])  ? trim($line[$yearIdx])  : '';

            // Row-level validation
            if ($title === '') {
                $errors[] = ['row' => $row, 'title' => $title ?: '(empty)', 'reason' => 'Title is required.'];
                continue;
            }

            if (strlen($title) > 255) {
                $errors[] = ['row' => $row, 'title' => $title, 'reason' => 'Title must not exceed 255 characters.'];
                continue;
            }

            if (! ctype_digit($year) || (int) $year < 1888 || (int) $year > $maxYear) {
                $errors[] = ['row' => $row, 'title' => $title, 'reason' => "Release year must be a number between 1888 and {$maxYear}."];
                continue;
            }

            // Duplicate check (case-insensitive title + same year)
            $duplicate = Movie::whereRaw('LOWER(title) = ?', [strtolower($title)])
                ->where('release_year', (int) $year)
                ->exists();

            if ($duplicate) {
                $errors[] = ['row' => $row, 'title' => $title, 'reason' => "A movie called \"{$title}\" ({$year}) already exists."];
                continue;
            }

            Movie::create([
                'title'        => $title,
                'slug'         => Str::slug($title),
                'release_year' => (int) $year,
            ]);

            $imported++;
        }

        fclose($handle);

        $rowErrors = $errors;
        return view('movies.import', compact('imported', 'rowErrors'));
    }
}
