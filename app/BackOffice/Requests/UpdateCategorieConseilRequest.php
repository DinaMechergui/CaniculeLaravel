<?php

namespace App\BackOffice\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategorieConseilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ignore() : on peut garder son propre nom sans erreur « unique ».
            'nom' => [
                'required', 'string', 'max:100',
                Rule::unique('categorie_conseils', 'nom')->ignore($this->route('categorieConseil')),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'nom.unique' => 'Cette catégorie existe déjà.',
            'description.max' => 'La description ne doit pas dépasser 255 caractères.',
        ];
    }
}
