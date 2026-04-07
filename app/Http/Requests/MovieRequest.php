<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('credits_json')) {
            $this->merge(['credits' => json_decode($this->input('credits_json'), true) ?? []]);
        }
    }

    public function rules(): array
    {
        return [
            'title'               => 'required|string|max:255',
            'release_year'        => 'required|integer|min:1888|max:' . (date('Y') + 5),
            'poster'              => 'nullable|file|image|max:2048',
            'credits'             => 'nullable|array',
            'credits.*.person_id' => 'nullable|integer|exists:people,id',
            'credits.*.type_id'   => 'nullable|integer|exists:types,id',
            'credits.*.character' => 'nullable|string|max:255',
        ];
    }
}
