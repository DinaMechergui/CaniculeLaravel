<?php

namespace App\BackOffice\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation de la création d'un type de signalement.
 */
class StoreTypeSignalementRequest extends FormRequest
{
    /**
     * Les routes sont déjà protégées par le middleware « admin ».
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'libelle' => ['required', 'string', 'max:100', 'unique:type_signalements,libelle'],
        ];
    }

    /**
     * Messages d'erreur en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'libelle.required' => 'Le libellé du type est obligatoire.',
            'libelle.string'   => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max'      => 'Le libellé ne doit pas dépasser 100 caractères.',
            'libelle.unique'   => 'Ce libellé de type de signalement existe déjà.',
        ];
    }
}
