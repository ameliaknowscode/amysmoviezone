<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $collectionId = optional($this->route('collection'))->id;

        return [
            'name'        => ['required', 'string', 'max:255', 'unique:collections,name,' . $collectionId],
            'description' => ['nullable', 'string'],
        ];
    }
}
