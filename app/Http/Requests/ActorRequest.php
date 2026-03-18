<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                   => 'required|string|max:255',
            'date_of_birth'          => 'nullable|date|before:today',
            'nationality'            => 'nullable|string|max:255',
            'filmography'            => 'nullable|array',
            'filmography.*.movie_id' => 'nullable|integer|exists:movies,id',
            'filmography.*.role'     => 'nullable|string|max:255',
        ];
    }
}
