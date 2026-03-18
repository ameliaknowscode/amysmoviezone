<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // When updating, exclude the current type from the unique check.
        $typeId = optional($this->route('type'))->id;

        return [
            'name'    => 'required|string|max:255|unique:types,name,' . $typeId,
            'is_crew' => 'boolean',
        ];
    }
}
