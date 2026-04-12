<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GenreImportController extends Controller
{
    public function create(): View
    {
        return view('genres.import');
    }

    public function store(Request $request): View|RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path   = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        // Validate header row
        $header = array_map('trim', fgetcsv($handle));

        $required = ['title', 'release_year', 'genres'];
        foreach ($required as $col) {
            if (! in_array($col, $header)) {
                fclose($handle);
                return back()->withErrors(['file' => 'CSV must have "title", "release_year", and "genres" columns.']);
            }
        }

        $titleIdx  = array_search('title',        $header);
        $yearIdx   = array_search('release_year',  $header);
        $genreIdx  = array_search('genres',        $header);

        $updated    = 0;
        $skipped    = 0;
        $errors     = [];
        $row        = 1;
        $genreCache = [];

        while (($line = fgetcsv($handle)) !== false) {
            $row++;

            $title     = isset($line[$titleIdx])  ? trim($line[$titleIdx])  : '';
            $year      = isset($line[$yearIdx])   ? trim($line[$yearIdx])   : '';
            $genreRaw  = isset($line[$genreIdx])  ? trim($line[$genreIdx])  : '';

            if ($title === '') {
                $errors[] = ['row' => $row, 'title' => '(empty)', 'reason' => 'Title is required.'];
                continue;
            }

            if (! ctype_digit($year)) {
                $errors[] = ['row' => $row, 'title' => $title, 'reason' => 'Release year must be a number.'];
                continue;
            }

            if ($genreRaw === '') {
                $errors[] = ['row' => $row, 'title' => $title, 'reason' => 'Genres column is empty.'];
                continue;
            }

            $movie = Movie::whereRaw('LOWER(title) = ?', [strtolower($title)])
                ->where('release_year', (int) $year)
                ->first();

            if (! $movie) {
                $skipped++;
                $errors[] = ['row' => $row, 'title' => "{$title} ({$year})", 'reason' => 'Movie not found in database.'];
                continue;
            }

            // Resolve pipe-separated genre names, creating any that don't exist yet
            $genreNames = array_filter(array_map('trim', explode('|', $genreRaw)));
            $genreIds   = [];

            foreach ($genreNames as $name) {
                $key = strtolower($name);
                if (! isset($genreCache[$key])) {
                    $genreCache[$key] = Genre::whereRaw('LOWER(name) = ?', [$key])->first()
                        ?? Genre::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name)]);
                }
                $genreIds[] = $genreCache[$key]->id;
            }

            $movie->genres()->sync($genreIds);
            $updated++;
        }

        fclose($handle);

        $rowErrors = $errors;
        return view('genres.import', compact('updated', 'skipped', 'rowErrors'));
    }
}
