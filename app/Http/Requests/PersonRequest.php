<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'date_of_birth'      => 'nullable|date|before:today',
            'date_of_death'      => 'nullable|date|after_or_equal:date_of_birth',
            'nationality'        => 'nullable|string|max:255',
            'credits'            => ['nullable', 'array'],
            'credits.*.movie_id' => ['nullable', 'integer', 'exists:movies,id'],
            'credits.*.type_id'  => ['nullable', 'integer', 'exists:types,id'],
            'credits.*.character'=> ['nullable', 'string', 'max:255'],
        ];
    }
}
