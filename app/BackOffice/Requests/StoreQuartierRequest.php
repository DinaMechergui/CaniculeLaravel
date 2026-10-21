<?php

namespace App\BackOffice\Requests;

use App\BackOffice\Requests\Concerns\HasCoordinatesValidation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuartierRequest extends FormRequest
{
    use HasCoordinatesValidation;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return array_merge([
            'nom' => ['required', 'string', 'max:100', 'unique:quartiers,nom'],
            'ville' => ['required', 'string', 'max:100'],
            'code_postal' => ['required', 'string', 'max:10'],
        ], $this->coordinateRules());
    }

    public function messages(): array
    {
        return array_merge([
            'nom.required' => 'Le nom du quartier est obligatoire.',
            'nom.string' => 'Le nom du quartier doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du quartier ne peut pas dépasser 100 caractères.',
            'nom.unique' => 'Ce nom de quartier est déjà utilisé.',
            'ville.required' => 'La ville est obligatoire.',
            'ville.string' => 'La ville doit être une chaîne de caractères.',
            'ville.max' => 'Le nom de la ville ne peut pas dépasser 100 caractères.',
            'code_postal.required' => 'Le code postal est obligatoire.',
            'code_postal.string' => 'Le code postal doit être une chaîne de caractères.',
            'code_postal.max' => 'Le code postal ne peut pas dépasser 10 caractères.',
        ], $this->coordinateMessages());
    }

    protected function prepareForValidation(): void
    {
        $this->prepareCoordinatesForValidation();
    }
}
