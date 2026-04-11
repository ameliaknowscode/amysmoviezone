<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $genreId = optional($this->route('genre'))->id;

        return [
            'name' => 'required|string|max:255|unique:genres,name,' . $genreId,
        ];
    }
}
