<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PersonCreditImportController extends Controller
{
    public function create(Person $person): View
    {
        return view('people.import-credits', compact('person'));
    }

    public function store(Request $request, Person $person): View|RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path   = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        // Validate header row
        $header = array_map('trim', fgetcsv($handle));

        $required = ['movie_title', 'release_year', 'type'];
        foreach ($required as $col) {
            if (! in_array($col, $header)) {
                fclose($handle);
                return back()->withErrors(['file' => 'CSV must have "movie_title", "release_year", and "type" columns.']);
            }
        }

        $titleIdx     = array_search('movie_title',   $header);
        $yearIdx      = array_search('release_year',  $header);
        $typeIdx      = array_search('type',          $header);
        $charIdx      = array_search('character',     $header);

        $imported  = 0;
        $errors    = [];
        $row       = 1;
        $maxYear   = (int) date('Y') + 5;
        $rows      = [];
        $typeCache = [];

        // First pass: validate all rows before making any changes
        while (($line = fgetcsv($handle)) !== false) {
            $row++;

            $movieTitle   = isset($line[$titleIdx]) ? trim($line[$titleIdx]) : '';
            $year         = isset($line[$yearIdx])  ? trim($line[$yearIdx])  : '';
            $typeNameRaw  = isset($line[$typeIdx])  ? trim($line[$typeIdx])  : '';
            $characterRaw = ($charIdx !== false && isset($line[$charIdx])) ? trim($line[$charIdx]) : '';

            if ($movieTitle === '') {
                $errors[] = ['row' => $row, 'movie' => '(empty)', 'reason' => 'Movie title is required.'];
                continue;
            }

            if (! ctype_digit($year) || (int) $year < 1888 || (int) $year > $maxYear) {
                $errors[] = ['row' => $row, 'movie' => $movieTitle, 'reason' => "Release year must be a number between 1888 and {$maxYear}."];
                continue;
            }

            // Expand pipe-separated types; characters are positionally mapped to Actor entries only
            $typeNames  = array_map('trim', explode('|', $typeNameRaw));
            $characters = array_map('trim', explode('|', $characterRaw));
            $count      = count($typeNames);
            $charI      = 0;

            for ($i = 0; $i < $count; $i++) {
                $typeName = $typeNames[$i];

                if ($typeName === '') {
                    $errors[] = ['row' => $row, 'movie' => $movieTitle, 'reason' => 'Type is required.'];
                    continue;
                }

                $key = strtolower($typeName);
                if (! array_key_exists($key, $typeCache)) {
                    $typeCache[$key] = Type::whereRaw('LOWER(name) = ?', [$key])->first();
                }
                $type = $typeCache[$key];

                if ($type === null) {
                    $errors[] = ['row' => $row, 'movie' => $movieTitle, 'reason' => "Unknown type \"{$typeName}\". Add it under Admin → Types first."];
                    continue;
                }

                // Only Actor-type credits receive a character value
                $isActor   = strtolower($typeName) === 'actor';
                $character = ($isActor && isset($characters[$charI]) && $characters[$charI] !== '')
                    ? $characters[$charI]
                    : null;
                if ($isActor) {
                    $charI++;
                }

                $rows[] = [
                    'movie_title' => $movieTitle,
                    'year'        => (int) $year,
                    'type'        => $type,
                    'character'   => $character,
                ];
            }
        }

        fclose($handle);

        // If no valid rows at all and there are errors, don't wipe existing credits
        if (empty($rows) && ! empty($errors)) {
            $rowErrors = $errors;
            return view('people.import-credits', compact('person', 'rowErrors'))->with('imported', 0);
        }

        // Replace all existing credits then import valid rows
        $person->credits()->delete();

        $movieCache = [];
        $batch      = [];
        foreach ($rows as $r) {
            $key   = strtolower($r['movie_title']) . '|' . $r['year'];
            $movie = $movieCache[$key]
                ?? ($movieCache[$key] = Movie::whereRaw('LOWER(title) = ?', [strtolower($r['movie_title'])])
                    ->where('release_year', $r['year'])
                    ->first()
                    ?? Movie::create([
                        'title'        => $r['movie_title'],
                        'slug'         => Str::slug($r['movie_title']),
                        'release_year' => $r['year'],
                    ]));

            $batch[] = [
                'person_id'  => $person->id,
                'movie_id'   => $movie->id,
                'type_id'    => $r['type']->id,
                'character'  => $r['character'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $imported++;
        }

        if (! empty($batch)) {
            Credit::insert($batch);
        }

        $rowErrors = $errors;
        return view('people.import-credits', compact('person', 'imported', 'rowErrors'));
    }
}
