<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MovieCreditImportController extends Controller
{
    public function create(Movie $movie): View
    {
        return view('movies.import-credits', compact('movie'));
    }

    public function store(Request $request, Movie $movie): View|RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path   = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        // Validate header row
        $header = array_map('trim', fgetcsv($handle));

        $required = ['person_name', 'type'];
        foreach ($required as $col) {
            if (! in_array($col, $header)) {
                fclose($handle);
                return back()->withErrors(['file' => 'CSV must have "person_name" and "type" columns.']);
            }
        }

        $personIdx = array_search('person_name', $header);
        $typeIdx   = array_search('type',        $header);
        $charIdx   = array_search('character',   $header);

        $imported = 0;
        $errors   = [];
        $row      = 1;
        $rows     = [];

        // First pass: validate all rows before making any changes
        while (($line = fgetcsv($handle)) !== false) {
            $row++;

            $personName   = isset($line[$personIdx]) ? trim($line[$personIdx]) : '';
            $typeNameRaw  = isset($line[$typeIdx])   ? trim($line[$typeIdx])   : '';
            $characterRaw = ($charIdx !== false && isset($line[$charIdx])) ? trim($line[$charIdx]) : '';

            if ($personName === '') {
                $errors[] = ['row' => $row, 'person' => '(empty)', 'reason' => 'Person name is required.'];
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
                    $errors[] = ['row' => $row, 'person' => $personName, 'reason' => 'Type is required.'];
                    continue;
                }

                $type = Type::whereRaw('LOWER(name) = ?', [strtolower($typeName)])->first()
                    ?? Type::create(['name' => $typeName, 'is_crew' => false]);

                // Only Actor-type credits receive a character value
                $isActor   = strtolower($typeName) === 'actor';
                $character = ($isActor && isset($characters[$charI]) && $characters[$charI] !== '')
                    ? $characters[$charI]
                    : null;
                if ($isActor) {
                    $charI++;
                }

                $rows[] = [
                    'person_name' => $personName,
                    'type'        => $type,
                    'character'   => $character,
                ];
            }
        }

        fclose($handle);

        // If no valid rows at all and there are errors, don't wipe existing credits
        if (empty($rows) && ! empty($errors)) {
            $rowErrors = $errors;
            return view('movies.import-credits', compact('movie', 'rowErrors'))->with('imported', 0);
        }

        // Replace all existing credits then import valid rows
        $movie->credits()->delete();

        foreach ($rows as $r) {
            // Find existing person (case-insensitive) or create them
            $person = Person::whereRaw('LOWER(name) = ?', [strtolower($r['person_name'])])->first();

            if (! $person) {
                $person = Person::create([
                    'name' => $r['person_name'],
                    'slug' => Str::slug($r['person_name']),
                ]);
            }

            $movie->credits()->create([
                'person_id' => $person->id,
                'type_id'   => $r['type']->id,
                'character' => $r['character'],
            ]);

            $imported++;
        }

        $rowErrors = $errors;
        return view('movies.import-credits', compact('movie', 'imported', 'rowErrors'));
    }
}
