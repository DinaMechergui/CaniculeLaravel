<?php

namespace App\BackOffice\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation de la modification d'un type de signalement.
 */
class UpdateTypeSignalementRequest extends FormRequest
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
        // Récupère l'id depuis la route (paramètre typeSignalement).
        $id = $this->route('typeSignalement')?->id ?? $this->route('typeSignalement');

        return [
            'libelle' => [
                'required',
                'string',
                'max:100',
                Rule::unique('type_signalements', 'libelle')->ignore($id),
            ],
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
