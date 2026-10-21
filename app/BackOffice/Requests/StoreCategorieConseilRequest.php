<?php

namespace App\BackOffice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategorieConseilRequest extends FormRequest
{
    // Les routes sont déjà protégées par le middleware « admin ».
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100', 'unique:categorie_conseils,nom'],
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
