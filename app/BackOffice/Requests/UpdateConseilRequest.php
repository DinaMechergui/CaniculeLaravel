<?php

namespace App\BackOffice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConseilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categorie_conseil_id' => ['required', 'exists:categorie_conseils,id'],
            'titre' => ['required', 'string', 'max:150'],
            'contenu' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'categorie_conseil_id.required' => 'Veuillez choisir une catégorie.',
            'categorie_conseil_id.exists' => 'La catégorie choisie n\'existe pas.',
            'titre.required' => 'Le titre est obligatoire.',
            'titre.max' => 'Le titre ne doit pas dépasser 150 caractères.',
            'contenu.required' => 'Le contenu est obligatoire.',
        ];
    }
}
